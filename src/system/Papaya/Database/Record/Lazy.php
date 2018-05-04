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
* Papaya Database Record Lazy, superclass for easy database record encapsulation that
* can store loading parameters until needed.
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Lazy.php 38917 2013-11-11 14:31:11Z weinert $
*/
abstract class PapayaDatabaseRecordLazy
  extends PapayaDatabaseRecord {

  private $_loadingParameters = NULL;

  /**
  * Define lazy load parameters and activate it.
  *
  * @param mixed $filter
  */
  public function activateLazyLoad($filter) {
    $this->_loadingParameters = func_get_args();
    $this->clear();
  }

  /**
   * Return the lazy loading parameters, NULL if here are none.
   *
   * @return array|NULL
   */
  public function getLazyLoadParameters() {
    return $this->_loadingParameters;
  }

  /**
  * If loading parameters are stored, use them to load the record.
  */
  protected function lazyLoad() {
    if (isset($this->_loadingParameters)) {
      call_user_func_array(array($this, 'load'), $this->_loadingParameters);
      $this->_loadingParameters = NULL;
    }
  }

  /**
  * If a record are loaded, delete the stored loading parameters. So a direct call to load()
  * will reset them, too.
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
   * @see \PapayaObjectItem::assign()
   * @param array|\Traversable $data
   */
  public function assign($data) {
    $this->_loadingParameters = NULL;
    parent::assign($data);
  }

  /**
   * @param \PapayaDatabaseInterfaceKey|NULL $key
   * @return \PapayaDatabaseInterfaceKey
   */
  public function key(\PapayaDatabaseInterfaceKey $key = NULL) {
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
  * @return boolean
  */
  public function __isset($name) {
    $this->lazyLoad();
    return parent::__isset($name);
  }

  /**
  * Return the defined value
  *
  * @throws \OutOfBoundsException
  * @param string $name
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
  * @return boolean
  */
  public function offsetExists($name) {
    $this->lazyLoad();
    return parent::offsetExists($name);
  }
}
