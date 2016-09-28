<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
	
class Manejousuarios extends CI_Controller 
{
	protected $dbxmppusers = null;
	private $usuariologin, $sessionflag, $acc_lectura, $acc_escribe, $acc_modifi;

	public function __construct() 
	{
		parent::__construct();
		//$this->dbxmppusers = $this->load->database('simplexmpp', true);
		$this->load->database('gastossystema');
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->model('menu');
		$this->output->enable_profiler(TRUE);
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
		if ( !$this->input->post() || $this->input->post('nombre') != '') 
		{
			$this->index();
		}
		$nombre = $this->input->post('nombre');
		$contrasena = $this->input->post('contrasena');
		//$this->load->model('manejousuarios');
		$sqlusuario = "SELECT 
  count(`usu`.`intranet`) as cuantos,
 `usu`.`ficha`,                             -- ficha en nomina, cedula en vnzl
  ifnull(`usu`.`intranet`,'') as intranet,  -- solo entran quienes tengan intranet
  ifnull(`usu`.`intranet`,'') as username,  -- solo entran quienes tengan intranet
  `usu`.`clave`, `usu`.`nombre`,            -- como se llama y su apellido
  `usu`.`codger`,                           -- ubicacion segun la nomina, pues es por centro de costos
  `suc`.`cod_sucursal` as `cod_entidad`,    -- entidad sucursal en donde puede operar 
  `usu`.`estado`,                           -- solo entran quienes puedan ver gastos
  ifnull(`usu`.`sessionflag`,'') as sessionflag, 
  `usu`.`acc_lectura`, `usu`.`acc_escribe`, `usu`.`acc_modifi`, 
  `usu`.`fecha_ficha`, `usu`.`fecha_ultimavez`         -- ultima vez el usuario salio de sesion: YYYYMMDDhhmmss  
FROM 
 `usuarios` as `usu`
LEFT join
 `sucursal_usuario` as `suc` ON `suc`.`cod_usuario` = `usu`.`ficha`
WHERE 
  ifnull(`usu`.`intranet`,'') <> ''         -- solo entran quienes tengan intranet
AND
  (`usu`.`clave` = '".$contrasena."' AND ifnull(`usu`.`intranet`,'') = '".$nombre."')     -- aunque solo accede si tiene intranet, puede usar su cedula
  OR 
  (`usu`.`clave` = '".$contrasena."' AND ifnull(`usu`.`ficha`,'') = '".$nombre."')  ";
		//$query = $this->dbxmppusers->query($sqlusuario);
		$query = $this->db->query($sqlusuario);
		$objetousuario = $query->result();
		if ($objetousuario) 
		{
			foreach( $objetousuario as $rowuser )
			{
				$usuario_data = array(
					'ficha' => $rowuser->ficha,
					'intranet' => $rowuser->intranet,
					'username' => $rowuser->username,
					'nombre' => $rowuser->nombre,
					'codger' => $rowuser->codger,
					'cod_entidad' => $rowuser->cod_entidad,
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
				{
					$usuario_data['logueado'] = FALSE;
					$data['accionpagina']='deslogeado';
				}
				$this->session->set_userdata($usuario_data);
			}
		}
		if( $this->session->userdata('logueado') == TRUE && $this->session->userdata('logueado') != '')
		{
			// TODO agregar en cada archivo esta linea if($this->session->userdata('logueado')) y verifica sesion
			$data['username'] = $this->session->userdata('username');
			$data['nombre'] = $this->session->userdata('nombre');
			$data['correo'] = $this->session->userdata('correo');
			$data['logueado'] = $this->session->userdata('logueado');
			$data['accionpagina']='logeado';
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
