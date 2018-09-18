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

namespace Papaya\UI\Dialog\Field;

/**
 * A field containing a listview control.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class ListView extends \Papaya\UI\Dialog\Field {
  /**
   * listview object buffer
   *
   * @var \Papaya\UI\ListView
   */
  private $_listview;

  /**
   * Create object and assign needed values.
   *
   * @param \Papaya\UI\ListView $listview
   */
  public function __construct(\Papaya\UI\ListView $listview) {
    $this->listview($listview);
  }

  /**
   * Getter/Setter for the listview, the listview is always set in the constructor and
   * can never be NULL, so no implicit create is needed.
   *
   * @param \Papaya\UI\ListView $listview
   * @return \Papaya\UI\ListView
   */
  public function listview(\Papaya\UI\ListView $listview = NULL) {
    if (isset($listview)) {
      $this->_listview = $listview;
    }
    return $this->_listview;
  }

  /**
   * Append field to dialog xml element.
   *
   * @param \Papaya\XML\Element $parent
   * @return \Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->append($this->listview());
    return $field;
  }
}
