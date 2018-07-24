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

namespace Papaya\Cache\Identifier\Definition;
use Papaya\Cache\Identifier\Definition;

/**
 * Use the all values provided in the constructor as cache condition data
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Group
  implements \Papaya\Cache\Identifier\Definition {

  /**
   * @var array(Papaya\Cache\Identifier\PapayaCacheIdentifierDefinition)
   */
  private $_definitions = array();

  /**
   * Just store all arguments into an private member variable
   *
   * @param \Papaya\Cache\Identifier\Definition,... $definition
   */
  public function __construct(\Papaya\Cache\Identifier\Definition $definition = NULL) {
    foreach (func_get_args() as $definition) {
      $this->add($definition);
    }
  }

  /**
   * Add a definition to the internal list
   *
   * @param \Papaya\Cache\Identifier\Definition $definition
   */
  public function add(\Papaya\Cache\Identifier\Definition $definition) {
    $this->_definitions[] = $definition;
  }

  /**
   * If here are stored values return them in an array element, the key of this element is the
   * class name.
   *
   * If no arguments whre stored, return TRUE.
   *
   * @see \Papaya\Cache\Identifier\Definition::getStatus()
   * @return boolean|array
   */
  public function getStatus() {
    $result = array();
    /** @var \Papaya\Cache\Identifier\Definition $definition */
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
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   * @return integer
   */
  public function getSources() {
    $result = 0;
    /** @var \Papaya\Cache\Identifier\Definition $definition */
    foreach ($this->_definitions as $definition) {
      $result |= $definition->getSources();
    }
    return $result;
  }
}
