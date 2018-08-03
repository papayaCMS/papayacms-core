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
/**
 * A hierarchy menu is used to show a line of links representing the current hierarchy of data.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property Items $items
 */
class Menu extends \Papaya\UI\Control {

  /**
   * Items buffer variable
   *
   * @var Items
   */
  private $_items;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'items' => array('items', 'items')
  );

  /**
   * Append menu to parent xml element
   *
   * @param \Papaya\Xml\Element $parent
   * @return \Papaya\Xml\Element|NULL
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    if (count($this->items()) > 0) {
      $menu = $parent->appendElement('hierarchy-menu');
      $this->items()->appendTo($menu);
      return $menu;
    } else {
      return NULL;
    }
  }

  /**
   * Getter/Setter for the hierarchy items collection
   *
   * @param Items $items
   * @return Items
   */
  public function items(Items $items = NULL) {
    if (NULL !== $items) {
      $this->_items = $items;
    } elseif (NULL === $this->_items) {
      $this->_items = new Items();
      $this->_items->owner($this);
    }
    return $this->_items;
  }
}
