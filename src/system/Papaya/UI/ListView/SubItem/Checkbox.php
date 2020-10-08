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
namespace Papaya\UI\ListView\SubItem {

  use Papaya\Request\Parameters\GroupSeparator;
  use Papaya\Request\Parameters\Name as ParameterName;
  use Papaya\UI;
  use Papaya\XML;

  /**
   * An listview subitem with a checkbox element.
   *
   * @package Papaya-Library
   * @subpackage UI
   */
  class Checkbox extends UI\ListView\SubItem {
    /**
     * @var ParameterName
     */
    private $_parameterName;

    /**
     * @var UI\Dialog
     */
    private $_dialog;

    /**
     * @var string
     */
    private $_value;

    public function __construct(UI\Dialog $dialog, $parameterName, $value) {
      $this->_dialog = $dialog;
      $this->_parameterName = $parameterName instanceof ParameterName
        ? clone $parameterName : new ParameterName($parameterName);
      $this->_value = $value;
    }

    /**
     * Append subitem xml data to parent node. In this case just an <subitem/> element
     *
     * @param XML\Element $parent
     * @return XML\Element
     */
    public function appendTo(XML\Element $parent) {
      $subitem = $this->_appendSubItemTo($parent);
      $parameterName = clone $this->_parameterName;
      if ($group = $this->_dialog->parameterGroup()) {
        $parameterName->insertBefore(0, $this->_dialog->parameterGroup());
      }
      $checkbox = $subitem->appendElement(
        'input',
        [
          'type' => 'checkbox',
          'name' => $parameterName . GroupSeparator::ARRAY_SYNTAX,
          'value' => $this->_value
        ]
      );
      if ($this->isSelected()) {
        $checkbox->setAttribute('checked', 'checked');
      }
      return $subitem;
    }

    /**
     * @return bool
     */
    public function isSelected() {
      if ($this->_dialog->parameters()->has($this->_parameterName)) {
        $currentValues = $this->_dialog->parameters()->get($this->_parameterName, []);
      } else {
        $currentValues = $this->_dialog->data()->get($this->_parameterName, []);
      }
      return \in_array($this->_value, $currentValues, FALSE);
    }
  }
}
