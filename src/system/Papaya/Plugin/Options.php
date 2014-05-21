<?php
/**
* This is a list of the options for a single plugin module.
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
* @version $Id: Options.php 39416 2014-02-27 17:02:47Z weinert $
*/

/**
* This is a list of the options for a single plugin module.
*
* The options will be loaded if the first option is read, making it a lazy load. It is
* possible to load them manuall and disable the lazy load this way.
*
* @package Papaya-Library
* @subpackage Plugins
*/
class PapayaPluginOptions extends PapayaConfiguration {

  const STATUS_CREATED = 0;
  const STATUS_LOADING = 1;
  const STATUS_LOADED = 2;

  private $_status = self::STATUS_CREATED;
  private $_storage = NULL;
  private $_guid = '';

  /**
  * Create object and store plugin guid for later loading
  *
  * @param string $guid
  */
  public function __construct($guid) {
    parent::__construct(array());
    $this->_guid = PapayaUtilStringGuid::toLower($guid);
  }

  /**
  * Set an value
  *
  * @param string $name
  * @param mixed $value
  */
  public function set($name, $value) {
    $this->lazyLoad();
    $name = PapayaUtilStringIdentifier::toUnderscoreUpper($name);
    if ($this->_status == self::STATUS_LOADING) {
      $this->_options[$name] = $value;
    } else {
      parent::set($name, $value);
    }
  }

  /**
  * Set an value, trigger loading if needed
  *
  * @param string $name
  * @param mixed $default
  * @param PapayaFilter $filter
  * @return mixed
  */
  public function get($name, $default = NULL, PapayaFilter $filter = NULL) {
    $this->lazyLoad();
    return parent::get($name, $default, $filter);
  }

  /**
  * Validate if an option is available
  *
  * @param string $name
  * @return boolean
  */
  public function has($name) {
    $this->lazyLoad();
    return parent::has($name);
  }

  /**
  * IteratorAggregate Interface: return an iterator for the options.
  *
  * @return Iterator
  */
  public function getIterator() {
    $this->lazyLoad();
    return new PapayaConfigurationIterator(array_keys($this->_options), $this);
  }

  /**
  * Make sure that the option are loaded if needed.
  */
  private function lazyLoad() {
    if ($this->_status == self::STATUS_CREATED) {
      $this->load();
    }
  }

  /**
   * Load options and change loading status
   *
   * @param PapayaConfigurationStorage $storage
   * @return bool|void
   */
  public function load(PapayaConfigurationStorage $storage = NULL) {
    $this->_status = self::STATUS_LOADING;
    parent::load($storage);
    $this->_status = self::STATUS_LOADED;
  }

  /**
  * Returns the current loading status
  *
  * @return integer
  */
  public function getStatus() {
    return $this->_status;
  }

  /**
   * Getter/Setter for the configuration storage
   *
   * @param PapayaConfigurationStorage $storage
   * @return \PapayaConfigurationStorage
   */
  public function storage(PapayaConfigurationStorage $storage = NULL) {
    if (isset($storage)) {
      $this->_storage = $storage;
    } elseif (is_null($this->_storage)) {
      $this->_storage = new PapayaPluginOptionStorage($this->_guid);
      $this->_storage->papaya($this->papaya());
    }
    return parent::storage($this->_storage);
  }
}