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
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		if( $this->session->userdata('logueado') == FALSE)
			redirect('manejousuarios/desverificarintranet');
		if ($usuariocodgernow < 990 and $usuariocodgernow > 399 )
			redirect('cargargastomanual/gastomanualrevisarlos');
		$userdata = $this->session->all_userdata();
		$data['logueado'] = $this->session->userdata('logueado');
		$data['menu'] = $this->menu->general_menu();
		$data['admvistaurlaccion'] = 'admcategoriasconceptos';
		$data['advertenciaformato'] = "Vista general, para editar o agregar nuevas, debe usar los botones, abajo se lista las subcategorias.";
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
		$output = "<h4>Categorias</h4>".$output1->output."<h4>Subcategorias</h4>".$output2->output."";

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
		$crud->set_theme('flexigrid'); // flexigrid tiene bugs en varias cosas
		$crud->columns('des_categoria','fecha_categoria','cod_categoria','sessionflag');
		$crud->display_as('cod_categoria','Codigo')
			 ->display_as('des_categoria','Categoria')
			 ->display_as('fecha_categoria','Creado')
			 ->display_as('sessionflag','Modificado');
		$crud->set_subject('Categorias');
		$crud->field_type('des_categoria', 'text');
		$crud->unset_texteditor('des_categoria');
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
		$crud->field_type('des_subcategoria', 'text');
		$crud->unset_texteditor('des_subcategoria');
		$crud->set_relation('cod_categoria','categoria','{des_categoria}');
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

}
