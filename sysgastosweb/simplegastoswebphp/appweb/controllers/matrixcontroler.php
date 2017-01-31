<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class matrixcontroler extends CI_Controller {

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
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		if( is_array($usuariocodgernow) )
		{
			if (in_array("998", $usuariocodgernow) or in_array("1000", $usuariocodgernow) )
				$this->nivel = 'administrador';
			else if (in_array("163", $usuariocodgernow) or in_array("251", $usuariocodgernow) )
				$this->nivel = 'contabilidad';
			else
				$this->nivel = 'especial';
		}
		else
		{
			if( $usuariocodgernow == '998' or $usuariocodgernow == '1000' )
				$this->nivel = 'administrador';
			else if( ( $usuariocodgernow > 399 and $usuariocodgernow < 998) or $usuariocodgernow == '196' or $usuariocodgernow == '252' or $usuariocodgernow == '200' )
				$this->nivel = 'sucursal';
			else if ( $usuariocodgernow == '163' or $usuariocodgernow == '251' )
				$this->nivel = 'contabilidad';
			else
				$this->nivel = 'especial';
		}
	}

	/**
	 * Index Page cuando se invoca la url de este controlador, 
	 * aqui se invoca la vista o otro metodo que la invoque
	 * map to /index.php/matrixcontroler/index
	 */
	public function index()
	{
		$this->seccionmatrixpedirla();
	}
	
	public function seccionmatrixpedirla($mens = null)
	{
		/* ***** ini manejo de sesion ******************* */
		$this->_verificarsesion();
		$userdata = $this->session->all_userdata();
		$usercorreo = $userdata['correo'];
		$userintranet = $userdata['intranet'];
		$sessionflag = $this->session->userdata('username').date("YmdHis");
		$data['usercorreo'] = $usercorreo;
		$data['userintranet'] = $userintranet;
		$data['menu'] = $this->menu->general_menu();
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
			$arreglocategoriaes[''.$row->cod_categoria] = ' ' . $row->des_categoria . ' ('.$row->cod_categoria.')' ;
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
		  where ifnull(cod_entidad, '') <> '' and cod_entidad <> '' and status <> 'INACTIVO'
		";
		$resultadosentidad = $DBGASTO->query($sqlentidad);
		$arregloentidades = array(''=>'');
		foreach ($resultadosentidad->result() as $row)
		{
			$arregloentidades[''.$row->cod_entidad] = $row->abr_entidad .' - ' . $row->des_entidad;
		}
		$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box
		unset($arregloentidades['']);
		/* ****** fin cargar y listaar SUBCATEGORIAS para comboboxes vista ********** */
		
		/* ****** ini cargar y preparar para llamar y pintar vista ********** */
		$this->load->view('header.php',$data);
		$this->load->view('matrixvista.php',$data);
		$this->load->view('footer.php',$data);
		/* ****** fin cargar y preparar para llamar y pintar vista ********** */
	}

	public function matrixtotalesfiltrado()
	{
		/* ***** ini manejo de sesion ******************* */
		$this->_verificarsesion();
		$userdata = $this->session->all_userdata();
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		$usercorreo = $userdata['correo'];
		$userintranet = $userdata['intranet'];
		$sessionflag1 = date("YmdHis") . $usuariocodgernow . '.' .$this->session->userdata('username');
		$data['usercorreo'] = $usercorreo;
		$data['userintranet'] = $userintranet;
		/* ***** fin manejo de sesion ******************* */
		
		$data['accionejecutada'] = 'seccionmatrixresultado';
		$userintran = $this->session->userdata('intranet');

		// ******* OBTENER DATOS DE FORMULARIO ***************************** /
		$this->load->library('form_validation');
		
		$fechainimatrix = $this->input->get_post('fechainimatrix');
		$fechafinmatrix = $this->input->get_post('fechafinmatrix');
		$this->form_validation->set_rules('fechainimatrix', 'Rango de fecha de inicio ', 'required');
		$this->form_validation->set_rules('fechafinmatrix', 'Rango de fecha limite ', 'required');
		
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_categoria = $this->input->get_post('cod_categoria');
		//if ( trim(str_replace(' ', '', $cod_entidad)) != '')
		//$this->form_validation->set_rules('cod_entidad', 'Entidad o de quien es la suma', 'required');
		//if ( trim(str_replace(' ', '', $cod_categoria)) != '')
		//$this->form_validation->set_rules('cod_categoria', 'Categoria del gasto', 'required');
		if ( $this->form_validation->run() == FALSE )
		{
			$mens = validation_errors();
			log_message('info', $mens.'.');
			return $this->seccionmatrixpedirla( $mens );
		}
		log_message('info', 'Cargando totales, filtrar '. $cod_entidad . ' en ' . $cod_categoria . ' identificacion como '.$sessionflag1);

		$this->load->database('gastossystema');
		$filtro1 = $filtro2 = $filtro3 = $filtro4 = '';
		if ( trim(str_replace(' ', '', $cod_entidad)) != '')
			$filtro1 = " and	a.cod_entidad = '".$this->db->escape_str($cod_entidad)."' ";
		if ( trim(str_replace(' ', '', $fechainimatrix)) != '')
			$filtro2 = " and CONVERT(a.fecha_concepto,UNSIGNED) >= CONVERT('".$this->db->escape_str($fechainimatrix)."',UNSIGNED)  ";
		if ( trim(str_replace(' ', '', $fechafinmatrix)) != '')
			$filtro3 = " and CONVERT(a.fecha_concepto,UNSIGNED) <= CONVERT('".$this->db->escape_str($fechafinmatrix)."',UNSIGNED)  ";
		if ( trim(str_replace(' ', '', $cod_categoria)) != '')
			$filtro4 = " and a.cod_categoria = '".$this->db->escape_str($cod_categoria)."' ";
		/* ***** fin OBTENER DATOS FORMULARIO ********** */

		
		/* ******** inicio filtrar y resultado query cualquiera ejemplo ************* */
		$this->load->helper(array('form', 'url','inflector'));
		// creanos nuestro query sql que trae datos
		$tablatempototales = 'totalizar'.$userintran.rand(6,8);
		$this->db->query("DROP TABLE IF EXISTS ".$tablatempototales); // ejecuto el query
		$sqlregistro = "
			CREATE TABLE IF NOT EXISTS ".$tablatempototales." SELECT * FROM (
					SELECT 
						a.cod_entidad, b.des_entidad, 
						a.cod_categoria, c.des_categoria,
						ifnull(a.mon_registro,0) as mon_registro,
						SUBSTRING(a.fecha_concepto,1,8) as fecha_concepto, a.fecha_registro,
						a.fecha_concepto as fecha_gasto
						,a.sessionficha
					FROM registro_gastos a 
						LEFT JOIN entidad b on a.cod_entidad=b.cod_entidad /* todas las entiddes deben registrar gasto*/
						LEFT JOIN categoria c ON a.cod_categoria=c.cod_categoria /*solo en las categorias que haya gasto */
					where 
						a.cod_registro <> '' and b.status <> 'INACTIVO' " . $filtro1 . $filtro2 . $filtro3 . $filtro4 . "
						and a.estado <> 'RECHAZADO'
				     UNION
					SELECT 
						a.cod_entidad, 'A l dia', 
						a.cod_categoria, 'TOTAL',
						IFNULL(SUM(IFNULL(a.mon_registro,0)),0) as mon_registro,
						SUBSTRING(a.fecha_concepto,1,8) as fecha_concepto, a.fecha_registro,
						a.fecha_concepto as fecha_gasto
						, '".date("YmdHis").".999.al.total' as sessionficha
					FROM registro_gastos a 
						LEFT JOIN entidad b on a.cod_entidad=b.cod_entidad /* todas las entiddes deben registrar gasto*/
						LEFT JOIN categoria c ON a.cod_categoria=c.cod_categoria /*solo en las categorias que haya gasto */
					where 
						a.cod_registro <> '' and b.status <> 'INACTIVO' " . $filtro1 . $filtro2 . $filtro3 . $filtro4 . " 
			) AS tablatotaltemp
			ORDER BY sessionficha DESC			
			";
		
		$this->db->query($sqlregistro); // ejecuto el query
		/* ***** fin filtrar y resultados del query segun formulario **************** */
		
		$this->load->helper(array('inflector','url'));
		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
		$crud->set_table($tablatempototales);
		$crud->set_theme('bootstrap'); // flexigrid tiene bugs en varias cosas
		$crud->set_primary_key('sessionficha');
		$crud->display_as('des_entidad','Entidad')
			 ->display_as('des_categoria','Centro<br>Coste')
			 ->display_as('mon_registro','Monto')
			 ->display_as('fecha_gasto','Fecha<br>Estimada')
			 ->display_as('fecha_concepto','Fecha<br>Gasto')
			 ->display_as('fecha_registro','Fecha<br>Ingresado')
			 ->display_as('sessionficha','Registro<br>Autor');
		$crud->columns('des_entidad','des_categoria','mon_registro','fecha_gasto','sessionficha');
		$crud->unset_add();
		$crud->unset_read();
		$crud->unset_edit();
		$crud->unset_delete();
		$crud->callback_column('mon_registro',array($this,'_numerosgente'));
		$output = $crud->render();

		$data['js_files'] = $output->js_files;
		$data['css_files'] = $output->css_files;
		$data['htmlquepintamatrix'] = $output->output;
		$data['menu'] = $this->menu->general_menu();
		$data['seccionpagina'] = 'seccionmatrixresultado';
		$data['fechainimatrix'] = $fechainimatrix;
		$data['fechafinmatrix'] = $fechafinmatrix;
		$data['cod_entidad'] = $cod_entidad;
		$data['cod_categoria'] = $cod_categoria;
		$this->db->query("DROP TABLE IF EXISTS ".$tablatempototales); // ejecuto el query
		$this->load->view('header.php',$data);
		$this->load->view('matrixvista.php',$data);
		$this->load->view('footer.php',$data);
	}

	public function _numerosgente($value, $row)
	{
		$formateado = number_format($row->mon_registro, 2, ',', '.');
		return $formateado;
	}

	public function enviarcorreo()
	{
		// PROCESO POSTERIOR generacion de txt y envio por correo
		$sql = "SELECT right('000000000'+DBA.td_orden_despacho.cod_interno,10) as cp, null as v2, DBA.td_orden_despacho.cantidad as ca, null as v3, '' as v4, ";//DBA.td_orden_despacho.precio_venta ";
		$sql .= " isnull(convert(integer, (DBA.td_orden_despacho.cantidad/(SELECT top 1 unid_empaque FROM DBA.ta_proveedor_producto where cod_proveedor<>'000000000000' and cod_interno=right('000000000'+DBA.td_orden_despacho.cod_interno,10)))),0) as bu ";
		$sql .= "  FROM DBA.tm_orden_despacho join DBA.td_orden_despacho on DBA.tm_orden_despacho.cod_order=DBA.td_orden_despacho.cod_order WHERE dba.tm_orden_despacho.cod_order='".$filenamen."'";
		$this->load->dbutil();
		$querypaltxt = $this->db->query($sql);
		// ejemplo desde el sql generamos un adjunto
		$correocontenido = $this->dbutil->csv_from_result($querypaltxt, "\t", "\n", '', FALSE);
		$this->load->helper('file');
		$filenameneweordendespachoadjuntar = $cargaconfig['upload_path'] . '/ordendespachogenerada' . $this->numeroordendespacho . '.txt';
		if ( ! write_file($filenameneweordendespachoadjuntar, $correocontenido))
		{
			 echo 'Unable to write the file';
		}
		// en la db buscamos el correo del usuario y vemos a cuantos se enviaran
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
