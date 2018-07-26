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
* A list of listview columns, used for the $columns property of a {@see \PapayaUiListview}
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiListviewColumns
  extends \PapayaUiControlCollection {

  /**
  * Only {@see \PapayaUiListviewColumn} objects are allowed in this list
  *
  * @var string
  */
  protected $_itemClass = \PapayaUiListviewColumn::class;

  /**
  * If a tag name is provided, an additional element will be added in
  * {@see \PapayaUiControlCollection::appendTo()) that will wrapp the items.
  * @var string
  */
  protected $_tagName = 'cols';

  /**
  * Create object an set owner listview object.
  *
  * @param \PapayaUiListview $listview
  */
  public function __construct(\PapayaUiListview $listview) {
    $this->owner($listview);
  }

  /**
   * Return the listview of this list
   *
   * @param \PapayaUiListview $listview
   * @return \PapayaUiListview
   */
  public function owner($listview = NULL) {
    \PapayaUtilConstraints::assertInstanceOfOrNull(\PapayaUiListview::class, $listview);
    return parent::owner($listview);
  }
}
