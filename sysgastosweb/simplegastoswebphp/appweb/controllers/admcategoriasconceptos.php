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
		$data['output'] = $output; // TODO: output tiene mAs datos sacarlos y meterlos en $data hace funcionar todo normal
		$this->load->view('header.php',$data);
		$this->load->view('admvista.php',$output);
		$this->load->view('footer.php',$data);
	}

	public function products_management()
	{
			$crud = new grocery_CRUD();
			$crud->set_table('products');
			$crud->set_subject('Product');
			$crud->unset_columns('productDescription');
			$crud->callback_column('buyPrice',array($this,'valueToEuro'));
			$output = $crud->render();
			$this->_esputereport($output);
	}

	public function valueToEuro($value, $row)
	{
		return $value.' &euro;';
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
		."<h3>Conceptos</h3>".$output2->output
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
		$crud->columns('cod_categoria','des_categoria','fecha_categoria','sessionflag');
		$crud->display_as('cod_categoria','Codigo')
			 ->display_as('des_categoria','Categoria')
			 ->display_as('fecha_categoria','Creado')
			 ->display_as('sessionflag','Modificado');
		$crud->set_subject('Categorias');
		$crud->field_type('des_categoria', 'text');
		$crud->add_fields('cod_categoria','des_categoria','fecha_categoria');
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->set_rules('cod_categoria', 'Codigo', 'trim|required|alphanumeric');
			$crud->set_rules('des_categoria', 'Descripcion', 'trim|required|alphanumeric');
			$crud->set_rules('fecha_categoria', 'Creado', 'trim|required');
			$crud->callback_add_field('cod_categoria', function () {	return '<input type="text" maxlength="50" value="CAT'.date("YmdHis").'" name="cod_categoria" readonly="true">';	});
			$crud->callback_add_field('fecha_categoria', function () {	return '<input type="text" maxlength="50" value="'.date("Ymd").'" name="fecha_categoria" readonly="true">';	});
		}
		else if ($currentState == 'edit')
		{
			$crud->field_type('cod_categoria', 'readonly');
			$crud->set_rules('des_categoria', 'Descripcion', 'trim|required|alphanumeric');
			$crud->field_type('fecha_categoria', 'readonly');
			$crud->field_type('sessionflag', 'readonly');
		}
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url(strtolower(__CLASS__."/admcategoriasconceptos")));
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
		$crud->columns('cod_categoria','cod_subcategoria','des_subcategoria','fecha_subcategoria','sessionflag');
		$crud->display_as('cod_subcategoria','Codigo')
			 ->display_as('des_subcategoria','Concepto')
			 ->display_as('fecha_subcategoria','Creado')
			 ->display_as('sessionflag','Modificado');
		$crud->set_subject('Conceptos');
		$crud->display_as('cod_categoria','Categoria');
		$crud->set_relation('cod_categoria','categoria','{cod_categoria} - {des_categoria}');
		$crud->unset_add_fields('sessionflag');
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->set_rules('cod_subcategoria', 'Codigo', 'trim|required|alphanumeric');
			$crud->set_rules('des_subcategoria', 'Descripcion', 'trim|required|alphanumeric');
			$crud->set_rules('fecha_subcategoria', 'Creado', 'trim|required');
			$crud->callback_add_field('fecha_subcategoria', function () {	return '<input type="text" maxlength="50" value="'.date("YmdHis").'" name="fecha_subcategoria" readonly="true">';	});
			$crud->callback_add_field('cod_subcategoria', function () {	return '<input type="text" maxlength="50" value="SUB'.date("YmdHis").'" name="cod_subcategoria" readonly="true">';	});
			//$crud->callback_insert(array($this,'cod_subcategoria_categoria_insert_callback')); // no se pudo.. se inserta relacion normal joder
		}
		else if ($currentState == 'edit')
		{
			//$crud->field_type('cod_categoria', 'readonly');
			$crud->field_type('cod_subcategoria', 'readonly');
			$crud->set_rules('des_subcategoria', 'Descripcion', 'trim|required|alphanumeric');
			$crud->field_type('fecha_subcategoria', 'readonly');
			$crud->field_type('sessionflag', 'readonly');
		}
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url(strtolower(__CLASS__."/admcategoriasconceptos")));
		$output = $crud->render();
		if($crud->getState() != 'list') {
			$this->_esputereport($output);
		} else {
			return $output;
		}
	}

	function echapajacuando($post_array, $primary_key)
	{
		$post_array['sessionflag'] = $this->session->userdata('username').date("YmdHis");
		// TODO: insert para tabla log
		return $post_array;
	}

}
