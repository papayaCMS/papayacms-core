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
  implements \Papaya\Message\Displayable {

  /**
   * Message type
   *
   * @var integer
   */
  protected $_type = \Papaya\Message::SEVERITY_INFO;

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
  protected $_allowedTypes = array(
    \Papaya\Message::SEVERITY_INFO,
    \Papaya\Message::SEVERITY_WARNING,
    \Papaya\Message::SEVERITY_ERROR
  );

  /**
   * @param integer $type
   * @param string|\Papaya\UI\Text $message
   */
  public function __construct($type, $message) {
    $this->_isValidType($type);
    $this->_type = $type;
    $this->_message = $message;
  }

  /**
   * check if the given type is valid for this kind of messages
   *
   * @param int $type
   * @return bool
   */
  protected function _isValidType($type) {
    if (in_array($type, $this->_allowedTypes, FALSE)) {
      return TRUE;
    }
    throw new \InvalidArgumentException('Invalid message type.');
  }

  /**
   * Get type of message (info, warning, error)
   *
   * @return integer
   */
  public function getType() {
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
}
