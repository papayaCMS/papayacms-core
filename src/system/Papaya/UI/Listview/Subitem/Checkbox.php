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

namespace Papaya\UI\Listview\Subitem;
/**
 * An listview subitem with a checkbox element.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Checkbox extends \Papaya\UI\Listview\Subitem {

  /**
   * @var \Papaya\Request\Parameters\Name
   */
  private $_parameterName;
  /**
   * @var \Papaya\UI\Dialog
   */
  private $_dialog;

  /**
   * @var string
   */
  private $_value;

  public function __construct(\Papaya\UI\Dialog $dialog, $parameterName, $value) {
    $this->_dialog = $dialog;
    $this->_parameterName = new \Papaya\Request\Parameters\Name($parameterName);
    $this->_value = $value;
  }

  /**
   * Append subitem xml data to parent node. In this case just an <subitem/> element
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
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
