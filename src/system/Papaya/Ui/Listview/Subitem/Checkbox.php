<?php
/**
 * An listview subitem with a checkbox element.
 *
 * @copyright 2011 by papaya Software GmbH - All rights reserved.
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
 * @version $Id: Empty.php 38958 2013-11-22 12:45:31Z weinert $
 */

/**
 * An listview subitem with a checkbox element.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class PapayaUiListviewSubitemCheckbox extends PapayaUiListviewSubitem {

  /**
   * @var PapayaRequestParametersName
   */
  private $_parameterName;
  /**
   * @var PapayaUiDialog
   */
  private $_dialog;

  /**
   * @var string
   */
  private $_value;

  public function __construct(PapayaUiDialog $dialog, $parameterName, $value) {
    $this->_dialog = $dialog;
    $this->_parameterName = new PapayaRequestParametersName($parameterName);
    $this->_value = $value;
  }

  /**
   * Append subitem xml data to parent node. In this case just an <subitem/> element
   *
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
    $item = $parent->appendElement('subitem');
    $parameterName = clone $this->_parameterName;
    if ($group = $this->_dialog->parameterGroup()) {
      $parameterName->insertBefore(0, $this->_dialog->parameterGroup());
    }
    $checkbox = $item->appendElement(
      'input',
      array(
        'type' => 'checkbox',
        'name' => (string)$parameterName.'[]',
        'value' => (string)$this->_value
      )
    );
    if ($this->isSelected()) {
      $checkbox->setAttribute('checked', 'checked');
    }
  }

  public function isSelected() {
    if ($this->_dialog->parameters()->has($this->_parameterName)) {
      $currentValues = $this->_dialog->parameters()->get($this->_parameterName, []);
    } else {
      $currentValues = $this->_dialog->data()->get($this->_parameterName, []);
    }
    return (in_array($this->_value, $currentValues));
  }
}