<?php



class SYS_Database extends SYS_Database_Driver
{
	public $result_class = 'SYS_Database_Result';
	public $active_group = 'default';
	
	public $last_query   = '';
	public $query_count  = 0;
	
	public $hostname = '';
	public $username = '';
	public $password = '';
	public $database = '';
	public $charset  = '';

	public $bind_marker = '?';

	public $group = array(
		'default' => array(
			'hostname' => '',
			'username' => '',
			'password' => '',
			'database' => '',
			'charset'  => ''
		)
	);
	//--------------------------------------------------------------------------
	
	public function __construct()
	{
		sys::set_config_items(&$this, 'db');

		$this->set_group();

		$this->db_connect();
		$this->db_select();
		$this->db_set_charset();
	}

	//--------------------------------------------------------------------------

	public function set_group($group = NULL)
	{
		if ( ! $group && isset($this->group[$group]))
		{
			$this->active_group = $group;
		}

		$this->hostname = $this->group[$this->active_group]['hostname'];
		$this->username = $this->group[$this->active_group]['username'];
		$this->password = $this->group[$this->active_group]['password'];
		$this->database = $this->group[$this->active_group]['database'];
		$this->charset  = $this->group[$this->active_group]['charset'];
	}

	//--------------------------------------------------------------------------
	
	public function db_connect()
	{

		parent::db_connect();
		
		if ( ! $this->dbr)
		{
			sys::db_error('cannot_connect_to_database', array('database'=> $this->database, 'hostname' => $this->hostname));
			exit;
		}
		
		sys::log('Hostname: ' . $this->hostname, SYS_DB, 'Connect');
	}

	//--------------------------------------------------------------------------

	public function db_select()
	{
		$result = parent::db_select();
		
		if ( ! $result)
		{
			sys::db_error('cannot_select_database', array('database' => $this->database));
			exit;
		}
		
		sys::log('Database: ' . $this->database, SYS_DB, 'Select');
		
		return $result;
	}

	//--------------------------------------------------------------------------

	public function bind($sql)
	{
		if (func_num_args() == 1)
		{
			return $sql;
		}

		$args    = func_get_args();
		$args[0] = '';

		foreach ($args as $i => $v)
		{
			$args[$i] = $this->escape($v);
		}

		$args[0] = str_replace($this->bind_marker, '"%s"', $sql);

		return call_user_func_array('sprintf', $args);
	}

	//--------------------------------------------------------------------------

	public function query($sql, $bind_ = NULL)
	{
		if (func_num_args() != 1)
		{
			$args = func_get_args();
			$sql  = call_user_func_array(array($this, 'bind'), $args);
		}
		
		$result = $this->simple_query($sql);

		$result_class = SYSTEM_CLASS_PREFIX . $this->result_class;
		
		return new $result_class($result);
	}
	
	//--------------------------------------------------------------------------
	
	public function simple_query($sql, $bind_ = NULL)
	{
		if ($bind_ !== NULL)
		{
			$args = func_get_args();
			$sql  = call_user_func_array(array($this, 'bind'), $args);
		}
		
		$this->last_query = $sql;
		
		sys::benchmark('DB_A');
		$result = parent::query($sql);
		sys::benchmark('DB_B');
		
		if ($this->error())
		{
			sys::db_error('query_error', array('sql' => $sql, 'error' => $this->error_message()));
			return $result;
		}
		
		$this->query_count ++;
		sys::log($sql, SYS_DB, 'Query: ' . $this->query_count, sys::benchmark('DB_A', 'DB_B'));
		
		return $result;
	}
	
	//--------------------------------------------------------------------------
}