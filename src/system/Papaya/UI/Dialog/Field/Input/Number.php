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
 * A single line input for unsigned numbers with optional minimum/maximum length
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
class Number extends \Papaya\UI\Dialog\Field\Input {
  /**
   * Field type, used in template
   *
   * @var string
   */
  protected $_type = 'number';

  /**
   * Minimum length
   *
   * @var int
   */
  protected $_minimumLength;

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
   * Creates dialog field for date input with caption, name, default value and
   * mandatory status
   *
   * @param string $caption
   * @param string $name
   * @param mixed $default optional, default NULL
   * @param bool $mandatory optional, default FALSE
   * @param int $minimumLength optional, default NULL
   * @param int $maximumLength optional, default NULL
   *
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
    if (NULL !== $minimumLength) {
      if (!\is_numeric($minimumLength) || $minimumLength <= 0) {
        throw new \UnexpectedValueException('Minimum length must be greater than 0.');
      }
    }
    if (!\is_numeric($maximumLength) || $maximumLength <= 0) {
      throw new \UnexpectedValueException('Maximum length must be greater than 0.');
    }
    if (NULL !== $minimumLength && $minimumLength > $maximumLength) {
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
