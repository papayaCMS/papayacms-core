<?php
/**
* A boolean value or callback returing a boolean value defines if caching is allowed
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
* @version $Id: Callback.php 39416 2014-02-27 17:02:47Z weinert $
*/

/**
* A boolean value or callback returing a boolean value defines if caching is allowed
*
* @package Papaya-Library
* @subpackage Plugins
*/
class PapayaCacheIdentifierDefinitionCallback
  implements PapayaCacheIdentifierDefinition {

  private $_callback = NULL;
  private $_data = NULL;

  public function __construct($callback) {
    PapayaUtilConstraints::assertCallable($callback);
    $this->_callback = $callback;
  }

  /**
   * Return cache identification data from a callback, return FALSE if it is nor cacheable
   *
   * @see PapayaCacheIdentifierDefinition::getStatus()
   * @return array|FALSE
   */
  public function getStatus() {
    if (NULL === $this->_data) {
      if (!($this->_data = call_user_func($this->_callback))) {
        $this->_data = FALSE;
      }
    }
    return ($this->_data) ? array(get_class($this) => $this->_data) : FALSE;
  }

  /**
   * Values are from variables provided creating the object.
   *
   * @see PapayaCacheIdentifierDefinition::getSources()
   * @return integer
   */
  public function getSources() {
    return self::SOURCE_VARIABLES;
  }
}