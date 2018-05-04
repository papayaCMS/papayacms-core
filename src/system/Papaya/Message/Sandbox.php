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
* Papaya Message Exception, message object representing a php exception. This allows to convert
* any exception into an error log message.
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageSandbox extends PapayaObject {

  private $_callback = NULL;

  /**
  * Create object and set values from exception object
  *
  * @param Callable $callback
  */
  public function __construct($callback) {
    PapayaUtilConstraints::assertCallable($callback);
    $this->_callback = $callback;
  }

  /**
   * invoke the callback, return the result. If an exception occurs, dispatch it as an
   * message and return NULL.
   *
   * @param mixed,... $argument
   * @return mixed
   */
  public function __invoke() {
    $result = NULL;
    try {
      $arguments = func_num_args() > 0 ? func_get_args() : array();
      $result = call_user_func_array($this->_callback, $arguments);
    } catch (ErrorException $e) {
      $this->papaya()->messages->dispatch(new \PapayaMessagePhpException($e));
    } catch (Exception $e) {
      $this->papaya()->messages->dispatch(new \PapayaMessageException($e));
    }
    return $result;
  }
}
