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
 * A single line input for email
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|\Papaya\UI\Text $caption
 * @property string $name
 * @property string $hint
 * @property string|NULL $defaultValue
 * @property boolean $mandatory
 */
class Email extends \Papaya\UI\Dialog\Field\Input {
  /**
   * Field type, used in template
   *
   * @var string
   */
  protected $_type = 'email';

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
    'mandatory' => array('getMandatory', 'setMandatory')
  );

  /**
   * Creates dialog field for email address input with caption, name, default value and
   * mandatory status
   *
   * @param string $caption
   * @param string $name
   * @param mixed $default optional, default NULL
   * @param boolean $mandatory optional, default FALSE
   */
  public function __construct($caption, $name, $default = NULL, $mandatory = FALSE) {
    parent::__construct($caption, $name, 1024, $default);
    $this->setMandatory($mandatory);
    $this->setFilter(
      new \Papaya\Filter\Email()
    );
  }
}