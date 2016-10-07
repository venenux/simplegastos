<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cargargastover extends CI_Controller {

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
		$this->load->view('cargargastover.php',$data);
		$this->load->view('footer.php',$data);
	}

	public function gastoregistros()
	{
		if( $this->session->userdata('logueado') == FALSE)
		{
			redirect('manejousuarios/desverificarintranet');
		}
		$userdata = $this->session->all_userdata();
		$usercorreo = $userdata['correo'];
		$userintran = $userdata['intranet'];
		$userpermacceso = $userdata['abr_entidad']; // TODO per,misos
		// OBTENER DATOS DE FORMULARIO ***************************** /
		$fec_registroini = $this->input->get_post('fec_registroini');
		$fec_registrofin = $this->input->get_post('fec_registrofin');
		$mon_registroigual = $this->input->get_post('mon_registroigual');
		$mon_registromayor = $this->input->get_post('mon_registromayor');
		$des_registrolike = $this->input->get_post('des_registrolike');
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_subcategoria = $this->input->get_post('cod_subcategoria');
		$this->load->helper(array('inflector','url'));
		// CONFIGURACION DE FILTROS SEGUN FORMULARIO PARA EL QUERY
        //if ( $this->uri->segment(1) != 'todos');
		//	$sqlreportegasto .= " and CONVERT(fecha_registro,UNSIGNED INTEGER) >= CONVERT('".date('Ymd',strtotime('-1 month'))."',UNSIGNED INTEGER) ";
		// filters if exist
		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
	    if ( $cod_entidad != ''){	$crud->where('registro_gastos.cod_entidad',$cod_entidad);	}
	    if ( $cod_subcategoria != ''){	$crud->where('registro_gastos.cod_subcategoria',$cod_subcategoria);	}
		if ( $des_registrolike != ''){	$crud->like('registro_gastos.des_registro',$des_registrolike);	}
		if ( $cod_subcategoria != ''){	$crud->where('registro_gastos.cod_subcategoria',$cod_subcategoria);	}
		if ( $fec_registroini != ''){	$crud->where('CONVERT(fecha_registro,UNSIGNED INTEGER) >=',$fec_registroini);	}
        if ( $fec_registrofin != ''){	$crud->where('CONVERT(fecha_registro,UNSIGNED INTEGER) <=',$fec_registrofin);	}
        if ( $mon_registroigual != ''){	$crud->where('mon_registro >=',$mon_registroigual);	}
        if ( $mon_registromayor != ''){	$crud->where('mon_registro >=',$mon_registromayor);	}
		$crud->set_table('registro_gastos');
		$crud->columns('fecha_registro','cod_entidad','cod_categoria','cod_subcategoria','des_registro','mon_registro','hex_factura','fecha_factura','sessionflag','cod_registro');
		$crud->add_fields('fecha_registro','cod_entidad','cod_categoria','cod_subcategoria','des_registro','mon_registro','hex_factura','fecha_factura','cod_registro');
		$crud->edit_fields('fecha_registro','cod_entidad','cod_categoria','cod_subcategoria','des_registro','mon_registro','hex_factura','fecha_factura','sessionflag','cod_registro');
		$crud->set_relation('cod_entidad','entidad','{des_entidad}'); //,'{des_entidad}<br> ({cod_entidad})'
		$crud->set_relation('cod_categoria','categoria','des_categoria'); // ,'{des_categoria}<br> ({cod_categoria})'
		$crud->set_relation('cod_subcategoria','subcategoria','des_subcategoria'); // ,'{des_subcategoria}<br> ({cod_subcategoria})'

		$crud->set_subject('Registro');
		$crud
			 ->display_as('cod_registro','Codigo')
			 ->display_as('cod_entidad','Centro')
			 ->display_as('cod_categoria','Categoria')
			 ->display_as('cod_subcategoria','Subcategoria')
			 ->display_as('des_registro','Concepto')
			 ->display_as('mon_registro','Monto')
			 ->display_as('fecha_registro','Cuando')
			 ->display_as('num_factura','Factura<br>Numero')
			 ->display_as('fecha_factura','Factura<br>Fecha')
			 ->display_as('hex_factura','Factura<br>Escaneada')
			 ->display_as('sessionflag','Modificado');

		$crud->unset_delete();
		$crud->required_fields('des_registro','mon_registro','estado');
		$crud->set_field_upload('hex_factura','appweb/archivoscargas');
		//$crud->field_type('cod_registro', 'readonly'); // esto no se puede si ya se hizo algo antes
		$crud->field_type('estado','dropdown',array('APROBADO' => 'APROBADO', 'PENDIENTE' => 'PENDIENTE', 'RECHAZADO' => 'RECHAZADO'));
		$crud->set_rules('des_registro', 'Concepto', 'trim|required|alphanumeric');
		$crud->set_rules('mon_registro', 'Monto', 'trim|required|decimal');

		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->required_fields('des_registro','mon_registro','estado','cod_entidad','cod_registro','cod_categoria','cod_subcategoria');
			$crud->set_rules('cod_entidad', 'Centro de Costo', 'trim|alphanumeric');
			$crud->callback_add_field('cod_registro', function () {	return '<input type="text" maxlength="50" value="GAS'.date("YmdHis").'" name="cod_registro" readonly="true">';	});
			$crud->callback_add_field('fecha_registro', function () {	$fecha_registro=date('Ymd');	$idfecreg='fecha_registro';	$valoresinputfechareg = array('name'=>$idfecreg,'id'=>$idfecreg, 'onclick'=>'javascript:NewCssCal(\''.$idfecreg.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecreg, $$idfecreg));	return form_input($valoresinputfechareg);	});
			$crud->callback_add_field('fecha_factura', function () {	$fecha_factura=date('Ymd');	$idfecfac='fecha_factura';	$valoresinputfechafac = array('name'=>$idfecfac,'id'=>$idfecfac, 'onclick'=>'javascript:NewCssCal(\''.$idfecfac.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecfac, $$idfecfac));	return form_input($valoresinputfechafac);	});
		}
		else if ($currentState == 'edit')
		{
			$crud->field_type('cod_registro', 'readonly'); // esto no se puede si ya se hizo algo antes
			//$crud->field_type('cod_entidad', 'readonly');
			//$crud->field_type('cod_categoria', 'readonly');
			//$crud->field_type('cod_subcategoria', 'readonly');
			$crud->field_type('fecha_registro', 'readonly');
			$crud->set_rules('sessionflag', 'Su id se registrara <br>como el que modifica');
			$crud->field_type('sessionflag', 'readonly');
		}
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->callback_before_insert(array($this,'generarcodigo'));
		$crud->callback_column('sessionflag',array($this,'_callback_verusuario'));

		$this->load->library('gc_dependent_select');
		$configfielsjoin = array(
			'cod_categoria' => array('table_name' => 'categoria','title' => 'des_categoria','relate' => null), // categoria es sin relacion
			'cod_subcategoria' => array('table_name'=>'subcategoria','title'=>'des_subcategoria','id_field'=>'cod_subcategoria','relate' => 'cod_categoria','data-placeholder' => 'Seleccione primero categoria')
			);
		$configtablejoin = array(
			'main_table' => 'registro_gastos',
			'main_table_primary' => 'cod_registro',
			'url' => base_url() . 'index.php/' . strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__) . '/'	//'ajax_loader' => base_url() . 'style/images/'. 'ajax-loader.gif'//'segment_name' =>'Your_segment_name' // It's an optional parameter. by default "get_items"
		);
		$categoriasysubcategorias = new gc_dependent_select($crud, $configfielsjoin, $configtablejoin);
		$output = $crud->render();
		$output->output.= $categoriasysubcategorias->get_js();
		// TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'cargardatosfiltrados';
		$this->load->view('header.php',$data);
		$this->load->view('cargargastover.php',$output);
		$this->load->view('footer.php',$data);
	}

	function _callback_verusuario($value, $row)
	{
		$sqlquien = "select ficha from usuarios where intranet = '".substr_replace($value,'', -14)."'";
		$sqlquienresult = $this->db->query($sqlquien);
			$ficha = '';
			foreach ($sqlquienresult->result() as $row)
			{
				$ficha = $row->ficha;
			}
		return "<a href='".site_url('admusuariosentidad/admusuariosavanzado/read/'.$ficha)."'>$value</a>";
	}

	function echapajacuando($post_array, $primary_key)
	{
		$post_array['sessionflag'] = $this->session->userdata('username').date("YmdHis");
		// TODO: insert para tabla log
		return $post_array;
	}

	function generarcodigo($post_array)
	{
		$post_array['cod_registro'] = 'GAS'.date("YmdHis");
		$post_array['fecha_registro'] = date_format(date_create($post_array['fecha_registro']),'Ymd');
		$fec_registro=date('Ymd');
		// TODO: insert para tabla log
		return $post_array;
	}
}
