<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admcategorias extends CI_Controller {

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
		$data['advertenciaformato'] = "RECUERDE SI AGREGA UNA CATEGORIA ADMINISTRATIVA EL FORMATO ES CAT2016000012XXXX <br>los ultimos 4 'X' no deben repetirse de otra categoria, y.. <br>UNA CATEGORIA NO PUEDE ESTAR HUERFANA, debe tener al menos una subcategoria!";
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
		$this->admcategorias();
	}


	public function admcategorias()
	{
		$crud = new grocery_CRUD();
		$crud->set_table('categoria');
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->columns('des_categoria','fecha_categoria','cod_categoria','sessionflag');
		$crud->display_as('cod_categoria','Codigo')
			 ->display_as('des_categoria','Categoria')
			 ->display_as('fecha_categoria','Creado')
			 ->display_as('sessionflag','Modificado');
		$crud->set_subject('Categorias');
		$crud->add_fields('cod_categoria','des_categoria','fecha_categoria');
		$crud->edit_fields('cod_categoria','des_categoria','sessionflag');
		$crud->field_type('sessionflag', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		$crud->field_type('fecha_categoria', 'invisible',''.date("Ymd"));
		$crud->field_type('des_categoria', 'text');
		$crud->unset_texteditor('des_categoria');
		$crud->unset_export();
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->required_fields('des_categoria');
			$crud->set_rules('des_categoria', 'Descripcion', 'trim|alphanumeric');
			$crud->callback_add_field('cod_categoria', function () {	return '<input type="text" maxlength="50" value="CAT'.date("YmdHis").'" name="cod_categoria" readonly="true">';	});
		}
		else if ($currentState == 'edit')
		{
			$crud->required_fields('des_categoria');
			//$crud->field_type('cod_categoria', 'readonly');
			$crud->set_rules('des_categoria', 'Descripcion', 'trim|alphanumeric');
		}
		$crud->callback_before_insert(array($this,'datospostinsertcat'));
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url("/admcategoriasconceptos"));
		$output = $crud->render();
		$this->_esputereport($output);
	}

	function datospostinsertcat($post_array)
	{
		//$post_array['cod_categoria'] = 'CAT'.date("YmdHis");
		$post_array['fecha_categoria'] = date("Ymd");
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
