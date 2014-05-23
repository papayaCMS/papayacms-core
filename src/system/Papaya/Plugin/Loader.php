<?php
/**
* The PluginLoader allows to to get module/plugin objects by guid.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Plugins
* @version $Id: Loader.php 39721 2014-04-07 13:13:23Z weinert $
*/

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
* @property PapayaPluginList $plugins
* @property PapayaPluginOptionGroups $options
*/
class PapayaPluginLoader extends PapayaObject {

  /**
  * Database access to plugin data
  *
  * @var PapayaPluginList
  */
  private $_plugins = NULL;

  /**
  * Access to plugin options data, grouped by plugin
  *
  * @var PapayaPluginOptionGroups
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
  * @throws LogicException
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
    throw new LogicException(
      sprintf('Can not read unkown property %s::$%s', get_class($this), $name)
    );
  }

  /**
   * define plugins and options as readable properties
   *
   * @param string $name
   * @param $value
   * @throws LogicException
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
    throw new LogicException(
      sprintf('Can not write unkown property %s::$%s', get_class($this), $name)
    );
  }

  /**
  * Getter/Setter für plugin data list
  */
  public function plugins(PapayaPluginList $plugins = NULL) {
    if (isset($plugins)) {
      $this->_plugins = $plugins;
    }
    if (is_null($this->_plugins)) {
      $this->_plugins = new PapayaPluginList();
      $this->_plugins->activateLazyLoad(
        array('active' => TRUE)
      );
    }
    return $this->_plugins;
  }

  /**
  * Getter/Setter für plugin option groups (grouped by module guid)
  */
  public function options(PapayaPluginOptionGroups $groups = NULL) {
    if (isset($groups)) {
      $this->_optionGroups = $groups;
    }
    if (is_null($this->_optionGroups)) {
      $this->_optionGroups = new PapayaPluginOptionGroups();
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
  * be registered in the PapayaAutoloader
  *
  * @param string $guid
  * @param object $parent
  * @param array $data
  * @param boolean $singleInstance Plugin object should be created once,
  *   additional call will return the first instance.
  * @return Object|NULL
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
   * Alias for {@see PapayaPluginLoader::get()}. For backwards compatibility only.
   *
   * @deprecated
   * @param string $guid
   * @param Object|NULL $parent
   * @param array $data
   * @param string $class
   * @param string $file
   * @param bool $singleton Plugin object should be created once,
   *   additional calls will return the first instance.
   * @return Object|NULL
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
      if ($result = PapayaAutoloader::getClassFile($pluginData['class'])) {
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
    if (!(empty($pluginData['prefix']) || PapayaAutoloader::hasPrefix($pluginData['prefix']))) {
      $path = $this->getPluginPath($pluginData['path']);
      PapayaAutoloader::registerPath($pluginData['prefix'], $path);
    }
    if (!empty($pluginData['classes'])) {
      $path = substr($this->getPluginPath($pluginData['path']), 0, -1);
      if (!PapayaAutoloader::hasClassMap($path)) {
        /** @noinspection PhpIncludeInspection */
        PapayaAutoloader::registerClassMap(
          $path, include($path.'/'.$pluginData['classes'])
        );
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
        $logMessage = new PapayaMessageLog(
          PapayaMessageLogable::GROUP_MODULES,
          PapayaMessage::SEVERITY_ERROR,
          sprintf('Can not include module file "%s"', $fileName)
        );
        $logMessage->context()->append(new PapayaMessageContextBacktrace());
        $this->papaya()->messages->dispatch($logMessage);
        return FALSE;
      }
      if (!class_exists($pluginData['class'], FALSE)) {
        $logMessage = new PapayaMessageLog(
          PapayaMessageLogable::GROUP_MODULES,
          PapayaMessage::SEVERITY_ERROR,
          sprintf('Can not find module class "%s"', $pluginData['class'])
        );
        $logMessage->context()->append(new PapayaMessageContextBacktrace());
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
      'vendor:' => '../vendor/',
      'src:' => '../src/'
    );
    foreach ($map as $prefix => $mapPath) {
      if (0 === strpos($path, $prefix)) {
        $basePath = PapayaUtilFilePath::getDocumentRoot().$mapPath;
        $relativePath = substr($path, strlen($prefix));
        return PapayaUtilFilePath::cleanup(
          $basePath.$relativePath, TRUE
        );
      }
    }
    if ($includePath = $this->papaya()->options->get('PAPAYA_INCLUDE_PATH', '')) {
      return PapayaUtilFilePath::cleanup(
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
  * @param Object|NULL $parent
  * @param boolean $singleInstance
  * @return Object|NULL
  */
  private function createObject(array $pluginData, $parent, $singleInstance = FALSE) {
    if ($singleInstance &&
        isset($this->_instances[$pluginData['guid']])) {
      return $this->_instances[$pluginData['guid']];
    }
    $result = new $pluginData['class']($parent);
    if ($result instanceof PapayaObjectInterface) {
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
    PapayaUtilConstraints::assertObject($plugin);
    if ($plugin instanceof PapayaPluginEditable) {
      if (is_array($data) || $data instanceof Traversable) {
        $plugin->content()->assign($data);
      } elseif (is_string($data)) {
        $plugin->content()->setXml($data);
      }
    } elseif (!empty($data) && method_exists($plugin, 'setData')) {
      if (is_array($data) || $data instanceof Traversable) {
        $plugin->setData(
          PapayaUtilStringXml::serializeArray(
            PapayaUtilArray::ensure($data)
          )
        );
      } else {
        $plugin->setData($data);
      }
    }
  }
}
