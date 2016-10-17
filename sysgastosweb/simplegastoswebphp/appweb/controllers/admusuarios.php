<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admusuarios extends CI_Controller {

	private static $modulosadm = array('admusuarios','admcategoriasconceptos','admgastoslog');

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
		$data['username'] = $this->session->userdata('username');
		$data['nombre'] = $this->session->userdata('nombre');
		$data['correo'] = $this->session->userdata('correo');
		$data['logueado'] = $this->session->userdata('logueado');
		$data['menu'] = $this->menu->general_menu();
		$data['admvistaurlaccion'] = 'admusuariosentidad';
		$data['advertenciaformato'] = "Los usuarios se crea o asigna clave es con actualizacion de clave de intranet, aqui ud los habilita entrar o no";
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
		$this->admusuariosavanzado();
	}

	public function admusuariosavanzado()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->unset_export();
		$crud->set_table('usuarios');
		$crud->set_subject('Usuarios');	// columns y fields no pueden ir juntos bug crud
		$currentState = $crud->getState();
		//$crud->set_relation_n_n('sucursal', 'entidad_usuario', 'entidad', 'intranet', 'cod_entidad', 'des_entidad'); // TODO error insertando
		//$crud->set_relation('cod_fondo','fondo','{mon_fondo} ({fecha_fondo})');
		$crud->columns('ficha','nombre','intranet',/*'sucursal',*/'estado','cod_fondo','sessionficha','acc_lectura','acc_escribe','acc_modifi','sessionflag','fecha_ultimavez');
		$crud->display_as('ficha','Ficha/CI')
			 ->display_as('nombre','Nombre')
			 ->display_as('intranet','Intranet')
			// ->display_as('sucursal','Codger') // bug se borra asociacion si nuevo usuario
			// ->display_as('cod_fondo','Fondo')
			 ->display_as('sessionficha','Creado')
			 ->display_as('fecha_ultimavez','Session')
			 ->display_as('acc_lectura','Accede')
			 ->display_as('acc_escribe','Crea')
			 ->display_as('acc_modifi','Altera')
			 ->display_as('sessionflag','Modificado'); // si usa add_fiels y unset_add no inserta
		$crud->add_fields('nombre','ficha','intranet','estado','cod_fondo','acc_lectura','acc_escribe','acc_modifi','sessionficha','sessionflag');
		$crud->edit_fields('nombre','ficha','intranet',/*'sucursal',*/'estado','cod_fondo','acc_lectura','acc_escribe','acc_modifi','sessionficha','sessionflag');
		$crud->field_type('sessionficha', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		$crud->field_type('sessionflag', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		$crud->field_type('acc_lectura', 'set',self::$modulosadm);
		$crud->field_type('acc_escribe', 'set',self::$modulosadm);
		$crud->field_type('acc_modifi', 'set',self::$modulosadm);
		$currentState = $crud->getState();
		$crud->unset_read(); // TODO: bug error muestra la clave desnuda
		if($currentState == 'add')
		{
			$crud->required_fields('ficha','intranet','nombre','estado');
			$crud->field_type('sessionflag', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		}
		else if ($currentState == 'edit')
		{
			$crud->required_fields('ficha',/*'sucursal',*/'nombre','estado');
			$crud->field_type('intranet', 'readonly');
		}
		$crud->callback_before_insert(array($this,'extradatainsert'));
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->field_type('estado','dropdown',array('ACTIVO' => 'ACTIVO', 'INACTIVO' => 'INACTIVO', 'SUSPENDIDO' => 'SUSPENDIDO'));
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url(strtolower(__CLASS__."/admusuarios")));
		$output = $crud->render();
		$this->_esputereport($output);
	}

	function extradatainsert($post_array)
	{
		//$post_array['cod_fondo'] = // verificar sea uno no seleccionado
		$post_array['sessionficha'] = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		// TODO: insert para tabla log
		return $post_array;
	}

	function echapajacuando($post_array, $primary_key)
	{
		$post_array['sessionflag'] = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		// TODO: insert para tabla log
		return $post_array;
	}

}
