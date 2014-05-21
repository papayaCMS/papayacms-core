<?php
/**
* Use the all values provided in the constructor as cache condition data
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
* @version $Id: Group.php 39429 2014-02-27 20:14:26Z weinert $
*/

/**
* Use the all values provided in the constructor as cache condition data
*
* @package Papaya-Library
* @subpackage Plugins
*/
class PapayaCacheIdentifierDefinitionGroup
  implements PapayaCacheIdentifierDefinition {

  /**
   * @var array(PapayaCacheIdentifierDefinition)
   */
  private $_definitions = array();

  /**
   * Just store all arguments into an private member variable
   *
   * @param PapayaCacheIdentifierDefinition,... $definition
   */
  public function __construct(PapayaCacheIdentifierDefinition $definition = NULL) {
    foreach (func_get_args() as $definition) {
      $this->add($definition);
    }
  }

  /**
   * Add a definition to the internal list
   *
   * @param PapayaCacheIdentifierDefinition $definition
   */
  public function add(PapayaCacheIdentifierDefinition $definition) {
    $this->_definitions[] = $definition;
  }

  /**
   * If here are stored values return them in an array element, the key of this element is the
   * class name.
   *
   * If no arguments whre stored, return TRUE.
   *
   * @see PapayaCacheIdentifierDefinition::getStatus()
   * @return boolean|array
   */
  public function getStatus() {
    $result = array();
    /** @var PapayaCacheIdentifierDefinition $definition */
    foreach ($this->_definitions as $definition) {
      $data = $definition->getStatus();
      if (FALSE === $data) {
        return FALSE;
      } elseif (TRUE === $data) {
        continue;
      }
      $result[] = $data;
    }
    return empty($result) ? TRUE : array(get_class($this) => $result);
  }

  /**
   * Compile a bitmask with all the source from the definitions.
   *
   * @see PapayaCacheIdentifierDefinition::getSources()
   * @return integer
   */
  public function getSources() {
    $result = 0;
    /** @var PapayaCacheIdentifierDefinition $definition */
    foreach ($this->_definitions as $definition) {
      $result |= $definition->getSources();
    }
    return $result;
  }
}