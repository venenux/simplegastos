<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * habladorcontrol.php
 * 
 * Copyright 2017 PICCORO Lenz McKAY mckaygerhard@gmail.com
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * ODBC: https://vegnuli.wordpress.com/2016/12/14/venenux-odbc-y-sybase-nocicones-y-conectividad/#Trabajar-ODBC-con-SybaseSQL-freeDTS
 * 
 * apt-get install unixodbc tdsodbc libsybdb5 libct4
 * odbcinst -i -d -f /usr/share/tdsodbc/odbcinst.ini 
 * touch /etc/odbc.ini
 * isql oasis0 dba sql
 */


/** controlador que imprime un hablador segun los formatos en hoja de calculo */
class habladorcontrol extends CI_Controller {

	private $botongestion0 = null;
	private $nivel = "ninguno";
	public $dbgasto = null;
	public $dboasis = null;

	function __construct()
	{
		parent::__construct();
		$this->dbgasto = $this->load->database('gastossystema', TRUE);
		$this->dboasis = $this->load->database('oasis', TRUE);
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->model('menu');
		$this->load->library('PHPReport');
		$this->output->enable_profiler(TRUE);

		$this->botongestion0 = ''
			. anchor('suc_hablador/habladorcontrol/index',form_button('', 'Procesar codigos', 'class="btn-primary btn" '))
			. anchor('suc_hablador/habladorcontrol/habladorpaso4cargarplantilla',form_button('', 'Nueva plantilla', 'class="btn-primary btn" '))
			;

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
			else if( ( $usuariocodgernow > 399 and $usuariocodgernow < 998) or $usuariocodgernow == '196' or $usuariocodgernow == '252' or $usuariocodgernow == '200' )
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
	public function index($mens = '')
	{
		$this->_verificarsesion();
		$this->habladorpaso1datos($mens);
	}

	public function habladorpaso1datos($mens = '')
	{
		$this->_verificarsesion();
		$sqlentidad = " select abr_entidad, abr_zona, cod_entidad, des_entidad from entidad ";
		$resultadosentidad = $this->dbgasto->query($sqlentidad);
		$arregloentidades = array(''=>'');
		foreach ($resultadosentidad->result() as $row)
			$arregloentidades[$row->cod_entidad] = $row->abr_entidad .' - ' . $row->des_entidad;
		$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box
		unset($arregloentidades['']);

		$data['botongestion0'] = $this->botongestion0;
		$data['menu'] = $this->menu->menudesktop();
		$data['accionejecutada'] = 'habladorpaso1datos';	// para cargar parte especifica de la vista envio un parametro accion
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
		$data['mens'] = $mens;
		$this->load->view('header.php',$data);
		$this->load->view('suc_hablador/habladorvistaprocesar.php',$data);
		$this->load->view('footer.php',$data);
		
	}

	public function habladorpaso1datosrecibe()
	{
		$this->_verificarsesion();
		
		$data['menu'] = $this->menu->menudesktop();
		$data['accionejecutada'] = 'gastomanualfiltrardouno';
		
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		$userintran = $this->session->userdata('intranet');

		// ******* OBTENER DATOS DE FORMULARIO ***************************** /
		$iptiendapeticion = $this->input->ip_address();
		$fec_impresion = $this->input->get_post('fec_impresion');
		$cod_productos =  $this->input->get_post('cod_productos');
		$tipo_hablador =  $this->input->get_post('list_tipo_hablador');
		$cod_entidad =  $this->input->get_post('cod_entidad');
		$cod_entidadusr =  $this->input->get_post('cod_entidadusr');
		$ind_existencia =  $this->input->get_post('ind_existencia');
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('cod_productos', 'los codigos son requeridos y deben ser separado por comas', 'required');
		//$this->form_validation->set_rules('tipo_hablador', 'el formato de hablador de la lista', 'required');
		if ( $this->form_validation->run() == FALSE )
		{
			$mens = validation_errors();
			log_message('info', $mens.'.');
			return $this->index( $mens );
		}
		
		// averiguar el sello de la entidad
		$sqlentidad = " SELECT * FROM entidad where cod_entidad = ".$cod_entidad." ";
		$resultadosentidad = $this->dbgasto->query($sqlentidad);
		$resultadosentidadobj = $resultadosentidad->result();
		$row = $resultadosentidadobj[0];
		$cod_sello = $row->sello;

		// averiguar si requiere comprobar existencia:
		if ( $ind_existencia == '')
			$ind_existencia = 1;

		// armar la consulta maximo 12 productos
		$sqlprecioporexistencia = " SELECT ";
		$arraycodigos = explode(',', $cod_productos); //split string into array seperated by ', '
		// debe ser consulta armada con union, no se puede por limites de odbc
		// si itero sobre un resultado de una consulta, por culpa de odbc no 
		// no podra obtener un segundo resultado mientras itera sobre el primero
		// esto porque la llamada es sobre un procedimiento almacenado
		// la mejor manera es de una vez llamar todo usando uniones de select por cada codigo consultar
		$arraycuantos = 0;
		foreach($arraycodigos as $codigos) //loop over values
		{
			if ( $arraycuantos > 12 )	// procesar max 12 products
				break;
			$arraycuantos = $arraycuantos + 1;
			$codigoproducto =  $this->dboasis->escape_str(trim($codigos));
			$sqlprecioporexistencia .= $arraycuantos." as orden, cod_interno as codigo, txt_descripcion_larga as descripcion, PrecioDescuento as total, ((((precioFull-PrecioDescuento)*100)/precioFull)) as descuento, precioFull as precio, 12 as iva  FROM st_hablador('".$cod_sello."','".$codigoproducto."','".$ind_existencia."') UNION SELECT ";
		}
		$sqlprecioporexistencia = rtrim($sqlprecioporexistencia, "UNION SELECT ");
		
		// consult those products (codes) parsed as query
		$srtprecioporexistencia = $this->dboasis->query($sqlprecioporexistencia);
		$srlpreciosproductosarray = $srtprecioporexistencia->result_array();
		
		foreach($srlpreciosproductosarray as $indiceproducto => $productoarray)
		{
			foreach($productoarray as $colnam => $rowval)
			{
				if($colnam == 'total')
				{	
					if($rowval == '0' OR $rowval < 1 )
						$newproducto[$colnam] = $productoarray['precio'];
				}
				else if($colnam == 'descuento')
				{	
					if($rowval == '100' OR $rowval == 100 )
						$newproducto[$colnam] = '0';
				}
				else
					$newproducto[$colnam] = $rowval;
			}
			$dataproductosarray[] = $newproducto;
		}

		if ( count($dataproductosarray) < 1 )
		{
			$productosarray = array(array('codigo'=>0, 'descripcion'=>'No hay mas existencias','descuento'=>0,'precio'=>0,'total'=>0,'iva'=>12));
		}
		else
		{
			$productosarray = $dataproductosarray;
		}
		
		// parseo mediante el reporteador php report embebido
		$dirtemplates =  APPPATH . 'archivoscargas/suc_hablador_templates/'; // $dirtemplates = 'archivoscargas/' . date("Y");
		if ( ! is_dir($dirtemplates) )
		{
			if ( is_file($dirtemplates) )
			{	unlink($dirtemplates);	}
			mkdir($dirtemplates, 0777, true);
			chmod($dirtemplates,0777);
		}
		
		$descuentoformato = array('number'=>array('sufix'=>'%','decimals'=>0));
		$preciovenformato = array('number'=>array('prefix'=>'Bs ','decimals'=>2));
		$ivaformato = array('number'=>array('sufix'=>'% ','decimals'=>0));
		$formatocolumnas = array('descuento'=>$descuentoformato,'total'=>$preciovenformato,'iva'=>$ivaformato);
		
		$configreport = array('template'=>$tipo_hablador.'.xls','templateDir'=>$dirtemplates);
		$this->load->library('phpreport');
		$rendered = new phpreport($configreport);
		$rendered->load(
			array(
				array(
					'id'=>'prod',
					'repeat'=>true,
					'data'=>$productosarray,
					'format'=>$formatocolumnas
					),
				array(
					'id'=>'inv',
					'data'=>array('date'=>$fec_impresion)
					)
				)
        );
		// TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$botonregresar = '' 
			. anchor('suc_hablador/habladorcontrol/index',form_button('', 'Procesar de nuevo', 'class="btn-primary btn" '))
			;
		$data['botongestion0'] = $botonregresar;
		$data['menu'] = $this->menu->menudesktop();
		$data['mens']='IMPRIMA EN SU IMPRESORA SIN ALTERAR, DESDE EL NAVEGADOR DE INTERNET, pulse boton imprimir';
		$data['accionejecutada'] = 'habladorpaso2impresion';
		//$this->load->view('header.php',$data);
		$this->load->view('suc_hablador/habladorvistaprocesar.php',$data);
		//$this->load->view('footer.php',$data);
		echo $rendered->render('html');
	}

	public function habladorpaso4cargarplantilla()
	{
		//AS
	}

	public function gastomanualeditaruno($mens = NULL, $codigo = 'ultimo')
	{
		$this->_verificarsesion();
		$usuariocodgernow = $this->session->userdata('cod_entidad');
		$data['usercodger'] = $usuariocodgernow;

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
		from entidad where ifnull(cod_entidad, '') <> '' and ( cod_entidad <> '' or cod_entidad = '".$usuariocodgernow."')";
		if ( $this->nivel == 'ninguno' )
			$sqlentidad .= " and (cod_entidad = '' or cod_entidad = '".$usuariocodgernow."')";
		if ( $this->nivel == 'especial' )
			$sqlentidad .= " AND cod_entidad <> '111' AND cod_entidad <> 113 and cod_entidad <> 1009 and cod_entidad <> 176 and cod_entidad <> 121 and cod_entidad <> 212 and cod_entidad <> 1109 "; //." and (tipo_entidad <> 'ADMINISTRATIVO' or cod_entidad = '".$usuariocodgernow."') and (tipo_entidad NOT LIKE 'ADMINISTRATI%' or cod_entidad = '".$usuariocodgernow."') ";
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
		if ( $this->nivel == 'administrador')
			$data['botongestion0'] = anchor('cargargastoadministrativo/gastoregistros/add',form_button('cargargastoadministrativo/gastoregistros/add', 'Cargar directo', 'class="btn-primary btn" '));
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
			log_message('info', $mens.'.');
			return $this->gastomanualeditaruno( $mens, $cod_registro );
		}
		$fecha_concepto = $this->input->get_post('fecha_concepto');
		$dias	= (strtotime(date("Ymd"))-strtotime($fecha_concepto))/86400;
		$dias 	= abs($dias);
		$dias = floor($dias);
		if ( $dias > 39 )
		{
			$mens = "La fecha maxima es 16 dias atras o ser del mes en curso, semana en curso : " . $dias . " dias es muy atras!";
			log_message('info', $mens.'.');
			return $this->gastomanualeditaruno( $mens, $cod_registro );
		}
		if ( $dias < 0)
		{
			$mens = "La fecha parece ser del futro, revise la de su computador : coloco unos " . abs($dias) . " dias es adelante!";
			log_message('info', $mens.'.');
			return $this->gastomanualcargaruno( $mens );
		}
		$mon_registro = $this->input->get_post('mon_registro');
		$des_concepto = $this->input->get_post('des_concepto');
		$tipo_concepto = $this->input->get_post('tipo_concepto');
		$factura_tipo = $this->input->get_post('factura_tipo');
		$factura_num = $this->input->get_post('factura_num');
		$factura_rif = $this->input->get_post('factura_rif');
		$factura_bin = $this->input->get_post('factura_binX');
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_subcategoria = $this->input->get_post('cod_subcategoria');

		// ******* GENERACION de la carga id codigo de registro
		$cod_registro = $this->input->get_post('cod_registro');
		// ******* CARGA DEL ARCHIVO ****************** */
		$dirtemplates = 'archivoscargas/2016'; // $dirtemplates = 'archivoscargas/' . date("Y");
		if ( ! is_dir($dirtemplates) )
		{
			if ( is_file($dirtemplates) )
			{	unlink($dirtemplates);	}
			mkdir($dirtemplates, 0777, true);
			chmod($dirtemplates,0777);
		}
		$cargaconfig['upload_path'] = $dirtemplates;
		$cargaconfig['allowed_types'] = 'gif|jpg|png|jpeg|pdf';
		$cargaconfig['max_size']  = 3072*1024;  //$cargaconfig['max_size']= '100'; // en kilobytes
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
			if ( $this->input->get_post('factura_binX') != '' and $file_data['file_name'] == '' )
			{
				$conadjunto = FALSE;
				$factura_bin = $this->input->get_post('factura_binX');
				$linkadjunto = $this->input->get_post('factura_binX');
			}
			else
			{
				if ( $file_data['file_name'] == '' )
				{
					if ( $factura_tipo == 'CONTRIBUYENTE' )
					{
						$this->gastomanualeditaruno($mens = '<br>CUANDO ES CONTRIBUYENTE Debe subir un archivo escaneado <br>que avale el gasto que ud esta registrando! REPITA EL PROCESO', $cod_registro);
						log_message('info', $mens.'.');
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
					$linkadjunto = anchor_popup( '../'.$dirtemplates.'/'. $factura_bin,$factura_bin,array('width'=>'800','height'=>'600','resizable'=>'yes'));
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
		if ( $this->nivel == 'administrador')
			$data['botongestion0'] = anchor('cargargastoadministrativo/gastoregistros/add',form_button('cargargastoadministrativo/gastoregistros/add', 'Cargar directo', 'class="btn-primary btn" '));
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
		$data['mens']=$this->upload->display_errors();
		$data['accionejecutada'] = 'gastomanualfiltrardouno';
//		$data['upload_errors'] = $this->upload->display_errors('<p>', '</p>');
		$this->load->helper(array('form', 'url','html'));
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
		$this->load->view('header.php',$data);
		$this->load->view('cargargastosucursales.php',$data);
		$this->load->view('footer.php',$data);
	}

	// ############# INI gastomanualrevisarlos todos los gstos crud bonito ############
	public function gastosucursalesrevisarlos()
	{
		$this->_verificarsesion(); // verifico si el usuario esta logeado
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastosucursalesrevisarlos';

			// ****** ini post/get si vino algun filtro tomo los valores
				$fec_conceptoini = $this->input->get_post('fec_conceptoini');
				$fec_conceptofin = $this->input->get_post('fec_conceptofin');
				$fec_registroini = $this->input->get_post('fec_registroini');
				$fec_registrofin = $this->input->get_post('fec_registrofin');
				$mon_registroigual = $this->input->get_post('mon_registroigual');
				$mon_registromayor = $this->input->get_post('mon_registromayor');
				$des_registrolike = $this->input->get_post('des_registrolike');
				$cod_entidad = $this->input->get_post('cod_entidad'); // no importaperfil, si no viene no lo usa
				$cod_categoria = $this->input->get_post('cod_categoria');
				$cod_subcategoria = $this->input->get_post('cod_subcategoria');
				$sessionfichav = $this->input->get_post('sessionficha');
			// ****** fin post/get si vino algun filtro tomo los valores

		$usuariocodgernow = $this->session->userdata('cod_entidad');
		$userintran = $this->session->userdata('intranet');

		// ******* ini nombres de tablas para filtrar los datos:
		$segurodelatabla = rand(6,8);
		$tablaentidades = "entidad";
		$tablacategoria = "categoria";
		$tablasubcatego = "subcategoria";
		$tablaregistros = "registro_gastos1"; // pendiente

		$urlsegmentos = $this->uri->segment_array();

		if ( ! in_array("edit", $urlsegmentos) or ! in_array("add", $urlsegmentos) )
			$tablaregistros = "registro_gastos_".$userintran . $segurodelatabla;

		if ( ! in_array("edit", $urlsegmentos) or ! in_array("add", $urlsegmentos) )
		{
			$this->db->trans_strict(TRUE); // todo o nada
			$this->db->trans_begin();	// en una tabla temporal solo registros ultimos y por perfil
				$sqltablagastousr = "
					CREATE TABLE IF NOT EXISTS `".$tablaregistros."` SELECT registro_gastos.* FROM (`registro_gastos`)
					WHERE ( cod_registro <> '' or cod_entidad = '".$usuariocodgernow."') ";
					if ( $this->nivel != 'administrador' and ($fec_conceptoini != '' and $fec_conceptofin == '') ) 	$sqltablagastousr .= "AND CONVERT(SUBSTRING(fecha_concepto,1,6),UNSIGNED) >= CONVERT('".(date('Ym', strtotime('now - 1 month')))."',UNSIGNED)";
					if ( $this->nivel == 'contabilidad' ) 	$sqltablagastousr .= " and factura_tipo = 'CONTRIBUYENTE'";
					if ( $this->nivel == 'sucursal' ) 	$sqltablagastousr .= " and cod_entidad = '".$usuariocodgernow."'";
					if ( $this->nivel == 'especial' ) 	$sqltablagastousr .= " and ( tipo_concepto <> 'ADMINISTRATIVO' or tipo_concepto NOT LIKE 'ADMINISTRATI%' or cod_entidad = '".$usuariocodgernow."') ";
					if ( $this->nivel == 'ninguno' ) 	$sqltablagastousr .= " and tipo_concepto = '' ";
					if ( $sessionfichav != '')	$sqltablagastousr .= " AND (sessionficha LIKE '".str_replace('.','_',$this->db->escape_str($sessionfichav))."' or sessionflag LIKE '".str_replace('.','_',$this->db->escape_str($sessionfichav))."') ";
					if ( $fec_conceptoini != '')	$sqltablagastousr .= " AND CONVERT(fecha_concepto,UNSIGNED) >= ".$this->db->escape_str($fec_conceptoini)." ";
					if ( $fec_conceptofin != '')	$sqltablagastousr .= " AND CONVERT(fecha_concepto,UNSIGNED) <= ".$this->db->escape_str($fec_conceptofin)." ";
					if ( $fec_registroini != '')	$sqltablagastousr .= " AND CONVERT(fecha_registro,UNSIGNED) >= ".$this->db->escape_str($fec_registroini)." ";
					if ( $fec_registrofin != '')	$sqltablagastousr .= " AND CONVERT(fecha_registro,UNSIGNED) <= ".$this->db->escape_str($fec_registrofin)." ";
					if ( $des_registrolike != '')	$sqltablagastousr .= " AND registro_gastos.des_concepto LIKE '%".$this->db->escape_str($des_registrolike)."%' ";
					if ( $mon_registroigual != '')	$sqltablagastousr .= " AND registro_gastos.mon_registro = '".$this->db->escape_str($mon_registroigual)."' ";
					if ( $mon_registromayor != '')	$sqltablagastousr .= " AND registro_gastos.mon_registro LIKE '".$this->db->escape_str($mon_registromayor)."%' ";
					if ( $cod_entidad != '')
					{
						if ( $this->nivel == 'sucursal' or $this->nivel == 'contabilidad' )
							$sqltablagastousr .= " AND registro_gastos.cod_entidad = '".$this->db->escape_str($cod_entidad)."' AND cod_entidad = '".$this->db->escape_str($cod_entidad)."' ";
						else if ( $this->nivel == 'especial' )
							$sqltablagastousr .= " AND registro_gastos.cod_entidad = '".$this->db->escape_str($cod_entidad)."' AND cod_entidad <> '111' AND cod_entidad <> 113 and cod_entidad <> 1009 and cod_entidad <> 176 and cod_entidad <> 121 and cod_entidad <> 212 and cod_entidad <> 1109 ";
						else if ( $this->nivel == 'administrador' )
							$sqltablagastousr .= " AND registro_gastos.cod_entidad = '".$this->db->escape_str($cod_entidad)."' ";
						else
							$sqltablagastousr .= "  AND cod_entidad <> '111' AND cod_entidad <> 113 and cod_entidad <> 1009 and cod_entidad <> 176 and cod_entidad <> 121 and cod_entidad <> 212 and cod_entidad <> 1109 ";
					}
					else
						if ( $this->nivel != 'administrador' )
							$sqltablagastousr .= "  AND cod_entidad <> '111' AND cod_entidad <> 113 and cod_entidad <> 1009 and cod_entidad <> 176 and cod_entidad <> 121 and cod_entidad <> 212 and cod_entidad <> 1109  ";
					if ( $cod_categoria != '')		$sqltablagastousr .= " AND registro_gastos.cod_categoria = '".$this->db->escape_str($cod_categoria)."' ";
					if ( $cod_subcategoria != '')	$sqltablagastousr .= " AND registro_gastos.cod_subcategoria = '".$this->db->escape_str($cod_subcategoria)."' ";
					if ( in_array("list", $urlsegmentos) and $fec_registroini != '')	$sqltablagastousr .= "AND CONVERT(fecha_registro,UNSIGNED) >= ".$fec_registroini." ";
					$sqltablagastousr .= " ORDER BY fecha_concepto DESC, fecha_registro DESC ";
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablaregistros.";";
			if ( $this->nivel != 'administrador')
				$sqltablagastousr .= " LIMIT 800";
			else
				$sqltablagastousr .= " LIMIT 2000";
			$this->db->query($sqldatostablasfiltrados);	// remuevo la viejas o datos viejos si hay aun
			$this->db->query($sqltablagastousr);		// recreo con el select la tabla temporal y se usara
			if ($this->db->trans_status() === FALSE)
			{
				$this->db->trans_rollback();
				$data['output'] = "Error ejecutando su filtro, repita el proceso, si persiste consulte systemas";
				$this->load->view('header.php',$data);
				$this->load->view('cargargastosucursales.php',$data);
				$this->load->view('footer.php',$data);
				return;
			}
			else
				$this->db->trans_commit();
		}

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
			 ->display_as('des_estado','Correcciones')
			 ->display_as('fecha_concepto','Fecha<br>Gastado')
			 ->display_as('fecha_registro','Fecha<br>Ingresado')
			 ->display_as('factura_tipo','Factura<br>Tipo')
			 ->display_as('factura_num','Factura<br>Numero')
			 ->display_as('factura_rif','Factura<br>Rif')
			 ->display_as('factura_bin','Factura<br>Escaneada');
		if ( $this->nivel != 'sucursal')
			$crud->columns('fecha_concepto','fecha_registro','cod_entidad','cod_categoria','cod_subcategoria','des_concepto','mon_registro','estado','des_estado','tipo_concepto','factura_tipo','factura_num','factura_rif','factura_bin','cod_registro','sessionficha','sessionflag');
		else
			$crud->columns('fecha_concepto','fecha_registro','cod_categoria','cod_subcategoria','des_concepto','mon_registro','estado','des_estado','tipo_concepto','factura_tipo','factura_num','factura_rif','factura_bin','cod_registro','sessionficha','sessionflag');
		$crud->set_relation('cod_entidad',$tablaentidades,'{des_entidad}'); //,'{des_entidad}<br> ({cod_entidad})'
		$crud->set_relation('cod_categoria',$tablacategoria,'{des_categoria}'); // ,'{des_categoria}<br> ({cod_categoria})'
		$crud->set_relation('cod_subcategoria',$tablasubcatego,'{des_subcategoria}'); // ,'{des_subcategoria}<br> ({cod_subcategoria})'
		$crud->unset_add();
		$crud->unset_edit();
		$crud->unset_delete();
		if ( $this->nivel == 'especial' or $this->nivel == 'administrador')
		{
			$crud->add_action('Auditar', '', '','ui-icon-plus',array($this,'_cargargastosucursalauditar'));
			$crud->add_action('Editar', '', '','ui-icon-plus',array($this,'_cargargastosucursaleditandocodigo'));
		}
		if ($this->nivel == 'administrador')
		{
			$crud->add_action('Editar', '', '','ui-icon-plus',array($this,'_cargargastoadministraeditandocodigo'));
			$crud->add_action('Eliminar', '', '','ui-icon-plus',array($this,'_cargargastoadministraeliminacodigo'));
			$data['botongestion0'] = anchor('cargargastoadministrativo/gastoregistros/add',form_button('cargargastoadministrativo/gastoregistros/add', 'Cargar directo', 'class="btn-primary btn" '));
		}
		else if ( $this->nivel == 'sucursal')
			$crud->add_action('Editar', '', '','ui-icon-plus',array($this,'_cargargastosucursaleditandocodigo'));
		$dirtemplates = 'archivoscargas/2016'; // $dirtemplates = 'archivoscargas/' . date("Y");
		if ( ! is_dir($dirtemplates) )
		{
			if ( is_file($dirtemplates) )
			{	unlink($dirtemplates);	}
			mkdir($dirtemplates, 0777, true);
			chmod($dirtemplates,0777);
		}
		$crud->set_field_upload('factura_bin',$dirtemplates);
		//$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url("/admusuariosentidad"));
		$crud->callback_column('mon_registro',array($this,'_numerosgente'));
		$output = $crud->render();

		// TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$data['nivel'] = $this->nivel;
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'gastosucursalesrevisarlos';
		$data['js_files'] = $output->js_files;
		$data['css_files'] = $output->css_files;
		$data['output'] = $output->output;
		$this->load->view('header.php',$data);
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion
		$this->load->view('cargargastosucursales.php',$data);
		$this->load->view('footer.php',$data);

/*
 *combinado con la url de administradores, si es administrador lo lleva a su interfaz y se devuelve
* TODO : campo de concepto tiene que ser mas ancho
* TODO: url de devolucion no puede ser por fecha, sino por alguna manera de que sea el ultimo gasto, se usa el modificado y creado
* TODO : pendiente remover de tabla errores si hay gasto erroneo arreglado
* TODO : pendiente mostrar que y donde estan los gastos erroneos


 * */


		// ******** limpiar de las tablas "disque temporales" CUIDADO! no usar a la ligera!!!!
		if ( ! in_array("edit", $urlsegmentos) or ! in_array("add", $urlsegmentos) )
		{
			$sqldatostablasfiltrados = "DROP TABLE IF EXISTS ".$tablaregistros.";";
			$this->db->query($sqldatostablasfiltrados);
		}
	}

	public function _numerosgente($value, $row)
	{
		$formateado = number_format($row->mon_registro, 2, ',', '.');
		return $formateado;
	}

	function _cargargastosucursaleditandocodigo($primary_key , $row)
	{
		return site_url('cargargastosucursalesadm/gastomanualeditaruno').'?cod_registro='.$row->cod_registro;
	}

	function _cargargastoadministraeditandocodigo($primary_key , $row)
	{
		return site_url('cargargastoadministrativo/gastoregistros/edit/'.$row->cod_registro).'?cod_registro='.$row->cod_registro;
	}

	function _cargargastoadministraeliminacodigo($primary_key , $row)
	{
		$enlace = site_url('cargargastoadministrativo/gastoregistros/delete/'.$row->cod_registro).'?cod_registro='.$row->cod_registro;
		return "javascript:void(window.open ('".$enlace."','NOtificador','menubar=1,resizable=1,width=350,height=250'));";
	}

	function _cargargastosucursalauditar($primary_key, $row)
	{
		$enlace = site_url('cargargastosucursalesadm/auditar/'.$row->cod_registro).'?cod_registro='.$row->cod_registro;
		return "javascript:void(window.open ('".$enlace."','NOtificador','menubar=1,resizable=1,width=350,height=250'));";
	}

	public function auditar($codigo = '')
	{
		$data['mens'] = 'Notificar y auditoria de gasto';
		$data['codigo'] = $codigo;
		$data['accionauditar'] = 'ninguna';
		$data['accionejecutada'] = 'gastoauditoriacodigo';
		$data['haciacontrolador'] = 'cargargastosucursalesadm';	// para cargar parte especifica de la vista envio un parametro accion

		if ($codigo == '' )
		{
			$data['mens'] = 'Ud esta realizando la opeacion mal, no hay codigo de gasto';
			$this->load->view('cargargastosucursales.php',$data);
			log_message('debug', $this->session->userdata('username').' cargado mal auditar gasto ' . $data['mens'] );
			return;
		}
		$this->load->database('gastossystema');
		$consultaregistroerror = "
			SELECT
				(select count(cod_registro) from registro_gastos where estado LIKE '%RECHAZADO%') as rechazado,
				(select count(cod_registro) from registro_gastos where estado LIKE '%ERRO%') as erroneos,
				r.cod_registro, r.des_concepto, r.estado,
				substr(r.sessionflag, instr(r.sessionflag,'.') +1) as quienaltero,
				substr(r.sessionficha, instr(r.sessionficha,'.') +1) as quienlocreo,
				substr(r.sessionflag, 1, 8) AS cuandoaltero,
				substr(r.sessionficha, 1, 8) AS cuandolocreo,
				r.cod_entidad, e.des_entidad, e.sello
			FROM gastossystema.registro_gastos AS r
			LEFT JOIN entidad AS e
				ON r.cod_entidad = e.cod_entidad
			WHERE r.cod_registro = '".$codigo."'
				ORDER BY r.fecha_concepto DESC, r.fecha_registro DESC
		";
		$sqlentidaderror = $this->db->query($consultaregistroerror);
		foreach ($sqlentidaderror->result() as $nombrerow)
		{
			$rechazado = $nombrerow->rechazado ;
			$erroneos = $nombrerow->erroneos ;
			$quienaltero = $nombrerow->quienaltero ;
			$quienlocreo = $nombrerow->quienlocreo ;
			$cuandoaltero = $nombrerow->cuandoaltero ;
			$cuandolocreo = $nombrerow->cuandolocreo ;
			$cod_entidad = $nombrerow->cod_entidad ;
			$des_entidad = $nombrerow->des_entidad ;
			$des_concepto = $nombrerow->des_concepto ;
			$sello = $nombrerow->sello ;
			$estado = $nombrerow->estado ;
			break;
		}
		if ( $quienaltero == '' )
			$quiendebe = $quienlocreo;
		else
			$quiendebe = $quienaltero;


		/* ******** obtencion de datos * **** */
		$correoaenviar = $this->session->userdata('correo') . ', systemasvnz@intranet1.net.ve, ';
		$correoaenviar .= $quiendebe .'@intranet1.net.ve';
		$data['can_rechazados'] = $rechazado+0;
		$data['can_erroneos'] = $erroneos+0;
		$accionauditar = $this->input->get_post('accionauditar');
		if ($accionauditar != '')
		{
			$estado = $this->input->get_post('estado');
			$razone = $this->input->get_post('msg_errado');
			$sessionflag = date("YmdHis").$this->session->userdata('cod_entidad').'.'.$this->session->userdata('username');
			$consultaprocesar = "UPDATE `gastossystema`.`registro_gastos` SET `estado`='".$estado."', des_estado= '".$razone."', sessionflag='".$sessionflag."' WHERE `cod_registro`='".$codigo."'";
			$sqlentidaderror = $this->db->query($consultaprocesar);
			$data['htmlauditarcodigo'] = "Gasto ha sido cambiado a ".$estado;
			if ($estado == 'ERRONEO' )
			{
				$consultanotificar = "INSERT INTO `gastossystema`.`registro_errado` (`cod_registro`, `cod_entidad`, `intranet`, `msg_errado`, sessionflag) VALUES ('".$codigo."', '".$cod_entidad."', '".$quiendebe."', '".$razone."', '".$sessionflag."');";
				$sqlentidaderror = $this->db->query($consultaprocesar);
				$data['can_erroneos'] = $erroneos+1;
			}
			else if  ($estado == 'RECHAZADO' )
				$data['can_rechazados'] = $rechazado+1;
			$correomsg = "Gasto ".$estado." por ".$razone.": \nCodigo ".$codigo." modificado el ".$cuandolocreo." alterado el ".$cuandoaltero." \nde ".$des_entidad;
			if ( $estado == 'ERRONEO' OR $estado == 'RECHAZADO' )
			{
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
				$this->email->message($correomsg.PHP_EOL.PHP_EOL );
				//	$this->email->attach($filenameneweordendespachoadjuntar);
				if($this->email->send())
				{
					$this->session->set_flashdata("email_sent","Correo enviado con notificacion a ".$correoaenviar);
					$data['resultadomsg'] = "Notificacion de correo para correccion enviada a ". $correoaenviar;
				}
				else
				{
					$this->session->set_flashdata("email_sent","Notificacion en cola sin correo enviad a ".$correoaenviar);
					$data['resultadomsg'] = "Correo no enviado, notificacion en cola correo no enviado a ".$correoaenviar;
				}
				$data['mens'] = 'Envio de notificacion realizada'.br().br().PHP_EOL.$correomsg .br().br().$data['resultadomsg'] ;
				log_message('info', $this->session->userdata('username').' auditando error/rechazo el gasto ' . $data['resultadomsg'] . ' ' . $des_concepto);
			}
			else
				log_message('info', $this->session->userdata('username').' auditando aprobado el gasto ' . $codigo . ' ' . $des_concepto);
			$data['botongestion0'] = '';
			$this->load->view('cargargastosucursales.php',$data);
			return;
		}

		if ( $estado == 'PENDIENTE' )
		{
			$mens = "esta pendiente";
			$list_estado = array(''=>'','APROBADO' => 'APROBADO', 'RECHAZADO' => 'RECHAZADO', 'ERRONEO' => 'ERRONEO');
		}
		else
		{
			$mens = "no esta pendiente";
			$list_estado = array(''=>'','RECHAZADO' => 'RECHAZADO', 'ERRONEO' => 'ERRONEO');
		}

		$htmlformaattributos = array('name'=>'cargargastoucursal','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		$htmlauditarcodigo = form_open_multipart('cargargastosucursalesadm/auditar/'.$codigo, $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
		$this->table->set_template(array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">' ) );
			$this->table->add_row($codigo. ' ', ' ('. $des_concepto.')');
			$this->table->add_row('Accion', form_dropdown('estado', $list_estado, '', 'class="btn btn-primary btn-large"' ));
			$this->table->add_row('Razon:', form_input('msg_errado','', 'class="btn btn-primary btn-large"'));
			$this->table->add_row('',form_submit('auditagasto1', 'Auditar', 'class="btn btn-primary btn-large b10"'));
		$htmlauditarcodigo .= $this->table->generate().br().PHP_EOL;
		$htmlauditarcodigo .= form_hidden('accionauditar', 'terminado'); // no se puede resubir archivos, entonces comparo si cambio el nombre y tomo el subido nuevo, sino esta variable es el nombre viejo inalterado
		$htmlauditarcodigo .= form_close() . PHP_EOL;
		$data['htmlauditarcodigo'] = $htmlauditarcodigo;
		log_message('info', $this->session->userdata('username').' cargado pagina auditar gasto ' . $codigo . ' descripcion ' . $des_concepto);
		$this->load->view('cargargastosucursales.php',$data);
	}

}
