<?php defined('EXT') OR die('No direct script access allowed');


class SYS_Model_Database extends SYS_Model
{
	//--------------------------------------------------------------------------
	
	public $table  = '';
	public $fields = array();
	
	//--------------------------------------------------------------------------
	
	public static $_models = array();

	//--------------------------------------------------------------------------
	
	public function __construct()
	{
		parent::__construct();

		if ( ! empty(sys::$config->sys->use_model_helper))
		{
			// require_once __DIR__ . '/helpers/field' . EXT;
			$this->set = new SYS_Model_Helpers_Field($this);
			if (method_exists($this, 'set_fields'))
			{
				$this->set_fields();
			}
		}

		$this->init();
		
		if (isset($this->admin))
		{
			if (self::$_models)
			{
				foreach (self::$_models as &$obj)
				{
					$this->admin->set_actions($obj);
				}
				self::$_models = array();
			}
			$this->admin->set_actions($this);
		}
		else
		{
			self::$_models[] =& $this;
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function init() {}
	
	//--------------------------------------------------------------------------
	
	public function create_table()
	{
		$default_engine  = 'MyISAM';
		$default_charset = 'utf8';

		$fields = array();
		$keys   = array();
		
		foreach($this->fields[$this->table] as $field => $opt)
		{
			$fields[$field] = array();
			$f = &$fields[$field];

			if (empty($opt['field'])) $opt['field'] = '';

			if (substr($field, -2) == 'id' || $field == 'status' || substr($field, 0, 3) == 'id_')
			{
				$keys[$field] = $field == 'id';
			}

			switch ($opt['field'])
			{
				case 'textarea':
				case 'html':
					$f['type'] = 'text';
					break;

				case 'input':
				case 'file':
					$size = 255;
					if (isset($opt['rules']))
					{
						preg_match('@max_length\[(\d+)\]@ui', $opt['rules'], $matches);
						if (isset($matches[1])) $size = $matches[1];
					}
					$f['type'] = 'varchar('.$size.')';
					break;
				
				default:
					$f['type'] = 'int(11)';
					break;
			}

			if ( isset($opt['default']))
			{
				$f['default'] = $opt['default'];
			}
		}

		$query_fields = array();

		foreach ($fields as $field => $opt)
		{
			$query_fields[] = "`{$field}` {$opt['type']} NOT NULL" . ($field == 'id' ? ' AUTO_INCREMENT' : '');
			// . (isset($opt['default']) ? " DEFAULT '{$opt['default']}'" : '');
		}
		foreach ($keys as $field => $primary)
		{
			$query_fields[] = $primary ? "PRIMARY KEY (`{$field}`)" : "KEY `{$field}` (`{$field}`)";
		}

		$query  = "CREATE TABLE `{$this->table}` (\n".implode(",\n", $query_fields)."\n) ENGINE={$default_engine} DEFAULT CHARSET={$default_charset};";

		$this->db->query($query);
	}

	//--------------------------------------------------------------------------

	public function init_form($table = NULL)
	{
		$this->load->library('form');
		
		$table_fields = $table === NULL ? $this->fields : array($table => $this->fields[$table]);
		
		foreach ($table_fields as $table => $field_list)
		{
			foreach ($field_list as $field => $opt)
			{
				if (empty($opt['field']))
				{
					continue;
				}
				if ( ! empty($opt['user_group']) && ! in_array($this->user->group_id, (array)$opt['user_group']))
				{
					continue;
				}
				
				$type    = $opt['field'];
				$label   = empty($opt['label'])   ? $field : $opt['label'];
				$rules   = empty($opt['rules'])   ? NULL   : $opt['rules'];
				
				if ( ! empty($opt['extra'])) $this->form->set_extra($field, $opt['extra']);
				
				$this->form->set_field($field, $type, $label, $rules);
				
				if ( ! empty($opt['options']))
				{
					$options = $opt['options'];
					
					if (is_string($options))
					{
						$options = $this->$options();
					}
					
					$this->form->set_options($field, $options);
				}
				
				if (isset($opt['default']))
				{
					$this->form->set_value($field, $opt['default']);
				}
			}
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function set_table($table)
	{
		$this->table = $table;
	}
	
	//--------------------------------------------------------------------------
	
	public function table()
	{
		return $this->table;
	}
	
	//--------------------------------------------------------------------------
	
	public function prepare_row_result(&$row)
	{
		if ( ! isset($row->admin_buttons) && isset($this->admin))
		{
			$row->admin_buttons = $this->admin->row_actions($this, $row);
		}
		
		return $row;
	}
	
	//--------------------------------------------------------------------------
	
	public function get($table = NULL)
	{
		if ( ! $table)
		{
			$table = $this->table;
		}
		
		return $this->db->get($table);
	}
	
	//--------------------------------------------------------------------------
	
	public function get_result($table = NULL)
	{
		if ( ! $table)
		{
			$table = $this->table;
		}
		
		$result = $this->get($table)->result();
		
		if (count($result))
		{
			foreach ($result as &$row)
			{
				$row = $this->prepare_row_result($row);
			}
		}
		
		return $result;
	}
	
	//--------------------------------------------------------------------------
	
	public function get_row($table = NULL, $prepare = TRUE)
	{
		if ( ! $table)
		{
			$table = $this->table;
		}

		$row = $this->get($table)->row();

		if ($prepare && $row)
		{
			$row = $this->prepare_row_result($row);
		}
		
		return $row;
	}
	
	//--------------------------------------------------------------------------
	
	public function insert($table = NULL, $data = NULL)
	{
		if ( ! $data)
		{
			$data = $_POST;
		}
		
		if ( ! $table)
		{
			$table = $this->table;
		}
		
		$insert_data = array();
		
		$fields = isset($this->fields[$table]['fields']) ? $this->fields[$table]['fields'] : $this->fields[$table];

		foreach ($fields as $field => $opt)
		{
			if ($field{0} == '_') continue;
			
			if (isset($data[$field]))
			{
				$insert_data[$field] = $data[$field];
			}
			elseif (isset($opt['default']))
			{
				$insert_data[$field] = $opt['default'];
			}
		}

		return $this->db->insert($table, $insert_data);
	}
	
	//--------------------------------------------------------------------------
	
	public function update($table = NULL, $data = NULL)
	{
		
		if ( ! $data)
		{
			$data = $_POST;
		}
		
		if ( ! $table)
		{
			$table = $this->table;
		}
		
		$update_data = array();

		foreach ($this->fields[$table] as $field => $opt)
		{
			if ($field{0} == '_') continue;
			
			if (isset($data[$field]))
			{
				$update_data[$field] = $data[$field];
			}
			elseif (isset($opt['default']))
			{
				//$update_data[$field] = $opt['default'];
			}
		}

		return $this->db->update($table, $update_data);
	}
	
	//--------------------------------------------------------------------------
	
	public function delete($table = NULL, $data = NULL)
	{
		if ( ! $table)
		{
			$table = $this->table;
		}
		
		return $this->db->delete($table, $data);
	}
	
	//--------------------------------------------------------------------------
	
	public function count_all($table = NULL)
	{
		if ( ! $table)
		{
			$table = $this->table;
		}
		
		return $this->db->count_all($table);
	}
	
	//--------------------------------------------------------------------------
}