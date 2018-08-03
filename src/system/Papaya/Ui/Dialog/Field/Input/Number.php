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
 * A single line input for unsigned numbers with optional minimum/maximum length
 *
 * @package Papaya-Library
 * @subpackage Ui
 *
 * @property string|\Papaya\Ui\Text $caption
 * @property string $name
 * @property string $hint
 * @property string|NULL $defaultValue
 * @property boolean $mandatory
 */
class Number extends \Papaya\Ui\Dialog\Field\Input {

  /**
   * Field type, used in template
   *
   * @var string
   */
  protected $_type = 'number';

  /**
   * Minimum length
   *
   * @var integer
   */
  protected $_minimumLength = NULL;

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
   * Creates dialog field for date input with caption, name, default value and
   * mandatory status
   *
   * @param string $caption
   * @param string $name
   * @param mixed $default optional, default NULL
   * @param boolean $mandatory optional, default FALSE
   * @param integer $minimumLength optional, default NULL
   * @param integer $maximumLength optional, default NULL
   * @throws \UnexpectedValueException
   */
  public function __construct(
    $caption,
    $name,
    $default = NULL,
    $mandatory = FALSE,
    $minimumLength = NULL,
    $maximumLength = 1024
  ) {
    if ($minimumLength !== NULL) {
      if (!is_numeric($minimumLength) || $minimumLength <= 0) {
        throw new \UnexpectedValueException('Minimum length must be greater than 0.');
      }
    }
    if (!is_numeric($maximumLength) || $maximumLength <= 0) {
      throw new \UnexpectedValueException('Maximum length must be greater than 0.');
    }
    if ($minimumLength !== NULL && $minimumLength > $maximumLength) {
      throw new \UnexpectedValueException(
        'Maximum length must be greater than or equal to minimum length.'
      );
    }
    parent::__construct($caption, $name, $maximumLength, $default);
    $this->_minimumLength = $minimumLength;
    $this->setMandatory($mandatory);
    $this->setFilter(
      new \Papaya\Filter\Number($this->_minimumLength, $this->_maximumLength)
    );
  }
}
