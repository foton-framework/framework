<?php defined('EXT') OR die('No direct script access allowed');

//--------------------------------------------------------------------------

define('FOTON_VERSION', '0.2');
define('FOTON_VERSION_STATUS', 'beta');

//--------------------------------------------------------------------------
//   Log levels
//--------------------------------------------------------------------------

define('SYS_PHP'   , 1);
define('SYS_DB'    , 2);
define('SYS_DEBUG' , 4);
define('SYS_USER'  , 8);

//--------------------------------------------------------------------------

define('SYSTEM_CLASS_PREFIX'   , 'SYS_');
define('COMPONENT_CLASS_PREFIX', 'COM_');
define('MODEL_CLASS_PREFIX'    , 'MODEL_');
define('EXTENSION_CLASS_PREFIX', 'EXT_');
define('HELPER_CLASS_PREFIX'   , 'h_');


//--------------------------------------------------------------------------

define('ACTION_METHOD_PREFIX', 'act_');

//--------------------------------------------------------------------------

define('COMPONENT_EXT', '.com'   . EXT);
define('VIEW_EXT'     , '.view'  . EXT);
define('TEMPLATE_EXT' , '.tpl'   . EXT);
define('HELPER_EXT'   , '.hlp'   . EXT);
define('EXTENSION_EXT', '.ext'   . EXT);
define('MODEL_EXT'    , '.model' . EXT);

//--------------------------------------------------------------------------