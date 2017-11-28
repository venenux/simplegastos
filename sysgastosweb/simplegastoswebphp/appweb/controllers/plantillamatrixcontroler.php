<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class plantillamatrixcontroler extends CI_Controller {

	private $DBGASTO = null;
	private $usuariologin, $sessionflag, $usuariocodger, $acc_lectura, $acc_escribe, $acc_modifi;

	function __construct()
	{
		parent::__construct();
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->model('menu');
		$this->output->enable_profiler(TRUE);
	}

	public function _verificarsesion()
	{
		if( $this->session->userdata('logueado') != TRUE)
			redirect('manejousuarios/desverificarintranet');
	}

	/**
	 * Index Page cuando se invoca la url de este controlador, 
	 * aqui se invoca la vista o otro metodo que la invoque
	 * map to /index.php/plantillamatrixcontroler/index
	 */
	public function index()
	{
		$this->seccionformulario();
	}
	
	public function seccionformulario()
	{
		/* ***** ini manejo de sesion ******************* */
		$this->_verificarsesion();
		$userdata = $this->session->all_userdata();
		$usercorreo = $userdata['correo'];
		$userintranet = $userdata['intranet'];
		$sessionflag = $this->session->userdata('username').date("YmdHis");
		$data['usercorreo'] = $usercorreo;
		$data['userintranet'] = $userintranet;
		$data['menu'] = $this->menu->menudesktop();
		/* ***** fin manejo de sesion ******************* */
		
		/* **** hay que cargar las bae de datos */
		$DBGASTO = $this->load->database('gastossystema',TRUE);
		/* ***** fin ********** */
		
		/* ****** ini cargar y listaar CATEGORIAS para comboboxes u otros ********** */
		$sqlcategoria = " select 
		 ifnull(cod_categoria,'99999999999999') as cod_categoria,
		 ifnull(des_categoria,'sin_descripcion') as des_categoria,
		 ifnull(fecha_categoria, '20160101') as fecha_categoria
		from categoria where ifnull(cod_categoria, '') <> '' and cod_categoria <> ''"; // documentado en wiki tabla y select
		$resultadoscategoria = $DBGASTO->query($sqlcategoria);
		$arreglocategoriaes = array(''=>'');  // declaro un arreglo y lo lleno con la lista (sera el combobox)
		foreach ($resultadoscategoria->result() as $row)
			$arreglocategoriaes[''.$row->cod_categoria] = '' . $row->des_categoria . '-' . $row->fecha_categoria;
		$data['list_categoria'] = $arreglocategoriaes; // meto en data para enviar a la vista
		unset($arreglocategoriaes['']);
		/* ****** fin cargar y listaar CATEGORIAS para comboboxes u otros ********** */

		/* ****** ini cargar y listaar SUBCATEGORIAS para comboboxes vista ********** */
		$sqlsubcategoria = " SELECT 
		 ifnull(ca.cod_categoria,'0') as cod_categoria, ifnull(ca.des_categoria,'ninguna') as des_categoria, 
		 ifnull(sb.cod_subcategoria,'0') as cod_subcategoria, ifnull(sb.des_subcategoria,'ninguna') as des_subcategoria,
		 ca.fecha_categoria, sb.fecha_subcategoria, sb.sessionflag
		FROM categoria AS ca JOIN subcategoria AS sb ON sb.cod_categoria = ca.cod_categoria 
		WHERE ifnull(cod_subcategoria, '') <> '' AND cod_subcategoria <> ''"; // documentado en wiki tabla y select
		$resultadossubcategoria = $DBGASTO->query($sqlsubcategoria);
		$arreglosubcategoriaes = array(''=>'');
		foreach ($resultadossubcategoria->result() as $row)
			$arreglosubcategoriaes[''.$row->cod_subcategoria] = $row->des_categoria . ' - ' . $row->des_subcategoria;
		$data['list_subcategoria'] = $arreglosubcategoriaes; // agrega este arreglo una lista para el combo box
		unset($arreglosubcategoriaes['']);
		/* cargar y listaar las UBIUCACIONES que se usaran para registros */
		$sqlentidad = "
		select
		 abr_entidad, abr_zona, des_entidad,
		 ifnull(cod_entidad,'99999999999999') as cod_entidad,      -- YYYYMMDDhhmmss
		 ifnull(des_entidad,'sin_descripcion') as des_entidad
		from entidad
		  where ifnull(cod_entidad, '') <> '' and cod_entidad <> ''
		";
		$resultadosentidad = $DBGASTO->query($sqlentidad);
		$arregloentidades = array(''=>'');
		foreach ($resultadosentidad->result() as $row)
		{
			$arregloentidades[''.$row->cod_entidad] = $row->cod_entidad . ' - ' . $row->abr_entidad .' - ' . $row->des_entidad . ' ('. $row->abr_zona .')';
		}
		$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box
		unset($arregloentidades['']);
		/* ****** fin cargar y listaar SUBCATEGORIAS para comboboxes vista ********** */
		
		/* ****** ini cargar y preparar para llamar y pintar vista ********** */
		$this->load->view('header.php',$data);
		$this->load->view('plantillamatrixvista.php',$data);
		$this->load->view('footer.php',$data);
		/* ****** fin cargar y preparar para llamar y pintar vista ********** */
	}

	public function secciontablamatrix()
	{
		/* ***** ini manejo de sesion ******************* */
		$this->_verificarsesion();
		$userdata = $this->session->all_userdata();
		$usercorreo = $userdata['correo'];
		$userintranet = $userdata['intranet'];
		$sessionflag = $this->session->userdata('username').date("YmdHis");
		$data['usercorreo'] = $usercorreo;
		$data['userintranet'] = $userintranet;
		$data['menu'] = $this->menu->menudesktop();
		/* ***** fin manejo de sesion ******************* */
		
		/* **** hay que cargar las bae de datos */
		$DBGASTO = $this->load->database('gastossystema',TRUE);
		/* ***** fin ********** */
		
		/* ******** inicio preparacino query cualquiera ejemplo ************* */
		$this->load->helper(array('form', 'url','inflector'));
		$cantidadLineas = 0;
		// creanos nuestro query sql que trae datos
		$sqlregistro = "
			SELECT
			  registro_gastos.cod_registro,
			  registro_adjunto.cod_adjunto,
			  registro_gastos.cod_entidad,
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
			  registro_adjunto.ruta_adjunto,
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
			ON	 entidad.cod_entidad = registro_gastos.cod_entidad
			WHERE	 ifnull(registro_gastos.cod_registro,'') <> '' and registro_gastos.cod_registro <> ''
			ORDER BY cod_registro DESC	LIMIT 5";
		
		/* ***** ini OBTENER DATOS DE FORMULARIO ***************************** */
		$fechafiltramatrix = $this->input->get_post('fechafiltramatrix');
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_subcategoria = $this->input->get_post('cod_subcategoria');
		/* ***** fin OBTENER DATOS DE FORMULARIO ***************************** */
		
		/* ***** ini filtrar los resultados del query segun formulario **************** */
		//if ( $fechafiltramatrix != '')
		//	$sqlregistro .= "and CONVERT(fecha_registro,UNSIGNED INTEGER) = CONVERT('".$fechafiltramatrix."',UNSIGNED INTEGER)";
		if ( $cod_entidad != '')
			$sqlregistro .= "and cod_entidad = '".$cod_entidad."'";
		if ( $cod_subcategoria != '')
			$sqlregistro .= "and registro_gastos.cod_subcategoria = '".$cod_subcategoria."'";
		$resultadocarga = $DBGASTO->query($sqlregistro); // ejecuto el query
		/* ***** fin filtrar los resultados del query segun formulario **************** */
		
		/* ***** ini pintar una tabla recorriendo el query **************** */
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->table->clear();
		$tmplnewtable = array ( 'table_open'  => '<table border="1" cellpadding="1" cellspacing="1" class="table">' );
		$this->table->set_caption("Tabla de gastos");
		$this->table->set_template($tmplnewtable);
		$this->table->set_heading(
				'Registro',	'Categoria', 'Subcategoria',// 'cod_categoria', 'des_categoria', 'des_subcategoria',
				'Destino',  //'des_entidad','abr_entidad', 'abr_zona',
				'Concepto descripcion', 'Monto', 'Estado', 'Realizado el'
			);
		$resultadocargatabla = $resultadocarga->result_array(); // llamo a los resultados del query
		foreach ($resultadocargatabla as $rowtable)
		{
			$this->table->add_row(
				$rowtable['cod_registro'], $rowtable['des_categoria'], $rowtable['des_subcategoria'],
				$rowtable['des_entidad'] . ' ('.$rowtable['abr_entidad'].') -'. $rowtable['abr_zona'],
				$rowtable['des_registro'], $rowtable['mon_registro'], $rowtable['estado'], $rowtable['fecha_registro']
			);
		}
		$data['htmlquepintamatrix'] = $this->table->generate(); // html generado lo envia a la matrix
		/* ***** fin pintar una tabla recorriendo el query **************** */
		
		
		$data['menu'] = $this->menu->menudesktop();
		$data['seccionpagina'] = 'secciontablamatrix';
		$data['userintran'] = $userintran;
		$data['fechafiltramatrix'] = $fechafiltramatrix;
		$data['cod_entidad'] = $cod_entidad;
		$data['cod_subcategoria'] = $cod_subcategoria;
		$this->load->view('header.php',$data);
		$this->load->view('cargargastoex.php',$data);
		$this->load->view('footer.php',$data);
	}

	public function enviarcorreo()
	{
		// PROCESO POSTERIOR generacion de txt y envio por correo
		$sql = "SELECT right('000000000'+DBA.td_orden_despacho.cod_interno,10) as cp, null as v2, DBA.td_orden_despacho.cantidad as ca, null as v3, '' as v4, ";//DBA.td_orden_despacho.precio_venta ";
		$sql .= " isnull(convert(integer, (DBA.td_orden_despacho.cantidad/(SELECT top 1 unid_empaque FROM DBA.ta_proveedor_producto where cod_proveedor<>'000000000000' and cod_interno=right('000000000'+DBA.td_orden_despacho.cod_interno,10)))),0) as bu ";
		$sql .= "  FROM DBA.tm_orden_despacho join DBA.td_orden_despacho on DBA.tm_orden_despacho.cod_order=DBA.td_orden_despacho.cod_order WHERE dba.tm_orden_despacho.cod_order='".$filenamen."'";
		$this->load->dbutil();
		$querypaltxt = $DBGASTO->query($sql);
		// ejemplo desde el sql generamos un adjunto
		$correocontenido = $DBGASTOutil->csv_from_result($querypaltxt, "\t", "\n", '', FALSE);
		$this->load->helper('file');
		$filenameneweordendespachoadjuntar = $cargaconfig['upload_path'] . '/ordendespachogenerada' . $this->numeroordendespacho . '.txt';
		if ( ! write_file($filenameneweordendespachoadjuntar, $correocontenido))
		{
			 echo 'Unable to write the file';
		}
		// en la db buscamos el correo del usuario y vemos a cuantos se enviaran
		$sql = "select top 1 correo from dba.tm_codmsc_correo where codmsc='".$intranet."'";
		$sqlcorreoorigen = $DBGASTO->query($sql);
		$obtuvecorreo = 0;
		foreach ($sqlcorreoorigen->result() as $correorow)
		{
			$correoorigenaenviarle = $correorow->correo;
			$obtuvecorreo++;
		}
		if ($obtuvecorreo < 1)
			$correoorigenaenviarle = 'ordenesdespachos@intranet1.net.ve, lenz_gerardo@intranet1.net.ve';
		// ahora procedemos apreparar el envio de correo
		$this->load->library('email');
		$configm1['protocol'] = 'smtp'; 		// esta configuracion requiere mejoras
		$configm1['smtp_host'] = 'ssl://intranet1.net.ve'; // porque en la libreia, no conecta bien ssl
		$configm1['smtp_port'] = '465';
		$configm1['smtp_timeout'] = '8';
		$configm1['smtp_user'] = 'usuarioqueenviacorreo';
		$configm1['smtp_pass'] = 'superclave';
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
		$this->email->subject('Orden Despacho '. $this->numeroordendespacho .' Origen:'.$intranet.' Destino:'.$fechafiltramatrix);
		//$messageenviar = str_replace("\n", "\r\n", $correocontenido);
		$this->email->message('Orden de despacho adjunta.'.PHP_EOL.PHP_EOL.$correocontenido );
		$this->email->attach($filenameneweordendespachoadjuntar);
		$this->email->send();
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
			$this->email->subject('Registro de gasto '. $this->numeroordendespacho .' Responsable:'.$intranet.' Fecha registro:'.$fechafiltramatrix);
		//else
		//	$this->email->subject('Orden prueba '. $this->numeroordendespacho .' Origen:'.$intranet.' Destino:'.$fechafiltramatrix);
		$this->email->message('Orden de despacho adjunta.'.PHP_EOL.PHP_EOL.'**************************************'.PHP_EOL.PHP_EOL.$resultadocargatablatxtmsg.$data['htmltablageneradodetalle'].'***************************************'.PHP_EOL.PHP_EOL.'Orden para el galpon cargar oasis:'.PHP_EOL.PHP_EOL.$correocontenido );
		$this->email->attach($filenameneweordendespachoadjuntar);
		$this->email->send();

		//echo $this->email->print_debugger();
*/
	}
}
