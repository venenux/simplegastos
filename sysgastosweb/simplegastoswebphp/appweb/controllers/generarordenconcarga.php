<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Generarordenconcarga extends CI_Controller {

	protected $numeroordendespacho =  '';
	private $usuariologin = 'lenz_gerardo';

	function __construct()
	{
		parent::__construct();
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		if( $this->session->userdata('logueado') == FALSE)
		{
			redirect('manejousuarios');
		}
		$userdata = array();
		$userdata = $this->session->all_userdata();
		$this->usuariologin=$userdata['username'];
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->model('menu');
		$this->numeroordendespacho = date("Y") . date("m") . date("d") . date("H") . date("i") ;
		$connecion = $this->load->database('oasis');
		 //el profiler esta dañado.. debido a una mala coarga de arreglos para los de idiomas
//		$this->output->enable_profiler(TRUE);
		// hasta tener manejo de sesion
	}

	/**
	 * Index Page for this controller.
	 * 		http://example.com/index.php/indexcontroler
	 * 		http://example.com/index.php/indexcontroler/index
	 * map to /index.php/indexcontroler/<method_name>
	 */
	public function index()
	{
		$data['menu'] = $this->menu->general_menu();
		// quiery plano y ejecucion
		$sqlubicacion = "SELECT cod_msc, nom_sucursal FROM dba.exttiendas ORDER BY nom_sucursal ASC;";
		$resultadosubicacion = $this->db->query($sqlubicacion);
		// armo un arreglo que lista las ubicaciones, el msc es el id del combobox, relleno con un combobox
		$arregloubicaciones = array(''=>'');
		foreach ($resultadosubicacion->result() as $row)
		{
			$arregloubicaciones[''.$row->cod_msc] = '' . $row->cod_msc . '-' . $row->nom_sucursal;
		}
		// agrega este arreglo como dos listas una de origen y una de destino
		$data['list_ubicacionorigen'] = $arregloubicaciones;
		unset($arregloubicaciones['']);
		$data['list_ubicaciondestin'] = $arregloubicaciones;
		
		$this->load->view('header.php',$data);
		$this->load->view('generarordenconcarga.php',$data);
		$this->load->view('footer.php',$data);
	}

	public function generacionautomatica()
	{
		$userdata = $this->session->all_userdata();
		$correousuariosesion = $this->usuariologin=$userdata['correo'];
		// OBTENER DATOS DE FORMULARIO ***************************** /
		$delimitador = $this->input->get_post('archivoproductospreciosep');
		$ubicacionorigen = $this->input->get_post('ubicacionorigen');
		$ubicaciondestin = $this->input->get_post('ubicaciondestin');
		// CARGA DEL ARCHIVO ****************************************************** /
		$cargaconfig['upload_path'] = CATAPATH . '/appweb/archivoscargas';
		$cargaconfig['allowed_types'] = 'txt|.|csv';
		//$cargaconfig['max_size']= '100'; // en kilobytes
		$cargaconfig['max_size']  = 0;
		$cargaconfig['max_width'] = 0;
		$cargaconfig['max_height'] = 0;
		//$cargaconfig['remove_spaces'] = true;
		$cargaconfig['encrypt_name'] = TRUE;
		$this->load->library('upload', $cargaconfig);
		$this->load->helper('inflector');
		$this->upload->initialize($cargaconfig);
		$this->upload->do_upload('archivoproductosprecionom'); // nombre del campo alla en el formulario
		$file_data = $this->upload->data();
		$filenamen = 'ordendespachocarga' . $this->numeroordendespacho .'.txt';
        $filenameorig =  $file_data['file_path'] . $file_data['file_name'];
        $filenamenewe =  $file_data['file_path'] . $filenamen;
        copy( $filenameorig, $filenamenewe); // TODO: rename
        //rename( $filenameorig, $filenamenewe);
		$data['upload_data'] = $this->upload->data();
		$data['archivos'] = $filenameorig . '  y  ' . $filenamenewe ;
		// TRABAJAR COMO CSV ****************************************************** /
		$this->load->library('csvimport');
		$this->csvimport->filepath($filenameorig);
		$this->csvimport->delimiter($delimitador);
		$this->csvimport->initial_line(0);
		$this->csvimport->detect_line_endings(TRUE);
		$this->csvimport->column_headers(FALSE);
		//$csv_array = $this->csvimport->get_array($filenameorig,TRUE,TRUE,0,'|');
		$csv_array = $this->csvimport->get_array();
		$cantidadLineas = 0;
		if ( ! $csv_array ) 
		{
			$resultadocarga = array('Error, no se completo el proceso', 'Sin datos', '0', '', '', '', '');
		}
		else
		{
			$resultadocarga = array('Error, no se completo el proceso', 'Sin datos', '0', '', '', '', '');
            $sql = "INSERT INTO dba.td_orden_despacho (cod_interno, cantidad, precio_venta, cod_order, ord_origen) VALUES ";
			foreach ($csv_array as $row) 
            {
				$sql .= "(  right('000000000'+".$this->db->escape($row['cod_producto']).",10), ".$this->db->escape($row['can_cantidad']).", ".$this->db->escape($row['can_precio']).", ".$this->db->escape($filenamen).", ".$this->db->escape($filenameorig)."),";
                $cantidadLineas++;
            }
            $sql = substr ($sql, 0, -1);
			$this->db->query($sql);
			
            $sql = "INSERT INTO dba.tm_orden_despacho (cod_order, estado, nom_usuario, cantidadLineas, origen, destino, cambio_precio_asc) VALUES ";
            $eldestinosinsertar = implode(",",$ubicaciondestin);
			//foreach($ubicaciondestin as $posi=>$eldestinosinsertar)
			//{
				// cambio de precio es in id de cm_cpp
				$sql .=	"(".$this->db->escape($filenamen).", '0', '".$correousuariosesion ."', ".$cantidadLineas.", ".$this->db->escape($ubicacionorigen).", ".$this->db->escape($eldestinosinsertar).", ''),";
			//}
			$sql = substr ($sql, 0, -1);
			$this->db->query($sql);
        // CARGA EXITOSA UESTRO DETALLE ************************************* /
			$sqlcargado = "SELECT  
				(select txt_descripcion_larga from dba.tv_producto where cod_interno=a.cod_interno) AS des_producto
				,a.cod_interno AS cod_producto
				,a.cantidad AS can_cantidaddespachar
				,a.precio_venta as precio_archivo
				,(select mto_precio from dba.ta_precio_producto where cod_precio=0 and cod_interno=a.cod_interno and cod_sucursal=(select num_sucursal from DBA.tc_codmsc where cod_msc='".$ubicacionorigen."')) as precio_origen
				,(select convert(integer ,saldo_producto) from dba.tv_existencia where cod_interno = a.cod_interno and cod_msc='".$ubicacionorigen."') as saldo_origen
				FROM dba.td_orden_despacho as a
				where cod_order = '".$filenamen."'";
//				,(select mto_precio from dba.ta_precio_producto where cod_precio=0 and cod_interno=a.cod_interno and cod_sucursal=(select num_sucursal from DBA.tc_codmsc where cod_msc='".$ubicaciondestin."')) as precio_destino
			$resultadocarga = $this->db->query($sqlcargado); //row_array
        }
        // TERMINAR EL PROCESO (solo paso 1) **************************************************** /
		$this->table->clear();
		$tmplnewtable = array ( 'table_open'  => '<table border="1" cellpadding="1" cellspacing="1" class="table">' );
		$this->table->set_caption(NULL);
		$this->table->set_template($tmplnewtable);
		$this->table->set_heading('des_producto', 'cod_producto', 'can_despachar', 'precio_archivo', 'precio_origen', 'precio_destino', 'existencia_origen');
		$resultadocargatablatxtmsg = '';
		$resultadocargatablatxtmsg .= "| cod_producto \t| can_despachar \t| des_producto \t\t".PHP_EOL;
		$resultadocargatabla = $resultadocarga->result_array();
		foreach ($resultadocargatabla as $rowtable)
		{
			$this->table->add_row($rowtable['des_producto'], $rowtable['cod_producto'], $rowtable['can_cantidaddespachar'], $rowtable['precio_archivo'], $rowtable['precio_origen'], /*$rowtable['precio_destino'],*/ $rowtable['saldo_origen']);
			$resultadocargatablatxtmsg .= "| ".$rowtable['cod_producto'] ." \t| ". $rowtable['can_cantidaddespachar'] . " \t| ". $rowtable['des_producto'] .' '.PHP_EOL;
		}
		$data['htmltablageneradodetalle'] = $this->table->generate();
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'resultadocargardatos';
		$data['upload_errors'] = $this->upload->display_errors('<p>', '</p>');
		$data['ubicacionorigen'] = $ubicacionorigen;
		$data['eldestinosinsertar'] = $eldestinosinsertar;
		$data['cantidadLineas'] = $cantidadLineas;
		$data['filenamen'] = $filenamen;
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->view('header.php',$data);
		$this->load->view('generarordenconcarga.php',$data);
		$this->load->view('footer.php',$data);
		// PROCESO POSTERIOR generacion de txt (y dale con el txt() para el ajuste
		$sql = "SELECT right('000000000'+DBA.td_orden_despacho.cod_interno,10) as cp, null as v2, DBA.td_orden_despacho.cantidad as ca, null as v3, '' as v4, ";//DBA.td_orden_despacho.precio_venta ";
		$sql .= " isnull(convert(integer, (DBA.td_orden_despacho.cantidad/(SELECT top 1 unid_empaque FROM DBA.ta_proveedor_producto where cod_proveedor<>'000000000000' and cod_interno=right('000000000'+DBA.td_orden_despacho.cod_interno,10)))),0) as bu ";
		$sql .= "  FROM DBA.tm_orden_despacho join DBA.td_orden_despacho on DBA.tm_orden_despacho.cod_order=DBA.td_orden_despacho.cod_order WHERE dba.tm_orden_despacho.cod_order='".$filenamen."'";
		// TODO agregazr columna de precio sacar de subselect de la orden despacho
		// TODO agregar numero de linea incrementar, sacar del numero de la linea
	//}
	//public function generacionautomatica()
	//{	
		$this->load->dbutil();
		$querypaltxt = $this->db->query($sql);
		$correocontenido = $this->dbutil->csv_from_result($querypaltxt, "\t", "\n", '', FALSE);
		// volvar a un archivo de esta orden despacho asociada:
		// volvar a un archivo de esta orden despacho asociada:
		$this->load->helper('file');
		//appweb/archivoscargas
		$filenameneweordendespachoadjuntar = $cargaconfig['upload_path'] . '/ordendespachogenerada' . $this->numeroordendespacho . '.txt';
		if ( ! write_file($filenameneweordendespachoadjuntar, $correocontenido))
		{
			 echo 'Unable to write the file';
		}
		//$ubicacionorigen='0000a';
		$sql = "select top 1 correo from dba.tm_codmsc_correo where codmsc='".$ubicacionorigen."'";
		$sqlcorreoorigen = $this->db->query($sql);
		$obtuvecorreo = 0;
		foreach ($sqlcorreoorigen->result() as $correorow)
		{
			$correoorigenaenviarle = $correorow->correo;
			$obtuvecorreo++;
		}
		if ($obtuvecorreo < 1)
			$correoorigenaenviarle = 'ordenesdespachos@intranet1.net.ve, lenz_gerardo@intranet1.net.ve';
		
		$this->load->library('email');
		/*
		// esta configuracion requiere mejoras en la libreia, no conecta bien ssl
		$configm1['protocol'] = 'smtp';
		$configm1['smtp_host'] = 'ssl://intranet1.net.ve';
		$configm1['smtp_port'] = '465';
		$configm1['smtp_timeout'] = '8';
		$configm1['smtp_user'] = 'lenz_gerardo';
		$configm1['smtp_pass'] = 'deide.3';
		$configm1['charset'] = 'utf-8';
		$configm1['starttls'] = TRUE;
		$configm1['smtp_crypto'] = 'tls';
		$configm1['newline'] = "\n";
		$configm1['mailtype'] = 'text'; // or html
		$configm1['validation'] = FALSE; // bool whether to validate email or not      
		$this->email->initialize($configm1);
		$this->email->from('ordenesdespachos@intranet1.net.ve', 'ordenesdespachos');
		$this->email->cc($correousuariosesion); 
		$this->email->to($correoorigenaenviarle); // enviar a los destinos de galpones
		$this->email->subject('Orden Despacho '. $this->numeroordendespacho .' Origen:'.$ubicacionorigen.' Destino:'.$eldestinosinsertar);
		//$messageenviar = str_replace("\n", "\r\n", $correocontenido);
		$this->email->message('Orden de despacho adjunta.'.PHP_EOL.PHP_EOL.$correocontenido );
		$this->email->attach($filenameneweordendespachoadjuntar);
		$this->email->send();
*/
		
		
		$configm2['protocol'] = 'mail';// en sysdevel y sysnet envia pero syscenter no
		$configm2['wordwrap'] = FALSE;
		$configm2['starttls'] = TRUE; // requiere sendmail o localmail use courierd START_TLS_REQUIRED=1 sendmail no envia
		$configm2['smtp_crypto'] = 'tls';
//		$configm2['mailtype'] = 'html';
		
		$this->load->library('email');
		$this->email->initialize($configm2);
		$this->email->from('ordenesdespachos@intranet1.net.ve', 'ordenesdespachos');
//		if ($obtuvecorreo < 1)
		    $this->email->cc($correousuariosesion);
		$this->email->reply_to('ordenesdespachos@intranet1.net.ve', 'ordenesdespachos');
		$this->email->to($correoorigenaenviarle ); // enviar a los destinos de galpones
		//if ($obtuvecorreo < 1)
			$this->email->subject('Orden Despacho '. $this->numeroordendespacho .' Origen:'.$ubicacionorigen.' Destino:'.$eldestinosinsertar);
		//else
		//	$this->email->subject('Orden prueba '. $this->numeroordendespacho .' Origen:'.$ubicacionorigen.' Destino:'.$eldestinosinsertar);
		$this->email->message('Orden de despacho adjunta.'.PHP_EOL.PHP_EOL.'**************************************'.PHP_EOL.PHP_EOL.$resultadocargatablatxtmsg/*$data['htmltablageneradodetalle']*/.'***************************************'.PHP_EOL.PHP_EOL.'Orden para el galpon cargar oasis:'.PHP_EOL.PHP_EOL.$correocontenido );
		$this->email->attach($filenameneweordendespachoadjuntar);
		$this->email->send();

		//echo $this->email->print_debugger();
		
	}
}


