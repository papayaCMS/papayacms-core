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
use Papaya\Utility;

class CLI
  implements Application\Access, Message\Dispatcher {
  use Application\Access\Aggregation;

  const TARGET_STDOUT = 'stdout';

  const TARGET_STDERR = 'stderr';

  private static $_SEVERITY_LABELS = [
    Message::SEVERITY_DEBUG => 'Debug',
    Message::SEVERITY_INFO => 'Info',
    Message::SEVERITY_NOTICE => 'Notice',
    Message::SEVERITY_WARNING => 'Warning',
    Message::SEVERITY_ERROR => 'Error',
    Message::SEVERITY_CRITICAL => 'Critical',
    Message::SEVERITY_ALERT => 'Alert',
    Message::SEVERITY_EMERGENCY => 'Emergency'
  ];

  /**
   * The PHP server API name
   *
   * @var string
   */
  private $_phpSAPIName;

  /**
   * Output streams
   *
   * @var resource[]
   */
  private $_streams = [
    self::TARGET_STDOUT => NULL,
    self::TARGET_STDERR => NULL
  ];

  /**
   * Output log message to stdout
   *
   * @param Message $message
   *
   * @return bool
   */
  public function dispatch(Message $message) {
    if ($message instanceof Message\Logable &&
      $this->allow()) {
      $label = $this->getLabelFromType($message->getSeverity());
      $isError = \in_array(
        $message->getSeverity(),
        [
          Message::SEVERITY_WARNING,
          Message::SEVERITY_ERROR,
          Message::SEVERITY_CRITICAL,
          Message::SEVERITY_ALERT,
          Message::SEVERITY_EMERGENCY
        ],
        FALSE
      );
      \fwrite(
        $this->stream($isError ? self::TARGET_STDERR : self::TARGET_STDOUT),
        \sprintf(
          "\n\n%s: %s %s\n",
          $label,
          $message->getMessage(),
          $message->context()->asString()
        )
      );
    }
    return FALSE;
  }

  /**
   * Get/set the php sapi name
   *
   * @see php_sapi_name()
   *
   * @param string $name
   *
   * @return string
   */
  public function phpSAPIName($name = NULL) {
    if (NULL !== $name) {
      $this->_phpSAPIName = $name;
    }
    if (NULL === $this->_phpSAPIName) {
      $this->_phpSAPIName = \strtolower(PHP_SAPI);
    }
    return $this->_phpSAPIName;
  }

  /**
   * Check if it is allowed to use the dispatcher
   */
  public function allow() {
    return ('cli' === $this->phpSAPIName());
  }

  /**
   * @param int $type
   *
   * @return array
   */
  public function getLabelFromType($type) {
    if (isset(self::$_SEVERITY_LABELS[$type])) {
      return self::$_SEVERITY_LABELS[$type];
    }
    return self::$_SEVERITY_LABELS[Message::SEVERITY_ERROR];
  }

  /**
   * Getter/Setter for the target output streams (stdout/stderr)
   *
   * @param string $target
   * @param resource $stream
   *
   * @throws \InvalidArgumentException
   *
   * @return resource
   */
  public function stream($target, $stream = NULL) {
    if (!\array_key_exists($target, $this->_streams)) {
      throw new \InvalidArgumentException(
        \sprintf('Invalid output target "%s".', $target)
      );
    }
    if (NULL !== $stream) {
      Utility\Constraints::assertResource($stream);
      $this->_streams[$target] = $stream;
    } elseif (NULL === $this->_streams[$target]) {
      $name = 'php://'.$target;
      $this->_streams[$target] = \fopen($name, 'wb');
    }
    return $this->_streams[$target];
  }
}
