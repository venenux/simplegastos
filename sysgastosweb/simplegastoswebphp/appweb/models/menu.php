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
	function general_menu($params='inparametro')
	{
		$menu = new MenuLib;
		$nodes = new MenuNodes;

		$admins=anchor('admgeneral','Gestion');
		$admgeneral['admusuariosentidad']=anchor('admusuariosentidad','Usuarios');
		$admgeneral['admcategoriasconceptos']=anchor('admcategoriasconceptos','Categorias');
		$admgeneral['admgastoslog']=anchor('admgastoslog','Log');

		$vistas=anchor('mimatrixcontroller','Matrix');
		$vistaglobal=array();
		$vistaglobal['cargargastover']=anchor('mimatrixcontroller/mimatrixfiltrar','Vista Reporte');

		$cargasadm=anchor('cargargastoadministrativo/gastoregistros/todos','Cargas');// TODO filtrar por la tienda si no es personal administrativo
		$cargargastoadministrativo['cargargastoadministrativoadd']=anchor('cargargastoadministrativo/gastoregistros/add','Cargar un gasto'); // TODO: verificar permiso y este menu solo cargfa en tienda
		$cargargastoadministrativo['cargargastoadministrativover']=anchor('cargargastoadministrativo/index','Revisar todas las cargas'); // TODO: verificar permiso y este menu solo cargfa en administrativos

		$cargastie=anchor('cargargastoentidadestienda/gastoregistros/todos','Cargas');// TODO filtrar por la tienda si no es personal administrativo
		$cargargastoentidadestienda['cargargastoentidadestiendaadd']=anchor('cargargastoentidadestienda/gastoregistros/add','Cargar otros gasto'); // TODO: verificar permiso y este menu solo cargfa en tienda
		$cargargastoentidadestienda['cargargastoentidadestiendaver']=anchor('cargargastoentidadestienda/index','Revisar nuestras cargas'); // TODO: verificar permiso y este menu solo cargfa en administrativos

		$cargasrep=anchor('cargargastoadministrativo/gastoregistros/todos','Cargas');// TODO filtrar por la tienda si no es personal administrativo
		$cargasgastoreportesvertodo['admgastosregistrosver']=anchor('admgastosregistros/index','Revisar las cargas'); // TODO: verificar permiso y este menu solo cargfa en administrativos

		if(!$this->session->userdata('logueado'))
			$labelindex = 'Ingreso';
		else
			$labelindex = 'Sesion';

		$inicio=anchor('indexcontroler',$labelindex);
		$intranet=anchor('http://intranet1.net.ve','Intranet');
		$elcorreo=anchor('http://intranet1.net.ve/elcorreo','Correo');

		// el
		$header['2'] = $nodes->m_header_nodes($intranet, array());
		$header['3'] = $nodes->m_header_nodes($elcorreo, array());
		if($this->session->userdata('logueado'))
		{
			$usuariocodgernow = $this->session->userdata('cod_entidad');

			$inicionlogin['manejousuarios/manejousuarios']=anchor('manejousuarios/desverificarintranet','Salir');
			$header['0'] = $nodes->m_header_nodes($inicio, $inicionlogin);
			if($usuariocodgernow >399 and $usuariocodgernow < 990)
			{
				$header['4tie'] = $nodes->m_header_nodes($cargastie,$cargargastoentidadestienda);
			}
			else if ($usuariocodgernow >= 998)
			{
				$header['4adm'] = $nodes->m_header_nodes($cargasadm,$cargargastoadministrativo);
				$header['5'] = $nodes->m_header_nodes($vistas,$vistaglobal);
				$header['6'] = $nodes->m_header_nodes($admins,$admgeneral);
			}
			else if ($usuariocodgernow >= 990 and $usuariocodgernow < 998)
			{
				$header['4rep'] = $nodes->m_header_nodes($cargasrep,$cargasgastoreportesvertodo);
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
