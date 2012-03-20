<?php



class SYS_Database_Result extends SYS_Database_Result_Driver
{
	public $result;

	//--------------------------------------------------------------------------

	public function __construct(&$result)
	{
		$this->result = $result;
	}
	
	//--------------------------------------------------------------------------
	
}