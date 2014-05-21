<?php
/**
* Papaya autoloader
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
* @package Papaya
* @version $Id: Autoloader.php 39434 2014-02-28 09:37:30Z weinert $
*/

/**
* Papaya autoloader
*
* @package Papaya-Library
*/
class PapayaAutoloader {

  /**
  * prefix => path mapping array for modules/plugins.
  *
  * @var array
  */
  private static $_paths = array();

  /**
   * path => array('lowecaseclass' => '/path/class.php', ...)
   *
   * @var array
   */
  private static $_classmaps = array();

  /**
   *
   * @param string $name
   * @param string|null $file
   * @return void
   */
  public static function load($name, $file = NULL) {
    if (!class_exists($name, FALSE)) {
      $file = (is_null($file)) ? self::getClassFile($name) : $file;
      if (isset($file) &&
          file_exists($file) &&
          is_file($file) &&
          is_readable($file)) {
        /** @noinspection PhpIncludeInspection */
        include($file);
      }
    }
  }

  /**
  * Get file for a class
  *
  * @param string $className
  * @return string|NULL
  */
  public static function getClassFile($className) {
    static $systemDirectory = NULL;
    $systemDirectory = isset($systemDirectory)
      ? $systemDirectory : str_replace('\\', '/', dirname(__DIR__));
    self::lazyLoadClassmap($systemDirectory);
    $key = strtolower($className);
    foreach (self::$_classmaps as $path => $map) {
      if (isset($map[$key])) {
        return $path.$map[$key];
      }
    }
    $fileName = self::prepareFileName($className);
    if (0 !== strpos($fileName, '/Papaya/') ||
        0 === strpos($fileName, '/Papaya/Module/')) {
      foreach (self::$_paths as $prefix => $path) {
        if (0 === strpos($fileName, $prefix)) {
          return $path.substr($fileName, strlen($prefix)).'.php';
        }
      }
      return NULL;
    } else {
      return $systemDirectory.$fileName.'.php';
    }
  }

  /**
  * Get file from matches class parts
  *
  * The file will include only the part of the path defined by the class.
  *
  * @param array $className
  * @return string
  */
  private static function prepareFileName($className) {
    $classPattern = '((?:[A-Z][a-z\d]+)|(?:[A-Z]+(?![a-z\d])))S';
    if (preg_match_all($classPattern, $className, $matches)) {
      $parts = $matches[0];
    } else {
      return '/'.$className;
    }
    $result = '';
    foreach ($parts as $part) {
      $result .= '/'.ucfirst(strtolower($part));
    }
    return $result;
  }

  /**
  * Register an path for classes starting with a defined prefix. The prefix "Papaya" is reserved,
  * except "PapayaModule".
  *
  * @param string $modulePrefix
  * @param string $modulePath
  */
  public static function registerPath($modulePrefix, $modulePath) {
    self::$_paths[self::prepareFileName($modulePrefix).'/'] =
      PapayaUtilFilePath::cleanup($modulePath);
    uksort(self::$_paths, array('self', 'compareByCharacterLength'));
  }

  /**
   * Check if a classname prefix is already registered.
   *
   * @param $modulePrefix
   * @return bool
   */
  public static function hasPrefix($modulePrefix) {
    return isset(self::$_paths[self::prepareFileName($modulePrefix).'/']);
  }

  /**
   * Register an class map for a path. The map is an array of lowercase classnames (as keys)
   * and the class specific part of the path to the file containing the class.
   *
   * The $path argument is used as a prefix for the class file name.
   *
   * array(
   *   'classname' => '/path/to/class.php',
   *   ...
   * )
   *
   * @param string $path
   * @param array $classMap
   */
  public static function registerClassMap($path, array $classMap) {
    self::$_classmaps[$path] = $classMap;
  }

  /**
   * Check if a classmap for the given path is already registered
   *
   * @param $path
   * @return bool
   */
  public static function hasClassMap($path) {
    return isset(self::$_classmaps[$path]);
  }

  /**
   * Registered prefix are sortet by length (descending). A longer prefix has a higher priority.
   *
   * @param string $prefixOne
   * @param string $prefixTwo
   * @return int
   */
  public static function compareByCharacterLength($prefixOne, $prefixTwo) {
    if (strlen($prefixOne) > strlen($prefixTwo)) {
      return -1;
    } else {
      return strcmp($prefixOne, $prefixTwo);
    }
  }

  /**
  * Clear all additional registered data about class and path mappings
  */
  public static function clear() {
    self::$_paths = array();
    self::$_classmaps = array();
  }

  /**
   * Lazy load the class map for the old classes in the papaya-lib/system directory
   *
   * @param string $directory
   */
  private static function lazyLoadClassmap($directory) {
    if (empty(self::$_classmaps) || !isset(self::$_classmaps[$directory])) {
      /** @noinspection PhpIncludeInspection */
      self::registerClassMap($directory, include($directory.'/_classmap.php'));
    }
  }
}