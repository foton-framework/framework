<?php

//--------------------------------------------------------------------------

define('BENCHMARK_START', microtime());

//--------------------------------------------------------------------------

define('DS',  DIRECTORY_SEPARATOR);
define('DS_UP',  DS . '..');

//--------------------------------------------------------------------------

defined('CRON_MODE') OR define('CRON_MODE', FALSE);

//--------------------------------------------------------------------------

defined('APP_CONFIG') OR define('APP_CONFIG', 'config');

//--------------------------------------------------------------------------

defined('APP_FOLDER') OR define('APP_FOLDER', 'application');
defined('COM_FOLDER') OR define('COM_FOLDER', 'components');
defined('TPL_FOLDER') OR define('TPL_FOLDER', 'templates');
defined('SYS_FOLDER') OR define('SYS_FOLDER', 'framework');
defined('EXT_FOLDER') OR define('EXT_FOLDER', 'extensions');

//--------------------------------------------------------------------------

define('ROOT_PATH', realpath(dirname(__FILE__) . DS_UP . DS_UP) . DS);
define('APP_PATH' , realpath(ROOT_PATH . APP_FOLDER) . DS);
define('COM_PATH' , realpath(ROOT_PATH . COM_FOLDER) . DS);
define('SYS_PATH' , realpath(ROOT_PATH . SYS_FOLDER) . DS);
define('EXT_PATH' , realpath(ROOT_PATH . EXT_FOLDER) . DS);
define('TPL_PATH' , realpath(ROOT_PATH . TPL_FOLDER) . DS);

define('CORE_PATH' , SYS_PATH . 'core' . DS);

//--------------------------------------------------------------------------

defined('FF_DEBUG')   OR define('FF_DEBUG'  , file_exists(ROOT_PATH . '.FF_DEBUG'));
defined('FF_DEVMODE') OR define('FF_DEVMODE', file_exists(ROOT_PATH . '.FF_DEVMODE'));

//--------------------------------------------------------------------------

error_reporting(FF_DEBUG ? E_ALL : FALSE);

//--------------------------------------------------------------------------

define('EXT', '.' . pathinfo(__FILE__, PATHINFO_EXTENSION));

//--------------------------------------------------------------------------

require_once CORE_PATH . 'constants' . EXT;
require_once CORE_PATH . 'common' . EXT;
require_once CORE_PATH . 'sys' . EXT;

//--------------------------------------------------------------------------
// TODO: оформить запуск в отдельный класс для возможности его перекрытия через app/hooks
//--------------------------------------------------------------------------

sys::init();

sys::load_config(APP_CONFIG);

sys::post_config_init();

sys::load_class('Loader', 'load');

echo lib()->load->component();

//--------------------------------------------------------------------------