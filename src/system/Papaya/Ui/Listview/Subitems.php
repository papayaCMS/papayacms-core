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
* Subitems are additional data, attached to an listview item. They are displayed as additional
* columns in the most cases.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiListviewSubitems
  extends \PapayaUiControlCollection {

  /**
  * Only {@see \PapayaUiListviewSubItem} objects are allowed in this list
  *
  * @var string
  */
  protected $_itemClass = \PapayaUiListviewSubitem::class;

  /**
  * Provide no tag name, so no additional element will be added in
  * {@see \PapayaUiControlCollection::appendTo()) that whould wrap the items.
  *
  * @var string
  */
  protected $_tagName = '';

  /**
   * Create object an set owner listview object.
   *
   * @param \PapayaUiListviewItem $item
   */
  public function __construct(\PapayaUiListviewItem $item) {
    $this->owner($item);
  }

  /**
   * Return the listview item for this list of subitems
   *
   * @param \PapayaUiListviewItem $item
   * @return \PapayaUiListviewItem
   */
  public function owner($item = NULL) {
    \PapayaUtilConstraints::assertInstanceOfOrNull(\PapayaUiListviewItem::class, $item);
    return parent::owner($item);
  }

  /**
  * Return the listview the owner item is part of.
  *
  * @return \PapayaUiListview
  */
  public function getListview() {
    return $this->owner()->collection()->owner();
  }
}
