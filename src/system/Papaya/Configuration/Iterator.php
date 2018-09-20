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
namespace Papaya\Configuration;

use Papaya\Configuration;

/**
 * Iterator for the \Papaya\Configuration class.
 *
 * @package Papaya-Library
 * @subpackage Configuration
 */
class Iterator implements \Iterator {
  /**
   * option names
   *
   * @var array(string)
   */
  private $_names = [];

  /**
   * configuration object
   *
   * @var Configuration
   */
  private $_configuration;

  /**
   * Current iterator position
   *
   * @var int
   */
  private $_position = 0;

  /**
   * iterator maximum
   *
   * @var int
   */
  private $_maximum = 0;

  /**
   * Create object, store names and configuration object
   *
   * @param array $names
   * @param Configuration $configuration
   */
  public function __construct(array $names, Configuration $configuration) {
    $this->_names = \array_values($names);
    $this->_maximum = \count($names) - 1;
    $this->_configuration = $configuration;
  }

  /**
   * Reset iterator position
   */
  public function rewind() {
    $this->_position = 0;
  }

  /**
   * Return option value form current iterator position
   *
   * return mixed
   */
  public function current() {
    if ($this->_position <= $this->_maximum) {
      return $this->_configuration->get($this->_names[$this->_position]);
    }
    return NULL;
  }

  /**
   * Return option name form current iterator position
   *
   * return string
   */
  public function key() {
    return $this->_names[$this->_position];
  }

  /**
   * Move iterator to next position and return option value
   *
   * return mixed
   */
  public function next() {
    ++$this->_position;
    return $this->current();
  }

  /**
   * Return if current iterator position is valid
   *
   * return bool
   */
  public function valid() {
    return $this->_position <= $this->_maximum;
  }
}
