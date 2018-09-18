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
 * A subitem is additional data, attached to an listview item. They are displayed as additional
 * columns in the most cases.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class SubItem extends \Papaya\UI\Control\Collection\Item {
  /**
   * Alignment, if it is NULL, the column alignment is used, "left" is the default value.
   *
   * @var null|int
   */
  protected $_align;

  /**
   * Specific parameters for a link
   *
   * @var array
   */
  protected $_actionParameters;

  /**
   * Set the alignment.
   *
   * @param null|int $align
   */
  public function setAlign($align) {
    $this->_align = $align;
  }

  /**
   * Get the alignment, if the internal value is NULL. It will try to get the alignment from the
   * column. If the column is not available it will return "left".
   *
   * @return int
   */
  public function getAlign() {
    if (\is_null($this->_align)) {
      $columnIndex = $this->index();
      if ($this->hasCollection() &&
        ($collection = $this->collection()) &&
        $collection instanceof \Papaya\UI\ListView\SubItems &&
        $collection->getListView()->columns()->has($columnIndex + 1)) {
        /* @noinspection PhpUndefinedMethodInspection */
        return $collection->getListView()->columns()->get($columnIndex + 1)->getAlign();
      } else {
        return \Papaya\UI\Option\Align::LEFT;
      }
    } else {
      return $this->_align;
    }
  }

  /**
   * Store action parameters for links or form elements in the subitem
   *
   * @param array $actionParameters
   */
  public function setActionParameters(array $actionParameters = NULL) {
    $this->_actionParameters = $actionParameters;
  }
}
