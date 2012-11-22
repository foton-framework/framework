<?php defined('EXT') OR die('No direct script access allowed');



class SYS_Router
{
	//--------------------------------------------------------------------------
	
	public $default_component = '';
	public $db_router  = FALSE;  // Depricatred
	public $ext_router = FALSE;  // Example: "ext.sub.router"
	
	
	//--------------------------------------------------------------------------
	
	private $path      = COM_PATH;
	private $component = '';
	private $method    = '';
	private $arguments = array();
	
	//--------------------------------------------------------------------------
	
	public $_uri;
	
	//--------------------------------------------------------------------------
	
	public function __construct()
	{
		sys::set_config_items(&$this, 'router');
	}
	
	//--------------------------------------------------------------------------
	
	public function _exec()
	{
		$this->_uri =& sys::load_class('Uri');
		
		if ($this->ext_router)
		{
			sys::call($this->ext_router, array($this->_uri->segments()));
		}
		elseif ($this->db_router)
		{
			sys::load_class('Loader', 'load');
			
			sys::$lib->load->database();
			$sub = sys::$lib->db->limit(1)->where('ext_link = ?', implode('/', $this->_uri->segments()))->get('sub')->result();
			
			if ($sub)
			{
				$this->set_request(array('sub'));
			}
			else
			{
				$this->set_request($this->_uri->segments());
			}
		}
		else
		{
			$this->set_request($this->_uri->segments());
			$this->set_rules();
		}
	}

	//--------------------------------------------------------------------------
	
	public function set_rules()
	{
		$uri_string = $this->_uri->uri_string();
		
		foreach ($this->rules as $key => $rule)
		{
			if ($key == substr($uri_string, 0, strlen($key)))
			{
				$segments = explode('/', substr($uri_string, strlen($key) + 1));
				
				if (isset($rule['path'])) $this->set_path($rule['path']);
				if (isset($rule['component'])) $this->set_component($rule['component']);
				else $this->set_method(array_shift($segments));
				if (isset($rule['method'])) $this->set_method($rule['method']);
				else $this->set_method(array_shift($segments));
				if (isset($rule['arguments'])) $this->set_arguments($rule['arguments']);
				else $this->set_arguments($segments);
			}
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function set_request($segments = array())
	{
		if ( ! count($segments))
		{
			$this->set_component($this->default_component);
			return;
		}
		
		$this->set_component(array_shift(&$segments));
		$this->set_method(array_shift(&$segments));
		$this->set_arguments($segments);
	}
	
	//--------------------------------------------------------------------------
	
	public function set_path($path)
	{
		$this->path = strval($path);
	}
	
	//--------------------------------------------------------------------------
	
	public function path()
	{
		return $this->path;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_component($component)
	{
		$this->component = strval($component);
	}
	
	//--------------------------------------------------------------------------
	
	public function component()
	{
		return $this->component;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_method($method)
	{
		$this->method = strval($method);
	}
	
	//--------------------------------------------------------------------------
	
	public function method()
	{
		return $this->method;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_arguments($arguments)
	{
		$this->arguments = $arguments;
	}
	
	//--------------------------------------------------------------------------
	
	public function arguments()
	{
		return $this->arguments;
	}
	
	//--------------------------------------------------------------------------
}
