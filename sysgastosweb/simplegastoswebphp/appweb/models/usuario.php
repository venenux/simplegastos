<?php 
/**
 * adm_usuario.php
 * 
 * abstraccion para obtener datos de usuario en codeigniter
 *
 * tabla y campos
 * * adm_usuario(ficha,intranet,clave,estado,detalles,tipo_usuario,cod_perfil)
 * * adm_entidad_usuario(intranet, cod_entidad)
 * * adm_usuario_perfil(intranet, cod_perfil)
 * * adm_entidad(cod_entidad, cod_sello, abr_zona, abr_entidad, status, tipo_entidad, clase_entidad) 
 * * adm_perfil(cod_perfil, cod_controlador, cod_aplicacion)
 *
 * objeto sesion manejado
 * * ci_session:
 * ** user_data seteado a nulo no se usa
 * ** array(0, 1, ... n) arreglo de usuarios por cada entidad/perfil
 * ** 0 => array(cuantos, intranet, ficha, estado, tipo_usuario ... cod_perfil(array), cod_entidad(array) )
 * 
 * Copyright 2017 PICCORO Lenz McKAY <mckaygerhard@gmail.com>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 or any other.
 * 
 */
class usuario extends CI_Model 
{ 

	public function __construct() 
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('session');
	}

	/**
	 * retorna un arreglo de usuarios segun criterio, necesita nombre y clave, necesita nombre ajuro
	 * el arreglo tiene indices de numero, pero cada arreglo tiene una llave "cuantos" que dice el total
	 * ejemplo 1 dos usuarios
	 * 0 -> (cuantos->2, intranet->pepe, ...)
	 * 1 -> (cuantos->2, intranet->pablo, ...)
	 * ejemplo 2 sin usuarios 
	 * 0 -> (cuantos->0)
	 */
	public function getentidadusuario($username, $clavename = '', $credential = null)
	{
		$this->load->database('gastossystema');
		// determino que es lo que se pide un usuario en todo perfil o todos los usuarios
		if ( $username != '*' and trim($username) != '' )
			$queryfiltro1 = " ( `intranet`='".$username."' AND `clave` = md5(".$clavename. ") ) OR  ( `ficha`='".$username."' AND `clave` = md5(".$clavename. ") )";
		else if ( $username == '*')
			$queryfiltro1 = " `intranet` <> '' ";
		else
			$queryfiltro1 = " `intranet` <> '' AND clave = '' ";
		// primero cuento cuantos hay en la misma entidad // TODO hacer join con las entidades asociadas
		$sqldbusuarios1c = "
			SELECT count(*) as cuantos 
			FROM usuarios 
			WHERE ( ".$queryfiltro1." ) AND `estado` = 'ACTIVO' ";
		$querydbusuarios1c = $this->db->query($sqldbusuarios1c);
		// el resultado es el mismo usuario repetido tantas entidades tenga asociada, esto se cuenta cuantos hay
		$resultobjusuario = $querydbusuarios1c->result();
		foreach ($resultobjusuario as $row)
			$cuantos = $row->cuantos;
		// una vez el numero, se inserta como parte del sql para que se vaya en el arreglo de respuesta
		$sqldbusuarios2u = "
			SELECT ".$cuantos." as cuantos, usuarios.* 
			FROM usuarios 
			WHERE ( ".$queryfiltro1." ) AND `estado` = 'ACTIVO'";
		if ( $cuantos < 1 ) 
		{
			$adm_usuarios_result = $querydbusuarios1c->result_array();	// pero solo si hay 1 o mas
		}
		else
		{
			$querydbusuarios2u = $this->db->query($sqldbusuarios2u);	// sino devuelve el count pero en arreglo
			$adm_usuarios_result = $querydbusuarios2u->result_array();
		}
		$this->db->close();
		return $adm_usuarios_result;	// devuelve un arreglo y el primer elemento del elemento '0' es 'cuantos'
		
	}
	
	/**
	 * retorna y permite seguir o no dependiendo de la sesion activa
	 * @return FALSE si no hay sesion valida, arreglo con cod_entidad si la sesion de de un intranet
	 *
	 * objeto sesion manejado
	 * * ci_session:
	 * ** user_data seteado a nulo no se usa
	 * ** array(0, 1, ... n) arreglo de usuarios por cada entidad/perfil
	 * ** 0 => array(cuantos, intranet, ficha, estado, tipo_usuario ... cod_perfil(array), cod_entidad(array) )
	 */
	public function indsessionusuario()
	{
		// se verifica existe un elemento "0" en el objeto sesion (la funcion arriba se asegura de ello
		if( $this->session->userdata('0') )
		{
			$permisos = array('999','111'); // fake emulando los permisos esto es lo que se retornra realmente
			$userdata = $this->session->userdata('0'); // se consulta "0" o cualquiera porque ya traen "cuantos" como columna
			if ( $userdata['cuantos'] > 0 )
				$usuario = $userdata['intranet']; // TODO recorrer el array con el usuario ir a DB traer donde puede entrar
			else
				return FALSE; // si no hay un minimo de "cuantos" es porque o salio o se destruyo (salio forzado)
			return $userdata; // si existe se devuelve un arreglo con los cod_entidad que puede acceder y los aplicativos asociados
		}
		else
		{
			return FALSE; // no existia ningun objeto "0" en la sesion, acaba entrar o salio forzado (sesion detroy)
		}
	}
	
	/** toma el objeto sesion y lo invalida, reinicia el indice 0 de sesion a "cuantos" = 0 */
	public function invalidasesionysale()
	{
		$this->session->sess_destroy();
	    $arrayuser = array('0' => array('cuantos'=>'0')); // se coloca EXPLICITAMENTE que no hay usuarios activos
		$this->session->set_userdata($arrayuser);
	}

}
