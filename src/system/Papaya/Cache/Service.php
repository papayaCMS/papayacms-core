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

namespace Papaya\Cache;

use Papaya\Configuration;

/**
 * Abstract class for Papaya Cache Services
 *
 * @package Papaya-Library
 * @subpackage Cache
 */
abstract class Service {
  /**
   * Configuration object
   *
   * @var \Papaya\Configuration
   */
  protected $_configuration;

  /**
   * constructor
   *
   * @param \Papaya\Cache\Configuration|null $configuration
   */
  public function __construct(\Papaya\Cache\Configuration $configuration = NULL) {
    if (NULL !== $configuration) {
      $this->setConfiguration($configuration);
    }
  }

  /**
   * Set configuration
   *
   * @param \Papaya\Cache\Configuration $configuration
   */
  abstract public function setConfiguration(\Papaya\Cache\Configuration $configuration);

  /**
   * Verify that the cache has a valid configuration
   *
   * @param bool $silent
   */
  abstract public function verify($silent = TRUE);

  /**
   * Write element to cache
   *
   * @param string $group
   * @param string $element
   * @param string|array $parameters
   * @param string $data Element data
   * @param int $expires Maximum age in seconds
   * @return bool
   */
  abstract public function write($group, $element, $parameters, $data, $expires = NULL);

  /**
   * Read element from cache
   *
   * @param string $group
   * @param string $element
   * @param string|array $parameters
   * @param int $expires Maximum age in seconds
   * @param int $ifModifiedSince first possible creation time
   * @return string|false
   */
  abstract public function read($group, $element, $parameters, $expires, $ifModifiedSince = NULL);

  /**
   * Check if element in cache exists and is still valid
   *
   * @param string $group
   * @param string $element
   * @param string|array $parameters
   * @param int $expires Maximum age in seconds
   * @param int $ifModifiedSince first possible creation time
   * @return bool
   */
  abstract public function exists($group, $element, $parameters, $expires, $ifModifiedSince = NULL);

  /**
   * Check if element in cache exists and return creation time
   *
   * @param string $group
   * @param string $element
   * @param string|array $parameters
   * @param int $expires Maximum age in seconds
   * @param int $ifModifiedSince first possible creation time
   * @return int|false
   */
  abstract public function created($group, $element, $parameters, $expires, $ifModifiedSince = NULL);

  /**
   * Delete element(s) from cache
   *
   * @param string $group
   * @param string $element
   * @param string|array $parameters
   * @return int
   */
  abstract public function delete($group = NULL, $element = NULL, $parameters = NULL);

  /**
   * get the cache identifier string
   *
   * @param string $group
   * @param string $element
   * @param string|array $parameters
   * @throws \InvalidArgumentException
   * @return array
   */
  protected function _getCacheIdentification($group, $element, $parameters) {
    if (empty($group)) {
      throw new \InvalidArgumentException('Invalid cache group specified');
    }
    if (empty($element)) {
      throw new \InvalidArgumentException('Invalid cache element specified');
    }
    if (empty($parameters)) {
      throw new \InvalidArgumentException('Invalid cache parameters specified');
    }
    return [
      'group' => $this->_escapeIdentifierString($group),
      'element' => $this->_escapeIdentifierString($element),
      'parameters' => $this->_escapeIdentifierString(
        $this->_serializeParameters($parameters)
      )
    ];
  }

  /**
   * get the cache identifier string
   *
   * @param $group
   * @param $element
   * @param $parameters
   * @param int $maximumLength
   * @throws \InvalidArgumentException
   * @return string
   */
  public function getCacheIdentifier($group, $element, $parameters, $maximumLength = 255) {
    $identification = $this->_getCacheIdentification($group, $element, $parameters);
    $cacheId =
      $identification['group'].'/'.$identification['element'].'/'.$identification['parameters'];
    if (\strlen($cacheId) > $maximumLength) {
      throw new \InvalidArgumentException('Cache id string to large');
    }
    return $cacheId;
  }

  /**
   * escape identifier string using rawurlencode() if needed
   *
   * @param string $string
   * @return string
   */
  protected function _escapeIdentifierString($string) {
    if (\preg_match('(^[A-Za-z\d.-]+$)D', $string)) {
      return $string;
    }
    return \rawurlencode($string);
  }

  /**
   * serialize parameters to string
   *
   * @param mixed $parameters
   * @return string
   */
  protected function _serializeParameters($parameters) {
    return \is_array($parameters) || \is_object($parameters) ? \md5(\serialize($parameters)) : (string)$parameters;
  }
}
