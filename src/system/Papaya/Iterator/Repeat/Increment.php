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
namespace Papaya\Iterator\Repeat;

/**
 * This iterator increments a value by step until an maximum is reached.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Increment extends Callback {
  const MODE_LIST = 0;

  const MODE_ASSOC = 1;

  /**
   * Create object, store maximum and step vor callback method.
   *
   * @param int $minimum
   * @param int $maximum
   * @param int $step
   * @param int $mode
   */
  public function __construct($minimum, $maximum, $step = 1, $mode = self::MODE_LIST) {
    parent::__construct(
      function($value, $key) use ($maximum, $step, $mode) {
        $value += $step;
        if (self::MODE_ASSOC === $mode) {
          $key = $value;
        } else {
          ++$key;
        }
        if ($value <= $maximum) {
          return [$value, $key];
        }
        return FALSE;
      },
      $minimum - $step,
      -1
    );
  }
}
