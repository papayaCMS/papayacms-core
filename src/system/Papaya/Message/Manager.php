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

namespace Papaya\Message;

use Papaya\Message;

/**
 * Papaya Message Manager, central message manager, handles the dispatchers
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Manager extends \Papaya\Application\BaseObject {
  /**
   * Internal list of message dispatchers
   *
   * @var array(\Papaya\Message\Dispatcher)
   */
  private $_dispatchers = [];

  /**
   * List of php event hooks
   *
   * @var array
   */
  private $_hooks;

  /**
   * Add a dispatcher to the list
   *
   * @param Dispatcher $dispatcher
   */
  public function addDispatcher(Dispatcher $dispatcher) {
    $this->_dispatchers[] = $dispatcher;
  }

  /**
   * Dispatch a message to all available dispatchers
   *
   * @param \Papaya\Message $message
   */
  public function dispatch(\Papaya\Message $message) {
    /** @var Dispatcher $dispatcher */
    foreach ($this->_dispatchers as $dispatcher) {
      $dispatcher->dispatch($message);
    }
  }

  /**
   * @param int $severity
   * @param string $messageText
   * @param array|null $parameters
   */
  public function display($severity, $messageText, array $parameters = NULL) {
    $this->dispatch(new Display\Translated($severity, $messageText, $parameters ?: []));
  }

  /**
   * @param string $messageText
   * @param array|null $parameters
   */
  public function displayInfo($messageText, array $parameters = NULL) {
    $this->display(Message::SEVERITY_INFO, $messageText, $parameters ?: []);
  }

  /**
   * @param string $messageText
   * @param array|null $parameters
   */
  public function displayWarning($messageText, array $parameters = NULL) {
    $this->display(Message::SEVERITY_WARNING, $messageText, $parameters ?: []);
  }

  /**
   * @param string $messageText
   * @param array|null $parameters
   */
  public function displayError($messageText, array $parameters = NULL) {
    $this->display(Message::SEVERITY_ERROR, $messageText, $parameters ?: []);
  }

  /**
   * Log a message, if $context ist not an \Papaya\Message\Context\Data it will be encapsulated
   * into a \Papaya\Message\Context\Variable
   *
   * @param int $severity
   * @param int $group
   * @param int $text
   * @param mixed $context
   */
  public function log($severity, $group, $text, $context = NULL) {
    $message = new Log($severity, $group, $text);
    if ($context instanceof Context\Group) {
      $message->setContext($context);
    } elseif ($context instanceof Context\Data) {
      $message->context()->append($context);
    } elseif (NULL !== $context) {
      $message->context()->append(new Context\Variable($context));
    }
    $this->dispatch($message);
  }

  /**
   * Debug message shortcut, creates a default log message with debug contexts
   *
   * If arguments are provided, they are added to a variable context as an array.
   */
  public function debug() {
    $message = new Log(
      Logable::GROUP_DEBUG, \Papaya\Message::SEVERITY_DEBUG, ''
    );
    if (\func_num_args() > 0) {
      $message->context()->append(new Context\Variable(\func_get_args(), 5, 9999));
    }
    $message
      ->context()
      ->append(new Context\Memory())
      ->append(new Context\Runtime())
      ->append(new Context\Backtrace(1));
    $this->dispatch($message);
  }

  /**
   * Encapsulate an callback into an sandbox, capturing all exceptions and dispatching them
   * as logable error messages.
   *
   * @param \Callable $callback
   * @return Sandbox|callable
   */
  public function encapsulate($callback) {
    \Papaya\Utility\Constraints::assertCallable($callback);
    $sandbox = new Sandbox($callback);
    $sandbox->papaya($this->papaya());
    return [$sandbox, '__invoke'];
  }

  /**
   * Register error and exceptions hooks
   *
   * @param array|null $hooks
   * @return array
   */
  public function hooks(array $hooks = NULL) {
    if (NULL !== $hooks) {
      $this->_hooks = $hooks;
    } elseif (NULL === $this->_hooks) {
      $this->_hooks = [
        $exceptionsHook = new Hook\Exceptions($this),
        new Hook\Errors($this, $exceptionsHook),
      ];
    }
    return $this->_hooks;
  }

  /**
   * Setup message system
   *
   * This functions initializes the start time for runtime debug and activates the hooks for
   * php messages and exceptions.
   *
   * @param \Papaya\Configuration $options
   */
  public function setUp($options) {
    Context\Runtime::setStartTime(\microtime(TRUE));
    \error_reporting($options->get('PAPAYA_LOG_PHP_ERRORLEVEL', E_ALL & ~E_STRICT));
    /** @var \Papaya\Message\Hook $hook */
    foreach ($this->hooks() as $hook) {
      $hook->activate();
    }
  }
}
