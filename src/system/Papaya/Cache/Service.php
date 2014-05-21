<?php
/**
* Abstract class for Papaya Cache Services
*
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya:Library
* @subpackage Cache
* @version $Id: Service.php 39403 2014-02-27 14:25:16Z weinert $
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
  */
  public function __construct($configuration = NULL) {
    if (isset($configuration)) {
      $this->setConfiguration($configuration);
    }
  }

  /**
  * Set configuration
  *
  * @param PapayaCacheConfiguration $configuration
  * @return void
  */
  abstract function setConfiguration(PapayaCacheConfiguration $configuration);

  /**
  * Verify that the cache has a valid configuration
  * @param boolean $silent
  */
  abstract function verify($silent = TRUE);

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
  abstract function write($group, $element, $parameters, $data, $expires = NULL);

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
  abstract function read($group, $element, $parameters, $expires, $ifModifiedSince = NULL);

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
  abstract function exists($group, $element, $parameters, $expires, $ifModifiedSince = NULL);

  /**
  * Check if element in cache exists and return creation time
  *
  * @param string $group
  * @param string $element
  * @param string $parameters
  * @param integer $expires Maximum age in seconds
  * @param integer $ifModifiedSince first possible creation time
  * @return integer|FALSE
  */
  abstract function created($group, $element, $parameters, $expires, $ifModifiedSince = NULL);

  /**
  * Delete element(s) from cache
  *
  * @param string $group
  * @param string $element
  * @param string $parameters
  * @return integer
  */
  abstract function delete($group = NULL, $element = NULL, $parameters = NULL);

  /**
   * get the cache identifier string
   *
   * @param $group
   * @param $element
   * @param $parameters
   * @throws InvalidArgumentException
   * @return string
   */
  protected function _getCacheIdentification($group, $element, $parameters) {
    if (empty($group)) {
      throw new InvalidArgumentException('Invalid cache group specified');
    }
    if (empty($element)) {
      throw new InvalidArgumentException('Invalid cache element specified');
    }
    if (empty($parameters)) {
      throw new InvalidArgumentException('Invalid cache parameters specified');
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
   * @throws InvalidArgumentException
   * @return string
   */
  public function getCacheIdentifier($group, $element, $parameters, $maximumLength = 255) {
    $identification = $this->_getCacheIdentification($group, $element, $parameters);
    $cacheId =
      $identification['group'].'/'.$identification['element'].'/'.$identification['parameters'];
    if (strlen($cacheId) > $maximumLength) {
      throw new InvalidArgumentException('Cache id string to large');
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
    } else {
      return rawurlencode($string);
    }
  }

  /**
   * serialize parameters to string
   * @param mixed $parameters
   * @return string
   */
  protected function _serializeParameters($parameters) {
    if (is_array($parameters) || is_object($parameters)) {
      return md5(serialize($parameters));
    } else {
      return (string)$parameters;
    }
  }
}
