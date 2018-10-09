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
namespace Papaya\XML;

use Papaya\Application;
use Papaya\Message;

/**
 * Encapsulation object for the libxml errors.
 *
 * This is a wrapper for the libxml error handling function, it converts the warnings and errors
 * into \Papaya\Message objects and dispatches them into the MessageManager.
 *
 * @package Papaya-Library
 * @subpackage XML
 */
class Errors implements Application\Access {
  use Application\Access\Aggregation;
  /**
   * @var bool
   */
  private $_savedStatus = FALSE;

  /**
   * Map libxml error types to message types
   *
   * @var array
   */
  private $_errorMapping = [
    LIBXML_ERR_NONE => Message::SEVERITY_INFO,
    LIBXML_ERR_WARNING => Message::SEVERITY_WARNING,
    LIBXML_ERR_ERROR => Message::SEVERITY_ERROR,
    LIBXML_ERR_FATAL => Message::SEVERITY_ERROR
  ];

  /**
   * Activate the libxml internal error capturing (and clear the current buffer)
   */
  public function activate() {
    $this->_savedStatus = \libxml_use_internal_errors(TRUE);
    \libxml_clear_errors();
  }

  /**
   * Deactivate the libxml internal error capturing (and clear the current buffer)
   */
  public function deactivate() {
    \libxml_clear_errors();
    \libxml_use_internal_errors($this->_savedStatus);
  }

  /**
   * Encapsulate a libxml method to capture errors into exceptions. Returns
   * NULL if a \Papaya\XML\Exception was captured, the result of the callback
   * otherwise.
   *
   * @param callable $callback
   * @param null|array $arguments
   * @param bool $emitErrors
   *
   * @return mixed
   */
  public function encapsulate($callback, array $arguments = NULL, $emitErrors = TRUE) {
    $this->activate();
    try {
      $arguments = $arguments ?: [];
      $success = $callback(...$arguments);
      if ($emitErrors) {
        $this->emit();
      }
      $this->deactivate();
    } catch (Exception $e) {
      if ($emitErrors) {
        $context = new Message\Context\Group();
        if ($e->getContextFile()) {
          $context->append(
            new Message\Context\File(
              $e->getContextFile(), $e->getContextLine(), $e->getContextColumn()
            )
          );
        }
        $context->append(new Message\Context\Variable($arguments));
        $context->append(new Message\Context\Backtrace(1));
        $this->papaya()->messages->log(
          Message\Logable::GROUP_SYSTEM,
          Message::SEVERITY_ERROR,
          $e->getMessage(),
          $context
        );
      }
      return NULL;
    }
    return $success;
  }

  /**
   * Dispatches messages for the libxml errors in the internal buffer.
   *
   * @param bool $fatalOnly
   *
   * @throws Exception
   */
  public function emit($fatalOnly = FALSE) {
    $errors = \libxml_get_errors();
    foreach ($errors as $error) {
      if (LIBXML_ERR_FATAL === $error->level) {
        throw new Exception($error);
      }
      if (!$fatalOnly && 0 !== \strpos($error->message, 'Namespace prefix papaya')) {
        $this
          ->papaya()
          ->messages
          ->dispatch(
            $this->getMessageFromError($error)
          );
      }
    }
    \libxml_clear_errors();
  }

  /**
   * @deprecated {@see self::emit()}
   *
   * @param bool $fatalOnly
   *
   * @throws Exception
   */
  public function omit($fatalOnly = FALSE) {
    $this->emit($fatalOnly);
  }

  /**
   * Converts a libxml error object into a \Papaya\Message
   *
   * @param \libXMLError $error
   *
   * @return Message\Log
   */
  public function getMessageFromError(\libXMLError $error) {
    $messageType = $this->_errorMapping[$error->level];
    $message = new Message\Log(
      Message\Logable::GROUP_SYSTEM,
      $messageType,
      \sprintf(
        '%d: %s in line %d at char %d',
        $error->code,
        $error->message,
        $error->line,
        $error->column
      )
    );
    if (!empty($error->file)) {
      $message
        ->context()
        ->append(
          new Message\Context\File(
            $error->file, $error->line, $error->column
          )
        );
    }
    $message
      ->context()
      ->append(
        new Message\Context\Backtrace(3)
      );
    return $message;
  }
}
