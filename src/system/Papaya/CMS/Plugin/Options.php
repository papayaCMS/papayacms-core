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
namespace Papaya\CMS\Plugin;

use Papaya\Application;
use Papaya\Configuration;
use Papaya\Filter;
use Papaya\Utility;

/**
 * This is a list of the options for a single plugin module.
 *
 * The options will be loaded if the first option is read, making it a lazy load. It is
 * possible to load them manually and disable the lazy load this way.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Options extends Configuration implements Application\Access {
  use Application\Access\Aggregation;

  public const STATUS_CREATED = 0;

  public const STATUS_LOADING = 1;

  public const STATUS_LOADED = 2;

  private $_status = self::STATUS_CREATED;

  private $_storage;

  private $_guid;

  /**
   * Create object and store plugin guid for later loading
   *
   * @param string $guid
   */
  public function __construct($guid) {
    parent::__construct([]);
    $this->_guid = Utility\Text\Guid::toLower($guid);
  }

  public function getGUID(): string {
    return $this->_guid;
  }

  /**
   * Set an value
   *
   * @param string $name
   * @param mixed $value
   */
  public function set($name, $value) {
    $this->lazyLoad();
    $name = Utility\Text\Identifier::toUnderscoreUpper($name);
    if (self::STATUS_LOADING === $this->_status) {
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
   * @param Filter $filter
   *
   * @return mixed
   */
  public function get($name, $default = NULL, Filter $filter = NULL) {
    $this->lazyLoad();
    return parent::get($name, $default, $filter);
  }

  /**
   * Validate if an option is available
   *
   * @param string $name
   *
   * @return bool
   */
  public function has($name) {
    $this->lazyLoad();
    return parent::has($name);
  }

  /**
   * IteratorAggregate Interface: return an iterator for the options.
   *
   * @return \Iterator
   */
  public function getIterator() {
    $this->lazyLoad();
    return new Configuration\Iterator(\array_keys($this->_options), $this);
  }

  /**
   * Make sure that the option are loaded if needed.
   */
  private function lazyLoad() {
    if (self::STATUS_CREATED === $this->_status) {
      $this->load();
    }
  }

  /**
   * Load options and change loading status
   *
   * @param Configuration\Storage $storage
   *
   * @return bool|void
   */
  public function load(Configuration\Storage $storage = NULL) {
    $this->_status = self::STATUS_LOADING;
    parent::load($storage);
    $this->_status = self::STATUS_LOADED;
  }

  /**
   * Returns the current loading status
   *
   * @return int
   */
  public function getStatus() {
    return $this->_status;
  }

  /**
   * Getter/Setter for the configuration storage
   *
   * @param Configuration\Storage $storage
   *
   * @return Configuration\Storage
   */
  public function storage(Configuration\Storage $storage = NULL) {
    if (NULL !== $storage) {
      $this->_storage = $storage;
    } elseif (NULL === $this->_storage) {
      $this->_storage = new Option\Storage($this->_guid);
      $this->_storage->papaya($this->papaya());
    }
    return parent::storage($this->_storage);
  }
}
