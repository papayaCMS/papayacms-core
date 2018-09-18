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
 * Papaya Message Php, superclass for log messages for php erorrs and exceptions
 *
 * A log message with the ability to convert the php severity to a log message type.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
abstract class PHP
  implements Logable {
  /**
   * Message type
   *
   * @var int
   */
  protected $_type = \Papaya\Message::SEVERITY_ERROR;

  /**
   * Message text
   *
   * @var string
   */
  protected $_message = '';

  /**
   * Message context
   *
   * @var null|Context\Group
   */
  protected $_context;

  /**
   * Mapping PHP error levels to message types
   *
   * @var array
   */
  private $_errors = [
    E_ERROR => \Papaya\Message::SEVERITY_ERROR,
    E_USER_ERROR => \Papaya\Message::SEVERITY_ERROR,
    E_RECOVERABLE_ERROR => \Papaya\Message::SEVERITY_ERROR,
    E_WARNING => \Papaya\Message::SEVERITY_WARNING,
    E_USER_WARNING => \Papaya\Message::SEVERITY_WARNING,
    E_NOTICE => \Papaya\Message::SEVERITY_INFO,
    E_USER_NOTICE => \Papaya\Message::SEVERITY_INFO
  ];

  /**
   * Create context subobject, too
   */
  public function __construct() {
    $this->_context = new Context\Group();
  }

  /**
   * Set type from severity
   *
   * @param int $severity
   */
  public function setSeverity($severity) {
    if (isset($this->_errors[$severity])) {
      $this->_type = $this->_errors[$severity];
    }
  }

  /**
   * Get group of message (system, php, content, ...)
   *
   * @return int
   */
  public function getGroup() {
    return Logable::GROUP_PHP;
  }

  /**
   * Get type of message (info, warning, error)
   *
   * @return int
   */
  public function getType() {
    return $this->_type;
  }

  /**
   * Get type of message (info, warning, error)
   *
   * @return int
   */
  public function getSeverity() {
    return $this->_type;
  }

  /**
   * Get message string
   *
   * @return string
   */
  public function getMessage() {
    return $this->_message;
  }

  /**
   * Return a context object containing additional data about where and why the message happened.
   *
   * @return Context\Group
   */
  public function context() {
    return $this->_context;
  }

  /**
   * Set a context group object to the message.
   *
   * @param Context\Group $context
   */
  public function setContext(Context\Group $context) {
    $this->_context = $context;
  }
}
