<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class mimatrixcontroller extends CI_Controller {

	private $DBGASTO = null;
	private $usuariologin, $sessionflag, $usuariocodger, $acc_lectura, $acc_escribe, $acc_modifi;


/*

LEER :
* 
* http://10.10.34.20/proyectos/proyectos/projects/sysgastos/wiki/Sysgastoswebf1-db-matrix-y-totalizadores
* http://intranet1.net.ve/proyectos/proyectos/projects/sysgastos/wiki/Sysgastoswebf1-db-matrix-y-totalizadores

1) query base que da todos los totales de las tienda s por cada categoria presente
2) query base que da los totales de cada una categoria en todas las tiendas
3) query de totales de tiendas en todas sus categorias ews agrupando el pimero sin mostrar categorias


------- 1 inicio query 1 -------------
    select 
        `cod_entidad` AS `cod_entidad`,
        `des_entidad` AS `des_entidad`,
        `cod_categoria` AS `cod_categoria`,
        `des_categoria` AS `des_categoria`,
        `tipo_categoria` AS `tipo_categoria`,
        `mon_registro` AS `mon_registro`,
        `fecha_concepto` AS `fecha_concepto`,
        `fecha_registro` AS `fecha_registro`
    from
(
select 
        `a`.`cod_entidad` AS `cod_entidad`,
        `b`.`des_entidad` AS `des_entidad`,
        `a`.`cod_categoria` AS `cod_categoria`,
        `c`.`des_categoria` AS `des_categoria`,
        ifnull(`c`.`tipo_categoria`, 'ADMINISTRATIVO') AS `tipo_categoria`,
        sum(ifnull(`a`.`mon_registro`, 0)) AS `mon_registro`,
        substr(`a`.`fecha_concepto`, 1, 6) AS `fecha_concepto`,
        `a`.`fecha_registro` AS `fecha_registro`
    from
        ((`registro_gastos` `a`
        left join `entidad` `b` ON ((`a`.`cod_entidad` = `b`.`cod_entidad`)))
        left join `categoria` `c` ON ((`a`.`cod_categoria` = `c`.`cod_categoria`)))
		-- justo aqui se debe colcoar los filtros de fecha, esto traeta todo si no se hace
	group by `a`.`cod_entidad` , `a`.`cod_categoria` 
    union select 
        `entidad`.`cod_entidad` AS `cod_entidad`,
        `entidad`.`des_entidad` AS `des_entidad`,
        `categoria`.`cod_categoria` AS `cod_categoria`,
        `categoria`.`des_categoria` AS `des_categoria`,
        ifnull(`categoria`.`tipo_categoria`,
                'ADMINISTRATIVO') AS `tipo_categoria`,
        0 AS `mon_registro`,
        '' AS `fecha_concepto`,
        '' AS `fecha_registro`
    from
        (`categoria`
        join `entidad`)
    group by `entidad`.`cod_entidad` , `categoria`.`cod_categoria`
)
 as       `querymatrixnuivel1`
    group by `cod_entidad` , `cod_categoria`
    order by `cod_entidad` , `cod_categoria`

------- 1 fin query 1 -------------


------- 2 inicio query 2 -------------

select
des_categoria, cod_categoria, sum(mon_registro)
 from 
(
-- aqui consulta query 1 incluido el fuiltro, via php es facil usar la variable ya asignada
) as pepe
group by cod_categoria


-------- 2 fin de query 2 --------------



*/

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
				$fechafiltramatrix=$aniomes;	// asigno para despues tomar solo mes
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

		/* ******************************************* */
		/* ******** inicio calulo matrix ************* */
		/* ******************************************* */
		$this->load->helper(array('form', 'url','inflector'));
		$fecha_mesmatrix = $fechafiltramatrix;
        $querymatrixfiltros=
		"
		";
		$querytodolostotales="
			select 
				*
			from
				(
					select 
							`a`.`cod_entidad` AS `cod_entidad`,
							`b`.`des_entidad` AS `des_entidad`,
							`b`.`tipo_entidad` AS `tip_entidad`,
							`a`.`cod_categoria` AS `cod_categoria`,
							`c`.`des_categoria` AS `des_categoria`,
							ifnull(`c`.`tipo_categoria`, 'NORMAL') AS `tipo_categoria`,
							sum(ifnull(`a`.`mon_registro`, 0)) AS `mon_registro`,
							substr(`a`.`fecha_concepto`, 1, 6) AS `fecha_concepto`,
							`a`.`fecha_registro` AS `fecha_registro`
						from
							`registro_gastos` as `a`
						left join 
							`entidad` as `b` ON `a`.`cod_entidad` = `b`.`cod_entidad`
						left join 
							`categoria` as `c` ON `a`.`cod_categoria` = `c`.`cod_categoria`
						where ifnull(`a`.`cod_registro`,'') <> '' 
							/*".$querymatrixfiltros."	/* // TODO justo aqui se debe colcoar los filtros de fecha, esto traeta todo si no se hace */
							and substr(`a`.`fecha_concepto`, 1, 6) = '".$fecha_mesmatrix."' 
						group by `a`.`cod_entidad` , `a`.`cod_categoria` 
						
					union 
						select 
							`s`.`cod_entidad` AS `cod_entidad`,
							`s`.`des_entidad` AS `des_entidad`,
							`s`.`tipo_entidad` AS `tipo_entidad`,
							`d`.`cod_categoria` AS `cod_categoria`,
							`d`.`des_categoria` AS `des_categoria`,
							ifnull(`d`.`tipo_categoria`, 'NORMAL') AS `tipo_categoria`,
							0 AS `mon_registro`,
							'".$fecha_mesmatrix."' AS `fecha_concepto`,
							'".$fecha_mesmatrix."' AS `fecha_registro`
						from
							`categoria` as `d`
						join 
							`entidad` as `s`
								/* // TODO: los filtro tambien aqui OJO */
						group by `s`.`cod_entidad` , `d`.`cod_categoria`
				)
				as `inter`
			group by `cod_entidad` , `cod_categoria`
			order by `cod_entidad` , `cod_categoria`
		";
		// inicializa la tabla html donde se van adosando los montos que iteramos en cada tienda
		$tablestyle = array( 'table_open'  => '<table border="1" cellpadding="0" cellspacing="0" class="table display groceryCrudTable dataTable ui default ">' );
		$this->table->set_caption(NULL);
		$this->table->clear();
		$this->table->set_template($tablestyle);
		// ejecutamos la consulta, devolvera n veces la tienda cuantas categorias aya, X tienda * y categorias = total filas
		$dbobjetomatrixbruto = $this->db->query($querytodolostotales);
		// por ende hay que "saltar en cada cambio de entidad" pues es cuando se repiten las categorias
		$matrixenbruto = $dbobjetomatrixbruto->result_array();
		// inicializo iteradores, exepto 
		$cuantascategoria = $iteratienda = 0;
		$catantes = $catahora = '';
		foreach($matrixenbruto as $tienda=>$fila)	// cada n filas es una tieda repetida "tantas categorias"
		{
			$tieahora = $tieantes =$fila['cod_entidad']; //obtener a lo mero macho inicializa tienda
			// inicializo la columna 1 de la matrix, con la tienda repetida n veces la categoria, la saco una sola vez
			$arrayfilatiendatotales['entidad']=$fila['des_entidad'] . ' (' . $fila['cod_entidad'] . ')';// el nombre de la entidad en primera columna de una fila
			break; // inicializado no itero mas, la proxima es sobre el resto que son montos
		}
		foreach($matrixenbruto as $tienda=>$fila)	// cada n filas es una tieda repetida "tantas categorias"
		{
			if($tieantes != $tieahora)
			{
				// hay una interupción significa que los montos deben ser agregados 
				//break; //en vez de salir, agregar la fila y actualizar $iteantes
				
				$tieahora = $tieantes =$fila['cod_entidad']; //obtener a lo mero macho inicializa tienda
				// inicializo la columna 1 de la matrix, con la tienda repetida n veces la categoria, la saco una sola vez
				$arrayfilatiendatotales['entidad']=$fila['des_entidad'] . ' (' . $fila['cod_entidad'] . ')';// el nombre de la entidad en primera columna de una fila
			    $this->table->add_row($arrayfilatiendatotales);
			}
			else
			{
				// si aun estoy en la misma tienda itero pero en cada elemento de la fila (columna)
			
				$arrayfilatiendatotales[$fila['des_categoria']]=$fila['mon_registro'];// el nombre de la entidad
			}
			$tieahora = $fila['cod_entidad'];
			$iteratienda = $iteratienda + 1;//esto no tiene sentido ahora
			if ( $fila['cod_entidad'] == '945' )
				$data['aad']=$arrayfilatiendatotales;
		}
		//$this->table->add_row($arrayfilatiendatotales);
		
		/* ******************************************* */
		/* ******** final calulo matrix ************* */
		/* ******************************************* */
		 
		
		/* *** ini enviar lo calculado y mostrar vista datos al usuario ********************/
		$data['htmlquepintamatrix'] =  br() . $this->table->generate(); // $this->table->generate(); // html generado lo envia a la matrix
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



}
