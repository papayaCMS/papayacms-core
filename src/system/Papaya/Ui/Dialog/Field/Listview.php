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
* A field containing a listview control.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldListview extends \PapayaUiDialogField {

  /**
  * listview object buffer
  *
  * @var \PapayaUiListview
  */
  private $_listview = NULL;

  /**
  * Create object and assign needed values.
  *
  * @param \PapayaUiListview $listview
  */
  public function __construct(\PapayaUiListview $listview) {
    $this->listview($listview);
  }

  /**
  * Getter/Setter for the listview, the listview is always set in the constructor and
  * can never be NULL, so no implicit create is needed.
  *
  * @param \PapayaUiListview $listview
  * @return \PapayaUiListview
  */
  public function listview(\PapayaUiListview $listview = NULL) {
    if (isset($listview)) {
      $this->_listview = $listview;
    }
    return $this->_listview;
  }

  /**
   * Append field to dialog xml element.
   *
   * @param \PapayaXmlElement $parent
   * @return \PapayaXmlElement
   */
  public function appendTo(\PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->append($this->listview());
    return $field;
  }
}
