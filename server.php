<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

error_reporting(E_ALL & ~E_STRICT);
define('PAPAYA_DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'].'/');

$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$requestPathOriginal = parse_url($uri, PHP_URL_PATH);
$requestedPath = preg_replace('(^/sid(?:admin)?(?:[^/]+))', '', $requestPathOriginal);

if (
  file_exists(PAPAYA_DOCUMENT_ROOT.$requestPathOriginal) &&
  is_file(PAPAYA_DOCUMENT_ROOT.$requestPathOriginal)
) {
  return FALSE;
}

if (
  file_exists(PAPAYA_DOCUMENT_ROOT.$requestedPath)
) {
  if (is_file(PAPAYA_DOCUMENT_ROOT.$requestedPath)) {
    header('Location: '.$requestedPath); exit();
  }
  if (is_dir(PAPAYA_DOCUMENT_ROOT.$requestedPath) && '/' !== substr($requestedPath, -1)) {
    header('Location: '.$requestedPath.'/'); exit();
  }
  chdir(PAPAYA_DOCUMENT_ROOT.$requestedPath);
  include 'index.php';
} elseif (preg_match('(^(?<path>/papaya)/module_(?<module>.*)\.php)', $requestedPath, $match)) {
  chdir(PAPAYA_DOCUMENT_ROOT.'/papaya');
  include PAPAYA_DOCUMENT_ROOT.'/papaya/module.php';
} elseif (preg_match('(^(?:/papaya-themes/.*(css|js)\\.php))', $requestedPath, $match)) {
  chdir(PAPAYA_DOCUMENT_ROOT);
  include PAPAYA_DOCUMENT_ROOT.'/index.php';
} elseif (preg_match('(^(?<path>/.*)(?:/[^/]*))', $requestedPath, $match)) {
  chdir(PAPAYA_DOCUMENT_ROOT.$match['path']);
  return FALSE;
} else {
  chdir(PAPAYA_DOCUMENT_ROOT);
  include PAPAYA_DOCUMENT_ROOT.'index.php';
}
