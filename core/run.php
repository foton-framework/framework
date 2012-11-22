<?php
die('test submod');
//--------------------------------------------------------------------------

define('BENCHMARK_START', microtime());

//--------------------------------------------------------------------------

defined('APP_FOLDER') OR define('CRON_MODE', FALSE);

//--------------------------------------------------------------------------

defined('APP_FOLDER') OR define('APP_FOLDER', 'application');
defined('COM_FOLDER') OR define('COM_FOLDER', 'components');
defined('TPL_FOLDER') OR define('TPL_FOLDER', 'templates');
defined('SYS_FOLDER') OR define('SYS_FOLDER', 'framework');
defined('EXT_FOLDER') OR define('EXT_FOLDER', 'extensions');

//--------------------------------------------------------------------------

define('ROOT_PATH', realpath(dirname(__FILE__) . '/../../') . '/');
define('APP_PATH' , realpath(ROOT_PATH . APP_FOLDER) . '/');
define('COM_PATH' , realpath(ROOT_PATH . COM_FOLDER) . '/');
define('SYS_PATH' , realpath(ROOT_PATH . SYS_FOLDER) . '/');
define('EXT_PATH' , realpath(ROOT_PATH . EXT_FOLDER) . '/');
define('TPL_PATH' , realpath(ROOT_PATH . TPL_FOLDER) . '/');

//--------------------------------------------------------------------------

defined('FF_DEBUG')   OR define('FF_DEBUG'  , file_exists(ROOT_PATH . '.FF_DEBUG'));
defined('FF_DEVMODE') OR define('FF_DEVMODE', file_exists(ROOT_PATH . '.FF_DEVMODE'));

//--------------------------------------------------------------------------

error_reporting(FF_DEBUG ? E_ALL : FALSE);

//--------------------------------------------------------------------------

define('EXT', '.' . pathinfo(__FILE__, PATHINFO_EXTENSION));

//--------------------------------------------------------------------------

require_once SYS_PATH . 'core/constants' . EXT;
require_once SYS_PATH . 'core/sys' . EXT;

//--------------------------------------------------------------------------
// TODO: оформить запуск в отдельный класс для возможности его перекрытия через app/hooks
//--------------------------------------------------------------------------

sys::init();

sys::load_config('config');

sys::post_config_init();

sys::load_class('Loader', 'load');

echo sys::$lib->load->component();

//--------------------------------------------------------------------------