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
namespace Papaya\Message\Hook;

use Papaya\Message;

/**
 * Papaya Message Hook Exception, capture exceptions and handle them
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Exceptions
  implements Message\Hook {
  /**
   * Message manger object to dispatch the created messages
   *
   * @var Message\Manager
   */
  private $_messageManager;

  /**
   * Create hook and set message manager object
   *
   * @param Message\Manager $messageManager
   */
  public function __construct(Message\Manager $messageManager) {
    $this->_messageManager = $messageManager;
  }

  public function getMessageManager(): Message\Manager {
    return $this->_messageManager;
  }

  /**
   * Activate hook, override current exception handler. This will only capture exception not
   * catched in the source.
   */
  public function activate() {
    \set_exception_handler(
      function($exception) {
        $this->handle($exception);
      }
    );
  }

  /**
   * Deactivate hook, restore previous exception handler
   */
  public function deactivate() {
    \restore_exception_handler();
  }

  /**
   * Actual exception handler, just generate an message for it.
   *
   * @param \Exception|\Throwable $exception
   */
  public function handle($exception) {
    if ($exception instanceof \ErrorException) {
      $this->_messageManager->dispatch(
        new Message\PHP\Exception($exception)
      );
    } else {
      $error = new \ErrorException(
        \sprintf(
          "Uncaught exception '%s' with message '%s' in %s:%d",
          \get_class($exception),
          $exception->getMessage(),
          $exception->getFile(),
          $exception->getLine()
        )
      );
      $this->_messageManager->dispatch(
        new Message\PHP\Exception(
          $error,
          new Message\Context\Backtrace(0, $exception->getTrace())
        )
      );
    }
  }
}
