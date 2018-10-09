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
namespace Papaya\UI\Hierarchy;

use Papaya\UI;
use Papaya\XML;

/**
 * A collection of items representation the hierarchy of the current data element.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property int $limit limit the create links
 * @property Item $spacer a spacer replacing the items not shown
 */
class Items extends UI\Control\Collection {
  /**
   * Superclass for validation, only items of this class may be added.
   *
   * @var string
   */
  protected $_itemClass = Item::class;

  /**
   * If a tag name is provided, an additional element will be added in
   * {@see \Papaya\UI\Control\Collection::appendTo()) that will wrapp the items.
   *
   * @var string
   */
  protected $_tagName = 'items';

  /**
   * Allow to limit the items append to the parent xml. Zero means all items are shown. Half
   * of the limit is shown from index 0 the other half from the end. If the limit is
   * odd the greater part is shown from the end.
   *
   * @var int
   */
  protected $_limit = 6;

  /**
   * Papaya\UI\Hierarchy\Item
   *
   * @var Item|null
   */
  protected $_spacer;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'limit' => ['_limit', '_limit'],
    'spacer' => ['_spacer', '_spacer']
  ];

  /**
   * Append item output to parent element. If a tag name was provided, the items will be wrapped
   * in an additional element.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element|null
   */
  public function appendTo(XML\Element $parent) {
    $count = \count($this->_items);
    if ($this->_limit > 0 && $count > $this->_limit) {
      $parent = $parent->appendElement($this->_tagName);
      $limitStart = \floor($this->_limit / 2);
      $limitEnd = $count - \ceil($this->_limit / 2);
      for ($i = 0; $i < $limitStart; $i++) {
        /** @var Item $item */
        $item = $this->_items[$i];
        $item->appendTo($parent);
      }
      $this->spacer()->appendTo($parent);
      for ($i = $limitEnd; $i < $count; $i++) {
        /** @var Item $item */
        $item = $this->_items[$i];
        $item->appendTo($parent);
      }
      return $parent;
    } else {
      return parent::appendTo($parent);
    }
  }

  /**
   * Getter/Setter for a spacer item that replaces the items not appended because of the limit.
   *
   * @param Item $spacer
   *
   * @return Item
   */
  public function spacer(Item $spacer = NULL) {
    if (NULL !== $spacer) {
      $this->_spacer = $spacer;
    } elseif (NULL === $this->_spacer) {
      $this->_spacer = new Item('...');
      $this->_spacer->papaya($this->papaya());
    }
    return $this->_spacer;
  }
}
