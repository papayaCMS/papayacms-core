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
 * An navigation item for a list of navigation items.
 *
 * Any navigation item needs at least a reference - so the abstract class provides this.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Item extends UI\Control\Collection\Item {
  /**
   * @var UI\Reference
   */
  private $_reference;

  /**
   * @var bool
   */
  private $_selected = FALSE;

  /**
   * @var mixed
   */
  protected $_sourceValue;

  /**
   * @var string|int|null
   */
  protected $_sourceIndex;

  /**
   * Create object, store the source the navigation element is for and its index in a list if
   * available.
   *
   * @param mixed $sourceValue
   * @param string|int|null $sourceIndex
   */
  public function __construct($sourceValue, $sourceIndex = NULL) {
    $this->_sourceValue = $sourceValue;
    $this->_sourceIndex = $sourceIndex;
  }

  /**
   * Append a item to the xml and return it for further modifications in child classes.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $link = $parent->appendElement(
      'link',
      [
        'href' => $this->reference()->getRelative()
      ]
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
   * @param bool|null $selected
   *
   * @return bool
   */
  public function selected($selected = NULL) {
    if (NULL !== $selected) {
      Utility\Constraints::assertBoolean($selected);
      $this->_selected = $selected;
    }
    return $this->_selected;
  }

  /**
   * Getter/Setter for a reference subobject to create detail page links
   *
   * @param UI\Reference $reference
   *
   * @return UI\Reference
   */
  public function reference(UI\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      if ($this->hasCollection()) {
        /** @var \Papaya\UI\Navigation\Items $collection */
        $collection = $this->collection();
        $this->_reference = clone $collection->reference();
      } else {
        $this->_reference = new UI\Reference\Page();
        $this->_reference->papaya($this->papaya());
      }
    }
    return $this->_reference;
  }
}
