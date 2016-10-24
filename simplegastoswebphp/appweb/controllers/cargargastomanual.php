<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** controlador de carga de gastos inicial sin crud para vistas, usado en emergencias */
class Cargargastomanual extends CI_Controller {

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

	/** verifica la sesion segun nuestra logica el flag logeado debe estar presente y tener el objeto session */
	public function _verificarsesion()
	{
		if( $this->session->userdata('logueado') != TRUE)
			redirect('manejousuarios/desverificarintranet');
	}

	/**
	 * Index llama por defecto la entrada de llenar datos de ingreso de un gsto
	 */
	public function index()
	{
		$this->_verificarsesion();
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastomanualindex';
		$this->load->view('header.php',$data);
		$this->load->view('cargargastomanual.php',$data);
		$this->load->view('footer.php',$data);
	}

	public function gastomanualcargaruno()
	{
		$this->_verificarsesion();
		$usuariocodgernow = $this->session->userdata('cod_entidad');

		// ########## ini cargar y listaar las CATEGORIAS que se usaran para registros
		$sqlcategoria = " select ifnull(cod_categoria,'99999999999999') as cod_categoria, ifnull(des_categoria,'sin_descripcion') as des_categoria
		 from categoria where ifnull(cod_categoria, '') <> '' and cod_categoria <> '' ";
		if ( $usuariocodgernow != 998 or ( $usuariocodgernow > 399 and $usuariocodgernow < 998) )
			$sqlcategoria .= " and SUBSTRING(cod_categoria,12) NOT LIKE '1200%' "; // TODO "NOT LIKE" es mysql solamente
		$sqlcategoria .= " ORDER BY des_categoria DESC ";
		$resultadoscategoria = $this->db->query($sqlcategoria);
		$arreglocategoriaes = array(''=>'');
		foreach ($resultadoscategoria->result() as $row)
			$arreglocategoriaes[''.$row->cod_categoria] = '' . $row->des_categoria;
		$data['list_categoria'] = $arreglocategoriaes; // agrega este arreglo una lista para el combo box

		// ########## ini cargar y listaar las SUBCATEGORIAS que se usaran para registros
		$sqlsubcategoria = "
		SELECT ifnull(ca.cod_categoria,'99999') as cod_categoria,
		ca.des_categoria,  sb.cod_subcategoria,  sb.des_subcategoria
		FROM categoria as ca join subcategoria as sb on sb.cod_categoria = ca.cod_categoria ";
		if ( $usuariocodgernow != 998 or ( $usuariocodgernow > 399 and $usuariocodgernow < 998) )
			$sqlsubcategoria .= " and SUBSTRING(sb.cod_categoria,12) NOT LIKE '1200%' ";
		$sqlsubcategoria .= " ORDER BY ca.des_categoria DESC ";
		$resultadossubcategoria = $this->db->query($sqlsubcategoria);
		$arreglosubcategoriaes = array(''=>'');
		foreach ($resultadossubcategoria->result() as $row)
			$arreglosubcategoriaes[''.$row->cod_subcategoria] = $row->des_categoria . ' - ' . $row->des_subcategoria;
		$data['list_subcategoria'] = $arreglosubcategoriaes; // agrega este arreglo una lista para el combo box

		// ########## ini cargar y listaar EL TIPO DE GASTO que se usaran para registros
		$tipo_gasto = array( 'EGRESO' => 'EGRESO', 'CONTRIBUYENTE' => 'CONTRIBUYENTE');
		$data['tipo_gasto'] = $tipo_gasto;

		// ########## ini cargar y listaar las UBICACIONES/ENTIDADES que se usaran para registros
		$sqlentidad = " select abr_entidad, abr_zona, des_entidad, ifnull(cod_entidad,'99999999999999') as cod_entidad
		from entidad where ifnull(cod_entidad, '') <> '' and cod_entidad <> '' ";
		if ( $usuariocodgernow != 998 or ( $usuariocodgernow > 399 and $usuariocodgernow < 998) )
			$sqlentidad .= " and cod_entidad = '".$usuariocodgernow."'";
		else
			$arregloentidades = array(''=>'');
		$resultadosentidad = $this->db->query($sqlentidad);
		foreach ($resultadosentidad->result() as $row)
			$arregloentidades[$row->cod_entidad] = $row->abr_entidad .' - ' . $row->des_entidad . ' ('. $row->abr_zona .')';
		$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box

		// ########## ini cargar y listaar las UBICACIONES/ENTIDADES que se usaran para registros
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastomanualcargaruno';
		$this->load->view('header.php',$data);
		$this->load->view('cargargastomanual.php',$data);
		$this->load->view('footer.php',$data);
	}
	// ********* FIN carargastomanual uno solo ***************************

	public function gastomanualcargarunolisto()
	{
		$this->_verificarsesion();
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastomanualfiltrardouno';
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		$userintran = $this->session->userdata('intranet');

		$sessionflag1 = date("YmdHis") . $usuariocodgernow . '.' .$this->session->userdata('username');
		// ******* OBTENER DATOS DE FORMULARIO ***************************** /
		$fecha_concepto = $this->input->get_post('fecha_concepto');
		$mon_registro = $this->input->get_post('mon_registro');
		$des_concepto = $this->input->get_post('des_concepto');
		$tipo_gasto = $this->input->get_post('tipo_gasto');
		$factura1_num = $this->input->get_post('factura1_num');
		$factura1_rif = $this->input->get_post('factura1_rif');
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_subcategoria = $this->input->get_post('cod_subcategoria');

		// ******* GENERACION de la carga id codigo de registro
		$fecha_registro = date('Ymd');
		$cod_registro = 'GAS' . $fecha_registro . date('His');
		// ******* CARGA DEL ARCHIVO ****************** */
		$directoriofacturas = CATAPATH .'appweb/archivoscargas/' . date("Y") . '/' .date("Ym");
		if ( ! is_dir($directoriofacturas) )
		{
			if ( is_file($directoriofacturas) )
				unlink($directoriofacturas);
			mkdir($directoriofacturas, 0777, true);
			chmod($directoriofacturas,0777);
		}
		$cargaconfig['upload_path'] = $directoriofacturas;
		$cargaconfig['allowed_types'] = 'gif|jpg|png|pdf|ods';
		$cargaconfig['max_size']  = 0;  //$cargaconfig['max_size']= '100'; // en kilobytes
		$cargaconfig['max_width'] = 0;
		$cargaconfig['max_height'] = 0;
		//$cargaconfig['remove_spaces'] = true;
		$cargaconfig['encrypt_name'] = TRUE;
		$this->load->helper(array('form', 'url','inflector'));
		$this->load->library('upload', $cargaconfig);
		$this->upload->initialize($cargaconfig);
		$this->upload->overwrite = true;
		if ( $this->upload->do_upload('factura1_bin')) // nombre del campo alla en el formulario
		{
			$conadjunto = TRUE;
			$factura1_data = $this->upload->data();
			$factura1_bin = $factura1_data['full_path'];
		}
		else
		{
			$conadjunto = FALSE;
			$factura1_bin = "S/A";
		}
		$data['factura1_bin'] = $factura1_bin;
		$resultadocarga = array('Error, no se completo el proceso', 'Sin datos', '0', '', '', '', '');
		// ******* procesar el registro sin el adjunto
		$sqlregistrargasto = "
            INSERT INTO registro_gastos
			(
			   cod_registro, cod_entidad,
			   cod_subcategoria, cod_categoria,
			   des_concepto, mon_registro,
			   fecha_registro, fecha_concepto,
			   sessionficha, tipo_gasto, estado,
			   factura1_num, factura1_bin, factura1_rif
			 )
			VALUES
			(
			  '".$cod_registro."', '".$cod_entidad."',
			  '".$cod_subcategoria."',(SELECT cod_categoria FROM subcategoria where cod_subcategoria = '".$cod_subcategoria."' LIMIT 1),
			  '".$des_concepto."', '".$mon_registro."',
			  '".$fecha_registro."', '".$fecha_concepto."',
			  '".$sessionflag1."','".$tipo_gasto."','PENDIENTE',
			  '".$factura1_num."','".$factura1_bin."','".$factura1_rif."'
			)";
		$this->db->query($sqlregistrargasto);
        // TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$this->table->clear();
		$tmplnewtable = array ( 'table_open'  => '<table border="1" cellpadding="1" cellspacing="0" class="table containerblue tablelist">' );
		$this->table->set_caption(null);
		$this->table->set_template($tmplnewtable);
		$this->table->set_heading(
			'Registro',
			'Monto','Tipo','Realizado el','Del dia',
			'Adjunto'
		);
		$this->table->add_row(
			$cod_registro, $mon_registro,
			$tipo_gasto,$fecha_registro,$fecha_concepto,
			anchor_popup($factura1_bin,$factura1_bin,array('width'=>'800','height'=>'600','resizable'=>'yes')));
		$data['htmltablacargasregistros'] = $this->table->generate();
		$data['menu'] = $this->menu->general_menu();
		$data['cod_registro'] = $cod_registro;
		$data['accionejecutada'] = 'gastomanualfiltrardouno';
		$data['upload_errors'] = $this->upload->display_errors('<p>', '</p>');
		$this->load->helper(array('form', 'url','html'));
		$this->load->view('header.php',$data);
		$this->load->view('cargargastomanual.php',$data);
		$this->load->view('footer.php',$data);
	}

	// ############# INI gastomanualrevisarlos revisa todos los gstos crud bonito ############
	public function gastomanualrevisarlos()
	{
		$this->_verificarsesion();
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastomanualrevisarlos';
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		$userintran = $this->session->userdata('intranet');

		// ******* ini nombres de tablas para filtrar los datos:
		$segurodelatabla = rand(6,9);
		//$segurodelatabla = date("YmdHis");
		$tablaentidades = "entidad_" . $userintran . $segurodelatabla;
		$tablacategoria = "categoria_" . $userintran . $segurodelatabla;
		$tablasubcatego = "subcategoria_" . $userintran . $segurodelatabla;
		$tablaregistros = "registro_gastos_".$userintran . $segurodelatabla;

		// ****** ini limpieza de tablas antes de mostrar y despues que se muestra mas abajo al final
		$this->db->trans_strict(TRUE); // todo o nada
		$this->db->trans_begin();
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablaentidades." ;";
			$this->db->query($sqldatostablasfiltrados);
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablacategoria." ;";
			$this->db->query($sqldatostablasfiltrados);
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablasubcatego." ;";
			$this->db->query($sqldatostablasfiltrados);
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablaregistros.";";
			$this->db->query($sqldatostablasfiltrados);
			if ($this->db->trans_status() === FALSE)
			{
				$this->db->trans_rollback();
				$data['output'] = "Error ejecutando su filtro, repita el proceso, si persiste consulte systemas";
				$this->load->view('header.php',$data);
				$this->load->view('cargargastoadministrativo.php',$data);
				$this->load->view('footer.php',$data);
				return;
			}
			else
				$this->db->trans_commit();
		// ****** fin limpieza de tablas antes de mostrar y despues que se muestra mas abajo al final

		// ****** ini post/get si vino algun filtro tomo los valores
		$fec_registroini = $this->input->get_post('fec_registroini');
		$fec_registrofin = $this->input->get_post('fec_registrofin');
		$mon_registroigual = $this->input->get_post('mon_registroigual');
		$mon_registromayor = $this->input->get_post('mon_registromayor');
		$des_registrolike = $this->input->get_post('des_registrolike');
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_categoria = $this->input->get_post('cod_categoria');
		$cod_subcategoria = $this->input->get_post('cod_subcategoria');
		// ****** fin post/get si vino algun filtro tomo los valores

		// ****** ini relleno una tabla para mostrar solo en el crud lo relaciona
		$this->db->trans_begin();
			$sqltablagastousr = "
				CREATE TABLE IF NOT EXISTS `".$tablaregistros."` SELECT registro_gastos.* FROM (`registro_gastos`)
				WHERE cod_registro <> ''
				and cod_entidad = '".$usuariocodgernow."' and SUBSTRING(cod_categoria,12) NOT LIKE '1200%'
				AND CONVERT(SUBSTRING(cod_registro,4,6),UNSIGNED) > ".date('Ym',strtotime("-1 month"))."
				AND CONVERT(SUBSTRING(cod_registro,4,6),UNSIGNED) <= ".date('Ym')." ";
				if ( $cod_categoria != '')		$sqltablagastousr .= " AND registro_gastos.cod_categoria = '".$this->db->escape_str($cod_categoria)."' ";
				if ( $cod_subcategoria != '')	$sqltablagastousr .= " AND registro_gastos.cod_subcategoria = '".$this->db->escape_str($cod_subcategoria)."' ";
				if ( $des_registrolike != '')	$sqltablagastousr .= " AND registro_gastos.des_concepto LIKE '%".$this->db->escape_str($des_concepto)."%' ";
				if ( $fec_registroini != '')	$sqltablagastousr .= " AND CONVERT(fecha_registro,UNSIGNED) >= ".$this->db->escape_str($fec_registroini)." ";
				if ( $fec_registrofin != '')	$sqltablagastousr .= " AND CONVERT(fecha_registro,UNSIGNED) <= ".$this->db->escape_str($fec_registrofin)." ";
				if ( $mon_registroigual != '')	$sqltablagastousr .= " AND registro_gastos.mon_registro <= ".$this->db->escape_str($mon_registroigual)." ";
				if ( $mon_registromayor != '')	$sqltablagastousr .= " AND registro_gastos.mon_registro >= ".$this->db->escape_str($mon_registromayor)." ";
		$sqltablaentidadusr = "
			CREATE TABLE IF NOT EXISTS  `".$tablaentidades."`
			select cod_entidad, abr_entidad, abr_zona, des_entidad
			from entidad where ifnull(cod_entidad, '') <> '' and cod_entidad <> '' ";
			if ( $usuariocodgernow != 998 or ( $usuariocodgernow > 399 and $usuariocodgernow < 998) )
				$sqltablaentidadusr .= "and cod_entidad = '".$usuariocodgernow."' ";
		$sqltablacategorias = "
			CREATE TABLE IF NOT EXISTS  `".$tablacategoria."` (primary key (cod_categoria))
			select ifnull(cod_categoria,'99999999999999') as cod_categoria, ifnull(des_categoria,'sin_descripcion') as des_categoria
			from categoria where ifnull(cod_categoria, '') <> '' and cod_categoria <> ''  ";
			if ( $usuariocodgernow != 998 or ( $usuariocodgernow > 399 and $usuariocodgernow < 998) )
				$sqltablacategorias .= " and SUBSTRING(cod_categoria,12) NOT LIKE '1200%' ";
			$sqltablacategorias .= " ORDER BY des_categoria DESC ";
		$sqltablasubcatego = "
			CREATE TABLE IF NOT EXISTS  `".$tablasubcatego."` (primary key (cod_subcategoria))
			SELECT ifnull(ca.cod_categoria,'99999') as cod_categoria,
			ca.des_categoria as des_categoria,  sb.cod_subcategoria as cod_subcategoria,  sb.des_subcategoria  as des_subcategoria
			FROM categoria as ca join subcategoria as sb on sb.cod_categoria = ca.cod_categoria ";
			if ( $usuariocodgernow != 998 or ( $usuariocodgernow > 399 and $usuariocodgernow < 998) )
				$sqltablasubcatego .= " and SUBSTRING(sb.cod_categoria,12) NOT LIKE '1200%' ";
			$sqltablasubcatego .= " ORDER BY ca.des_categoria DESC ";
		// ejecutsamos los querys que crean las 4 tablas a usar en el crud con datos ya filtrados
		$this->db->query($sqltablagastousr);
		$this->db->query($sqltablaentidadusr);
		$this->db->query($sqltablacategorias);
		$this->db->query($sqltablasubcatego);
		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$data['output'] = "Error ejecutando su filtro, repita el proceso, si persiste consulte systemas";
			$this->load->view('header.php',$data);
			$this->load->view('cargargastoadministrativo.php',$data);
			$this->load->view('footer.php',$data);
			return;
		}
		else
			$this->db->trans_commit();
		// ****** fin relleno de tablas para mostrar solo en el crud lo relacionado unicamente

		// ****** ini mostrar vita crud para todos los registros del mes actual y el mes anterior
		$this->load->helper(array('inflector','url'));
		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
		$crud->set_table($tablaregistros);
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->set_primary_key('cod_registro');
		$crud->display_as('cod_registro','Codigo')
			 ->display_as('cod_entidad','Centro')
			 ->display_as('cod_categoria','Categoria')
			 ->display_as('cod_subcategoria','Subcategoria')
			 ->display_as('mon_registro','Monto')
			 ->display_as('des_concepto','Concepto')
			 ->display_as('des_detalle','Detalles')
			 ->display_as('des_estado','Justificacion')
			 ->display_as('fecha_concepto','Fecha<br>Gasto')
			 ->display_as('fecha_registro','Fecha<br>Registro')
			 ->display_as('tipo_gasto','Tipo')
			 ->display_as('factura1_num','Factura<br>Numero')
			 ->display_as('factura1_rif','Factura<br>Rif')
			 ->display_as('factura1_bin','Factura<br>Escaneada')
		//	 ->display_as('cod_fondo','Fondo')
			 ->display_as('sessionflag','Modificado')
			 ->display_as('sessionficha','Creador');
		$crud->columns('fecha_registro','fecha_concepto','cod_categoria','cod_subcategoria','mon_registro','des_concepto','tipo_gasto','estado','des_estado','factura1_num','factura1_rif','factura1_bin','cod_registro','sessionficha','sessionflag');
		$crud->set_relation('cod_entidad',$tablaentidades,'{des_entidad}'); //,'{des_entidad}<br> ({cod_entidad})'
		$crud->set_relation('cod_categoria',$tablacategoria,'{des_categoria}'); // ,'{des_categoria}<br> ({cod_categoria})'
		$crud->set_relation('cod_subcategoria',$tablasubcatego,'{des_subcategoria}'); // ,'{des_subcategoria}<br> ({cod_subcategoria})'
		$crud->unset_operations();
		$directoriofacturas = 'appweb/archivoscargas/' . date("Y") . '/' .date("Ym");
		if ( ! is_dir($directoriofacturas) )
		{
			if ( is_file($directoriofacturas) )
				unlink($directoriofacturas);
			mkdir($directoriofacturas, 0777, true);
			chmod($directoriofacturas,0777);
		}
		$crud->set_field_upload('factura1_bin',$directoriofacturas);
		//$crud->callback_column('sessionficha',array($this,'_callback_verusuario'));
		//$crud->callback_column('sessionflag',array($this,'_callback_verusuario'));
		$output = $crud->render();

		// TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastomanualrevisarlos';
		$data['js_files'] = $output->js_files;
		$data['css_files'] = $output->css_files;
		$data['output'] = $output->output;
		$this->load->view('header.php',$data);
		$this->load->view('cargargastomanual.php',$data);
		$this->load->view('footer.php',$data);

		// ******** limpiar de las tablas "disque temporales" con datos filtrados para hacer crud
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablaentidades." ;";
			$this->db->query($sqldatostablasfiltrados);
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablacategoria." ;";
			$this->db->query($sqldatostablasfiltrados);
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablasubcatego." ;";
			$this->db->query($sqldatostablasfiltrados);
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablaregistros.";";
			$this->db->query($sqldatostablasfiltrados);
	}

	/* ver quien hizo la carga en cada columna de la tabla de registros mostrada */
	function _callback_verusuario($value, $row)
	{
	/*	if ($value != '' )
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
			}else*/
				return $value;
		//}
	}

}
