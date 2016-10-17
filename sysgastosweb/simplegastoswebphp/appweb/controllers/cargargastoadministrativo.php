<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cargargastoadministrativo extends CI_Controller {

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
		$this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT',TRUE);
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', TRUE);
		$this->output->set_header('Pragma: no-cache', TRUE);
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT", TRUE);
		$this->output->enable_profiler(TRUE);
	}

	/**
	 * index del control de cargargastoadministrativo
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
		$data['accionejecutada'] = 'cargardatosadministrativosfiltrar';
		$this->load->view('header.php',$data);
		$this->load->view('cargargastoadministrativo.php',$data);
		$this->load->view('footer.php',$data);
	}

	/* metodo de acceso url de gastos a mostrar los registros, detecta la accion ejecutada que son de dos tipos mostrar o filtrar */
	public function gastoregistros()
	{
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		if( $this->session->userdata('logueado') == FALSE)
			redirect('manejousuarios/desverificarintranet');
		if ($usuariocodgernow < 990 and $usuariocodgernow > 399 )
			redirect('cargargastomanual/gastomanualrevisarlos');
		$userdata = $this->session->all_userdata();
		$usercorreo = $userdata['correo'];
		$usersessid = $userdata['session_id'];
		$userintran = $userdata['intranet'];
		$userpermacceso = $userdata['abr_entidad']; // TODO per,misos
		$tablaregistros = "registro_gastos";
		$accionejecutada = $this->input->get_post('accionejecutada');
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'cargardatosadministrativosfiltrar';
		if ($accionejecutada = 'cargardatosadministrativosfiltrar')
		{
			// OBTENER DATOS DE FORMULARIO ***************************** /
			$fec_registroini = $this->input->get_post('fec_registroini');
			$fec_registrofin = $this->input->get_post('fec_registrofin');
			$mon_registroigual = $this->input->get_post('mon_registroigual');
			$mon_registromayor = $this->input->get_post('mon_registromayor');
			$des_detallelike = $this->input->get_post('des_detallelike');
			$cod_entidad = $this->input->get_post('cod_entidad');
			$cod_categoria = $this->input->get_post('cod_categoria');
			$cod_subcategoria = $this->input->get_post('cod_subcategoria');
			// filtrar, crear una vista en vez de usar tabla con todos los datos
			if ($usuariocodgernow != 998)
			{
				$tablaregistros = "registro_gastos_".$userintran."";
				$this->db->trans_strict(TRUE); // todo o nada
				$this->db->trans_begin();
					$sqltableviewbyentidadporusuario = "
					DROP TABLE IF EXISTS ".$tablaregistros." ;";
				if ($this->db->trans_status() === FALSE)
					$this->db->trans_rollback();
				else
					$this->db->trans_commit();
				$this->db->trans_begin();
					$this->db->query($sqltableviewbyentidadporusuario);
					$sqltableviewbyentidadporusuario = "
					CREATE TABLE IF NOT EXISTS `".$tablaregistros."`
					SELECT registro_gastos.*
					FROM (`registro_gastos`)
					WHERE
					 cod_registro <> ''
					 AND registro_gastos.cod_entidad >= '400' AND registro_gastos.cod_entidad <= '990'
					";
				if ( $cod_entidad != '')
					$sqltableviewbyentidadporusuario .= " AND registro_gastos.cod_entidad = '".$cod_entidad."' ";
				if ( $cod_categoria != '')
					$sqltableviewbyentidadporusuario .= " AND registro_gastos.cod_categoria = '".$cod_categoria."' ";
				if ( $cod_subcategoria != '')
					$sqltableviewbyentidadporusuario .= " AND registro_gastos.cod_subcategoria = '".$cod_subcategoria."' ";
				if ( $des_detallelike != '')
					$sqltableviewbyentidadporusuario .= " AND registro_gastos.des_concepto LIKE '%".$des_concepto."%' ";
				if ( $fec_registroini != '')
					$sqltableviewbyentidadporusuario .= " AND CONVERT(fecha_registro,UNSIGNED) >= ".$fec_registroini." ";
				if ( $fec_registrofin != '')
					$sqltableviewbyentidadporusuario .= " AND CONVERT(fecha_registro,UNSIGNED) <= ".$fec_registrofin." ";
				if ( $mon_registroigual != '')
					$sqltableviewbyentidadporusuario .= " AND registro_gastos.mon_registro <= ".$mon_registroigual." ";
				if ( $mon_registromayor != '')
					$sqltableviewbyentidadporusuario .= " AND registro_gastos.mon_registro >= ".$mon_registromayor." ";
				$sqltableviewbyentidadporusuario .= " ";
				$this->db->query($sqltableviewbyentidadporusuario);
				if ($this->db->trans_status() === FALSE)
					$this->db->trans_rollback();
				else
					$this->db->trans_commit();
				if (! $this->db->table_exists($tablaregistros) )
					{
						$data['output'] = "Error ejecutando su filtro, repita el proceso, si persiste consulte systemas";
						$this->load->view('header.php',$data);
						$this->load->view('cargargastoadministrativo.php',$data);
						$this->load->view('footer.php',$data);
						return;
					}
			}
			else
				$tablaregistros = "registro_gastos";
		}
		else
		{
			$data['output'] = "Accion ejecutada incompleta, repita el proceso";
			$this->load->view('header.php',$data);
			$this->load->view('cargargastoadministrativo.php',$data);
			$this->load->view('footer.php',$data);
			return;
		}
		$this->load->helper(array('inflector','url'));
		$data['seguir']=$this->uri->segment(1).$this->uri->segment(2).$this->uri->segment(3);
		// CONFIGURACION DE FILTROS SEGUN FORMULARIO PARA EL QUERY
        //if ( $this->uri->segment(1) != 'todos');
		//	$sqlreportegasto .= " and CONVERT(fecha_registro,UNSIGNED INTEGER) >= CONVERT('".date('Ymd',strtotime('-1 month'))."',UNSIGNED INTEGER) ";
		// filters if exist
		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
		$crud->set_table($tablaregistros);
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->set_primary_key('cod_registro');
			if ($usuariocodgernow == 998)
			{
				if ( $cod_entidad != '')
					$crud->where($tablaregistros.'.cod_entidad' ,$cod_entidad);
				if ( $cod_categoria != '')
					$crud->where($tablaregistros.'.cod_categoria', $cod_categoria);
				if ( $cod_subcategoria != '')
					$crud->where($tablaregistros.'.cod_subcategoria',$cod_subcategoria);
				if ( $des_detallelike != '')
					$crud->where('des_concepto',$des_concepto);
				if ( $fec_registroini != '')
					$crud->where('CONVERT(fecha_registro,UNSIGNED) >= ',$fec_registroini);
				if ( $fec_registrofin != '')
					$crud->where('CONVERT(fecha_registro,UNSIGNED) <= ',$fec_registrofin);
				if ( $mon_registroigual != '')
					$crud->where('mon_registro <= ',$mon_registroigual);
				if ( $mon_registromayor != '')
					$crud->where('mon_registro >= ',$mon_registromayor);
			}
			else
			{
				$crud->where($tablaregistros.'.cod_categoria > ', '399');
				$crud->where($tablaregistros.'.cod_categoria < ', '990');
			}
		$crud->set_subject('Gasto');
		$crud
			 ->display_as('cod_registro','Codigo')
			 ->display_as('cod_entidad','Centro')
			 ->display_as('cod_categoria','Categoria')
			 ->display_as('cod_subcategoria','Subcategoria')
			 ->display_as('mon_registro','Monto')
			 ->display_as('des_concepto','Concepto')
			 ->display_as('des_detalle','Detalles')
			 ->display_as('des_estado','Justificacion')
			 ->display_as('fecha_concepto','Fecha<br>Concepto')
			 ->display_as('fecha_registro','Fecha<br>Registro')
			 ->display_as('tipo_gasto','Tipo')
			 ->display_as('factura1_num','Factura<br>Numero')
			 ->display_as('factura1_rif','Factura<br>Rif')
			 ->display_as('factura1_bin','Factura<br>Escaneada')
		//	 ->display_as('cod_fondo','Fondo')
			 ->display_as('sessionflag','Modificado')
			 ->display_as('sessionficha','Creador');
		$crud->columns('fecha_registro','cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','fecha_concepto','tipo_gasto','estado','des_estado','factura1_num','factura1_rif','factura1_bin','cod_registro','sessionficha','sessionflag');
		$crud->add_fields('fecha_registro','fecha_concepto','cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','tipo_gasto','estado','factura1_num','factura1_rif','factura1_bin','cod_registro','sessionficha');
		$crud->edit_fields('fecha_registro','fecha_concepto','cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','tipo_gasto','estado','des_estado','factura1_num','factura1_rif','factura1_bin','cod_registro','sessionflag');
		$crud->set_relation('cod_entidad','entidad','{des_entidad}'); //,'{des_entidad}<br> ({cod_entidad})'
		$crud->set_relation('cod_categoria','categoria','{des_categoria}'); // ,'{des_categoria}<br> ({cod_categoria})'
		$crud->set_relation('cod_subcategoria','subcategoria','{des_subcategoria}'); // ,'{des_subcategoria}<br> ({cod_subcategoria})'
		$data['addde'] = $usuariocodgernow;
		if ($usuariocodgernow < 990 and $usuariocodgernow > 998 )
		{
			$crud->unset_add();
			$crud->unset_edit();
			$crud->unset_delete();
		}
		else if ($usuariocodgernow >= 990 and $usuariocodgernow < 998 )
		{
			$crud->unset_add();
			$crud->unset_delete();
		}
		else if ($usuariocodgernow != 998 )
			$crud->unset_operations();
		$crud->required_fields('cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','tipo_gasto','des_estado');
		$directoriofacturas = 'appweb/archivoscargas/' . date("Y") . '/' .date("Ym");
		if ( ! is_dir($directoriofacturas) )
		{
			if ( is_file($directoriofacturas) )
				unlink($directoriofacturas);
			mkdir($directoriofacturas, 0777, true);
			chmod($directoriofacturas,0777);
		}
		$crud->set_field_upload('factura1_bin',$directoriofacturas);
		//$crud->field_type('cod_registro', 'readonly'); // esto no se puede si ya se hizo algo antes
		$crud->set_rules('des_concepto', 'Concepto', 'trim|alphanumeric');
		$crud->set_rules('mon_registro', 'Monto', 'trim|decimal');
		$crud->set_rules('cod_entidad', 'Centro de Costo', 'trim|alphanumeric');
		$crud->field_type('sessionficha', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		$crud->field_type('sessionflag', 'invisible',''.date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username'));
		$crud->field_type('tipo_gasto','dropdown',array('EGRESO' => 'EGRESO', 'CONTRIBUYENTE' => 'CONTRIBUYENTE'));
		$crud->field_type('des_detalle','text');
		$crud->field_type('des_estado','text');
		$crud->unset_texteditor('des_detalle');
		$crud->unset_texteditor('des_estado');
		$currentState = $crud->getState();
		if($currentState == 'add')
		{
			$crud->field_type('fecha_registro', 'invisible',date("Ymd"));
			$crud->callback_add_field('cod_registro', function () {	return '<input type="text" maxlength="50" value="GAS'.date("YmdHis").'" name="cod_registro" readonly="true">';	});
			$crud->callback_add_field('fecha_concepto', function () {	$fecha_concepto=date('Ymd');	$idfeccon='fecha_concepto';	$valoresinputfechacon = array('name'=>$idfeccon,'id'=>$idfeccon, 'onclick'=>'javascript:NewCssCal(\''.$idfeccon.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfeccon, $$idfeccon));	return form_input($valoresinputfechacon);	});
			if ($usuariocodgernow >= 990 or $usuariocodgernow < 10 )
			{
				$crud->field_type('estado', 'invisible','PENDIENTE');
				$crud->required_fields('cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','tipo_gasto');
			}
		}
		else if ($currentState == 'edit')
		{
			$crud->field_type('fecha_registro', 'readonly');
			$crud->field_type('cod_registro', 'readonly'); // esto no se puede si ya se hizo algo antes
			$crud->callback_edit_field('fecha_concepto', function () {	$fecha_concepto=date('Ymd');	$idfeccon='fecha_concepto';	$valoresinputfechacon = array('name'=>$idfeccon,'id'=>$idfeccon, 'onclick'=>'javascript:NewCssCal(\''.$idfeccon.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfeccon, $$idfeccon));	return form_input($valoresinputfechacon);	});
			if ($usuariocodgernow >= 990 or $usuariocodgernow < 10 )
			{
				$crud->field_type('estado','dropdown',array('APROBADO' => 'APROBADO', 'PENDIENTE' => 'PENDIENTE', 'RECHAZADO' => 'RECHAZADO'));
				$crud->required_fields('cod_entidad','cod_categoria','cod_subcategoria','mon_registro','des_concepto','tipo_gasto','des_estado');
			}
			//$crud->field_type('cod_entidad', 'readonly'); // tiendas no editan entidad, viene asignada
			//$crud->field_type('cod_categoria', 'readonly');
			//$crud->field_type('cod_subcategoria', 'readonly');
		}
		$crud->callback_before_update(array($this,'echapajacuando'));
		$crud->callback_before_insert(array($this,'generarcodigo'));
		$crud->callback_column('sessionflag',array($this,'_callback_verusuario'));
		$crud->callback_column('sessionficha',array($this,'_callback_verusuario'));

		$this->load->library('gc_dependent_select');
		$configfielsjoin = array(
			'cod_categoria' => array('table_name' => 'categoria','title' => 'des_categoria','relate' => null), // categoria es sin relacion
			'cod_subcategoria' => array('table_name'=>'subcategoria','title'=>'des_subcategoria','id_field'=>'cod_subcategoria','relate' => 'cod_categoria','data-placeholder' => 'Seleccione primero categoria')
			);
		$configtablejoin = array(
			'main_table' => 'registro_gastos',
			'main_table_primary' => 'cod_registro',
			'url' => base_url() . 'index.php/' . strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__) . '/'	//'ajax_loader' => base_url() . 'style/images/'. 'ajax-loader.gif'//'segment_name' =>'Your_segment_name' // It's an optional parameter. by default "get_items"
		);
		$outputjoincatysubcat = new gc_dependent_select($crud, $configfielsjoin, $configtablejoin);
		$output = $crud->render();
		$output->output.= $outputjoincatysubcat->get_js();
		// TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'cargardatosadminnistrativosfiltrados';
		$this->load->view('header.php',$data);
		$this->load->view('cargargastoadministrativo.php',$output);
		$this->load->view('footer.php',$data);
	}

	/* ver quien hizo la carga en cada columna de la tabla de registros mostrada */
	function _callback_verusuario($value, $row)
	{
		if ($value != '' )
		{
			$usuariover = explode('.',$value);	$intranet = '';
			if (isset($usuariover[1]))
			{	if ($usuariover[1] != null)
				{
					$sqlquien = "select intranet from usuarios where intranet = '".$usuariover[1]."'";
					$sqlquienresult = $this->db->query($sqlquien);
					$intranet = '';
			//if ($sqlquienresult->num_rows() > 0)
					foreach ($sqlquienresult->result() as $row)
						$intranet = $row->intranet;
					return "<a href='".site_url('admusuariosentidad/admusuariosavanzado/read/'.$intranet)."'>$value</a>";
				}
			}else
				return $value;
		}
	}

	/* llamar preupdate antes actualizar adiciona quien esta realizando la actualizacion */
	function echapajacuando($post_array, $primary_key)
	{
		$post_array['sessionflag'] = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
		// TODO: insert para tabla log
		return $post_array;
	}

	/* antes de cada insercion se autogenera el codigo de nuevo, y a que hora lo hace, por si tardo mucho rellenando los datos se genera con hora exacta */
	function generarcodigo($post_array)
	{
		$fec_registro=date('Ymd');
		$post_array['cod_registro'] = 'GAS'.date("YmdHis");
		$post_array['recha_registro'] = $fec_registro;
		$post_array['sessionficha'] = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
	//	$post_array['fecha_registro'] = date_format(date_create($post_array['fec_registro']),'Ymd');
		// TODO: insert para tabla log
		return $post_array;
	}
}
