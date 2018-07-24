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

namespace Papaya\Cache\Service;

use Papaya\Cache\Service\Apc\Wrapper;

/**
 * Papaya Cache Service for APC based cache
 *
 * @package Papaya-Library
 * @subpackage Cache
 */
class Apc extends \Papaya\Cache\Service {

  /**
   * process cache - to avoid double requests
   *
   * @var array
   */
  protected $_localCache = array();

  /**
   * process cache - cache create times
   *
   * @var array
   */
  protected $_cacheCreated = array();

  /**
   * APC object
   *
   * @var Wrapper
   */
  private $_apcObject;

  /**
   * The APC does not need any configuration, So just overwrite the abstract function with an empty
   * one.
   *
   * @param \Papaya\Cache\Configuration $configuration
   * @return boolean
   */
  public function setConfiguration(\Papaya\Cache\Configuration $configuration) {
    return TRUE;
  }

  /**
   * check if APC is here
   *
   * @param boolean $silent
   * @throws \LogicException
   * @return boolean
   */
  public function verify($silent = TRUE) {
    $valid = $this->getApcObject()->available();
    if (!($silent || $valid)) {
      throw new \LogicException('APC is not available');
    }
    return $valid;
  }

  /**
   * Get APC mapper object instance
   *
   * @return \Papaya\Cache\Service\Apc\Wrapper
   */
  public function getApcObject() {
    if (!isset($this->_apcObject)) {
      $this->_apcObject = new \Papaya\Cache\Service\Apc\Wrapper();
    }
    return $this->_apcObject;
  }

  /**
   * Set APC mapper object instance
   *
   * @param \Papaya\Cache\Service\Apc\Wrapper $apcObject
   */
  public function setApcObject(\Papaya\Cache\Service\Apc\Wrapper $apcObject) {
    $this->_apcObject = $apcObject;
  }

  /**
   * Write element to cache
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param string $data Element data
   * @param integer $expires Maximum age in seconds
   * @return boolean
   */
  public function write($group, $element, $parameters, $data, $expires = NULL) {
    if ($this->verify() && ($cacheId = $this->getCacheIdentifier($group, $element, $parameters))) {
      if ($this->getApcObject()->store($cacheId, array(time(), $data), $expires)) {
        return $cacheId;
      }
    }
    return FALSE;
  }


  /**
   * Read element from cache
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param integer $expires Maximum age in seconds
   * @param integer $ifModifiedSince first possible creation time
   * @return string|FALSE
   */
  public function read($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->verify() && ($cacheId = $this->getCacheIdentifier($group, $element, $parameters))) {
      if (isset($this->_localCache[$cacheId]) ||
        $this->_read($cacheId, $expires, $ifModifiedSince)) {
        return $this->_localCache[$cacheId];
      }
    }
    return FALSE;
  }

  /**
   * Check if element in cache exists and is still valid
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param integer $expires Maximum age in seconds
   * @param integer $ifModifiedSince first possible creation time
   * @return boolean
   */
  public function exists($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->verify() && ($cacheId = $this->getCacheIdentifier($group, $element, $parameters))) {
      if (isset($this->_localCache[$cacheId])) {
        return !empty($this->_localCache[$cacheId]);
      } else {
        return (boolean)$this->_read($cacheId, $expires, $ifModifiedSince);
      }
    }
    return FALSE;
  }

  /**
   * Check if element in cache exists and which time is was created
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param integer $expires Maximum age in seconds
   * @param integer $ifModifiedSince first possible creation time
   * @return integer|FALSE
   */
  public function created($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->verify() && ($cacheId = $this->getCacheIdentifier($group, $element, $parameters))) {
      if (isset($this->_cacheCreated[$cacheId])) {
        return $this->_cacheCreated[$cacheId];
      } elseif ($this->_read($cacheId, $expires, $ifModifiedSince)) {
        return $this->_cacheCreated[$cacheId];
      }
    }
    return FALSE;
  }

  /**
   * Delete element(s) from cache, apc supports no groups - so delete all
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @return integer
   */
  public function delete($group = NULL, $element = NULL, $parameters = NULL) {
    if ($this->verify()) {
      $this->getApcObject()->clearCache('user');
      return TRUE;
    }
    return 0;
  }

  /**
   * internal read item from cache - cache items are arrays with two element,
   * first element contains cache time
   *
   * @param $cacheId
   * @param $expires
   * @param $ifModifiedSince
   * @return boolean
   */
  private function _read($cacheId, $expires, $ifModifiedSince) {
    $cache = $this->getApcObject()->fetch($cacheId);
    if (is_array($cache) && count($cache) == 2) {
      $created = (int)$cache[0];
      if (($created + $expires) > time()) {
        if (is_null($ifModifiedSince) || $ifModifiedSince < $created) {
          $this->_cacheCreated[$cacheId] = $created;
          $this->_localCache[$cacheId] = $cache[1];
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}
