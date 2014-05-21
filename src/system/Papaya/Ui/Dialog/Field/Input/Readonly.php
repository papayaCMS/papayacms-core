<?php
/**
 * A simple single line readonly input field with a caption.
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
 * @version $Id: Readonly.php 37484 2012-08-27 22:21:02Z weinert $
 */

/**
 * A simple single line readonly input field with a caption.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class PapayaUiDialogFieldInputReadonly extends PapayaUiDialogFieldInput {

  /**
   * Initialize object, set caption and field name
   *
   * @param string|PapayaUiString $caption
   * @param string $name
   * @param mixed $default
   */
  public function __construct($caption, $name, $default = NULL) {
    parent::__construct($caption, $name, 0, $default);
  }

  /**
   * Append field and input ouptut to DOM
   *
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'input',
      array(
        'type' => $this->_type,
        'name' => $this->_getParameterName($this->getName()),
        'readonly' => 'yes'
      ),
      (string)$this->getCurrentValue()
    );
  }

  /**
   * Always return the provided defaultvalue
   *
   * @return mixed|null
   */
  public function getCurrentValue() {
    return $this->getDefaultValue();
  }
}
