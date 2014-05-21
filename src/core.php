<?php
/**
* Path to the papaya CMS class framework
*/
if (!defined('PAPAYA_INCLUDE_PATH')) {
  define('PAPAYA_INCLUDE_PATH', dirname(__FILE__).'/');
}

/**
* Include and register autoloader
*/
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Autoloader.php');
spl_autoload_register('PapayaAutoloader::load');

return TRUE;