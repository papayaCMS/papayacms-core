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

use Papaya\Content\Protocol\ProtocolEntry;
use Papaya\Content\Tables;
use Papaya\Database\Accessible as DatabaseBaseAccess;
use Papaya\Message;
use Papaya\URL\Current as CurrentURL;

/**
 * Papaya Message Dispatcher Database, handles messages logged to the database
 *
 * Make sure that the dispatcher does not initialize it's resources only if needed,
 * It will be created at the start of the script, unused initialization will slow the script down.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Database
  implements DatabaseBaseAccess, Message\Dispatcher {
  use DatabaseBaseAccess\Aggregation;

  private static $_SEVERITY_TYPES = [
    Message::SEVERITY_DEBUG => ProtocolEntry::SEVERITY_DEBUG,
    Message::SEVERITY_INFO => ProtocolEntry::SEVERITY_INFO,
    Message::SEVERITY_NOTICE => ProtocolEntry::SEVERITY_INFO,
    Message::SEVERITY_WARNING => ProtocolEntry::SEVERITY_WARNING,
    Message::SEVERITY_ERROR => ProtocolEntry::SEVERITY_ERROR,
    Message::SEVERITY_CRITICAL => ProtocolEntry::SEVERITY_ERROR,
    Message::SEVERITY_ALERT => ProtocolEntry::SEVERITY_ERROR,
    Message::SEVERITY_EMERGENCY => ProtocolEntry::SEVERITY_ERROR
  ];

  /**
   * Name of logging table
   *
   * @var string
   */
  private $_logTableName = Tables::LOG;

  /**
   * Used to prevent DB errors from recursion
   *
   * @var bool
   */
  protected $_preventMessageRecursion = FALSE;

  /**
   * Log messages to database
   *
   * @param Message $message
   *
   * @return bool
   */
  public function dispatch(Message $message) {
    if ($message instanceof Message\Logable && $this->allow($message)) {
      return $this->save($message);
    }
    return FALSE;
  }

  /**
   * Check if the current message should be logged
   *
   * @param Message|Message\Logable $message
   *
   * @return bool
   */
  public function allow(Message\Logable $message) {
    $options = $this->papaya()->options;
    if ($options->get('PAPAYA_PROTOCOL_DATABASE', FALSE)) {
      switch ($message->getSeverity()) {
        case Message::SEVERITY_DEBUG:
          return $options->get('PAPAYA_PROTOCOL_DATABASE_DEBUG', FALSE);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Save the message to database
   *
   * @param Message|Message\Logable $message
   *
   * @return bool
   */
  protected function save(Message\Logable $message) {
    $url = new CurrentURL();
    $options = $this->papaya()->options;
    $details = '<p>'.$message->getMessage().'</p>';
    if ($message->context() instanceof Message\Context\Interfaces\XHTML) {
      $details .= $message->context()->asXhtml();
    }
    $cookies = ($message instanceof Message\PHP\Error && !empty($_SERVER['HTTP_COOKIE']))
      ? $_SERVER['HTTP_COOKIE'] : '';
    $values = [
      'log_time' => \time(),
      'log_msgtype' => $message->getGroup(),
      'log_msgno' => isset(self::$_SEVERITY_TYPES[$message->getSeverity()]) ? self::$_SEVERITY_TYPES[$message->getSeverity()] : 0,
      'log_msg_short' => $message->getMessage(),
      'log_msg_long' => $details,
      'log_msg_uri' => $url->getURL(),
      'log_msg_referer' => empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'],
      'log_msg_cookies' => $cookies,
      'log_msg_script' => empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'],
      'log_msg_from_ip' => empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'],
      'log_version_papaya' => $options->get('PAPAYA_VERSION_STRING', ''),
      'log_version_project' => $options->get('PAPAYA_WEBSITE_REVISION', '')
    ];
    if ($this->papaya()->hasObject('AdministrationUser', FALSE) &&
      $this->papaya()->administrationUser->isLoggedIn()) {
      $values['user_id'] = $this->papaya()->administrationUser->getUserId();
      $values['username'] = $this->papaya()->administrationUser->getDisplayName();
    }
    if (!$this->_preventMessageRecursion) {
      $this->_preventMessageRecursion = TRUE;
      $databaseAccess = $this->getDatabaseAccess();
      $result = $databaseAccess->insertRecord(
        $databaseAccess->getTableName($this->_logTableName, TRUE), NULL, $values
      );
      $this->_preventMessageRecursion = FALSE;
      return FALSE !== $result;
    }
    return FALSE;
  }
}
