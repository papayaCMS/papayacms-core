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

/**
* The PluginFactory is a superclass for specialized plguin factories. It allows to define
* an array of name => guid pairs and access the plugin by the "local" name.
*
* This allows to avoid conflicts, while still using names for plugin access and not guids.
*
* @package Papaya-Library
* @subpackage Plugins
*/
abstract class PapayaPluginFactory extends PapayaObject {

  /**
  * The plugin name => guid list.
  *
  * @var array(string=>string,...)
  */
  protected $_plugins = array();

  /**
  * Plugin options objects
  *
  * @var array
  */
  private $_options = array();

  /**
  * plugin loader object
  *
  * @var PapayaPluginLoader
  */
  private $_pluginLoader = NULL;

  /**
  * An optional owner object, given to the plugin on create.
  *
  * @var NULL|object
  */
  protected $_owner = NULL;

  /**
  * Initialize plugin factory and store the owner object.
  *
  * @param object $owner
  */
  public function __construct($owner = NULL) {
    \PapayaUtilConstraints::assertObjectOrNull($owner);
    $this->_owner = $owner;
  }

  /**
  * @param \PapayaPluginLoader $pluginLoader
  * @return \PapayaPluginLoader
  */
  public function loader(\PapayaPluginLoader $pluginLoader = NULL) {
    if ($pluginLoader !== NULL) {
      $this->_pluginLoader = $pluginLoader;
    } elseif (null === $this->_pluginLoader) {
      $this->_pluginLoader = $this->papaya()->plugins;
    }
    return $this->_pluginLoader;
  }

  /**
  * Validate if a guid for the given plugin name was defined.
  *
  * @param string $pluginName
  * @return boolean
  */
  public function has($pluginName) {
    return array_key_exists($pluginName, $this->_plugins);
  }

  /**
  * Fetch a plugin from plugin loader using the guid definition in self::$_plugins.
  *
  * @throws \InvalidArgumentException
  * @param string $pluginName
  * @param boolean $singleInstance
  * @return NULL|object
  */
  public function get($pluginName, $singleInstance = FALSE) {
    if ($this->has($pluginName)) {
      return $this->loader()->get(
        $this->_plugins[$pluginName], $this->_owner, NULL, $singleInstance
      );
    } else {
      throw new \InvalidArgumentException(
        sprintf(
          'InvalidArgumentException: "%s" does not know plugin "%s".',
          get_class($this),
          $pluginName
        )
      );
    }
  }

  /**
  * Allow to fetch plugins by using dynamic properties. This will always create a new
  * plugin instance.
  *
  * @throws \InvalidArgumentException
  * @param string $pluginName
  * @return NULL|object
  */
  public function __get($pluginName) {
    return $this->get($pluginName);
  }

  /**
   * @param $pluginName
   * @return bool
   */
  public function __isset($pluginName) {
    return $this->has($pluginName);
  }

  /**
   * @param string $pluginName
   * @param mixed $plugin
   */
  public function __set($pluginName, $plugin) {
    throw new \BadMethodCallException('Can not set plugins.');
  }

  /**
   * @param string $pluginName
   */
  public function __unset($pluginName) {
    throw new \BadMethodCallException('Can not unset plugins.');
  }

  /**
  * Getter/setter the module options object of the given plugin.
  *
  * @param string $pluginName
  * @param \PapayaConfiguration $options
  * @return NULL|\PapayaConfiguration
  */
  public function options($pluginName, \PapayaConfiguration $options = NULL) {
    if ($this->has($pluginName)) {
      if ($options !== NULL) {
        $this->_options[$pluginName] = $options;
      } elseif (!isset($this->_options[$pluginName])) {
        $this->_options[$pluginName] = $this
          ->loader()
          ->options[$this->_plugins[$pluginName]];
      }
      return $this->_options[$pluginName];
    }
    return NULL;
  }

  /**
  * Read an single option of the given plugin.
  *
  * @param string $pluginName
  * @param string $optionName
  * @param mixed $default
  * @param \PapayaFilter $filter
  * @return mixed
  */
  public function getOption($pluginName, $optionName, $default = NULL, $filter = NULL) {
    if ($options = $this->options($pluginName)) {
      return $options->get($optionName, $default, $filter);
    }
    return $default;
  }
}
