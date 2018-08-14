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

namespace Papaya\UI\Listview;
/**
 * A list of listview items, used for the $items property of a {@see \Papaya\UI\Listview}
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Items
  extends \Papaya\UI\Control\Collection {

  /**
   * Only {@see \Papaya\UI\Listview\Item} objects are allowed in this list
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
   * A basic reference (link object) for the listview items. The reference object is cloned and
   * modified by the item using it's $actionParameters.
   *
   * @var NULL|\Papaya\UI\Reference
   */
  protected $_reference = NULL;

  /**
   * Create object an set owner listview object.
   *
   * @param \Papaya\UI\Listview $listview
   */
  public function __construct(\Papaya\UI\Listview $listview) {
    $this->owner($listview);
  }

  /**
   * Return the listview of this list
   *
   * @param \Papaya\UI\Listview $listview
   * @return \Papaya\UI\Listview
   */
  public function owner($listview = NULL) {
    \Papaya\Utility\Constraints::assertInstanceOfOrNull(\Papaya\UI\Listview::class, $listview);
    return parent::owner($listview);
  }

  /**
   * Getter/Setter for the basic reference used by the list items. This will be the link on the
   * caption/image.
   *
   * @param \Papaya\UI\Reference $reference
   * @return \Papaya\UI\Reference
   */
  public function reference(\Papaya\UI\Reference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    }
    if (is_null($this->_reference)) {
      $this->_reference = $this->owner()->reference();
    }
    return $this->_reference;
  }
}