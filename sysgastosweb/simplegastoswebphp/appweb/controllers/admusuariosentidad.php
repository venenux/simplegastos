<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admusuariosentidad extends CI_Controller {

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

	public function products_management()
	{
			$crud = new grocery_CRUD();
			$crud->set_table('products');
			$crud->set_subject('Product');
			$crud->unset_columns('productDescription');
			$crud->callback_column('buyPrice',array($this,'valueToEuro'));
			$output = $crud->render();
			$this->_esputereport($output);
	}

	public function valueToEuro($value, $row)
	{
		return $value.' &euro;';
	}

	function index()
	{
		$this->_verificarsesion();
		$this->config->load('grocery_crud');
		$this->config->set_item('grocery_crud_dialog_forms',true);
		$this->config->set_item('grocery_crud_default_per_page',10);

		$output1 = $this->admusuariosavanzado();
		$output2 = $this->admsucursalesyusuarios();

		$js_files = $output1->js_files + $output2->js_files;
		$css_files = $output1->css_files + $output2->css_files;
		$output = ""
		."<h3>Administrar Usuarios</h3>".$output1->output
		."<h3>Centro de Costos</h3>".$output2->output
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
		$crud->set_table('usuarios');
		$crud->columns('ficha','nombre','intranet','sucursal','estado','acc_lectura','acc_escribe','acc_modifi','fecha_ficha','fecha_ultimavez','sessionflag');
		$crud->display_as('ficha','Ficha/CI')
			 ->display_as('nombre','Nombre')
			 ->display_as('intranet','Intranet')
			 ->display_as('sucursal','Centro de Costo')
			 ->display_as('fecha_ficha','Creado')
			 ->display_as('fecha_ultimavez','Ultima vez')
			 ->display_as('acc_lectura','Accede a')
			 ->display_as('acc_escribe','Crea en')
			 ->display_as('acc_modifi','Altera en')
			 ->display_as('sessionflag','Modificado');
		$crud->set_subject('Usuarios');
		$crud->unset_add_fields('clave','sessionflag','fecha_ultimavez');
		$crud->unset_edit_fields('clave','fecha_ultimavez');
		$crud->set_relation_n_n('sucursal', 'entidad_usuario', 'entidad', 'ficha', 'cod_entidad', 'des_entidad');
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->set_rules('intranet', 'Login intranet', 'trim|required|alphanumeric');
			$crud->set_rules('ficha', 'Ficha', 'trim|required|numeric');
			$crud->set_rules('acc_lectura', 'Modulos que accede', 'trim|required|alphanumeric');
			$crud->set_rules('acc_escribe', 'Modulos que crea', 'trim|required|alphanumeric');
			$crud->set_rules('acc_modifi', 'Modulos que altera', 'trim|required|alphanumeric');
			$crud->callback_add_field('fecha_ficha', function () {	return '<input type="text" maxlength="50" value="'.date("YmdHis").'" name="fecha_ficha" readonly="true">';	});
		}
		else if ($currentState == 'edit')
		{
			$crud->field_type('fecha_ficha', 'readonly');
			$crud->field_type('intranet', 'readonly');
			$crud->field_type('ficha', 'readonly');
			$crud->set_rules('acc_lectura', 'Modulos que accede', 'trim|required|alphanumeric');
			$crud->set_rules('acc_escribe', 'Modulos que crea', 'trim|required|alphanumeric');
			$crud->set_rules('acc_modifi', 'Modulos que altera', 'trim|required|alphanumeric');
			$crud->field_type('sessionflag', 'readonly');
		}
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->callback_add_field('estado', function () {	return '<input type="text" maxlength="50" value="" name="status"> (ACTIVO|INACTIVO)';	});
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url(strtolower(__CLASS__."/admusuariosentidad")));
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
		$crud->set_table('entidad');
		$crud->set_subject('Sucursal');
		$crud->columns('abr_entidad','abr_zona','cod_entidad','des_entidad','status','nam_usuario','codger','sessionflag');
		$crud->display_as('cod_entidad','Cod. Centro')
			 ->display_as('abr_entidad','Cod. Siglas')
			 ->display_as('abr_zona','Cod. Zona')
			 ->display_as('des_entidad','Nombre')
			 ->display_as('codger','Relacion en nomina')
			 ->display_as('status','Estado')
			 ->display_as('nam_usuario','Asociados')
			 ->display_as('sessionflag','Modificado');
		$crud->unset_add_fields('sessionflag');
		$crud->set_relation_n_n('nam_usuario', 'entidad_usuario', 'usuarios', 'cod_entidad', 'ficha', 'nombre');
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->set_rules('cod_entidad', 'Centro de Costo (codger)', 'trim|required|numeric');
			$crud->set_rules('abr_entidad', 'Siglas', 'trim|required|alphanumeric');
		}
		else if ($currentState == 'edit')
		{
			$crud->field_type('cod_entidad', 'readonly');
			$crud->field_type('abr_entidad', 'readonly');
			$crud->field_type('sessionflag', 'readonly');
		}
		$crud->set_rules('abr_zona', 'Zona', 'trim|required|alphanumeric');
		$crud->set_rules('des_entidad', 'Nombre', 'trim|required|alphanumeric');
		$crud->callback_add_field('status', function () {	return '<input type="text" maxlength="50" value="" name="status"> (ACTIVO|INACTIVO|CERRADO)';	});
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url(strtolower(__CLASS__."/admusuariosentidad")));
		$output = $crud->render();
		if($crud->getState() != 'list') {
			$this->_esputereport($output);
		} else {
			return $output;
		}
	}

	function echapajacuando($post_array, $primary_key)
	{
		$post_array['sessionflag'] = $this->session->userdata('username').date("YmdHis");
		// TODO: insert para tabla log
		return $post_array;
	}

}
