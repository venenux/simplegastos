<?php class Usuario extends CI_Model 
{ 
	protected $dbxmppusers = null;
	protected $dbgastousers = null;
	protected $userdatas = array(null);

	public function __construct() 
	{
		parent::__construct();

		try
			$this->load->database('gastossystema');
		catch(Exception $e)
			log_message('error', $e->getMessage() );
		
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('encrypt'); // TODO buscar como setiear desde aqui key encrypt
		$this->load->library('session');
		$this->load->library('table');
	}
	
	/* CHECK LOGIN, return BOOL */
	public function userlogeado(){
		if(null !== $this->session->userdata('logueado'))
		{
			if( $this->session->userdata('logueado') == true or $this->session->userdata('logueado') == '1')
				return true;
			else
				return false;
		}
		else
			return false;
	}

	public function userdataset($intranet = '*', $contrasena = '')
	{
		$this->userdatas['intranet'] = $intranet;
		$this->userdatas['contrasena'] = $contrasena;
	}
	public function userdata()
	{
		$nombre = $this->userdatas['intranet'];
		$contrasena = $this->userdatas['contrasena'];
		
		$sqlusuario = "SELECT
		  count(`usu`.`intranet`) as cuantos,
		 `usu`.`ficha`,                             /* ficha en nomina, cedula en vnzl */
		  ifnull(`usu`.`intranet`,'') as intranet,  /* solo entran quienes tengan intranet */
		  ifnull(`usu`.`intranet`,'') as username,  /* solo entran quienes tengan intranet */
		  `usu`.`clave`, `usu`.`nombre`,            /* como se llama y su apellido */
		  `usu`.`sello`,                           /* ubicacion segun la nomina, pues es por centro de costos */
		  `suc`.`cod_entidad`,                      /* entidad sucursal en donde puede operar */
		  `usu`.`estado`,                           /* solo entran quienes puedan ver gastos */
		  `usu`.`cod_fondo`,
		  `ent`.`abr_entidad`,
		  `ent`.`abr_zona`,
		  `ent`.`des_entidad`,
		  ifnull(`usu`.`sessionflag`,'') as sessionflag,
		  `usu`.`acc_lectura`, `usu`.`acc_escribe`, `usu`.`acc_modifi`,
		  `usu`.`sessionficha` as fecha_ficha, `usu`.`fecha_ultimavez`
		FROM
		 `usuarios` as `usu`
		LEFT join
		 `entidad_usuario` as `suc` ON `suc`.`intranet` = `usu`.`intranet`
		LEFT JOIN
		 `entidad` as `ent` ON `ent`.`cod_entidad` = `suc`.`cod_entidad`
		WHERE
		  ifnull(`usu`.`intranet`,'') <> ''
		AND
		  (
			(`usu`.`clave` = CONVERT(md5('".$this->db->escape_str($contrasena)."'), CHAR(50)) AND `usu`.`intranet` = '".$this->db->escape_str($nombre)."')
			  OR
			(`usu`.`clave` = CONVERT(md5('".$this->db->escape_str($contrasena)."'), CHAR(50)) AND `usu`.`ficha` = '".$this->db->escape_str($nombre)."')
		  )
		AND
		  ( usu.estado = 'ACTIVO' OR usu.estado = 'activo')
		 ";
		//$query = $this->dbxmppusers->query($sqlusuario);
		$query = $this->db->query($sqlusuario);
		$cuantosusuario = 0;
		$cuantoscodgers = 0;
		
	}
	
	

}
