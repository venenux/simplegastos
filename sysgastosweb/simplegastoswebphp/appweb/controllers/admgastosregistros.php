<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admgastosregistros extends CI_Controller {

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

	function index()
	{
		if( $this->session->userdata('logueado') == FALSE)

			redirect('manejousuarios/desverificarintranet');

		else
			redirect('cargargastover/gastovercustom/todos');
	}

}
