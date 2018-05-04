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

class PapayaUiListviewItemRadio extends PapayaUiListviewItem {

  private $_fieldName = '';

  /**
   * @var PapayaUiDialog
   */
  private $_dialog;

  private $_value = '';

  private $_checked = NULL;

  /**
   * @param string $image
   * @param \PapayaUiString|string $caption
   * @param \PapayaUiDialog $dialog
   * @param bool $fieldName
   * @param $value
   */
  public function __construct($image, $caption, \PapayaUiDialog $dialog, $fieldName, $value) {
    parent::__construct($image, $caption);
    $this->_dialog = $dialog;
    $this->_fieldName = $fieldName;
    $this->_value = $value;
  }

  public function appendTo(\PapayaXmlElement $parent) {
    $node = parent::appendTo($parent);
    $input = $node->appendElement(
      'input',
      [
        'type' => 'radio',
        'name' => new \PapayaUiDialogFieldParameterName($this->_dialog, $this->_fieldName),
        'value' => $this->_value
      ]
    );
    if ($this->isChecked()) {
      $input->setAttribute('checked', 'checked');
    }
  }

  public function isChecked() {
    if (NULL === $this->_checked) {
      $this->_checked = FALSE;
      if ($this->_dialog->parameters()->get($this->_fieldName, '') === (string)$this->_value) {
        $this->_checked = TRUE;
      } elseif ($this->_dialog->data()->get($this->_fieldName, '') === (string)$this->_value) {
        $this->_checked = TRUE;
      }
    }
    return $this->_checked;
  }
}
