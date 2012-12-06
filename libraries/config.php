<?php defined('EXT') OR die('No direct script access allowed');



class SYS_Config
{

	//--------------------------------------------------------------------------
	
	public function __get($key)
	{
		$this->$key = new stdClass();
		return $this->$key;
	}

	//--------------------------------------------------------------------------

}