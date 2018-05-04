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
* Define an order by using field names
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Fields.php 39730 2014-04-07 21:05:30Z weinert $
*/
class PapayaDatabaseRecordOrderByFields
  implements PapayaDatabaseInterfaceOrder, IteratorAggregate {

  /**
   * @var PapayaDatabaseRecordOrderList
   */
  private $_list = NULL;

  /**
   * @param array $fields
   */
  public function __construct(array $fields) {
    $this->_list = new \PapayaDatabaseRecordOrderList();
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
      $this->_list[] = new \PapayaDatabaseRecordOrderField($field, $direction);
    }
  }

  /**
   * Cast object into a SQL string
   * @see \PapayaDatabaseInterfaceOrder::__toString()
   */
  public function __toString() {
    return (string)$this->_list;
  }

  /**
   * @return \Iterator
   */
  public function getIterator() {
    return new \PapayaIteratorTraversable($this->_list);
  }
}
