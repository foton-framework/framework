<?php defined('EXT') OR die('No direct script access allowed');



class SYS_Model
{
	public function __construct()
	{
		sys::set_config_items($this, 'model');
		sys::set_base_objects($this);
	}
}
