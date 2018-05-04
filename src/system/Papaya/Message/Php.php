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
* Papaya Message Php, superclass for log messages for php erorrs and exceptions
*
* A log message with the ability to convert the php severity to a log message type.
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessagePhp
  implements PapayaMessageLogable {

  /**
  * Message type
  * @var integer
  */
  protected $_type = PapayaMessage::SEVERITY_ERROR;

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
  * Mapping PHP error levels to message types
  * @var array
  */
  private $_errors = array(
    E_ERROR => PapayaMessage::SEVERITY_ERROR,
    E_USER_ERROR => PapayaMessage::SEVERITY_ERROR,
    E_RECOVERABLE_ERROR => PapayaMessage::SEVERITY_ERROR,
    E_WARNING => PapayaMessage::SEVERITY_WARNING,
    E_USER_WARNING => PapayaMessage::SEVERITY_WARNING,
    E_NOTICE => PapayaMessage::SEVERITY_INFO,
    E_USER_NOTICE => PapayaMessage::SEVERITY_INFO
  );

  /**
  * Create context subobject, too
  */
  public function __construct() {
    $this->_context = new \PapayaMessageContextGroup();
  }

  /**
  * Set type from severity
  *
  * @param integer $severity
  */
  public function setSeverity($severity) {
    if (isset($this->_errors[$severity])) {
      $this->_type = $this->_errors[$severity];
    }
  }

  /**
  * Get group of message (system, php, content, ...)
  *
  * @return integer
  */
  public function getGroup() {
    return PapayaMessageLogable::GROUP_PHP;
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
    return $this->_message;
  }

  /**
  * Return a context object containing additional data about where and why the message happened.
  *
  * @return PapayaMessageContextGroup
  */
  public function context() {
    return $this->_context;
  }

  /**
   * Set a context group object to the message.
   *
   * @param PapayaMessageContextGroup $context
   * @internal param $PapayaMessageContext
   */
  public function setContext(PapayaMessageContextGroup $context) {
    $this->_context = $context;
  }
}
