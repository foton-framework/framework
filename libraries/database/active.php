<?php


class SYS_Database_Active extends SYS_Database
{
	private $_sql;

	//--------------------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();

		$this->reset();
	}
	
	//--------------------------------------------------------------------------

	public function get($table = NULL, $where = array())
	{
		if ( ! empty($table)) $this->from($table);
		if ( ! empty($where)) $this->where($where);

		$sql = $this->_build_query();

		return $this->query($sql);
	}
	
	//--------------------------------------------------------------------------
	
	public function count_all($table = NULL, $where = array())
	{
		if ( ! empty($table)) $this->from($table);
		if ( ! empty($where)) $this->where($where);
		
		$this->select('COUNT(*)');
		
		return (int)current((array)$this->query($this->_build_query())->row_array(FALSE));;
	}
	
	//--------------------------------------------------------------------------

	public function &select($select)
	{
		if ($this->_sql['select'])
		{
			$this->_sql['select'] .= ', ';
		}

		if (is_array($select))
		{
			$select = implode(', ', $select);
		}

		$this->_sql['select'] .= $select;
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &from($table)
	{
		$this->_sql['table'] = $table;
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &join($table, $on, $type = 'LEFT')
	{
		$type = strtoupper($type);
		if ($this->_sql['join']) $this->_sql['join'] .= ' ';
		$this->_sql['join'] .= $type . ' JOIN ' . $table . ' ON ' . $on;
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &where($field, $bind_ = NULL)
	{
		if ( ! $field) return $this;
		
		switch (TRUE)
		{
			case is_array($field):
				foreach ($field as $f => $v)
				{
					$this->where($f, $v);
				}
				break;

			case func_num_args() > 1:
				if ($this->_sql['where'])
				{
					$this->_sql['where'] .= ' AND ';
				}
				$args = func_get_args();
				$this->_sql['where'] .= '(' . call_user_func_array(array($this, 'bind'), $args) . ')';
				break;

			case is_string($field):
				$this->_sql['where'] .= ($this->_sql['where'] ? ' AND ' : '') . '(' . $field . ')';
		}
		return $this;
	}
	
	//--------------------------------------------------------------------------
	
	public function or_where($field, $bind_ = NULL)
	{
		switch (TRUE)
		{
			case is_array($field):
				foreach ($field as $f => $v)
				{
					$this->where($f, $v);
				}
				break;

			case func_num_args() > 1:
				if ($this->_sql['where'])
				{
					$this->_sql['where'] .= ' OR ';
				}
				$args = func_get_args();
				$this->_sql['where'] .= '(' . call_user_func_array(array($this, 'bind'), $args) . ')';
				break;

			case is_string($field):
				$this->_sql['where'] .= ($this->_sql['where'] ? ' OR ' : '') . '(' . $field . ')';
		}
		return $this;
	}
	
	//--------------------------------------------------------------------------

	public function &where_in($field, $params = array())
	{
		foreach ($params as &$val) $val = $this->escape($val);
		$this->_sql['where'] .= ($this->_sql['where'] ? ' AND ' : '') . $field . ' IN ("' . implode('","', $params) . '")';
		return $this;
	}
	
	//--------------------------------------------------------------------------

	public function &where_not_in($field, $params = array())
	{
		foreach ($params as &$val) $val = $this->escape($val);
		$this->_sql['where'] .= ($this->_sql['where'] ? ' AND ' : '') . $field . ' NOT IN ("' . implode('","', $params) . '")';
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &group_by($field)
	{
		if ($this->_sql['group_by'])
		{
			$this->_sql['group_by'] .= ', ';
		}

		if (func_num_args() == 1)
		{
			$this->_sql['group_by'] .= $field;
		}
		else
		{
			$this->_sql['group_by'] .= $field;
		}

		return $this;
	}

	//--------------------------------------------------------------------------

	public function &order_by($field, $desc = FALSE)
	{
		if ($this->_sql['order_by'])
		{
			$this->_sql['order_by'] .= ', ';
		}
		
		if (func_num_args() == 1)
		{
			$this->_sql['order_by'] .= $field;
		}
		else
		{
			$this->_sql['order_by'] .= $field . ($desc ? ' DESC' : '');
		}

		return $this;
	}

	//--------------------------------------------------------------------------

	public function &limit($start, $count = 0)
	{
		$this->_sql['limit'] = $start . ($count ? ', ' . $count : '');
		
		return $this;
	}

	//--------------------------------------------------------------------------

	public function insert($table, $data)
	{
		foreach ($data as $field => &$value)
		{
			$value = '"' . $this->escape($value) . '"';
		}

		$this->_sql['table']  = "`{$table}`";
		$this->_sql['fields'] = '`' . implode('`, `', array_keys($data)) . '`';
		$this->_sql['values'] = implode(', ', $data);

		$this->query( $this->_build_query('INSERT') );

		return $this->insert_id();
	}

	//--------------------------------------------------------------------------
	
	public function &set($field)
	{
		$this->_sql['set'][] = $field;
		
		return $this;
	}
	
	//--------------------------------------------------------------------------
	
	public function update($table, $data = array())
	{
		if ( ! $this->_sql['where'])
		{
			return sys::db_error('"db->where()" not set');
		}
		
		foreach ($data as $field => $value)
		{
			$this->_sql['set'][] = '`' . $field . '` = "' . $this->escape($value) . '"';
		}

		$this->_sql['table'] = $table;
		
		if ( ! $this->_sql['set'])
		{
			return sys::db_error('"db->set()" not set');
		}
		
		$this->_sql['set'] = implode(', ', $this->_sql['set']);
		
		$this->query( $this->_build_query('UPDATE') );

		return $this->affected_rows();
	}

	//--------------------------------------------------------------------------

	public function delete($table = NULL, $where = array())
	{
		if ( ! empty($table)) $this->from($table);
		if ( ! empty($where)) $this->where($where);
		
		$this->query( $this->_build_query('DELETE') );
			
		return $this->affected_rows();
	}

	//--------------------------------------------------------------------------

	private function _build_query($type = 'SELECT', $data = '')
	{
		switch ($type)
		{
			case 'SELECT':
				$sql = 'SELECT ' . ($this->_sql['select'] ? trim($this->_sql['select']) : '*');
				$sql .= ' FROM ' . $this->_sql['table'];

				if ($this->_sql['join'])     $sql .= ' ' . $this->_sql['join'];
				if ($this->_sql['where'])    $sql .= ' WHERE '    . $this->_sql['where'];
				if ($this->_sql['group_by']) $sql .= ' GROUP BY ' . $this->_sql['group_by'];
				if ($this->_sql['order_by']) $sql .= ' ORDER BY ' . $this->_sql['order_by'];
				if ($this->_sql['limit'])    $sql .= ' LIMIT '    . $this->_sql['limit'];
				break;
				
			case 'INSERT':
				$sql = 'INSERT INTO ' . $this->_sql['table'];
				$sql .= ' (' . $this->_sql['fields'] . ')';
				$sql .= ' VALUES (' . $this->_sql['values'] . ')';
				break;

			case 'UPDATE':
				$sql = 'UPDATE ' . $this->_sql['table'];
				$sql .= ' SET ' . $this->_sql['set'];
				if ($this->_sql['where']) $sql .= ' WHERE ' . $this->_sql['where'];
				if ($this->_sql['limit']) $sql .= ' LIMIT '    . $this->_sql['limit'];
				break;

			case 'DELETE':
				$sql = 'DELETE FROM ' . $this->_sql['table'];
				if ($this->_sql['where']) $sql .= ' WHERE ' . $this->_sql['where'];
				if ($this->_sql['limit']) $sql .= ' LIMIT ' . $this->_sql['limit'];
				break;
		}
		

		$sql .= ';';

		$this->reset();

		return $sql;
	}
	
	//--------------------------------------------------------------------------

	public function reset()
	{
		$this->_sql = array(
			'select'   => '',
			'table'    => '',
			'where'    => '',
			'join'     => '',
			'order_by' => '',
			'group_by' => '',
			'limit'    => '',
			'values'   => '',
			'fields'   => '',
			'set'      => '',
		);
	}

}