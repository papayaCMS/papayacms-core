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
 * Papaya Message Log, standard log message class
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Log
  implements \Papaya\Message\Logable {
  /**
   * Message group
   */
  protected $_group = \Papaya\Message\Logable::GROUP_SYSTEM;

  /**
   * Message type
   *
   * @var int
   */
  protected $_type = \Papaya\Message::SEVERITY_INFO;

  /**
   * Message text
   *
   * @var string
   */
  protected $_message = '';

  /**
   * Message context
   *
   * @var null|\Papaya\Message\Context\Group
   */
  protected $_context;

  /**
   * @param int $group
   * @param int $type
   * @param string $message
   */
  public function __construct($group, $type, $message) {
    $this->_group = $group;
    $this->_type = $type;
    $this->_message = $message;
  }

  /**
   * Get group of message (system, php, content, ...)
   *
   * @return int
   */
  public function getGroup() {
    return $this->_group;
  }

  /**
   * Get severity/type of message (info, warning, error)
   *
   * @return int
   */
  public function getType() {
    return $this->_type;
  }

  /**
   * Get severity of message (info, warning, error)
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
    return (string)$this->_message;
  }

  /**
   * Return a context object containing additional data about where and why the message happened.
   *
   * @return \Papaya\Message\Context\Group
   */
  public function context() {
    if (\is_null($this->_context)) {
      $this->_context = new \Papaya\Message\Context\Group();
    }
    return $this->_context;
  }

  /**
   * Set a context group object to the message.
   *
   * @param \Papaya\Message\Context\Group $context
   */
  public function setContext(\Papaya\Message\Context\Group $context) {
    $this->_context = $context;
  }
}
