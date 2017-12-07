<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class gas_registro_gastos_ingreso extends CI_Controller {

	private $usuariologin, $sessionflag, $usuariocodger, $acc_lectura, $acc_escribe, $acc_modifi;
	public $sysdbgastos = null;
	public $sysdbadmins = null;

	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session'); // debe estar primero que el menu
		$this->load->model('menu');
		$this->sysdbgastos = $this->load->database('gastossystema', TRUE);
		$this->sysdbadmins = $this->load->database('sysdbadmins', TRUE);
		$this->load->database('gastossystema');
		$this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT',TRUE);
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', TRUE);
		$this->output->set_header('Pragma: no-cache', TRUE);
		$this->output->set_header("Expires: 0", TRUE);
		$this->output->enable_profiler(TRUE);
		$this->output->enable_profiler(TRUE);
	}

	/**
	 * index del control de gas_registro_gastos_ingreso
	 */
	public function index()
	{
		if( $this->session->userdata('logueado') < 1)
		{
			redirect('manejousuarios/desverificarintranet');
		}
		$data['menu'] = $this->menu->menudesktop();

		/* cargar y listaar las CATEGORIAS que se usaran para registros */
			$sqlcategoria = "
			select
			 ifnull(cod_categoria,'99999999999999') as cod_categoria,
			 ifnull(des_categoria,'sin_descripcion') as des_categoria,
			 ifnull(fecha_categoria, 'INVALIDO') as fecha_categoria,
			 ifnull(sessionflag,'') as sessionflag
			from categoria
			  where ifnull(cod_categoria, '') <> '' and cod_categoria <> ''
			";
			$resultadoscategoria = $this->sysdbgastos->query($sqlcategoria);
			$arreglocategoriaes = array(''=>'');
			foreach ($resultadoscategoria->result() as $row)
			{
				$arreglocategoriaes[''.$row->cod_categoria] = '' . $row->des_categoria;
			}
			$data['list_categoria'] = $arreglocategoriaes; // agrega este arreglo una lista para el combo box
			unset($arreglocategoriaes['']);
		/* cargar y listaar las UBIUCACIONES que se usaran para registros */
			$sqlentidad = "
			select
			 abr_entidad, abr_zona, cod_sello, cod_msc, 
			 ifnull(cod_entidad,'99999999999999') as cod_entidad,      -- YYYYMMDDhhmmss
			 ifnull(des_entidad,'sin_descripcion') as des_entidad
			from sysdbadmins.adm_entidad
			  where ifnull(cod_entidad, '') <> '' and cod_entidad <> ''
			order by des_entidad
			";
			$resultadosentidad = $this->sysdbadmins->query($sqlentidad);
			$arregloentidades = array(''=>'');
			foreach ($resultadosentidad->result() as $row)
			{
				$arregloentidades[''.$row->cod_entidad] = $row->abr_entidad .' - ' . $row->cod_sello . ' - ' . $row->des_entidad . ' ('. $row->abr_zona .')';
			}
			$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box
			unset($arregloentidades['']);
		/* ahora renderizar o pintar el formulario de carga la vista */
		$data['accionejecutada'] = 'cargardatosadministrativosfiltrar';
		$this->load->view('header.php',$data);
		$this->load->view('gas_registro_gastos_ingreso.php',$data);
		$this->load->view('footer.php',$data);
	}

	/* metodo de acceso url de gastos a mostrar los registros, detecta la accion ejecutada que son de dos tipos mostrar o filtrar */
	public function gastoregistros()
	{
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		if( $this->session->userdata('logueado') < 1)
			redirect('manejousuarios/desverificarintranet');
		if ($usuariocodgernow < 990 and $usuariocodgernow > 399 )
			redirect('cargargastosucursales/gastosucursalesrevisarlos');
		$userdata = $this->session->all_userdata();
		$usercorreo = $userdata['correo'];
		$usersessid = $userdata['session_id'];
		$userintran = $userdata['intranet'];
		$userpermacceso = $userdata['abr_entidad']; // TODO per,misos
		$accionejecutada = $this->input->get_post('accionejecutada');
		$data['menu'] = $this->menu->menudesktop();
		$data['accionejecutada'] = 'cargardatosadministrativosfiltrar';
			// OBTENER DATOS DE FORMULARIO ***************************** /
		if ($accionejecutada = 'cargardatosadministrativosfiltrar')
		{
			$fec_registroini = $this->input->get_post('fec_registroini');
			$fec_registrofin = $this->input->get_post('fec_registrofin');
			$fec_conceptoini = $this->input->get_post('fec_conceptoini');
			$fec_conceptofin = $this->input->get_post('fec_conceptofin');
			$mon_registroigual = $this->input->get_post('mon_registroigual');
			$mon_registromayor = $this->input->get_post('mon_registromayor');
			$des_registrolike = $this->input->get_post('des_registrolike');
			$cod_entidad = $this->input->get_post('cod_entidad');
			$cod_categoria = $this->input->get_post('cod_categoria');
			$cod_juridico = $this->input->get_post('cod_juridico');
			$sessioncarga = $this->input->get_post('sessioncarga');
		}
		$this->load->helper(array('inflector','url'));
		$data['seguir']=$this->uri->segment(1).$this->uri->segment(2).$this->uri->segment(3);
		$tablaregistros = "gas_registro_gastos_ingreso";
		$this->load->database('gastossystema');
		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
		$crud->set_table($tablaregistros);
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->set_primary_key('cod_registro');
			if ( $cod_entidad != '')
				$crud->where($tablaregistros.'.cod_entidad' ,$cod_entidad);
			if ( $cod_categoria != '')
				$crud->where($tablaregistros.'.cod_categoria', $cod_categoria);
			if ( $cod_juridico != '')
				$crud->where($tablaregistros.'.cod_juridico',$cod_juridico);
			if ( $des_registrolike != '')
				$crud->like('des_concepto',$des_registrolike);
			if ( $fec_registroini != '')
				$crud->where('CONVERT(substring(fecha_registro,1,8),UNSIGNED) >= ',$fec_registroini);
			if ( $fec_registrofin != '')
				$crud->where('CONVERT(substring(fecha_registro,1,8),UNSIGNED) <= ',$fec_registrofin);
			if ( $fec_conceptoini != '')
				$crud->where('CONVERT(substring(fecha_gastado,1,8),UNSIGNED) >= ',$fec_conceptoini);
			if ( $fec_conceptofin != '')
				$crud->where('CONVERT(substring(fecha_gastado,1,8),UNSIGNED) <= ',$fec_conceptofin);
			if ( $mon_registroigual != '')
				$crud->like('mon_registro',$mon_registroigual, 'after');
			if ( $mon_registromayor != '')
				$crud->where('mon_registro >= ',$mon_registromayor);
			if ( $sessioncarga != '' )
				$crud->like('SUBSTRING(sessionficha, LOCATE(\'.\', sessionficha)+1 )',$sessioncarga );
		//	$crud->where($tablaregistros.'.cod_categoria > ', '399');
		//	$crud->where($tablaregistros.'.cod_categoria < ', '990');
		$crud->set_subject('Gasto');
		$crud
			 ->display_as('cod_registro','Codigo')
			 ->display_as('cod_entidad','Centro<br>Coste')
			 ->display_as('cod_categoria','Categoria')
			 ->display_as('cod_juridico','juridico')
			 ->display_as('mon_registro','Monto')
			 ->display_as('des_concepto','Concepto')
			 ->display_as('des_detalle','Razon<br>Para aprobar')
			 ->display_as('des_estado','Justificacion')
			 ->display_as('tipo_concepto','Tipo<br>Gasto')
			 ->display_as('fecha_gastado','Fecha<br>Gastado')
			 ->display_as('fecha_registro','Fecha<br>Ingresado')
			 ->display_as('factura_tipo','Factura<br>Tipo')
			 ->display_as('factura_num','Factura<br>Numero')
			 ->display_as('factura_rif','Factura<br>Rif')
			 ->display_as('factura_bin','Factura<br>Escaneada')
			 ->display_as('sessionflag','Modificado')
			 ->display_as('sessionficha','Creador');
		$crud->columns('fecha_gastado','fecha_registro','cod_entidad','cod_categoria',/*'cod_juridico',*/'mon_registro','des_concepto','estado','des_estado','tipo_concepto','factura_tipo','factura_num','factura_rif','factura_bin','cod_registro','fecha_registro','sessionficha','sessionflag');
		$crud->add_fields('fecha_registro','fecha_gastado','cod_entidad','cod_categoria',/*'cod_juridico',*/'mon_registro','des_concepto','estado','tipo_concepto','factura_tipo','factura_num','factura_rif','factura_bin','cod_registro','sessionficha');
		$crud->edit_fields('fecha_registro','fecha_gastado','cod_entidad','cod_categoria',/*'cod_juridico',*/'mon_registro','des_concepto','estado','des_detalle','des_estado','tipo_concepto','factura_tipo','factura_num','factura_rif','factura_bin','cod_registro','sessionflag');
		$crud->set_relation('cod_entidad','entidad','{des_entidad} - {cod_entidad}'); //,'{des_entidad}<br> ({cod_entidad})'
		$crud->set_relation('cod_categoria','categoria','{des_categoria}'); // ,'{des_categoria}<br> ({cod_categoria})'
		//$crud->set_relation('cod_juridico','adm_juridico','{des_razonsocial} - {cod_rif}'); // ,'{des_juridico}<br> ({cod_juridico})'
		//$crud->add_action('Auditar', '', '','ui-icon-plus',array($this,'_cargargastosucursalauditar'));
		$longdate=date('Ymd');//la fecha larga
		$leyear=substr($longdate,0,4);//solo interesa año
		$directoriofacturas = 'archivoscargas/gas_registro_gastos_ingreso/'.$leyear;//$directoriofacturas = 'archivoscargas/gas_registro_gastos_ingreso/2017'; // $directoriofacturas = 'archivoscargas/' . date("Y");
		if ( ! is_dir($directoriofacturas) )
		{
			if ( is_file($directoriofacturas) )
			{	unlink($directoriofacturas);	}
			mkdir($directoriofacturas, 0777, true);
			chmod($directoriofacturas,0777);
		}
		$urlsegmentos = $this->uri->segment_array();
		if ( ! in_array("todos", $urlsegmentos) )
			$crud->set_field_upload('factura_bin',$directoriofacturas);
		$data['rutas'] = $urlsegmentos;
		$crud->set_field_upload('factura_bin',$directoriofacturas);
		$crud->set_rules('des_concepto', 'Concepto', 'trim|alphanumeric');
		$crud->set_rules('mon_registro', 'Monto', 'trim|decimal');
		$crud->set_rules('cod_entidad', 'Centro de Costo', 'trim|alphanumeric');
		$crud->field_type('cod_juridico', 'invisible','100000000001');
		$crud->field_type('sessionficha', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		$crud->field_type('sessionflag', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		$crud->field_type('fecha_registro', 'invisible',''.date("Ymd"));
		$crud->field_type('tipo_concepto','invisible','ADMINISTRATIVO');
		$crud->field_type('factura_tipo','dropdown',array('CONTRIBUYENTE' => 'CONTRIBUYENTE', 'PRESUPUESTO' => 'PRESUPUESTO', 'NOTA' => 'NOTA'));
		$crud->field_type('des_detalle','text');
		$crud->field_type('des_estado','text');
		$crud->field_type('estado','dropdown',array('APROBADO' => 'APROBADO', 'PENDIENTE' => 'PENDIENTE', 'INVALIDO' => 'INVALIDO'));
		$crud->unset_texteditor('des_detalle');
		$crud->unset_texteditor('des_estado');
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->callback_add_field('cod_registro', function () {	return '<input type="text" maxlength="50" value="GAS'.date("YmdHis").'" name="cod_registro" readonly="true">';	});
			$crud->callback_add_field('fecha_gastado', function () {	$fecha_gastado=date('Ymd');	$idfeccon='fecha_gastado';	$valoresinputfechacon = array('name'=>$idfeccon,'id'=>$idfeccon, 'onclick'=>'javascript:NewCssCal(\''.$idfeccon.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfeccon, $$idfeccon));	return form_input($valoresinputfechacon);	});
			$crud->required_fields('cod_entidad','cod_categoria','mon_registro','des_concepto','factura_tipo','factura_bin');
		}
		else if ($currentState == 'edit')
		{
			$crud->field_type('fecha_registro', 'readonly');
			$crud->field_type('cod_registro', 'readonly'); // esto no se puede si ya se hizo algo antes
			$crud->callback_edit_field('fecha_gastado',array($this,'_editarfechagasto'));
			$crud->required_fields('cod_entidad','cod_categoria','mon_registro','des_concepto','factura_tipo','des_estado','factura_bin');
		}
		$crud->callback_column('mon_registro',array($this,'_numerosgente'));
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->callback_before_insert(array($this,'generarcodigo'));
		$crud->callback_before_delete(array($this,'echapajaborrando'));
		$output = $crud->render();
		$data['menu'] = $this->menu->menudesktop();
		$data['accionejecutada'] = 'cargardatosadminnistrativosfiltrados';
		$this->load->view('header.php',$data);
		$this->load->view('gas_registro_gastos_ingreso.php',$output);
		$this->load->view('footer.php',$data);
	}

	public function _numerosgente($value, $row)
	{
		$formateado = number_format($row->mon_registro, 2, ',', '.');
		return $formateado;
	}

	function _editarfechagasto($value, $primary_key)
	{
		$fecha_gastado=$value;
		$idfeccon='fecha_gastado';
		$valoresinputfechacon = array('name'=>$idfeccon,'id'=>$idfeccon, 'onclick'=>'javascript:NewCssCal(\''.$idfeccon.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfeccon, $$idfeccon));
		return form_input($valoresinputfechacon);
	}

	/* llamar preupdate antes actualizar adiciona quien esta realizando la actualizacion */
	function echapajacuando($post_array, $primary_key)
	{
		$justi = $post_array['des_detalle'];
		$razon = $post_array['des_estado'];
		$ver = str_replace(' ', '', $justi);
		if ( $ver = '' )
		    $justi = 'Aprobando sin justificar por mi: '.$this->session->userdata('username');
		$sessionflag = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		$post_array['sessionflag'] = $sessionflag;
		$operacion = $this->session->userdata('username').' cambio, datos viejos de ' . $primary_key . ': '.$post_array['des_concepto'].'-'.$post_array['cod_entidad'].'-'.$post_array['mon_registro'].'-'.$post_array['cod_categoria'].'-'.$post_array['cod_juridico'];

		$this->db->insert('log',array('cod_log' => date('YmdHis'), 'operacion' => $operacion, 'sessionficha' => $sessionflag));
		log_message('info', $operacion . ' por  ' . $sessionflag );
		return $post_array;
	}

	/* si se borra echa paja cuando */
	function echapajaborrando($primary_key)
	{
		$results = $this->db->query("SELECT * FROM registro_gastos WHERE cod_registro = '".$primary_key."'");
		$stringlog =  '>>>';
			foreach ($results->result() as $row)
			{
				$stringlog .= $row->cod_entidad.'-'.$row->cod_categoria.'-'.$row->cod_juridico.'-'.$row->mon_registro.''.'-'.$row->des_concepto.'-'.$row->sessionflag.'-'.$row->sessionficha.'';
			}
		$stringlog .= '<<<';
		$operacion = $this->session->userdata('username').' borrando el gasto ' . $primary_key . ':'.$stringlog;
		$sessionficha = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		$this->db->insert('log',array('cod_log' => date('YmdHis'), 'operacion' => $operacion, 'sessionficha' => $sessionficha));
		log_message('info', $operacion . ' en  ' . $sessionficha);
		return ;
	}

	/* antes de cada insercion se autogenera el codigo de nuevo, y a que hora lo hace, por si tardo mucho rellenando los datos se genera con hora exacta */
	function generarcodigo($post_array)
	{
		$fec_registro=date('Ymd');
		$cod_registronuevo = 'GAS'.date("YmdHis");
		$operacion = $this->session->userdata('username').' creando gasto nuevo ' . $cod_registronuevo;
		$sessionficha = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		$post_array['cod_registro'] = $cod_registronuevo;
		$post_array['fecha_registro'] = $fec_registro;
		$post_array['sessionficha'] = $sessionficha;
		$this->db->insert('log',array('cod_log' => date('YmdHis'), 'operacion' => $operacion, 'sessionficha' => $sessionficha));
		log_message('info', $operacion . ' en  ' . $sessionficha);
		return $post_array;
	}

	function _ver_after_insert($post_array,$primary_key)
	{
		$sessionficha = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		$operacion = $this->session->userdata('username').' gasto nuevo creado exitoso ' . $post_array['cod_registro'];
		$enlace = site_url('gas_registro_gastos_ingreso/gastoregistros/read/'.$post_array['cod_registro']).'?cod_registro='.$post_array['cod_registro'];
		$this->db->insert('log',array('cod_log' => date('YmdHis'), 'operacion' => $operacion, 'sessionficha' => $sessionficha));
		log_message('info', $operacion);
		//redirect($enlace);
		return "javascript:window.open ('".$enlace."','NOtificador','menubar=1,resizable=1,width=350,height=250');";
	}

}
