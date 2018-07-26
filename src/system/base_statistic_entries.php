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

if (!defined('PAPAYA_STATISTIC_TEMP_ENGINE')) {
  define('PAPAYA_STATISTIC_TEMP_ENGINE', 'MyISAM');
}

/**
* This class provides database access methods for processing statistic entries
* @package Papaya
* @subpackage Statistic
 * @deprecated
*/
class base_statistic_entries extends base_db_statistic {

  /**
  * @var integer $sessionLength length of a session in seconds (for ip based evaluation)
  */
  var $sessionLength = 7200; // 2 hours

  var $storeStack = array();

  var $totalSessionEntries = 0;

  /**
   * @var int
   */
  public $absCount = 0;

  /**
   * @var string
   */
  public $entryCounterGUID = NULL;

  /**
   * @var object
   */
  public $parentObj = NULL;

  /**
   * @var string
   */
  public $entryType = NULL;
  /**
   * @var string
   */
  public $cacheType = NULL;

  /**
  * PHP5 constructor
  */
  function __construct() {
    parent::__construct();
    $this->tableStatisticEntries = PAPAYA_DB_TABLEPREFIX.'_statistic_entries';
    $this->tableStatisticEntriesCache = PAPAYA_DB_TABLEPREFIX.'_statistic_entries_cache';
    $this->tableStatisticRequests = PAPAYA_DB_TABLEPREFIX.'_statistic_requests';
    $this->tableWorkingSessions = PAPAYA_DB_TABLEPREFIX.'_statistic_entries_sessions';
    $memoryLimit = @ini_get('memory_limit');
    if ($memoryLimit >= 0 && $memoryLimit < 67108864) {
      @ini_set('memory_limit', memory_get_usage() + 67108864);
    }
  }

  /**
  * get an instance of this class, singleton pattern
  *
  * @return object instance of base_statistic_entries
  */
  function &getInstance() {
    static $statistic;
    if (!(
          isset($statistic) &&
          is_object($statistic) &&
          is_a($statistic, 'base_statistic_entries')
        )) {
      $statistic = new base_statistic_entries;
    }
    return $statistic;
  }

  /**
   * get the last cache entry time for a given cache guid and type
   *
   * @param string $guid
   * @param string $type
   * @return FALSE|int $result FALSE on error, otherwise the timestamp of the latest
   *   cache entry
   */
  function getLastCacheEntryByType($guid, $type) {
    if ($guid != '' && $type != '') {
      $condition = $this->databaseGetSQLCondition(
        array('entry_cache_guid' => $guid, 'entry_cache_type' => $type)
      );
      $sql = "SELECT MAX(entry_cache_to)
              FROM %s
              WHERE $condition
            ";
      $params = array($this->tableStatisticEntriesCache);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        return $res->fetchField();
      }
    }
    return FALSE;
  }

  /**
  * get the first entry for a given module guid and type
  *
  * @param string $guid module guid
  * @param string $type entry type
  * @return mixed $result  FALSE on error, otherwise the timestamp of the oldest entry
  */
  function getFirstEntry($guid, $type) {
    $sql = "SELECT MIN(statistic_entry_time)
                FROM %s
              WHERE module_guid = '%s'
                AND statistic_entry_type = '%s'
           ";
    $params = array($this->tableStatisticEntries, $guid, $type);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return FALSE;
  }

  /**
  * This method initializes the temporary table used for storing sessions and
  * request ids for a day.
  */
  function initializeTemporaryTable() {
    static $created;
    if (!(isset($created) && $created)) {
      $created = TRUE;
      // $this->databaseQueryFmtWrite('DROP TABLE %s IF EXISTS', $this->tableWorkingSessions);
      switch (strtolower(PAPAYA_STATISTIC_TEMP_ENGINE)) {
      case 'innodb':
        $engine = 'InnoDB';
        break;
      case 'memory':
        $engine = 'MEMORY';
        break;
      default:
      case 'myisam':
        $engine = 'MyISAM';
        break;
      }
      $sql = "CREATE TEMPORARY TABLE %s (
                statistic_server_id INT(11) NOT NULL,
                statistic_request_id INT(11) NOT NULL,
                statistic_sid VARCHAR(32) NOT NULL,
                statistic_ip VARCHAR(32) NOT NULL,
                statistic_time INT(11) NOT NULL,
                PRIMARY KEY (statistic_server_id, statistic_request_id),
                KEY statistic_sid (statistic_sid),
                KEY statistic_time (statistic_time)
              ) ENGINE=$engine DEFAULT CHARSET=UTF8
            ";
      $params = array($this->tableWorkingSessions);
      if ($this->databaseQueryFmtWrite($sql, $params)) {
        return TRUE;
      }
    }
    $this->databaseQueryFmtWrite('TRUNCATE TABLE %s', $this->tableWorkingSessions);
    return FALSE;
  }

  /**
  * This method loads sessions and request_ids into the temporary table. This
  * improves the performance, since the records in question are not queried from
  * the requests table one at a time, possibly blocking writes, but only once
  * for each day.
  *
  * @param integer $uts timestamp identifying the currently processed day, i.e
  *   00:00 GMT of that day
  * @return boolean TRUE on success, otherwise FALSE
  */
  function loadSessionsForDay($uts) {
    if (isset($uts) && $uts > 0) {
      // set the start and endtime to 0:00:00 and 23:59:59 of the day specified by $uts
      $startTime = gmmktime(0, 0, 0, gmdate('m', $uts), gmdate('d', $uts), gmdate('Y', $uts));
      $endTime = $startTime + 86399; // a day
      if ($endTime + $this->sessionLength >= time()) {
        // sessions may not have been finished yet
        return FALSE;
      }

      $this->initializeTemporaryTable();

      $sql = "INSERT INTO %s (statistic_server_id, statistic_request_id,
                              statistic_sid, statistic_ip, statistic_time)
              SELECT DISTINCT r.statistic_server_id, r.statistic_request_id,
                     r.statistic_sid, r.statistic_ip,
                     r.statistic_time
                FROM %s AS r
                JOIN %s AS e USING(statistic_server_id, statistic_request_id)
              WHERE r.statistic_time BETWEEN %d AND %d
            ";
      $params = array(
        $this->tableWorkingSessions,
        $this->tableStatisticRequests,
        $this->tableStatisticEntries,
        $startTime - $this->sessionLength,
        $endTime + $this->sessionLength
      );
      if ($this->databaseQueryFmt($sql, $params)) {
        return TRUE;
      }
    }
    return FALSE;
  }


  /**
  * This method retrieves all sessions for a given day.
  *
  * @param integer $uts unix timestamp for the day (00:00 GMT is requested)
  * @return array $result list of sessions, array('ip' => array(sessionsbyip),
  *   'sid' => array(sessionsbysessionid))
  */
  function getSessionsForDay($uts) {
    $result = FALSE;
    if ($this->loadSessionsForDay($uts)) {

      if (isset($uts) && $uts > 0) {
        // set the start and endtime to 0:00:00 and 23:59:59 of the day specified by $uts
        $startTime = gmmktime(0, 0, 0, gmdate('m', $uts), gmdate('d', $uts), gmdate('Y', $uts));
        $endTime = $startTime + 86399; // a day
        if ($endTime + $this->sessionLength >= time()) {
          // sessions may not have been finished yet
          return FALSE;
        }

        // the parameters (table and the timeframe dates) are equal for session and ip
        $params = array(
          $this->tableWorkingSessions,
          $startTime - $this->sessionLength,
          $endTime + $this->sessionLength
        );

        $sqlSID = "SELECT s.statistic_sid, MIN(s.statistic_time) AS start,
                          MAX(s.statistic_time) AS end
                    FROM %s AS s
                    WHERE s.statistic_time >= %d
                      AND s.statistic_time <= %d
                    GROUP BY s.statistic_sid";

        // get the records that can be identified by session
        $resultSID = FALSE;
        if ($res = $this->databaseQueryFmt($sqlSID, $params)) {
          $resultSID = array();
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            // now only take those sessions, which start in the specified timeframe
            // i.e. drop those, that caused requests the day examined, but the
            // first request was a day before
            if ($row['statistic_sid'] != '' && $row['start'] >= $startTime) {
              $resultSID[$row['statistic_sid']] = $row;
            }
          }
        }

        $sqlIP = "SELECT s.statistic_ip, MIN(s.statistic_time) AS start,
                        MAX(s.statistic_time) AS end
                    FROM %s AS s
                  WHERE s.statistic_time >= %d
                    AND s.statistic_time <= %d
                    AND s.statistic_sid = ''
                  GROUP BY s.statistic_ip";

        $resultIP = FALSE;
        if ($res = $this->databaseQueryFmt($sqlIP, $params)) {
          $resultIP = array();
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            if ($row['start'] >= $startTime) {
              $resultIP[$row['statistic_ip']] = $row;
            }
          }
        }
        $result = array('sid' => $resultSID, 'ip' => $resultIP);
      }
    }
    return $result;
  }

  /**
  * loads statistic entries by session
  *
  * @param string $sessionType ip OR sid
  * @param string $guid entries module guid
  * @param string $identifier session ID or IP, depends on sessionType
  * @param integer $from unix timestamp start of timeframe
  * @param integer $to unix timestamp end of timeframe
  * @param string $type type of entries (yes, you can have multiple types per guid)
  * @param integer $offset not in use yet
  * @param integer $limit not in use yet
  * @return mixed $result entries for that session/type, FALSE on error
  */
  function getEntriesForSession(
    $sessionType, $guid, $identifier, $from, $to, $type = '', $offset = 0, $limit = 1000
  ) {
    $result = FALSE;

    // make sure there is a guid and a valid timespan
    if (isset($guid) && $guid != ''
        && $from > 0 && $to > 0 && $from < $to) {
      if (isset($type) && $type != '') {
        $typeCondition =
          ' AND '.$this->databaseGetSQLCondition('e.statistic_entry_type', $type);
      } else {
        $typeCondition = '';
      }
      if ($sessionType == 'sid') {
        $sessionCondition =
          ' AND '.$this->databaseGetSQLCondition('s.statistic_sid', $identifier);
      } elseif ($sessionType = 'ip') {
        $sessionCondition =
          ' AND '.$this->databaseGetSQLCondition('s.statistic_ip', $identifier);
      } else {
        // we need session information
        return FALSE;
      }
      $sql = "SELECT e.statistic_entry_id, e.statistic_request_id,
                     e.statistic_entry_time, e.statistic_entry_data,
                     s.statistic_sid, s.statistic_ip
                FROM %s s
                LEFT OUTER JOIN %s e
                  ON (s.statistic_request_id = e.statistic_request_id)
               WHERE e.module_guid = '%s'
                     $typeCondition
                     $sessionCondition
                 AND e.statistic_entry_time >= %d
                 AND e.statistic_entry_time <= %d
               ORDER BY e.statistic_entry_time ASC
            ";
      $params = array($this->tableWorkingSessions, $this->tableStatisticEntries,
        $guid, $from, $to);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        $this->absCount = $res->absCount();
        $result = array();
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $resultRows[] = $row;
        }
        // we separate this to reduce memory usage (got to verify this)
        if (isset($resultRows) && is_array($resultRows)) {
          foreach ($resultRows as $row) {
            $rowUnserialized = $row;
            $rowUnserialized['statistic_entry_data'] =
              unserialize($row['statistic_entry_data']);
            $result[] = $rowUnserialized;
          }
        }
      }
    }
    return $result;
  }

  /**
  * This method loads all entries of a given module and type for a day.
  *
  * @param integer $day unix timstamp of the day to process (GMT 00:00)
  * @return array $result list of statistic entries for that day and module/type
  */
  function getEntriesForDay($day) {
    $result = FALSE;

    if ($day > 0) {
      if (isset($this->entryType) && $this->entryType != '') {
        $typeCondition =
          ' AND '.$this->databaseGetSQLCondition('e.statistic_entry_type', $this->entryType);
      } else {
        $typeCondition = '';
      }
      $sql = "SELECT e.statistic_entry_id, e.statistic_request_id,
                      e.statistic_entry_time, e.statistic_entry_data,
                      r.statistic_sid, r.statistic_ip
                FROM %s e
                LEFT OUTER JOIN %s r
                  ON (r.statistic_request_id = e.statistic_request_id)
                WHERE module_guid = '%s'
                      $typeCondition
                  AND statistic_entry_time >= %d
                  AND statistic_entry_time <= %d
                ORDER BY statistic_entry_time ASC
            ";
      $params = array($this->tableStatisticEntries, $this->tableStatisticRequests,
        $this->entryCounterGUID, $day, $day + 86400);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        $this->absCount = $res->absCount();
        $result = array();
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $resultRows[] = $row;
        }
        // we separate this to reduce memory usage (got to verify this)
        if (isset($resultRows) && is_array($resultRows)) {
          foreach ($resultRows as $row) {
            $rowUnserialized = $row;
            $rowUnserialized['statistic_entry_data'] =
              unserialize($row['statistic_entry_data']);
            $result[] = $rowUnserialized;
          }
        }
      }
    }
    return $result;
  }

  /**
   * stores cached data in the statistics caching table
   *
   * @param string $guid
   * @param string $type
   * @param integer $from unix timestamp start of timeframe
   * @param integer $to unix timestamp end of timeframe
   * @param array $cacheData cache data as array (will be serialized)
   * @param boolean $serialize whether or not to serialize the data passed to this method,
   *                  if you want to store XML, use FALSE here
   * @param boolean $delayed whether or not to write the data immediatelly to the database
   *                  this is useful if you want to write a lot of enties; call flushStoreCache()
   *                  when you're done!
   * @param null $subject
   * @return boolean FALSE on error, TRUE on success
   */
  function storeCachedData(
    $guid, $type, $from, $to, $cacheData, $serialize = TRUE, $delayed = FALSE, $subject = NULL
  ) {
    // check whether a record exists
    $checkFields = array(
      'entry_cache_guid' => $guid,
      'entry_cache_type' => $type,
      'entry_cache_from' => $from,
      'entry_cache_to' => $to,
    );
    if ($subject != '') {
      $checkFields['entry_cache_subject'] = $subject;
    }

    $checkCondition = $this->databaseGetSQLCondition($checkFields);

    $sql = "SELECT entry_cache_id
              FROM %s
             WHERE $checkCondition
             ORDER BY entry_cache_id ASC
           ";
    $params = array($this->tableStatisticEntriesCache);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $count = 0;
      $cacheId = NULL;
      while ($id = $res->fetchField()) {
        $count++;
        $cacheId = $id;
      }
      if ($count > 1) {
        // there should be no duplicate records for one type/time, triggering an error
        $this->logMsg(
          MSG_ERROR,
          PAPAYA_LOGTYPE_SYSTEM,
          'Statistic caching error: multiple caches exist!',
          sprintf(
            'I found %d records for cache guid "%s", type "%s", '.
            'subject "%s" in the timeframe %s - %s. This should not happen.',
            $count,
            $guid,
            $type,
            (string)$subject,
            gmdate('Y-m-d H:i:s T', $from),
            gmdate('Y-m-d H:i:s T', $to)
          )
        );
      } elseif ($count == 1) {
        // update the existing record
        $entryCacheData = ($serialize) ? serialize($cacheData) : $cacheData;
        $data = array(
          'entry_cache_data' => $entryCacheData,
          'entry_cache_generated' => time(),
          'entry_cache_serialized' => (int)$serialize,
        );
        $condition = array(
          'entry_cache_id' => $cacheId
        );
        return FALSE !== $this->databaseUpdateRecord(
          $this->tableStatisticEntriesCache, $data, $condition
        );
      } else {
        // no record existed
        $entryCacheData = ($serialize) ? serialize($cacheData) : $cacheData;
        $data = array(
          // empty, since it cannot be null; will be removed after split to guid, type is finished
          'entry_cache_uri' => '',
          'entry_cache_guid' => $guid,
          'entry_cache_type' => $type,
          'entry_cache_subject' => (string)$subject,
          'entry_cache_from' => $from,
          'entry_cache_to' => $to,
          'entry_cache_data' => $entryCacheData,
          'entry_cache_generated' => time(),
          'entry_cache_serialized' => (int)$serialize,
        );
        if ($delayed) {
          $this->storeStack[] = $data;
          return TRUE;
        } else {
          return FALSE !== $this->databaseInsertRecord(
            $this->tableStatisticEntriesCache, NULL, $data
          );
        }
      }
    }
    return FALSE;
  }

  /**
  * writes the stacked entry cache records to the database
  * you can tell storeCachedData() to delay the insertion for performance reasons
  *
  * @return boolean whether or not the insertion succeeded
  */
  function flushStoreCache() {
    $result = FALSE;
    if (isset($this->storeStack) && is_array($this->storeStack) && count($this->storeStack) > 0) {
      $result = (
        FALSE !== $this->databaseInsertRecords(
          $this->tableStatisticEntriesCache, $this->storeStack
        )
      );
      if ($result) {
        $this->storeStack = array();
      }
    }
    return $result;
  }

  /**
  * load cached statistic data by its guid and type for a given timeframe
  *
  * @param string $guid entry module guid
  * @param string $type entry type, should be unique per module guid
  * @param integer $from unix timestamp start of timeframe
  * @param integer $to unix timestamp end of timeframe
  * @param string $subject optional subject for cache data (e.g. surfer, file, etc.)
  * @return mixed $result cached data as array, empty if none found, FALSE on error
  */
  function getCachedData($guid, $type, $from, $to, $subject = NULL) {
    $result = FALSE;
    $conditionParams = array('entry_cache_guid' => $guid, 'entry_cache_type' => $type);
    if ($subject != '') {
      $conditionParams['entry_cache_subject'] = $subject;
    }
    $condition = $this->databaseGetSQLCondition($conditionParams);
    $sql = "SELECT entry_cache_guid, entry_cache_type, entry_cache_subject,
                   entry_cache_from, entry_cache_to, entry_cache_data,
                   entry_cache_generated, entry_cache_serialized
              FROM %s
             WHERE $condition
               AND entry_cache_from >= %d
               AND entry_cache_to <= %d
           ";
    $params = array($this->tableStatisticEntriesCache, $from, $to);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $result = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (isset($result[$row['entry_cache_from']])) {
          $this->logMsg(
            MSG_ERROR,
            PAPAYA_LOGTYPE_MODULES,
            'Multiple statistic entry records found',
            sprintf(
              'There should be only on record for each timestamp of a type.'.
              ' type: %s; time from: %s',
              $row['entry_cache_type'],
              $row['entry_cache_from']
            )
          );
        } else {
          if ($row['entry_cache_serialized']) {
            $result[$row['entry_cache_from']] = unserialize($row['entry_cache_data']);
          } else {
            $result[$row['entry_cache_from']] = $row['entry_cache_data'];
          }
        }
      }
    }
    return $result;
  }

  /**
   * This method fetches the raw data, aggregates is and stores the result for
   * each day in the database
   *
   * It can be used by statistic modules to aggregate data for a specific entry type
   *
   * What happens?
   * - A statistic module calls this method with the appropriate parameters.
   * - This method finds the latest cached record or the first existing entry
   * - For each day, this method calls prepareDay on the calling object (e.g. to
   *   reset counters
   * - This method loads unproccessed sessions and entry records for each day
   *   and passes them to processSessionEntries of the calling object which has
   *   to do the aggregation part
   * - This method calls finalizeDay after each day is processed and the parent
   *   object should store the aggregated data using storeCachedData
   *
   * How it works:
   * - find the latest cached record time if exists, otherwise, get the first entry
   *   of the type we treat at the moment
   * - for each day since the calculated time
   *   - get all sessions
   *   - get all entries for the sessions
   *   - aggregate the entries data
   *   - store the aggregated data in the caching table
   *
   * @param object $parentObj the statistic module object calling
   * @param string $entryCounterGUID the module guid that issued the statistic entry
   * @param string $entryType the type of entry that should be loaded
   * @param string $cacheType the cached entry type (to find out what is already there and continue)
   * @param bool $bySession
   * @return bool
   */
  function processUncachedRecords(
    $parentObj, $entryCounterGUID, $entryType, $cacheType, $bySession = TRUE
  ) {
    if (is_object($parentObj)) {
      if ((
           ($bySession && method_exists($parentObj, 'processSessionEntries')) ||
           (!$bySession && method_exists($parentObj, 'processDayEntries'))
          ) &&
          method_exists($parentObj, 'finalizeDay')) {
        // make the parameters available to other methods (processSessions)
        $this->parentObj = $parentObj;
        $this->entryCounterGUID = $entryCounterGUID;
        $this->entryType = $entryType;
        $this->cacheType = $cacheType;

        $startAt = $this->getStartTime();

        // we continue from the calculated start time until today and because
        // only complete days are aggregated, it's effectively until yesterday
        for ($i = $startAt; $i < time(); $i += 86400) {

          // calculate the actual day borders for the current day being processed
          $startTime = gmmktime(0, 0, 0, gmdate('m', $i), gmdate('d', $i), gmdate('Y', $i));
          $endTime = $startTime + 86399;

          if (method_exists($parentObj, 'prepareDay')) {
            $parentObj->prepareDay($startTime, $endTime);
          }

          $this->processDay($i, $bySession);

          $parentObj->finalizeDay($startTime, $endTime);
        }
      } else {
        $this->papaya()->messages->log(
          \PapayaMessageLogable::GROUP_SYSTEM,
          Papaya\Message::SEVERITY_ERROR,
          sprintf($this->_gt('Missing method in class "%s".'), get_class($parentObj)),
          sprintf(
            $this->_gt(
              'The class "%s" needs to implement the method(s) "%s" in order to work properly.'
            ),
            get_class($parentObj),
            'processSessionEntries, finalizeDay'
          )
        );
        return FALSE;
      }
    } else {
      // parameter parentObj is no object
      return FALSE;
    }
    return TRUE;
  }

  /**
  * This method checks the database to determine, where to start/continue
  *
  * @return integer $result timestamp to start at with aggregating
  */
  function getStartTime() {
    // determine where to start with updating the cache
    $result = $this->getLastCacheEntryByType($this->entryCounterGUID, $this->cacheType);
    if (!$result) {
      // we need the time of the first entry of this guid/type
      $result = $this->getFirstEntry($this->entryCounterGUID, $this->entryType);
    }
    if (!$result) {
      // oh, there are no entries for the given entry type, so we return the current time
      $result = time();
    }
    return $result;
  }

  /**
   * This method processes entries for a session, i.e. aggregates the data therein
   *
   * @see base_statistic_entries::processUncachedRecords()
   * @param array $sessions
   * @param string $type ip|sid which type of session we have, for loading entries via requests
   * @internal param array $sessionEntries sessions
   */
  function processSessions($sessions, $type) {
    if (isset($sessions) && is_array($sessions) && count($sessions) > 0
        && ($type == 'ip' || $type = 'sid')) {
      $noRecords = 0;
      $currentSession = 0;
      $numberOfSessions = count($sessions);
      $sessionNumberLength = strlen($numberOfSessions);
      foreach ($sessions as $identifier => $session) {
        $currentSession++;
        $entries = $this->getEntriesForSession(
          $type,
          $this->entryCounterGUID,
          $identifier,
          $session['start'],
          $session['end'],
          $this->entryType
        );
        if (is_array($entries) && count($entries) > 0) {
          base_cronjob::cronDebug(
            sprintf(
              'processing session #%0'.$sessionNumberLength.'d '.
              'of %d (%01.02f%%) by %s with %5d records',
              $currentSession,
              $numberOfSessions,
              ($currentSession / $numberOfSessions * 100),
              $type,
              count($entries)
            )
          );
          $this->totalSessionEntries += count($entries);
          $this->parentObj->processSessionEntries($entries);
        } else {
          $noRecords++;
        }
      }
      base_cronjob::cronOutput($noRecords.' sessions had no entry records');
    } else {
      base_cronjob::cronOutput('no sessions given');
    }
  }

  /**
  * This method loads sessions (if applicable) and entries for a day and calls
  * the processing methods on the specific statistic module.
  *
  * @param integer $day unix timstamp of the day to process (GMT 00:00)
  * @param boolean $bySession whether or not to preserve session information
  */
  function processDay($day, $bySession) {
    if ($bySession) {
      // load the sessions for a day ($i may be any time of a day, the borders
      // are calculated from that time
      if ($sessions = $this->getSessionsForDay($day)) {

        if (isset($sessions) && is_array($sessions)) {
          if (isset($sessions['sid']) && is_array($sessions['sid'])) {
            base_cronjob::cronOutput(
              sprintf(
                'Processing %d sid sessions on %s',
                count($sessions['sid']),
                gmdate('Y-m-d', $day)
              )
            );
            // we aggregate all entries identified by a session id
            $this->processSessions($sessions['sid'], 'sid');
          }
          if (isset($sessions['ip']) && is_array($sessions['ip'])) {
            base_cronjob::cronOutput(
              sprintf(
                'Processing %d ip sessions on %s',
                count($sessions['ip']),
                gmdate('Y-m-d', $day)
              )
            );
            // we aggregate all entries identified by their ip (and a timeframe),
            $this->processSessions($sessions['ip'], 'ip');
          }
        }
      }
    } else {
      $entries = $this->getEntriesForDay($day);
      if (count($entries) > 0) {
        base_cronjob::cronOutput(
          sprintf(
            'Processing %d entries on %s',
            count($entries),
            gmdate('Y-m-d', $day)
          )
        );
        $this->parentObj->processDayEntries($entries);
      }
    }
  }
}

