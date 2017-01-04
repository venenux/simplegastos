<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Adm_indicador_eficiencia_ventagasto extends CI_Controller {

	private $mensage = <<<'EOD'
Indicadores de Gestion: eficiencia de ventas vs gasto.
EOD;

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
	}

	public function _esputereport($output = null)
	{
		$data['controller'] = 'adm_indicador_eficiencia_ventagasto';
		$data['mensage'] = $this->mensage;
		$data['js_files'] = $output->js_files;
		$data['css_files'] = $output->css_files;
		$data['output'] = $output->output;
		$data['menu'] = null;
		$this->load->view('header.php',$data);
		$this->load->view('adm_indicadores_verdata.php',$data);
		$this->load->view('footer.php',$data);
	}

	function index()
	{
		//$this->_verificarsesion();

		$this->_esputereport((object)array(
				'js_files' => null,
				'css_files' => null,
				'output'	=> null
		));
	}

	public function gervisualizardata($fecha_mes = null)
	{
		if ( $fecha_mes == null )
			$fecha_mes = $this->input->get_post('fecha_mes');
			if ( $fecha_mes == '' )
				$fecha_mes = date('Ymd', strtotime('now - 1 month'));
			
		$fecha_mes = substr($fecha_mes, 0, 6); // aseguro que solo sea anio y mes, no importa dia, es de 01 a 31
		$this->config->load('grocery_crud');
		$this->config->set_item('grocery_crud_dialog_forms',false);
		$this->config->set_item('grocery_crud_default_per_page',100);
		$crud = new grocery_CRUD();
		$crud->set_theme('flexigrid'); // flexigrid tiene bugs en varias cosas
		$crud->set_table('adm_indicador_eficiencia_ventagasto');
		$crud->set_relation('cod_entidad','entidad','{des_entidad} - {cod_entidad}');
		if ( $fecha_mes != '')
		{
			$crud->where('CONVERT(fecha_mes,UNSIGNED) >= ',$fecha_mes);
			$crud->where('CONVERT(fecha_mes,UNSIGNED) <= ',$fecha_mes);
		}
		$crud->where('adm_indicador_eficiencia_ventagasto.cod_entidad >= ','399');
		$crud->where('adm_indicador_eficiencia_ventagasto.cod_entidad <= ','997');
		$crud->columns('cod_entidad','mon_gastototal','mon_ventatotal','reservado','fecha_mes' /*, 'sessionficha', 'sessionflag'*/);
		$crud->display_as('cod_entidad','Entidad');
		$crud->display_as('mon_gastototal','Gasto');
		$crud->display_as('mon_ventatotal','Venta');
		$crud->display_as('fecha_mes','Mes');
		$crud->display_as('sessionficha','Ingresado');
		$crud->display_as('sessionflag','Actualizado'); // si usa add_fiels y unset_add no inserta
		$crud->display_as('reservado','Porcentaje');
		$crud->set_subject('Eficiencia venta/gasto');	// columns y fields no pueden ir juntos bug crud
		$crud->field_type('cod_entidad', 'readonly');
		$crud->field_type('des_entidad', 'readonly');
		$crud->field_type('mon_gastototal', 'readonly');
		$crud->field_type('fecha_mes', 'readonly');
		$crud->field_type('sessionficha', 'readonly');
		$crud->field_type('sessionflag', 'readonly');
		//$crud->field_type('reservado', 'readonly');
		$crud->unset_add();
		$crud->unset_delete();
		$crud->callback_column('reservado',array($this,'_callback_porcentage'));
		$crud->callback_column('mon_gastototal',array($this,'_callback_formatonumero'));
		$crud->callback_column('mon_ventatotal',array($this,'_callback_formatonumero'));
		$crud->callback_edit_field('reservado', function () {	return '<input type="text" maxlength="50" value="'.date("YmdHis").'" name="porcentaje" readonly="true">';	});
		$crud->required_fields('mon_ventatotal');
		$output = $crud->render();
		$data['controller'] = 'adm_indicador_eficiencia_ventagasto';
		$data['mensage'] = $this->mensage;
		$data['output'] = $output;
		$data['menu'] = null;
		$this->load->view('header.php',$data);
		$this->load->view('adm_indicadores_verdata.php',$output);
		$this->load->view('footer.php',$data);
	}	
	public function _callback_porcentage($value, $row)
	{
		if ( $row->mon_ventatotal > 0 )
		$porcentage = ($row->mon_gastototal * 100)/	$row->mon_ventatotal;
		else
		$porcentage = 100;
		return '' . substr($porcentage,0,4) . '%';
	}
	public function _callback_formatonumero($value, $row)
	{
		return number_format($value, 2, ',', '.');
	}

	public function geractualizardata($fecha_mes = null)
	{
		$fecha_mes = $this->input->get_post('fecha_mes');
		$fecha_mes = substr($fecha_mes, 0, 6); // aseguro que solo sea anio y mes, no importa dia, es de 01 a 31
		$sqlverificar = "SELECT count(cod_entidad) as cuanto FROM adm_indicador_eficiencia_ventagasto WHERE fecha_mes = '".$fecha_mes."'";
		$existen = -1;
		$this->db->query($sqlusuario);
		$objetousuario = $query->result();
		if ($objetousuario)
			foreach( $objetousuario as $rowuser )
			{
				$existen = $rowuser->cuanto;
				break;
			}
		else
			$error = 1;
		$this->mensage = $this->db->_error_message();

		$this->db->trans_start();
		if( $existen > 0 )
		{
			$sqlactualizardata = "SET SQL_SAFE_UPDATES=0;DELETE FROM adm_indicador_eficiencia_ventagasto WHERE fecha_mes = '".$fecha_mes."';SET SQL_SAFE_UPDATES=1;";
			$this->db->query($sqlactualizardata);
		}
		$sqlactualizardata = "
			INSERT INTO adm_indicador_eficiencia_ventagasto
				cod_entidad, 
				mon_gastototal, 
				mon_ventatotal, 
				fecha_mes,
				reservado
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
