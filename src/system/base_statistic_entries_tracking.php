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
* This class provides means of tracking events that will be evaluated statistically.
* @package Papaya
* @subpackage Statistic
 * @deprecated
*/
class base_statistic_entries_tracking extends base_db_statistic {

  var $logEntries = array();

  /**
  * @var integer $statisticRequestId the general statistic page request log id
  */
  var $statisticRequestId = NULL;

  /**
  * generates an instance of the base_statistic_entries_tracking class, singleton
  *
  * you may not need this, logEntry can be called directly
  *
  * @return object single instance of base_statistic_entries_tracking
  */
  public static function getInstance() {
    static $statistic = NULL;
    if (!isset($statistic)) {
      $statistic = new base_statistic_entries_tracking;
    }
    return $statistic;
  }

  /**
  * logs the occurance of an event with any parameters necessary
  *
  * may be called outside an instance of this class,
  * i.e. base_statistic_entries_tracking::logEntry()
  *
  * @param string $guid module guid
  * @param string $entryType the type of event, use a readable english word/verb
  * @param mixed $parameters whatever parameters concern this event;
  *                          may be an associative array, string or integer
  *
  * @return boolean TRUE, if the new entry had been added successfully, else FALSE
  */
  public static function logEntry($guid, $entryType, $parameters) {
    if (isset($guid) && $guid != '' && isset($entryType) && $entryType != '') {
      $statObj = base_statistic_entries_tracking::getInstance();
      /*
      * statistic_request_id relates the entry to the generic statistic entry and may
      *                      be used for further information, as useragent, session, etc.
      *                      it will be set later in flushLog, since it's created later
      * statistic_entry_time is the current timestamp,
      *                      redundant to request_id for faster access
      * statistic_entry_data whatever data a log process needs is stored serialized here
      */
      $data = array(
        'statistic_request_id' => NULL,
        'statistic_server_id' => PAPAYA_WEBSERVER_IDENT,
        'module_guid' => $guid,
        'statistic_entry_type' => $entryType,
        'statistic_entry_time' => time(),
        'statistic_entry_data' => serialize($parameters),
      );
      $statObj->logEntries[] = $data;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * flushes previously triggered statistic entries buffered in statObj->logEntries
  *
  * @param integer $requestId the page requests ID for reference; this implies
  *                           the request was valid and should be tracked
   * @return boolean was logged
  */
  function flushLog($requestId) {
    $statObj = base_statistic_entries_tracking::getInstance();
    // We need a request id (i.e. request was loggable), otherwise we got no
    // reference to additional data like ip and session. This should only occur
    // using the preview mode, which should not be counted anyway.
    if ($requestId != 0 && isset($statObj->logEntries) &&
        is_array($statObj->logEntries) && count($statObj->logEntries) > 0) {
      foreach ($statObj->logEntries as $key => $entry) {
        $statObj->logEntries[$key]['statistic_request_id'] = $requestId;
      }
      $inserted = $statObj->databaseInsertRecords(
        $statObj->tableStatisticEntries, $statObj->logEntries
      );
      if (FALSE !== $inserted) {
        $statObj->logEntries = array();
        return TRUE;
      } else {
        $statObj->logMsg(
          MSG_ERROR,
          PAPAYA_LOGTYPE_MODULES,
          'Error storing entries data (Query failed)',
          sprintf('The DB query for inserting statistic entry records failed.')
        );
      }
    }
    return FALSE;
  }

  /**
   * gets the last request id in the specified session
   *
   * @param string $sessionId
   * @return mixed request id OR FALSE if no request id is available
   */
  function getLastRequestIdBySession($sessionId) {
    $statObj = base_statistic_entries_tracking::getInstance();
    $sql = "SELECT MAX(statistic_request_id) as request_id
            FROM %s
            WHERE statistic_sid = '%s'";
    $params = array(
      $statObj->tableStatisticRequests,
      $sessionId
    );
    /**
     * @var boolean|\Papaya\Database\Result $res
     */
    if ($res = $statObj->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(\Papaya\Database\Result::FETCH_ASSOC)) {
        return ($row['request_id']);
      }
    }
    return FALSE;
  }

}
