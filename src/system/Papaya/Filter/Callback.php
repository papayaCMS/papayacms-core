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
 * Papaya filter class that uses a callback function to validate the value
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Callback implements Filter {
  /**
   * callback function or method
   *
   * @var string
   */
  private $_callback;

  /**
   * Additional arguments for the callback
   *
   * @var array
   */
  private $_arguments;

  /**
   * Construct object and initialize function name and optional arguments.
   *
   * The function will throw an exception if the function does not exist.
   *
   * You can provide additional arguments, but the value will always be the first.
   *
   * @param \Callback $callback
   * @param array $arguments
   */
  public function __construct($callback, array $arguments = []) {
    $this->_callback = $callback;
    $this->_arguments = $arguments;
  }

  /**
   * Validate the input value using the function and
   * throw an exception if the validation has failed.
   *
   * @throws Exception
   *
   * @param string $value
   *
   * @return true
   */
  public function validate($value) {
    $callback = $this->_callback;
    if (!\is_callable($callback)) {
      throw new Exception\InvalidCallback($callback);
    }
    $arguments = $this->_arguments;
    \array_unshift($arguments, $value);
    if (!$callback(...$arguments)) {
      throw new Exception\FailedCallback($callback);
    }
    return TRUE;
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param string $value
   *
   * @return string|null
   */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (Exception $e) {
      return NULL;
    }
  }
}
