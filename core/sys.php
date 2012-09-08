<?php defined('EXT') OR die('No direct script access allowed');



class sys
{
	public static $config;
	public static $lib;
	public static $ext;
	public static $com;
	public static $model;
	public static $log = array();
	
	//--------------------------------------------------------------------------
	
	public static function init()
	{
	
		sys::log('init', SYS_DEBUG, 'sys');
		
		sys::$log[0]['microtime'] = BENCHMARK_START;
		
		
		sys::$lib   = new stdClass();
		sys::$ext   = new stdClass();
		sys::$com   = new stdClass();
		sys::$model = new stdClass();
		
		
		spl_autoload_register('sys::_autoload');
		set_error_handler('sys::_php_error');
		
		if (@get_magic_quotes_gpc())
		{
			function _stripslashes_deep($value)
			{
				$value = is_array($value) ? array_map('_stripslashes_deep', $value) : stripslashes($value);
				return $value;
			}

			$_POST    = array_map('_stripslashes_deep', $_POST);
			$_GET     = array_map('_stripslashes_deep', $_GET);
			$_COOKIE  = array_map('_stripslashes_deep', $_COOKIE);
			$_REQUEST = array_map('_stripslashes_deep', $_REQUEST);
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function post_config_init()
	{
		if ( ! empty(sys::$config->sys->encoding))
		{
			mb_internal_encoding(sys::$config->sys->encoding);
		}
		
		if ( ! empty(sys::$config->sys->timezone))
		{
			date_default_timezone_set(sys::$config->sys->timezone);
		}
	}
	
	//--------------------------------------------------------------------------
	
	public static function load_config($name)
	{
		static $config;
		
		sys::log($name, SYS_DEBUG, 'Config');
		
		if ($config === NULL)
		{
			$config = new stdClass();
			sys::$config =& $config;
		}
		
		$file = sys::validate_file(APP_PATH . 'configs/' . $name . EXT, TRUE);
		
		require_once $file;
	}
	
	//--------------------------------------------------------------------------
	
	public static function &load_class($class, $object_name = TRUE)
	{
		$class_lower = strtolower($class);
		
		if ($object_name === TRUE)
		{
			$object_name = $class_lower;
		}
		
		
		// Return if class already loaded
		if (isset(sys::$lib->$object_name))
		{
			return sys::$lib->$object_name;
		}
		
		
		$class_dir = 'libraries/';
		$class_file_alt = strpos($class_lower, '_')
			? str_replace('_', '/', $class_lower)
			: $class_lower . '/' . $class_lower;
		
		$class_prefix = '';
		
		switch (TRUE)
		{
			case $file = sys::validate_file(APP_PATH . $class_dir . $class_lower . EXT):
			case $file = sys::validate_file(APP_PATH . $class_dir . $class_file_alt . EXT):
				break;
			
			case $file = sys::validate_file(SYS_PATH . $class_dir . $class_lower . EXT):
			case $file = sys::validate_file(SYS_PATH . $class_dir . $class_file_alt . EXT):
				$class_prefix = SYSTEM_CLASS_PREFIX;
				break;
			
			default: // Framework error
				sys::error('FILE_NOT_FOUND', array('file' => APP_PATH . 'libraries/' . $class_lower . EXT));
				return;
		}
		
		$class_name = $class_prefix . $class;
		
		sys::log($class_name, SYS_DEBUG, 'Class');
		
		require_once $file;
		
		
		if ( ! $object_name)
		{
			return $object_name;
		}
		
		sys::validate_class($class_name, TRUE);
		
		sys::$lib->$object_name = new $class_name();
		
		if (method_exists(&sys::$lib->$object_name, '_exec'))
		{
			sys::$lib->$object_name->_exec();
		}
		
		sys::set_base_objects(sys::$lib->$object_name, $object_name);
		
		return sys::$lib->$object_name;
	}
	
	//--------------------------------------------------------------------------
	
	//TODO: переделать eval() на call_user_func_array()
	public static function call($cmd, $args = array())
	{
		$cmd = explode('.', $cmd);
		$obj = NULL;
		$eval_args = array();
		foreach ($args as $i=>$val) $eval_args[$i] = '$args['.$i.']';
		
		switch ($cmd[0])
		{
			case 'com':
				isset(sys::$com->$cmd[1]) ? $obj =& sys::$com->$cmd[1] : $obj =& sys::$lib->load->component($cmd[1]);
				break;

			case 'lib':
				isset(sys::$lib->$cmd[1]) ? $obj =& sys::$lib->$cmd[1] : $obj =& sys::$lib->load->library($cmd[1]);
				break;
			
			case 'ext':
				isset(sys::$ext->$cmd[1]) ? $obj =& sys::$ext->$cmd[1] : $obj =& sys::$lib->load->extension($cmd[1]);
				break;
				
			case 'model':
				isset(sys::$model->$cmd[1]) ? $obj =& sys::$model->$cmd[1] : $obj =& sys::$lib->load->model($cmd[1]);
				break;
		}

		if (empty($cmd[2]))
		{
			return $obj;
		}

		return eval('return sys::$' . implode('->', $cmd) . '(' . implode(', ', $eval_args) . ');');
	}
	
	//--------------------------------------------------------------------------
	
	public static function set_base_objects(&$object, $object_name = FALSE)
	{
		static $base_objects = array();
		
		if ( ! $object_name)
		{
			$base_objects[] =& $object;
			
			foreach (sys::$lib as $name => &$object_link)
			{
				if (isset($object->$name)) continue;
				$object->$name =& $object_link;
			}

			foreach (sys::$ext as $name => &$object_link)
			{
				if (isset($object->$name)) continue;
				$object->$name =& $object_link;
			}

			$object->config =& sys::$config;
		}
		else
		{
			foreach ($base_objects as &$base_object)
			{
				if (isset($base_object->$object_name)) continue;
				if (get_class($base_object) == get_class($object)) continue;
				$base_object->$object_name =& $object;
			}
		}
		
	}
	
	//--------------------------------------------------------------------------
	
	public static function set_config_items(&$object, $config_name)
	{
		if ( ! isset(sys::$config->$config_name))
		{
			return;
		}
		
		foreach (sys::$config->$config_name as $key => $val)
		{
			$object->$key = $val;
		}
	}
	
	//--------------------------------------------------------------------------
	
	public static function validate_file($file, $show_error = FALSE)
	{
		if ( ! file_exists($file))
		{
			if ($show_error)
			{
				sys::error('FILE_NOT_FOUND', array('file'=>$file));
			}
			return FALSE;
		}
		
		return $file;
	}
	
	//--------------------------------------------------------------------------
	
	public static function validate_class($class_name, $show_error = FALSE)
	{
		if ( ! class_exists($class_name, FALSE))
		{
			if ($show_error)
			{
				sys::error('CLASS_NOT_FOUND', array('class'=>$class_name));
			}
			return FALSE;
		}
		
		return TRUE;
	}
	
	//--------------------------------------------------------------------------
	
	public static function benchmark($point_a = FALSE, $point_b = FALSE, $decimals = 4)
	{
		static $points = array();

		switch (TRUE)
		{
			case ! $point_a:
				$points[$point_a = '_START'] = BENCHMARK_START;
				$points[$point_b = '_END']   = microtime();
				break;

			case ! $point_b:
				$points[$point_a] = microtime();
				return;

			case ! (isset($points[$point_a]) && isset($points[$point_b])):
				return;
		}

		$a = explode(' ', $points[$point_a]);
		$b = explode(' ', $points[$point_b]);

		return number_format((float)$b[0] - (float)$a[0] + (int)$b[1] - (int)$a[1], $decimals);
	}
	
	//--------------------------------------------------------------------------
	
	public static function log($message, $level = SYS_USER, $type = NULL, $run_time = NULL)
	{
		sys::$log[] = array(
			'message'   => $message,
			'level'     => $level,
			'type'      => $type,
			'run_time'  => $run_time,
			'microtime' => microtime()
		);
	}
	
	//--------------------------------------------------------------------------
	
	public static function error($msg, $data = array())
	{
		ob_get_level() && ob_clean();
		
		$data_text = '';
		foreach ($data as $key => $val)
		{
			$val = str_replace(ROOT_PATH, '%ROOT%/', $val);
			$data_text .= "{$key}: $val<br>";
		}
		
		die("<pre><b>FRAMEWORK ERROR:</b> {$msg}<br>$data_text</pre>");
	}
	
	//--------------------------------------------------------------------------
	
	public static function error_404()
	{
		ob_get_level() && ob_clean();
		
		sys::$lib->load->autoload();
		
		if (isset(sys::$ext->admin))
		{
			sys::$ext->admin->enable = false;
		}
		
		if ( ! empty(sys::$lib->template->template_404))
		{
			die(sys::$lib->load->template(sys::$lib->template->template_404));
		}
		
		die("<h1>Page Not Found (404)</h1>");
	}
		
	//--------------------------------------------------------------------------
	
	public function db_error($msg, $data = array())
	{
		ob_get_level() && ob_clean();
		
		$data_text = '';
		foreach ($data as $key => $val)
		{
			$val = str_replace(ROOT_PATH, '%ROOT%/', $val);
			$data_text .= "{$key}: $val<br>";
		}
		
		die("<pre><b>DATABASE ERROR:</b> {$msg}<br>$data_text</pre>");
	}
	
	//--------------------------------------------------------------------------
	
	public static function _php_error($severity, $message, $file, $line)
	{
		if (($severity & error_reporting()) != $severity)
		{
			return;
		}
		
		$file = str_replace(ROOT_PATH, '', $file);
		
		sys::log("{$message}<br>File: {$file} (Line: {$line})", SYS_PHP, $severity);
		
		if (FF_DEBUG || FF_DEVMODE) echo "<div style='background:#FEE;border:2px solid #C33; padding:3px; margin:5px; font:normal 11px \"Trebuchet MS\",sans-serif'><b>PHP ERROR:</b> {$message}<br>{$file} (Line: {$line})</div>";
		
//		exit;
	}
	
	//--------------------------------------------------------------------------
	
	//TODO: необходимо связать с self::load_class что бы все инклюды классов шли через один метод
	public static function _autoload($class)
	{
		switch (TRUE)
		{
			case substr($class, 0, strlen(SYSTEM_CLASS_PREFIX)) == SYSTEM_CLASS_PREFIX:
				$class = substr($class, strlen(SYSTEM_CLASS_PREFIX));
				
				//TODO: очень некрасиво, но работает
				sys::load_class($class, FALSE);
				return;
				break;
			
			case substr($class, 0, strlen(EXTENSION_CLASS_PREFIX)) == EXTENSION_CLASS_PREFIX:
				$class = substr($class, strlen(EXTENSION_CLASS_PREFIX));
				$path  = EXT_PATH;
				break;
			
			default:
				$path  = APP_PATH;
		}
		
		switch (TRUE)
		{
			case substr($class, 0, strlen(COMPONENT_CLASS_PREFIX)) == COMPONENT_CLASS_PREFIX:
				$class  = substr($class, strlen(COMPONENT_CLASS_PREFIX));
				$folder = COM_FOLDER . '/' . strtolower($class) . '/' . strtolower($class) . COMPONENT_EXT;
				if ($path == APP_PATH) $path = ROOT_PATH;
				break;
				
			case substr($class, 0, strlen(MODEL_CLASS_PREFIX)) == MODEL_CLASS_PREFIX:
				$class  = substr($class, strlen(MODEL_CLASS_PREFIX));
				$folder = COM_FOLDER . '/' . strtolower($class) . '/' . strtolower($class) . MODEL_EXT;
				if ($path != EXT_PATH) $path = ROOT_PATH;
				break;
					
			case substr($class, 0, strlen(HELPER_CLASS_PREFIX)) == HELPER_CLASS_PREFIX:
				$class = substr($class, strlen(HELPER_CLASS_PREFIX));
				//TODO: очень некрасиво, но работает
				sys::$lib->load->helper($class);
				return;
				$class  = substr($class, strlen(HELPER_CLASS_PREFIX));
				$folder = 'helpers/' . strtolower($class) . HELPER_EXT;
				$path   = SYS_PATH;
				break;
				
			case $path == EXT_PATH;
				$folder = strtolower($class) . EXTENSION_EXT;
				break;
				
			default:
				$folder = 'libraries/' . strtolower($class) . EXT;
		}

		switch ($path)
		{			
			case EXT_PATH:
				$path .= strtolower($class) . '/' . $folder;
				break;

			default:
				$path .= $folder;
				break;
			
		}

		sys::log($class, SYS_DEBUG, 'Autoload');
		
		if (sys::validate_file($path))
		{
			require_once $path;
			return;
		}

		// try find in ext
		$path = EXT_PATH . $class . '/' . $folder;
		if (sys::validate_file($path))
		{
			require_once $path;
			return;
		}

		// sys::error("Autoload class error: class \"{$class}\" not found" );
	}
	
	//--------------------------------------------------------------------------
	
}