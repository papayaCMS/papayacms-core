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

use Papaya\Cache;

/**
 * Use the all values provided in the constructor as cache condition data
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Group
  implements Cache\Identifier\Definition {
  /**
   * @var Cache\Identifier\Definition[]
   */
  private $_definitions = [];

  /**
   * Just store all arguments into an private member variable
   *
   * @param Cache\Identifier\Definition ...$definitions
   */
  public function __construct(Cache\Identifier\Definition ...$definitions) {
    foreach ($definitions as $definition) {
      $this->add($definition);
    }
  }

  /**
   * Add a definition to the internal list
   *
   * @param Cache\Identifier\Definition $definition
   */
  public function add(Cache\Identifier\Definition $definition) {
    $this->_definitions[] = $definition;
  }

  /**
   * If here are stored values return them in an array element, the key of this element is the
   * class name.
   *
   * If no arguments whre stored, return TRUE.
   *
   * @see \Papaya\Cache\Identifier\Definition::getStatus()
   *
   * @return bool|array
   */
  public function getStatus() {
    $result = [];
    /** @var Cache\Identifier\Definition $definition */
    foreach ($this->_definitions as $definition) {
      $data = $definition->getStatus();
      if (FALSE === $data) {
        return FALSE;
      }
      if (TRUE === $data) {
        continue;
      }
      $result[] = $data;
    }
    return empty($result) ? TRUE : [\get_class($this) => $result];
  }

  /**
   * Compile a bitmask with all the source from the definitions.
   *
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   *
   * @return int
   */
  public function getSources() {
    $result = 0;
    /** @var Cache\Identifier\Definition $definition */
    foreach ($this->_definitions as $definition) {
      $result |= $definition->getSources();
    }
    return $result;
  }
}
