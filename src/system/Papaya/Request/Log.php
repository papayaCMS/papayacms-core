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

namespace Papaya\Request;
/**
 * Request log, a debugging object, colleting and omitting informations about the events during the
 * request processing.
 *
 * @package Papaya-Library
 * @subpackage Request
 */
class Log extends \Papaya\Application\BaseObject {

  /**
   * Same instance to make it usable like a singleton
   *
   * @var \PapayaRequestLog
   */
  private static $_instance = NULL;

  /**
   * Time the object instance was created.
   *
   * @var float
   */
  private $_startTime = 0;

  /**
   * Last time an event was logged
   *
   * @var float
   */
  private $_previousTime = 0;

  /**
   * Logged event messages
   *
   * @var array(string)
   */
  private $_events = array();

  /**
   * Construct object and initialize start time.
   */
  public function __construct() {
    $now = microtime(TRUE);
    $dateString = date('Y-m-d H:i:s:');
    $dateString .= round(($now - (int)$now) * 1000);
    $this->_startTime = $now;
    $this->_events[] = 'Started at '.$dateString;
  }

  /**
   * Get singletone instance for this object
   *
   * This object can be used like a singleton, or created normally.
   *
   * @param boolean $reset create new instance
   * @return \PapayaRequestLog
   */
  public static function getInstance($reset = FALSE) {
    if (is_null(self::$_instance) || $reset) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Log an event an the time
   *
   * @param string $message
   */
  public function logTime($message) {
    $now = microtime(TRUE);
    $message .= ' after '.\Papaya\Utility\Date::periodToString(
        $now - $this->_startTime
      );
    if ($this->_previousTime > 0) {
      $message .= ' (+'.\Papaya\Utility\Date::periodToString(
          $now - $this->_previousTime
        ).')';
    }
    $this->_previousTime = $now;
    $this->_events[] = $message;
  }

  /**
   * Emit request log to message system.
   *
   * @param bool $stop Add an additional stop event
   */
  public function emit($stop = TRUE) {
    if ($stop) {
      $this->logTime('Stopped');
    }
    $log = new \Papaya\Message\Log(
      \Papaya\Message\Logable::GROUP_DEBUG,
      \Papaya\Message::SEVERITY_DEBUG,
      'Request Log'
    );
    foreach ($this->_events as $event) {
      $log
        ->context()
        ->append(
          new \Papaya\Message\Context\Text($event)
        );
    }
    $log
      ->context()
      ->append(
        new \Papaya\Message\Context\Memory()
      );
    $this->papaya()->messages->dispatch($log);
  }
}
