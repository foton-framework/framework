<?php defined('EXT') OR die('No direct script access allowed');



class SYS_Uri
{
	//--------------------------------------------------------------------------
	
	public $uri_string = '';
	public $segments   = array();
	
	public $permitted_chars = 'a-z 0-9~%.:_-';
	public $source          = 'PATH_INFO';
	
	//--------------------------------------------------------------------------
	
	public function __construct()
	{
		sys::set_config_items(&$this, 'uri');
	}
	
	//--------------------------------------------------------------------------
	
	public function _exec()
	{
		$this->_parse_uri();
	}
	
	//--------------------------------------------------------------------------
	
	public function segments($segment = 0)
	{
		if ($segment)
		{
			return isset($this->segments[$segment - 1]) ? $this->segments[$segment - 1] : FALSE;
		}
		else
		{
			return $this->segments;
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function uri_string()
	{
		return $this->uri_string;
	}
	
	//--------------------------------------------------------------------------
	
	public function _parse_uri($uri_string = NULL)
	{
		$this->uri_string = $uri_string !== NULL ? $uri_string : $this->_load_uri_string();
		
		$this->_filter_uri();

		$this->segments = $this->uri_string ? explode('/', $this->uri_string) : array();
	}

	//--------------------------------------------------------------------------

	private function _load_uri_string()
	{
		return isset($_SERVER[$this->source]) ? $_SERVER[$this->source] : '';
	}

	//--------------------------------------------------------------------------

	private function _filter_uri()
	{
		$uri_regexp = '|^[/' . $this->permitted_chars . ']+$|i';

		//$this->uri_string = htmlspecialchars($this->uri_string, ENT_QUOTES, 'utf-8');

		if ($this->uri_string && ! preg_match($uri_regexp, $this->uri_string))
		{
			return sys::error_404();
			
			$uri_string = strip_tags($this->uri_string);
			$uri_string = preg_replace('|([^/' . $this->permitted_chars . ']+)|i', '<b style="border:1px solid #FCC; color:red">\1</b>', $uri_string);
			
			header("HTTP/1.0 404 Not Found");
			//header('HTTP/1.1 400 Bad Request');
			
			sys::error('uri_wrong_chars', array('uri_string' => $uri_string));
			exit;
		}

		// Удаляем "слэш" вначале и конце url-строки
		$this->uri_string = preg_replace('|^[/]*(.*?)[/]*$|i', '\1', $this->uri_string);

		return $this->uri_string;
	}

	//--------------------------------------------------------------------------
	
}