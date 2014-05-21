<?php
/**
* Papaya Message Log, standard log message class
*
* The message may not be translated and must be in english.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Messages
* @version $Id: Log.php 39430 2014-02-28 09:21:51Z weinert $
*/

/**
* Papaya Message Log, standard log message class
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageLog
  implements PapayaMessageLogable {

  /**
  * Message group
  */
  protected $_group = PapayaMessageLogable::GROUP_SYSTEM;

  /**
  * Message type
  * @var integer
  */
  protected $_type = PapayaMessage::SEVERITY_INFO;

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
   * PapayaMessageLog constructor
   *
   * @param integer $group
   * @param integer $type
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
  * @return integer
  */
  public function getGroup() {
    return $this->_group;
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

  /**
  * Return a context object containing additional data about where and why the message happened.
  *
  * @return PapayaMessageContextGroup
  */
  public function context() {
    if (is_null($this->_context)) {
      $this->_context = new PapayaMessageContextGroup();
    }
    return $this->_context;
  }

  /**
   * Set a context group object to the message.
   *
   * @param PapayaMessageContextGroup $context
   */
  public function setContext(PapayaMessageContextGroup $context) {
    $this->_context = $context;
  }
}
