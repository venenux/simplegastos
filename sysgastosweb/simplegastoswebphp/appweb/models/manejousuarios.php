<?php
class Manejousuarios extends CI_Model 
{ 
   protected $dbxmppusers = null;
   
   public function __construct() 
   {
      parent::__construct();
      $this->dbxmppusers = $this->load->database('simplexmpp', true);
   }

   public function usuario_ejabberd($nombre, $contrasena)
   {
	   /*
	   $this->db->select('id, nombre');
	   $this->db->from('usuarios');
	   $this->db->where('nombre', $nombre);
	   $this->db->where('contrasena', $contrasena);
	   $consulta = $this->db->get();
	   $resultado = $consulta->row();
	   return $resultado;
	   */
	   $sqlusuario = "select username,\"password\" as clave from users where username='".$nombre."' and '".$contrasena."' ";
	   $query = $this->dbxmppusers->query($sqlusuario);
	   $objectquery = $query->result();
	   return $objectquery;
   }
}
