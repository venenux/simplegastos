<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admentidades extends CI_Controller {

	private static $modulosadm = array('admusuariosentidad','admcategoriasconceptos','admgastoslog');

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
		$data['logueado'] = $this->session->userdata('logueado');
		$data['menu'] = $this->menu->general_menu();
		$data['advertenciaformato'] = "DEBE AUTORIZAR LOS USUARIOS, ASOCIELES UN CENTRO DE COSTO, la clave ellos la deben cambiar en la intranet.";
		$data['admvistaurlaccion'] = 'admusuariosentidad';
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
		$this->admsucursalesyusuarios();
	}

	public function admsucursalesyusuarios()
	{
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		if( $this->session->userdata('logueado') == FALSE)
			redirect('manejousuarios/desverificarintranet');
		if ($usuariocodgernow < 990 and $usuariocodgernow > 399 )
			redirect('cargargastomanual/gastomanualrevisarlos');
		$userdata = $this->session->all_userdata();
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->unset_export();
		$crud->set_table('entidad');
		$crud->set_subject('Sucursal');
		$crud->set_relation_n_n('nam_usuario', 'entidad_usuario', 'usuarios', 'cod_entidad', 'intranet', 'nombre');
		$crud->set_relation('cod_fondo','fondo','{mon_fondo} ({fecha_fondo})');
		$crud->columns('abr_entidad','abr_zona','cod_entidad','des_entidad','status','cod_fondo','nam_usuario','sello','sessionflag');
		$crud->display_as('cod_entidad','Cod. Centro')
			 ->display_as('abr_entidad','Cod. Siglas')
			 ->display_as('abr_zona','Cod. Zona')
			 ->display_as('des_entidad','Nombre')
			 ->display_as('cod_fondo','Fondo')
			 ->display_as('sello','Sello')
			 ->display_as('status','Estado')
			 ->display_as('nam_usuario','Asociados')
			 ->display_as('sessionflag','Modificado');
		$crud->unset_add_fields('sessionflag','nam_usuario'); // TODO: bug no asocia usuario en crear
		$crud->unset_export();
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->required_fields('cod_entidad','abr_entidad','abr_zona','des_entidad','status');
			$crud->set_rules('cod_entidad', 'Centro de Costo (codger)', 'trim|numeric');
		}
		else if ($currentState == 'edit')
		{
			$crud->required_fields('abr_entidad','abr_zona','des_entidad','status');
			$crud->field_type('cod_entidad', 'readonly');
			$crud->field_type('sessionflag', 'readonly');
		}
		$crud->set_rules('abr_entidad', 'Siglas', 'trim|alphanumeric');
		$crud->set_rules('abr_zona', 'Zona', 'trim|alphanumeric');
		$crud->set_rules('des_entidad', 'Nombre', 'trim|alphanumeric');
		$crud->field_type('status','dropdown',array('ACTIVO' => 'ACTIVO', 'INACTIVO' => 'INACTIVO', 'CERRADO' => 'CERRADO', 'ESPECIAL' => 'ESPECIAL'));
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url("/admusuariosentidad"));
		$output = $crud->render();
		$this->_esputereport($output);
	}

	function echapajacuando($post_array, $primary_key)
	{
		$post_array['sessionflag'] = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		// TODO: insert para tabla log
		return $post_array;
	}

}
