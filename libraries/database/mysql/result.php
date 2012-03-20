<?php



class SYS_Database_Result_Driver
{
	public $result;
	public $data = array();
	
	//--------------------------------------------------------------------------

	public function num_rows()
	{
		return mysql_num_rows($this->result);
	}
	
	//--------------------------------------------------------------------------

	public function &result_array()
	{
		while ($data = mysql_fetch_assoc($this->result))
		{
			$this->data[] = $data;
		}
		return $this->data;
	}

	//--------------------------------------------------------------------------

	public function &result()
	{
		while ($data = mysql_fetch_object($this->result))
		{
			$this->data[] = $data;
		}
		return $this->data;
	}

	//--------------------------------------------------------------------------
	
	public function &row_array($assoc = TRUE)
	{
		$fn = $assoc ? 'mysql_fetch_assoc' : 'mysql_fetch_array';
		$this->data = $fn($this->result);
		return $this->data;
	}

	//--------------------------------------------------------------------------

	public function &row()
	{
		$this->data = mysql_fetch_object($this->result);
		return $this->data;
	}

	//--------------------------------------------------------------------------
}