<?php defined('EXT') OR die('No direct script access allowed');



class SYS_Component
{
	//--------------------------------------------------------------------------
	
	public $view      = TRUE;
	public $data      = array();
	public $_file     = FALSE;
	
	//--------------------------------------------------------------------------
	
	public function __construct()
	{
		sys::set_config_items(&$this, 'component');
		
		sys::$lib->load->autoload();
		
		sys::set_base_objects(&$this);
	}
	
	//--------------------------------------------------------------------------
	
	public function _exec($component, $method, $arguments, $main_component, $com_path = COM_PATH)
	{
		// Reset values
		$this->set_view();
		$this->set_data();
		
		$arguments = (array)$arguments;
		
		// Init
		if (method_exists(&$this, 'init'))
		{
			$this->init($main_component);
		}
		
		$action_method = in_array($method, array('', NULL, FALSE), TRUE) ? 'index' : ACTION_METHOD_PREFIX . $method;
		$method_result = FALSE;
		
		ob_start();

		// Call component method
		switch (TRUE)
		{
			case method_exists(&$this, $action_method):
				$method_result = call_user_func_array(array(&$this, $action_method), $arguments);
				break;
			
			case ! $main_component:
				if ($method && method_exists(&$this, $method))
				{
					$method_result = call_user_func_array(array(&$this, $method), $arguments);
				}
				else
				{
					return sys::error('METHOD_NOT_FOUND', array('method'=>$action_method, 'component'=>get_class(&$this)));
				}
				break;
			
			case method_exists(&$this, 'router'):
				$this->set_view(FALSE);
				$arguments     = array_merge((array)$method, $arguments);
				$method_result = call_user_func_array(array(&$this, 'router'), $arguments);
				break;
		}
		
		if ($method_result !== NULL && ! $method_result)
		{
			sys::error_404();
			return;
		}
		
		if ($this->view)
		{
			if ($this->view === TRUE)
			{
				$this->view = $method ? $method : 'index';
			}

			$file = ($com_path . $component) . '/views/' . $this->view . VIEW_EXT;
			
			$parent_class = get_parent_class(&$this);
			if (substr($parent_class, 0, strlen(EXTENSION_CLASS_PREFIX)) == EXTENSION_CLASS_PREFIX)
			{
				$ext = strtolower(substr($parent_class, strlen(EXTENSION_CLASS_PREFIX . COMPONENT_CLASS_PREFIX)));

				if ( ! sys::validate_file($file))
				{
					$file = EXT_PATH . $ext . '/' . COM_FOLDER . '/' . $ext . '/views/' . $this->view . VIEW_EXT;
				}
			}
			
			echo ob_get_clean();
			echo $this->_render($file);
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function _render($view_file = FALSE)
	{
		ob_start();
		if ($view_file)
		{
			extract($this->data, EXTR_REFS + EXTR_OVERWRITE);
			require sys::validate_file(func_get_arg(0), TRUE);
		}
		
		return ob_get_clean();
	}
	
	//--------------------------------------------------------------------------
	
	public function set_view($view = TRUE)
	{
		$this->view = $view;
	}
	
	//--------------------------------------------------------------------------

	public function set_data(&$data = array())
	{
		$this->data =& $data;
	}

}