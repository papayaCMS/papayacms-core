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
* A list of ui icons, generic handling to provide encapsulation.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiIconList implements \ArrayAccess, \Countable, \IteratorAggregate {

  /**
  * The internal icon objects array
  *
  * @var array
  */
  private $_icons = array();

  /**
  * ArrayAccess Interface: check if an icon is availiable
  *
  * @param string $offset
  * @return boolean
  */
  public function offsetExists($offset) {
    return isset($this->_icons[$offset]);
  }

  /**
  * ArrayAccess Interface: return an icon from the internal list
  *
  * @param string $offset
  * @return \PapayaUiIcon
  */
  public function offsetGet($offset) {
    return $this->_icons[$offset];
  }

  /**
   * ArrayAccess Interface: add an icon to the list, replaces existing icon with the specified
   * offset if it is there.
   *
   * @param string $offset
   * @param \PapayaUiIcon $icon
   * @throws \InvalidArgumentException
   */
  public function offsetSet($offset, $icon) {
    if (is_null($offset)) {
      throw new \InvalidArgumentException(
        'InvalidArgumentException: Please provide a valid offset for the icon.'
      );
    }
    if ($icon instanceof \PapayaUiIcon) {
      $this->_icons[$offset] = $icon;
    } else {
      throw new \InvalidArgumentException(
        'InvalidArgumentException: Please provide an instance of PapayaUiIcon.'
      );
    }
  }

  /**
  * ArrayAccess Interface: remove an icon from the internal list
  *
  * @param string $offset
  * @return \PapayaUiIcon
  */
  public function offsetUnset($offset) {
    unset($this->_icons[$offset]);
  }

  /**
  * Countable Interface: return the icon count
  *
  * @return integer
  */
  public function count() {
    return count($this->_icons);
  }

  /**
  * IteratorAggregate Interface: get an iterator for the icons
  *
  * @return \ArrayIterator
  */
  public function getIterator() {
    return new \ArrayIterator($this->_icons);
  }
}
