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
* Field factory profiles for a generic input.
*
* Each profile defines how a field {@see PapayaUiDialogField} is created for a specified
* type. Here is an options subobject to provide data for the field configuration.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldFactoryProfileInputSuggest
  extends \PapayaUiDialogFieldFactoryProfile {

  /**
   * @see \PapayaUiDialogFieldFactoryProfile::getField()
   * @return \PapayaUiDialogFieldInput
   */
  public function getField() {
    $field = new \PapayaUiDialogFieldInputSuggest(
      $this->options()->caption,
      $this->options()->name,
      $this->options()->parameters,
      $this->options()->default,
      $this->options()->validation
    );
    $field->setMandatory($this->options()->mandatory);
    if ($this->options()->disabled) {
      $field->setDisabled(TRUE);
    }
    if ($hint = $this->options()->hint) {
      $field->setHint($hint);
    }
    return $field;
  }
}
