<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admusuariosentidad extends CI_Controller {

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
		$data['menu'] = $this->menu->menudesktop();
		$data['output'] = $output; // TODO: output tiene mAs datos sacarlos y meterlos en $data hace funcionar todo normal
		$data['admvistaurlaccion'] = 'admusuariosentidad';
		$data['advertenciaformato'] = "Aqui se listan los usuarios, los centros de costos mas abajo, y para editarlos debe usar los botones para activar la ediccion (solo nivel administrativo).";
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
		$this->config->load('grocery_crud');
		$this->config->set_item('grocery_crud_dialog_forms',true);
		$this->config->set_item('grocery_crud_default_per_page',10);

		$output1 = $this->admusuariosavanzado();
		$output2 = $this->admsucursalesyusuarios();
//		$output3 = $this->admsoloverlosfondos();

		$js_files = $output1->js_files + $output2->js_files/* + $output3->js_files*/;
		$css_files = $output1->css_files + $output2->css_files/* + $output3->css_files*/;
		$output = ""
		."<h4>Listado de Usuarios del sistema</h4>".$output1->output
		."<h4>Listado de Centro de Costos</h4>".$output2->output
//		."<h4>Fondos registrados</h4>".$output3->output
		."";

		$this->_esputereport((object)array(
				'js_files' => $js_files,
				'css_files' => $css_files,
				'output'	=> $output
		));
	}

	public function admusuariosavanzado()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->set_table('usuarios');
		$crud->set_relation_n_n('sucursal', 'entidad_usuario', 'entidad', 'intranet', 'cod_entidad', 'des_entidad');
		$crud->set_relation('cod_fondo','fondo','{mon_fondo} ({fecha_fondo})');
		$crud->columns('ficha','nombre','intranet','sucursal','tipo_usuario','estado','cod_fondo','acc_lectura','acc_escribe','acc_modifi','fecha_ultimavez','sessionficha','sessionflag');
		$crud->display_as('ficha','Ficha/CI')
			 ->display_as('nombre','Nombre')
			 ->display_as('intranet','Intranet')
			 ->display_as('sucursal','Asociacion')
			 ->display_as('cod_fondo','Fondo')
			 ->display_as('tipo_usuario','Tipo')
			 ->display_as('sessionficha','Session')
			 ->display_as('fecha_ultimavez','Ultima')
			 ->display_as('acc_lectura','Accede')
			 ->display_as('acc_escribe','Crea')
			 ->display_as('acc_modifi','Altera')
			 ->display_as('sessionflag','Modificado'); // si usa add_fiels y unset_add no inserta
		$crud->set_subject('Usuarios');	// columns y fields no pueden ir juntos bug crud
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url("/admusuariosentidad"));
		$crud->unset_operations();
		$output = $crud->render();
		if($crud->getState() != 'list') {
			$this->_esputereport($output);
		} else {
			return $output;
		}
	}

	public function admsucursalesyusuarios()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->set_table('entidad');
		$crud->set_subject('Sucursal');
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
			 ->display_as('num_telefonofijo','Telefono')
			 ->display_as('num_celularenc1','Celular')
			 ->display_as('sello','Sello')
			 ->display_as('sessionflag','Modificado');
		$crud->unset_add_fields('sessionflag');
		$crud->set_relation_n_n('nam_usuario', 'entidad_usuario', 'usuarios', 'cod_entidad', 'intranet', 'nombre');
		$crud->set_relation('cod_fondo','fondo','{mon_fondo} ({fecha_fondo})');
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url("/admusuariosentidad"));
		$crud->unset_operations();
		$output = $crud->render();
		if($crud->getState() != 'list') {
			$this->_esputereport($output);
		} else {
			return $output;
		}
	}

	public function admsoloverlosfondos()
	{
		$crud = new grocery_CRUD();
		//$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->set_table('fondos');  // TODO : requiere trato especias y vista fondos creada
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->columns('fecha_fondo','mon_fondo','quien','cod_quien','cod_fondo','sessionflag');
		$crud->display_as('cod_fondo','Codigo')
			 ->display_as('mon_fondo','Disponible')
			 ->display_as('fecha_fondo','Al')
			 ->display_as('cod_quien','Id')
			 ->display_as('quien','Quien')
			 ->display_as('sessionflag','Alterado');
		$crud->set_primary_key('cod_fondo','fondos');
		$crud->unset_operations();
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url(strtolower(__CLASS__."/admusuariosentidad")));
		$output = $crud->render();
		if($crud->getState() != 'list') {
			$this->_esputereport($output);
		} else {
			return $output;
		}
	}

}
