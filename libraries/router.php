<?php defined('EXT') OR die('No direct script access allowed');



class SYS_Router
{
	//--------------------------------------------------------------------------
	
	public $default_component = '';
	public $db_router = FALSE;
	
	
	//--------------------------------------------------------------------------
	
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
		
		if ($this->db_router)
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