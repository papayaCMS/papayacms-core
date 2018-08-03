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

namespace Papaya\Ui\Listview;
/**
 * A list of listview columns, used for the $columns property of a {@see \Papaya\Ui\PapayaUiListview}
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class Columns
  extends \Papaya\Ui\Control\Collection {

  /**
   * Only {@see \Papaya\Ui\Listview\PapayaUiListviewColumn} objects are allowed in this list
   *
   * @var string
   */
  protected $_itemClass = Column::class;

  /**
   * If a tag name is provided, an additional element will be added in
   * {@see \Papaya\Ui\Control\PapayaUiControlCollection::appendTo()) that will wrapp the items.
   *
   * @var string
   */
  protected $_tagName = 'cols';

  /**
   * Create object an set owner listview object.
   *
   * @param \Papaya\Ui\Listview $listview
   */
  public function __construct(\Papaya\Ui\Listview $listview) {
    $this->owner($listview);
  }

  /**
   * Return the listview of this list
   *
   * @param \Papaya\Ui\Listview $listview
   * @return \Papaya\Ui\Listview
   */
  public function owner($listview = NULL) {
    \Papaya\Utility\Constraints::assertInstanceOfOrNull(\Papaya\Ui\Listview::class, $listview);
    return parent::owner($listview);
  }
}
