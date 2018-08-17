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

namespace Papaya\Database\Record\Order;

/**
 * Group several order by definitions into one.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Group
  implements \Papaya\Database\Interfaces\Order, \IteratorAggregate {

  private $_lists;

  /**
   * Create iterator to store lists and attach all function arguments to it.
   */
  public function __construct() {
    $this->_lists = new \Papaya\Iterator\Union();
    foreach (func_get_args() as $list) {
      $this->add($list);
    }
  }

  /**
   * Attach an additional list to the group
   *
   * @param \Papaya\Database\Interfaces\Order $list
   */
  public function add(\Papaya\Database\Interfaces\Order $list) {
    $this->remove($list);
    /** @noinspection PhpParamsInspection */
    $this->_lists->attachIterator($list);
  }

  /**
   * Remove a list to the group
   *
   * @param \Papaya\Database\Interfaces\Order $list
   */
  public function remove(\Papaya\Database\Interfaces\Order $list) {
    /** @noinspection PhpParamsInspection */
    $this->_lists->detachIterator($list);
  }

  /**
   * Return the internal multiple iterator to allow to iterate over all items in all atached lists
   *
   * @see \IteratorAggregate::getIterator()
   * @return \Iterator
   */
  public function getIterator() {
    return $this->_lists;
  }

  /**
   * Casting the list to string generates the needed sql
   *
   * @see \Papaya\Database\Interfaces\Order::__toString()
   * @return string
   */
  public function __toString() {
    $result = '';
    foreach ($this as $item) {
      $result .= ', '.$item;
    }
    return (string)substr($result, 2);
  }
}
