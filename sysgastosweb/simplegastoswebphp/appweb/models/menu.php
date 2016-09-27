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
			$label = 'Inicio';
		else
			$label = 'Session';

		$inicio=anchor('indexcontroler',$label);

		$intranet=anchor('http://intranet1.net.ve','Intranet');
		$elcorreo=anchor('http://intranet1.net.ve/elcorreo','Correo');

		$ordenes=anchor('generarordenconcarga','Ordenes de despacho');
//		$generarordenconcarga['consultarordendespachos']=anchor('consultarordendespachos','Consultar Orden');
		$generarordenconcarga['generarordenconcarga']=anchor('generarordenconcarga','Generar Orden');
		$generarordenconcarga['cargardetallecambioprecio']=anchor('cargardetallecambioprecio','Cambio de precio');

		$vistas=anchor('vistaglobal','Vistas');
		$vistaglobal['vistaglobalcategorias']=anchor('vistaglobalcategorias','Vista Categorias');
		$vistaglobal['vistaglobaldetalles']=anchor('vistaglobaldetalles','Vista Detalles');

		$gastos=anchor('gastosmatrix','Gastos');
		$gastosmatrix['gastosmatrix']=anchor('gastosmatrix','Vista Global');

		$cargas=anchor('cargaglobal','Cargas');
		$cargaglobal['generarordenconcarga']=anchor('generarordenconcarga','Cargar gasto');
		$cargaglobal['generarordenconcarga2']=anchor('generarordenconcarga2','Cargar gasto2');
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

			$header['1'] = $nodes->m_header_nodes($gastos, $gastosmatrix);
		}
		else
		{
			$header['0'] = $nodes->m_header_nodes($inicio, array());
			$header['1'] = $nodes->m_header_nodes($inicio, array());
		}
		$header['2'] = $nodes->m_header_nodes($intranet, array());
		$header['3'] = $nodes->m_header_nodes($elcorreo, array());
		if($this->session->userdata('logueado'))
		{
			$header['4'] = $nodes->m_header_nodes($cargas,$cargaglobal);
			$header['5'] = $nodes->m_header_nodes($vistas,$vistaglobal);
	/*		$header['6'] = $nodes->m_header_nodes($n200000,$m200000);
			$header['7'] = $nodes->m_header_nodes($n100000,$m100000);
			$header['8'] = $nodes->m_header_nodes($n010000,$m010000);
			$header['9'] = $nodes->m_header_nodes($n000000,$m000000);*/
		}
		$menu->m_create_headers($header);
		return $menu->show_menu();
	}

}
