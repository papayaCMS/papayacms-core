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
namespace Papaya\CMS\Plugin {

  use Papaya\Application;
  use Papaya\Autoloader;
  use Papaya\Iterator;
  use Papaya\Message\Context\Backtrace as BacktraceContext;
  use Papaya\Message\Log as LogMessage;
  use Papaya\CMS\Plugin\Collection as PluginCollection;
  use Papaya\Utility;

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
   * @property Collection $plugins
   * @property Option\Groups $options
   */
  class Loader
    implements Application\Access {
    use Application\Access\Aggregation;

    /**
     * Database access to plugin data
     *
     * @var Collection
     */
    private $_plugins;

    /**
     * Access to plugin options data, grouped by plugin
     *
     * @var Option\Groups
     */
    private $_optionGroups;

    /**
     * Internal list of single instance plugins (external singletons)
     *
     * @var array
     */
    private $_instances = [];

    /**
     * define plugins and options as readable properties
     *
     * @param string $name
     *
     * @return mixed
     * @throws \LogicException
     *
     */
    public function __get($name) {
      switch ($name) {
      case 'plugins' :
        return $this->plugins();
      case 'options' :
        return $this->options();
      }
      throw new \LogicException(
        \sprintf('Can not read unknown property %s::$%s', \get_class($this), $name)
      );
    }

    /**
     * define available dynamic properties
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __isset($name) {
      switch ($name) {
      case 'plugins' :
      case 'options' :
        return TRUE;
      }
      return FALSE;
    }

    /**
     * define plugins and options as readable properties
     *
     * @param string $name
     * @param $value
     *
     * @return mixed
     * @throws \LogicException
     *
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
        \sprintf('Can not write unknown property %s::$%s', \get_class($this), $name)
      );
    }

    /**
     * Getter/Setter für plugin data list
     *
     * @param PluginCollection|null $plugins
     *
     * @return PluginCollection
     */
    public function plugins(Collection $plugins = NULL) {
      if (NULL !== $plugins) {
        $this->_plugins = $plugins;
      }
      if (NULL === $this->_plugins) {
        $this->_plugins = new Collection();
        $this->_plugins->activateLazyLoad(
          ['active' => TRUE]
        );
      }
      return $this->_plugins;
    }

    /**
     * Getter/Setter für plugin option groups (grouped by module guid)
     *
     * @param Option\Groups|null $groups
     *
     * @return Option\Groups
     */
    public function options(Option\Groups $groups = NULL) {
      if (NULL !== $groups) {
        $this->_optionGroups = $groups;
      }
      if (NULL === $this->_optionGroups) {
        $this->_optionGroups = new Option\Groups();
      }
      return $this->_optionGroups;
    }

    /**
     * Preload plugin data by guid. This functions allows to minimize database queries. Less database
     * queries means better performance. The system will now always load all plugins descriptions.
     *
     * @return true
     * @deprecated
     */
    public function preload() {
      return TRUE;
    }

    /**
     * Check if the data for a given plugin guid is available.
     *
     * @param string $guid
     *
     * @return bool
     */
    public function has($guid) {
      $plugins = $this->plugins();
      if (isset($plugins[$guid])) {
        return TRUE;
      }
      return FALSE;
    }

    /**
     * Create and get a plugin instance. If the plugin package defines an autoload prefix it will
     * be registered in the \Papaya\Autoloader
     *
     * @param string $guid
     * @param object $parent
     * @param array $data
     * @param bool $singleInstance Plugin object should be created once,
     *                             additional call will return the first instance.
     *
     * @return object|null
     */
    public function get($guid, $parent = NULL, $data = NULL, $singleInstance = FALSE) {
      $plugins = $this->plugins();
      if (
        ($pluginData = $plugins[$guid]) &&
        $this->preparePluginFile($pluginData)
      ) {
        $plugin = $this->createObject($pluginData, $parent, $singleInstance);
        $this->configure($plugin, $data);
        return $plugin;
      }
      return NULL;
    }

    /**
     * Alias for {@see \Papaya\CMS\Plugin\Loader::get()}. For backwards compatibility only.
     *
     * @param string $guid
     * @param object|null $parent
     * @param array $data
     * @param string $class
     * @param string $file
     * @param bool $singleton Plugin object should be created once,
     *                        additional calls will return the first instance.
     *
     * @return object|null
     * @deprecated
     *
     */
    public function getPluginInstance(
      /* @noinspection PhpUnusedParameterInspection */
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
     *
     * @return string
     */
    public function getFileName($guid) {
      $plugins = $this->plugins();
      if ($pluginData = $plugins[$guid]) {
        $this->prepareAutoloader($pluginData);
        if ($result = Autoloader::getClassFile($pluginData['class'])) {
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
      if (!(empty($pluginData['prefix']) || Autoloader::hasPrefix($pluginData['prefix']))) {
        $path = $this->getPluginPath($pluginData['path']);
        Autoloader::registerPath($pluginData['prefix'], $path);
      }
      if (!empty($pluginData['classes'])) {
        $path = \substr($this->getPluginPath($pluginData['path']), 0, -1);
        /* @noinspection PhpIncludeInspection */
        if (
          !Autoloader::hasClassMap($path) &&
          ($classMap = include $path.'/'.$pluginData['classes'])
        ) {
          Autoloader::registerClassMap($path, $classMap);
        }
      }
    }

    /**
     * Prepares and includes a plugin file.
     *
     * @param array $pluginData
     *
     * @return bool
     */
    private function preparePluginFile(array $pluginData) {
      $this->prepareAutoloader($pluginData);
      if (!\class_exists($pluginData['class'], TRUE)) {
        $fileName = $this->getPluginPath($pluginData['path']).$pluginData['file'];
        /* @noinspection PhpIncludeInspection */
        /* @noinspection UsingInclusionOnceReturnValueInspection */
        if (
        !(
          \file_exists($fileName) &&
          \is_readable($fileName) &&
          include_once $fileName
        )
        ) {
          $logMessage = new LogMessage(
            LogMessage::GROUP_MODULES,
            LogMessage::SEVERITY_ERROR,
            \sprintf('Can not include module file "%s"', $fileName)
          );
          $logMessage->context()->append(new BacktraceContext());
          if ($this->papaya()->messages) {
            $this->papaya()->messages->dispatch($logMessage);
          }
          return FALSE;
        }
        if (!\class_exists($pluginData['class'], FALSE)) {
          $logMessage = new LogMessage(
            LogMessage::GROUP_MODULES,
            LogMessage::SEVERITY_ERROR,
            \sprintf('Can not find module class "%s"', $pluginData['class'])
          );
          $logMessage->context()->append(new BacktraceContext());
          if ($this->papaya()->messages) {
            $this->papaya()->messages->dispatch($logMessage);
          }
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
     *
     * @return string|false
     * @throws
     *
     */
    private function getPluginPath($path = '') {
      if (\preg_match('(^(?:/|[a-zA-Z]:))', $path)) {
        return $path;
      }
      $map = [
        'vendor:' => Utility\File\Path::getVendorPath(),
        'src:' => Utility\File\Path::getSourcePath()
      ];
      $documentRoot = $this->papaya()->options->get(
        \Papaya\CMS\CMSConfiguration::DOCUMENT_ROOT, Utility\File\Path::getDocumentRoot()
      );
      foreach ($map as $prefix => $mapPath) {
        if (0 === \strpos($path, $prefix)) {
          $basePath = $documentRoot.$mapPath;
          $relativePath = \substr($path, \strlen($prefix));
          if (substr($basePath, -1) === '/' && 0 === strpos($relativePath, '/')) {
            $relativePath = substr($relativePath, 1);
          }
          return Utility\File\Path::cleanup(
            $basePath.$relativePath, TRUE
          );
        }
      }
      if ($includePath = $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::INCLUDE_PATH, '')) {
        return Utility\File\Path::cleanup(
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
     * @param object|null $parent
     * @param bool $singleInstance
     *
     * @return object|null
     */
    private function createObject(array $pluginData, $parent, $singleInstance = FALSE) {
      if (
        $singleInstance &&
        isset($this->_instances[$pluginData['guid']])
      ) {
        return $this->_instances[$pluginData['guid']];
      }
      $result = new $pluginData['class']($parent);
      if ($result instanceof Application\Access) {
        $result->papaya($this->papaya());
      }
      /* @noinspection PhpUndefinedFieldInspection */
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
      Utility\Constraints::assertObject($plugin);
      if ($plugin instanceof \Papaya\Plugin\Editable) {
        if (is_iterable($data)) {
          $plugin->content()->assign($data);
        } elseif (\is_string($data)) {
          $plugin->content()->setXML($data);
        }
      } elseif (!empty($data) && \method_exists($plugin, 'setData')) {
        if (is_iterable($data)) {
          $plugin->setData(
            Utility\Text\XML::serializeArray(
              Utility\Arrays::ensure($data)
            )
          );
        } else {
          $plugin->setData($data);
        }
      }
    }

    /**
     * @param $type
     * @return \Traversable
     */
    public function withType($type) {
      return new Iterator\Callback(
        $this->plugins()->withType($type),
        function ($data, $guid) {
          return $this->get($guid);
        }
      );
    }
  }
}
