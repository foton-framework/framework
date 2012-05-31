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
	
	public function result_list()
	{
		$result = $this->result_array();
		$list = array();

		if (count($result))
		{
			list($key, $val) = array_keys($result[0]);

			foreach ($result as $row)
			{
				$list[$row[$key]] = $row[$val];
			}
		}

		return $list;
	}
	
	//--------------------------------------------------------------------------
	
}