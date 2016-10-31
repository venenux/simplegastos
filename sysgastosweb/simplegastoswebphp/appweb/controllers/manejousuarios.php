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
		$this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT',TRUE);
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', TRUE);
		$this->output->set_header('Pragma: no-cache', TRUE);
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT", TRUE);
		$this->output->enable_profiler(TRUE);
	}


	public function index()
	{
		$data = array('logueado' => FALSE, 'accionpagina' => 'iniciar');
		$data['menu'] = $this->menu->general_menu();
		$data['accionpagina'] = 'deslogueado';
		$usuario_data = null;
		$usuario_data = array('logueado' => FALSE);
		$this->session->set_userdata($usuario_data);
		$this->load->helper(array('form', 'url','html'));
		$this->load->view('header.php',$data);
		$this->load->view('manejousuarios', $data);
		$this->load->view('footer.php',$data);
	}


	public function verificarintranet()
	{
		if ( ! $this->input->post() )
		{
			$this->index();
		}
		$nombre = $this->input->post('nombre');
		$contrasena = $this->input->post('contrasena');
		//$this->load->model('manejousuarios');
		$sqlusuario = "SELECT
		  count(`usu`.`intranet`) as cuantos,
		 `usu`.`ficha`,                             /* ficha en nomina, cedula en vnzl */
		  ifnull(`usu`.`intranet`,'') as intranet,  /* solo entran quienes tengan intranet */
		  ifnull(`usu`.`intranet`,'') as username,  /* solo entran quienes tengan intranet */
		  `usu`.`clave`, `usu`.`nombre`,            /* como se llama y su apellido */
		  `usu`.`sello`,                           /* ubicacion segun la nomina, pues es por centro de costos */
		  `suc`.`cod_entidad`,                      /* entidad sucursal en donde puede operar */
		  `usu`.`estado`,                           /* solo entran quienes puedan ver gastos */
		  `usu`.`cod_fondo`,
		  `ent`.`abr_entidad`,
		  `ent`.`abr_zona`,
		  `ent`.`des_entidad`,
		  ifnull(`usu`.`sessionflag`,'') as sessionflag,
		  `usu`.`acc_lectura`, `usu`.`acc_escribe`, `usu`.`acc_modifi`,
		  `usu`.`sessionficha` as fecha_ficha, `usu`.`fecha_ultimavez`
		FROM
		 `usuarios` as `usu`
		LEFT join
		 `entidad_usuario` as `suc` ON `suc`.`intranet` = `usu`.`intranet`
		LEFT JOIN
		 `entidad` as `ent` ON `ent`.`cod_entidad` = `suc`.`cod_entidad`
		WHERE
		  ifnull(`usu`.`intranet`,'') <> ''
		AND
		  (
			(`usu`.`clave` = md5('".$this->db->escape_str($contrasena)."') AND `usu`.`intranet` = '".$this->db->escape_str($nombre)."')
			  OR
			(`usu`.`clave` = md5('".$this->db->escape_str($contrasena)."') AND `usu`.`ficha` = '".$this->db->escape_str($nombre)."')
		  )
		AND
		  ( usu.estado = 'ACTIVO' OR usu.estado = 'activo')
		 ";
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
					'sello' => $rowuser->sello,
					'cod_entidad' => $rowuser->cod_entidad,
					'abr_entidad' => $rowuser->abr_entidad,
					'abr_zona' => $rowuser->abr_zona,
					'des_entidad' => $rowuser->des_entidad,
					'correo' => $rowuser->username . '@intranet1.net.ve'
				);
				break;
			}
			if ( isset($usuario_data['username']) )
			{
				if ($usuario_data['username'] != '')
				{
					$usuario_data['logueado'] = TRUE;
					$data['accionpagina']='logueado';
				}
				else
				{
					$usuario_data['logueado'] = FALSE;
					$data['accionpagina']='deslogueado';
				}
				$this->session->set_userdata($usuario_data);
			}
		}
		if( $this->session->userdata('logueado') == TRUE && $this->session->userdata('logueado') != '')
		{
			// TODO agregar en cada archivo esta linea if($this->session->userdata('logueado')) y verifica sesion
			$data['intranet'] = $this->session->userdata('intranet');
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
			$this->table->add_row('Sello', $this->session->userdata('sello'));
			$this->table->add_row('Centro de costo', $this->session->userdata('cod_entidad') . ' - ' . $this->session->userdata('des_entidad'), '');
			$data['presentar']='<h3>RECORDAR QUE ESTO ESTA EN FASE DE PRUEBA AUN!!!</h3><br>'.$this->table->generate();
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
		// limpiar tablas si existen de las vistas de reportes hacking
		if( $this->session->userdata('logueado') == TRUE && $this->session->userdata('logueado') != '')
		{
			$userintran = $this->session->userdata('intranet');
			$sqllimpiatableviewbyentidadporusuario = "DROP TABLE IF EXISTS registro_gastos_".$userintran." ;";
			$this->load->database('gastossystema');
			$this->db->query($sqllimpiatableviewbyentidadporusuario);
		}
		// destruir la session e invalidarla
		$usuario_data = array('logueado' => FALSE);
		$this->session->set_userdata($usuario_data);
		$this->session->sess_destroy();
		$this->index();
	}
}
