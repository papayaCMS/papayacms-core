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
 * Standard debug message
 *
 * Contains an optional message, the relative runtime to the last debug,
 * information memory about the memory consumption and a backtrace.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Debug
  implements Logable {
  /**
   * Message group
   */
  protected $_group = Logable::GROUP_DEBUG;

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
   * @param int $group
   * @param string $message
   */
  public function __construct($group = Logable::GROUP_DEBUG, $message = '') {
    $this->_group = $group;
    $this->_message = $message;
    $this->_context = new Context\Group();
    $this
      ->_context
      ->append(new Context\Memory())
      ->append(new Context\Runtime())
      ->append(new Context\Backtrace(1));
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
   * Get type of message (info, warning, error)
   *
   * @return int
   */
  public function getSeverity() {
    return Message::SEVERITY_DEBUG;
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
   * Return the context object containing additional data about where and why the message happened.
   *
   * @return Context\Group
   */
  public function context() {
    return $this->_context;
  }
}
