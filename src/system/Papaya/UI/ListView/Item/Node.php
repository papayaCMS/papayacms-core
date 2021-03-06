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
namespace Papaya\UI\ListView\Item;

use Papaya\UI;
use Papaya\XML;

/**
 * Provides a node marker for a listview item
 *
 * The marker can have 3 status: hidden, empty, open, close.
 *
 * "hidden" will disable the node output - even in the xml.
 * "empty" defines a node element without children and without a link
 * "open" the node is open, children are shown link closes the node
 * "closed" the node is closed, children are hidden link opens the node*
 *
 * The reference defines the link for the node
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property int $status
 * @property UI\Reference $reference
 * @property-read UI\ListView\Item $item
 */
class Node extends UI\Control {
  const NODE_HIDDEN = 0;

  const NODE_EMPTY = 1;

  const NODE_CLOSED = 2;

  const NODE_OPEN = 3;

  /**
   * @var array
   */
  private static $_statusStrings = [
    self::NODE_EMPTY => 'empty',
    self::NODE_CLOSED => 'closed',
    self::NODE_OPEN => 'open',
  ];

  /**
   * @var UI\ListView\Item
   */
  protected $_item;

  /**
   * @var UI\Reference
   */
  protected $_reference;

  /**
   * @var int
   */
  protected $_status = self::NODE_EMPTY;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'status' => ['_status', 'setStatus'],
    'reference' => ['reference', 'reference'],
    'item' => ['_item']
  ];

  /**
   * Store the owner item and set an status
   *
   * @param UI\ListView\Item $item
   * @param int $status
   */
  public function __construct(UI\ListView\Item $item, $status = self::NODE_HIDDEN) {
    $this->_item = $item;
    $this->setStatus($status);
  }

  /**
   * Append the listview item node marker to the parent xml element
   *
   * @param XML\Element $parent
   * @param XML\Element|null
   *
   * @return null|XML\Element
   */
  public function appendTo(XML\Element $parent) {
    if (self::NODE_HIDDEN !== $this->status) {
      $node = $parent->appendElement(
        'node',
        [
          'status' => self::$_statusStrings[$this->status]
        ]
      );
      if (self::NODE_EMPTY !== $this->status) {
        $node->setAttribute('href', (string)$this->reference());
      }
      return $node;
    }
    return NULL;
  }

  /**
   * Sett for the status
   *
   * @param int $status
   */
  public function setStatus($status) {
    if (isset(self::$_statusStrings[$status])) {
      $this->_status = (int)$status;
    } else {
      $this->_status = self::NODE_HIDDEN;
    }
  }

  /**
   * Getter/Setter for the node reference, if no reference is provided it is cloned
   * from the item.
   *
   * @param UI\Reference $reference
   *
   * @return UI\Reference
   */
  public function reference(UI\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = clone $this->item->reference();
    }
    return $this->_reference;
  }
}
