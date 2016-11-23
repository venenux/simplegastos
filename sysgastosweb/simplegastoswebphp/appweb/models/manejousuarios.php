<?php class Manejousuarios extends CI_Model 
{ 
	protected $dbxmppusers = null;
	protected $dbgastousers = null;
	protected $dbxmpperror = null;
	protected $dbgastoerror = null;

	$usuario

	public function __construct() 
	{
		parent::__construct();

		try
		{
			$this->dbxmppusers = $this->load->database('simplexmpp', true);
		}
		catch(Exception $e)
		{
			$dbxmpperror = $e->getMessage();
		}

		try
		{
			$this->dbgastousers = $this->load->database('gastossystema', true);
		}
		catch(Exception $e)
		{
			$dbgastoerror = $e->getMessage();
		}
	}

	public function usuario_ejabberd($nombre, $contrasena)
	{
		$sqlusuario = "select username,\"password\" as clave from users where username='".$nombre."' and '".$contrasena."' ";
		if 
		$query = $this->dbxmppusers->query($sqlusuario);
		$objectquery = $query->result();
		return $objectquery;
	}

	function get_last_ten_entries()
	{
		$query = $this->db->get('entries', 10);
		return $query->result();
	}

	function insert_entry()
	{
		$this->title   = $_POST['title']; // please read the below note
		$this->content = $_POST['content'];
		$this->date	= time();

		$this->db->insert('entries', $this);
	}

	function update_entry()
	{
		$this->title   = $_POST['title'];
		$this->content = $_POST['content'];
		$this->date	= time();

		$this->db->update('entries', $this, array('id' => $_POST['id']));
	}

}
