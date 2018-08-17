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

namespace Papaya\Database\Record\Order\By;

/**
 * Define an order by using field names
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Fields
  implements \Papaya\Database\Interfaces\Order, \IteratorAggregate {

  /**
   * @var \Papaya\Database\Record\Order\Collection
   */
  private $_list;

  /**
   * @param array $fields
   */
  public function __construct(array $fields) {
    $this->_list = new \Papaya\Database\Record\Order\Collection();
    $this->setFields($fields);
  }

  /**
   * Clear internal list and set fields from and field => direction array
   *
   * @param array $fields
   */
  public function setFields(array $fields) {
    $this->_list->clear();
    foreach ($fields as $field => $direction) {
      $this->_list[] = new \Papaya\Database\Record\Order\Field($field, $direction);
    }
  }

  /**
   * Cast object into a SQL string
   *
   * @see \Papaya\Database\Interfaces\Order::__toString()
   */
  public function __toString() {
    return (string)$this->_list;
  }

  /**
   * @return \Iterator
   */
  public function getIterator() {
    return new \Papaya\Iterator\TraversableIterator($this->_list);
  }
}
