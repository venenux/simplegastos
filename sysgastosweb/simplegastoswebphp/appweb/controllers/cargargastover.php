<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cargargastover extends CI_Controller {

	protected $numeroordendespacho =  '';
	private $usuariologin, $sessionflag, $usuariocodger, $acc_lectura, $acc_escribe, $acc_modifi;

	function __construct()
	{
		parent::__construct();
		$this->load->database('gastossystema');
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->model('menu');
		$this->output->enable_profiler(TRUE);
	}

	/**
	 * Index Page for this controller.
	 * 		http://example.com/index.php/indexcontroler
	 * 		http://example.com/index.php/indexcontroler/index
	 * map to /index.php/indexcontroler/<method_name>
	 */
	public function index()
	{
		if( $this->session->userdata('logueado') == FALSE)
		{
			redirect('manejousuarios/desverificarintranet');
		}
		$data['menu'] = $this->menu->general_menu();

		/* cargar y listaar las CATEGORIAS que se usaran para registros */
			$sqlcategoria = "
			select
			 ifnull(cod_categoria,'99999999999999') as cod_categoria,      -- YYYYMMDDhhmmss
			 ifnull(des_categoria,'sin_descripcion') as des_categoria,
			 ifnull(fecha_categoria, 'INVALIDO') as fecha_categoria,
			 ifnull(sessionflag,'') as sessionflag
			from categoria
			  where ifnull(cod_categoria, '') <> '' and cod_categoria <> ''
			";
			$resultadoscategoria = $this->db->query($sqlcategoria);
			$arreglocategoriaes = array(''=>'');
			foreach ($resultadoscategoria->result() as $row)
			{
				$arreglocategoriaes[''.$row->cod_categoria] = '' . $row->des_categoria . '-' . $row->fecha_categoria;
			}
			$data['list_categoria'] = $arreglocategoriaes; // agrega este arreglo una lista para el combo box
			unset($arreglocategoriaes['']);
		/* cargar y listaar las SUBCATEGORIAS que se usaran para registros */
			$sqlsubcategoria = "
			SELECT
			ca.cod_categoria,
			ca.des_categoria,
			sb.cod_subcategoria,
			sb.des_subcategoria,
			ca.fecha_categoria,
			sb.fecha_subcategoria,
			sb.sessionflag
			FROM categoria as ca
			join subcategoria as sb
			on sb.cod_categoria = ca.cod_categoria
			";
			$resultadossubcategoria = $this->db->query($sqlsubcategoria);
			$arreglosubcategoriaes = array(''=>'');
			foreach ($resultadossubcategoria->result() as $row)
			{
				$arreglosubcategoriaes[''.$row->cod_subcategoria] = $row->des_categoria . ' - ' . $row->des_subcategoria;
			}
			$data['list_subcategoria'] = $arreglosubcategoriaes; // agrega este arreglo una lista para el combo box
			unset($arreglosubcategoriaes['']);
		/* cargar y listaar las UBIUCACIONES que se usaran para registros */
			$sqlentidad = "
			select
			 abr_entidad, abr_zona, des_entidad,
			 ifnull(cod_entidad,'99999999999999') as cod_entidad,      -- YYYYMMDDhhmmss
			 ifnull(des_entidad,'sin_descripcion') as des_entidad
			from entidad
			  where ifnull(cod_entidad, '') <> '' and cod_entidad <> ''
			";
			$resultadosentidad = $this->db->query($sqlentidad);
			$arregloentidades = array(''=>'');
			foreach ($resultadosentidad->result() as $row)
			{
				$arregloentidades[''.$row->cod_entidad] = $row->cod_entidad . ' - ' . $row->abr_entidad .' - ' . $row->des_entidad . ' ('. $row->abr_zona .')';
			}
			$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box
			unset($arregloentidades['']);
		/* ahora renderizar o pintar el formulario de carga la vista */
		$this->load->view('header.php',$data);
		$this->load->view('cargargastover.php',$data);
		$this->load->view('footer.php',$data);
	}

	public function gastovercustom()
	{
		if( $this->session->userdata('logueado') == FALSE)
		{
			redirect('manejousuarios/desverificarintranet');
		}
		$userdata = $this->session->all_userdata();
		$usercorreo = $userdata['correo'];
		$userintran = $userdata['intranet'];
		$userpermacceso = $userdata['abr_entidad']; // TODO per,misos
		// OBTENER DATOS DE FORMULARIO ***************************** /
		$fec_registroini = $this->input->get_post('fec_registroini');
		$fec_registrofin = $this->input->get_post('fec_registrofin');
		$mon_registroigual = $this->input->get_post('mon_registroigual');
		$mon_registromayor = $this->input->get_post('mon_registromayor');
		$des_registrolike = $this->input->get_post('des_registrolike');
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_subcategoria = $this->input->get_post('cod_subcategoria');
		$this->load->helper('inflector');
		$cantidadLineas = 0;
		$resultadocarga = array('Error, no se completo el proceso', 'Sin datos', '0', '', '', '', '');
		// CONFIGURAR EL REPIORTADOR Y ESCUPIR CON QUERY CUSTOM
		$this->load->helper('url');
		$sqlreportegasto = "SELECT
              `registro_gastos`.`cod_registro`, `registro_adjunto`.`cod_adjunto`,
              `registro_gastos`.`cod_entidad`,
              `registro_gastos`.`cod_categoria`, `registro_gastos`.`cod_subcategoria`,
              `categoria`.`des_categoria`, `subcategoria`.`des_subcategoria`,
              `registro_gastos`.`des_registro`, `registro_gastos`.`mon_registro`,
              `entidad`.`des_entidad`, `entidad`.`abr_entidad`, `entidad`.`abr_zona`,
              `registro_gastos`.`estado`,  `registro_gastos`.`num_factura`,
              `registro_adjunto`.`hex_adjunto`, `registro_adjunto`.`nam_adjunto`,
              `registro_adjunto`.`fecha_adjunto`,
              `registro_gastos`.`fecha_registro`, `registro_gastos`.`fecha_factura`,
              `registro_gastos`.`sessionflag`
            FROM
             `registro_gastos`
            LEFT JOIN
             `registro_adjunto` ON `registro_adjunto`.`cod_registro` = `registro_gastos`.`cod_registro`
            LEFT JOIN
             `subcategoria` ON  `subcategoria`.`cod_subcategoria` = `registro_gastos`.`cod_subcategoria`
            LEFT JOIN
             `categoria` ON  `categoria`.`cod_categoria` = `registro_gastos`.`cod_categoria`
            LEFT JOIN
             `entidad` ON  `entidad`.`cod_entidad` = `registro_gastos`.`cod_entidad`
            WHERE
             ifnull(`registro_gastos`.`cod_registro`,'') <> '' and `registro_gastos`.`cod_registro` <> ''
            ";
        // cCONFIGURACION DE FILTROS SEGUN FORMULARIO PARA EL QUERY
        if ( $fec_registroini != '')		$sqlreportegasto .= "and CONVERT(fecha_registro,UNSIGNED INTEGER) >= CONVERT('".$fec_registroini."',UNSIGNED INTEGER)";
        if ( $fec_registrofin != '')		$sqlreportegasto .= "and CONVERT(fecha_registro,UNSIGNED INTEGER) <= CONVERT('".$fec_registrofin."',UNSIGNED INTEGER)";
        if ( $mon_registroigual != '')		$sqlreportegasto .= "and mon_registro >= '".$mon_registroigual."'";
        if ( $mon_registromayor != '')		$sqlreportegasto .= "and mon_registro <= '".$mon_registromayor."'";
		if ( $des_registrolike != '')		$sqlreportegasto .= "and des_registro LIKE '%".$des_registrolike."'%";
		if ( $cod_entidad != '')			$sqlreportegasto .= "and cod_entidad = '".$cod_entidad."'";
		if ( $cod_subcategoria != '')		$sqlreportegasto .= "and registro_gastos.cod_subcategoria = '".$cod_subcategoria."'";
		// TODO PERMISOS; si esta en tienda o no es admin, solo un mes de datos
		if ( $this->uri->segment(1) != 'todos');
			$sqlreportegasto .= " and CONVERT(fecha_registro,UNSIGNED INTEGER) >= CONVERT('".date('Ymd',strtotime('-1 month'))."',UNSIGNED INTEGER) ";
		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
	    $crud->set_model('Custom_grocery_crud_model');
	    $crud->set_table('registro_gastos');
	    $crud->basic_model->set_custom_query($sqlreportegasto);
		//$crud->columns('cod_categoria','des_categoria','fecha_categoria','sessionflag');
		$crud->display_as('cod_registro','Cod. Gasto')
			 ->display_as('cod_entidad','Cod. CodGer')
			 ->display_as('cod_categoria','Cod. Categoria')
			 ->display_as('cod_subcategoria','Cod. Concepto')
			 ->display_as('des_categoria','Categoria')
			 ->display_as('des_subcategoria','Concepto')
			 ->display_as('des_registro','Descripcion Gasto')
			 ->display_as('mon_registro','Monto del Gasto')
			 ->display_as('num_factura','Factura (opt)')
			 ->display_as('sessionflag','Modificado');
		$crud->set_subject('Registros');
		$crud->edit_fields('des_registro','mon_registro','num_factura','cod_registro','cod_entidad','cod_categoria','cod_subcategoria','sessionflag');
		$crud->unset_add();
		$crud->unset_delete();
		$crud->field_type('des_registro', 'text');
		$crud->field_type('estado','dropdown',array('APROBADO' => 'APROBADO', 'PENDIENTE' => 'PENDIENTE', 'RECHAZADO' => 'RECHAZADO'));
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->set_rules('cod_entidad', 'Codigo Centro costo', 'trim|required|alphanumeric');
			$crud->set_rules('cod_registro', 'Codigo Registro', 'trim|required|alphanumeric');
			$crud->set_rules('cod_categoria', 'Codigo Categoria', 'trim|required|alphanumeric');
			$crud->set_rules('cod_subcategoria', 'Codigo Concepto', 'trim|required|alphanumeric');
			$crud->set_rules('des_registro', 'Descripcion', 'trim|required|alphanumeric');
			$crud->set_rules('fecha_categoria', 'Creado', 'trim|required');
			$crud->callback_add_field('fecha_categoria', function () {	return '<input type="text" maxlength="50" value="'.date("YmdHis").'" name="fecha_categoria" readonly="true">';	});
		}
		else if ($currentState == 'edit')
		{
			$crud->field_type('cod_registro', 'readonly');
			$crud->field_type('cod_entidad', 'readonly');
			$crud->field_type('cod_categoria', 'readonly');
			$crud->field_type('cod_subcategoria', 'readonly');
			$crud->set_rules('des_registro', 'Descripcion', 'trim|required|alphanumeric');
			$crud->set_rules('mon_registro', 'Descripcion', 'trim|required|alphanumeric');
			$crud->field_type('num_factura', 'readonly');
			$crud->field_type('sessionflag', 'readonly');
		}
		$crud->callback_before_update(array($this,'echapajacuando'));
		$output = $crud->render();
		// TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'resultadocargardatosver';
		$data['userintran'] = $userintran;
		$data['fec_registroini'] = $fec_registroini;
		$data['fec_registrofin'] = $fec_registrofin;
		$data['mon_registroigual'] = $mon_registroigual;
		$data['mon_registromayor'] = $mon_registromayor;
		$data['des_registrolike'] = $des_registrolike;
		$data['cod_entidad'] = $cod_entidad;
		$data['cod_subcategoria'] = $cod_subcategoria;
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->view('header.php',$data);
		$this->load->view('cargargastover.php',$output);
		$this->load->view('footer.php',$data);
	}

	function echapajacuando($post_array, $primary_key)
	{
		$post_array['sessionflag'] = $this->session->userdata('username').date("YmdHis");
		// TODO: insert para tabla log
		return $post_array;
	}
}
