<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cargargastoadministrativo extends CI_Controller {

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
		$this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT',TRUE);
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', TRUE);
		$this->output->set_header('Pragma: no-cache', TRUE);
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT", TRUE);
		$this->output->enable_profiler(TRUE);
	}

	/**
	 * index del control de cargargastoadministrativo
	 */
	public function index()
	{
		if( $this->session->userdata('logueado') < 1)
		{
			redirect('manejousuarios/desverificarintranet');
		}
		$data['menu'] = $this->menu->general_menu();

		/* cargar y listaar las CATEGORIAS que se usaran para registros */
			$sqlcategoria = "
			select
			 ifnull(cod_categoria,'99999999999999') as cod_categoria,
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
			ca.cod_categoria,
			ca.des_categoria,
			sb.cod_subcategoria,
			sb.des_subcategoria,
			ca.fecha_categoria,
			sb.fecha_subcategoria,
			sb.sessionflag
			FROM categoria as ca
			join subcategoria as sb
			on sb.cod_categoria = ca.cod_categoria
			";
			$resultadossubcategoria = $this->db->query($sqlsubcategoria);
			$arreglosubcategoriaes = array(''=>'');
			foreach ($resultadossubcategoria->result() as $row)
			{
				$arreglosubcategoriaes[''.$row->cod_subcategoria] = $row->des_categoria . ' - ' . $row->des_subcategoria;
			}
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
			order by des_entidad
			";
			$resultadosentidad = $this->db->query($sqlentidad);
			$arregloentidades = array(''=>'');
			foreach ($resultadosentidad->result() as $row)
			{
				$arregloentidades[''.$row->cod_entidad] = $row->abr_entidad .' - ' . $row->cod_entidad . ' - ' . $row->des_entidad . ' ('. $row->abr_zona .')';
			}
			$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box
			unset($arregloentidades['']);
		/* ahora renderizar o pintar el formulario de carga la vista */
		$data['accionejecutada'] = 'cargardatosadministrativosfiltrar';
		$this->load->view('header.php',$data);
		$this->load->view('cargargastoadministrativo.php',$data);
		$this->load->view('footer.php',$data);
	}

	/* metodo de acceso url de gastos a mostrar los registros, detecta la accion ejecutada que son de dos tipos mostrar o filtrar */
	public function gastoregistros()
	{
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		if( $this->session->userdata('logueado') < 1)
			redirect('manejousuarios/desverificarintranet');
		if ($usuariocodgernow < 990 and $usuariocodgernow > 399 )
			redirect('cargargastosucursales/gastosucursalesrevisarlos');
		$userdata = $this->session->all_userdata();
		$usercorreo = $userdata['correo'];
		$usersessid = $userdata['session_id'];
		$userintran = $userdata['intranet'];
		$userpermacceso = $userdata['abr_entidad']; // TODO per,misos
		$accionejecutada = $this->input->get_post('accionejecutada');
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'cargardatosadministrativosfiltrar';
			// OBTENER DATOS DE FORMULARIO ***************************** /
		if ($accionejecutada = 'cargardatosadministrativosfiltrar')
		{
			$fec_registroini = $this->input->get_post('fec_registroini');
			$fec_registrofin = $this->input->get_post('fec_registrofin');
			$fec_conceptoini = $this->input->get_post('fec_conceptoini');
			$fec_conceptofin = $this->input->get_post('fec_conceptofin');
			$mon_registroigual = $this->input->get_post('mon_registroigual');
			$mon_registromayor = $this->input->get_post('mon_registromayor');
			$des_registrolike = $this->input->get_post('des_registrolike');
			$cod_entidad = $this->input->get_post('cod_entidad');
			$cod_categoria = $this->input->get_post('cod_categoria');
			$cod_subcategoria = $this->input->get_post('cod_subcategoria');
			$sessioncarga = $this->input->get_post('sessioncarga');
			$cod_registro = $this->input->get_post('cod_registro');
		}
		$this->load->helper(array('inflector','url'));
		$data['seguir']=$this->uri->segment(1).$this->uri->segment(2).$this->uri->segment(3);
		$tablaregistros = "registro_gastos";
		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
		$crud->set_table($tablaregistros);
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->set_primary_key('cod_registro');
			if ( $cod_entidad != '')
				$crud->where($tablaregistros.'.cod_entidad' ,$cod_entidad);
			if ( $cod_registro != '')
				$crud->where($tablaregistros.'.cod_registro' ,$cod_registro);
			if ( $cod_categoria != '')
				$crud->where($tablaregistros.'.cod_categoria', $cod_categoria);
			if ( $cod_subcategoria != '')
				$crud->where($tablaregistros.'.cod_subcategoria',$cod_subcategoria);
			if ( $des_registrolike != '')
				$crud->like('des_concepto',$des_registrolike);
			if ( $fec_registroini != '')
				$crud->where('CONVERT(fecha_registro,UNSIGNED) >= ',$fec_registroini);
			if ( $fec_registrofin != '')
				$crud->where('CONVERT(fecha_registro,UNSIGNED) <= ',$fec_registrofin);
			if ( $fec_conceptoini != '')
				$crud->where('CONVERT(fecha_concepto,UNSIGNED) >= ',$fec_conceptoini);
			if ( $fec_conceptofin != '')
				$crud->where('CONVERT(fecha_concepto,UNSIGNED) <= ',$fec_conceptofin);
			if ( $mon_registroigual != '')
				$crud->like('mon_registro',$mon_registroigual, 'after');
			if ( $mon_registromayor != '')
				$crud->where('mon_registro >= ',$mon_registromayor);
			if ( $sessioncarga != '' )
				$crud->like('SUBSTRING(sessionficha, LOCATE(\'.\', sessionficha)+1 )',$sessioncarga );
		//	$crud->where($tablaregistros.'.cod_categoria > ', '399');
		//	$crud->where($tablaregistros.'.cod_categoria < ', '990');
		$crud->set_subject('Gasto');
		$crud
			 ->display_as('cod_registro','Codigo')
			 ->display_as('cod_entidad','Entidad')
			 ->display_as('cod_categoria','Categoria')
			 ->display_as('cod_subcategoria','Subcategoria')
			 ->display_as('mon_registro','Monto')
			 ->display_as('des_concepto','Concepto')
			 ->display_as('des_detalle','Detalles')
			 ->display_as('des_estado','Justificacion')
			 ->display_as('tipo_concepto','Tipo')
			 ->display_as('fecha_concepto','Fecha<br>Gastado')
			 ->display_as('fecha_registro','Fecha<br>Ingresado')
			 ->display_as('factura_tipo','Factura<br>Tipo')
			 ->display_as('factura_num','Factura<br>Numero')
			 ->display_as('factura_rif','Factura<br>Rif')
			 ->display_as('factura_bin','Factura<br>Escaneada')
			 ->display_as('sessionflag','Alterador')
			 ->display_as('sessionficha','Creador');
		$crud->columns('fecha_concepto','fecha_registro','cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','estado','des_estado','tipo_concepto','factura_tipo','factura_num','factura_rif','factura_bin','cod_registro','fecha_registro','sessionficha','sessionflag');
		$crud->add_fields('fecha_registro','fecha_concepto','cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','estado','tipo_concepto','factura_tipo','factura_num','factura_rif','factura_bin','cod_registro','sessionficha');
		$crud->edit_fields('fecha_registro','fecha_concepto','cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','estado','des_estado','tipo_concepto','factura_tipo','factura_num','factura_rif','factura_bin','cod_registro','sessionflag');
		$crud->set_relation('cod_entidad','entidad','{des_entidad} - {cod_entidad}'); //,'{des_entidad}<br> ({cod_entidad})'
		$crud->set_relation('cod_categoria','categoria','{des_categoria}'); // ,'{des_categoria}<br> ({cod_categoria})'
		$crud->set_relation('cod_subcategoria','subcategoria','{des_subcategoria}'); // ,'{des_subcategoria}<br> ({cod_subcategoria})'
		$crud->add_action('Auditar', '', '','ui-icon-plus',array($this,'_cargargastosucursalauditar'));
		$crud->required_fields('cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','tipo_concepto','des_estado');
		$directoriofacturas = 'archivoscargas';
		if ( ! is_dir($directoriofacturas) )
		{
			if ( is_file($directoriofacturas) )
			{	unlink($directoriofacturas);	}
			mkdir($directoriofacturas . 'GAS'.date('Y'), 0777, true);
			chmod($directoriofacturas,0777);
		}
		$urlsegmentos = $this->uri->segment_array();
		$data['rutas'] = $urlsegmentos;
		$crud->set_field_upload('factura_bin',$directoriofacturas);
		$crud->set_rules('des_concepto', 'Concepto', 'trim|alphanumeric');
		$crud->set_rules('mon_registro', 'Monto', 'trim|decimal');
		$crud->set_rules('cod_entidad', 'Centro de Costo', 'trim|alphanumeric');
		$crud->field_type('sessionficha', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		$crud->field_type('sessionflag', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		$crud->field_type('fecha_registro', 'invisible',''.date("Ymd"));
		$crud->field_type('tipo_concepto','dropdown',array('SUCURSAL' => 'SUCURSAL', 'ADMINISTRATIVO' => 'ADMINISTRATIVO'));
		$crud->field_type('factura_tipo','dropdown',array('EGRESO' => 'EGRESO', 'CONTRIBUYENTE' => 'CONTRIBUYENTE'));
		$crud->field_type('des_detalle','text');
		$crud->field_type('des_estado','text');
		$crud->unset_texteditor('des_detalle');
		$crud->unset_texteditor('des_estado');
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->callback_add_field('cod_registro', function () {	return '<input type="text" maxlength="50" value="GAS'.date("YmdHis").'" name="cod_registro" readonly="true">';	});
			$crud->callback_add_field('fecha_concepto', function () {	$fecha_concepto=date('Ymd');	$idfeccon='fecha_concepto';	$valoresinputfechacon = array('name'=>$idfeccon,'id'=>$idfeccon, 'onclick'=>'javascript:NewCssCal(\''.$idfeccon.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfeccon, $$idfeccon));	return form_input($valoresinputfechacon);	});
			$crud->field_type('estado','dropdown',array('APROBADO' => 'APROBADO', 'PENDIENTE' => 'PENDIENTE', 'RECHAZADO' => 'RECHAZADO'));
			$crud->required_fields('cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','tipo_concepto','factura_tipo');
		}
		else if ($currentState == 'edit')
		{
			$crud->field_type('fecha_registro', 'readonly');
			$crud->field_type('cod_registro', 'readonly'); // esto no se puede si ya se hizo algo antes
			$crud->callback_edit_field('fecha_concepto',array($this,'_editarfechagasto'));
			$crud->field_type('estado','dropdown',array('APROBADO' => 'APROBADO', 'PENDIENTE' => 'PENDIENTE', 'RECHAZADO' => 'RECHAZADO', 'ERROR' => 'ERROR'));
			$crud->required_fields('cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','tipo_concepto','factura_tipo','des_estado');
		}
		$crud->callback_column('mon_registro',array($this,'_numerosgente'));
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->callback_before_insert(array($this,'generarcodigo'));
		$crud->callback_before_delete(array($this,'echapajaborrando'));
		$crud->callback_after_insert(array($this,'_manejarfileinserts'));
		$crud->callback_after_update(array($this,'_manejarfileuploads'));
		//$crud->callback_column('sessionflag',array($this,'_callback_verusuario'));
		//$crud->callback_column('sessionficha',array($this,'_callback_verusuario'));

		$this->load->library('gc_dependent_select');
		$configfielsjoin = array(
			'cod_categoria' => array('table_name' => 'categoria','title' => 'des_categoria','relate' => null), // categoria es sin relacion
			'cod_subcategoria' => array('table_name'=>'subcategoria','title'=>'des_subcategoria','id_field'=>'cod_subcategoria','relate' => 'cod_categoria','data-placeholder' => 'Seleccione primero categoria')
			);
		$configtablejoin = array(
			'main_table' => 'registro_gastos',
			'main_table_primary' => 'cod_registro',
			'url' => site_url() . strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__) . '/'	//'ajax_loader' => base_url() . 'style/images/'. 'ajax-loader.gif'//'segment_name' =>'Your_segment_name' // It's an optional parameter. by default "get_items"
		);
		$outputjoincatysubcat = new gc_dependent_select($crud, $configfielsjoin, $configtablejoin);
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url("/cargargastosucursalesadm/gastosucursalesrevisarlos/list/?sessionficha=".date("YmdH").'....'.$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username').""));
		$output = $crud->render();
		//else if ($currentState == 'edit')
		$output->output.= $outputjoincatysubcat->get_js();
		// TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'cargardatosadminnistrativosfiltrados';
		$this->load->view('header.php',$data);
		$this->load->view('cargargastoadministrativo.php',$output);
		$this->load->view('footer.php',$data);
	}

	public function _numerosgente($value, $row)
	{
		$formateado = number_format($row->mon_registro, 2, ',', '.');
		return $formateado;
	}

	function _editarfechagasto($value, $primary_key)
	{
		$fecha_concepto=$value;
		$idfeccon='fecha_concepto';	$valoresinputfechacon = array('name'=>$idfeccon,'id'=>$idfeccon, 'onclick'=>'javascript:NewCssCal(\''.$idfeccon.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfeccon, $$idfeccon));
		return form_input($valoresinputfechacon);
	}

	/* llamar preupdate antes actualizar adiciona quien esta realizando la actualizacion */
	function echapajacuando($post_array, $primary_key)
	{
		$operacion = $this->session->userdata('username').' cambio, datos viejos de ' . $primary_key . ': '.$post_array['des_concepto'].'-'.$post_array['cod_entidad'].'-'.$post_array['mon_registro'].'-'.$post_array['cod_categoria'].'-'.$post_array['cod_subcategoria'];
		$sessionflag = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		$post_array['sessionflag'] = $sessionflag;
		$this->db->insert('log',array('cod_log' => date('YmdHis'), 'operacion' => $operacion, 'sessionficha' => $sessionflag));
		log_message('info', $operacion . ' por  ' . $sessionflag );
		return $post_array;
	}

	/* si se borra echa paja cuando */
	function echapajaborrando($primary_key)
	{
		$results = $this->db->query("SELECT * FROM registro_gastos WHERE cod_registro = '".$primary_key."'");
		$stringlog =  '>>>';
			foreach ($results->result() as $row)
			{
				$stringlog .= $row->cod_entidad.'-'.$row->cod_categoria.'-'.$row->cod_subcategoria.'-'.$row->mon_registro.''.'-'.$row->des_concepto.'-'.$row->sessionflag.'-'.$row->sessionficha.'';
			}
		$stringlog .= '<<<';
		$operacion = $this->session->userdata('username').' borrando el gasto ' . $primary_key . ':'.$stringlog;
		$sessionficha = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		$this->db->insert('log',array('cod_log' => date('YmdHis'), 'operacion' => $operacion, 'sessionficha' => $sessionficha));
		log_message('info', $operacion . ' en  ' . $sessionficha);
		return ;
	}

	/* antes de cada insercion se autogenera el codigo de nuevo, ruta archivo, y a que hora lo hace, por si tardo mucho rellenando los datos se genera con hora exacta */
	function generarcodigo($post_array)
	{
		$fec_registro=date('Ymd');
		$cod_registronuevo = 'GAS'.date("YmdHis");
		$operacion = $this->session->userdata('username').' creando gasto nuevo ' . $cod_registronuevo . ' descripcion: ' . $post_array['des_concepto'] ;
		$sessionficha = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		$post_array['cod_registro'] = $cod_registronuevo;
		$post_array['fecha_registro'] = $fec_registro;
		$post_array['sessionficha'] = $sessionficha;
		$this->db->insert('log',array('cod_log' => date('YmdHis'), 'operacion' => $operacion, 'sessionficha' => $sessionficha));
		log_message('info', $operacion . ' en  ' . $sessionficha);
		return $post_array;
	}

	function _manejarfileinserts($post_array,$primary_key)
	{
		$filesub = 'GAS'.date('Y').'/';
		$fileold = $post_array['factura_bin'];
		$filenew = $filesub.$post_array['factura_bin'];
		if (stripos($fileold, $filesub) !== FALSE)
			return TRUE;	// la unica manera que fileold ya tenga el subdir es que no lo hayan alterado ademas el archivo no tendra "/" en su nombre
		$directoriofacturas = 'archivoscargas/';
		rename($directoriofacturas . $fileold, $directoriofacturas . $filenew);
		$cod_registro = $post_array['cod_registro'];
		$sqlupload = array('cod_registro' => $cod_registro, 'factura_bin' => $filenew);
		$sqlwhere = array('cod_registro' => $cod_registro);
		$this->db->update('registro_gastos',$sqlupload,$sqlwhere);
		$sessionficha = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		$post_array['factura_bin'] = $filenew;
		$operacion = $this->session->userdata('username').' ajustando archivo editado en directorio ' .$filenew . ' de '.$fileold ;
		log_message('info', $operacion . ' en  ' . $sessionficha);
		return $sqlresult;
	}

	function _manejarfileuploads($post_array,$primary_key)
	{
		$filesub = 'GAS'.date('Y') . '/';
		$fileold = $post_array['factura_bin'];
		$filenew = $filesub.$post_array['factura_bin'];
		if (stripos($fileold, $filesub) !== FALSE)
			return TRUE;	// la unica manera que fileold ya tenga el subdir es que no lo hayan alterado ademas el archivo no tendra "/" en su nombre
		$directoriofacturas = 'archivoscargas/';
		rename($directoriofacturas . $fileold, $directoriofacturas . $filenew);
		$cod_registro = $primary_key;
		$sqlupload = array('cod_registro' => $cod_registro, 'factura_bin' => $filenew);
		$sqlwhere = array('cod_registro' => $cod_registro);
		$this->db->update('registro_gastos',$sqlupload,$sqlwhere);
		$sessionficha = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		$post_array['factura_bin'] = $filenew;
		$operacion = $this->session->userdata('username').' ajustando archivo editado en directorio ' .$filenew . ' de '.$fileold ;
		log_message('info', $operacion . ' en  ' . $sessionficha);
		return TRUE;
	}

	function _cargargastosucursalauditar($primary_key, $row)
	{
		$enlace = site_url('cargargastosucursalesadm/auditar/'.$row->cod_registro).'?cod_registro='.$row->cod_registro;
		return "javascript:void(window.open ('".$enlace."','NOtificador','menubar=1,resizable=1,width=650,height=450'));";
	}

}
