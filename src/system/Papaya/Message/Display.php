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
 * Papaya Message Display, simple message displayed to the user
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Display
  implements Displayable {
  /**
   * Message type
   *
   * @var int
   */
  protected $_severity = \Papaya\Message::SEVERITY_INFO;

  /**
   * Message text
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_message = '';

  /**
   * Allowed message types, creating a message with an invalid type will thrown an exception
   *
   * @var array
   */
  private $_possibleSeverities = [
    \Papaya\Message::SEVERITY_INFO,
    \Papaya\Message::SEVERITY_WARNING,
    \Papaya\Message::SEVERITY_ERROR
  ];

  /**
   * @param int $severity
   * @param string|\Papaya\UI\Text $message
   */
  public function __construct($severity, $message) {
    $this->_isValidSeverity($severity);
    $this->_severity = $severity;
    $this->_message = $message;
  }

  /**
   * check if the given type is valid for this kind of messages
   *
   * @param int $severity
   *
   * @return bool
   */
  private function _isValidSeverity($severity) {
    if (\in_array($severity, $this->_possibleSeverities, FALSE)) {
      return TRUE;
    }
    throw new \InvalidArgumentException('Invalid message type.');
  }

  /**
   * Get type of message (info, warning, error)
   *
   * @return int
   */
  public function getType() {
    return $this->_severity;
  }

  /**
   * Get type of message (info, warning, error)
   *
   * @return int
   */
  public function getSeverity() {
    return $this->_severity;
  }

  /**
   * Get message string
   *
   * @return string
   */
  public function getMessage() {
    return (string)$this->_message;
  }
}
