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
namespace Papaya\Filter;

use Papaya\Filter;

/**
 * Papaya filter for numeric with unit validation
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class NumberWithUnit implements Filter {
  /**
   * Allowed units for validation
   *
   * @var array
   */
  private $_units = [];

  /**
   * Minimum float value for numeric part. If the value is NULL no check will occur.
   *
   * @var float|null
   */
  private $_minimum;

  /**
   * Maximum float value for numeric part. If the value is NULL no check will occur.
   *
   * @var float|null
   */
  private $_maximum;

  /**
   * Explicit string for validating algebraic sign in front of numeric value. If the value is NULL
   * no check will occur.
   *
   * @var string|null
   */
  private $_algebraicSign;

  /**
   * Construct object, check and store options
   *
   * @param array|int|string $units
   * @param float $minimum (optional)
   * @param float $maximum (optional)
   * @param string $algebraicSign (optional)
   */
  public function __construct($units, $minimum = NULL, $maximum = NULL, $algebraicSign = NULL) {
    \Papaya\Utility\Constraints::assertNotEmpty($units);
    if (!\is_array($units)) {
      $units = [$units];
    }
    $this->_units = $units;
    if (NULL !== $minimum) {
      $this->_minimum = $minimum;
    }
    if (NULL !== $maximum) {
      $this->_maximum = $maximum;
    }
    if (NULL !== $algebraicSign) {
      $this->_algebraicSign = $algebraicSign;
    }
  }

  /**
   * Validates the given string and throws exceptions
   *
   * @throws Exception
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    $value = (string)$value;
    $matches = [];
    $units = $this->getUnitSubPattern();
    \preg_match(
      '~^(?P<number>
        (-|\+)?
        (\d+,)?+
        (\d+)
        (\.\d+)?
      )
      (?P<unit>'.$units.')$~Dix',
      $value,
      $matches
    );
    if (
      isset($matches['unit']) &&
        \in_array($matches['unit'], $this->_units, TRUE)
    ) {
      if (
        '-' === $this->_algebraicSign &&
        0 !== \strpos($matches['number'], '-')
      ) {
        throw new Exception\InvalidCharacter($matches['number'], 0);
      }
      if (
        '+' === $this->_algebraicSign &&
        (float)$matches['number'] < 0) {
        throw new Exception\InvalidCharacter($matches['number'], 0);
      }
      if (
        NULL !== $this->_minimum &&
        (float)$matches['number'] < $this->_minimum) {
        throw new Exception\OutOfRange\ToSmall($this->_minimum, (float)$matches['number']);
      }
      if (
        NULL !== $this->_maximum &&
        (float)$matches['number'] > $this->_maximum) {
        throw new Exception\OutOfRange\ToLarge($this->_maximum, (float)$matches['number']);
      }
      return TRUE;
    }
    if ('0' === (string)$value) {
      return TRUE;
    }
    throw new Exception\NotIncluded($value);
  }

  /**
   * Removes unwanted characters from value
   *
   * @param mixed $value
   *
   * @return string|null
   */
  public function filter($value) {
    $value = (string)$value;
    $units = $this->getUnitSubPattern();
    $matches = [];
    \preg_match(
      '~^
      (?:[^\d\+-]+)?
      (?P<number>
        (\+|-)?
        (\d+,)?+
        (\d+)
        (\.\d+)?
      )
      (?:[^\d\+-]+)?
      (?P<unit>'.$units.')
      (?:.*)?$~Dix',
      $value,
      $matches
    );
    if (isset($matches['number'], $matches['unit'])) {
      $value = $matches['number'].$matches['unit'];
    }
    try {
      $this->validate($value);
      return $value;
    } catch (Exception $e) {
      return NULL;
    }
  }

  /**
   * Returns a string to be used in regular expressions for units
   *
   * @return string
   */
  private function getUnitSubPattern() {
    $result = [];
    foreach ($this->_units as $unit) {
      $result[] = \preg_quote($unit, '(');
    }
    return '(?:'.\implode('|', $result).')';
  }
}
