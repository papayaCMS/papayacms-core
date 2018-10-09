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
namespace Papaya\Message\Dispatcher;

use Papaya\Application;
use Papaya\Message;

/**
 * Papaya Message Dispatcher Wildfire, send out log messages using the Wildfire protocol
 *
 * Wildfire ist the protocol behind FirePHP, an Firefox extension to display messages,
 * recieved in HTTP headers. {@link http://www.firephp.org}
 *
 * This dispatcher uses the json_encode() funtion and checks the user agent for 'FirePHP'.*
 * It will automatically disable itself if content was send to the browser. In this case sending
 * http headers is not possible any more.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Wildfire
  implements Application\Access, Message\Dispatcher {
  use Application\Access\Aggregation;

  private static $_SEVERITY_TYPES = [
    Message::SEVERITY_DEBUG => 'LOG',
    Message::SEVERITY_INFO => 'INFO',
    Message::SEVERITY_NOTICE => 'INFO',
    Message::SEVERITY_WARNING => 'WARN',
    Message::SEVERITY_ERROR => 'ERROR',
    Message::SEVERITY_CRITICAL => 'ERROR',
    Message::SEVERITY_ALERT => 'ERROR',
    Message::SEVERITY_EMERGENCY => 'ERROR'
  ];

  private static $_SEVERITY_LABELS = [
    Message::SEVERITY_DEBUG => 'Debug',
    Message::SEVERITY_INFO => 'Information',
    Message::SEVERITY_NOTICE => 'Notice',
    Message::SEVERITY_WARNING => 'Warning',
    Message::SEVERITY_ERROR => 'Error',
    Message::SEVERITY_CRITICAL => 'Critical',
    Message::SEVERITY_ALERT => 'Alert',
    Message::SEVERITY_EMERGENCY => 'Emergency'
  ];

  private $_handler;

  /**
   * Send log message to browser using the Wildfire protocol if possible
   *
   * @param Message $message
   *
   * @return bool
   *
   * @throws \InvalidArgumentException
   */
  public function dispatch(Message $message) {
    if ($message instanceof Message\Logable &&
      $this->allow()) {
      // @codeCoverageIgnoreStart
      $this->send($message);
    }
    // @codeCoverageIgnoreEnd
    return FALSE;
  }

  /**
   * Check if it is allowed to use the dispatcher
   *
   * @param \Callback $usableCallback function to test technical conditions, if it is not set
   *                                  self::usable is used.
   *
   * @return bool|mixed
   */
  public function allow($usableCallback = NULL) {
    $options = $this->papaya()->options;
    if (
      $options->get('PAPAYA_PROTOCOL_WILDFIRE', FALSE) &&
      (
        isset($_SERVER['HTTP_X_FIREPHP_VERSION']) ||
        (
          isset($_SERVER['HTTP_USER_AGENT']) &&
          FALSE !== \strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP')
        )
      )
    ) {
      if (NULL === $usableCallback) {
        return self::usable();
      }
      return $usableCallback();
    }
    return FALSE;
  }

  /**
   * Check if WildFire can be used
   *
   * @return bool
   */
  public static function usable() {
    return (\function_exists('json_encode') && 'cli' !== PHP_SAPI && !\headers_sent());
  }

  /**
   * Set Wildfire protocol handler object
   *
   * @param Wildfire\Handler $handler
   */
  public function setHandler(Wildfire\Handler $handler) {
    $this->_handler = $handler;
  }

  /**
   * Get Wildfire protocol handler object, create one if none ist set
   *
   * @return Wildfire\Handler
   *
   * @throws \InvalidArgumentException
   */
  public function getHandler() {
    if (NULL === $this->_handler) {
      $this->_handler = new Wildfire\Handler('header');
    }
    return $this->_handler;
  }

  /**
   * Send log message using the Wildfire protocol
   *
   * @param Message\Logable $message
   *
   * @throws \InvalidArgumentException
   */
  public function send(Message\Logable $message) {
    $wildfire = $this->getHandler();
    if (\count($message->context()) > 0) {
      $wildfire->startGroup($this->getWildfireGroupLabelFromType($message->getSeverity()));
      $messageText = $message->getMessage();
      if (!empty($messageText)) {
        $wildfire->sendMessage(
          $messageText, $this->getWildfireMessageType($message->getSeverity())
        );
      }
      foreach ($message->context() as $context) {
        $this->sendContext($context);
      }
      $wildfire->endGroup();
    } else {
      $wildfire->sendMessage(
        $message->getMessage(), $this->getWildfireMessageType($message->getSeverity())
      );
    }
  }

  /**
   * Send a message context using the Wildfire protocol
   *
   * @param \Papaya\Message\Context\Data $context
   *
   * @throws \InvalidArgumentException
   */
  public function sendContext($context) {
    if ($context instanceof Message\Context\Variable) {
      $this->_sendContextVariable($context);
    } elseif ($context instanceof Message\Context\Backtrace) {
      $this->_sendContextTrace($context);
    } elseif ($context instanceof Message\Context\Interfaces\Table) {
      $this->_sendContextTable($context);
    } else {
      $wildfire = $this->getHandler();
      if ($context instanceof Message\Context\Interfaces\Labeled) {
        $wildfire->startGroup($context->getLabel());
      }
      if ($context instanceof Message\Context\Interfaces\Items) {
        foreach ($context->asArray() as $index => $item) {
          $wildfire->sendMessage('('.($index + 1).') '.$item, 'LOG');
        }
      } elseif ($context instanceof Message\Context\Interfaces\Text) {
        $wildfire->sendMessage($context->asString(), 'LOG');
      }
      if ($context instanceof Message\Context\Interfaces\Labeled) {
        $wildfire->endGroup();
      }
    }
  }

  /**
   * Convert internal type to Wildfire message type
   *
   * @param int $type
   *
   * @return string
   */
  public function getWildfireMessageType($type) {
    if (isset(self::$_SEVERITY_TYPES[$type])) {
      return self::$_SEVERITY_TYPES[$type];
    }
    return 'LOG';
  }

  /**
   * Convert internal type to a group label
   *
   * @param int $type
   *
   * @return string
   */
  public function getWildfireGroupLabelFromType($type) {
    if (isset(self::$_SEVERITY_LABELS[$type])) {
      return self::$_SEVERITY_LABELS[$type];
    }
    return 'Log';
  }

  /**
   * Send a variable dump context
   *
   * Variables dumps need to have a special format to display as much informations as possible.
   *
   * @param Message\Context\Variable $context
   *
   * @throws \InvalidArgumentException
   */
  private function _sendContextVariable(Message\Context\Variable $context) {
    $visitor = new Wildfire\Variable\Visitor(
      $context->getDepth(), $context->getStringLength()
    );
    $context->acceptVisitor($visitor);
    $this->getHandler()->sendDump($visitor->getDump());
  }

  /**
   * Send a backtrace context
   *
   * FirePHP has a special formatted output for traces, that is a lot better then just
   * output a list.
   *
   * @param Message\Context\Backtrace $context
   *
   * @throws \InvalidArgumentException
   */
  private function _sendContextTrace(Message\Context\Backtrace $context) {
    $trace = $context->getBacktrace();
    $count = \count($trace);
    if ($count > 0) {
      $element = $this->_traceElementToArray($trace[0]);
      $data = [
        'Class' => $this->_getArrayElement($element, 'class'),
        'Type' => $this->_getArrayElement($element, 'type'),
        'Message' => $context->getLabel(),
        'Function' => $this->_getArrayElement($element, 'function'),
        'File' => $this->_getArrayElement($element, 'file'),
        'Line' => $this->_getArrayElement($element, 'line'),
        'Args' => $this->_getArrayElement($element, 'args'),
      ];
      for ($i = 1; $i < $count; $i++) {
        $data['Trace'][] = $this->_traceElementToArray($trace[$i]);
      }
      $this->getHandler()->sendMessage($data, 'TRACE', $context->getLabel());
    }
  }

  /**
   * Helper method to get a given element from an array if it is set and a defult if not.
   *
   * @param array $array
   * @param string $index
   * @param mixed $default
   *
   * @return mixed|null
   */
  private function _getArrayElement($array, $index, $default = NULL) {
    return isset($array[$index]) ? $array[$index] : $default;
  }

  /**
   * Prepare a trace element output for FirePHP
   *
   * Prepare and collect trace informations and get a better variable dump
   * for arguments avoiding recursions.
   *
   * @param array $element
   *
   * @return array
   */
  private function _traceElementToArray(array $element) {
    $trace = [
      'class' => $this->_getArrayElement($element, 'class'),
      'type' => $this->_getArrayElement($element, 'type'),
      'function' => $this->_getArrayElement($element, 'function'),
      'file' => $this->_getArrayElement($element, 'file'),
      'line' => $this->_getArrayElement($element, 'line'),
    ];
    if (!empty($element['args'])) {
      $arguments = new Message\Context\Variable($element['args']);
      $visitor = new Wildfire\Variable\Visitor(
        $arguments->getDepth(), $arguments->getStringLength()
      );
      $arguments->acceptVisitor($visitor);
      $trace['args'] = $visitor->getDump();
    }
    return $trace;
  }

  /**
   * Send a tabular context
   *
   * FirePHP has a special formatted output for tables.
   *
   * @param Message\Context\Interfaces\Table $context
   *
   * @throws \InvalidArgumentException
   */
  private function _sendContextTable(Message\Context\Interfaces\Table $context) {
    $table = [];
    $columns = $context->getColumns();
    if (NULL !== $columns) {
      $table[] = \array_values($columns);
    } else {
      $table[] = [];
    }
    $count = $context->getRowCount();
    for ($i = 0; $i < $count; $i++) {
      $table[] = \array_map(
        [$this, 'formatTableValue'],
        \array_values($context->getRow($i))
      );
    }
    $this->getHandler()->sendMessage($table, 'TABLE', $context->getLabel());
  }

  /**
   * The table values need to be strings
   *
   * This has to be a public function, so it is possible to call it using array_map
   *
   * @param mixed $value
   *
   * @return string
   */
  public function formatTableValue($value) {
    return (string)$value;
  }
}
