<?php
error_reporting(E_ALL & ~E_STRICT);
define('PAPAYA_DOCUMENT_ROOT', __DIR__.'/htdocs/');

$uri = $_SERVER['REQUEST_URI'] ?? '';
$requestedPath = parse_url($uri, PHP_URL_PATH);
$requestedPath = preg_replace('(^/sid(?:admin)?(?:[^/]+))', '', $requestedPath);

if (file_exists($requestedPath)) {
  if (is_file($requestedPath)) {
    return FALSE;
  }
  chdir(PAPAYA_DOCUMENT_ROOT.$requestedPath);
  include('index.php');
} elseif (preg_match('(^(?<path>/papaya)/module_(?<module>.*)\.php)', $requestedPath, $match)) {
  chdir(PAPAYA_DOCUMENT_ROOT.'/papaya');
  include(PAPAYA_DOCUMENT_ROOT.'/papaya/module.php');
} elseif (preg_match('(^(?:/papaya-themes/.*(css|js)\\.php))', $requestedPath, $match)) {
  chdir(PAPAYA_DOCUMENT_ROOT);
  include(PAPAYA_DOCUMENT_ROOT.'/index.php');
} elseif (preg_match('(^(?<path>/.*)(?:/[^/]*))', $requestedPath, $match)) {
  chdir(PAPAYA_DOCUMENT_ROOT.$match['path']);
  $file = PAPAYA_DOCUMENT_ROOT.$requestedPath;
  if (!(file_exists($file) && is_file($file))) {
    $file .= '/index.php';
  }
  return FALSE;
} else {
  chdir(PAPAYA_DOCUMENT_ROOT);
  include(PAPAYA_DOCUMENT_ROOT.'/index.php');
}


