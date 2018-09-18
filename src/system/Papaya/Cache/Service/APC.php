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

/**
 * Papaya Cache Service for APC based cache
 *
 * @package Papaya-Library
 * @subpackage Cache
 */
class APC extends \Papaya\Cache\Service {
  /**
   * process cache - to avoid double requests
   *
   * @var array
   */
  protected $_localCache = [];

  /**
   * process cache - cache create times
   *
   * @var array
   */
  protected $_cacheCreated = [];

  /**
   * APC object
   *
   * @var APC\Wrapper
   */
  private $_apcObject;

  /**
   * The APC does not need any configuration, So just overwrite the abstract function with an empty
   * one.
   *
   * @param \Papaya\Cache\Configuration $configuration
   * @return bool
   */
  public function setConfiguration(\Papaya\Cache\Configuration $configuration) {
    return TRUE;
  }

  /**
   * check if APC is here
   *
   * @param bool $silent
   * @throws \LogicException
   * @return bool
   */
  public function verify($silent = TRUE) {
    $valid = $this->getAPCObject()->available();
    if (!($silent || $valid)) {
      throw new \LogicException('APC is not available');
    }
    return $valid;
  }

  /**
   * Get APC mapper object instance
   *
   * @return APC\Wrapper
   */
  public function getAPCObject() {
    if (NULL === $this->_apcObject) {
      $this->_apcObject = new APC\Wrapper();
    }
    return $this->_apcObject;
  }

  /**
   * Set APC mapper object instance
   *
   * @param APC\Wrapper $apcObject
   */
  public function setAPCObject(APC\Wrapper $apcObject) {
    $this->_apcObject = $apcObject;
  }

  /**
   * Write element to cache
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param string $data Element data
   * @param int $expires Maximum age in seconds
   * @return bool
   */
  public function write($group, $element, $parameters, $data, $expires = NULL) {
    if (
      $this->verify() &&
      ($cacheId = $this->getCacheIdentifier($group, $element, $parameters)) &&
      $this->getAPCObject()->store($cacheId, [\time(), $data], $expires)
    ) {
      return $cacheId;
    }
    return FALSE;
  }

  /**
   * Read element from cache
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param int $expires Maximum age in seconds
   * @param int $ifModifiedSince first possible creation time
   * @return string|false
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
   * @param int $expires Maximum age in seconds
   * @param int $ifModifiedSince first possible creation time
   * @return bool
   */
  public function exists($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->verify() && ($cacheId = $this->getCacheIdentifier($group, $element, $parameters))) {
      if (isset($this->_localCache[$cacheId])) {
        return !empty($this->_localCache[$cacheId]);
      }
      return (bool)$this->_read($cacheId, $expires, $ifModifiedSince);
    }
    return FALSE;
  }

  /**
   * Check if element in cache exists and which time is was created
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param int $expires Maximum age in seconds
   * @param int $ifModifiedSince first possible creation time
   * @return int|false
   */
  public function created($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->verify() && ($cacheId = $this->getCacheIdentifier($group, $element, $parameters))) {
      if (isset($this->_cacheCreated[$cacheId])) {
        return $this->_cacheCreated[$cacheId];
      }
      if ($this->_read($cacheId, $expires, $ifModifiedSince)) {
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
   * @return int
   */
  public function delete($group = NULL, $element = NULL, $parameters = NULL) {
    if ($this->verify()) {
      $this->getAPCObject()->clearCache('user');
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
   * @return bool
   */
  private function _read($cacheId, $expires, $ifModifiedSince) {
    $cache = $this->getAPCObject()->fetch($cacheId);
    if (\is_array($cache) && 2 === \count($cache)) {
      $created = (int)$cache[0];
      if (($created + $expires) > \time()) {
        if (NULL === $ifModifiedSince || $ifModifiedSince < $created) {
          $this->_cacheCreated[$cacheId] = $created;
          $this->_localCache[$cacheId] = $cache[1];
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}
