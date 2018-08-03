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

namespace Papaya\Ui\Navigation;
/**
 * An navigation item for a list of navigation items.
 *
 * Any navigation item needs at least a reference - so the abstract class provides this.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
abstract class Item extends \Papaya\Ui\Control\Collection\Item {

  private $_reference = NULL;
  private $_selected = FALSE;

  protected $_sourceValue = NULL;
  protected $_sourceIndex = NULL;

  /**
   * Create object, store the source the navigation element is for and its index in a list if
   * available.
   *
   * @param mixed $sourceValue
   * @param mixed $sourceIndex
   */
  public function __construct($sourceValue, $sourceIndex = NULL) {
    $this->_sourceValue = $sourceValue;
    $this->_sourceIndex = $sourceIndex;
  }

  /**
   * Append a item to the xml and return it for further modifications in child classes.
   *
   * @param \Papaya\Xml\Element $parent
   * @return \Papaya\Xml\Element
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $link = $parent->appendElement(
      'link',
      array(
        'href' => $this->reference()->getRelative()
      )
    );
    if ($this->_selected) {
      $link->setAttribute('selected', 'selected');
    }
    return $link;
  }

  /**
   * Getter/Setter for the selected status. If it is set to true, an boolean attribute will be added
   * to the xml element
   *
   * @param boolean|NULL $selected
   * @return boolean
   */
  public function selected($selected = NULL) {
    if (isset($selected)) {
      \Papaya\Utility\Constraints::assertBoolean($selected);
      $this->_selected = $selected;
    }
    return $this->_selected;
  }

  /**
   * Getter/Setter for a reference subobject to create detail page links
   *
   * @param \Papaya\Ui\Reference $reference
   * @return \Papaya\Ui\Reference
   */
  public function reference(\Papaya\Ui\Reference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      if ($this->hasCollection()) {
        /** @var \Papaya\Ui\Navigation\Items $collection */
        $collection = $this->collection();
        $this->_reference = clone $collection->reference();
      } else {
        $this->_reference = new \Papaya\Ui\Reference\Page();
        $this->_reference->papaya($this->papaya());
      }
    }
    return $this->_reference;
  }
}
