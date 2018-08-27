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
 * @property integer $status
 * @property \Papaya\UI\Reference $reference
 * @property-read \Papaya\UI\ListView\Item $item
 */
class Node extends \Papaya\UI\Control {

  const NODE_HIDDEN = 0;
  const NODE_EMPTY = 1;
  const NODE_CLOSED = 2;
  const NODE_OPEN = 3;

  private $_statusStrings = array(
    self::NODE_EMPTY => 'empty',
    self::NODE_CLOSED => 'closed',
    self::NODE_OPEN => 'open',
  );

  /**
   * @var \Papaya\UI\ListView\Item
   */
  protected $_item = NULL;

  /**
   * @var \Papaya\UI\Reference
   */
  protected $_reference = NULL;

  /**
   * @var integer
   */
  protected $_status = self::NODE_EMPTY;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'status' => array('_status', 'setStatus'),
    'reference' => array('reference', 'reference'),
    'item' => array('_item')
  );

  /**
   * Store the owner item and set an status
   *
   * @param \Papaya\UI\ListView\Item $item
   * @param integer $status
   */
  public function __construct(\Papaya\UI\ListView\Item $item, $status = self::NODE_HIDDEN) {
    $this->_item = $item;
    $this->setStatus($status);
  }

  /**
   * Append the listview item node marker to the parent xml element
   *
   * @param \Papaya\XML\Element $parent
   * @param \Papaya\XML\Element|NULL
   * @return null|\Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    if ($this->status != self::NODE_HIDDEN) {
      $node = $parent->appendElement(
        'node',
        array(
          'status' => $this->_statusStrings[$this->status]
        )
      );
      if ($this->status != self::NODE_EMPTY) {
        $node->setAttribute('href', (string)$this->reference());
      }
      return $node;
    }
    return NULL;
  }

  /**
   * Sett for the status
   *
   * @param integer $status
   */
  public function setStatus($status) {
    if (isset($this->_statusStrings[$status])) {
      $this->_status = (int)$status;
    } else {
      $this->_status = self::NODE_HIDDEN;
    }
  }

  /**
   * Getter/Setter for the node reference, if no reference is provided it is cloned
   * from the item.
   *
   * @param \Papaya\UI\Reference $reference
   * @return \Papaya\UI\Reference
   */
  public function reference(\Papaya\UI\Reference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = clone $this->item->reference();
    }
    return $this->_reference;
  }
}
