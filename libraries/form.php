<?php



class SYS_Form
{
	//--------------------------------------------------------------------------
	
	public $group = 'default';
	
	public $post_values       = TRUE;
	public $post_overwrite    = TRUE;
	public $strict_validation = TRUE;
	
	public $fields   = array();
	public $errors   = array();
	public $template = array(
		'error_prefix'       => '<div class="error">',
		'error_suffix'       => "</div>",
		'error_divider'      => "<hr>",
		'error_label_prefix' => '<b>"',
		'error_label_suffix' => '"</b>',
		
		'form_prefix'         => '<table class="ff_form">',
		'form_suffix'         => '</table>',
		
		'row_prefix'         => '<tr>',
		'row_suffix'         => '</tr>',
		
		'label_prefix'         => '<td>',
		'label_suffix'         => ':</td>',
		
		'field_prefix'         => '<td>',
		'field_suffix'         => '</td>',
	);
	
	public $error_messages = array(
		'required'      => 'Поле %s обязательно для заполнения.',
		'matches'       => 'Значение поля %s не совпадает со значением поля %s.',
		'valid_email'   => 'В поле %s должен быть введен корректный адрес электронной почты.',
		'valid_url'     => 'ERROR:valid_url',
		'length'        => 'Длина поля %s должна быть от %s да %s символов.',
		'min_length'    => 'Длина поля %s должна быть не меньше %s символов.',
		'max_length'    => 'Длина поля %s не может превышать %s символов.',
		'alpha'         => 'Поле %s может содержать только символы алфавита.',
		'alpha_numeric' => 'Поле %s может содержать только символы алфавита и цифры.',
		'alpha_dash'    => 'Поле %s может содержать только символы алфавита и цифры, подчеркивания и тире.',
		'integer'       => 'Поле %s должно содержать целое число.',
		'numeric'       => 'Поле %s должно содержать только цифры.',
		'callback'      => 'Callback error',
		'translit'      => 'Translit error',
		'unique'        => 'Значение поля %s не уникально!',
	);
	
	//--------------------------------------------------------------------------
	
	public function group_field($group = 'default')
	{
		$this->set_group($group);
		return h_form::hidden($this->group(), 1);
	}
	
	//--------------------------------------------------------------------------
	
	public function set_group($group = 'default')
	{
		$this->group = $group;
	}
	
	//--------------------------------------------------------------------------
	
	public function group()
	{
		return $this->group;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_field($field, $type, $label = NULL, $rules = NULL, $options = NULL)
	{
		if ($type)  $this->set_type($field, $type);
		if ($label) $this->set_label($field, $label);
		if ($rules) $this->set_rules($field, $rules);
	}
	//--------------------------------------------------------------------------
	
	public function remove_field($field)
	{
		unset($this->fields[$this->group][$field]);
		unset($this->errors[$this->group][$field]);
	}
	
	//--------------------------------------------------------------------------
	
	public function set_type($field, $type)
	{
		$this->fields[$this->group][$field]['type'] = $type;
	}
	
	//--------------------------------------------------------------------------
	
	public function type($field)
	{
		return isset($this->fields[$this->group][$field]['type']) ? $this->fields[$this->group][$field]['type'] : FALSE;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_label($field, $label)
	{
		$this->fields[$this->group][$field]['label'] = $label;
	}
	
	//--------------------------------------------------------------------------
	
	public function label($field, $for = '', $extra = '')
	{
		if ( ! isset($this->fields[$this->group][$field]['label'])) return;
		
		return h_form::label($this->fields[$this->group][$field]['label'], $for, $extra);
	}
	
	//--------------------------------------------------------------------------
	
	public function label_text($field)
	{
		if ( ! isset($this->fields[$this->group][$field]['label'])) return;
		
		return $this->fields[$this->group][$field]['label'];
	}
	
	//--------------------------------------------------------------------------
	
	public function set_rules($field, $rules)
	{
		$this->fields[$this->group][$field]['rules'] = $rules;
	}
	
	//--------------------------------------------------------------------------
	
	public function rules($field)
	{
		return $this->fields[$this->group][$field]['rules'];
	}
	
	//--------------------------------------------------------------------------
	
	public function set_extra($field, $extra)
	{
		$this->fields[$this->group][$field]['extra'] = $extra;
	}
	
	//--------------------------------------------------------------------------
	
	public function extra($field)
	{
		if ( ! isset($this->fields[$this->group][$field]['extra'])) return '';
		return $this->fields[$this->group][$field]['extra'];
	}
	
	//--------------------------------------------------------------------------
	
	public function set_options($field, $options)
	{
		$this->fields[$this->group][$field]['options'] = $options;
	}
	
	//--------------------------------------------------------------------------
	
	public function options($field)
	{
		if ( ! isset($this->fields[$this->group][$field]['options'])) return array();
		
		return $this->fields[$this->group][$field]['options'];
	}
	
	//--------------------------------------------------------------------------
	
	public function set_required($field, $required = TRUE)
	{
		if ( ! is_array($field))
		{
			$field = array($field);
		}
		
		foreach ($field as $f)
		{
			$this->fields[$this->group][$f]['required'] = $required;
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function required($field)
	{
		return empty($this->fields[$this->group][$field]['required']) ? FALSE : $this->fields[$this->group][$field]['required'];
	}
	
	//--------------------------------------------------------------------------
	
	public function field($field, $value = NULL, $extra = '')
	{
		$type = $this->type($field);
		
		if ( ! $type) return;
		
		if ($value) $this->set_value($field, $value);
		if ($extra) $this->set_extra($field, $extra);
		
		$args = func_get_args();
		
		switch ($type)
		{
			case 'select':
			case 'multiselect':
			case 'multicheckbox':
			case 'radiogroup':
			case 'checkboxgroup':
				return h_form::$type($field, $this->options($field), $this->value($field), $this->extra($field));
				break;
			case 'password':
				return h_form::password($field, $this->extra($field));
				break;
			case 'html':
				$this->set_extra($field, "id='ckfield_{$field}'");
				return h_form::textarea($field, $this->value($field), $this->extra($field))
					. "<script type='text/javascript'>
					CKEDITOR.replace('ckfield_{$field}');
					</script>";
				break;
			default:
				return h_form::$type($field, $this->value($field), $this->extra($field));
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function set_error($field, $message)
	{
		$this->errors[$this->group][$field] = $message;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_error_message($key, $message)
	{
		$this->error_messages[$key] = $message;
	}
	
	//--------------------------------------------------------------------------
	
	public function error($field, $prefix = NULL, $suffix = NULL)
	{
		if (empty($this->errors[$this->group][$field])) return;
		
		return $this->_template('error', $this->errors[$this->group][$field], $prefix, $suffix);
	}
	
	//--------------------------------------------------------------------------
	
	public function form_errors($prefix = NULL, $suffix = NULL, $divider = NULL)
	{
		if (empty($this->errors[$this->group]))
		{
			return;
		}
		
		foreach ($this->errors[$this->group] as $field => $error)
		{
			$result[] = $this->error($field, FALSE, FALSE);
		}
		
		if ($divider === NULL)
		{
			$divider = $this->template['error_divider'];
		}
		
		return $this->_template('error', implode($divider, $result), $prefix, $suffix);
	}
	
	//--------------------------------------------------------------------------
	
	public function set_post_values($enable)
	{
		$this->post_values = $enable;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_post_overwrite($enable)
	{
		$this->post_overwrite = $enable;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_strict_validation($enable)
	{
		$this->strict_validation = $enable;
	}
	
	//--------------------------------------------------------------------------
	
	public function validation()
	{
		if ( ! count($_POST) && ! count($_FILES)) return FALSE;
		
		if ( ! isset($_POST[$this->group]) && $this->group != 'default') return FALSE;
		
		foreach ($this->fields[$this->group] as $field => $opt)
		{	
			$this->field_validation($field);
		}
		
		return empty($this->errors[$this->group]);
	}
	
	//--------------------------------------------------------------------------
	
	public function field_validation($field)
	{
		if (empty($this->fields[$this->group][$field]['rules'])) return TRUE;
		
		$rules = explode('|', $this->fields[$this->group][$field]['rules']);
		$ftype = $this->fields[$this->group][$field]['type'];
		$value = $this->post_value($field);
		
		if ($this->required($field))
		{
			if ( ! trim($value))
			{
				$this->errors[$this->group][$field] = sprintf($this->error_messages['required'], $this->_template('error_label', $this->label_text($field)));
				return FALSE;
			}
		}

		foreach ($rules as $rule)
		{
			$rule_str = trim($rule);
			$rule_opt = '';
			
			if (strpos($rule_str, '[') !== FALSE)
			{
				list($rule, $rule_opt) = explode('[', substr($rule_str, 0, -1));
			}
			
			$rule_opt = explode(',', $rule_opt);
			
			// Form validation method
			$rule_method = 'r_' . $rule;
			if (method_exists(&$this, $rule_method))
			{
				$rule_method = 'r_' . $rule;
				$result = $this->$rule_method(&$value, $rule_opt, $field);
				
				if ( ! $result)
				{
					$error_msg = isset($this->error_messages[$rule_str]) ? $this->error_messages[$rule_str] : (isset($this->error_messages[$rule]) ? $this->error_messages[$rule] : FALSE);
					if ($error_msg)
					{
						$error_vals = array(
							$error_msg,
							$this->_template('error_label', $this->label_text($field)),
							$rule_opt[0],
							isset($rule_opt[1]) ? $rule_opt[1] : NULL
						);
						$this->errors[$this->group][$field] = call_user_func_array('sprintf', $error_vals);
					}
					return FALSE;
				}
				elseif (is_string($result))
				{
					$value = $result;
				}
			}
			// Ext functions
			elseif ($value && function_exists($rule))
			{
				$args = $rule_opt[0] ? array_merge(array($value), $rule_opt) : $value;
				$value = call_user_func_array($rule, (array)$args);
			}
		}
		
		if ($value !== FALSE)
		{
			if ( ! ($ftype == 'file' && ! $value))
			{
				$this->set_value($field, $value, TRUE);
			}
			return TRUE;
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function set_value($field, $value, $set_post = FALSE)
	{	
		$this->fields[$this->group][$field]['value'] = $value;
		
		if ($set_post) $this->set_post_value($field, $value);
	}
	
	//--------------------------------------------------------------------------
	
	public function value($field)
	{
		if ($this->post_overwrite)
		{
			$value = $this->post_value($field);
			if ($value !== FALSE) return $value;
		}
		
		if ( ! isset($this->fields[$this->group][$field]['value']))
		{
			if ($this->post_values)
			{
				$value = $this->post_value($field);
				if ($value !== FALSE) return $value;
			}
			
			return;
		}

		return $this->fields[$this->group][$field]['value'];
	}
	
	//--------------------------------------------------------------------------
	
	public function post_value($field)
	{
//		if (isset($this->fields[$this->group][$field]['value'])) return $this->fields[$this->group][$field]['value'];
		
		if (strpos($field, '[') !== FALSE AND preg_match_all('/\[(.*?)\]/', $field, $matches))
		{
			$indexes = $matches[1];
			array_unshift($indexes, current(explode('[', $field)));

			$value = $_POST;
			$len   = count($indexes);
			for ($i = 0; $i < $len && isset($value[$indexes[$i]]); $i++) $value = $value[$indexes[$i]];

			if ($i != $len) return FALSE;
		}
		else
		{
			$type = isset($this->fields[$this->group][$field]['type']) ? $this->fields[$this->group][$field]['type'] : '';
			switch ($type)
			{
				case 'file':
					$value = isset($_FILES[$field]) ? $_FILES[$field]['tmp_name'] : FALSE;
					break;
					
				case 'multiselect':
					$value = isset($_POST[$field]) ? $_POST[$field] : FALSE;
					if (isset($value[0]) && ! $value[0] && count($value) == 1) $value = '';
					break;
					
				default:
					$value = isset($_POST[$field]) ? $_POST[$field] : FALSE;
			}
		}
		
//		$this->fields[$this->group][$field]['value'] = $value;

//		return $this->fields[$this->group][$field]['value'];

		return $value;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_post_value($field, $value)
	{
		if (strpos($field, '[') !== FALSE AND preg_match_all('/\[(.*?)\]/', $field, $matches))
		{
			$indexes = $matches[1];
			array_unshift($indexes, current(explode('[', $field)));

			$post =& $_POST;
			$len  = count($indexes);
			for ($i = 0; $i < $len && isset($post[$indexes[$i]]); $i++)
			{
				unset($bufer);
				$bufer =& $post;
				unset($post);
				$post =& $bufer[$indexes[$i]];
			}

			if ($i != $len) return FALSE;
		}
		else
		{
			$post =& $_POST[$field];
		}
		
		return ($post = $value);
	}
	
	//--------------------------------------------------------------------------
	
	public function render($group = NULL)
	{
		if ($group) $this->set_group($group);
		
		if (empty($this->fields[$this->group])) return $this->form_errors();
		
		$result = '';

		foreach ($this->fields[$this->group] as $field => $opt)
		{
			if ( ! $this->field($field)) continue;
			
			if ($opt['type'] == 'hidden')
			{
				$result .= $this->field($field);
			}
			else
			{
				$result .= $this->_template('row',
					$this->_template('label', $this->label($field)) . 
					$this->_template('field', $this->field($field))
				);
			}
		}
		
		return $this->form_errors() . $this->_template('form', $result);
	}
	
	//--------------------------------------------------------------------------
	
	public function _template($key, $content, $prefix = NULL, $suffix = NULL)
	{
		if ($prefix === NULL) $prefix = $this->template["{$key}_prefix"];
		if ($suffix === NULL) $suffix = $this->template["{$key}_suffix"];
		
		return $prefix . $content . $suffix;
	}
	
	//--------------------------------------------------------------------------
	//   VALIDATION METHODS
	//--------------------------------------------------------------------------
	
	public function r_required($val)
	{
		if ($this->strict_validation)
		{
			return $val;
		}
		
		return $val === '' ? FALSE : TRUE;
	}
	
	//--------------------------------------------------------------------------
	
	public function r_matches($val, &$opt)
	{
		$post_value = $this->post_value($opt[0]);
		$opt[0] = $this->_template('error_label', $this->label($opt[0]));
		return $val == $post_value;
	}
	
	//--------------------------------------------------------------------------
	
	public function r_length($val, $opt)
	{
		return $this->r_min_length($val, $opt) && $this->r_max_length($val, array($opt[1]));
	}
	
	//--------------------------------------------------------------------------
	
	public function r_min_length($val, $opt)
	{
		return $val ? mb_strlen($val) >= (int)$opt[0] : TRUE;
	}
	
	//--------------------------------------------------------------------------
	
	public function r_max_length($val, $opt)
	{
		return $val ? mb_strlen($val) <= (int)$opt[0] : TRUE;
	}
	
	//--------------------------------------------------------------------------
	
	public function r_alpha($val, $opt)
	{
		return $val == '' ? TRUE : preg_match('/^[a-z]+$/ui', $val);
	}
	
	//--------------------------------------------------------------------------
	
	public function r_alpha_numeric($val, $opt)
	{
		return $val == '' ? TRUE : preg_match('/^[A-Za-z0-9]+$/ui', $val);
	}
	
	//--------------------------------------------------------------------------
	
	public function r_alpha_dash($val, $opt)
	{
		return $val == '' ? TRUE : preg_match('/^[-_A-Za-z0-9]+$/ui', $val);
	}
	
	//--------------------------------------------------------------------------
	
	public function r_integer($val, $opt)
	{
		return $val == '' ? TRUE : preg_match('/^[0-9]+$/ui', $val);
	}
	
	//--------------------------------------------------------------------------
	
	public function r_numeric($val, $opt)
	{
		return $val == '' ? TRUE : is_numeric($val);
	}
	
	//--------------------------------------------------------------------------
	
	public function r_valid_email($val, $opt)
	{
		return $val == '' ? TRUE : preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $val);
	}
	
	//--------------------------------------------------------------------------
	
	public function r_valid_url($val, $opt)
	{
		return 'http://'.preg_replace('/^http:\/\//i', '', $val);
		//return preg_match('/^http:\/\/[-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|]$/i', $val) == FALSE ? FALSE : $val;
	}
	
	//--------------------------------------------------------------------------
	
	public function r_translit($val, $opt, $field)
	{
		if ($val)
		{
			return h_common::translit($val);
		}
		
		// Нечего обрабатывать
		if ( empty($opt[0]) || empty($_POST[$opt[0]]))
		{
			return TRUE;
		}
		
		return h_common::translit($_POST[$opt[0]]);
	}
	
	//--------------------------------------------------------------------------
	
	public function r_unique($val, $opt, $field)
	{
		if (empty($opt[0]))
		{
			$this->set_error_message("unique", "Table name not set. Use attr: <b>unique[tablename]</b>");
			return FALSE;
		}
		if (($id = $this->value('id')))
		{
			sys::$lib->db->where('id!=?', $id);
		}
		return (bool)!sys::$lib->db->where("{$field}=?", $val)->count_all($opt[0]);
	}

	//--------------------------------------------------------------------------

	public function r_callback($val, $opt)
	{
		$args = '$val';
		foreach ($opt as $i=>$o) $args .= ', $opt[' . $i . ']';

		$fn_path = explode('.', $opt[0]);
		switch ($fn_path[0])
		{
			case 'com':
				if ( ! isset(sys::$com->$fn_path[1])) sys::$lib->load->component($fn_path[1]);
				break;
			
			case 'ext':
				if ( ! isset(sys::$com->$fn_path[1])) sys::$lib->load->extension($fn_path[1]);
				break;
				
			case 'model':
				if ( ! isset(sys::$model->$fn_path[1])) sys::$lib->load->model($fn_path[1]);
				break;
		}
		return eval('return sys::$' . str_replace('.', '->', $opt[0]) . '(' . $args . ');');
	}
	
	//--------------------------------------------------------------------------
	
}