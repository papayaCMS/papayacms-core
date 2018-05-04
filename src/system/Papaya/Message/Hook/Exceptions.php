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
* Papaya Message Hook Exception, capture exceptions and handle them
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageHookExceptions
  implements PapayaMessageHook {

  /**
  * Message manger object to dispatch the created messages
  * @var PapayaMessageManager
  */
  private $_messageManager = NULL;

  /**
  * Create hook and set message manager object
  *
  * @param \PapayaMessageManager $messageManager
  */
  public function __construct(\PapayaMessageManager $messageManager) {
    $this->_messageManager = $messageManager;
  }


  /**
  * Activate hook, override current exception handler. This will only capture exception not
  * catched in the source.
  */
  public function activate() {
    set_exception_handler(array($this, 'handle'));
  }

  /**
  * Deactivate hook, restore previous exception handler
  */
  public function deactivate() {
    restore_exception_handler();
  }

  /**
  * Actual exception handler, just generate an message for it.
  *
  * @param \Exception|\Throwable $exception
  */
  public function handle($exception) {
    if ($exception instanceof \ErrorException) {
      $this->_messageManager->dispatch(
        new \PapayaMessagePhpException($exception)
      );
    } else {
      $error = new \ErrorException(
        sprintf(
          "Uncaught exception '%s' with message '%s' in %s:%d",
          get_class($exception),
          $exception->getMessage(),
          $exception->getFile(),
          $exception->getLine()
        )
      );
      $this->_messageManager->dispatch(
        new \PapayaMessagePhpException(
          $error,
          new \PapayaMessageContextBacktrace(0, $exception->getTrace())
        )
      );
    }
  }
}
