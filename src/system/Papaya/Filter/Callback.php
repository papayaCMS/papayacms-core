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
* Papaya filter class that uses a callback function to validate the value
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterCallback implements \Papaya\Filter {

  /**
  * callback function or method
  *
  * @var string
  */
  private $_callback = '';

  /**
  * Addiitonal arguments for the callback
  *
  * @var string
  */
  private $_arguments = array();

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
  public function __construct($callback, array $arguments = array()) {
    $this->_callback = $callback;
    $this->_arguments = $arguments;
  }

  /**
  * Validate the input value using the function and
  * throw an exception if the validation has failed.
  *
  * @throws \PapayaFilterException
  * @param string $value
  * @return TRUE
  */
  public function validate($value) {
    $this->_isCallback($this->_callback);
    $arguments = $this->_arguments;
    array_unshift($arguments, $value);
    if (!call_user_func_array($this->_callback, $arguments)) {
      throw new \Papaya\Filter\Exception\FailedCallback($this->_callback);
    }
    return TRUE;
  }

  /**
  * The filter function is used to read a input value if it is valid.
  *
  * @param string $value
  * @return string|NULL
  */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (\PapayaFilterException $e) {
      return NULL;
    }
  }

  /**
   * Check if the callback function is callable
   *
   * @param \Callback $callback
   * @throws \Papaya\Filter\Exception\InvalidCallback
   */
  public function _isCallback($callback) {
    if (!is_callable($callback)) {
      throw new \Papaya\Filter\Exception\InvalidCallback($callback);
    }
  }
}
