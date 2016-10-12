<?php
error_reporting(E_ALL & ~E_STRICT);
define('PAPAYA_DOCUMENT_ROOT', __DIR__.'/');

$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$requestedPath = parse_url($uri, PHP_URL_PATH);

$requestedPath = preg_replace('(^/sid(?:admin)?(?:[^/]+))', '', $requestedPath);

if (file_exists($requestedPath)) {
  if (is_file($requestedPath)) {
    return FALSE;
  } else {
    chdir(__DIR__.$requestedPath);
    include('index.php');
  }
} elseif (preg_match('(^(?<path>/papaya)/module_(?<module>.*)\.php)', $requestedPath, $match)) {
  chdir(__DIR__.'/papaya');
  include(__DIR__.'/papaya/module.php');
} elseif (preg_match('(^(?:/papaya-themes/.*(css|js)\\.php))', $requestedPath, $match)) {
  chdir(__DIR__);
  include(__DIR__.'/index.php');
} elseif (preg_match('(^(?<path>/.*)(?:/[^/]*))', $requestedPath, $match)) {
  chdir(__DIR__.$match['path']);
  $file = __DIR__.$requestedPath;
  if (!(file_exists($file) && is_file($file))) {
    $file .= '/index.php';
  }
  include($file);
} else {
  chdir(__DIR__);
  include(__DIR__.'/index.php');
}


