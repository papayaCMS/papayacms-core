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
 * Encapsulate data for an sql order by element
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Field
  implements \Papaya\Database\Interfaces\Order {
  /**
   * @var string
   */
  private $_field;

  /**
   * @var int
   */
  private $_direction;

  /**
   * @var array
   */
  private $_directions = [
    self::ASCENDING => 'ASC',
    self::DESCENDING => 'DESC'
  ];

  /**
   * Create object store field name and order by direction
   *
   * @param string $field
   * @param int $direction
   */
  public function __construct($field, $direction = self::ASCENDING) {
    \Papaya\Utility\Constraints::assertNotEmpty($field);
    $this->_field = (string)$field;
    $this->_direction = (int)$direction;
  }

  /**
   * Cast order by to string, concat field name and direction with a space
   *
   * @see \Papaya\Database\Interfaces\Order::__toString()
   */
  public function __toString() {
    return $this->_field.' '.$this->getDirectionString($this->_direction);
  }

  /**
   * Get sql identifier string for the order by direction (ASC or DESC)
   *
   * @param int $direction
   * @return string
   */
  protected function getDirectionString($direction) {
    $direction = isset($this->_directions[$direction]) ? $direction : self::ASCENDING;
    return $this->_directions[$direction];
  }
}
