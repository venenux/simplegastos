<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class mimatrixcontroller extends CI_Controller {

	private $DBGASTO = null;
	private $usuariologin, $sessionflag, $usuariocodger, $acc_lectura, $acc_escribe, $acc_modifi;

	function __construct()
	{
		parent::__construct();
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->model('menu');
		$this->load->database('gastossystema');
		$this->output->enable_profiler(TRUE);
	}

	public function _verificarsesion()
	{
		if( $this->session->userdata('logueado') != TRUE)
			redirect('manejousuarios/desverificarintranet');
	}

	/**
	 * Index Page cuando se invoca la url de este controlador,
	 * aqui se invoca la vista o otro metodo que la invoque
	 * map to /index.php/plantillamatrixcontroler/index
	 */
	public function index()
	{
		$this->secciontablamatrix();
	}

	public function mimatrixfiltrar()
	{
		$userdata = $this->session->all_userdata();
		$this->_verificarsesion();
		$usercorreo = $userdata['correo'];
		$userintranet = $userdata['intranet'];
		$sessionflag = $this->session->userdata('username').date("YmdHis");
				$usuariocodgernow = $this->session->userdata('cod_entidad');

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
			if($usuariocodgernow >399 and $usuariocodgernow < 998)
				$sqlcategoria .= " and cod_categoria NOT LIKE 'CAT2016000012%'";
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
			if($usuariocodgernow >399 and $usuariocodgernow < 998)
				$sqlsubcategoria .= " and sb.cod_categoria NOT LIKE 'CAT2016000012%'";
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
			if($usuariocodgernow >399 and $usuariocodgernow < 998)
				$data['pepe'] = $sqlentidad .= " and cod_entidad = '".$usuariocodgernow."'";
			$resultadosentidad = $this->db->query($sqlentidad);
			$arregloentidades = array(''=>'');
			foreach ($resultadosentidad->result() as $row)
			{
				$arregloentidades[''.$row->cod_entidad] = $row->cod_entidad . ' - ' . $row->abr_entidad .' - ' . $row->des_entidad . ' ('. $row->abr_zona .')';
			}
			$data['list_entidad'] = $arregloentidades; // agrega este arreglo una lista para el combo box
			unset($arregloentidades['']);

		$data['usercorreo'] = $usercorreo;
		$data['userintranet'] = $userintranet;
		$data['menu'] = $this->menu->general_menu();
		$data['seccionpagina'] = 'seccionfiltrarmatrix';
		$data['userintran'] = $userintranet;
		$this->load->view('header.php',$data);
		$this->load->view('mivistamatrix.php',$data);
		$this->load->view('footer.php',$data);

	}

	public function secciontablamatrix()
	{
		/* ***** ini manejo de sesion ******************* */
		$userdata = $this->session->all_userdata();
		$this->_verificarsesion();
		$usercorreo = $userdata['correo'];
		$userintranet = $userdata['intranet'];
		$sessionflag = $this->session->userdata('username').date("YmdHis");
		$data['usercorreo'] = $usercorreo;
		$data['userintranet'] = $userintranet;
		$data['menu'] = $this->menu->general_menu();

		/* ******** inicio de los querys ************* */
		$this->load->helper(array('form', 'url','inflector'));

		 $queryentidades ="
		 select
		   ifnull(abr_entidad,'S/A') as siglas,cod_entidad as codigo
		 from
		   entidad
		  where (cod_entidad >=000 and   cod_entidad <=005) or (cod_entidad >=400 and   cod_entidad <=998) 
		    and abr_entidad <>'' order by siglas
		 ";
		$indicet=0;
		$lastiendas= $this->db->query($queryentidades);
		$tiendas= array();
		foreach ($lastiendas->result() as $row)
		{
			$tiendas[$row->codigo]=$row->siglas;
		}
		$xtiendas= count($tiendas);// ya se cuantas tiendas


		// crear el query sql para cargar las cabeceras
		$querycabeceras ="
		SELECT
		   cod_categoria as codex,des_categoria  as categoria
		FROM
		   categoria
		";
		// aquiio se establece las cabeceras
		$micabeceras = $this->db->query($querycabeceras);
        $finalindex=1;
        $filafinal=array(0=>'Totales:');
		$categorias= array('0'=>'Tiendas');
		
		foreach ($micabeceras->result() as $row)
		{
			$categorias[$row->codex]=$row->categoria;
            //aprovecho este ciclo para cargar con cero los totales
            $filafinal[$finalindex]= 0.00;
		    $finalindex= $finalindex+1;
		}
		$categorias[count($categorias)]='   TOTAL:   ';

		/* ***** ini OBTENER DATOS DE FORMULARIO (con esto no me meto todavia) ***************************** */
		$fechafiltramatrix = $this->input->get_post('fechafiltramatrix');
		if( $fechafiltramatrix == '' or empty($fechafiltramatrix))
			$fechafiltramatrix = date('Ymd');
		$cod_entidad = $this->input->get_post('cod_entidad');
		$cod_subcategoria = $this->input->get_post('cod_subcategoria');
		/* ***** fin OBTENER DATOS DE FORMULARIO ***************************** */
		 // generar fila por fila la tabla
		$maxcat=count($categorias) ;
		$elgraantootal =0;
		$tablestyle = array( 'table_open'  => '<table border="1" cellpadding="2" cellspacing="2" class="table display groceryCrudTable dataTable ui default ">' );
		$this->table->set_caption(NULL);
		$this->table->clear();
		$this->table->set_template($tablestyle);
		$this->table->set_heading($categorias);
		$fechachucuta=substr($fechafiltramatrix, 0, 6);
		foreach($tiendas as $indicetienda => $tiend)
		{
			$icat=0;
            $finalindex=1; 
			$fila =array($tiend);
			foreach ($categorias as $indicecategoria=> $descripcioncat)
			{   
				$querysuma1="
				Select
				  ifnull(cast(sum(mon_registro) as decimal(30,2)),0) as suma
				from
				  registro_gastos
				where
			      registro_gastos.cod_entidad =  '".$indicetienda."'
				and
				  registro_gastos.cod_categoria='".$indicecategoria."'
				and  
				  registro_gastos.fecha_registro like '%".$fechachucuta."%'		 
				 ";
                
                //aqui se calcula el gasto categoria por tienda
                 if ($icat<$maxcat-1){

				  if ($icat>0){
				  $lasuma=$this->db->query($querysuma1);
                  foreach ($lasuma->result() as $row)
		          { $total=$row->suma;break;}
			      $fila[$icat]=$total;

			      // calculo de la suma de una categoria en todas las tiendas
                  $filafinal[$finalindex]=$filafinal[$finalindex] + $total; 
                  $finalindex= $finalindex+1;
                  			      }
			     $icat = $icat +1;
			     }else
			     {
			      // aqui se calcula el total en una categoria en una tienda
			   
                     $querygastotiendasfullcat="
			      Select
			        ifnull(cast(sum(mon_registro)  as decimal(30,2)),0) as sumatienda
			      from registro_gastos where
			        registro_gastos.cod_entidad = '".$indicetienda."'
			      	and  
				  registro_gastos.fecha_registro like '%".$fechachucuta."%'      ";

			      $totaltienda=$this->db->query($querygastotiendasfullcat);
			      foreach ($totaltienda->result() as $row)
		          { $totalfullcat=$row->sumatienda;break;}
			        
			        $fila[$icat]= $totalfullcat;
			        //acumular el total cada categoria
                   
			      }
			 }
			   $this->table->add_row($fila);
			   $elgraantootal=$elgraantootal+$totalfullcat;
			   
		//   uff tanto  trabajo
	  }
		$filafinal[$finalindex+1]= $elgraantootal;
		$this->table->add_row($filafinal);
		$data['htmlquepintamatrix'] = $this->table->generate(); // html generado lo envia a la matrix
		/* ***** fin pintar una tabla recorriendo el query **************** */
		$data['Menú'] = $this->menu->general_menu();
		$data['Categorias Cargadas'] = $categorias;
		$data['Tiendas Cargadas:'] = $tiendas;
		$data['Fecha:']=$fechafiltramatrix;
		$data['Fecha recortada:']=$fechachucuta;
		$data['seccionpagina'] = 'secciontablamatrix';
		$data['userintran'] = $userintranet;
		$data['fechafiltramatrix'] = $fechafiltramatrix;
		$data['cod_entidad'] = $cod_entidad;
		$data['cod_subcategoria'] = $cod_subcategoria;
		$this->load->view('header.php',$data);
		$this->load->view('mivistamatrix.php',$data);
		$this->load->view('footer.php',$data);
	}

	/*{
		// PROCESO POSTERIOR generacion de txt y envio por correo
		$sql = "SELECT right('000000000'+DBA.td_orden_despacho.cod_interno,10) as cp, null as v2, DBA.td_orden_despacho.cantidad as ca, null as v3, '' as v4, ";//DBA.td_orden_despacho.precio_venta ";
		$sql .= " isnull(convert(integer, (DBA.td_orden_despacho.cantidad/(SELECT top 1 unid_empaque FROM DBA.ta_proveedor_producto where cod_proveedor<>'000000000000' and cod_interno=right('000000000'+DBA.td_orden_despacho.cod_interno,10)))),0) as bu ";
		$sql .= "  FROM DBA.tm_orden_despacho join DBA.td_orden_despacho on DBA.tm_orden_despacho.cod_order=DBA.td_orden_despacho.cod_order WHERE dba.tm_orden_despacho.cod_order='".$filenamen."'";
		$this->load->dbutil();
		$querypaltxt = $this->query($sql);
		// ejemplo desde el sql generamos un adjunto
		$correocontenido = $thisutil->csv_from_result($querypaltxt, "\t", "\n", '', FALSE);
		$this->load->helper('file');
		$filenameneweordendespachoadjuntar = $cargaconfig['upload_path'] . '/ordendespachogenerada' . $this->numeroordendespacho . '.txt';
		if ( ! write_file($filenameneweordendespachoadjuntar, $correocontenido))
		{arreglos bidimensionales en php
			 echo 'Unable to write the file';
		}
		// en la db buscamos el correo del usuario y vemos a cuantos se enviaran
		$sql = "select top 1 correo from dba.tm_codmsc_correo where codmsc='".$intranet."'";
		$sqlcorreoorigen = $this->query($sql);
		$obtuvecorreo = 0;
		foreach ($sqlcorreoorigen->result() as $correorow)
		{
			$correoorigenaenviarle = $correorow->correo;
			$obtuvecorreo++;
		}
		if ($obtuvecorreo < 1)
			$correoorigenaenviarle = 'ordenesdespachos@intranet1.net.ve, lenz_gerardo@intranet1.net.ve';
		// ahora procedemos apreparar el envio de correo
		$this->load->library('email');
		$configm1['protocol'] = 'smtp'; 		// esta configuracion requiere mejoras
		$configm1['smtp_host'] = 'ssl://intranet1.net.ve'; // porque en la libreia, no conecta bien ssl
		$configm1['smtp_port'] = '465';
		$configm1['smtp_timeout'] = '8';
		$configm1['smtp_user'] = 'usuarioqueenviacorreo';
		$configm1['smtp_pass'] = 'superclave';
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
		$this->email->subject('Orden Despacho '. $this->numeroordendespacho .' Origen:'.$intranet.' Destino:'.$fechafiltramatrix);
		//$messageenviar = str_replace("\n", "\r\n", $correocontenido);
		$this->email->message('Orden de despacho adjunta.'.PHP_EOL.PHP_EOL.$correocontenido );
		$this->email->attach($filenameneweordendespachoadjuntar);
		$this->email->send();
/*
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
			$this->email->subject('Registro de gasto '. $this->numeroordendespacho .' Responsable:'.$intranet.' Fecha registro:'.$fechafiltramatrix);
		//else
		//	$this->email->subject('Orden prueba '. $this->numeroordendespacho .' Origen:'.$intranet.' Destino:'.$fechafiltramatrix);
		$this->email->message('Orden de despacho adjunta.'.PHP_EOL.PHP_EOL.'**************************************'.PHP_EOL.PHP_EOL.$resultadocargatablatxtmsg.$data['htmltablageneradodetalle'].'***************************************'.PHP_EOL.PHP_EOL.'Orden para el galpon cargar oasis:'.PHP_EOL.PHP_EOL.$correocontenido );
		$this->email->attach($filenameneweordendespachoadjuntar);
		$this->email->send();

		//echo $this->email->print_debugger();

	}*/

}
