<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Consultarordendespachos extends CI_Controller {

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
		// quiery plano y ejecucion
		$sqlultimodespachosdias = "SELECT TOP 20 a.cod_order as orden_id, a.cod_interno AS cod_producto ,(select txt_descripcion_larga from dba.tv_producto where cod_interno=a.cod_interno) AS des_producto ,a.cantidad AS can_cantidaddespachar,a.precio_venta as precio_archivo FROM dba.td_orden_despacho as a WHERE a.cod_order LIKE 'ordendespachocarga" . date("Y") . date("m") . date("d") . /*date("H") .*/ '%' ."' ORDER BY a.cod_order DESC;";
		$resultadoultimodespachosdias = $this->db->query($sqlultimodespachosdias);
		$ultimodespachosdias = $resultadoultimodespachosdias->result_array();
		
		$tmplnewtable = array ( 'table_open'  => '<table border="1" cellpadding="1" cellspacing="1" class="table">' );
		$this->table->set_caption(NULL);
		$this->table->clear();
		$this->table->set_template($tmplnewtable);
		$this->table->set_heading('orden_id','des_producto','cod_producto','can_cantidaddespachar','precio_archivo');
		$lascincoul=0;
		foreach ($ultimodespachosdias as $rowtable)
		{
			if ($lascincoul<20)
				$this->table->add_row($rowtable['orden_id'], $rowtable['des_producto'], $rowtable['cod_producto'], $rowtable['can_cantidaddespachar'], $rowtable['precio_archivo']);
			$lascincoul++;
		}
		if ($lascincoul<1)
			$this->table->add_row('Ninguna orden aun', '', '', 'GENERE UNA!!', 'Pongase a trabajar!!');
		$data['htmltablaultimosdespachosordenados'] = $this->table->generate();
		$this->load->view('header.php',$data);
		$this->load->view('consultarordendespachos.php',$data);
		$this->load->view('footer.php',$data);
	}
	
	public function consultadecargasordenesdespacho($coddespacho='')
	{
		// OBTENER DATOS DE FORMULARIO ***************************** /
		$delimitador = $this->input->get_post('archivoproductospreciosep');
		$ubicacionorigen = $this->input->get_post('ubicacionorigen');
		$ubicaciondestin = $this->input->get_post('ubicaciondestin');
		$fechabusqueda = $this->input->get_post('fechainicio');
		// CARGA DEL ARCHIVO ****************************************************** /
		$cargaconfig['upload_path'] = CATAPATH . '/appweb/archivoscargas';
		$cargaconfig['allowed_types'] = 'txt|.';
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
		$filenamen = 'ordendespachocarga' . date("Y") . date("m") . date("d") . date("H") . date("i") .'.txt';
        $filenameorig =  $file_data['file_path'] . $file_data['file_name'];
        $filenamenewe =  $file_data['file_path'] . $filenamen;
        //copy( $filenameorig, $filenamenewe); // TODO: rename
        rename( $filenameorig, $filenamenewe);
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
            $sql = "INSERT INTO dba.test_detalleordendespacho (cod_interno, cantidad, precio_venta, cod_orden, ord_origen) VALUES ";
			foreach ($csv_array as $row) 
            {
				$sql .= "(".$this->db->escape($row['cod_producto']).", ".$this->db->escape($row['can_cantidad']).", ".$this->db->escape($row['can_cantidad']).", ".$this->db->escape($filenamen).", ".$this->db->escape($filenameorig)."),";
                $cantidadLineas++;
            }
            $sql = substr ($sql, 0, -1);
			$this->db->query($sql);
			
            $sql = "INSERT INTO dba.test_masterordendespacho (cod_orden, estado, nom_usuario, cantidadLineas, origen, destino) VALUES 
					(".$this->db->escape($filenamen).", '0', 'systemas@intranet1.net.ve', ".$this->db->escape($cantidadLineas).", ".$this->db->escape($ubicacionorigen).", ".$this->db->escape($ubicaciondestin).")";
			$this->db->query($sql);
        // CARGA EXITOSA UESTRO DETALLE ************************************* /
			$sqlcargado = "SELECT 
				(select txt_descripcion_larga from dba.tv_producto where cod_interno=a.cod_interno) AS des_producto
				,a.cod_interno AS cod_producto
				,a.cantidad AS can_cantidaddespachar
				,a.precio_venta as precio_archivo
				,(select mto_precio from dba.ta_precio_producto where cod_precio=0 and cod_interno=a.cod_interno and cod_sucursal=(select num_sucursal from DBA.tc_codmsc where cod_msc='".$ubicacionorigen."')) as precio_origen
				,(select mto_precio from dba.ta_precio_producto where cod_precio=0 and cod_interno=a.cod_interno and cod_sucursal=(select num_sucursal from DBA.tc_codmsc where cod_msc='".$ubicaciondestin."')) as precio_destino
				,(select convert(integer ,saldo_producto) from tv_existencia where cod_interno = a.cod_interno and cod_msc='".$ubicacionorigen."') as saldo_origen
				FROM dba.test_detalleordendespacho as a
				where cod_orden = '".$filenamen."'";
			$resultadocarga = $this->db->query($sqlcargado); //row_array
        }
        // agrega este arreglo como dos listas una de origen y una de destino
		$data['resultadocarga'] = $resultadocarga->result_array();
		// TERMINAR EL PROCESO **************************************************** /
		$this->table->clear();
		$tmplnewtable = array ( 'table_open'  => '<table border="1" cellpadding="1" cellspacing="1" class="table">' );
		$this->table->set_template($tmplnewtable);
		$data['menu'] = $this->menu->general_menu();
		$data['accionejecutada'] = 'resultadoordenescargadas';
		
		$data['upload_errors'] = $this->upload->display_errors('<p>', '</p>');
		$data['ubicacionorigen'] = $ubicacionorigen;
		$data['ubicaciondestin'] = $ubicaciondestin;
		$data['cantidadLineas'] = $cantidadLineas;
		$data['filenamen'] = $filenamen;
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->view('header.php',$data);
		$this->load->view('consultarordendespachos.php',$data);
		$this->load->view('footer.php',$data);
	}
}


