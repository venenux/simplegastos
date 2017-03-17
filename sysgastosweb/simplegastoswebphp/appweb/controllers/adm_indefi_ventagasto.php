<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Adm_indefi_ventagasto extends CI_Controller {

	private $mensage = 'Indicadores de Gestion: eficiencia de ventas vs gasto.';
	private $controlername = 'adm_indefi_ventagasto';
	private $accionformulario  = null;
	private $menurender = '';

	public function __construct()
	{
		parent::__construct();
		$this->load->database('gastossystema');
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->helper(array('form', 'url','html'));
		$this->load->library('table');
		$this->load->model('menu');
		$this->load->library('grocery_CRUD');
		$this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT',TRUE);
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', TRUE);
		$this->output->set_header('Pragma: no-cache', TRUE);
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT", TRUE);
		$this->output->enable_profiler(TRUE);
	}

	public function _verificarsesion()
	{
		if( $this->session->userdata('logueado') != TRUE)
			redirect('manejousuarios/desverificarintranet');

		$usuariocodgernow = $this->session->userdata('cod_entidad');
		if( is_array($usuariocodgernow) )
		{
			if (! in_array("111", $usuariocodgernow) or ! in_array("998", $usuariocodgernow) )
				$this->nivel = false;
			else
				$this->nivel = true;
		}
		else
		{
			if( $usuariocodgernow == '998' or $usuariocodgernow == '111' )
				$this->nivel = false;
			else
				$this->nivel = true;
		}
		if ( $this-nivel )
		{
			$this->menurender = $this->menu->general_menu();
		}
	}

	public function _esputereport($data, $output = null)
	{
		$data['controller'] = $this->controlername;
		$data['mensage'] = $this->mensage;  // cualquier mensage de error debe ser enviado aqui
		if( $output != null )
		{
			$data['js_files'] = $output->js_files;
			$data['css_files'] = $output->css_files;
			$data['output'] = $output->output;  // toda salida html debe ir aqui, la vista pintara esto
			$data['fecha_mes'] = $output->fecha_mes;
		}
		$data['accionformulario'] = $this->accionformulario;
		$data['menu'] = $this->menu->general_menu();  // definir un menu segun perfil
		$data['controlername'] = $this->controlername;
		$this->load->view('header.php',$data);
		$this->load->view('adm_indicadores_verdata.php',$data);
		$this->load->view('footer.php',$data);
	}

	function index()
	{
		//$this->_verificarsesion();
		$this->accionformulario = 'gervisualizarventagasto';
		$data['accionformulario'] = $this->accionformulario;
		$this->_esputereport($data, (object)array(
				'js_files' => array(''),
				'css_files' => array(''),
				'output'	=> '',
				'fecha_mes' => date('Ym')
		));
	}

	public function gerpediraccionventagasto()
	{
		$this->accionformulario = 'gervisualizarventagasto';
	}

	public function gervisualizarventagasto($fecha_mes = null)
	{
		$this->accionformulario = 'gervisualizarventagasto';
		if ( $fecha_mes == null or ! is_numeric($fecha_mes) )
		{
			$fecha_mes = $this->input->get_post('fecha_mes');
			if ( $fecha_mes == '')
				$fecha_mes = date('Ymd', strtotime('now - 1 month'));
		}
		$fecha_mes = substr($fecha_mes, 0, 6); // aseguro que solo sea anio y mes, no importa dia, es de 01 a 31
		$this->config->load('grocery_crud');
		$this->config->set_item('grocery_crud_dialog_forms',false);
		$this->config->set_item('grocery_crud_default_per_page',100);
		$crud = new grocery_CRUD();
		$crud->set_theme('flexigrid'); // flexigrid tiene bugs en varias cosas
		$crud->set_table('adm_indefi_ventagasto');
		$crud->set_relation('cod_entidad','entidad','{des_entidad} - {cod_entidad}');
		if ( $fecha_mes != '')
		{
			$crud->where('CONVERT(fecha_mes,UNSIGNED) >= ',$fecha_mes);
			$crud->where('CONVERT(fecha_mes,UNSIGNED) <= ',$fecha_mes);
		}
		$crud->where('adm_indefi_ventagasto.cod_entidad >= ','399');
		$crud->where('adm_indefi_ventagasto.cod_entidad <= ','997');
		$crud->columns('cod_entidad','mon_gastototal','mon_ventatotal','fecha_mes', 'sessionficha' /*, 'sessionflag'*/);
		$crud->display_as('cod_entidad','Entidad');
		$crud->display_as('mon_gastototal','Gasto');
		$crud->display_as('mon_ventatotal','Venta');
		$crud->display_as('fecha_mes','Mes');
		$crud->display_as('sessionficha','Eficiencia');
		$crud->display_as('sessionflag','Actualizado'); // si usa add_fiels y unset_add no inserta
		$crud->display_as('cod_indicador', 'Diferenciador');
		$crud->set_primary_key('cod_indicador');
		$crud->set_subject('Eficiencia venta/gasto');	// columns y fields no pueden ir juntos bug crud
		$crud->field_type('cod_entidad', 'readonly');
		$crud->field_type('des_entidad', 'readonly');
		$crud->field_type('mon_gastototal', 'readonly');
		$crud->field_type('fecha_mes', 'readonly');
		$crud->field_type('sessionficha', 'readonly');
		$crud->field_type('sessionflag', 'readonly');
		$crud->field_type('cod_indicador', 'readonly');
		$crud->unset_add();
		$crud->unset_delete();
		$crud->callback_column('sessionficha',array($this,'_callback_porcentage'));
		$crud->callback_column('mon_gastototal',array($this,'_callback_formatonumero'));
		$crud->callback_column('mon_ventatotal',array($this,'_callback_formatonumero'));
		$crud->callback_edit_field('sessionficha', function () {	return '<input type="text" maxlength="50" value="'.date("YmdHis").'" name="sessionficha" readonly="true">'.br().PHP_EOL;	});
		$crud->required_fields('mon_ventatotal');
		$currentState = $crud->getState();
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__."/".$fecha_mes."")),site_url(strtolower(__CLASS__."/".__FUNCTION__)."/".$fecha_mes."/list/?fecha_mes=".$fecha_mes.""));
		$output = $crud->render();
		$data['mensage'] = $this->mensage;
		$this->_esputereport($data, (object)array(
				'js_files' => $output->js_files,
				'css_files' => $output->css_files,
				'output'	=> $output->output,
				'fecha_mes' => $fecha_mes
		));
	}
	public function _callback_formatonumero($value, $row)
	{
		return number_format($value, 2, ',', '.');
	}
	public function _callback_porcentage($value, $row)
	{
		$venta = (string) $row->mon_ventatotal;
		$gasto = (string) $row->mon_gastototal;
		// se ha trabajado con 2 decimales, por ende averiguar si esta a los dos ultimos digitos coma o punto
		$otro =  strpos($venta, '.',(strlen($venta)-3));
		if ( $otro === false )	// sep decimal siempre esta dos antes del ultimo
		{	$deldecimal = ',';$delmiles = '.';	}
		else 					// si no hay coma 2 digitos antes es un punto
		{	$deldecimal = '.';$delmiles = ',';	}
		// ahora uso el estandar, decimales con punto y sin separador miles
		$venta = str_replace($deldecimal, ":", $venta);
		$venta = str_replace($delmiles, "", $venta);
		$gasto = str_replace($deldecimal, ":", $gasto);
		$gasto = str_replace($delmiles, "", $gasto);
		$venta = str_replace(":", ".", $venta);
		$gasto = str_replace(":", ".", $gasto);
		if ( $row->mon_ventatotal <=  0 ) $porcentage = 100;
		else	$porcentage = bcmul( (string)$gasto, "100", 5) / $venta; // bcmul( (string)$gasto, "100", 2) / $venta; corregido precicion a mas decimales
		$valueef = $porcentage; // substr($porcentage,0,5)
		$valueef = number_format($valueef, 2, ',', '.');
		$valueef =  $valueef . ' % ';
		return $valueef;
	}

	public function geractualizardata($fecha_mes = null, $modo_act = null)
	{
		// *********** ini obtener datos minimos ******************
		if ( $modo_act == null )
			$modo_act = $this->input_get_post('modo_act');
			if ( $modo_act == '' )
				$modo_act = 'GASTOS'; // GASTOS, VENTAS, AMBOS
		if ( $fecha_mes == null )
			$fecha_mes = $this->input->get_post('fecha_mes');
			if ( $fecha_mes == '' )
				$fecha_mes = date('Ymd', strtotime('now - 1 month'));
		$fecha_mes = substr($fecha_mes, 0, 6); // aseguro que solo sea anio y mes, no importa dia, es de 01 a 31
		// *********** fin obtener datos minimos ******************


		// *********** ini verificar si datos vacios ******************
		$sqlverificar = "SELECT count(cod_entidad) as cuanto FROM adm_indicador_eficiencia_ventagasto WHERE fecha_mes = '".$fecha_mes."'";
		$existen = -1;
		$this->db->query($sqlusuario);
		$objetousuario = $query->result();
		if ($objetousuario)
			foreach( $objetousuario as $rowuser )
			{
				$existen = $rowuser->cuanto;
				$error = 0;
				break;
			}
		else
		{
			$error = 1;
			$this->mensage = $this->db->_error_message() . ' No se pudo ubicar datos, error no manejado';
			$this->_esputereport(
				(object)array( 'js_files' => '', 'css_files' => '', 'output'	=> $this->mensage)
			);
		}
		// *********** fin verificar si datos vacios ******************

		// *********** inicio generacion de datos segun existencia de estos *************
		$this->db->trans_start();
		if( $existen < 1)
		{
			if(  $modo_act == 'GASTOS' )
			$sqlactualizardata = "
				INSERT INTO adm_indefi_ventagasto
					cod_entidad,
					mon_gastototal,
					mon_ventatotal, /* aqui va un query de ventas, por ahora no se puede */
					fecha_mes,
					reservado /* esta es columna fake para el calculo */
				SELECT
					a.cod_entidad as cod_entidad,
					IFNULL(SUM(IFNULL(a.mon_registro, 0)), 0) as mon_gastototal,
					0 as mon_ventatotal,
					SUBSTRING(a.fecha_concepto, 1, 6) as fecha_mes,
					'".date('YmdHis')."'
				FROM
					registro_gastos a
				LEFT JOIN entidad b ON a.cod_entidad = b.cod_entidad
				where
					a.cod_registro <> ''
						and b.status <> 'INACTIVO'
						and CONVERT( a.fecha_concepto , UNSIGNED) >= CONVERT( '".$fecha_mes."' , UNSIGNED)
						and CONVERT( a.fecha_concepto , UNSIGNED) <= CONVERT( '".$fecha_mes."' , UNSIGNED)
				group by a.cod_entidad
				order by a.cod_entidad
			";
			else if ( $modo_act == 'VENTAS' )
			$sqlactualizardata = "
				INSERT INTO adm_indefi_ventagasto
					cod_entidad,
					mon_gastototal,
					mon_ventatotal, /* aqui va un query de ventas, por ahora no se puede */
					fecha_mes,
					reservado /* esta es columna fake para el calculo */
				SELECT
					a.cod_entidad as cod_entidad,
					IFNULL(SUM(IFNULL(a.mon_registro, 0)), 0) as mon_gastototal,
					0 as mon_ventatotal,
					SUBSTRING(a.fecha_concepto, 1, 6) as fecha_mes,
					'".date('YmdHis')."'
				FROM
					registro_gastos a
				LEFT JOIN entidad b ON a.cod_entidad = b.cod_entidad
				where
					a.cod_registro <> ''
						and b.status <> 'INACTIVO'
						and CONVERT( a.fecha_concepto , UNSIGNED) >= CONVERT( '".$fecha_mes."' , UNSIGNED)
						and CONVERT( a.fecha_concepto , UNSIGNED) <= CONVERT( '".$fecha_mes."' , UNSIGNED)
				group by a.cod_entidad
				order by a.cod_entidad
			";
		}
		else
		{
			$sqlactualizardata = "SET SQL_SAFE_UPDATES=0;DELETE FROM adm_indicador_eficiencia_ventagasto WHERE fecha_mes = '".$fecha_mes."';SET SQL_SAFE_UPDATES=1;";
			$this->db->query($sqlactualizardata);
		}

		$this->db->query($sqlactualizardata);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
			$this->mensage = "Error, data perdida o no actualizada: " . $this->db->_error_message();
		else
			$this->mensage = "Datos actualizados de gastos totales, correctamente, consulte para revisarlos";

		$this->_esputereport((object)array(
				'js_files' => $js_files,
				'css_files' => $css_files,
				'output'	=> $this->mensage
		));

	}

	public function admsoloverlosfondos()
	{
		$crud = new grocery_CRUD();
		//$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->set_table('fondos');  // TODO : requiere trato especias y vista fondos creada
		$crud->set_theme('datatables'); // flexigrid tiene bugs en varias cosas
		$crud->columns('fecha_fondo','mon_fondo','quien','cod_quien','cod_fondo','sessionflag');
		$crud->display_as('cod_fondo','Codigo')
			 ->display_as('mon_fondo','Disponible')
			 ->display_as('fecha_fondo','Al')
			 ->display_as('cod_quien','Id')
			 ->display_as('quien','Quien')
			 ->display_as('sessionflag','Alterado');
		$crud->set_primary_key('cod_fondo','fondos');
		$crud->unset_operations();
		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url(strtolower(__CLASS__."/adm_indicador_eficiencia_ventagasto")));
		$output = $crud->render();
		if($crud->getState() != 'list') {
			$this->_esputereport($output);
		} else {
			return $output;
		}
	}

}
