<?php defined('EXT') OR die('No direct script access allowed');



class SYS_Template
{
	//--------------------------------------------------------------------------

	public $enable           = TRUE;
	public $template_folder  = '';
	public $template_default = '';

	public $html_head = '';
	public $title     = '';


	//--------------------------------------------------------------------------

	public function __construct()
	{
		sys::set_config_items($this, 'template');

		sys::set_base_objects($this);

		$this->set_template_path(TPL_PATH);

		$this->template =& $this;
	}

	//--------------------------------------------------------------------------

	public function render($_template = NULL, $_data = array(), $_main_template = FALSE)
	{
		if ( ! $_template)
		{
			$_template = $this->template_default;
		}

		sys::log(($_template{0} != '/' ? $this->template_folder() . '/' : '') . $_template, SYS_DEBUG, 'Template');

		$_template_file = sys::validate_file( ( (($_template{0} == '/') or ($_template{2} == '/')) ? $_template : $this->template_path() . $this->template_folder() . '/' . $_template) . TEMPLATE_EXT, TRUE);

		extract($_data, EXTR_REFS);

		ob_start();

		$template_path = '/' . substr( dirname(realpath($_template_file)), strlen(ROOT_PATH)) . '/';

		require $_template_file;

		$output = ob_get_clean();

		if ($this->html_head && $_main_template)
		{
			$output = str_replace('</head>', $this->html_head . "\n</head>", $output);
			$this->html_head = '';
		}

		$output = str_replace(array(
			'{elapsed_time}',
			'{memory_usage}',
		), array(
			sys::benchmark(),
			sprintf('%.02f', memory_get_usage(TRUE)/1024/1024)
		), $output);

		return $output;
	}

	//--------------------------------------------------------------------------

	public function set_template_folder($template_folder)
	{
		$this->template_folder = $template_folder;
	}

	//--------------------------------------------------------------------------

	public function template_folder()
	{
		return $this->template_folder;
	}

	//--------------------------------------------------------------------------

	public function set_template_default($template_default)
	{
		$this->template_default = $template_default;
	}

	//--------------------------------------------------------------------------

	public function template_default()
	{
		return $this->template_default;
	}

	//--------------------------------------------------------------------------

	public function set_template_path($template_path)
	{
		$this->template_path = $template_path;
	}

	//--------------------------------------------------------------------------

	public function template_path()
	{
		return $this->template_path;
	}

	//--------------------------------------------------------------------------

	public function enable($enable = NULL)
	{
		if ($enable === NULL)
		{
			return $this->enable;
		}

		$this->enable = $enable;
	}

	//--------------------------------------------------------------------------

	public function head_begin()
	{
		ob_start();
	}

	//--------------------------------------------------------------------------

	public function head_end()
	{
		$this->html_head .= ob_get_clean();
	}

	//--------------------------------------------------------------------------

	public function add_head_content($content)
	{
		$this->html_head .= $content;
	}

	//--------------------------------------------------------------------------

	// public function &title($val = NULL, $replace = FALSE)
	// {
	// 	return $this->_val('title', $val, $replace);
	// }

	// //--------------------------------------------------------------------------

	// public function h1($val = NULL, $replace = FALSE)
	// {
	// 	return $this->_val('h1', $val, $replace);
	// }

	// //--------------------------------------------------------------------------

	// public function message($val = NULL, $replace = FALSE)
	// {
	// 	return $this->_val('message', $val, $replace);
	// }

	// //--------------------------------------------------------------------------

	// public function error($val = NULL, $replace = FALSE)
	// {
	// 	return $this->_val('error', $val, $replace);
	// }

	// //--------------------------------------------------------------------------

	// public function info($val = NULL, $replace = FALSE)
	// {
	// 	return $this->_val('info', $val, $replace);
	// }

	// //--------------------------------------------------------------------------

	// public function meta_description($val = NULL, $replace = FALSE)
	// {
	// 	return $this->_val('meta_description', $val, $replace);
	// }

	// //--------------------------------------------------------------------------

	// public function meta_keywords($val = NULL, $replace = FALSE)
	// {
	// 	return $this->_val('meta_description', $val, $replace);
	// }

	//--------------------------------------------------------------------------

	public function __call($name, $args)
	{
		$value   = isset($args[0]) ? $args[0] : NULL;
		$replace = isset($args[1]) ? $args[1] : FALSE;

		return $this->_val($name, $value, $replace);
	}

	//--------------------------------------------------------------------------

	private function &_val($name, $val = NULL, $replace = FALSE)
	{
		if ($val === NULL)
		{
			return $this->$name;
		}

		if ( empty($this->$name) || $replace) $this->$name = $val;

		return $this;
	}

	//--------------------------------------------------------------------------

}