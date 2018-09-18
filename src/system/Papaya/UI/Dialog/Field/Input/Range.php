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

/**
 * A single line input for Range
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|\Papaya\UI\Text $caption
 * @property string $name
 * @property string $hint
 * @property float|null $defaultValue
 * @property bool $mandatory
 * @property float $minimum
 * @property float $maximum
 * @property float $step
 */
class Range extends \Papaya\UI\Dialog\Field\Input {
  /**
   * Field type, used in template
   *
   * @var string
   */
  protected $_type = 'range';

  /**
   * Minimum value for range
   *
   * @var float
   */
  protected $_minimum;

  /**
   * Maximum value for range
   *
   * @var float
   */
  protected $_maximum;

  /**
   * step value for Range
   *
   * @var float
   */
  protected $_step;

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
    'mandatory' => ['getMandatory', 'setMandatory'],
    'minimum' => ['_minimum', 'setMinimum'],
    'maximum' => ['_maximum', 'setMaximum'],
    'step' => ['_step', 'setStep']
  ];

  /**
   * Creates dialog field for Range input with caption, name, default value and
   * mandatory status
   *
   * @param string $caption
   * @param string $name
   * @param float $default
   * @param float|int $minimum
   * @param float|int $maximum
   * @param float|int $step
   * @param bool $mandatory
   */
  public function __construct(
    $caption,
    $name,
    $default = NULL,
    $minimum = 0,
    $maximum = 100,
    $step = 1,
    $mandatory = FALSE
  ) {
    if (\is_null($default)) {
      $default = \round(($minimum + $maximum) / 2, 0);
    }
    parent::__construct($caption, $name, 20, $default);
    $this->setMandatory($mandatory);
    $this->setFilter(
      new \Papaya\Filter\FloatValue($this->_minimum, $this->_maximum)
    );
    $this->_minimum = $minimum;
    $this->_maximum = $maximum;
    $this->_step = $step;
  }

  /**
   * Append field and input ouptut to DOM
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'input',
      [
        'type' => $this->_type,
        'name' => $this->_getParameterName($this->getName()),
        'min' => $this->_minimum,
        'max' => $this->_maximum,
        'step' => $this->_step
      ],
      (string)$this->getCurrentValue()
    );
  }
}
