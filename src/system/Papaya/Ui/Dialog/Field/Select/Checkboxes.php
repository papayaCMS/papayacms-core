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
* A selection field displayed as checkboxes, multiple values can be selected.
*
* The actual value is a list of the selected keys.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldSelectCheckboxes extends PapayaUiDialogFieldSelect {

  /**
  * type of the select control, used in the xslt template
  *
  * @var string
  */
  protected $_type = 'checkboxes';

  /**
   * Determine if the option is selected using the current value and the option value.
   *
   * @param array $currentValue
   * @param string $optionValue
   * @return bool
   */
  protected function _isOptionSelected($currentValue, $optionValue) {
    return in_array($optionValue, (array)$currentValue);
  }

  /**
  * If the values are set, it is nessessary to create a filter based on the values.
  */
  protected function _createFilter() {
    return new \PapayaFilterArray(
      parent::_createFilter()
    );
  }

  /**
  * Get the current field value.
  *
  * If none of the checkboxes was selected the browser will not submit any parameter. So
  * we need to assume that no checkbox was selected if the dialog was submitted.
  *
  * @return mixed
  */
  public function getCurrentValue() {
    $dialog = $this->getDialog();
    if ($dialog && $dialog->isSubmitted()) {
      return $dialog->parameters()->get($this->getName(), array());
    }
    $result = parent::getCurrentValue();
    return is_array($result) ? $result : array();
  }
}
