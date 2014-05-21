<?php
/**
* Field factory profiles for a input for an iso datetime.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Time.php 37464 2012-08-24 10:08:54Z weinert $
*/

/**
* Field factory profiles for a input for an iso datetime.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldFactoryProfileInputDateTime extends PapayaUiDialogFieldFactoryProfile {

  /**
   * @see PapayaUiDialogFieldFactoryProfile::getField()
   * @return PapayaUiDialogFieldInput
   */
  public function getField() {
    $field = new PapayaUiDialogFieldInputDate(
      $this->options()->caption,
      $this->options()->name,
      $this->options()->default,
      $this->options()->mandatory,
      PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    if ($hint = $this->options()->hint) {
      $field->setHint($hint);
    }
    return $field;
  }
}