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

/**
* This iterator decrements a value by step until an minimum is reached.
*
*
* @package Papaya-Library
* @subpackage Iterator
*/
class PapayaIteratorRepeatDecrement extends PapayaIteratorRepeatCallback {

  protected $_minimum = 0;
  protected $_step = 0;

  const MODE_LIST = 0;
  const MODE_ASSOC = 1;

  /**
   * Create object, store maximum and step vor callback method.
   *
   * @param integer $maximum
   * @param integer $minimum
   * @param integer $step
   * @param int $mode
   */
  public function __construct($maximum, $minimum, $step = 1, $mode = self::MODE_LIST) {
    $this->_minimum = $minimum;
    $this->_step = $step;
    $this->_mode = $mode;
    parent::__construct(array($this, 'decrement'), $maximum + $step, -1);
  }

  /**
  * Increment the current value by step until it is larger then the maximim.
  *
  * @param integer $value
  * @param integer $key
  * @return boolean
  */
  public function decrement($value, $key) {
    $value -= $this->_step;
    if ($this->_mode == self::MODE_ASSOC) {
      $key = $value;
    } else {
      ++$key;
    }
    if ($value >= $this->_minimum) {
      return array($value, $key);
    } else {
      return FALSE;
    }
  }
}
