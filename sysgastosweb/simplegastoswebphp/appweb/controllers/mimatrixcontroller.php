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
        $data['menu'] = $this->menu->menudesktop();
		$data['seccionpagina'] = 'seccionfiltrarmatrix';		// se indica muestre formulario para filtrar que datos se mostraran

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

		$this->load->view('header.php',$data);
		$this->load->view('mivistamatrix.php',$data);
		$this->load->view('footer.php',$data);

	}

	/*
	 * esta seccion se muestra segun una fecha la contruccion de la matrix
	 * con una fecha itera en las tiendas y totaliza todos los gastos de la fecha
	 * si no recibe fecha entonces asume la unica fecha actual menos un mes (el mes anterior)
	 */
	public function secciontablamatrix( $fechainiparam = '', $fechafinparam = '')
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
		$fechainimatrix = $this->input->get_post('fechainimatrix'); // tomo la fecha del formulario de filtro
		$fechafinmatrix = $this->input->get_post('fechafinmatrix'); // tomo la fecha del formulario de filtro
		if ($fechainimatrix== '')
		{
			if($fechainiparam=='')					// si no se envio inicializo
				$fechainimatrix=date('Ymd');
			else
				$fechainimatrix=$fechainiparam;	// asigno para despues tomar solo mes
		}
		if ($fechafinmatrix== '')
		{
			if($fechafinparam=='')					// si no se envio inicializo
				$fechafinmatrix=date('Ymd');
			else
				$fechafinmatrix=$fechafinparam;	// asigno para despues tomar solo mes
		}
		/* ***** fin OBTENER DATOS DE FORMULARIO ***************************** */

        $usuariocodgernow = $this->session->userdata('cod_entidad');
		// averiguar si elusuario es administrativo o usuario de tienda
		if( $this->session->userdata('logueado') == FALSE)
            redirect('manejousuarios/desverificarintranet');
        if( $usuariocodgernow == null)
            redirect('manejousuarios/desverificarintranet');
        $cod_entidad = $cod_categoria = ''; /* por ahora no se usa, se usara en futuro */

		/* ******************************************* */
		/* ******** inicio calculo matrix ************* */
		/* ******************************************* */
		$this->load->helper(array('form', 'url','inflector'));
		$filtro1 = $filtro2 = $filtro3 = $filtro4 = '';
		if ( trim(str_replace(' ', '', $cod_entidad)) != '')
			$filtro1 = " and	a.cod_entidad = '".$this->db->escape_str($cod_entidad)."' ";
		if ( trim(str_replace(' ', '', $fechainimatrix)) != '')
			$filtro2 = " and CONVERT(a.fecha_concepto,UNSIGNED) >= CONVERT('".$this->db->escape_str($fechainimatrix)."',UNSIGNED)  ";
		if ( trim(str_replace(' ', '', $fechafinmatrix)) != '')
			$filtro3 = " and CONVERT(a.fecha_concepto,UNSIGNED) <= CONVERT('".$this->db->escape_str($fechafinmatrix)."',UNSIGNED)  ";
		if ( trim(str_replace(' ', '', $cod_categoria)) != '')
			$filtro4 = " and a.cod_categoria = '".$this->db->escape_str($cod_categoria)."' ";
		
        $sqlfiltro_enti_con_cate=
		"
		 and a.estado <> 'RECHAZADO'
		";
		$sqltotales_enti_con_cate="
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
							".$sqlfiltro_enti_con_cate."	/* // TODO justo aqui se debe colcoar los filtros de fecha, esto traeta todo si no se hace */
							".$filtro2."
							".$filtro3."
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
							'".$fechainiparam."' AS `fecha_concepto`,
							'".$fechafinparam."' AS `fecha_registro`
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
		$db_obj_enti_con_cate = $this->db->query($sqltotales_enti_con_cate);
		$db_res_enti_con_cate = $db_obj_enti_con_cate->result_array();			// por ende hay que "saltar en cada cambio de entidad" pues es cuando se repiten las categorias
		// ****** 1) inicializando los ciclos y detectando la primera fila a ver repetida n veces
		$sqltotales_enti_cruza_cate=" SELECT ";			// crear select para usar grocery CRUD y datatables html5
		foreach($db_res_enti_con_cate as $tienda=>$fila)	// cada n filas es una tienda repetida "tantas categorias"
		{
			$tieantes = $fila['cod_entidad']; //obtener a lo mero macho inicializa tienda;
			$sqltotales_cruza_fila_uno= "'".trim($fila['des_entidad']) . " (" . $fila['cod_entidad'] . ")'";
			$sqltotales_enti_cruza_cate= $sqltotales_enti_cruza_cate ." 'ENTIDAD' ";
			break; // inicializado no iterar mas, la proxima es sobre el resto que son montos
		}
	  	$codinicial=$tieantes;	//se guarda el valor inicial del código para evitar problemas
		$total_categ_de_tiends=array();//un arreglo para cargar los totales de una categorias en todas las tiendas
		// ****** 2) detectando titulos/categorias de los primeros N filas columna 1 igual y cargar las categorias
		foreach($db_res_enti_con_cate as $tienda=>$fila)	// cada n filas es una tienda repetida "tantas categorias"
		{
			$tieahora = $fila['cod_entidad'];
			if($tieantes != $tieahora)	// inicializar la columna 1 de la matrix, y si cambio la entidad es una nueva fila
			{
				$tieahora = $fila['cod_entidad'];
				$sqltotales_enti_cruza_cate=$sqltotales_enti_cruza_cate.",'TOTAL ENTIDAD' UNION SELECT " .$sqltotales_cruza_fila_uno;
				break;//terminar el ciclo cuando sea otra tienda para volver iterar sus categorias entre "rows" (filas) de el resultado del query array
			}
			else 	// se va iterar entre cada rown de la misma tienda hasta que cambie, estas son las categorias como un indice de cada monto
			{
				$sqltotales_enti_cruza_cate=$sqltotales_enti_cruza_cate.",'".trim($fila['des_categoria'])."'";//ir concatenando las categorias
			    $total_categ_de_tiends[$fila['des_categoria']]=0;//guardar cada categoria como indice de totales de categorias, y valor en cero
			}
	    }
		// ****** 3)   ciclo para cargar los montos  ************* *********** *************
		$total_tienda_tod_categ=0;//acumulador para el calcular el total de las tiendas
		$tieantes=$codinicial;//reasignar el código inicial
		foreach($db_res_enti_con_cate as $tienda=>$fila)	// cada n filas es una tienda repetida "tantas categorias"
		{
			$tieahora = $fila['cod_entidad'];// asignación del código, es imperativo y lógico hacerlo al inicio del ciclo...
			if($tieantes != $tieahora)	// inicializar la columna 1 de la matrix, y si cambio la entidad es una nueva fila
			{
				$sqltotales_enti_cruza_cate=$sqltotales_enti_cruza_cate.",'".number_format($total_tienda_tod_categ, 2, ',', '.')."'";//sumar el monto
		    	$total_tienda_tod_categ=0;//reiniciar a cero para no seguir acumulando
				$sqltotales_enti_cruza_cate=$sqltotales_enti_cruza_cate." UNION SELECT "; //seguir generando el select
				$tieantes =$fila['cod_entidad']; //cambio de entidad, repite n categorias agregar la fila y actualizar $iteantes
				$sqltotales_enti_cruza_cate=$sqltotales_enti_cruza_cate. "'".$fila['des_entidad'] . " (" . $fila['cod_entidad'] . ")'"; //seguir generando el select
			}
			$sqltotales_enti_cruza_cate=$sqltotales_enti_cruza_cate.",'".number_format($fila['mon_registro'], 2, ',', '.')."'";
			$total_tienda_tod_categ=$total_tienda_tod_categ+$fila['mon_registro'];
			/***** calcular los totales de cada categoria, sin queries ni nada, exprimiendo el query ya hecho...¿pa' que más'? */
			$total_categ_de_tiends[$fila['des_categoria']]=$total_categ_de_tiends[$fila['des_categoria']]+$fila['mon_registro'];  //... asi de sencillito ;-) nada de código superultrarequeterecontramegacalifragilisticoespialidosisimo similar a ciertos queries raros...
		}
		$sqltotales_enti_cruza_cate = $sqltotales_enti_cruza_cate.",'".number_format($total_tienda_tod_categ, 2, ',', '.')."'";// al final de cada recorrido de categorias de una tienda, se agrega el total de cada tienda para todas las categorias ( total una tienda en todas las categorias)
		$sqltotales_enti_cruza_cate = $sqltotales_enti_cruza_cate." UNION SELECT  'A LOS TOTALES:'"; //seguir generando el select, ahora con la fila final con los totales de cada categoria de la tiendas (total de una categoria para todas las tiendas)
		$totalmatrix=0;	// recorrer arrelgo para totales , inicializo cero y voy sumando
		foreach( $total_categ_de_tiends as $indicecolum =>$lascateg)	// recorrer el indice de totales de categorias para el select de totales
		{
			$sqltotales_enti_cruza_cate = $sqltotales_enti_cruza_cate.",'".number_format($total_categ_de_tiends[$indicecolum], 2, ',', '.')."'";
			$totalmatrix = $totalmatrix + $total_categ_de_tiends[$indicecolum]; // uso el recorrido para total de todo (total todas las tiendas de todas las categorias)
		}
		$sqltotales_enti_cruza_cate = $sqltotales_enti_cruza_cate.",'".number_format($totalmatrix, 2, ',', '.')."'";	// se adicional el ultimo esquina n,n total de todas cat de todas tiend todo sumado en la esquina inferior
		/* ******************************************* */
		/* ******** final calulo matrix ************* */
		/* ******************************************* */

		/* ************ iniciio de grocery crud usamos una tabla por usuario y crud desde esta *********/
		$sqltotales_enti_cruza_cate_table = "gas_matrix_totales_entidad_categoria_" . $userintranet;
		$sqltotales_enti_cruza_cate_final =
		"
		CREATE TABLE ".$sqltotales_enti_cruza_cate_table." AS " . $sqltotales_enti_cruza_cate . "  ORDER BY ENTIDAD  /* en el calculo se llamo entida la primera columna */
		";
		$sqltotales_enti_cruza_cate_final_nohea =
		"
		DELETE FROM ".$sqltotales_enti_cruza_cate_table." WHERE `ENTIDAD`='ENTIDAD'; /* borrar el primer registro, que sirve para definir nombre de columnas */
		";
		$sqltotales_enti_cruza_cate_final_del =
		"
		DROP TABLE IF EXISTS ".$sqltotales_enti_cruza_cate_table."; /* si existe alguna tabla mejor crear una nueva, eliminamos previas, pues son temporales */
		";
		$this->db->query($sqltotales_enti_cruza_cate_final_del); // limpiamos cualquer rastro anterior no completado
		$this->db->trans_strict(TRUE); // asegurar una tabla por usuario con la data filtrada
		$this->db->trans_begin();	// en una tabla por usuario (temporal) solo registros ultimos y por perfil
		$this->db->query($sqltotales_enti_cruza_cate_final); // creamos la tabla por usuario con datos exclusivos por usuarios
		$this->db->trans_commit();
		$this->db->query($sqltotales_enti_cruza_cate_final_nohea); // eliminar el query union que sirve para que se definan los nombres de columnas (select from query en vez de table)
		$this->load->helper(array('inflector','url'));	// inicar el pintar bonito los datos de una tabla temporal matrix cruzada
		$this->load->library('grocery_CRUD');		// uso la libreria que pinga bonito una tabla
		$crud = new grocery_CRUD();			// creo el objeto crud a mostrar en html
		$crud->set_theme('datatables'); 		// flexigrid tiene bugs pero exporta solo openoffice
		$crud->set_table($sqltotales_enti_cruza_cate_table);	// la tabal es temporal pero del usuario
		$crud->set_primary_key('ENTIDAD');	// la tabla es temporal, forzar PK
		$crud->unset_add();			// no se adiconan registros, es reportar
		$crud->unset_read();	// se creara despues un boton que llame el total en dicha tienda
		$crud->unset_edit();		// se desabilita cualquer ediccion
		$crud->unset_delete();		// aqui nada se pierde, no borrar
		// $crud->add_action('AVISO Y AYUDA', '', '','ui-icon-plus',array($this,'_redirecciontotalizadores')); // TODO: enlazar totalizadores aqui
		$output = $crud->render();		// pinta el html con tabletools
		$this->db->query($sqltotales_enti_cruza_cate_final_del); // limpiar db de la tabla usada temporalmente para el grocerycrud
		$data['js_files'] = $output->js_files;
		$data['css_files'] = $output->css_files;
		$data['output'] = $output->output;
		/* ************ fin de grocery crud usamos una tabla por usuario y crud desde esta *********/

		/* *** ini enviar lo calculado y mostrar vista datos al usuario ********************/
		$data['htmlquepintamatrix'] =  br() ; // $this->table->generate(); // html generado lo envia a la matrix
		$data['usercorreo'] = $usercorreo;
		$data['userintranet'] = $userintranet;
		$data['menu'] = $this->menu->menudesktop();
		$data['seccionpagina'] = 'secciontablamatrix';
		$data['fechainimatrix'] = $fechainimatrix;
		$data['fechafinmatrix'] = $fechafinmatrix;
		$this->load->view('header.php',$data);
		$this->load->view('mivistamatrix.php',$data);
		$this->load->view('footer.php',$data);
		/* *** fin enviar lo calculado y mostrar vista datos al usuario ********************/
	}
	public function _redirecciontotalizadores($primary_key, $row)
	{
		//$enlace = site_url('matrixcontroler/matrixtotalesfiltrado/?fechainimatrix='.'&cod_entidad='.$row->ENTIDAD);
		//return "javascript:window.open ('".$enlace."','NOtificador','menubar=1,resizable=1,width=350,height=250');";
		return "javascript:alert('Si solo aparecen pocos registros, revise no tenga filtros, use el boton arriba resetear filtros<br>\na la derecha en la primera linea!!!');";
	}
    
}
