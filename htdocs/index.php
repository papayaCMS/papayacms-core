<?php
/**
* This script is responsible for generating all frontend output of papaya CMS, including
* file delivery (if not static or themes). It also handles basic system errors like lack
* of the papaya library, static error document and maintenance mode.
*
* @copyright 2002-2014 by dimensional GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license   GNU General Public Licence (GPL) 2 http://www.gnu.org/copyleft/gpl.html
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Frontend
*/

/**
* Including the basic configuration file
*/
$configuration =  file_exists(__DIR__.'/conf.inc.php')
  ? __DIR__.'/conf.inc.php': __DIR__.'/../papaya.php';
require_once($configuration);

$bootstrap = __DIR__.'/../vendor/autoload.php';
$error = FALSE;
if (defined('PAPAYA_MAINTENANCE_MODE') && PAPAYA_MAINTENANCE_MODE) {
  $maintenanceFile = defined('PAPAYA_ERRORDOCUMENT_MAINTENANCE')
    ? PAPAYA_ERRORDOCUMENT_MAINTENANCE : NULL;
  if ($maintenanceFile &&
      file_exists($maintenanceFile) &&
      is_file($maintenanceFile) &&
      is_readable($maintenanceFile)) {
    $error = $maintenanceFile;
  } else {
    $error = TRUE;
  }
} else {
  if (defined('PAPAYA_DBG_DEVMODE') && PAPAYA_DBG_DEVMODE) {
    $error = !include($bootstrap);
  } else {
    $error = !@include($bootstrap);
  }
}
if ($error) {
  if (PHP_SAPI === 'cgi' || PHP_SAPI === 'fast-cgi') {
    @header('Status: 503 Service Unavailable');
  } else {
    @header('HTTP/1.1 503 Service Unavailable');
  }
  header('Content-type: text/html; charset=utf-8;');
  if (is_string($error)) {
    readfile($error);
  } elseif (defined('PAPAYA_ERRORDOCUMENT_503') &&
            file_exists(PAPAYA_ERRORDOCUMENT_503) &&
            is_file(PAPAYA_ERRORDOCUMENT_503) &&
            is_readable(PAPAYA_ERRORDOCUMENT_503)) {
    header('Content-type: text/html; charset=utf-8;');
    readfile(PAPAYA_ERRORDOCUMENT_503);
  } else {
    echo 'Service Unavailable';
  }
} else {
  $PAPAYA_PAGE = new papaya_page();
  $PAPAYA_PAGE->execute();
  $PAPAYA_PAGE->get();
}
