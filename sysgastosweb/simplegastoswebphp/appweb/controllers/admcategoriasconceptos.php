<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admcategoriasconceptos extends CI_Controller {

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
		$this->_verificarsesion();
		$data['username'] = $this->session->userdata('username');
		$data['nombre'] = $this->session->userdata('nombre');
		$data['correo'] = $this->session->userdata('correo');
		$data['logueado'] = $this->session->userdata('logueado');
		$data['menu'] = $this->menu->general_menu();
		$data['admvistaurlaccion'] = 'admcategoriasconceptos';
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
		$this->config->load('grocery_crud');
		$this->config->set_item('grocery_crud_dialog_forms',true);
		$this->config->set_item('grocery_crud_default_per_page',10);

		$output1 = $this->admcategorias();
		$output2 = $this->admsubcategorias();

		$js_files = $output1->js_files + $output2->js_files;
		$css_files = $output1->css_files + $output2->css_files;
		$output = ""
		."<h3>Categorias</h3>".$output1->output
		."<h3>Subcategorias</h3>".$output2->output
		."";

		$this->_esputereport((object)array(
				'js_files' => $js_files,
				'css_files' => $css_files,
				'output'	=> $output
		));
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
			$crud->field_type('cod_categoria', 'readonly');
			$crud->set_rules('des_categoria', 'Descripcion', 'trim|alphanumeric');
		}
		$crud->callback_before_insert(array($this,'datospostinsertcat'));
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->unset_add();
		$crud->unset_edit();
		$crud->unset_delete();
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url("/admcategoriasconceptos"));
		$output = $crud->render();
		if($crud->getState() != 'list') {
			$this->_esputereport($output);
		} else {
			return $output;
		}
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
		$crud->unset_add();
		$crud->unset_edit();
		$crud->unset_delete();
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url("/admcategoriasconceptos"));
		$output = $crud->render();
		if($crud->getState() != 'list') {
			$this->_esputereport($output);
		} else {
			return $output;
		}
	}

	function datospostinsertcat($post_array)
	{
		$post_array['cod_categoria'] = 'CAT'.date("YmdHis");
		$post_array['fecha_categoria'] = date("Ymd");
		// TODO: insert para tabla log
		return $post_array;
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
