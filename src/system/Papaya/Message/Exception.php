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
class PapayaMessageException
  extends PapayaMessagePhp {

  /**
   * Create object and set values from exception object
   *
   * @param \Exception $exception
   * @param \PapayaMessageContextBacktrace $trace
   */
  public function __construct(
    Exception $exception,
    \PapayaMessageContextBacktrace $trace = NULL
  ) {
    parent::__construct();
    $this->setSeverity(E_USER_ERROR);

    $this->_message = sprintf(
      "Uncaught exception '%s' with message '%s' in '%s:%d'.",
      get_class($exception),
      $exception->getMessage(),
      $exception->getFile(),
      $exception->getLine()
    );
    $this
      ->_context
      ->append(
        is_null($trace)
          ? new \PapayaMessageContextBacktrace(0, $exception->getTrace())
          : $trace
      );
  }
}
