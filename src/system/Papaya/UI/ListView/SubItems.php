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

namespace Papaya\UI\ListView;
/**
 * Subitems are additional data, attached to an listview item. They are displayed as additional
 * columns in the most cases.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class SubItems
  extends \Papaya\UI\Control\Collection {

  /**
   * Only {@see \Papaya\Ui\ListView\SubItem} objects are allowed in this list
   *
   * @var string
   */
  protected $_itemClass = SubItem::class;

  /**
   * Provide no tag name, so no additional element will be added in
   * {@see \Papaya\UI\Control\Collection::appendTo()) that whould wrap the items.
   *
   * @var string
   */
  protected $_tagName = '';

  /**
   * Create object an set owner listview object.
   *
   * @param Item $item
   */
  public function __construct(Item $item) {
    $this->owner($item);
  }

  /**
   * Return the listview item for this list of subitems
   *
   * @param Item $item
   * @return Item
   */
  public function owner($item = NULL) {
    \Papaya\Utility\Constraints::assertInstanceOfOrNull(Item::class, $item);
    return parent::owner($item);
  }

  /**
   * Return the listview the owner item is part of.
   *
   * @return \Papaya\UI\ListView
   */
  public function getListView() {
    return $this->owner()->collection()->owner();
  }
}
