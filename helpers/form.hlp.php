<?php defined('EXT') OR die('No direct script access allowed');



class h_form
{
	public static $current_url  = '';
	public static $no_end_slash = FALSE;
	
	//--------------------------------------------------------------------------
	
	public static function _exec()
	{
		self::$no_end_slash = isset(sys::$config->uri->no_end_slash) ? sys::$config->uri->no_end_slash : FALSE;
		self::$current_url  = sys::$config->sys->base_url . sys::$lib->uri->uri_string();
	}
	
	//--------------------------------------------------------------------------
	
	public static function open($action = NULL, $method = 'post', $extra = '')
	{
		if ( ! $action) $action = self::$current_url;
		
		if (strpos($action, '#') !== FALSE)
		{
			
		}
		elseif (!self::$no_end_slash && preg_match('/[^\/]$/i', $action))
		{
			$action .= '/';
		}
		
		if ($extra) $extra = ' ' . $extra;
		return "<form action='{$action}' method='{$method}'{$extra}>";
	}
	
	//--------------------------------------------------------------------------
	
	public static function open_multipart($action = NULL, $method = 'post', $extra = '')
	{
		if ( ! $action) $action = self::$current_url;
		
		$extra = 'enctype="multipart/form-data"' . ($extra ? ' ' . $extra : '');
		return self::open($action, $method, $extra);
	}
	
	//--------------------------------------------------------------------------

	public static function close()
	{
		return "</form>";
	}
	
	//--------------------------------------------------------------------------

	public static function input($name, $value = NULL, $extra = '')
	{
		$value = self::_clean($value);
		if ($extra) $extra = ' ' . $extra;
		return "<input name='{$name}' value='{$value}' type='text'{$extra} />";
	}
		
	//--------------------------------------------------------------------------

	public static function password($name, $extra = '')
	{
		if ($extra) $extra = ' ' . $extra;
		return "<input name='{$name}' type='password'{$extra} />";
	}
	
	//--------------------------------------------------------------------------

	public static function hidden($name, $value = NULL, $extra = '')
	{
		$value = self::_clean($value);
		if ($extra) $extra = ' ' . $extra;
		return "<input name='{$name}' value='{$value}' type='hidden'{$extra} />";
	}
	
	//--------------------------------------------------------------------------
	
	public static function radio($name, $value = NULL, $checked = FALSE, $extra = '')
	{
		$value = self::_clean($value);
		if ($extra)   $extra   = ' ' . $extra;
		if ($checked) $checked = " checked='checked'";
		return "<input type='radio' name='{$name}' value='{$value}'{$extra}{$checked}>";
	}
		
	//--------------------------------------------------------------------------

	public static function radiogroup($name, $options_list, $selectedvalue = NULL, $extra = '')
	{
		if ($extra) $extra = ' ' . $extra;
		$radiogroup = '';

		foreach ($options_list as $value => $title)
		{
			$selected = $selectedvalue == $value ? ' checked="checked"' : '';
			$radiogroup .= "<label class=\"radio_label\"><input type='radio' style='width:auto' name='{$name}' value='{$value}'{$selected}{$extra} /> {$title}</label> ";
		}
		
		
		return $radiogroup;
	}
	
	//--------------------------------------------------------------------------
	
	public static function checkbox($name, $value = NULL, $checked = FALSE, $extra = '')
	{
		$value = self::_clean($value);
		if ($extra)   $extra   = ' ' . $extra;
		if ($checked) $checked = " checked='checked'";
		return "<input type='checkbox' name='{$name}' value='{$value}'{$extra}{$checked}>";
	}
	
	//--------------------------------------------------------------------------
	
	public static function checkboxgroup($name, $options_list, $checked = array(), $extra = '')
	{
		$checked = (array)$checked;
		$name    = $name . '[]';
		$html = '';
		foreach ($options_list as $value => $title)
		{
			
			$html .= '<label class="checkbox_label">' . self::checkbox($name, $value, in_array($value, $checked), $extra) . $title . "</label>";
		}
		return $html;
		
		$value = self::_clean($value);
		if ($extra)   $extra   = ' ' . $extra;
		if ($checked) $checked = " checked='checked'";
		return "<input type='checkbox' name='{$name}' value='{$value}'{$extra}{$checked}>";
	}
	
	//--------------------------------------------------------------------------
	
	public static function file($name, $extra = '')
	{
		if ($extra) $extra = ' ' . $extra;
		return "<input name='{$name}' type='file'{$extra} />";
	}
	
	//--------------------------------------------------------------------------

	public static function submit($name, $value = NULL, $extra = '')
	{
		if ($value === NULL)
		{
			return self::button($name, $extra);
		}
		
		$value = self::_clean($value);
		if ($extra) $extra = ' ' . $extra;
		return "<input name='{$name}' value='{$value}' type='submit' />";
	}
	
	//--------------------------------------------------------------------------
	
	public static function button($name, $extra = '')
	{
		if ($extra) $extra = ' ' . $extra;
		return "<button{$extra}>{$name}</button>";
	}
	
	//--------------------------------------------------------------------------

	public static function select($name, $options_list, $selectedvalue = NULL, $extra = '')
	{
		$options = '';
		
		foreach ($options_list as $value => $title)
		{
			if (is_array($title))
			{
				$options .= "<optgroup label='{$value}'></optgroup>";
				
				foreach($title as $val => $t)
				{
					$selected = is_array($selectedvalue)
						? (in_array($val, $selectedvalue) ? ' selected' : '')
						: ($selectedvalue == $val ? ' selected="selected"' : '');
					$options .= "<option value='{$val}'{$selected}>{$t}</option>";
				}
			}
			else
			{
				is_array($selectedvalue)
					? ($selected = in_array($value, $selectedvalue) ? ' selected' : '')
					: ($selected = $selectedvalue == $value ? ' selected' : '');
				$options .= "<option value='{$value}'{$selected}>{$title}</option>";
			}
		}
		
		if ($extra) $extra = ' ' . $extra;
		return "<select name='{$name}'{$extra}>{$options}</select>";
	}
	
	//--------------------------------------------------------------------------

	public static function multiselect($name, $options, $selectedvalues = NULL, $extra = '')
	{
		$extra = "multiple='multiple'" . ($extra ? ' ' . $extra : '');
		return self::select($name . '[]', $options, $selectedvalues, $extra);
	}
	
	//--------------------------------------------------------------------------

	public static function multicheckbox($name, $options, $selectedvalues = NULL, $extra = '')
	{
		if ($extra) $extra = ' ' . $extra;
		$html = '';
		$selectedvalues = (array)$selectedvalues;
		foreach ($options as $value => $title)
		{
			if ( ! $value) continue;
			$selected = in_array($value, $selectedvalues) ? ' checked="checked"' : '';
			$html .= "<div><label class=\"checkbox_label\"><input type='checkbox' style='width:auto' name='{$name}[]' value='{$value}'{$selected}{$extra} /> {$title}</label></div>";
		}
		
		
		return '<div class="multicheckbox">' . $html . '</div>';
	}
	
	//--------------------------------------------------------------------------

	public static function textarea($name, $value = NULL, $extra = '')
	{
		if ($extra) $extra = ' ' . $extra;
		return "<textarea name='{$name}'{$extra}>{$value}</textarea>";
	}
	
	//--------------------------------------------------------------------------
	//--------------------------------------------------------------------------
	//--------------------------------------------------------------------------
	
	public static function label($label, $for = '', $extra = '')
	{
		if ($extra) $extra = ' ' . $extra;
		if ($for)   $extra = " for='{$for}'$extra";
		return "<label{$extra}>{$label}</label>";
	}

	//--------------------------------------------------------------------------
	//--------------------------------------------------------------------------
	//--------------------------------------------------------------------------
	
	public static function value($name, $default = NULL, $escape = TRUE)
	{
		$value = isset($_POST[$name]) ? $_POST[$name] : $default;
		return $escape ? htmlspecialchars($value, ENT_QUOTES) : $value;
	}

	//--------------------------------------------------------------------------
	
	public static function _clean($val)
	{
		return htmlspecialchars($val, ENT_QUOTES);
	}
	
	//--------------------------------------------------------------------------
}