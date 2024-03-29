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

use Papaya\Database;

/**
 * Define an order by using property names an a property-field-mapping
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Properties
  implements Database\Interfaces\Order, \IteratorAggregate {
  /**
   * @var Database\Record\Order\Collection
   */
  private $_list;

  /**
   * @var Database\Interfaces\Mapping
   */
  private $_mapping;

  /**
   * Create object, store mapping object and set order by properties
   *
   * @param array $properties
   * @param Database\Interfaces\Mapping $mapping
   */
  public function __construct(array $properties, Database\Interfaces\Mapping $mapping) {
    $this->_list = new Database\Record\Order\Collection();
    $this->_mapping = $mapping;
    $this->setProperties($properties);
  }

  /**
   * Map properties to fields and set up internal list
   *
   * @param array $properties
   */
  public function setProperties(array $properties) {
    $this->_list->clear();
    foreach ($properties as $property => $direction) {
      if ($field = $this->_mapping->getField($property)) {
        $this->_list[] = new Database\Record\Order\Field($field, $direction);
      }
    }
  }

  /**
   * @see \Papaya\Database\Interfaces\Order::__toString()
   *
   * @return string
   */
  public function __toString() {
    return (string)$this->_list;
  }

  /**
   * @return \Iterator
   */
  public function getIterator(): \Traversable {
    return new \Papaya\Iterator\TraversableIterator($this->_list);
  }
}
