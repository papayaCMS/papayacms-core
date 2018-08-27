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
 * A list of list view columns, used for the $columns property of a {@see \Papaya\UI\ListView}
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Columns
  extends \Papaya\UI\Control\Collection {

  /**
   * Only {@see \Papaya\UI\ListView\Column} objects are allowed in this list
   *
   * @var string
   */
  protected $_itemClass = Column::class;

  /**
   * If a tag name is provided, an additional element will be added in
   * {@see \Papaya\UI\Control\Collection::appendTo()) that will wrapp the items.
   *
   * @var string
   */
  protected $_tagName = 'cols';

  /**
   * Create object an set owner listview object.
   *
   * @param \Papaya\UI\ListView $listview
   */
  public function __construct(\Papaya\UI\ListView $listview) {
    $this->owner($listview);
  }

  /**
   * Return the listview of this list
   *
   * @param \Papaya\UI\ListView $listview
   * @return \Papaya\UI\ListView
   */
  public function owner($listview = NULL) {
    \Papaya\Utility\Constraints::assertInstanceOfOrNull(\Papaya\UI\ListView::class, $listview);
    return parent::owner($listview);
  }
}
