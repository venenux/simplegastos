<?php
class Menu extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$obj = & get_instance();
		//$this->load->library('session');
		$obj->load->library('menulib');
	}

	function general_menu($params='inparametro')
	{
		$menu = new MenuLib;
		$nodes = new MenuNodes;

/*		$n400000=anchor('indexcontroler','Inicio');
		$m400000['m400000']=anchor('generarordenconcarga/index','Generar Orden');

		$n010000=anchor('#','Otro');
		$m010000['m010000']=anchor('indexcontroler/parametrosdesession','Sus datos');

		$header['1'] = $nodes->m_header_nodes($n400000,$m400000);
		$header['2'] = $nodes->m_header_nodes($n010000,$m010000);
*/
		if(!$this->session->userdata('logueado'))
			$label = 'Ingreso';
		else
			$label = 'Sesion';

		$inicio=anchor('indexcontroler',$label);

		$intranet=anchor('http://intranet1.net.ve','Intranet');
		$elcorreo=anchor('http://intranet1.net.ve/elcorreo','Correo');

		$admins=anchor('admgeneral','Gestion');
		$admgeneral['admusuariosentidad']=anchor('admusuariosentidad','Usuarios');
		$admgeneral['admcategoriasconceptos']=anchor('admcategoriasconceptos','Categorias');
		$admgeneral['admgastoslog']=anchor('admgastoslog','Log');

		$vistas=anchor('cargargastover','Vistas');
		$vistaglobal=array();
		$vistaglobal['cargargastover']=anchor('gastosmatrix','Vista Reporte');

		$cargas=anchor('cargargastover/gastoregistros/tienda','Cargas');// TODO filtrar por la tienda si no es personal administrativo
		$cargaglobal['Cargargastot']=anchor('cargargastover/gastoregistros/add','Cargar gasto'); // TODO: verificar permiso y este menu solo cargfa en tienda
		$cargaglobal['Cargargastoa']=anchor('cargargastover/index','Revisar cargas'); // TODO: verificar permiso y este menu solo cargfa en administrativos
/*
		$n300000=anchor('m300000','Procesos');
		$m300000['m301000']=anchor('m301000','Actualizar Productos desde compras');
		$m300000['m302000']=anchor('m302000','Actualizar Productos desde archivo');

		$n200000=anchor('m200000','Productos');
		$m200000['m201000']=anchor('m201000',config_item('labelm201000'));
		$m200000['m202000']=anchor('m202000',config_item('labelm202000'));
		$m200000['m203000']=anchor('m203000',config_item('labelm203000'));
*/
		if($this->session->userdata('logueado'))
		{
			$inicionlogin['manejousuarios/manejousuarios']=anchor('manejousuarios/desverificarintranet','Salir');
			$header['0'] = $nodes->m_header_nodes($inicio, $inicionlogin);

		//	$header['1'] = $nodes->m_header_nodes($gastos, $gastosmatrix);
		}
		else
		{
			$header['0'] = $nodes->m_header_nodes($inicio, array());
			$header['1'] = $nodes->m_header_nodes('', array());
		}
		$header['2'] = $nodes->m_header_nodes($intranet, array());
		$header['3'] = $nodes->m_header_nodes($elcorreo, array());
		if($this->session->userdata('logueado'))
		{
			$header['4'] = $nodes->m_header_nodes($cargas,$cargaglobal);
			$header['5'] = $nodes->m_header_nodes($vistas,$vistaglobal);
			$header['6'] = $nodes->m_header_nodes($admins,$admgeneral);
	/*		$header['7'] = $nodes->m_header_nodes($n100000,$m100000);
			$header['8'] = $nodes->m_header_nodes($n010000,$m010000);
			$header['9'] = $nodes->m_header_nodes($n000000,$m000000);*/
		}
		$menu->m_create_headers($header);
		return $menu->show_menu();
	}

}
