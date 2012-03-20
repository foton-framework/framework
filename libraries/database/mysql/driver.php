<?php



class SYS_Database_Driver
{

	//--------------------------------------------------------------------------

	public function db_connect()
	{
		$this->dbr = @mysql_connect($this->hostname, $this->username, $this->password, TRUE);
	}

	//--------------------------------------------------------------------------

	public function db_close()
	{
		mysql_close($this->dbr);
	}

	//--------------------------------------------------------------------------

	public function db_select()
	{
		return @mysql_select_db($this->database, $this->dbr);
	}

	//--------------------------------------------------------------------------

	public function db_set_charset()
	{
		return @mysql_query('SET NAMES "'.$this->charset.'"', $this->dbr);
	}

	//--------------------------------------------------------------------------

	public function escape($str)
	{
		return mysql_real_escape_string($str);
	}

	//--------------------------------------------------------------------------

	public function query($sql)
	{
		return mysql_query($sql, $this->dbr);
	}
	//--------------------------------------------------------------------------

	public function insert_id()
	{
		return mysql_insert_id($this->dbr);
	}

	//--------------------------------------------------------------------------

	public function affected_rows()
	{
		return mysql_affected_rows($this->dbr);
	}

	//--------------------------------------------------------------------------

	public function error()
	{
		return mysql_errno($this->dbr);
	}

	//--------------------------------------------------------------------------

	public function error_message()
	{
		return mysql_error($this->dbr);
	}
	
	//--------------------------------------------------------------------------
}