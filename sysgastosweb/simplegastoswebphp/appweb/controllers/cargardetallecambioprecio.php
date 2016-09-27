<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cargardetallecambioprecio extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		if( $this->session->userdata('logueado') == FALSE)
		{
			redirect('manejousuarios');
		}
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->model('menu');
		$connecion = $this->load->database('oasis');
		 //el profiler esta dañado.. debido a una mala coarga de arreglos para los de idiomas
//		$this->output->enable_profiler(TRUE);
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/indexcontroler
	 *	- or -  
	 * 		http://example.com/index.php/indexcontroler/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/indexcontroler/<method_name>
	 * @see /user_guide/general/urls.html
	 */
	public function index()
	{
		$data['menu'] = $this->menu->general_menu();
		// quiery plano y ejecucion//,nom_usuario,origen,destino
		$sqlultimodespachosdias = "select cod_order  from dba.tm_orden_despacho where cambio_precio_asc=''";
		$resultadoultimodespachosdias = $this->db->query($sqlultimodespachosdias);
		$ultimodespachosdias = array();
		//$ultimodespachosdias = $resultadoultimodespachosdias->result_array();
		foreach ($resultadoultimodespachosdias->result() as $row1)
		{
			$ultimodespachosdias[$row1->cod_order] = rtrim($row1->cod_order, ".txt");
		}
		$sqleventoscambioprecio = "select num_cpp  from dba.tm_evento_precio where flag_estado <>2 order by num_cpp desc";
		$resultadoeventoscambioprecio = $this->db->query($sqleventoscambioprecio);
		$ultimoseventosprecions = array();
		//$ultimoseventosprecions = $resultadoeventoscambioprecio->result_array();
		foreach ($resultadoeventoscambioprecio->result() as $row2)
		{
			$ultimoseventosprecions[$row2->num_cpp] = $row2->num_cpp;
		}
		if( empty( $ultimodespachosdias ) ) $ultimodespachosdias = array(''=>'');
		if( empty( $ultimoseventosprecions ) ) $ultimoseventosprecions = array(''=>'');
		$data['listadesplegableordenesbox']=form_dropdown('listadesplegableordenesbox', $ultimodespachosdias);
		$data['listadesplegablepreciosbox']=form_dropdown('listadesplegablepreciosbox', $ultimoseventosprecions);
		$this->load->view('header.php',$data);
		$this->load->view('cargardetallecambioprecio.php',$data);
		$this->load->view('footer.php',$data);
	}
	
	public function asociardetalleconordendespacho()
	{
		//"call dba.ls_insertar_detalle_cambio_precio_orden(1,2019,'ordendespachocarga201603091236.txt')";
		// OBTENER DATOS DE FORMULARIO ***************************** /
		$listadesplegableordenesbox = $this->input->get_post('listadesplegableordenesbox');
		$listadesplegablepreciosbox = $this->input->get_post('listadesplegablepreciosbox');
		// LLAMAR EL PROCEDURE PARA QUE ASOCIE LO SELECCIOANDO *********************** /
		$sqlllamadaprecioaso = "call dba.ls_insertar_detalle_cambio_precio_orden(1,".$listadesplegablepreciosbox.",'".$listadesplegableordenesbox."')";
		$resultadoasociarprecio = $this->db->query($sqlllamadaprecioaso);
		// TODO $this->db->afec obtener los afected roms 
		// TERMINAR EL PROCESO **************************************************** /
		$data['accionejecutada'] = 'resultadocargardetallecambioprecio';
		$this->index(); // en este mismo controlador despues me regreso y vuelvo a cargar todo llamando a una funcion interna
	}
}


