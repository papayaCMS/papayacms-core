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
namespace Papaya\Database\Record;

use Papaya\Database;

/**
 * Papaya Database Record Lazy, superclass for easy database record encapsulation that
 * can store loading parameters until needed.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
abstract class Lazy
  extends Database\Record {
  /**
   * @var null|array
   */
  private $_loadingParameters;

  /**
   * Define lazy load parameters and activate it.
   *
   * @param array|int|string $filter
   */
  public function activateLazyLoad($filter) {
    $this->_loadingParameters = [$filter];
    $this->clear();
  }

  /**
   * Return the lazy loading parameters, NULL if here are none.
   *
   * @return array|null
   */
  public function getLazyLoadParameters() {
    return $this->_loadingParameters;
  }

  /**
   * If loading parameters are stored, use them to load the record.
   */
  protected function lazyLoad() {
    if (NULL !== $this->_loadingParameters) {
      $this->load(...$this->_loadingParameters);
      $this->_loadingParameters = NULL;
    }
  }

  /**
   * If a record are loaded, delete the stored loading parameters. So a direct call to load()
   * will reset them, too.
   *
   * @param string $sql
   * @param array|null $parameters
   *
   * @return bool
   */
  protected function _loadRecord($sql, array $parameters = NULL) {
    $this->_loadingParameters = NULL;
    return parent::_loadRecord($sql, $parameters);
  }

  /**
   * Allow to read the loading status
   *
   * @return bool
   */
  public function isLoaded() {
    $this->lazyLoad();
    return parent::isLoaded();
  }

  /**
   * Deactivate lazy loading if data is assigned
   *
   * @see \Papaya\BaseObject\Item::assign()
   *
   * @param array|\Traversable $data
   */
  public function assign($data) {
    $this->_loadingParameters = NULL;
    parent::assign($data);
  }

  /**
   * @param Database\Interfaces\Key|null $key
   *
   * @return Database\Interfaces\Key
   */
  public function key(Database\Interfaces\Key $key = NULL) {
    $key = parent::key($key);
    $this->lazyLoad();
    return $key;
  }

  /**
   * Get the values as an array
   *
   * @return array
   */
  public function toArray() {
    $this->lazyLoad();
    return parent::toArray();
  }

  /**
   * Validate if the defined value is set.
   *
   * @param string $name
   *
   * @return bool
   */
  public function __isset($name) {
    $this->lazyLoad();
    return parent::__isset($name);
  }

  /**
   * Return the defined value
   *
   * @throws \OutOfBoundsException
   *
   * @param string $name
   *
   * @return mixed
   */
  public function __get($name) {
    $this->lazyLoad();
    return parent::__get($name);
  }

  /**
   * Change a defined value
   *
   * @throws \OutOfBoundsException
   *
   * @param string $name
   * @param mixed $value
   */
  public function __set($name, $value) {
    $this->lazyLoad();
    parent::__set($name, $value);
  }

  /**
   * Set the deifned value to NULL.
   *
   * @throws \OutOfBoundsException
   *
   * @param string $name
   */
  public function __unset($name) {
    $this->lazyLoad();
    parent::__unset($name);
  }

  /**
   * ArrayAccess: Validate if a index/property exists at all
   *
   * @param string $name
   *
   * @return bool
   */
  public function offsetExists($name): bool {
    $this->lazyLoad();
    return parent::offsetExists($name);
  }
}
