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

use Papaya\Message;

/**
 * Papaya Message Dispatcher Database, handles messages logged to the database
 *
 * Make sure that the dispatcher does not initialize it's resources only if needed,
 * It will be created at the start of the script, unused initialzation will slow the script down.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Database
  extends \Papaya\Database\BaseObject
  implements Message\Dispatcher {
  private static $_SEVERITY_TYPES = [
    Message::SEVERITY_DEBUG => 3,
    Message::SEVERITY_INFO => 0,
    Message::SEVERITY_NOTICE => 0,
    Message::SEVERITY_WARNING => 1,
    Message::SEVERITY_ERROR => 2,
    Message::SEVERITY_CRITICAL => 2,
    Message::SEVERITY_ALERT => 2,
    Message::SEVERITY_EMERGENCY => 2
  ];

  /**
   * Name of logging table
   *
   * @var string
   */
  private $_logTableName = 'log';

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
    if ($message instanceof Message\Logable) {
      if ($this->allow($message)) {
        return $this->save($message);
      }
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
    } else {
      return FALSE;
    }
  }

  /**
   * Save the message to database
   *
   * @param Message|Message\Logable $message
   *
   * @return bool
   */
  protected function save(Message\Logable $message) {
    $url = new \Papaya\URL\Current();
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
      $result = $this->databaseInsertRecord(
        $this->databaseGetTableName($this->_logTableName, TRUE), NULL, $values
      );
      $this->_preventMessageRecursion = FALSE;
      return FALSE !== $result;
    }
    return FALSE;
  }
}
