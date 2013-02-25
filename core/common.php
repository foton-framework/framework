<?php defined('EXT') OR die('No direct script access allowed');

//--------------------------------------------------------------------------

function &ext($name = NULL)
{
	if (is_null($name))
	{
		return sys::$ext;
	}

	if ( ! isset(sys::$ext->$name))
	{
		sys::$lib->load->extension($name);
	}

	return sys::$ext->$name;
}

//--------------------------------------------------------------------------

function &lib($name = NULL)
{
	if (is_null($name))
	{
		return sys::$lib;
	}

	if ( ! isset(sys::$lib->$name))
	{
		sys::$lib->load->library($name);
	}

	return sys::$lib->$name;
}

//--------------------------------------------------------------------------

//TODO: реализовать возможность обращаться к методам: com('news/latest/10')
function &com($name = NULL)
{
	if (is_null($name))
	{
		return sys::$com;
	}

	if ( ! isset(sys::$com->$name))
	{
		sys::$lib->load->component($name);
	}

	return sys::$com->$name;
}

//--------------------------------------------------------------------------

function &model($name = NULL)
{
	if (is_null($name))
	{
		return sys::$model;
	}

	if ( ! isset(sys::$com->$name))
	{
		sys::$lib->load->model($name);
	}

	return sys::$model->$name;
}

//--------------------------------------------------------------------------

function &config($key = NULL)
{
	if (is_null($key))
	{
		return sys::$config;
	}

	if ( ! isset(sys::$config->$key))
	{
		return NULL;
	}

	return sys::$config->$key;
}

//--------------------------------------------------------------------------

function t($key, $file = 'common')
{
	static $lang = array();

	if (isset($lang[$file][$key]))
	{
		return $lang[$file][$key];
	}

	if ( ! isset($lang[$file]))
	{
		$file = APP_PATH . 'languages/' . sys::$config->sys->language . '/' . $file . LANGUAGE_EXT;

		$lang[$file] = file_exists($file) ? include($file) : array();
	}

	return isset($lang[$file][$key]) ? $lang[$file][$key] : '~'.$key.'~';
}

//--------------------------------------------------------------------------
