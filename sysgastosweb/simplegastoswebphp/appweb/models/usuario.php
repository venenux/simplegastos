<?php class Usuario extends CI_Model 
{ 
	protected $dbxmppusers = null;
	protected $dbgastousers = null;
	protected $dbxmpperror = null;
	protected $dbgastoerror = null;

	public function __construct() 
	{
		parent::__construct();

		try
		{
			$this->dbxmppusers = $this->load->database('simplexmpp', true);
			//
		}
		catch(Exception $e)
		{
			$this->dbxmpperror = $e->getMessage();
		}

		try
		{
			$this->dbgastousers = $this->load->database('gastossystema', true);
		}
		catch(Exception $e)
		{
			$this->dbgastoerror = $e->getMessage();
		}
	}

	public function dataget()
	{
		//$query = $this->dbxmppusers->query("select 'pepe'");
		return $query;
		
	}

}
