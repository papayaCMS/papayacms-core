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
namespace Papaya\UI\Icon;

/**
 * A list of ui icons, generic handling to provide encapsulation.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Collection implements \ArrayAccess, \Countable, \IteratorAggregate {
  /**
   * The internal icon objects array
   *
   * @var array
   */
  private $_icons = [];

  /**
   * ArrayAccess Interface: check if an icon is available
   *
   * @param string $offset
   *
   * @return bool
   */
  public function offsetExists($offset): bool {
    return isset($this->_icons[$offset]);
  }

  /**
   * ArrayAccess Interface: return an icon from the internal list
   *
   * @param string $offset
   *
   * @return \Papaya\UI\Icon
   */
  #[\ReturnTypeWillChange]
  public function offsetGet($offset) {
    return $this->_icons[$offset];
  }

  /**
   * ArrayAccess Interface: add an icon to the list, replaces existing icon with the specified
   * offset if it is there.
   *
   * @param string $offset
   * @param \Papaya\UI\Icon $icon
   *
   * @throws \InvalidArgumentException
   */
  public function offsetSet($offset, $icon): void {
    if (NULL === $offset) {
      throw new \InvalidArgumentException(
        'InvalidArgumentException: Please provide a valid offset for the icon.'
      );
    }
    if ($icon instanceof \Papaya\UI\Icon) {
      $this->_icons[$offset] = $icon;
    } else {
      throw new \InvalidArgumentException(
        'InvalidArgumentException: Please provide an instance of Papaya\UI\Icon.'
      );
    }
  }

  /**
   * ArrayAccess Interface: remove an icon from the internal list
   *
   * @param string $offset
   */
  public function offsetUnset($offset): void {
    unset($this->_icons[$offset]);
  }

  /**
   * Countable Interface: return the icon count
   *
   * @return int
   */
  public function count(): int {
    return \count($this->_icons);
  }

  /**
   * IteratorAggregate Interface: get an iterator for the icons
   *
   * @return \ArrayIterator
   */
  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->_icons);
  }
}
