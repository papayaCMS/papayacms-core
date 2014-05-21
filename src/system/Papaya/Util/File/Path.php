<?php
/**
* Papaya Utilities class with static function for paths
*
* @copyright 2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Util
* @version $Id: Path.php 39429 2014-02-27 20:14:26Z weinert $
*/

/**
* Papaya Utilities class with static function for paths
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilFilePath {

  /**
   * Cleanup an absolute path and return a path without ./,  ../, multiple / and
   * make sure that start and end have / (or device letter on windows)
   *
   * @param string $path
   * @param bool $withTralingSlash
   * @return string
   */
  public static function cleanup($path, $withTralingSlash = TRUE) {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('(//+)', '/', $path);
    if (strpos($path, './') !== FALSE) {
      $result = array();
      $parts = explode('/', $path);
      foreach ($parts as $part) {
        switch ($part) {
        case '' :
        case '.' :
          break;
        case '..' :
          array_pop($result);
          break;
        default :
          $result[] = $part;
          break;
        }
      }
      $path = implode('/', $result);
    } else {
      $path = str_replace('/./', '/', $path);
    }
    $path = ($withTralingSlash)
      ? self::ensureTrailingSlash($path) : self::ensureNoTrailingSlash($path);
    $path = self::ensureIsAbsolute($path);
    return $path;
  }

  /**
  * Make sure that the path starts with an / or a device letter. Add a / if neither is found.
  *
  * @param string $path
  * @return string
  */
  public static function ensureIsAbsolute($path) {
    if (substr($path, 0, 1) !== '/' &&
        !preg_match('(^[a-zA-Z]:/)', $path)) {
      return '/'.$path;
    } else {
      return $path;
    }
  }

  /**
  * Make sure that the path ends with an /.
  *
  * @param string $path
  * @return string
  */
  public static function ensureTrailingSlash($path) {
    if (substr($path, -1) !== '/') {
      return $path.'/';
    } else {
      return $path;
    }
  }


  /**
  * Make sure that the path does not end with an /.
  *
  * @param string $path
  * @return string
  */
  public static function ensureNoTrailingSlash($path) {
    if (substr($path, -1) === '/') {
      return substr($path, 0, -1);
    } else {
      return $path;
    }
  }

  /**
   * Get the base path/url path to the called script
   *
   * @param bool|string $includeDocumentRoot
   * @return string
   */
  public static function getBasePath($includeDocumentRoot = TRUE) {
    $path = dirname($_SERVER['SCRIPT_FILENAME']);
    if ($includeDocumentRoot) {
      $result = $path;
    } else {
      if (preg_match('~^\w:~', $_SERVER['DOCUMENT_ROOT']) &&
          !preg_match('~^\w:~', $_SERVER['SCRIPT_FILENAME'])) {
        $result = substr($path, strlen($_SERVER['DOCUMENT_ROOT']) - 2);
      } else {
        $result = substr($path, strlen($_SERVER['DOCUMENT_ROOT']));
      }
    }
    return self::cleanup($result);
  }

  /**
   * Get the document root if possible
   * @param PapayaConfiguration $options
   * @return string
   */
  public static function getDocumentRoot($options = NULL) {
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
      return self::cleanup($_SERVER['DOCUMENT_ROOT']);
    } elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
      $path = dirname($_SERVER['SCRIPT_FILENAME']);
      if (isset($options)) {
        if ($options->get('PAPAYA_ADMIN_PAGE', FALSE)) {
          $path = dirname($path);
        }
        if ($webPath = $options->get('PAPAYA_PATH_WEB', '/')) {
          $path = substr($path, 0, 1 - strlen($webPath));
        }
      }
      return self::cleanup($path);
    }
    return '/';
  }

  /**
  * Remove all files and subdirectories in a given directory.
  *
  * @param string $directory
  * @return integer
  */
  public static function clear($directory) {
    $counter = 0;
    if (is_dir($directory)) {
      if ($dh = opendir($directory)) {
        if (!in_array(substr($directory, -1), array('/', DIRECTORY_SEPARATOR))) {
          $directory .= DIRECTORY_SEPARATOR;
        }
        while (FALSE !== ($entry = readdir($dh))) {
          if ($entry !== '.' && $entry !== '..') {
            if (is_dir($directory.$entry)) {
              $counter += self::clear($directory.$entry.DIRECTORY_SEPARATOR);
              @rmdir($directory.$entry);
            } elseif (is_file($directory.$entry)) {
              if (@unlink($directory.$entry)) {
                ++$counter;
              }
            }
          }
        }
        closedir($dh);
      }
    }
    return $counter;
  }
}