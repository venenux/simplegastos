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
		$this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT',TRUE);
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', TRUE);
		$this->output->set_header('Pragma: no-cache', TRUE);
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT", TRUE);
		$this->output->enable_profiler(TRUE);
	}

	/* este es un verificador del objeto sesion, 
	 * se invoca en cada funcion-seccion para certificar el usuario es valido y presente
	 * y asi una llamada directa desde internet no se relize */
	public function _verificarsesion()
	{
		// si el semaforo logeado no esta presente se sale por seguridad, solo usuarios validos
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
		// si no se especifica mostrar seccion que pide que datos se filtran en la matrix
		$this->mimatrixfiltrar();
	}

	/* esta funciona inicializa datos para un formulario de filtro en la vista
	 * y asi poder mostrar una matrix de solo unas fechas y no de todo lo que existe que es mucho
	 * le indica que mes inicial y que campos tiene el formulario */
	public function mimatrixfiltrar()
	{
		$userdata = $this->session->all_userdata();		// tomo los datos del usuario actual si existe
		$this->_verificarsesion();						// verifico este un usuario realizando la llamada
		$usuariocodgernow = $this->session->userdata('cod_entidad');	// aun si es valido debe tener permisos
        if( $usuariocodgernow == null or trim($usuariocodgernow,'') == '')
            redirect('manejousuarios/desverificarintranet'); 	// si el usuario no tiene alguna asociacion de entidad se le deniega
        $data['menu'] = $this->menu->general_menu();
		$data['seccionpagina'] = 'seccionfiltrarmatrix';		// se indica muestre formulario para filtrar que datos se mostraran
		$this->load->view('header.php',$data);
		$this->load->view('mivistamatrix.php',$data);
		$this->load->view('footer.php',$data);

	}

	/*
	 * esta seccion se muestra segun una fecha la contruccion de la matrix
	 * con una fecha itera en las tiendas y totaliza todos los gastos de la fecha
	 * si no recibe fecha entonces asume la unica fecha actual menos un mes (el mes anterior)
	 */
	public function secciontablamatrix( $aniomes = NULL)
	{
		/* ***** ini manejo de sesion ******************* */
		$userdata = $this->session->all_userdata();		// tomo los datos del usuario actual si existe
		$this->_verificarsesion();						// verifico este un usuario realizando la llamada
		$usuariocodgernow = $this->session->userdata('cod_entidad');	// aun si es valido debe tener permisos
        if( $usuariocodgernow == null or trim($usuariocodgernow,'') == '')
            redirect('manejousuarios/desverificarintranet'); 	// si el usuario no tiene alguna asociacion de entidad se le deniega
		$usercorreo = $userdata['correo'];
		$userintranet = $userdata['intranet'];
		/* ***** fin manejo de sesion ******************* */

		/* ***** ini OBTENER DATOS DE FORMULARIO **************************** */
		$aniomes='';			// inicializo una marca de fecha de referencia
		$fechafiltramatrix = $this->input->get_post('fechafiltramatrix'); // tomo la fecha del formulario de filtro
		if ($fechafiltramatrix== '')
		{
			if($aniomes=='')					// si no se envio inicializo
				$fechafiltramatrix=date('Ym');
			else
				$fechafiltramatrix=$aniomes;	// asigno apra despues tomar solo mes
		}
		$aniomes=substr($fechafiltramatrix, 0, 6); //aqui tomo solo el mes (por eso el formato anio/mes/dia pegado)
		$fechafiltramatrix=$aniomes;				// coloco ambas variables iguales y ya tengo que mes
		/* ***** fin OBTENER DATOS DE FORMULARIO ***************************** */
		
		// averiguar si elusuario es administrativo o usuario de tienda
        	if( $this->session->userdata('logueado') == FALSE)
        {
            redirect('manejousuarios/desverificarintranet');
        }
        $usuariocodgernow = $this->session->userdata('cod_entidad');
        if( $usuariocodgernow == null)
        {
            redirect('manejousuarios/desverificarintranet');
        }

		/* ******** inicio de los querys ************* */
		$this->load->helper(array('form', 'url','inflector'));
         
         /*************** preparar el query para las sucursales */
         
         
         
         /*determinar nivel de usuario para mostrar
           las tiendas (sucursales) en la matrix           */
          if ($usuariocodgernow > 990 and $usuariocodgernow < 998 )
		  { $queryentidades=  $queryentidades ="
		    select
		     ifnull(abr_entidad,'S/A') as siglas,cod_entidad as codigo
		   from
		    entidad
		    where (cod_entidad > '399' and cod_entidad < '990') " ;
		   }
	      if ($usuariocodgernow > 399 and $usuariocodgernow < 990  )
		  { $queryentidades =  " 
		    select
		     ifnull(abr_entidad,'S/A') as siglas,cod_entidad as codigo
		   from
		    entidad
		    where cod_entidad = '".$usuariocodgernow."'" ;
		   }
	      if ($usuariocodgernow < 399 )
		  { $queryentidades=  $queryentidades ="
		    select
		     ifnull(abr_entidad,'S/A') as siglas,cod_entidad as codigo
		   from
		    entidad
		    where  ifnull(cod_entidad,'') <> '' " ;
		   }
	      
	      if($usuariocodgernow == 998)
	      {$queryentidades = "
	        select
		     CONCAT( '(', abr_entidad, ') \n<br>', substring(ifnull(des_entidad,'S/A'), 1, 28) ) as siglas,cod_entidad as codigo, des_entidad 
		   from
		    entidad
		    where  ifnull(cod_entidad,'') <> '' " ;
	       }
		   // pero filtrar las entidades activas nada mas
			$queryentidades = $queryentidades." and ( status <> 'INACTIVO') order by abr_zona desc";	 
         // buscar las tiendas en un rango ordenadas por zona
		 
		$indicet=0;
		$lastiendas= $this->db->query($queryentidades);
		$tiendas= array();
		foreach ($lastiendas->result() as $row)
		{
			$tiendas[$row->codigo]=$row->siglas;
		}
		$xtiendas= (string)count($tiendas);// ya se cuantas tiendas


		/* crear el query sql para cargar las cabeceras, tomando en cuenta
		 el nivel usuario (codger) para listar  */
		
		$querycabeceras ="
		SELECT
		   cod_categoria as codex, substring(des_categoria, 1, 11)  as categoria
		FROM
		   categoria
		";
		
		
		$micabeceras = $this->db->query($querycabeceras);
        $finalindex=1;
        $filafinal=array(0=>'Totales ('.$xtiendas.'):');
		$categorias= array('0'=>'Tiendas ('.$xtiendas.'): ');

		foreach ($micabeceras->result() as $row)
		{
			$categorias[$row->codex]=$row->categoria;
            //aprovecho este ciclo para cargar con cero los totales
            $filafinal[$finalindex]= (float)0.00;
		    $finalindex= $finalindex+1;
		}
		$categorias[count($categorias)]='TOTAL:';

		 // generar fila por fila la tabla
		$maxcat=count($categorias) ;
		$elgraantootal =0;
		$tablestyle = array( 'table_open'  => '<table border="1" cellpadding="0" cellspacing="0" class="table display groceryCrudTable dataTable ui default ">' );
		$this->table->set_caption(NULL);
		$this->table->clear();
		$this->table->set_template($tablestyle);
		$this->table->set_heading($categorias);
		foreach($tiendas as $indicetienda => $tiend)
		{
			$icat=0;			// en primera fila las categorias
            $finalindex=1; 		// inicio en 1 porque el 0 ya tiene la esquina 0,0
			$fila =array($tiend);		// cada fila es una tienda
			foreach ($categorias as $indicecategoria=> $descripcioncat)	// por cada tienda llenara una categoria  de totales
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
				  registro_gastos.fecha_concepto like '".$fechafiltramatrix."%'
				and
				  registro_gastos.estado <> 'RECHAZADO'
				 ";

                //aqui se calcula el gasto categoria por tienda
                if ($icat<$maxcat-1)
                {

					if ($icat>0)
					{
						$lasuma=$this->db->query($querysuma1);
						foreach ($lasuma->result() as $row)
						{ 
							$total=$row->suma;break;
						}
						// se coloca en la posicion de la categoria pero ya formateado el total de esta tienda de esta categoria
						$fila[$icat]=number_format($total,2,',','.');

						// calculo de la suma de una categoria en todas las tiendas
						$totalestafilafinal = (float)$filafinal[$finalindex] + (float)$total;
						$filafinal[$finalindex]= $totalestafilafinal;
						  
						$finalindex= $finalindex+1;	// siguiente tienda
					}
					$icat = $icat +1;		// ya listo sigueinte categoria para todas las tiendas
			    }
			    else
			    {
					// caso contrario es la fila final despues de todas las tiendas totales
					// aqui se calcula el total todas categoria en una tienda, es decir total gastado de una tienda
					$querygastotiendasfullcat="
					 Select
						ifnull(cast(sum(mon_registro)  as decimal(30,2)),0) as sumatienda
					  from registro_gastos where
						registro_gastos.cod_entidad = '".$indicetienda."'
						and
					  registro_gastos.fecha_concepto like '".$fechafiltramatrix."%'      ";
					// se itera para tener el resultado en php
					$totaltienda=$this->db->query($querygastotiendasfullcat);
					foreach ($totaltienda->result() as $row)
					{ 
						$totalfullcat2=$row->sumatienda;
						$totalfullcat=number_format((float)$totalfullcat2,2,',','.');
						break;
					}
					//acumular el total cada categoria
					$fila[$icat]=$totalfullcat;
			    }
			}
			// ya se construyo la fila completa, se agrega a la tabla en construccion
			$this->table->add_row($fila);
			// el ultimo cuadro n,n de la tabla es el total gastado en el mes
			$elgraantootal=(float)$elgraantootal+(float)$totalfullcat2;
			// uff tanto  trabajo  ---> nojoda y quedo chimbo hay que hacerlo de nuevo tyron, esto resulta en 3mil queryS!!!!! <<
		}
		// se agrega el ultimo cuadro n,n a la tabla, adicionandolo a la fila final de totales por categorias
		$filafinal[$finalindex+1]= number_format($elgraantootal,2,',','.');
		// se manda renderizar la tabla
		$this->table->add_row($filafinal);
		/* **** fin de calculo matrix *************** */
		
		/* *** ini enviar lo calculado y mostrar vista datos al usuario ********************/
		$data['htmlquepintamatrix'] = $this->table->generate(); // html generado lo envia a la matrix
		$data['usercorreo'] = $usercorreo;
		$data['userintranet'] = $userintranet;
		$data['menu'] = $this->menu->general_menu();
		$data['Fecha:']=$fechafiltramatrix;
		$data['seccionpagina'] = 'secciontablamatrix';
		$data['userintran'] = $userintranet;
		$data['fechafiltramatrix'] = $fechafiltramatrix;
		$this->load->view('header.php',$data);
		$this->load->view('mivistamatrix.php',$data);
		$this->load->view('footer.php',$data);
		/* *** fin enviar lo calculado y mostrar vista datos al usuario ********************/
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
