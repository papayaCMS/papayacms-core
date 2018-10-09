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
namespace Papaya\UI\Navigation;

use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

/**
 * An navigation builder class, creates a link navigation from an Transversable or array.
 *
 * Different callbacks allow to modify the navigation links.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Builder extends UI\Control {
  /**
   * member variable for the source
   *
   * @var array|\Traversable
   */
  private $_elements = [];

  /**
   * member variable for the links
   *
   * @var Items
   */
  private $_items;

  /**
   * member variable for the callbacks
   *
   * @var Builder\Callbacks
   */
  private $_callbacks;

  /**
   * member variable for the navigation item class while using default creation
   *
   * @var Builder\Callbacks
   */
  private $_itemClass;

  /**
   * Create object, store source elements and default item class
   *
   * @param array|\Traversable $elements
   * @param string $itemClass
   *
   * @throws \InvalidArgumentException
   */
  public function __construct($elements, $itemClass = Item\Text::class) {
    $this->elements($elements);
    if (!\is_subclass_of($itemClass, Item::class)) {
      throw new \InvalidArgumentException(
        \sprintf(
          'Class "%s" is not an subclass of "%s".',
          $itemClass,
          Item::class
        )
      );
    }
    $this->_itemClass = $itemClass;
  }

  /**
   * Create items for each source element and append them to the parent xml element.
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $this->items()->clear();
    $this->callbacks()->onBeforeAppend($this->items());
    foreach ($this->elements() as $index => $element) {
      if (isset($this->callbacks()->onCreateItem)) {
        $item = $this->callbacks()->onCreateItem($element, $index);
      } else {
        $itemClass = $this->_itemClass;
        $item = new $itemClass($element, $index);
      }
      if ($item instanceof Item) {
        $this->items()->add($item);
        $this->callbacks()->onAfterAppendItem($item, $element, $index);
      }
    }
    $parent->append($this->items());
    $this->callbacks()->onAfterAppend($this->items());
  }

  /**
   * Getter/Setter for the source elements
   *
   * @param array|\Traversable $elements
   *
   * @return array|\Traversable
   */
  public function elements($elements = NULL) {
    if (NULL !== $elements) {
      Utility\Constraints::assertArrayOrTraversable($elements);
      $this->_elements = $elements;
    }
    return $this->_elements;
  }

  /**
   * Getter/Setter for the navigation items
   *
   * @param Items $items
   *
   * @return Items
   */
  public function items(Items $items = NULL) {
    if (NULL !== $items) {
      $this->_items = $items;
    } elseif (NULL === $this->_items) {
      $this->_items = new Items();
      $this->_items->papaya($this->papaya());
    }
    return $this->_items;
  }

  /**
   * Getter/Setter for the callbacks
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
