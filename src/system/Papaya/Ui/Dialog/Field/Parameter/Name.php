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

class PapayaUiDialogFieldParameterName {

  /**
   * @var PapayaUiDialog|NULL
   */
  private $_dialog;
  /**
   * @var string|array
   */
  private $_fieldName;

  /**
   * PapayaUiDialogFieldParameterName constructor.
   * @param PapayaUiDialog|NULL $dialog
   * @param $fieldName
   */
  public function __construct(PapayaUiDialog $dialog = NULL, $fieldName) {
    $this->_dialog = $dialog;
    $this->_fieldName = $fieldName;
  }

  /**
   * @param bool $withGroup
   * @return string
   */
  public function get($withGroup = TRUE) {
    if ($withGroup && $this->_dialog instanceof \PapayaUiDialog) {
      $name = $this->_dialog->getParameterName($this->_fieldName);
      $prefix = $this->_dialog->parameterGroup();
      if (isset($prefix)) {
        $name->prepend($prefix);
      }
    } else {
      $name = new \PapayaRequestParametersName($this->_fieldName);
    }
    return (string)$name;
  }

  /**
   * @return string
   */
  public function __toString() {
    return $this->get();
  }

}
