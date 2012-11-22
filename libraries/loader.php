<?php defined('EXT') OR die('No direct script access allowed');



class SYS_Loader
{
	//--------------------------------------------------------------------------
	
	public $autoload_enable = TRUE;
	public $autoload        = array();
	
	//--------------------------------------------------------------------------
	
	public $_router;
	
	//--------------------------------------------------------------------------
	
	private $_cache_on = FALSE;
	
	//--------------------------------------------------------------------------
	
	public function __construct()
	{
		sys::set_config_items(&$this, 'load');
	}
	
	//--------------------------------------------------------------------------
	
	public function _exec()
	{
		$this->_router =& sys::load_class('Router');
	}
	
	//--------------------------------------------------------------------------
	
	public function &library($class, $object_name = TRUE)
	{
		if (strtolower($class) == 'database')
		{
			return $this->database();
		}
		
		return sys::load_class($class, $object_name);
	}
	
	//--------------------------------------------------------------------------
	
	public function &model($model, $strikt = TRUE)
	{
		$model_name = strtolower($model);
		
		if (empty(sys::$model->$model_name))
		{
			$model_class = MODEL_CLASS_PREFIX . $model;

			if ( ! class_exists($model_class))
			{
				$model_class = EXTENSION_CLASS_PREFIX . $model_class;
				if ( ! class_exists($model_class))
				{
					if ($strikt)
					{
						sys::error('Model not found: ' . $model);
					}
					else
					{
						return $strikt;
					}
				}
			}

			sys::$model->$model_name = new $model_class;
		}
		
		return sys::$model->$model_name;
	}
	
	//--------------------------------------------------------------------------
	
	//TODO: Optimize file loading
	public function &database()
	{
		if ( ! isset(sys::$config->db['active_class']))
		{
			sys::$config->db['active_class'] = 'Database';
		}
		
		return sys::load_class(sys::$config->db['active_class'], 'db');
	}
	
	//--------------------------------------------------------------------------
	
	public function config($config)
	{
		return sys::load_config($config);
	}
	
	//--------------------------------------------------------------------------
	
	public function component($component = NULL, $exec_method = NULL, $arguments = array())
	{
		static $_main_component;

		$main_component = FALSE;
		$load_only      = FALSE;
		$com_path       = COM_PATH;
		
		switch (func_num_args())
		{
			case (0):
				if ($_main_component) return;
				$component   = $this->_router->component();
				$exec_method = $this->_router->method();
				$arguments   = $this->_router->arguments();
				$com_path    = $this->_router->path();
				$main_component  = TRUE;
				$_main_component = $component;
				break;
				
			case (1):
				$request = explode(' ', $component);
				if (count($request) > 1)
				{
					$com_path  = $request[0];
					$component = strtolower($request[1]);
				}
				$arguments   = explode('/', $component);
				$component   = array_shift($arguments);
				$exec_method = array_shift($arguments);
				$load_only   = ! $exec_method && $com_path == COM_PATH;
				break;
		}
		
		$create_cache = FALSE;
		if ( ! $load_only && $this->_cache_on)
		{
			$args = func_get_args();
			$cache = $this->load_cache('components', $args);
			if ($cache !== FALSE) return $cache;
			else $create_cache = TRUE;
		}
		
		sys::log($component, SYS_DEBUG, 'Component');
		
		if ( ! isset(sys::$com->$component))
		{
			$com_file = sys::validate_file($com_path . $component . '/' . $component . COMPONENT_EXT, ! $main_component);
			
			if ( ! $com_file)
			{
				sys::error_404();
			}
			
			require_once $com_file;
			
			$com_class = COMPONENT_CLASS_PREFIX . $component;
			
			sys::validate_class($com_class, TRUE);
			
			sys::$com->$component = new $com_class();
		}
		
		if ($load_only) return sys::$com->$component;
		
//		if ( ! $main_component && ! $exec_method) return;

		ob_start();
		
		switch (TRUE)
		{
			// com->_exec($method, $arguments, $main_controller)
			case method_exists(&sys::$com->$component, '_exec'):
				call_user_func_array(array(&sys::$com->$component, '_exec'), array($component, $exec_method, $arguments, $main_component, $com_path));
				break;
			
			// com->{$method}($arg1, $arg2, ...)
			case $exec_method && method_exists(&sys::$com->$component, $exec_method):
				call_user_func_array(array(&sys::$com->$component, $exec_method), $arguments);
				break;
			
			// com->index()	
			case method_exists(&sys::$com->$component, 'index'):
				sys::$com->$component->index();
				break;
			
			// error 404
			case $main_component:
				sys::error_404();
				return;
		}
		
		$content = ob_get_clean();
		
		if ($create_cache)
		{
			$args = func_get_args();
			$this->save_cache('components', $args, $content);
		}
		
		// Template
		if ($main_component && isset(sys::$lib->template) && sys::$lib->template->enable())
		{
			return sys::$lib->template->render(NULL, array('content' => $content), TRUE);
		}
		
		return $content;
	}
	
	//--------------------------------------------------------------------------
	
	public function helper($helper/*, $alias_name = NULL*/)
	{
		static $loaded_helpers = array();
		
		$lower_helper = strtolower($helper);
		//$alias_name = $alias_name ? $alias_name : $lower_helper;
		
		sys::log($helper/* . ' AS ' . $alias_name*/, SYS_DEBUG, 'Helper');
		
		if ( ! isset($loaded_helpers[$lower_helper]))
		{
			require_once sys::validate_file(SYS_PATH . 'helpers/' . $lower_helper . HELPER_EXT, TRUE);
			$loaded_helpers[$lower_helper] = TRUE;
			
			$helper_class = HELPER_CLASS_PREFIX . $lower_helper;
			if (method_exists($helper_class, '_exec'))
			{
//				$helper_class::_exec();
				call_user_func(array($helper_class, '_exec'));
			}
		}

		/*
		if ( ! class_exists($alias_name, FALSE))
		{
			$class_name = HELPER_CLASS_PREFIX . $helper;
			
			phpversion() >= 5.3
				? class_alias($class_name, $alias_name)
				: eval("class {$alias_name} extends {$class_name} {}");
		}
		*/
		
	}
	
	public function template($name, $data = array())
	{
		$create_cache = FALSE;

		if ($this->_cache_on)
		{
			$args = array($name);
			$cache = $this->load_cache('templates', $args);
			if ($cache !== FALSE) return $cache;
			else $create_cache = TRUE;
		}

		$content = sys::$lib->template->render($name, &$data);

		if ($create_cache)
		{
			$this->save_cache('templates', $args, $content);
		}

		return $content;
	}
	
	//--------------------------------------------------------------------------
	
	public function &extension($extension)
	{
		if ($extension == '*')
		{
			$this->load_all('extension', EXT_PATH);
			return $null;
		}
		
		$ext_lower = strtolower($extension);
		
		if (isset(sys::$ext->$ext_lower))
		{
			return sys::$ext->$ext_lower;
		}
		
		sys::log($extension, SYS_DEBUG, 'Extension');
		
		require_once sys::validate_file(EXT_PATH . $ext_lower . '/' . $ext_lower . EXTENSION_EXT, TRUE);
		
		$class_name = EXTENSION_CLASS_PREFIX . $extension;
		
		sys::$ext->$ext_lower = new $class_name();
		
		sys::set_base_objects(&sys::$ext->$ext_lower, $ext_lower);
		
		return sys::$ext->$ext_lower;
	}
	
	//--------------------------------------------------------------------------
	
	public function &cache($time = 0)
	{
		$this->_cache_on = sys::$ext->user->group_id == 1 ? 0 : $time;
		
		return $this;
	}
	
	//--------------------------------------------------------------------------
	
	public function load_cache($type, $args)
	{
		$result = FALSE;
		$args_str = implode(':', $args);
		$cache_file = sys::$config->sys->cache_dir . $type . '/' . md5($args_str);
		
		if (file_exists($cache_file))
		{
			$live_time = time() - filemtime($cache_file);
			if (($this->_cache_on * 60) > $live_time)
			{
				$result = file_get_contents($cache_file);
				
				sys::log('Load: ' . $type . ' - ' . implode(',', $args), 'DEBUG', 'Cache');
				
				$this->_cache_on = FALSE;
				
				
			}
		}
		
		sys::log($type . ': ' . $args_str, SYS_DEBUG, 'CACHE');

		return $result;
	}
	
	//--------------------------------------------------------------------------
	
	public function save_cache($type, $args, $data)
	{
		$cache_file = sys::$config->sys->cache_dir . $type . '/' . md5(implode(':', $args));
		
		file_put_contents($cache_file, $data);
		
		$this->_cache_on = FALSE;
		
		sys::log('Save: ' . $type . ' - ' . implode(',', $args), 'DEBUG', 'Cache');
	}
	
	//--------------------------------------------------------------------------
	
	public function load_all($type, $path)
	{
		
		if ( ! method_exists(&$this, $type)) return;
		
		$dh = opendir($path);
		while ($file = readdir($dh))
		{
			if (preg_match('/^[\w]/i', $file) && is_dir($path . $file))
			{
				call_user_func_array(array(&$this, $type), array($file));
			}
		}
		closedir($dh);
		
	}
	
	//--------------------------------------------------------------------------
	
	public function autoload()
	{
		if ( ! $this->autoload_enable)
		{
			return;
		}

		foreach($this->autoload as $type => $autoload)
		{
			if ( ! method_exists(&$this, $type)) continue;
			
			foreach($autoload as $load_params)
			{
				call_user_func_array(array(&$this, $type), (array)$load_params);
			}
		}
		
	}
	
	//--------------------------------------------------------------------------
	
}
