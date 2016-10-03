<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cargargasto extends CI_Controller {

	protected $numeroordendespacho =  '';
	private $usuariologin, $sessionflag, $usuariocodger, $acc_lectura, $acc_escribe, $acc_modifi;

	function __construct()
	{
		parent::__construct();
		$this->load->database('gastossystema');
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->model('menu');
		$this->output->enable_profiler(TRUE);
	}

	/**
	 * Index Page for this controller.
	 * 		http://example.com/index.php/indexcontroler
	 * 		http://example.com/index.php/indexcontroler/index
	 * map to /index.php/indexcontroler/<method_name>
	 */
	public function index()
	{
		if( $this->session->userdata('logueado') == FALSE)
		{
			redirect('manejousuarios/desverificarintranet');
		}
		$data['menu'] = $this->menu->general_menu();
		/* cargar y listaar las CATEGORIAS que se usaran para registros */
		$sqlcategoria = "
		select
		 ifnull(cod_categoria,'99999999999999') as cod_categoria,      -- YYYYMMDDhhmmss
		 ifnull(des_categoria,'sin_descripcion') as des_categoria,
		 ifnull(fecha_categoria, 'INVALIDO') as fecha_categoria,
		 ifnull(sessionflag,'') as sessionflag
		from categoria
		  where ifnull(cod_categoria, '') <> '' and cod_categoria <> ''
		";
		$resultadoscategoria = $this->db->query($sqlcategoria);
		$arreglocategoriaes = array(''=>'');
		foreach ($resultadoscategoria->result() as $row)
		{
			$arreglocategoriaes[''.$row->cod_categoria] = '' . $row->des_categoria . '-' . $row->fecha_categoria;
		}
		$data['list_categoria'] = $arreglocategoriaes; // agrega este arreglo una lista para el combo box
		unset($arreglocategoriaes['']);
		/* cargar y listaar las SUBCATEGORIAS que se usaran para registros */
		$sqlsubcategoria = "
		SELECT
		SUBSTRING(sb.cod_subcategoria,15) as code,
		ca.cod_categoria,
		ca.des_categoria,
		sb.cod_subcategoria,
		sb.des_subcategoria,
		ca.fecha_categoria,
		sb.fecha_subcategoria,
		sb.sessionflag
		FROM categoria as ca
		join subcategoria as sb
		on SUBSTRING(sb.cod_subcategoria,1,14) = ca.cod_categoria
		";
		$resultadossubcategoria = $this->db->query($sqlsubcategoria);
		$arreglosubcategoriaes = array(''=>'');
		foreach ($resultadossubcategoria->result() as $row)
		{
			$arreglosubcategoriaes[''.$row->cod_subcategoria] = $row->code .': ' . $row->des_categoria . ' - ' . $row->des_subcategoria;
		}
		$data['list_subcategoria'] = $arreglosubcategoriaes; // agrega este arreglo una lista para el combo box
		unset($arreglosubcategoriaes['']);
		/* cargar y listaar las UBIUCACIONES que se usaran para registros */
		$sqlentidad = "
		select
		 abr_entidad, abr_zona, des_entidad, codger,
		 ifnull(cod_entidad,'99999999999999') as cod_entidad,      -- YYYYMMDDhhmmss
		 ifnull(des_entidad,'sin_descripcion') as des_entidad
		from entidad
		  where ifnull(cod_entidad, '') <> '' and cod_entidad <> ''
		";
		$resultadosentidad = $this->db->query($sqlentidad);
		$arregloentidades = array(''=>'');
		foreach ($resultadosentidad->result() as $row)
		{
			$arregloentidades[''.$row->cod_entidad] = $row->cod_entidad . ' - ' . $row->abr_entidad .' - ' . $row->des_entidad . ' ('. $row->abr_zona .')';
		}
		$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box
		unset($arregloentidades['']);
		/* ahora renderizar o pintar el formulario de carga la vista */
		$this->load->view('header.php',$data);
		$this->load->view('cargargasto.php',$data);
		$this->load->view('footer.php',$data);
	}

	public function registrargasto()
	{
		if( $this->session->userdata('logueado') == FALSE)
		{
			redirect('manejousuarios/desverificarintranet');
		}
		$userdata = $this->session->all_userdata();
		$usercorreo = $userdata['correo'];
		$userintran = $userdata['intranet'];
		// OBTENER DATOS DE FORMULARIO ***************************** /
		$fec_registro = $this->input->get_post('fec_registro');
		$mon_registro = $this->input->get_post('mon_registro');
		$des_registro = $this->input->get_post('des_registro');
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_subcategoria = $this->input->get_post('cod_subcategoria');
		// GENERACION de la carga id codigo de registro
		$cod_registro = 'GAS' . $fec_registro . date('His');
		$cod_adjunto = $cod_registro . 'ADJ' . $fec_registro . date('His');
		// OBTENER DATOS FORMULARIO CARGA DEL ARCHIVO ****************** */
		$cargaconfig['upload_path'] = CATAPATH . '/appweb/archivoscargas';
		$cargaconfig['allowed_types'] = 'gif|jpg|png';
		$cargaconfig['max_size']  = 0;  //$cargaconfig['max_size']= '100'; // en kilobytes
		$cargaconfig['max_width'] = 0;
		$cargaconfig['max_height'] = 0;
		//$cargaconfig['remove_spaces'] = true;
		$cargaconfig['encrypt_name'] = TRUE;
		$this->load->library('upload', $cargaconfig);
		$this->load->helper('inflector');
		$this->upload->initialize($cargaconfig);
		$this->upload->overwrite = true;
		if ( $this->upload->do_upload('nam_archivo')) // nombre del campo alla en el formulario
		{
			$file_data = $this->upload->data();
			$filenamen = $cod_adjunto . $file_data['file_ext'];
			$filenameorig =  $file_data['file_path'] . $file_data['file_name'];
			$filenamenewe =  $file_data['file_path'] . $filenamen;
			copy( $filenameorig, $filenamenewe); // TODO: rename en produccion // rename( $filenameorig, $filenamenewe);
			//$file_data['file_name'] = $filenamen;
			$data['upload_data'] = $file_data;
			$data['archivos'] = $filenameorig . '-' . $filenamenewe ;
			$data['filenamen'] = $filenamen;
			$conadjunto = TRUE;
		}
		else
		{
			// TODO manejo de error , aparte de si no uso archivo de carga "upload_errors"
			$conadjunto = FALSE;
			$data['filenamen'] = "sin archivos";
		}
		$cantidadLineas = 0;
			$resultadocarga = array('Error, no se completo el proceso', 'Sin datos', '0', '', '', '', '');
			// procesar el registro sin el adjunto
            $sqlregistrargasto = "
            INSERT INTO gastossystema.registro_gastos
			(
			   cod_registro, cod_sucursal,
			   cod_categoria, cod_subcategoria,
			   des_registro,
			   mon_registro,
			   fecha_registro, fecha_factura,
			   sessionflag,
			   estado,
			   num_factura
			 )
			VALUES
			(
			  '".$cod_registro."', '".$cod_entidad."',
			  SUBSTRING('".$cod_subcategoria."',1,14),'".$cod_subcategoria."',
			  '".$des_registro."',
			  '".$mon_registro."',
			  '".$fec_registro."',
			  '',
			  '',
			  'PROCESADO',
			  ''
			)";
			$this->db->query($sqlregistrargasto);
			if ( $conadjunto )
			{
				$sqlregistrargasto2 = "
				INSERT INTO gastossystema.registro_adjunto
				(
				  cod_adjunto,
				  cod_registro,
				  hex_adjunto,
				  ruta_adjunto,
				  fecha_adjunto,
				  sessionflag,
				  nam_adjunto,
				  nam_archivo
				 )
				VALUES
				(
				'".$cod_adjunto."',
				'".$cod_registro."',
				'',
				'".$file_data['full_path']."',
				'',
				'',
				'".$file_data['file_name']."',
				'".$filenameorig."'
				)
				";
				$this->db->query($sqlregistrargasto2);
			}
		   // CARGA EXITOSA UESTRO DETALLE ************************************* /
			$sqlregistro = "
			SELECT
			  registro_gastos.cod_registro,
			  registro_adjunto.cod_adjunto,
			  registro_gastos.cod_sucursal,
			  registro_gastos.cod_categoria,
			  registro_gastos.cod_subcategoria,
			  registro_gastos.des_registro,
			  registro_gastos.mon_registro,
			  categoria.des_categoria,
			  subcategoria.des_subcategoria,
			  entidad.des_entidad,
			  entidad.abr_entidad,
			  entidad.abr_zona,
			  registro_gastos.estado,
			  registro_gastos.num_factura,
			  registro_adjunto.hex_adjunto,
			  registro_adjunto.nam_adjunto,
			  registro_gastos.fecha_registro,
			  registro_gastos.fecha_factura,
			  registro_adjunto.fecha_adjunto,
			  registro_gastos.sessionflag
			FROM	 gastossystema.registro_gastos
			LEFT JOIN	 gastossystema.registro_adjunto
			ON	 registro_adjunto.cod_registro = registro_gastos.cod_registro
			LEFT JOIN	 gastossystema.subcategoria
			ON	 subcategoria.cod_subcategoria = registro_gastos.cod_subcategoria
			LEFT JOIN	 gastossystema.categoria
			ON	 categoria.cod_categoria = registro_gastos.cod_categoria
			LEFT JOIN	 gastossystema.entidad
			ON	 entidad.cod_entidad = registro_gastos.cod_sucursal
			WHERE	 ifnull(registro_gastos.cod_registro,'') <> '' and registro_gastos.cod_registro <> ''
			ORDER BY cod_registro DESC	LIMIT 5";
//--			 and cod_registro = '".$cod_registro." '
		$resultadocarga = $this->db->query($sqlregistro); //row_array
        // TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$this->table->clear();
		$tmplnewtable = array ( 'table_open'  => '<table border="1" cellpadding="1" cellspacing="1" class="table">' );
		$this->table->set_caption("Sus ultimas cargas:");
		$this->table->set_template($tmplnewtable);
		$this->table->set_heading(
			'Categoria', 'Concepto',// 'des_categoria', 'des_subcategoria',
			'Destino',  //'des_entidad','abr_entidad', 'abr_zona',
			'Gasto', 'Monto',
			'Estado',
			'fecha_registro'
			// 	'fecha_factura', 	'fecha_adjunto',	'sessionflag'	'num_factura',
			//	'hex_adjunto', 'nam_adjunto',// TODO : link para visualizar
			//'cod_registro', 'cod_adjunto', 'cod_sucursal', 'cod_categoria', 'cod_subcategoria'
		);
		$resultadocargatablatxtmsg = "| cod_producto \t| can_despachar \t| des_producto \t\t".PHP_EOL;
		$resultadocargatabla = $resultadocarga->result_array();
		foreach ($resultadocargatabla as $rowtable)
		{
			$this->table->add_row(
			$rowtable['des_categoria'], $rowtable['des_subcategoria'],
			$rowtable['des_entidad'] . ' ('.$rowtable['abr_entidad'].') -'. $rowtable['abr_zona'],
			$rowtable['des_registro'], $rowtable['mon_registro'],
			$rowtable['estado'],
			$rowtable['fecha_registro']
			// $rowtable['fecha_factura'], $rowtable['fecha_adjunto'], $rowtable['sessionflag'],$rowtable['num_factura'],
			//$rowtable['hex_adjunto'], $rowtable['nam_adjunto'], // TODO: link para descargar / visualizar
			//$rowtable['cod_registro'], $rowtable['cod_adjunto'], $rowtable['cod_sucursal'], $rowtable['cod_categoria'], $rowtable['cod_subcategoria']
			);

/*			$resultadocargatablatxtmsg .=
			"| ".$rowtable['cod_producto'] .
			" \t| ". $rowtable['can_cantidaddespachar'] .
			" \t| ". $rowtable['des_producto'] .
			' '.PHP_EOL;*/
		}
		$data['htmltablacargasregistros'] = $this->table->generate();
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'resultadocargardatos';
		$data['upload_errors'] = $this->upload->display_errors('<p>', '</p>');
		$data['userintran'] = $userintran;
		$data['fec_registro'] = $fec_registro;
		$data['mon_registro'] = $mon_registro;
		$data['des_registro'] = $des_registro;
		$data['cod_entidad'] = $cod_entidad;
		$data['cod_subcategoria'] = $cod_subcategoria;
		$data['cod_registro'] = $cod_registro;
		$data['cantidadLineas'] = $cantidadLineas;
		//$data['cantidadLineas'] = $cantidadLineas;
		//$data['cantidadLineas'] = $cantidadLineas;
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->view('header.php',$data);
		$this->load->view('cargargasto.php',$data);
		$this->load->view('footer.php',$data);
		/*
		// PROCESO POSTERIOR generacion de txt (y dale con el txt() para el ajuste
		$sql = "SELECT right('000000000'+DBA.td_orden_despacho.cod_interno,10) as cp, null as v2, DBA.td_orden_despacho.cantidad as ca, null as v3, '' as v4, ";//DBA.td_orden_despacho.precio_venta ";
		$sql .= " isnull(convert(integer, (DBA.td_orden_despacho.cantidad/(SELECT top 1 unid_empaque FROM DBA.ta_proveedor_producto where cod_proveedor<>'000000000000' and cod_interno=right('000000000'+DBA.td_orden_despacho.cod_interno,10)))),0) as bu ";
		$sql .= "  FROM DBA.tm_orden_despacho join DBA.td_orden_despacho on DBA.tm_orden_despacho.cod_order=DBA.td_orden_despacho.cod_order WHERE dba.tm_orden_despacho.cod_order='".$filenamen."'";
		// TODO agregazr columna de precio sacar de subselect de la orden despacho
		// TODO agregar numero de linea incrementar, sacar del numero de la linea
	//}
	//public function generacionautomatica()
	//{
		$this->load->dbutil();
		$querypaltxt = $this->db->query($sql);
		$correocontenido = $this->dbutil->csv_from_result($querypaltxt, "\t", "\n", '', FALSE);
		// volvar a un archivo de esta orden despacho asociada:
		// volvar a un archivo de esta orden despacho asociada:
		$this->load->helper('file');
		//appweb/archivoscargas
		$filenameneweordendespachoadjuntar = $cargaconfig['upload_path'] . '/ordendespachogenerada' . $this->numeroordendespacho . '.txt';
		if ( ! write_file($filenameneweordendespachoadjuntar, $correocontenido))
		{
			 echo 'Unable to write the file';
		}
		//$intranet='0000a';
		$sql = "select top 1 correo from dba.tm_codmsc_correo where codmsc='".$intranet."'";
		$sqlcorreoorigen = $this->db->query($sql);
		$obtuvecorreo = 0;
		foreach ($sqlcorreoorigen->result() as $correorow)
		{
			$correoorigenaenviarle = $correorow->correo;
			$obtuvecorreo++;
		}
		if ($obtuvecorreo < 1)
			$correoorigenaenviarle = 'ordenesdespachos@intranet1.net.ve, lenz_gerardo@intranet1.net.ve';

		$this->load->library('email');
		/*
		// esta configuracion requiere mejoras en la libreia, no conecta bien ssl
		$configm1['protocol'] = 'smtp';
		$configm1['smtp_host'] = 'ssl://intranet1.net.ve';
		$configm1['smtp_port'] = '465';
		$configm1['smtp_timeout'] = '8';
		$configm1['smtp_user'] = 'lenz_gerardo';
		$configm1['smtp_pass'] = 'deide.3';
		$configm1['charset'] = 'utf-8';
		$configm1['starttls'] = TRUE;
		$configm1['smtp_crypto'] = 'tls';
		$configm1['newline'] = "\n";
		$configm1['mailtype'] = 'text'; // or html
		$configm1['validation'] = FALSE; // bool whether to validate email or not
		$this->email->initialize($configm1);
		$this->email->from('ordenesdespachos@intranet1.net.ve', 'ordenesdespachos');
		$this->email->cc($correousuariosesion);
		$this->email->to($correoorigenaenviarle); // enviar a los destinos de galpones
		$this->email->subject('Orden Despacho '. $this->numeroordendespacho .' Origen:'.$intranet.' Destino:'.$fec_registro);
		//$messageenviar = str_replace("\n", "\r\n", $correocontenido);
		$this->email->message('Orden de despacho adjunta.'.PHP_EOL.PHP_EOL.$correocontenido );
		$this->email->attach($filenameneweordendespachoadjuntar);
		$this->email->send();
*/

/*
		$configm2['protocol'] = 'mail';// en sysdevel y sysnet envia pero syscenter no
		$configm2['wordwrap'] = FALSE;
		$configm2['starttls'] = TRUE; // requiere sendmail o localmail use courierd START_TLS_REQUIRED=1 sendmail no envia
		$configm2['smtp_crypto'] = 'tls';
//		$configm2['mailtype'] = 'html';

		$this->load->library('email');
		$this->email->initialize($configm2);
		$this->email->from('ordenesdespachos@intranet1.net.ve', 'ordenesdespachos');
//		if ($obtuvecorreo < 1)
		    $this->email->cc($correousuariosesion);
		$this->email->reply_to('ordenesdespachos@intranet1.net.ve', 'ordenesdespachos');
		$this->email->to($correoorigenaenviarle ); // enviar a los destinos de galpones
		//if ($obtuvecorreo < 1)
			$this->email->subject('Registro de gasto '. $this->numeroordendespacho .' Responsable:'.$intranet.' Fecha registro:'.$fec_registro);
		//else
		//	$this->email->subject('Orden prueba '. $this->numeroordendespacho .' Origen:'.$intranet.' Destino:'.$fec_registro);
		$this->email->message('Orden de despacho adjunta.'.PHP_EOL.PHP_EOL.'**************************************'.PHP_EOL.PHP_EOL.$resultadocargatablatxtmsg.$data['htmltablageneradodetalle'].'***************************************'.PHP_EOL.PHP_EOL.'Orden para el galpon cargar oasis:'.PHP_EOL.PHP_EOL.$correocontenido );
		$this->email->attach($filenameneweordendespachoadjuntar);
		$this->email->send();

		//echo $this->email->print_debugger();
*/
	}
}
