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

namespace Papaya\Plugin;
/**
 * The PluginLoader allows to to get module/plugin objects by guid.
 *
 * It can be used as an registry for single instance plugins, too (external singleton). The main
 * method of this class is get(), but preload() can be used to preload the nessesary data for
 * multiple plugin guids at once.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 *
 * @property \Papaya\Plugin\Collection $plugins
 * @property \Papaya\Plugin\Option\Groups $options
 */
class Loader extends \Papaya\Application\BaseObject {

  /**
   * Database access to plugin data
   *
   * @var \Papaya\Plugin\Collection
   */
  private $_plugins = NULL;

  /**
   * Access to plugin options data, grouped by plugin
   *
   * @var \Papaya\Plugin\Option\Groups
   */
  private $_optionGroups = NULL;

  /**
   * Internal list of single instance plugins (external singletons)
   *
   * @var array
   */
  private $_instances = array();

  /**
   * define plugins and options as readable properties
   *
   * @throws \LogicException
   * @param string $name
   * @return mixed
   */
  public function __get($name) {
    switch ($name) {
      case 'plugins' :
        return $this->plugins();
      case 'options' :
        return $this->options();
    }
    throw new \LogicException(
      sprintf('Can not read unknown property %s::$%s', get_class($this), $name)
    );
  }

  /**
   * define plugins and options as readable properties
   *
   * @param string $name
   * @param $value
   * @throws \LogicException
   * @return mixed
   */
  public function __set($name, $value) {
    switch ($name) {
      case 'plugins' :
        $this->plugins($value);
        return;
      case 'options' :
        $this->options($value);
        return;
    }
    throw new \LogicException(
      sprintf('Can not write unknown property %s::$%s', get_class($this), $name)
    );
  }

  /**
   * Getter/Setter für plugin data list
   */
  public function plugins(\Papaya\Plugin\Collection $plugins = NULL) {
    if (isset($plugins)) {
      $this->_plugins = $plugins;
    }
    if (is_null($this->_plugins)) {
      $this->_plugins = new \Papaya\Plugin\Collection();
      $this->_plugins->activateLazyLoad(
        array('active' => TRUE)
      );
    }
    return $this->_plugins;
  }

  /**
   * Getter/Setter für plugin option groups (grouped by module guid)
   */
  public function options(\Papaya\Plugin\Option\Groups $groups = NULL) {
    if (isset($groups)) {
      $this->_optionGroups = $groups;
    }
    if (is_null($this->_optionGroups)) {
      $this->_optionGroups = new \Papaya\Plugin\Option\Groups();
    }
    return $this->_optionGroups;
  }

  /**
   * Preload plugin data by guid. This functions allows to minimize database queries. Less database
   * queries means better performance. The system will now always load all plugins descriptions.
   *
   * @deprecated
   * @param array $guids
   * @return TRUE
   */
  public function preload(array $guids = array()) {
    return TRUE;
  }

  /**
   * Check if the data for a given plugin guid is available.
   *
   * @param string $guid
   * @return boolean
   */
  public function has($guid) {
    $plugins = $this->plugins();
    if (isset($plugins[$guid])) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Create and get a plugin instance. If the plugin package defines an autoload prefix it will
   * be registered in the \Papaya\Autoloader
   *
   * @param string $guid
   * @param object $parent
   * @param array $data
   * @param boolean $singleInstance Plugin object should be created once,
   *   additional call will return the first instance.
   * @return \Object|NULL
   */
  public function get($guid, $parent = NULL, $data = NULL, $singleInstance = FALSE) {
    $plugins = $this->plugins();
    if ($pluginData = $plugins[$guid]) {
      if ($this->preparePluginFile($pluginData)) {
        $plugin = $this->createObject($pluginData, $parent, $singleInstance);
        $this->configure($plugin, $data);
        return $plugin;
      }
    }
    return NULL;
  }

  /**
   * Alias for {@see \Papaya\Plugin\Loader::get()}. For backwards compatibility only.
   *
   * @deprecated
   * @param string $guid
   * @param \Object|NULL $parent
   * @param array $data
   * @param string $class
   * @param string $file
   * @param bool $singleton Plugin object should be created once,
   *   additional calls will return the first instance.
   * @return \Object|NULL
   */
  public function getPluginInstance(
    $guid,
    $parent = NULL,
    $data = NULL,
    $class = NULL,
    $file = NULL,
    $singleton = FALSE
  ) {
    return $this->get($guid, $parent, $data, $singleton);
  }

  /**
   * Loads the plugin data for the guid and returns the filename. The autoloader of the
   * plugin group will be activated, too.
   *
   * @param string $guid
   * @return string
   */
  public function getFileName($guid) {
    $plugins = $this->plugins();
    if ($pluginData = $plugins[$guid]) {
      $this->prepareAutoloader($pluginData);
      if ($result = \Papaya\Autoloader::getClassFile($pluginData['class'])) {
        return $result;
      }
      return $this->getPluginPath($pluginData['path']).$pluginData['file'];
    }
    return '';
  }

  /**
   * If the plugin class does not already exists, the autoloader for the plugin package is
   * registered.
   *
   * @param array $pluginData
   */
  private function prepareAutoloader(array $pluginData) {
    if (!(empty($pluginData['prefix']) || \Papaya\Autoloader::hasPrefix($pluginData['prefix']))) {
      $path = $this->getPluginPath($pluginData['path']);
      \Papaya\Autoloader::registerPath($pluginData['prefix'], $path);
    }
    if (!empty($pluginData['classes'])) {
      $path = substr($this->getPluginPath($pluginData['path']), 0, -1);
      /** @noinspection PhpIncludeInspection */
      if (
        !\Papaya\Autoloader::hasClassMap($path) &&
        ($classMap = include($path.'/'.$pluginData['classes']))
      ) {
        \Papaya\Autoloader::registerClassMap($path, $classMap);
      }
    }
  }

  /**
   * Prepares and includes a plugin file.
   *
   * @param array $pluginData
   * @return boolean
   */
  private function preparePluginFile(array $pluginData) {
    $this->prepareAutoloader($pluginData);
    if (!class_exists($pluginData['class'], TRUE)) {
      $fileName = $this->getPluginPath($pluginData['path']).$pluginData['file'];
      /** @noinspection PhpIncludeInspection */
      if (!(
        file_exists($fileName) &&
        is_readable($fileName) &&
        include_once($fileName)
      )) {
        $logMessage = new \Papaya\Message\Log(
          \Papaya\Message\Logable::GROUP_MODULES,
          \Papaya\Message::SEVERITY_ERROR,
          sprintf('Can not include module file "%s"', $fileName)
        );
        $logMessage->context()->append(new \Papaya\Message\Context\Backtrace());
        $this->papaya()->messages->dispatch($logMessage);
        return FALSE;
      }
      if (!class_exists($pluginData['class'], FALSE)) {
        $logMessage = new \Papaya\Message\Log(
          \Papaya\Message\Logable::GROUP_MODULES,
          \Papaya\Message::SEVERITY_ERROR,
          sprintf('Can not find module class "%s"', $pluginData['class'])
        );
        $logMessage->context()->append(new \Papaya\Message\Context\Backtrace());
        $this->papaya()->messages->dispatch($logMessage);
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * If the provided path is relative, prefix it with the module base path,
   * otherwise just return it.
   *
   * @param string $path
   * @throws
   * @return string|FALSE
   */
  private function getPluginPath($path = '') {
    if (preg_match('(^(?:/|[a-zA-Z]:))', $path)) {
      return $path;
    }
    $map = array(
      'vendor:' => \Papaya\Utility\File\Path::getVendorPath(),
      'src:' => \Papaya\Utility\File\Path::getSourcePath()
    );
    $documentRoot = $this->papaya()->options->get('PAPAYA_DOCUMENT_ROOT', \Papaya\Utility\File\Path::getDocumentRoot());
    foreach ($map as $prefix => $mapPath) {
      if (0 === strpos($path, $prefix)) {
        $basePath = $documentRoot.$mapPath;
        $relativePath = substr($path, strlen($prefix));
        return \Papaya\Utility\File\Path::cleanup(
          $basePath.$relativePath, TRUE
        );
      }
    }
    if ($includePath = $this->papaya()->options->get('PAPAYA_INCLUDE_PATH', '')) {
      return \Papaya\Utility\File\Path::cleanup(
          $includePath.'/modules/', TRUE
        ).$path;
    }
    return FALSE;
  }

  /**
   * Creates and returns the plugin object. If a single instance is requested, the plugin is stored
   * in an internal list. A second call will return the stored object.
   *
   * @param array $pluginData
   * @param \Object|NULL $parent
   * @param boolean $singleInstance
   * @return \Object|NULL
   */
  private function createObject(array $pluginData, $parent, $singleInstance = FALSE) {
    if ($singleInstance &&
      isset($this->_instances[$pluginData['guid']])) {
      return $this->_instances[$pluginData['guid']];
    }
    $result = new $pluginData['class']($parent);
    if ($result instanceof \Papaya\Application\Access) {
      $result->papaya($this->papaya());
    }
    /** @noinspection PhpUndefinedFieldInspection */
    $result->guid = $pluginData['guid'];
    if ($singleInstance) {
      $this->_instances[$pluginData['guid']] = $result;
    }
    return $result;
  }

  /**
   * Set plugin content data to a given plugin.
   *
   * @param object $plugin
   * @param string|array $data
   */
  public function configure($plugin, $data) {
    \Papaya\Utility\Constraints::assertObject($plugin);
    if ($plugin instanceof \Papaya\Plugin\Editable) {
      if (is_array($data) || $data instanceof \Traversable) {
        $plugin->content()->assign($data);
      } elseif (is_string($data)) {
        $plugin->content()->setXML($data);
      }
    } elseif (!empty($data) && method_exists($plugin, 'setData')) {
      if (is_array($data) || $data instanceof \Traversable) {
        $plugin->setData(
          \Papaya\Utility\Text\XML::serializeArray(
            \Papaya\Utility\Arrays::ensure($data)
          )
        );
      } else {
        $plugin->setData($data);
      }
    }
  }
}
