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
 * A single line input for ISO time
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|\Papaya\UI\Text $caption
 * @property string $name
 * @property string $hint
 * @property string|null $defaultValue
 * @property bool $mandatory
 */
class Time extends \Papaya\UI\Dialog\Field\Input {
  /**
   * Field type, used in template
   *
   * @var string
   */
  protected $_type = 'time';

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
   * @param float $step optional, default 60.0
   *
   * @throws \InvalidArgumentException
   */
  public function __construct(
    $caption, $name, $default = NULL, $mandatory = FALSE, $step = 60.0
  ) {
    if ($step < 0) {
      throw new \InvalidArgumentException('Step must not be less than 0.');
    }
    parent::__construct($caption, $name, 9, $default);
    $this->_step = $step;
    $this->setmandatory($mandatory);
    $this->setFilter(
      new \Papaya\Filter\Time($this->_step)
    );
  }
}
