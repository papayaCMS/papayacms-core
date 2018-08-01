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

namespace Papaya\Ui\Control\Command\Condition;
/**
 * A command condition based on a callback.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class Callback extends \Papaya\Ui\Control\Command\Condition {

  /**
   * member variable to store the callback
   *
   * @var callable
   */
  private $_callback;

  /**
   * Create object and store callback.
   *
   * @param callable $callback
   * @throws \InvalidArgumentException
   */
  public function __construct($callback) {
    if (!is_callable($callback)) {
      throw new \InvalidArgumentException(
        'InvalidArgumentException: provided $callback is not callable.'
      );
    }
    $this->_callback = $callback;
  }

  /**
   * Execute callback and return value.
   *
   * @return boolean
   */
  public function validate() {
    return (boolean)call_user_func($this->_callback);
  }
}
