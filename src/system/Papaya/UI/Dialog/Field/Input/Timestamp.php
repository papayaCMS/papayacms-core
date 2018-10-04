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

use Papaya\Filter;
use Papaya\UI;
use Papaya\XML;

/**
 * A single line input for date and optional time, the internal value is an unix timestamp.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|UI\Text $caption
 * @property string $name
 * @property string $hint
 * @property string|null $defaultValue
 * @property bool $mandatory
 * @property float $step
 * @property-read int $includeTime
 */
class Timestamp extends Date {
  /**
   * Create object and initalize integer filter
   *
   * @param string|UI\Text $caption
   * @param string $name
   * @param int $default
   * @param bool $mandatory
   * @param int $includeTime
   * @param float $step
   */
  public function __construct(
    $caption,
    $name,
    $default = NULL,
    $mandatory = FALSE,
    $includeTime = Filter\Date::DATE_NO_TIME,
    $step = 60.0
  ) {
    parent::__construct($caption, $name, $default, $mandatory, (int)$includeTime, $step);
    $this->setFilter(new Filter\IntegerValue(1));
  }

  /**
   * Get the current field value.
   *
   * If the dialog object has a matching paremeter it is used. Otherwise the data object of the
   * dialog is checked and used.
   *
   * If neither dialog parameter or data is available, the default value is returned.
   *
   * @return mixed
   */
  public function getCurrentValue() {
    $name = $this->getName();
    if (
      '' !== \trim($name) &&
      $this->hasCollection() &&
      $this->collection()->hasOwner() &&
      $this->collection()->owner()->parameters()->has($name)) {
      $dateTime = $this->collection()->owner()->parameters()->get($name);
      return \strtotime($dateTime);
    }
    return (int)parent::getCurrentValue();
  }

  /**
   * Append field and input ouptut to DOM
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'input',
      [
        'type' => $this->_type,
        'name' => $this->_getParameterName($this->getName()),
        'maxlength' => $this->_maximumLength
      ],
      $this->formatDateTime(
        $this->getCurrentValue(), Filter\Date::DATE_NO_TIME !== $this->_includeTime
      )
    );
    return $field;
  }

  /**
   * Convert timestamp into a string
   *
   * @param int $timestamp
   * @param bool $includeTime
   *
   * @return string
   */
  private function formatDateTime($timestamp, $includeTime = TRUE) {
    if (0 === (int)$timestamp) {
      return '';
    }
    if ($includeTime) {
      return \date('Y-m-d H:i:s', $timestamp);
    }
    return \date('Y-m-d', $timestamp);
  }
}
