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
  implements \Papaya\Message\Dispatcher {

  /**
   * Name of logging table
   *
   * @var string
   */
  private $_logTableName = 'log';

  /**
   * Used to prevent DB errors from recursion
   *
   * @var boolean
   */
  protected $_preventMessageRecursion = FALSE;

  /**
   * Log messages to database
   *
   * @param \Papaya\Message $message
   * @return boolean
   */
  public function dispatch(\Papaya\Message $message) {
    if ($message instanceof \Papaya\Message\Logable) {
      if ($this->allow($message)) {
        return $this->save($message);
      }
    }
    return FALSE;
  }

  /**
   * Check if the current message should be logged
   *
   * @param \Papaya\Message|\Papaya\Message\Logable $message
   * @return bool
   */
  public function allow(\Papaya\Message\Logable $message) {
    $options = $this->papaya()->options;
    if ($options->get('PAPAYA_PROTOCOL_DATABASE', FALSE)) {
      switch ($message->getType()) {
        case \Papaya\Message::SEVERITY_DEBUG:
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
   * @param \Papaya\Message|\Papaya\Message\Logable $message
   * @return bool
   */
  protected function save(\Papaya\Message\Logable $message) {
    $url = new \Papaya\Url\Current();
    $options = $this->papaya()->options;
    $details = '<p>'.$message->getMessage().'</p>';
    if ($message->context() instanceof \Papaya\Message\Context\Interfaces\Xhtml) {
      $details .= $message->context()->asXhtml();
    }
    $cookies = ($message instanceof \Papaya\Message\PHP\Error && !empty($_SERVER['HTTP_COOKIE']))
      ? $_SERVER['HTTP_COOKIE'] : '';
    $values = array(
      'log_time' => time(),
      'log_msgtype' => $message->getGroup(),
      'log_msgno' => $message->getType(),
      'log_msg_short' => $message->getMessage(),
      'log_msg_long' => $details,
      'log_msg_uri' => $url->getUrl(),
      'log_msg_referer' => empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'],
      'log_msg_cookies' => $cookies,
      'log_msg_script' => empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'],
      'log_msg_from_ip' => empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'],
      'log_version_papaya' => $options->get('PAPAYA_VERSION_STRING', ''),
      'log_version_project' => $options->get('PAPAYA_WEBSITE_REVISION', '')
    );
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
