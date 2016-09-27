<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
	
class Manejousuarios extends CI_Controller 
{
	protected $dbxmppusers = null;

	public function __construct() 
	{
		parent::__construct();
		$this->load->database('simplexmpp');
		//$this->dbxmppusers = $this->load->database('simplexmpp', true);
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->model('menu');
//		$this->output->enable_profiler(TRUE);
	}
	public function index() 
	{
		$data = array('logueado' => FALSE, 'accionpagina' => 'iniciar');
		$data['menu'] = $this->menu->general_menu();
		$data['accionpagina'] = 'deslogeado';
		$usuario_data = array('logueado' => FALSE);
		$this->session->set_userdata($usuario_data);
		$this->load->helper(array('form', 'url','html'));
		$this->load->view('header.php',$data);
		$this->load->view('manejousuarios', $data);
		$this->load->view('footer.php',$data);
	}
	
	public function verificarintranet() 
	{
		if ( ! $this->input->post()) 
		{
			$this->index();
		}
		$nombre = $this->input->post('nombre');
		$contrasena = $this->input->post('contrasena');
		//$this->load->model('manejousuarios');
		//$objetousuario = $this->manejousuarios->usuario_ejabberd($nombre, $contrasena);
		$sqlusuario = "select username,\"password\" as clave from users where username='".$nombre."' and \"password\"='".$contrasena."' ";
		//$query = $this->dbxmppusers->query($sqlusuario);
		$query = $this->db->query($sqlusuario);
		$objetousuario = $query->result();
		if ($objetousuario) 
		{
			foreach( $objetousuario as $rowuser )
			{
				$usuario_data = array(
					'username' => $rowuser->username,
					'correo' => $rowuser->username . '@intranet1.net.ve'
				);
				break;
			}
			if ( isset($usuario_data['username']) )
			{
				if ($usuario_data['username'] != '')
				{
					$usuario_data['logueado'] = TRUE;
					$data['accionpagina']='logeado';
				}
				else
					$usuario_data['logueado'] = FALSE;
				$this->session->set_userdata($usuario_data);
			}
			// TODO agregar en cada archivo esta linea if($this->session->userdata('logueado')) y verifica sesion
			$data['username'] = $this->session->userdata('username');
			$data['nombre'] = $this->session->userdata('username');
			$data['correo'] = $this->session->userdata('correo');
			$data['logueado'] = $this->session->userdata('logueado');
			$this->load->library('table');
			$this->load->helper(array('form', 'url','html'));
			$tmplnewtable = array ( 'table_open'  => '<table border="0" cellpadding="1" cellspacing="1" class="table">' );
			$this->table->set_caption(NULL);
			$this->table->clear();
			$this->table->set_template($tmplnewtable);
			$this->table->add_row('Bienvenido', $data['username'], '');
			$this->table->add_row('Correo', $data['correo'], '');
			$data['presentar']=$this->table->generate();
			$data['menu'] = $this->menu->general_menu();
			$this->load->view('header.php',$data);
			$this->load->view('manejousuarios', $data);
			$this->load->view('footer.php',$data);
		}
		else
			$this->desverificarintranet();
	}

	public function desverificarintranet() 
	{
		$data = array('logueado' => FALSE, 'accionpagina' => 'deslogeado');
		$usuario_data = array('logueado' => FALSE);
		$this->session->set_userdata($usuario_data);
		$this->index();
	}
}
