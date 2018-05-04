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

/**
* PapayaMessageDebug, standard debug message
*
* Contains an optional message, the relative runtime to the last debug,
* informations memory about the memory consumption and a backtrace.
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageDebug
  implements PapayaMessageLogable {

  /**
  * Message group
  */
  protected $_group = PapayaMessageLogable::GROUP_DEBUG;
  /**
  * Message text
  * @var string
  */
  protected $_message = '';

  /**
  * Message context
  * @var NULL|PapayaMessageContextGroup
  */
  protected $_context = NULL;

  /**
  * PapayaMessageDebug constructor
  *
  * @param integer $group
  * @param string $message
  */
  public function __construct($group = PapayaMessageLogable::GROUP_DEBUG, $message = '') {
    $this->_group = $group;
    $this->_message = $message;
    $this->_context = new \PapayaMessageContextGroup();
    $this
      ->_context
      ->append(new \PapayaMessageContextMemory())
      ->append(new \PapayaMessageContextRuntime())
      ->append(new \PapayaMessageContextBacktrace(1));
  }

  /**
  * Get group of message (system, php, content, ...)
  *
  * @return integer
  */
  public function getGroup() {
    return $this->_group;
  }

  /**
  * Get type of message, always "debug" for this class
  *
  * @return integer
  */
  public function getType() {
    return PapayaMessage::SEVERITY_DEBUG;
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
  * @return PapayaMessageContextGroup
  */
  public function context() {
    return $this->_context;
  }
}
