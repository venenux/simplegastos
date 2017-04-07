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
		$data['menu'] = $this->menu->menudesktop();
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
		if ( ($usuariocodgernow < 990 and $usuariocodgernow > 399) and ! ($usuariocodgernow == ''))
			redirect('cargargastomanual/gastomanualrevisarlos');
		$userdata = $this->session->all_userdata();
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->set_table('entidad');
		$crud->set_subject('Sucursal');
		$crud->unset_export(); // tabletools.js need ods eent csv extension
		$crud->set_relation_n_n('nam_usuario', 'entidad_usuario', 'usuarios', 'cod_entidad', 'intranet', 'nombre');
		$crud->set_relation('cod_fondo','fondo','{mon_fondo} ({fecha_fondo})');
		$crud->columns('abr_zona','abr_entidad','des_entidad','tipo_entidad','status','nam_usuario','cod_entidad','sello','rif_razonsocial','num_telefonofijo','num_celularenc1','cod_fondo','sessionflag');
		$crud->display_as('abr_entidad','Siglas')
			 ->display_as('abr_zona','Zona')
			 ->display_as('des_entidad','Nombre')
			 ->display_as('cod_fondo','Fondo')
			 ->display_as('tipo_entidad','Tipo')
			 ->display_as('status','Estado')
			 ->display_as('nam_usuario','Asociados')
			 ->display_as('cod_entidad','Codger')
			 ->display_as('rif_sucursal','Rif')
			 ->display_as('rif_razonsocial','Razon')
			 ->display_as('des_administradora','Adminstradora')
			 ->display_as('num_telefonofijo','Telefono')
			 ->display_as('des_nombreenc1','Encargado')
			 ->display_as('num_celularenc1','Celular')
			 ->display_as('des_nombreenc2','Sub Encargado')
			 ->display_as('num_celularenc2','Celular Sub Encargado')
			 ->display_as('sello','Sello')
			 ->display_as('sessionflag','Modificado');
		$crud->unset_add_fields('sessionflag','nam_usuario'); // TODO: bug no asocia usuario en crear
		$currentState = $crud->getState();
		$crud->set_rules('abr_entidad', 'Siglas', 'trim|alpha_numeric');
		$crud->set_rules('abr_zona', 'Zona', 'trim|alpha_dash');
		$crud->set_rules('des_entidad', 'Nombre', 'trim|alpha_numeric_spaces');
		if($currentState == 'add')
		{
			$crud->required_fields('cod_entidad','abr_entidad','abr_zona','des_entidad','tipo_entidad','status');
			$crud->set_rules('cod_entidad', 'Centro de Costo (codger)', 'trim|numeric');
		}
		else if ($currentState == 'edit')
		{
			$crud->required_fields('abr_entidad','abr_zona','des_entidad','tipo_entidad','status');
			$crud->field_type('cod_entidad', 'readonly');
			$crud->field_type('sessionflag', 'readonly');
		}
		$crud->field_type('tipo_entidad','dropdown',array('NORMAL' => 'NORMAL', 'SUCURSAL' => 'SUCURSAL', 'ADMINISTRATIVO' => 'ADMINISTRATIVO'));
		$crud->field_type('status','dropdown',array('ACTIVO' => 'ACTIVO', 'INACTIVO' => 'INACTIVO', 'CERRADO' => 'CERRADO', 'ESPECIAL' => 'ESPECIAL'));
		$crud->callback_before_update(array($this,'echapajacuando'));
		//$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url("/admentidades"));
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
