<?php



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