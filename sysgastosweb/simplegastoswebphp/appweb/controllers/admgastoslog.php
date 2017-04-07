<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admgastoslog extends CI_Controller {

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

	/**
	 * Index Page cuando se invoca la url de este controlador,
	 * aqui se invoca la vista o otro metodo que la invoque
	 * map to /index.php/admgastoslog/index
	 */
	public function index()
	{
		$this->seccionlog();
	}

	public function seccionlog()
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

		$this->load->helper(array('inflector','url'));
		$this->load->database('gastossystema');
		$this->load->library('grocery_CRUD');
		$this->config->load('grocery_crud');
		$this->config->set_item('grocery_crud_dialog_forms',true);
		$this->config->set_item('grocery_crud_default_per_page',80);
		$crud = new grocery_CRUD();
	    $crud->set_table('log');
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->columns('cod_log','operacion','sessionficha');
		$crud
			 ->display_as('cod_log','Cuando')
			 ->display_as('operacion','Operaciones')
			 ->display_as('sessionficha','Quien');
		$crud->unset_add();
		$crud->unset_edit();
		$crud->unset_delete();
		$crud->field_type('operacion','text');
		$this->load->helper(array('form', 'url','inflector'));
		$output = $crud->render();
		$data['menu'] = $this->menu->menudesktop();
		$this->load->view('header.php',$data);
		$this->load->view('admgastoslog.php',$output);
		$this->load->view('footer.php',$data);
	}

}
