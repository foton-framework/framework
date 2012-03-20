<?php


if (empty(sys::$config->db['driver']))
{
	sys::$config->db['driver'] = 'mysql';
}
require_once SYS_PATH . 'libraries/database/' . sys::$config->db['driver'] . '/driver' . EXT;
require_once SYS_PATH . 'libraries/database/' . sys::$config->db['driver'] . '/result' . EXT;