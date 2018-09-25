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
namespace Papaya\Filter\Exception;

use Papaya\Filter;

/**
 * A range exception is thrown if a value in a certain range is expected.
 *
 * In other words if a value is to small or to large
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
abstract class OutOfRange extends Filter\Exception {
  /**
   * The expected value limit (minimum or maximum)
   *
   * @var int|float
   */
  private $_expectedLimit;

  /**
   * The actual length of the value
   *
   * @var int|float
   */
  private $_actualValue;

  /**
   * Construct object, set message and range limit information
   *
   * @param string $message
   * @param int|float $expected
   * @param int|float $actual
   */
  public function __construct($message, $expected, $actual) {
    $this->_expectedLimit = $expected;
    $this->_actualValue = $actual;
    parent::__construct($message);
  }

  /**
   * Read private expected value limit property
   *
   * @return int|float
   */
  public function getExpectedLimit() {
    return $this->_expectedLimit;
  }

  /**
   * Read private actual value property
   *
   * @return int|float
   */
  public function getActualValue() {
    return $this->_actualValue;
  }
}
