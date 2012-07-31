<?php


class SYS_Model_Helpers_Field
{
	public $_current_table = '';
	public $_current_field = '';
	private $_model;
	private $_fields;
	private $_field_type = array(
		'int'    => 'input',
		'string' => 'input',
		'text'   => 'textarea',
		'list'   => 'select',
		'bool'   => 'checkbox',
	);

	//--------------------------------------------------------------------------

	public function __construct(&$model)
	{
		$this->_model =& $model;
	}

	//--------------------------------------------------------------------------
	
	public function &table($name)
	{
		$this->_current_table = $name;
		
		if (empty($this->_model->fields[$name]))
		{
			$this->_model->fields[$name] = array();
		}
		if (empty($this->_model->table))
		{
			$this->_model->table = $name;
		}
		
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &alias($alias)
	{
		$this->_model->fields[$this->_current_table]['alias'] = $alias;
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &select($select, $parse = TRUE)
	{
		$this->_model->fields[$this->_current_table]['select'] = $parse ? explode(',', $select) : $select;
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &relation($rel_field, $src_field, $join_type = 'left')
	{
		$this->_model->fields[$this->_current_table]['relation'] = array($rel_field, $src_field);
		$this->_model->fields[$this->_current_table]['join']     = $join_type;
		return $this;
	}

	//--------------------------------------------------------------------------

	public function get($table = NULL)
	{
		if ( ! $table)
		{
			$table = $this->_main_table;
		}
		
		return $this->_data[$table];
	}

	//--------------------------------------------------------------------------
	
	public function &f_int($name, $title = NULL, $rules = NULL)
	{
		$this->_set_field($name, 'int');
		$this->_set_field_value('field', $this->_field_type['int']);

		$title AND $this->title($title);
		$rules AND $this->rules($rules);
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &f_string($name, $title = NULL, $rules = NULL)
	{
		$this->_set_field($name, 'string');
		$this->_set_field_value('field', $this->_field_type['string']);
		
		$title AND $this->title($title);
		$rules AND $this->rules($rules);
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &f_text($name, $title = NULL, $rules = NULL)
	{
		$this->_set_field($name, 'text');
		$this->_set_field_value('field', $this->_field_type['text']);

		$title AND $this->title($title);
		$rules AND $this->rules($rules);
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &f_bool($name, $title = NULL, $rules = NULL)
	{
		$this->_set_field($name, 'bool');
		$this->_set_field_value('field', $this->_field_type['bool']);

		$title AND $this->title($title);
		$rules AND $this->rules($rules);
		return $this;
	}
	
	//--------------------------------------------------------------------------

	public function &f_list($name, $title = NULL, $rules = NULL, $options = array())
	{
		$this->_set_field($name, 'list');
		$this->_set_field_value('field', $this->_field_type['list']);

		$title   AND $this->title($title);
		$rules   AND $this->rules($rules);
		$options AND $this->options($options);
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &rules($value)
	{
		$value = str_replace(' ', '', $value);
		$this-> _set_field_value('rules', $value);
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &title($value)
	{
		$this-> _set_field_value('title', $value);
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &field($value)
	{
		$this-> _set_field_value('field', $value);
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &options($value)
	{
		$this-> _set_field_value('options', $value);
		return $this;
	}

	//--------------------------------------------------------------------------

	public function &def($value)
	{
		$this-> _set_field_value('def', $value);
		return $this;
	}

	//--------------------------------------------------------------------------

	private function _set_field_value($key, $value)
	{
		$name = $this->_current_field;
		
		if ( ! isset($this->_fields[$name]))
		{
			return;
		}
		
		$this->_fields[$name][$key] = $value;
	}
	

	//--------------------------------------------------------------------------

	private function _set_field($name, $type)
	{
		$this->_current_field = $name;

		if ( ! isset($this->_model->fields[$this->_current_table]['fields']))
		{
			$this->_model->fields[$this->_current_table]['fields'] = array();
		}

		$this->_fields =& $this->_model->fields[$this->_current_table]['fields'];

		if ( ! isset($this->_fields[$name]))
		{
			$this->_fields[$name] = array();
		}

		$this-> _set_field_value('type', $type);
	}
	
	//--------------------------------------------------------------------------

}