<?php
/**
* plugin registry, load plugins using the guid
*
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @subpackage Core
* @version $Id: base_pluginloader.php 39734 2014-04-08 19:01:37Z weinert $
*/

/**
* plugin registry, load plugins using the guid
*
* @package Papaya
* @subpackage Core
*/
class base_pluginloader extends base_db {

  /**
  * Instance of the base plugin loader singleton
  * @var base_pluginloader $_instance
  */
  private static $_instance = NULL;

  /**
  * Handles base plugin loader singleton instance
  *
  * @access public
  * @return base_pluginloader
  */
  public function getInstance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
  * Get the new PluginLoader
  *
  * @access public
  * @return PapayaPluginLoader
  */
  public function getPluginLoader() {
    return $this->papaya()->plugins;
  }

  /**
  * get a plugin object instance
  *
  * If $class and $file are not set, the plugin data is loaded from database
  *
  * @param string $guid
  * @param object $parent
  * @param string $data optional, XML plugin configuration string
  * @param string $class optional, plugin classname
  * @param string $file optional, plugin filename including path
  * @param boolean $singleton optional, default value FALSE,
  *   class is a singleton (use getInstance())
  * @access public
  * @return base_plugin
  */
  function getPluginInstance(
    $guid, $parent = NULL, $data = NULL, $class = NULL, $file = NULL, $singleton = FALSE
  ) {
    $pluginLoader = self::getInstance()->getPluginLoader();
    return $pluginLoader->get($guid, $parent, $data, $singleton);
  }

  /**
  * This method return the complete module path for a given module guid
  *
  * @param string $guid module guid
  * @return string $result full path to the guids module directory
  */
  function getPluginPath($guid) {
    $pluginLoader = self::getInstance()->getPluginLoader();
    return dirname($pluginLoader->getFileName($guid)).'/';
  }

  /**
   * Load module data from database modules table specified by guid
   * into $this->loadedGuids, if no data exists set value to false
   *
   * @param mixed $guids array or string - GUIDs of plugins
   * @return TRUE
   * @access public
   */
  function loadData($guids) {
    $pluginLoader = self::getInstance()->getPluginLoader();
    /** @noinspection PhpDeprecationInspection */
    return $pluginLoader->preload(is_array($guids) ? $guids : array($guids));
  }

  /**
  * add plugin data to plugin list
  *
  * This is a public function, other objects can add plugin data loaded in optimized queries
  *
  * @param string $guid
  * @param string $class
  * @param string $file
  * @access public
  */
  function addPluginData($guid, $class, $file) {
    //not used any more
  }

  /**
  * Create plugin object
  * Set $guid of module to create plugin object
  *
  * @param string $guid unique identifier of the object class
  * @param object $parent parent object
  * @param array $data optional data array
  * @param boolean $singleton optional, default value FALSE
  *   class is a singleton (use getInstance())
  * @access public
  * @return base_plugin
  */
  function createObject($guid, $parent = NULL, $data = NULL, $singleton = FALSE) {
    $pluginLoader = self::getInstance()->getPluginLoader();
    return $pluginLoader->get($guid, $parent, $data, $singleton);
  }

  /**
  * include plugin file by guid
  *
  * @param string $guid unique identifier of the object class
  * @access public
  * @return boolean
  */
  function includeFile($guid) {
    $pluginLoader = self::getInstance()->getPluginLoader();
    /** @noinspection PhpIncludeInspection */
    return include_once($pluginLoader->getFileName($guid));
  }
}