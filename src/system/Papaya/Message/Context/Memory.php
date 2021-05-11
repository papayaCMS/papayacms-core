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
namespace Papaya\Message\Context;

/**
 * Message context containing the information about the memory consumption
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Memory
  implements Interfaces\Text {
  /**
   * Class variable to remember last memory usage status and calculate differences
   *
   * @var int
   */
  private static $_previousUsage = 0;

  /**
   * Current usage (set in constructor)
   *
   * @var int
   */
  protected $_currentUsage = 0;

  /**
   * Peak usage (set in constructor)
   *
   * @var int
   */
  protected $_peakUsage = 0;

  /**
   * Usage difference, calculate in @see \Papaya\Message\Context\Memory::_setMemoryUsage()
   *
   * @var int
   */
  protected $_diffUsage = 0;

  /**
   * Create object, get memory usage
   */
  public function __construct() {
    if (\function_exists('memory_get_usage')) {
      $realUsage = PHP_VERSION_ID > 50200;
      $this->setMemoryUsage(
        $realUsage ? \memory_get_usage(TRUE) : \memory_get_usage(),
        $realUsage ? \memory_get_peak_usage(TRUE) : \memory_get_peak_usage()
      );
    }
  }

  /**
   * Get memory usage string output
   */
  public function asString() {
    $result = '';
    if ($this->_currentUsage > 0 || $this->_peakUsage > 0) {
      $result .= 'Memory Usage: '.\number_format($this->_currentUsage, 0, '.', ',').' bytes';
      if ($this->_diffUsage >= 0) {
        $result .= ' (+'.\number_format($this->_diffUsage, 0, '.', ',').' bytes)';
      } else {
        $result .= ' (-'.\number_format($this->_diffUsage * -1, 0, '.', ',').' bytes)';
      }
      $result .= ' | Peak Usage: '.\number_format($this->_peakUsage, 0, '.', ',').' Bytes';
    }
    return $result;
  }

  /**
   * Set memory usages, calculate difference to last call of this function
   *
   * @param int $current
   * @param int $peak
   */
  public function setMemoryUsage($current, $peak) {
    $this->_currentUsage = $current;
    $this->_peakUsage = $peak;
    $this->_diffUsage = $current - self::$_previousUsage;
    $this->rememberMemoryUsage($current);
  }

  /**
   * Remember memory usage for difference calculation
   *
   * @param int $current
   */
  public function rememberMemoryUsage($current) {
    self::$_previousUsage = $current;
  }

  public function getPreviousUsage(): int {
    return self::$_previousUsage;
  }

  public function getCurrentUsage():int {
    return $this->_currentUsage;
  }

  public function getPeakUsage(): int {
    return $this->_peakUsage;
  }

  public function getUsageDifference(): int {
    return $this->_diffUsage;
  }
}
