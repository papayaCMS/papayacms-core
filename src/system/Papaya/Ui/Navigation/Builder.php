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
* An navigation builder class, creates a link navigation from an Transversable or array.
*
* Different callbacks allow to modify the navigation links.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiNavigationBuilder extends \PapayaUiControl {

  /**
  * member vaiable for the source
  * @var array|Traversable
  */
  private $_elements = array();
  /**
  * member vaiable for the links
  * @var PapayaUiNavigationItems
  */
  private $_items = NULL;
  /**
  * member vaiable for the callbacks
  * @var PapayaUiNavigationBuilderCallbacks
  */
  private $_callbacks = NULL;
  /**
  * member vaiable for the navigation item class while using defalt creation
  * @var PapayaUiNavigationBuilderCallbacks
  */
  private $_itemClass = '';

  /**
   * Create object, store source elements and default item class
   *
   * @param array|\Traversable $elements
   * @param string $itemClass
   * @throws \InvalidArgumentException
   */
  public function __construct($elements, $itemClass = \PapayaUiNavigationItemText::class) {
    $this->elements($elements);
    if (!is_subclass_of($itemClass, \PapayaUiNavigationItem::class)) {
      throw new \InvalidArgumentException(
        sprintf(
          'Class "%s" is not an subclass of "%s".',
          $itemClass,
          \PapayaUiNavigationItem::class
        )
      );
    }
    $this->_itemClass = $itemClass;
  }

  /**
  * Create items for each source element and append them to the parent xml element.
  *
  * @param \PapayaXmlElement $parent
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $this->items()->clear();
    $this->callbacks()->onBeforeAppend($this->items());
    foreach ($this->elements() as $index => $element) {
      if (isset($this->callbacks()->onCreateItem)) {
        $item = $this->callbacks()->onCreateItem($element, $index);
      } else {
        $itemClass = $this->_itemClass;
        $item = new $itemClass($element, $index);
      }
      if ($item instanceof \PapayaUiNavigationItem) {
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
  * @return array|\Traversable
  */
  public function elements($elements = NULL) {
    if (isset($elements)) {
      \PapayaUtilConstraints::assertArrayOrTraversable($elements);
      $this->_elements = $elements;
    }
    return $this->_elements;
  }

  /**
  * Getter/Setter for the navigation items
  *
  * @param \PapayaUiNavigationItems $items
  * @return \PapayaUiNavigationItems
  */
  public function items(\PapayaUiNavigationItems $items = NULL) {
    if (isset($items)) {
      $this->_items = $items;
    } elseif (is_null($this->_items)) {
      $this->_items = new \PapayaUiNavigationItems();
      $this->_items->papaya($this->papaya());
    }
    return $this->_items;
  }

  /**
  * Getter/Setter for the callbacks
  *
  * @param \PapayaUiNavigationBuilderCallbacks $callbacks
  * @return \PapayaUiNavigationBuilderCallbacks
  */
  public function callbacks(\PapayaUiNavigationBuilderCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    } elseif (is_null($this->_callbacks)) {
      $this->_callbacks = new \PapayaUiNavigationBuilderCallbacks();
    }
    return $this->_callbacks;
  }
}
