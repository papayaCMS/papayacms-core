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
* A command that executes an action depending on a specific set of parameters
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiControlCommandAction extends \PapayaUiControlCommand {

  /**
  * Dialog object
  *
  * @var PapayaUiDialog
  */
  private $_data = NULL;

  /**
  * Dialog event callbacks
  *
  * @var PapayaUiControlCommandDialogCallbacks
  */
  private $_callbacks = NULL;

  /**
  * Execute command and append result to output xml
  *
  * @param \PapayaXmlElement $parent
  * @return \PapayaXmlElement
  */
  public function appendTo(\PapayaXmlElement $parent) {
    if ($this->data()->validate()) {
      $this->callbacks()->onValidationSuccessful($this, $parent);
    } else {
      $this->callbacks()->onValidationFailed($this, $parent);
    }
    return $parent;
  }

  /**
   * Getter/Setter to the validated parameters data subobject.
   *
   * @param \PapayaRequestParametersValidator $data
   * @return null|\PapayaRequestParametersValidator|\PapayaUiDialog
   */
  public function data(\PapayaRequestParametersValidator $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    } elseif (NULL === $this->_data) {
      $this->_data = $this->_createData();
    }
    return $this->_data;
  }

  /**
   * Create parameters validator using the "getDefintion()" callback
   *
   * @param array|NULL $definitions
   * @return \PapayaRequestParametersValidator
   */
  protected function _createData(array $definitions = NULL) {
    return new \PapayaRequestParametersValidator(
      isset($definitions) ? $definitions : $this->callbacks()->getDefinition(),
      $this->parameters()
    );
  }

  /**
  * Getter/Setter for the callbacks object
  *
  * @param \PapayaUiControlCommandActionCallbacks $callbacks
  * @return \PapayaUiControlCommandActionCallbacks
  */
  public function callbacks(\PapayaUiControlCommandActionCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    } elseif (is_null($this->_callbacks)) {
      $this->_callbacks = new \PapayaUiControlCommandActionCallbacks();
    }
    return $this->_callbacks;
  }
}
