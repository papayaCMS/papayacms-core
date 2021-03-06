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
namespace Papaya\UI\Control\Collection;

use Papaya\UI;
use Papaya\Utility;

/**
 * A abstract superclass for collection items. This class provides access to the collection and
 * the position of the item in the collection.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Item extends UI\Control {
  /**
   * Position of item in the collection
   *
   * @var int
   */
  private $_index = 0;

  /**
   * Owner collection of the item
   *
   * @var UI\Control\Collection
   */
  private $_collection;

  /**
   * Return TRUE if the item is part of a collection.
   */
  public function hasCollection() {
    return NULL !== $this->_collection;
  }

  /**
   * Return the owner collection of the item.
   *
   * @throws \BadMethodCallException
   *
   * @param UI\Control\Collection $collection
   *
   * @return UI\Control\Collection
   */
  public function collection(UI\Control\Collection $collection = NULL) {
    if (NULL !== $collection) {
      $this->_collection = $collection;
      $this->papaya($collection->papaya());
    } elseif (NULL === $this->_collection) {
      throw new \BadMethodCallException(
        'BadMethodCallException: Item ist not part of a collection.'
      );
    }
    return $this->_collection;
  }

  /**
   * Getter/Setter for the index of the item in the collection.
   *
   * @param int|null $index
   *
   * @throws \UnexpectedValueException
   *
   * @return int
   */
  public function index($index = NULL) {
    if (
      NULL !== $index &&
      (int)$index !== $this->_index
    ) {
      Utility\Constraints::assertInteger($index);
      if ($this->collection()->get($index) === $this) {
        $this->_index = $index;
      } else {
        throw new \UnexpectedValueException(
          \sprintf(
            'UnexpectedValueException: Index "%d" does not match the collection item.',
            $index
          )
        );
      }
    }
    return $this->_index;
  }
}
