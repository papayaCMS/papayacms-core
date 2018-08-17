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
if (!defined('E_RECOVERABLE_ERROR')) {
  /**
   * Available since PHP 5.2, define if not here
   *
   * @ignore
   */
  define('E_RECOVERABLE_ERROR', 4096);
}
if (!defined('E_DEPRECATED')) {
  /**
   * Available since PHP 5.3, define if not here
   *
   * @ignore
   */
  define('E_DEPRECATED', 8192);
}
if (!defined('E_USER_DEPRECATED')) {
  /**
   * Available since PHP 5.3, define if not here
   *
   * @ignore
   */
  define('E_USER_DEPRECATED', 16384);
}

/**
 * Papaya Message Hook Error, capture php error events and handle them
 *
 * Nonfatal errors, are send to the message system. Fatal errors are converted into exceptions.
 * The system recognizes duplicates by severity, file and line. Duplicates are ignored.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Errors
  implements \Papaya\Message\Hook {

  /**
   * Message manger object to dispatch the created messages
   *
   * @var \Papaya\Message\Manager
   */
  private $_messageManager = NULL;

  /**
   * Count errors messages, currently used for duplicate check
   *
   * @var array
   */
  private $_previousErrors = array();


  /**
   * Store the exception hook, so it can be used directly instead of throwing the
   * exception
   *
   * @var array
   */
  private $_exceptionHook = NULL;

  /**
   * List of nonfatal error severities
   *
   * @var array
   */
  protected $_nonfatalErrors = array(
    E_NOTICE, E_USER_NOTICE, E_WARNING, E_USER_WARNING,
    E_STRICT, E_RECOVERABLE_ERROR,
    E_DEPRECATED, E_USER_DEPRECATED
  );

  /**
   * Create hook and set message manager object
   *
   * @param \Papaya\Message\Manager $messageManager
   * @param \Papaya\Message\Hook\Exceptions $exceptionHook
   */
  public function __construct(
    \Papaya\Message\Manager $messageManager, \Papaya\Message\Hook\Exceptions $exceptionHook = NULL
  ) {
    $this->_messageManager = $messageManager;
    $this->_exceptionHook = $exceptionHook;
  }

  /**
   * Activate hook, override current error handler. E_STRICT errors are not handled because
   * we still have a lot of php 4 source.
   */
  public function activate() {
    set_error_handler(
      array($this, 'handle')
    );
  }

  /**
   * Deactivate hook, restore previous error handler
   */
  public function deactivate() {
    restore_error_handler();
  }

  /**
   * Actual error handler callback
   *
   * @param integer $severity
   * @param string $text
   * @param string $file
   * @param integer $line
   * @param mixed $context
   * @return bool
   */
  public function handle($severity, $text, $file, $line, $context) {
    $errorReporting = error_reporting();
    if (($errorReporting & $severity) == $severity) {
      try {
        if (in_array($severity, $this->_nonfatalErrors)) {
          if (!$this->checkErrorDuplicates($severity, $file, $line)) {
            // @codeCoverageIgnoreStart
            /*
            This is to avoid bug https://bugs.php.net/bug.php?id=47987
            If the Autoloader is not working and the class does not exist yes,
            we disable the internal handling and let php take over.
            */
            if (!class_exists(\Papaya\Message\PHP\Error::class)) {
              return FALSE;
            }
            // @codeCoverageIgnoreEnd
            $this->_messageManager->dispatch(
              new \Papaya\Message\PHP\Error($severity, $text, $context)
            );
          }
        } else {
          $this->handleException(
            new \ErrorException($text, 0, $severity, $file, $line)
          );
        }
      } catch (\ErrorException $e) {
        return $this->handleException($e);
      } catch (\Exception $e) {
        return FALSE;
      }
    }
    return TRUE;
  }

  private function handleException(\Exception $exception) {
    if (isset($this->_exceptionHook)) {
      $this->_exceptionHook->handle($exception);
      return TRUE;
    } else {
      throw $exception;
    }
  }

  /**
   * Count errors grouped by severity, file and line. Used to recognize duplicates.
   *
   * @param integer $severity
   * @param string $file
   * @param integer $line
   * @return int
   * @internal param string $text
   */
  public function checkErrorDuplicates($severity, $file, $line) {
    $hash = md5($severity.'|'.$file.'|'.$line);
    if (isset($this->_previousErrors[$hash])) {
      return $this->_previousErrors[$hash]++;
    } else {
      $this->_previousErrors[$hash] = 1;
      return 0;
    }
  }
}
