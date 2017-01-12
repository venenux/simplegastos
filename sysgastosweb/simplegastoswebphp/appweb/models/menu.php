<?php
class Menu extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$obj = & get_instance();
		$this->load->library('session');
		$obj->load->library('menulib');
	}

	/** modelo de menu gastos, implementa libreria menu de PICCORO Lenz McKAY */
	function general_menu($sessionobject = null)
	{
		$menu = new MenuLib;
		$nodes = new MenuNodes;

		$admins=anchor('admusuariosentidad','Gestion');
		$admgeneral['admusuarios']=anchor('admusuarios','Entidades');
		$admgeneral['admsubcategorias']=anchor('admsubcategorias','Categorias');

		// enlaces de gerencia
		$menugerencia=anchor('adm_indicador_eficiencia_ventagasto','Gerencia');
		$menugerencianodos['adm_indicador_eficiencia_ventagasto']=anchor('adm_indicador_eficiencia_ventagasto/gervisualizarventagasto/','Gasto vs Venta');

		$vistas=anchor('mimatrixcontroller','Matrix');
		$vistaglobal['matrixcontroler']=anchor('matrixcontroler','Totalizadores');
		$vistaglobal['cargargastover']=anchor('mimatrixcontroller/mimatrixfiltrar','Vista Reporte');
		// enlaces de cargas para administrativo edita, ver etc con permisologia
		$cargasadm=anchor('cargargastoadministrativo/gastoregistros/todos','Cargas');
		$cargargastoadministrativo['cargargastoadministrativoadd']=anchor('cargargastoadministrativo/gastoregistros/add','Cargar directo');
		//$cargargastoadministrativo['cargargastosucursalesuno']=anchor('cargargastosucursalesadm/gastomanualcargaruno','Cargar como tienda');
		$cargargastoadministrativo['cargargastoadministrativover']=anchor('cargargastoadministrativo/index','Filtrar directo');
		$cargargastoadministrativo['gastosucursalesrevisarlos']=anchor('cargargastosucursalesadm/gastomanualfiltrarlos','Filtrar RAPIDO');
		// enlaces de cargas para tiendas y perfiles no administrativos edita ver filtrado
		$cargastie=anchor('cargargastosucursalesadm/gastosucursalesrevisarlos','Gasto');
		$cargargastoentidadestienda['cargargastosucursalesuno']=anchor('cargargastosucursalesadm/gastomanualcargaruno','Cargar gasto');
		$cargargastoentidadestienda['gastosucursalesrevisarlos']=anchor('cargargastosucursalesadm/gastomanualfiltrarlos','Filtrar gasto');

		if(!$this->session->userdata('logueado'))
			$labelindex = 'Ingreso';
		else
			$labelindex = 'Sesion';

		$inicio=anchor('indexcontroler',$labelindex);
		$intranet=anchor('http://intranet1.net.ve','Intranet');
		$elcorreo=anchor('http://intranet1.net.ve/elcorreo','Correo');
		$systemalog=anchor('admgastoslog','Logs');


		// el
		$header['2'] = $nodes->m_header_nodes($intranet, array());
		$header['3'] = $nodes->m_header_nodes($elcorreo, array());
		if($this->session->userdata('logueado'))
		{
			$usuariocodgernow = $this->session->userdata('cod_entidad');

			$inicionlogin['manejousuarios/manejousuarios']=anchor('manejousuarios/desverificarintranet','Salir');
			$header['0'] = $nodes->m_header_nodes($inicio, $inicionlogin);
			if ( ! $usuariocodgernow == "" )
			{
				if( $usuariocodgernow == 111)
					$header['4geer'] = $nodes->m_header_nodes($menugerencia,$menugerencianodos);
				else if( $usuariocodgernow != 998)
				{
					$header['4tie'] = $nodes->m_header_nodes($cargastie,$cargargastoentidadestienda);
				}
				else /* ($usuariocodgernow = 998 and $usuariocodgernow != '' ) */
				{
					$header['4esp'] = $nodes->m_header_nodes($cargasadm,$cargargastoadministrativo);
					$header['5'] = $nodes->m_header_nodes($vistas,$vistaglobal);
					$header['6'] = $nodes->m_header_nodes($admins,$admgeneral);
					$header['7'] = $nodes->m_header_nodes($systemalog,array());
				}
			}
		}
		else
		{
			$header['0'] = $nodes->m_header_nodes($inicio, array());
			$header['1'] = $nodes->m_header_nodes('', array());
		}
		$menu->m_create_headers($header);
		return $menu->show_menu();
	}

}
