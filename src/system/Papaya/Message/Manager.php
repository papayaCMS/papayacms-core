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
   * @var array(\Papaya\Message\PapayaMessageDispatcher)
   */
  private $_dispatchers = array();

  /**
   * List of php event hooks
   *
   * @var array
   */
  private $_hooks = NULL;

  /**
   * Add a dispatcher to the list
   *
   * @param \Papaya\Message\Dispatcher $dispatcher
   */
  public function addDispatcher(\Papaya\Message\Dispatcher $dispatcher) {
    $this->_dispatchers[] = $dispatcher;
  }

  /**
   * Dispatch a message to all available dispatchers
   *
   * @param \Papaya\Message $message
   */
  public function dispatch(\Papaya\Message $message) {
    /** @var \Papaya\Message\Dispatcher $dispatcher */
    foreach ($this->_dispatchers as $dispatcher) {
      $dispatcher->dispatch($message);
    }
  }

  /**
   * Display a message to the user
   *
   * @param $severity
   * @param $text
   */
  public function display($severity, $text) {
    $this->dispatch(new \Papaya\Message\Display($severity, $text));
  }

  /**
   * Log a message, if $context ist not an \Papaya\Message\Context\PapayaMessageContextInterface it will be encapsulated
   * into a \Papaya\Message\Context\PapayaMessageContextVariable
   *
   * @param integer $severity
   * @param integer $group
   * @param integer $text
   * @param mixed $context
   */
  public function log($severity, $group, $text, $context = NULL) {
    $message = new \Papaya\Message\Log($severity, $group, $text);
    if ($context instanceof \Papaya\Message\Context\Group) {
      $message->setContext($context);
    } elseif ($context instanceof \Papaya\Message\Context\Data) {
      $message->context()->append($context);
    } elseif (isset($context)) {
      $message->context()->append(new \Papaya\Message\Context\Variable($context));
    }
    $this->dispatch($message);
  }

  /**
   * Debug message shortcut, creates a default log message with debug contexts
   *
   * If arguments are provided, they are added to a variable context as an array.
   */
  public function debug() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message\Logable::GROUP_DEBUG, \Papaya\Message::SEVERITY_DEBUG, ''
    );
    if (func_num_args() > 0) {
      $message->context()->append(new \Papaya\Message\Context\Variable(func_get_args(), 5, 9999));
    }
    $message
      ->context()
      ->append(new \Papaya\Message\Context\Memory())
      ->append(new \Papaya\Message\Context\Runtime())
      ->append(new \Papaya\Message\Context\Backtrace(1));
    $this->dispatch($message);
  }

  /**
   * Encapsulate an callback into an sandbox, capturing all exceptions and dispatching them
   * as logable error messages.
   *
   * @param \Callable $callback
   * @return \Papaya\Message\Sandbox|callable
   */
  public function encapsulate($callback) {
    \PapayaUtilConstraints::assertCallable($callback);
    $sandbox = new \Papaya\Message\Sandbox($callback);
    $sandbox->papaya($this->papaya());
    return array($sandbox, '__invoke');
  }

  /**
   * Register error and exceptions hooks
   */
  public function hooks(array $hooks = NULL) {
    if (isset($hooks)) {
      $this->_hooks = $hooks;
    } elseif (is_null($this->_hooks)) {
      $this->_hooks = array(
        $exceptionsHook = new \Papaya\Message\Hook\Exceptions($this),
        new \Papaya\Message\Hook\Errors($this, $exceptionsHook),
      );
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
    \Papaya\Message\Context\Runtime::setStartTime(microtime(TRUE));
    error_reporting($options->get('PAPAYA_LOG_PHP_ERRORLEVEL', E_ALL & ~E_STRICT));
    /** @var \Papaya\Message\Hook $hook */
    foreach ($this->hooks() as $hook) {
      $hook->activate();
    }
  }
}
