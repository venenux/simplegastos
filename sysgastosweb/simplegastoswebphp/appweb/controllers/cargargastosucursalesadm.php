<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** controlador de carga de gastos inicial sin crud para vistas, usado en emergencias */
class cargargastosucursalesadm extends CI_Controller {

	private $usuariologin, $sessionflag, $usuariocodger, $acc_lectura, $acc_escribe, $acc_modifi;
	private $nivel = "ninguno";

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
			else if( $usuariocodgernow > 399 and $usuariocodgernow < 998 )
				$this->nivel = 'sucursal';
			else if ( $usuariocodgernow == '163' or $usuariocodgernow == '251' )
				$this->nivel = 'contabilidad';
			else
				$this->nivel = 'especial';
		}
	}

	/**
	 * Index llama por defecto la entrada de llenar datos de ingreso de un gsto
	 */
	public function index()
	{
		$this->_verificarsesion();
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastosucursalesindex';	// para cargar parte especifica de la vista envio un parametro accion
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
		$this->load->view('header.php',$data);
		$this->load->view('cargargastosucursales.php',$data);
		$this->load->view('footer.php',$data);
	}

	public function gastomanualcargaruno($mens = NULL)
	{
		$this->_verificarsesion();
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		$data['nivel'] = $this->session->userdata('cod_entidad');

		// ########## ini cargar y listaar las CATEGORIAS que se usaran para registros
		$sqlcategoria = " select ifnull(cod_categoria,'99999999999999') as cod_categoria, ifnull(des_categoria,'sin_descripcion') as des_categoria
		 from categoria where ifnull(cod_categoria, '') <> '' and cod_categoria <> '' ";
		if ( $this->nivel == 'ninguno' )
			$sqlcategoria .= " and cod_categoria = ''";
		if ( $this->nivel != 'administrador' )
			$sqlcategoria .= " and tipo_categoria <> 'ADMINISTRATIVO' and tipo_categoria NOT LIKE 'ADMINISTRATI%' "; // TODO "NOT LIKE" es mysql solamente
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
		if ( $this->nivel == 'ninguno' )
			$sqlsubcategoria .= " and cod_subcategoria = ''";
		if ( $this->nivel != 'administrador' )
			$sqlsubcategoria .= " and tipo_categoria <> 'ADMINISTRATIVO' and tipo_categoria NOT LIKE 'ADMINISTRATI%' "; // TODO "NOT LIKE" es mysql solamente
		$sqlsubcategoria .= " ORDER BY ca.des_categoria DESC ";
		$resultadossubcategoria = $this->db->query($sqlsubcategoria);
		$arreglosubcategoriaes = array(''=>'');
		foreach ($resultadossubcategoria->result() as $row)
			$arreglosubcategoriaes[''.$row->cod_subcategoria] = $row->des_categoria . ' - ' . $row->des_subcategoria;
		$data['list_subcategoria'] = $arreglosubcategoriaes; // agrega este arreglo una lista para el combo box

		// ########## ini cargar y listaar EL TIPO DE GASTO que se usaran para registros
		$list_factura_tipo = array( 'EGRESO' => 'EGRESO', 'CONTRIBUYENTE' => 'CONTRIBUYENTE');
		$data['list_factura_tipo'] = $list_factura_tipo;

		// ########## ini cargar y listaar EL TIPO DE GASTO que se usaran para registros
		if ( $this->nivel != 'sucursal' )	$list_tipo_concepto = array( 'SUCURSAL' => 'SUCURSAL', 'ADMINISTRATIVO' => 'ADMINISTRATIVO');
		else					$list_tipo_concepto = array( 'SUCURSAL' => 'SUCURSAL');
		$data['list_tipo_concepto'] = $list_tipo_concepto;

		// ########## ini cargar y listaar las UBICACIONES/ENTIDADES que se usaran para registros
		$sqlentidad = " select abr_entidad, abr_zona, des_entidad, ifnull(cod_entidad,'99999999999999') as cod_entidad
		from entidad where ifnull(cod_entidad, '') <> '' and cod_entidad <> '' ";
		if ( $this->nivel == 'ninguno' )
			$sqlentidad .= " and cod_entidad = ''";
		if ( $this->nivel == 'especial' )
			$sqlentidad .= " and tipo_entidad <> 'ADMINISTRATIVO' and tipo_entidad NOT LIKE 'ADMINISTRATI%' ";
		if ( $this->nivel == 'sucursal' or $this->nivel == 'contabilidad' )
			$sqlentidad .= " and cod_entidad = '".$usuariocodgernow."'";
		$arregloentidades = array();
		$resultadosentidad = $this->db->query($sqlentidad);
		foreach ($resultadosentidad->result() as $row)
			$arregloentidades[$row->cod_entidad] = $row->abr_entidad .' - ' . $row->des_entidad . ' ('. $row->abr_zona .')';
		$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box

		// ########## ini cargar y listaar las UBICACIONES/ENTIDADES que se usaran para registros
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastomanualcargaruno';
		$data['mens'] = $mens;
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
		$this->load->view('header.php',$data);
		$this->load->view('cargargastosucursales.php',$data);
		$this->load->view('footer.php',$data);
	}
	// ********* FIN carargastomanual uno solo ***************************

	/** inserta un nuevo gasto en regisro_gasto */
	public function gastomanualcargarunolisto()
	{
		$this->_verificarsesion();
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastomanualfiltrardouno';
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		$userintran = $this->session->userdata('intranet');

		$sessionflag1 = date("YmdHis") . $usuariocodgernow . '.' .$this->session->userdata('username');
		// ******* OBTENER DATOS DE FORMULARIO ***************************** /
		$this->load->library('form_validation');
		$this->form_validation->set_rules('mon_registro', 'Monto (punto o coma SOLO PARA DECIMALES', 'required');
		$this->form_validation->set_rules('des_concepto', 'Concepto o detalle de que gasto', 'required');
		$this->form_validation->set_rules('factura_tipo', 'CONTRIBUYENTE debe ser el tipo si el archivo es factura', 'required');
		$this->form_validation->set_rules('cod_subcategoria', 'Categoria/SubCategoria del gasto', 'required');
		if ( $this->form_validation->run() == FALSE )
		{
			$mens = validation_errors();
			return $this->gastomanualcargaruno( $mens );
		}
		$fecha_concepto = $this->input->get_post('fecha_concepto');
		$dias	= (strtotime(date("Ymd"))-strtotime($fecha_concepto))/86400;
		$dias 	= abs($dias);
		$dias = floor($dias);
		if ( $dias > 12 )
		{
			$mens = "La fecha maxima es 16 dias atras o ser del mes en curso, semana en curso : " . $dias . " dias es muy atras!";
			return $this->gastomanualcargaruno( $mens );
		}
		$mon_registro = $this->input->get_post('mon_registro');
		$des_concepto = $this->input->get_post('des_concepto');
		$tipo_concepto = $this->input->get_post('tipo_concepto');
		$factura_tipo = $this->input->get_post('factura_tipo');
		$factura_num = $this->input->get_post('factura_num');
		$factura_rif = $this->input->get_post('factura_rif');
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_subcategoria = $this->input->get_post('cod_subcategoria');

		// ******* GENERACION de la carga id codigo de registro
		$fecha_registro = date('Ymd');
		$cod_registro = 'GAS' . $fecha_registro . date('His');
		// ******* CARGA DEL ARCHIVO ****************** */
		$directoriofacturas = 'archivoscargas/' . date("Y");
		if ( ! is_dir($directoriofacturas) )
		{
			if ( is_file($directoriofacturas) )
			{	unlink($directoriofacturas);	}
			mkdir($directoriofacturas, 0777, true);
			chmod($directoriofacturas,0777);
		}
		$cargaconfig['upload_path'] = $directoriofacturas;
		$cargaconfig['allowed_types'] = 'gif|jpg|png|jpeg';
		$cargaconfig['max_size']  = 0;  //$cargaconfig['max_size']= '100'; // en kilobytes
		$cargaconfig['max_width'] = 0;
		$cargaconfig['max_height'] = 0;
		//$cargaconfig['remove_spaces'] = true;
		$cargaconfig['encrypt_name'] = TRUE;
		$this->load->library('upload', $cargaconfig);
		$this->load->helper('inflector');
		$this->upload->initialize($cargaconfig);
		$this->upload->do_upload('factura_bin');
		$file_data = $this->upload->data();
		$filenamen = $cod_registro . $file_data['file_ext'];
        $filenameorig =  $file_data['file_path'] . $file_data['file_name'];
        $filenamenewe =  $file_data['file_path'] . $filenamen;
		if ( $factura_tipo == 'CONTRIBUYENTE' )
		{
			if ( $file_data['file_name'] == '' )
			{
				$this->gastomanualcargaruno($mens = '<br>CUADNO ES CONTRIBUYENTE Debe subir un archivo escaneado <br>que avale el gasto que ud esta registrando! REPITA EL PROCESO');
				return;
			}
			else
				rename( $filenameorig, $filenamenewe); // TODO: rename
		}
		if ( is_file($filenamenewe) ) // nombre del campo alla en el formulario
		{
			$conadjunto = TRUE;
			$factura_data = $file_data;
			$factura_bin = $filenamen;
			$linkadjunto = anchor_popup( '../' . $directoriofacturas .'/'. $factura_bin, $factura_bin,array('width'=>'800','height'=>'600','resizable'=>'yes'));
		}
		else
		{
			$conadjunto = FALSE;
			$factura_bin = '';
			$linkadjunto = '';
		}
		$file_data = $this->upload->data();
		$data['file_data'] = $file_data;
		$data['factura_bin'] = $factura_bin;
		$resultadocarga = array('Error, no se completo el proceso', 'Sin datos', '0', '', '', '', '');
		// ******* procesar el registro sin el adjunto
		$sqlregistrargasto = "
            INSERT INTO registro_gastos
			(
			   cod_registro, cod_entidad,
			   cod_subcategoria, cod_categoria,
			   des_concepto, mon_registro,
			   fecha_registro, fecha_concepto,
			   sessionficha, tipo_concepto, estado,
			   factura_tipo, factura_num, factura_bin, factura_rif
			 )
			VALUES
			(
			  '".$cod_registro."', '".$cod_entidad."',
			  '".$cod_subcategoria."',(SELECT cod_categoria FROM subcategoria where cod_subcategoria = '".$cod_subcategoria."' LIMIT 1),
			  '".$this->db->escape_str($des_concepto)."', '".$this->db->escape_str($mon_registro)."',
			  '".$fecha_registro."', '".$fecha_concepto."',
			  '".$sessionflag1."','".$tipo_concepto."','PENDIENTE',
			  '".$factura_tipo."','".$this->db->escape_str($factura_num)."','".$factura_bin."','".$this->db->escape_str($factura_rif)."'
			)";
		$this->db->query($sqlregistrargasto);
        // TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$this->table->clear();
		$tmplnewtable = array ( 'table_open'  => '<table border="1" cellpadding="1" cellspacing="0" class="table containerblue tablelist">' );
		$this->table->set_caption(null);
		$this->table->set_template($tmplnewtable);
		$this->table->set_heading(
			'Registro','Descripcion',
			'Monto','Gasto','Realizado el','Del dia',
			'Adjunto'
		);
		$this->table->add_row(
			$cod_registro, $this->db->escape_str($des_concepto), $mon_registro,
			$factura_tipo,$fecha_registro,$fecha_concepto,
			$linkadjunto);
		$data['htmltablacargasregistros'] = $this->table->generate();
		$data['menu'] = $this->menu->general_menu();
		$data['cod_registro'] = $cod_registro;
		$data['accionejecutada'] = 'gastomanualfiltrardouno';
		$data['upload_errors'] = $this->upload->display_errors('<p>', '</p>');
		$this->load->helper(array('form', 'url','html'));
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
		$this->load->view('header.php',$data);
		$this->load->view('cargargastosucursales.php',$data);
		$this->load->view('footer.php',$data);
	}

	public function gastomanualeditaruno($mens = NULL, $codigo = 'ultimo')
	{
		$this->_verificarsesion();
		$usuariocodgernow = $this->session->userdata('cod_entidad');

		// ########## ini cargar y listaar las CATEGORIAS que se usaran para registros
		$sqlcategoria = " select ifnull(cod_categoria,'99999999999999') as cod_categoria, ifnull(des_categoria,'sin_descripcion') as des_categoria
		 from categoria where ifnull(cod_categoria, '') <> '' and cod_categoria <> '' ";
		if ( $this->nivel == 'ninguno' )
			$sqlcategoria .= " and cod_categoria = ''";
		if ( $this->nivel != 'administrador' )
			$sqlcategoria .= " and tipo_categoria <> 'ADMINISTRATIVO' and tipo_categoria NOT LIKE 'ADMINISTRATI%' "; // TODO "NOT LIKE" es mysql solamente
		$sqlcategoria .= " ORDER BY des_categoria DESC ";
		$resultadoscategoria = $this->db->query($sqlcategoria);
		$arreglocategoriaes = array();
		foreach ($resultadoscategoria->result() as $row)
			$arreglocategoriaes[''.$row->cod_categoria] = '' . $row->des_categoria;
		$data['list_categoria'] = $arreglocategoriaes; // agrega este arreglo una lista para el combo box

		// ########## ini cargar y listaar las SUBCATEGORIAS que se usaran para registros
		$sqlsubcategoria = "
		SELECT ifnull(ca.cod_categoria,'99999') as cod_categoria,
		ca.des_categoria,  sb.cod_subcategoria,  sb.des_subcategoria
		FROM categoria as ca join subcategoria as sb on sb.cod_categoria = ca.cod_categoria ";
		if ( $this->nivel == 'ninguno' )
			$sqlsubcategoria .= " and cod_subcategoria = ''";
		if ( $this->nivel != 'administrador' )
			$sqlsubcategoria .= " and tipo_categoria <> 'ADMINISTRATIVO' and tipo_categoria NOT LIKE 'ADMINISTRATI%' "; // TODO "NOT LIKE" es mysql solamente
		$sqlsubcategoria .= " ORDER BY ca.des_categoria DESC ";
		$resultadossubcategoria = $this->db->query($sqlsubcategoria);
		$arreglosubcategoriaes = array();
		foreach ($resultadossubcategoria->result() as $row)
			$arreglosubcategoriaes[''.$row->cod_subcategoria] = $row->des_categoria . ' - ' . $row->des_subcategoria;
		$data['list_subcategoria'] = $arreglosubcategoriaes; // agrega este arreglo una lista para el combo box

		// ########## ini cargar y listaar EL TIPO DE GASTO que se usaran para registros
		$list_factura_tipo = array( 'EGRESO' => 'EGRESO', 'CONTRIBUYENTE' => 'CONTRIBUYENTE');
		$data['list_factura_tipo'] = $list_factura_tipo;

		// ########## ini cargar y listaar las UBICACIONES/ENTIDADES que se usaran para registros
		$sqlentidad = " select abr_entidad, abr_zona, des_entidad, ifnull(cod_entidad,'99999999999999') as cod_entidad
		from entidad where ifnull(cod_entidad, '') <> '' and cod_entidad <> '' ";
		if ( $this->nivel == 'ninguno' )
			$sqlentidad .= " and cod_entidad = ''";
		if ( $this->nivel == 'especial' )
			$sqlentidad .= " and tipo_entidad <> 'ADMINISTRATIVO' and tipo_entidad NOT LIKE 'ADMINISTRATI%' ";
		if ( $this->nivel == 'sucursal' or $this->nivel == 'contabilidad' )
			$sqlentidad .= " and cod_entidad = '".$usuariocodgernow."'";
		$arregloentidades = array();
		$resultadosentidad = $this->db->query($sqlentidad);
		foreach ($resultadosentidad->result() as $row)
			$arregloentidades[$row->cod_entidad] = $row->abr_entidad .' - ' . $row->des_entidad . ' ('. $row->abr_zona .')';
		$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box

		// ########## ini cargar el detalle del gasto
		$sqlgasto = " SELECT * FROM registro_gastos WHERE cod_registro <> '' ";
		$cod_registro = $this->input->get_post('cod_registro');
		if ( $cod_registro == '' )
			$cod_registro = $codigo;
		if ( $cod_registro != 'ultimo' )
		{
			$sqlgasto .= " and cod_registro = '".$this->db->escape_str($cod_registro)."' ";
		}
		$sqlgasto .= " ORDER BY cod_registro DESC LIMIT 1 ";	// aun si falla trae uno el ultimo
		$resultadogasto = $this->db->query($sqlgasto);
		foreach ($resultadogasto->result() as $rowdata)
			foreach ($rowdata as $rowkey => $valuekey)
				$data[$rowkey] = $valuekey;

		// ########## ini cargar y listaar el gasto editar
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastomanualeditaruno';
		$data['mens'] = $mens;
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
		$this->load->view('header.php',$data);
		$this->load->view('cargargastosucursales.php',$data);
		$this->load->view('footer.php',$data);
	}
	// ********* FIN carargastoeditar uno solo ***************************


	/** muestra despues de edita un gasto en regisro_gasto */
	public function gastomanualeditarunolisto()
	{
		$this->_verificarsesion();
		$usuariocodgernow = $this->session->userdata('cod_entidad');

		$sessionflag1 = date("YmdHis") . $usuariocodgernow . '.' .$this->session->userdata('username');
		// ******* OBTENER DATOS DE FORMULARIO ***************************** /
		$this->load->library('form_validation');
		$cod_registro = $this->input->get_post('cod_registro');
		$this->form_validation->set_rules('mon_registro', 'Monto (punto o coma SOLO PARA DECIMALES', 'required');
		$this->form_validation->set_rules('des_concepto', 'Concepto o detalle de que gasto', 'required');
		$this->form_validation->set_rules('factura_tipo', 'CONTRIBUYENTE debe ser el tipo si el archivo es factura', 'required');
		$this->form_validation->set_rules('cod_subcategoria', 'Categoria/SubCategoria del gasto', 'required');
		if ( $this->form_validation->run() == FALSE )
		{
			$mens = validation_errors();
			return $this->gastomanualeditaruno( $mens, $cod_registro );
		}
		$fecha_concepto = $this->input->get_post('fecha_concepto');
		$dias	= (strtotime(date("Ymd"))-strtotime($fecha_concepto))/86400;
		$dias 	= abs($dias);
		$dias = floor($dias);
		if ( $dias > 16 )
		{
			$mens = "La fecha maxima es 16 dias atras o ser del mes en curso, semana en curso : " . $dias . " dias es muy atras!";
			return $this->gastomanualeditaruno( $mens, $cod_registro );
		}
		$mon_registro = $this->input->get_post('mon_registro');
		$des_concepto = $this->input->get_post('des_concepto');
		$tipo_concepto = $this->input->get_post('tipo_concepto');
		$factura_tipo = $this->input->get_post('factura_tipo');
		$factura_num = $this->input->get_post('factura_num');
		$factura_rif = $this->input->get_post('factura_rif');
		$factura_bin = $this->input->get_post('factura_bin');
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_subcategoria = $this->input->get_post('cod_subcategoria');

		// ******* GENERACION de la carga id codigo de registro
		$cod_registro = $this->input->get_post('cod_registro');
		// ******* CARGA DEL ARCHIVO ****************** */
		$directoriofacturas = 'archivoscargas/' . date("Y");
		if ( ! is_dir($directoriofacturas) )
		{
			if ( is_file($directoriofacturas) )
			{	unlink($directoriofacturas);	}
			mkdir($directoriofacturas, 0777, true);
			chmod($directoriofacturas,0777);
		}
		$cargaconfig['upload_path'] = $directoriofacturas;
		$cargaconfig['allowed_types'] = 'gif|jpg|png|jpeg';
		$cargaconfig['max_size']  = 0;  //$cargaconfig['max_size']= '100'; // en kilobytes
		$cargaconfig['max_width'] = 0;
		$cargaconfig['max_height'] = 0;
		//$cargaconfig['remove_spaces'] = true;
		$cargaconfig['encrypt_name'] = TRUE;
		$this->load->library('upload', $cargaconfig);
		$this->load->helper('inflector');
		$this->upload->initialize($cargaconfig);
		$this->upload->do_upload('factura_binX');
		$file_data = $this->upload->data();
		$filenamen = $cod_registro .'1'. $file_data['file_ext'];
		$filenameorig =  $file_data['file_path'] . $file_data['file_name'];
		$filenamenewe =  $file_data['file_path'] . $filenamen;
			if ( $this->input->get_post('factura_bin') != '' and $file_data['file_name'] == '' )
			{
				$conadjunto = FALSE;
				$factura_bin = $this->input->get_post('factura_bin');
				$linkadjunto = $this->input->get_post('factura_bin');
			}
			else
			{
				if ( $file_data['file_name'] == '' )
				{
					if ( $factura_tipo == 'CONTRIBUYENTE' )
					{
						$this->gastomanualeditaruno($mens = '<br>CUANDO ES CONTRIBUYENTE Debe subir un archivo escaneado <br>que avale el gasto que ud esta registrando! REPITA EL PROCESO', $cod_registro);
						return;
					}
				}
				else
					rename( $filenameorig, $filenamenewe ); // TODO: rename
				if ( is_file($filenamenewe) ) // nombre del campo alla en el formulario
				{
					$conadjunto = TRUE;
					$factura_data = $file_data;
					$factura_bin = $filenamen;
					$linkadjunto = anchor_popup( '../'.$directoriofacturas.'/'. $factura_bin,$factura_bin,array('width'=>'800','height'=>'600','resizable'=>'yes'));
				}
				else
				{
					$conadjunto = FALSE;
					$factura_bin = '';
					$linkadjunto = '';
				}
			}
		$file_data = $this->upload->data();
		$data['file_data'] = $file_data;
		$data['factura_bin'] = $factura_bin;
		$resultadocarga = array('Error, no se completo el proceso', 'Sin datos', '0', '', '', '', '');
		// ******* procesar el registro sin el adjunto
		$sqlregistrargasto = "
            UPDATE gastossystema.registro_gastos
				SET
					cod_entidad='".$cod_entidad."',
					cod_categoria=(SELECT cod_categoria FROM subcategoria where cod_subcategoria = '".$cod_subcategoria."' LIMIT 1),
					cod_subcategoria='".$cod_subcategoria."',
					mon_registro='".$this->db->escape_str($mon_registro)."',
					des_concepto='".$this->db->escape_str($des_concepto)."',
					tipo_concepto='".$tipo_concepto."',
					factura_tipo='".$factura_tipo."',
					factura_rif='".$this->db->escape_str($factura_rif)."',
					factura_num='".$this->db->escape_str($factura_num)."',
					fecha_concepto='".$this->db->escape_str($fecha_concepto)."',";
				if ( $conadjunto )
					$sqlregistrargasto .= "
					factura_bin='".$factura_bin."',";
				$sqlregistrargasto .= "
					sessionflag='".$sessionflag1."' WHERE cod_registro='".$cod_registro."'";
		$this->db->query($sqlregistrargasto);
        // TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$this->table->clear();
		$tmplnewtable = array ( 'table_open'  => '<table border="1" cellpadding="1" cellspacing="0" class="table containerblue tablelist">' );
		$this->table->set_caption(null);
		$this->table->set_template($tmplnewtable);
		$this->table->set_heading(
			'Registro','Descripcion',
			'Monto','Gasto','Del dia',
			'Adjunto'
		);
		$this->table->add_row(
			$cod_registro, $this->db->escape_str($des_concepto), $mon_registro,
			$factura_tipo,$fecha_concepto,
			$linkadjunto);
		$data['htmltablacargasregistros'] = $this->table->generate();
		$data['menu'] = $this->menu->general_menu();
		$data['cod_registro'] = $cod_registro;
		$data['accionejecutada'] = 'gastomanualfiltrardouno';
//		$data['upload_errors'] = $this->upload->display_errors('<p>', '</p>');
		$this->load->helper(array('form', 'url','html'));
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
		$this->load->view('header.php',$data);
		$this->load->view('cargargastosucursales.php',$data);
		$this->load->view('footer.php',$data);
	}

	// ############# INI gastomanualrevisarlos revisa todos los gstos crud bonito ############
	public function gastosucursalesrevisarlos()
	{
		$this->_verificarsesion();
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastosucursalesrevisarlos';
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		$data['pepe'] = $usuariocodgernow;
		$userintran = $this->session->userdata('intranet');

		// ******* ini nombres de tablas para filtrar los datos:
		$segurodelatabla = rand(6,8);
		//$segurodelatabla = date("YmdHis");
		$tablaentidades = "entidad";
		$tablacategoria = "categoria";
		$tablasubcatego = "subcategoria";
		$tablaregistros = "registro_gastos_".$userintran . $segurodelatabla;

		// ****** ini limpieza de tablas antes de mostrar y despues que se muestra mas abajo al final
		$this->db->trans_strict(TRUE); // todo o nada
		$this->db->trans_begin();
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablaregistros.";";
			$this->db->query($sqldatostablasfiltrados);
			if ($this->db->trans_status() === FALSE)
			{
				$this->db->trans_rollback();
				$data['output'] = "Error ejecutando su filtro, repita el proceso, si persiste consulte systemas";
				$this->load->view('header.php',$data);
				$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
				$this->load->view('cargargastosucursales.php',$data);
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
				AND CONVERT(SUBSTRING(fecha_concepto,1,6),UNSIGNED) >= CONVERT('".(date('Ym')-1)."',UNSIGNED)
				AND CONVERT(SUBSTRING(fecha_concepto,1,6),UNSIGNED) <= CONVERT('".date('Ym')."',UNSIGNED) ";
				if ( $this->nivel == 'contabilidad' ) 	$sqltablagastousr .= " and factura_tipo = 'CONTRIBUYENTE'";
				if ( $this->nivel == 'sucursal' ) 	$sqltablagastousr .= " and cod_entidad = '".$usuariocodgernow."'";
				if ( $this->nivel == 'especial' ) 	$sqltablagastousr .= " and tipo_concepto <> 'ADMINISTRATIVO' and tipo_concepto NOT LIKE 'ADMINISTRATI%' ";
				if ( $this->nivel == 'ninguno' ) 	$sqltablagastousr .= " and tipo_concepto = '' ";
				$data['nivel'] = $this->nivel;
				if ( $cod_categoria != '')		$sqltablagastousr .= " AND registro_gastos.cod_categoria = '".$this->db->escape_str($cod_categoria)."' ";
				if ( $cod_subcategoria != '')	$sqltablagastousr .= " AND registro_gastos.cod_subcategoria = '".$this->db->escape_str($cod_subcategoria)."' ";
				if ( $des_registrolike != '')	$sqltablagastousr .= " AND registro_gastos.des_concepto LIKE '%".$this->db->escape_str($des_concepto)."%' ";
				if ( $fec_registroini != '')	$sqltablagastousr .= " AND CONVERT(fecha_concepto,UNSIGNED) >= ".$this->db->escape_str($fec_registroini)." ";
				if ( $fec_registrofin != '')	$sqltablagastousr .= " AND CONVERT(fecha_concepto,UNSIGNED) <= ".$this->db->escape_str($fec_registrofin)." ";
				if ( $mon_registroigual != '')	$sqltablagastousr .= " AND registro_gastos.mon_registro <= ".$this->db->escape_str($mon_registroigual)." ";
				if ( $mon_registromayor != '')	$sqltablagastousr .= " AND registro_gastos.mon_registro >= ".$this->db->escape_str($mon_registromayor)." ";
		// ejecutsamos los querys que crean las 4 tablas a usar en el crud con datos ya filtrados
		$sqltablagastousr .= " ORDER BY fecha_concepto DESC ";
		if ( $this->nivel == 'sucursal')
			$sqltablagastousr .= " LIMIT 600";
		else
			$sqltablagastousr .= " LIMIT 1000";
		$this->db->query($sqltablagastousr);
		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$data['output'] = "Error ejecutando su filtro, repita el proceso, si persiste consulte systemas";
			$this->load->view('header.php',$data);
			$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
			$this->load->view('cargargastosucursales.php',$data);
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
			 ->display_as('cod_categoria','Categoria')
			 ->display_as('cod_subcategoria','Subcategoria')
			 ->display_as('cod_entidad','Centro<br>Coste')
			 ->display_as('mon_registro','Monto')
			 ->display_as('des_concepto','Concepto')
			 ->display_as('des_detalle','Detalles')
			 ->display_as('des_estado','Justificacion<br>Errores')
			 ->display_as('fecha_concepto','Fecha<br>Gasto')
			 ->display_as('fecha_registro','Fecha<br>Registro')
			 ->display_as('factura_tipo','Factura<br>Tipo')
			 ->display_as('factura_num','Factura<br>Numero')
			 ->display_as('factura_rif','Factura<br>Rif')
			 ->display_as('factura_bin','Factura<br>Escaneada');
		if ( $this->nivel == 'sucursal' or $this->nivel == 'administrador')
			$crud->columns('fecha_concepto','cod_categoria','cod_subcategoria','mon_registro','des_concepto','estado','des_estado','factura_tipo','factura_num','factura_rif','factura_bin','cod_registro','fecha_registro');
		else
			$crud->columns('fecha_concepto','cod_categoria','cod_subcategoria','cod_entidad','mon_registro','des_concepto','estado','des_estado','factura_tipo','factura_num','factura_rif','factura_bin','cod_registro','fecha_registro');
		$crud->set_relation('cod_entidad',$tablaentidades,'{des_entidad}'); //,'{des_entidad}<br> ({cod_entidad})'
		$crud->set_relation('cod_categoria',$tablacategoria,'{des_categoria}'); // ,'{des_categoria}<br> ({cod_categoria})'
		$crud->set_relation('cod_subcategoria',$tablasubcatego,'{des_subcategoria}'); // ,'{des_subcategoria}<br> ({cod_subcategoria})'
		$crud->unset_add();
		$crud->unset_edit();
		$crud->unset_delete();
		if ( $this->nivel == 'sucursal' or $this->nivel == 'administrador')
			$crud->add_action('Editar', '', '','ui-icon-plus',array($this,'_cargargastosucursaleditandocodigo'));
		if ( $this->nivel == 'especial' or $this->nivel == 'administrador')
			$crud->add_action('Erroneo', '', '','ui-icon-plus',array($this,'_cargargastosucursalnotificacodigo'));
		if ($this->nivel == 'administrador')
			$crud->add_action('Eliminar', '', '','ui-icon-plus',array($this,'_cargargastosucursaleditandocodigo'));
		$directoriofacturas = 'archivoscargas/' . date("Y");
		if ( ! is_dir($directoriofacturas) )
		{
			if ( is_file($directoriofacturas) )
			{	unlink($directoriofacturas);	}
			mkdir($directoriofacturas, 0777, true);
			chmod($directoriofacturas,0777);
		}
		$crud->set_field_upload('factura_bin',$directoriofacturas);
		$output = $crud->render();

		// TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastosucursalesrevisarlos';
		$data['js_files'] = $output->js_files;
		$data['css_files'] = $output->css_files;
		$data['output'] = $output->output;
		$this->load->view('header.php',$data);
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
		$this->load->view('cargargastosucursales.php',$data);
		$this->load->view('footer.php',$data);

		// ******** limpiar de las tablas "disque temporales" con datos filtrados para hacer crud
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablaregistros.";";
			$this->db->query($sqldatostablasfiltrados);
	}

	function _cargargastosucursaleditandocodigo($primary_key , $row)
	{
		return site_url('cargargastosucursalesadm/gastomanualeditaruno').'?cod_registro='.$row->cod_registro;
	}

	function _cargargastosucursaleliminacodigo($primary_key , $row)
	{
		return site_url('cargargastosucursalesadm/gastomanualeditaruno').'?cod_registro='.$row->cod_registro;
	}

	function _cargargastosucursalnotificacodigo($primary_key, $row)
	{
		return "javascript:window.open ('enviarnotificacion/".$row->cod_registro."/".$row->cod_entidad."','NOtificador','menubar=1,resizable=1,width=350,height=250');";
	}

	public function enviarnotificacion($codigo,$aquien)
	{
		$squ = "SELECT des_entidad FROM gastossystema.entidad where cod_entidad=".$aquien.";";
		$sql = "select intranet from gastossystema.entidad_usuario where cod_entidad='".$aquien."'";
		$this->load->database('gastossystema');
		$sqlcorreonotificar = $this->db->query($sql);
		$sqlnombrenotificar = $this->db->query($squ);
		$correoaenviar = '';
		$cuantosenviar = 0;
		foreach ($sqlnombrenotificar->result() as $nombrerow)
		{
			$dquien = $nombrerow->des_entidad ;
			break;
		}
		foreach ($sqlcorreonotificar->result() as $correorow)
		{
			$correoaenviar .= $correorow->intranet .'@intranet1.net.ve, ';
			$cuantosenviar++;
		}
		$correoaenviar = substr($correoaenviar, 0, -2);
		if ($cuantosenviar < 1)
			if ( $this->session->userdata('correo') != '' )
				$correoaenviar = $this->session->userdata('correo') . ', lenz_gerardo@intranet1.net.ve';
			else
				$correoaenviar = 'lenz_gerardo@intranet1.net.ve';

		$this->load->library('email');
		$configm2['protocol'] = 'mail';// en sysdevel y sysnet envia pero syscenter no
		$configm2['wordwrap'] = FALSE;
		$configm2['starttls'] = TRUE; // requiere sendmail o localmail use courierd START_TLS_REQUIRED=1 sendmail no envia
		$configm2['smtp_crypto'] = 'tls';
//		$configm2['mailtype'] = 'html';
		$this->email->initialize($configm2);
		$this->email->from('gastostiendasvnz@intranet1.net.ve', 'gastostiendasvnz');
		//    $this->email->cc($correousuariosesion);
		$this->email->reply_to('gastostiendasvnz@intranet1.net.ve', 'gastostiendasvnz');
		$this->email->to($correoaenviar ); // enviar a los destinos de galpones
		$this->email->subject('Gasto erroneo '. $codigo);
		$this->email->message('El gasto de codigo '.$codigo.' esta errado, se debe revisar y corregirlo.'.PHP_EOL.PHP_EOL );
		//	$this->email->attach($filenameneweordendespachoadjuntar);
		$data['accionejecutada'] = 'gastonotificacionerror';
		$data['cod_registro'] = $codigo;
		$data['cod_entidad'] =$dquien;
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
		if($this->email->send())
		{
			$this->session->set_flashdata("email_sent","Correo enviado con notificacion.");
			$data['resultadomsg'] = "Notificacion de correo para correccion enviada";
		}
		else
		{
			$this->session->set_flashdata("email_sent","Correo no enviado, sistema en desarrollo.");
			$data['resultadomsg'] = "Correo no enviado, sistema en desarrollo.";
		}
		$data['mens'] = 'Envio de notificacion gasto errado';
		$this->load->view('cargargastosucursales.php',$data);
	}

}
