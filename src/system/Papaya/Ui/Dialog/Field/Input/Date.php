<?php
/**
* A single line input for ISO date with optional time
*
* Creates a dialog field for date (and optional time) input.
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
* @version $Id: Date.php 39435 2014-02-28 09:51:45Z weinert $
*/

/**
* A single line input for date and optional time
*
* @package Papaya-Library
* @subpackage Ui
*
* @property string|PapayaUiString $caption
* @property string $name
* @property string $hint
* @property string|NULL $defaultValue
* @property boolean $mandatory
* @property float $step
* @property-read int $includeTime
*/
class PapayaUiDialogFieldInputDate extends PapayaUiDialogFieldInput {

  /**
  * Field type, used in template
  *
  * @var string
  */
  protected $_type = 'date';

  /**
  * Include time?
  * @var int
  */
  protected $_includeTime = PapayaFilterDate::DATE_NO_TIME;

  /**
  * Step for time filter
  * @var float
  */
  protected $_step = 60.0;

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
    'includeTime' => array('_includeTime'),
    'step' => array('_step', '_step')
  );

  /**
   * Creates dialog field for date input with caption, name, default value and
   * mandatory status
   *
   * @param string|PapayaUiString $caption
   * @param string $name
   * @param integer $default
   * @param boolean $mandatory
   * @param int $includeTime
   * @param float $step
   * @throws InvalidArgumentException
   */
  public function __construct(
    $caption,
    $name,
    $default = NULL,
    $mandatory = FALSE,
    $includeTime = PapayaFilterDate::DATE_NO_TIME,
    $step = 60.0
  ) {
    if ($includeTime != PapayaFilterDate::DATE_NO_TIME &&
        $includeTime != PapayaFilterDate::DATE_OPTIONAL_TIME &&
        $includeTime != PapayaFilterDate::DATE_MANDATORY_TIME) {
      throw new InvalidArgumentException(
        'Argument must be PapayaFilterDate::DATE_NO_TIME, '.
        'PapayaFilterDate::DATE_OPTIONAL_TIME, or '.
        'PapayaFilterDate::DATE_MANDATORY_TIME.'
      );
    }
    if ($step < 0) {
      throw new InvalidArgumentException('Step must be greater than 0.');
    }
    $this->_includeTime = (int)$includeTime;
    $this->_step = $step;
    parent::__construct($caption, $name, 19, $default);
    $this->setType(
      $includeTime == PapayaFilterDate::DATE_NO_TIME ? 'date' : 'datetime'
    );
    $this->setMandatory($mandatory);
    $this->setFilter(
      new PapayaFilterDate($this->_includeTime, $this->_step)
    );
  }
}