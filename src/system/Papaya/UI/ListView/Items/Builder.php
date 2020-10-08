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
namespace Papaya\UI\ListView\Items;

use Papaya\UI;
use Papaya\Utility;

/**
 * Create listview items from an Traversable or Iterator
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Builder {
  private $_dataSource;

  private $_callbacks;

  /**
   * Create object and store the data source
   *
   * @param \Traversable|array $dataSource
   * @param callable|null $onCreateItem
   */
  public function __construct($dataSource, callable $onCreateItem = NULL) {
    Utility\Constraints::assertArrayOrTraversable($dataSource);
    $this->_dataSource = $dataSource;
    if (isset($onCreateItem)) {
      $this->callbacks()->onCreateItem = $onCreateItem;
    }
  }

  /**
   * Getter for the datasource member variable
   *
   * @return \Traversable|array
   */
  public function getDataSource() {
    return $this->_dataSource;
  }

  /**
   * Build the items
   *
   * @param UI\ListView\Items $items
   */
  public function fill(UI\ListView\Items $items) {
    if (!$this->callbacks()->onBeforeFill($items)) {
      $items->clear();
    }
    if (!isset($this->callbacks()->onCreateItem)) {
      $this->callbacks()->onCreateItem = function(
        /** @noinspection PhpUnusedParameterInspection */
        $context, UI\ListView\Items $items, $element
      ) {
        $items[] = new UI\ListView\Item('', (string)$element);
      };
    }
    foreach ($this->getDataSource() as $index => $element) {
      $this->callbacks()->onCreateItem($items, $element, $index);
    }
    $this->callbacks()->onAfterFill($items);
  }

  /**
   * Getter/Setter for the callbacks list.
   *
   *
   * @param Builder\Callbacks $callbacks
   *
   * @return Builder\Callbacks
   */
  public function callbacks(Builder\Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new Builder\Callbacks();
    }
    return $this->_callbacks;
  }
}
