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

namespace Papaya\Ui\Dialog\Field\Input;
/**
 * A single line input for date and optional time
 *
 * @package Papaya-Library
 * @subpackage Ui
 *
 * @property string|\PapayaUiString $caption
 * @property string $name
 * @property string $hint
 * @property string|NULL $defaultValue
 * @property boolean $mandatory
 * @property float $step
 * @property-read int $includeTime
 */
class Date extends \Papaya\Ui\Dialog\Field\Input {

  /**
   * Field type, used in template
   *
   * @var string
   */
  protected $_type = 'date';

  /**
   * Include time?
   *
   * @var int
   */
  protected $_includeTime = \Papaya\Filter\Date::DATE_NO_TIME;

  /**
   * Step for time filter
   *
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
   * @param string|\PapayaUiString $caption
   * @param string $name
   * @param integer $default
   * @param boolean $mandatory
   * @param int $includeTime
   * @param float $step
   * @throws \UnexpectedValueException
   * @throws \InvalidArgumentException
   */
  public function __construct(
    $caption,
    $name,
    $default = NULL,
    $mandatory = FALSE,
    $includeTime = \Papaya\Filter\Date::DATE_NO_TIME,
    $step = 60.0
  ) {
    if (
      $includeTime !== \Papaya\Filter\Date::DATE_NO_TIME &&
      $includeTime !== \Papaya\Filter\Date::DATE_OPTIONAL_TIME &&
      $includeTime !== \Papaya\Filter\Date::DATE_MANDATORY_TIME
    ) {
      throw new \InvalidArgumentException(
        sprintf(
          'Argument must be %1$s::DATE_NO_TIME, %1$s::DATE_OPTIONAL_TIME, or %1$s::DATE_MANDATORY_TIME.',
          \Papaya\Filter\Date::class
        )
      );
    }
    if ($step < 0) {
      throw new \InvalidArgumentException('Step must be greater than 0.');
    }
    $this->_includeTime = (int)$includeTime;
    $this->_step = $step;
    parent::__construct($caption, $name, 19, $default);
    $this->setType(
      $includeTime === \Papaya\Filter\Date::DATE_NO_TIME ? 'date' : 'datetime'
    );
    $this->setMandatory($mandatory);
    $this->setFilter(
      new \Papaya\Filter\Date($this->_includeTime, $this->_step)
    );
  }
}
