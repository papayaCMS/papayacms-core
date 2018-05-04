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
* Abstract class for Papaya Cache Services
*
* @package Papaya-Library
* @subpackage Cache
*/
abstract class PapayaCacheService {

  /**
  * Configuration object
  * @var PapayaConfiguration
  */
  protected $_configuration;

  /**
   * constructor
   *
   * @param \PapayaCacheConfiguration|NULL $configuration
   */
  public function __construct(\PapayaCacheConfiguration $configuration = NULL) {
    if (NULL !== $configuration) {
      $this->setConfiguration($configuration);
    }
  }

  /**
  * Set configuration
  *
  * @param \PapayaCacheConfiguration $configuration
  * @return void
  */
  abstract public function setConfiguration(\PapayaCacheConfiguration $configuration);

  /**
  * Verify that the cache has a valid configuration
  * @param boolean $silent
  */
  abstract public function verify($silent = TRUE);

  /**
  * Write element to cache
  *
  * @param string $group
  * @param string $element
  * @param string|array $parameters
  * @param string $data Element data
  * @param integer $expires Maximum age in seconds
  * @return boolean
  */
  abstract public function write($group, $element, $parameters, $data, $expires = NULL);

  /**
  * Read element from cache
  *
  * @param string $group
  * @param string $element
  * @param string|array $parameters
  * @param integer $expires Maximum age in seconds
  * @param integer $ifModifiedSince first possible creation time
  * @return string|FALSE
  */
  abstract public function read($group, $element, $parameters, $expires, $ifModifiedSince = NULL);

  /**
  * Check if element in cache exists and is still valid
  *
  * @param string $group
  * @param string $element
  * @param string|array $parameters
  * @param integer $expires Maximum age in seconds
  * @param integer $ifModifiedSince first possible creation time
  * @return boolean
  */
  abstract public function exists($group, $element, $parameters, $expires, $ifModifiedSince = NULL);

  /**
  * Check if element in cache exists and return creation time
  *
  * @param string $group
  * @param string $element
  * @param string|array $parameters
  * @param integer $expires Maximum age in seconds
  * @param integer $ifModifiedSince first possible creation time
  * @return integer|FALSE
  */
  abstract public function created($group, $element, $parameters, $expires, $ifModifiedSince = NULL);

  /**
  * Delete element(s) from cache
  *
  * @param string $group
  * @param string $element
  * @param string|array $parameters
  * @return integer
  */
  abstract public function delete($group = NULL, $element = NULL, $parameters = NULL);

  /**
   * get the cache identifier string
   *
   * @param string $group
   * @param string $element
   * @param string|array$parameters
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
    return array(
      'group' => $this->_escapeIdentifierString($group),
      'element' => $this->_escapeIdentifierString($element),
      'parameters' => $this->_escapeIdentifierString(
        $this->_serializeParameters($parameters)
      )
    );
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
    if (strlen($cacheId) > $maximumLength) {
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
    if (preg_match('(^[A-Za-z\d.-]+$)D', $string)) {
      return $string;
    }
    return rawurlencode($string);
  }

  /**
   * serialize parameters to string
   * @param mixed $parameters
   * @return string
   */
  protected function _serializeParameters($parameters) {
    return is_array($parameters) || is_object($parameters) ? md5(serialize($parameters)) : (string)$parameters;
  }
}
