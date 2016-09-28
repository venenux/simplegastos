<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Indexcontroler extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->model('menu');
		 //el profiler esta dañado.. debido a una mala coarga de arreglos para los de idiomas
		$this->output->enable_profiler(TRUE);
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/indexcontroler
	 *	- or -  
	 * 		http://example.com/index.php/indexcontroler/index
	 *	- or -
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/indexcontroler/<method_name>
	 * @see /user_guide/general/urls.html
	 */
	public function index()
	{
		$data['menu'] = $this->menu->general_menu();
		if( $this->session->userdata('logueado') == TRUE)
		{
		// TODO agregar en cada archivo esta linea if($this->session->userdata('logueado')) y verifica sesion
			$data['username'] = $this->session->userdata('username');
			$data['nombre'] = $this->session->userdata('nombre');
			$data['correo'] = $this->session->userdata('correo');
			$data['logueado'] = $this->session->userdata('logueado');
			$data['accionpagina']='logueado';
			$this->load->library('table');
			$this->load->helper(array('form', 'url','html'));
			$tmplnewtable = array ( 'table_open'  => '<table border="0" cellpadding="1" cellspacing="1" class="table">' );
			$this->table->set_caption(NULL);
			$this->table->clear();
			$this->table->set_template($tmplnewtable);
			$this->table->add_row('Bienvenido', $data['nombre'], '');
			$this->table->add_row('Correo', $data['correo'], '');
			$this->table->add_row('Centro de costo', $this->session->userdata('codger'), '');
			$this->table->add_row('Ubicacion', $this->session->userdata('cod_entidad'), '');
			$data['presentar']=$this->table->generate();
			$data['menu'] = $this->menu->general_menu();
			$this->load->view('header.php',$data);
			$this->load->view('manejousuarios', $data);
			$this->load->view('footer.php',$data);
		}
		else
		{
			if( $this->session->userdata('logueado') == FALSE)
			{
				redirect('manejousuarios/desverificarintranet');
			}
		}
	}

	public function otrafuncion()
	{
		$data['menu'] = $this->menu->general_menu();
			$this->load->view('header.php',$data);
			$this->load->view('inicion.php',$data);
			$this->load->view('footer.php',$data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
