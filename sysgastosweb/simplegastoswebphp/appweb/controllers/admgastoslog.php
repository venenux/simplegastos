<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admgastoslog extends CI_Controller {

	private $DBGASTO = null;
	private $usuariologin, $sessionflag, $usuariocodger, $acc_lectura, $acc_escribe, $acc_modifi;

	function __construct()
	{
		parent::__construct();
		$this->load->database('gastossystema');
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
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
		if( $this->session->userdata('logueado') != '1')
			redirect('manejousuarios/desverificarintranet');
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		if( is_array($usuariocodgernow) )
		{
			if (in_array("998", $usuariocodgernow) or in_array("1000", $usuariocodgernow) )
				$this->nivel = 'administrador';
			else if (in_array("163", $usuariocodgernow) or in_array("251", $usuariocodgernow) )
				$this->nivel = 'contabilidad';
			else
				$this->nivel = 'especial';
		}
		else
		{
			if( $usuariocodgernow == '998' or $usuariocodgernow == '1000' )
				$this->nivel = 'administrador';
			else if( ( $usuariocodgernow > 399 and $usuariocodgernow < 998) or $usuariocodgernow == '196' or $usuariocodgernow == '252' or $usuariocodgernow == '200' )
				$this->nivel = 'sucursal';
			else if ( $usuariocodgernow == '163' or $usuariocodgernow == '251' )
				$this->nivel = 'contabilidad';
			else
				$this->nivel = 'especial';
		}
	}

	/**
	 * Index Page cuando se invoca la url de este controlador,
	 * aqui se invoca la vista o otro metodo que la invoque
	 * map to /index.php/admgastoslog/index
	 */
	public function index()
	{
		$this->seccionlogpre();
	}

	public function seccionlogpre()
	{
		$this->_verificarsesion();
		$data['menu'] = $this->menu->menudesktop();
		$data['accionejecutado'] = 'seccionlogpre';	// para cargar parte especifica de la vista envio un parametro accion
		$data['accionejecutara'] = 'seccionlogver';	// para cargar parte especifica de la vista envio un parametro accion
		$this->load->view('header.php',$data);
		$this->load->view('admgastoslog.php',$data);
		$this->load->view('footer.php',$data);
	}

	public function seccionlogver()
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
		
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		$userintran = $this->session->userdata('intranet');
		$fec_registroini = $this->input->get_post('fec_registroini');
		$fec_registrofin = $this->input->get_post('fec_registrofin');
		$operacion = $this->input->get_post('operacion');
		$sessionfichav = $this->input->get_post('sessionficha');

		// ******* ini nombres de tablas para filtrar los datos:
		$segurodelatabla = rand(6,8);
		$tablalogorigin = "log";
		$tablalogsegura = "log_".$userintran . $segurodelatabla;

		$sqltablalogs = "
			CREATE TABLE IF NOT EXISTS `".$tablalogsegura."` SELECT * FROM (`".$tablalogorigin."`)
			WHERE ( cod_log <> '' or sessionficha <> '') ";
				if ( $this->nivel != 'administrador' ) 	$sqltablalogs .= "AND sessionficha LIKE '%".$userintran."%' ";
				if ( trim($fec_registroini) != '')	$sqltablalogs .= " AND CONVERT(substring(cod_log,1,8),UNSIGNED) >= ".$this->db->escape_str($fec_registroini)." ";
				if ( trim($fec_registrofin) != '')	$sqltablalogs .= " AND CONVERT(substring(cod_log,1,8),UNSIGNED) <= ".$this->db->escape_str($fec_registrofin)." ";
				if ( trim($operacion) != '')	$sqltablagastousr .= " AND (operacion LIKE '%".$this->db->escape_str($operacion)."%' ";
				if ( trim($sessionfichav) != '')	$sqltablagastousr .= " AND (sessionficha LIKE '".str_replace('.','_',$this->db->escape_str($sessionfichav))."' or sessionflag LIKE '".str_replace('.','_',$this->db->escape_str($sessionfichav))."') ";
		$sqltablalogs .= " ORDER BY cod_log DESC ";
		$sqltablapre = "DROP TABLE IF EXISTS ".$tablalogsegura.";";
		if ( $this->nivel != 'administrador')
			$sqltablalogs .= " LIMIT 800";
		else
			$sqltablalogs .= " LIMIT 2000";
		$this->db->query($sqltablapre);	// remuevo la viejas o datos viejos si hay aun
		$this->db->query($sqltablalogs);		// recreo con el select la tabla temporal y se usara
		$this->load->helper(array('inflector','url'));
		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
		$crud->set_table($tablalogsegura);
		$crud->set_primary_key('cod_log');
		$crud->set_theme('bootstrap');
		$crud->columns('cod_log','operacion','sessionficha');
		$crud
			 ->display_as('cod_log','Cuando')
			 ->display_as('operacion','Operaciones')
			 ->display_as('sessionficha','Quien');
		$crud->unset_add();
		$crud->unset_edit();
		$crud->unset_delete();
		//$crud->unset_export();
		$crud->field_type('operacion','text');
		$this->load->helper(array('form', 'url','inflector'));
		$output = $crud->render();
		$data['menu'] = $this->menu->menudesktop();
		$data['accionejecutado'] = 'seccionlogver';	// para cargar parte especifica de la vista envio un parametro accion
		$data['accionejecutara'] = 'seccionlogpre';	// para cargar parte especifica de la vista envio un parametro accion
		$this->load->view('header.php',$data);
		$this->load->view('admgastoslog.php',$output);
		$this->load->view('footer.php',$data);
	}

}
