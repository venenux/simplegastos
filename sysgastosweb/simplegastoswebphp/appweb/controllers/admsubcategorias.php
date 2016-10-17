<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admsubcategorias extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('gastossystema');
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->model('menu');
		$this->load->library('grocery_CRUD');
		$this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT',TRUE);
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', TRUE);
		$this->output->set_header('Pragma: no-cache', TRUE);
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT", TRUE);
		$this->output->enable_profiler(TRUE);
	}

	public function _verificarsesion()
	{
		if( $this->session->userdata('logueado') != TRUE)
			redirect('manejousuarios/desverificarintranet');
	}

	public function _esputereport($output = null)
	{
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		if( $this->session->userdata('logueado') == FALSE)
			redirect('manejousuarios/desverificarintranet');
		if ($usuariocodgernow < 990 and $usuariocodgernow > 399 )
			redirect('cargargastomanual/gastomanualrevisarlos');
		$userdata = $this->session->all_userdata();
		$data['username'] = $this->session->userdata('username');
		$data['nombre'] = $this->session->userdata('nombre');
		$data['correo'] = $this->session->userdata('correo');
		$data['logueado'] = $this->session->userdata('logueado');
		$data['menu'] = $this->menu->general_menu();
		$data['admvistaurlaccion'] = 'admcategoriasconceptos';
		$data['advertenciaformato'] = "A DIFERENCIA DE LAS CATEGORIAS, <br>TODA SUBCATEGORIA ES MOSTRADA SI LA CATEGORIA A LA QUE PERTENECE NO ES ADMINISTRATIVA!";
		$data['js_files'] = $output->js_files;
		$data['css_files'] = $output->css_files;
		$data['output'] = $output->output;
		$this->load->view('header.php',$data);
		$this->load->view('admvista.php',$data);
		$this->load->view('footer.php',$data);
	}

	function index()
	{
		$this->_verificarsesion();
		$this->admsubcategorias();
	}


	public function admsubcategorias()
	{
		$crud = new grocery_CRUD();
		$crud->set_table('subcategoria');
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->columns('cod_categoria','des_subcategoria','fecha_subcategoria','cod_subcategoria','sessionflag');
		$crud->display_as('cod_subcategoria','Codigo')
			 ->display_as('cod_categoria','Categoria')
			 ->display_as('des_subcategoria','SubCategoria')
			 ->display_as('fecha_subcategoria','Creado')
			 ->display_as('sessionflag','Modificado');
		$crud->set_subject('Subcategorias');
		$crud->add_fields('cod_categoria','cod_subcategoria','des_subcategoria','fecha_subcategoria');
		$crud->edit_fields('cod_categoria','cod_subcategoria','des_subcategoria','sessionflag');
		$crud->field_type('sessionflag', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		$crud->field_type('fecha_subcategoria', 'invisible',''.date("Ymd"));
		$crud->field_type('des_subcategoria', 'text');
		$crud->unset_texteditor('des_subcategoria');
		$crud->unset_export();
		$crud->set_relation('cod_categoria','categoria','{des_categoria}');
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->required_fields('cod_categoria','des_subcategoria');
			$crud->set_rules('cod_subcategoria', 'Codigo', 'trim|alphanumeric');
			$crud->set_rules('des_subcategoria', 'Descripcion', 'trim|alphanumeric');
			$crud->callback_add_field('cod_subcategoria', function () {	return '<input type="text" maxlength="50" value="SUB'.date("YmdHis").'" name="cod_subcategoria" readonly="true">';	});
		}
		else if ($currentState == 'edit')
		{
			$crud->required_fields('cod_categoria','des_subcategoria');
			$crud->field_type('cod_subcategoria', 'readonly');
			$crud->set_rules('des_subcategoria', 'Descripcion', 'trim|alphanumeric');
		}
		$crud->callback_before_insert(array($this,'datospostinsertsub'));
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url("/admcategoriasconceptos")); // TODO usar en tablas temporales de crud tiendas, esta es la solcuion
		$output = $crud->render();
		$this->_esputereport($output);
	}

	function datospostinsertsub($post_array)
	{
		$post_array['cod_subcategoria'] = 'SUB'.date("YmdHis");
		$post_array['fecha_subcategoria'] = date("Ymd");
		// TODO: insert para tabla log
		return $post_array;
	}

	function echapajacuando($post_array, $primary_key)
	{
		$post_array['sessionflag'] = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		// TODO: insert para tabla log
		return $post_array;
	}

}
