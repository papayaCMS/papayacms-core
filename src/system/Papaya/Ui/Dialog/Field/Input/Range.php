<?php
/**
* A single line input for Range
*
* Creates a dialog field for an numeric input with defind Range.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Range.php 39409 2014-02-27 16:36:19Z weinert $
*/

/**
* A single line input for Range
*
* @package Papaya-Library
* @subpackage Ui
*
* @property string|PapayaUiString $caption
* @property string $name
* @property string $hint
* @property float|NULL $defaultValue
* @property boolean $mandatory
* @property float $minimum
* @property float $maximum
* @property float $step
*/
class PapayaUiDialogFieldInputRange extends PapayaUiDialogFieldInput {

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
  protected $_minimum = NULL;

  /**
  * Maximum value for range
  *
  * @var float
  */
  protected $_maximum = NULL;

  /**
  * step value for Range
  *
  * @var float
  */
  protected $_step = NULL;

  /**
  * declare dynamic properties
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'caption' => array('getCaption', 'setCaption'),
    'name' => array('getName', 'setName'),
    'hint' => array('getHint', 'setHint'),
    'defaultValue' => array('getDefaultValue', 'setDefaultValue'),
    'mandatory' => array('getMandatory', 'setMandatory'),
    'minimum' => array('_minimum', 'setMinimum'),
    'maximum' => array('_maximum', 'setMaximum'),
    'step' => array('_step', 'setStep')
  );

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
   * @param boolean $mandatory
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
    if (is_null($default)) {
      $default = round(($minimum + $maximum) / 2, 0);
    }
    parent::__construct($caption, $name, 20, $default);
    $this->setMandatory($mandatory);
    $this->setFilter(
      new PapayaFilterFloat($this->_minimum, $this->_maximum)
    );
    $this->_minimum = $minimum;
    $this->_maximum = $maximum;
    $this->_step = $step;
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
        'min' => $this->_minimum,
        'max' => $this->_maximum,
        'step' => $this->_step
      ),
      (string)$this->getCurrentValue()
    );
  }
}