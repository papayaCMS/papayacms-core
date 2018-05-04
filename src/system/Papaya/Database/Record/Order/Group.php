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
* Group several order by definitions into one.
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Group.php 39408 2014-02-27 16:00:49Z weinert $
*/
class PapayaDatabaseRecordOrderGroup
  implements PapayaDatabaseInterfaceOrder, IteratorAggregate {

  private $_lists = NULL;

  /**
   * Create iterator to store lists and attach all function arguments to it.
   */
  public function __construct() {
    $this->_lists = new \PapayaIteratorMultiple();
    foreach (func_get_args() as $list) {
      $this->add($list);
    }
  }

  /**
   * Attach an additional list to the group
   *
   * @param \PapayaDatabaseInterfaceOrder $list
   */
  public function add(\PapayaDatabaseInterfaceOrder $list) {
    $this->remove($list);
    /** @noinspection PhpParamsInspection */
    $this->_lists->attachIterator($list);
  }

  /**
   * Remove a list to the group
   *
   * @param \PapayaDatabaseInterfaceOrder $list
   */
  public function remove(\PapayaDatabaseInterfaceOrder $list) {
    /** @noinspection PhpParamsInspection */
    $this->_lists->detachIterator($list);
  }

  /**
   * Return the internal multiple iterator to alllow to iterate over all items in all atached lists
   * @see \IteratorAggregate::getIterator()
   * @return \Iterator
   */
  public function getIterator() {
    return $this->_lists;
  }

  /**
   * Casting the list to string generates the needed sql
   *
   * @see \PapayaDatabaseInterfaceOrder::__toString()
   * @return string
   */
  public function __toString() {
    $result = '';
    foreach ($this as $item) {
      $result .= ', '.(string)$item;
    }
    return substr($result, 2);
  }
}
