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
namespace Papaya\UI\Dialog\Field\Input;

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\Filter;
use Papaya\UI;
use Papaya\XML;

/**
 * A checkbox for an active/inactive value
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|StringCastable $caption
 * @property string $name
 * @property string $hint
 * @property string|null $defaultValue
 * @property bool $mandatory
 */
class Checkbox extends UI\Dialog\Field\Input {
  /**
   * Specify the field type for the template
   *
   * @var string
   */
  protected $_type = 'checkbox';

  /**
   * Field type, used in template
   *
   * @var array
   */
  protected $_values = [
    'active' => TRUE,
    'inactive' => FALSE
  ];

  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'caption' => ['getCaption', 'setCaption'],
    'name' => ['getName', 'setName'],
    'hint' => ['getHint', 'setHint'],
    'defaultValue' => ['getDefaultValue', 'setDefaultValue'],
    'mandatory' => ['getMandatory', 'setMandatory']
  ];

  /**
   * Creates dialog field for time input with caption, name, default value and
   * mandatory status
   *
   * @param string $caption
   * @param string $name
   * @param mixed $default optional, default NULL
   * @param bool $mandatory optional, default FALSE
   */
  public function __construct($caption, $name, $default = NULL, $mandatory = TRUE) {
    parent::__construct($caption, $name, 9, $default);
    $this->setMandatory($mandatory);
    $this->setFilter(
      new Filter\Equals($this->_values['active'])
    );
  }

  /**
   * Append the field to the xml output
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $currentValue = $this->getCurrentValue();
    $input = $field->appendElement(
      'input',
      [
        'type' => $this->_type,
        'name' => $this->_getParameterName($this->getName())
      ],
      (string)$this->_values['active']
    );
    if ((string)$currentValue === (string)$this->_values['active']) {
      $input->setAttribute('checked', 'checked');
    }
    return $field;
  }

  /**
   * Allow to change the values
   *
   * @param mixed $active
   * @param mixed $inactive
   *
   * @throws \InvalidArgumentException
   */
  public function setValues($active, $inactive) {
    if (empty($active)) {
      throw new \InvalidArgumentException(
        'The active value can not be empty.'
      );
    }
    if ((string)$active === (string)$inactive) {
      throw new \InvalidArgumentException(
        'The active value and the inactive value must be different.'
      );
    }
    $this->_values = [
      'active' => $active,
      'inactive' => $inactive
    ];
    $this->setFilter(
      new Filter\Equals($this->_values['active'])
    );
  }

  public function getValues(): array {
    return $this->_values;
  }

  /**
   * Get the current field value. This can be either of two values specified by the member
   * variable $_values
   *
   * @return mixed
   */
  public function getCurrentValue() {
    $name = $this->getName();
    if (!empty($name) && ($dialog = $this->getDialog())) {
      if ($this->getDisabled()) {
        return $this->getDefaultValue();
      }
      if ($dialog->isSubmitted()) {
        $isActive = (
          $dialog->parameters()->has($name) &&
          $dialog->parameters()->get($name, '') === (string)$this->_values['active']
        );
      } elseif ($dialog->data()->has($name)) {
        $isActive = (string)$dialog->data()->get($name) === (string)$this->_values['active'];
      } else {
        $isActive = (string)$this->getDefaultValue() === (string)$this->_values['active'];
      }
      return $this->_values[$isActive ? 'active' : 'inactive'];
    }
    return $this->getDefaultValue();
  }

  /**
   * Get the default value for the field.
   *
   * @return string
   */
  public function getDefaultValue() {
    $value = parent::getDefaultValue();
    $isActive = (string)$value === (string)$this->_values['active'];
    return $this->_values[$isActive ? 'active' : 'inactive'];
  }

  /**
   * The filter is only active if the field is mandatory. Otherwise it will just set the
   * "inactive" value if it is not valid
   *
   * @see \Papaya\UI\Dialog\Field::getFilter()
   */
  public function getFilter() {
    if ($this->getMandatory()) {
      return parent::getFilter();
    }
    return NULL;
  }
}
