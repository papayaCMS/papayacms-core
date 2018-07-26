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
* @package Papaya
* @subpackage Statistic
*/
class base_statistic_actions extends base_db_statistic {

  var $logEntries = array();

  /**
  * PHP5 constructor
  *
  * @access public
  */
  function __construct() {
    parent::__construct();
    $this->tableStatisticActions = PAPAYA_DB_TABLEPREFIX.'_statistic_actions';
    $this->tableTopicPublicTrans = PAPAYA_DB_TABLEPREFIX.'_topic_public_trans';
  }

  /**
  * generates an instance of the base_statistic_actions class, singleton
  *
  * you may not need this, logAction can be called directly
  *
  * @return object single instance of base_statistic_actions
  */
  function &getInstance() {
    static $statistic;
    if (isset($statistic) &&
        is_object($statistic) &&
        is_a($statistic, 'base_statistic_actions')) {
      return $statistic;
    } else {
      $statistic = new base_statistic_actions;
      return $statistic;
    }
  }


  /**
  * Logs page request
  *
  * @param string $guid module GUID
  * @param integer $actionId action id of module object
  * @param string $message message added to action
  * @param string $refererPage optional, default value ''
  * @param string $refererParams optional, default value ''
  * @access public
  * @return boolean action logged?!
  */
  function logAction($guid, $actionId, $message, $refererPage = '', $refererParams = '') {
    $statObj = base_statistic_actions::getInstance();
    if (!\PapayaUtilServerAgent::isRobot()) {
      $data['statistic_action_id'] = NULL;
      $data['statistic_server_id'] = PAPAYA_WEBSERVER_IDENT;
      $data['module_guid'] = $guid;
      $data['statistic_moduleaction_id'] = $actionId;
      $data['statistic_referer_page'] = $refererPage;
      $data['statistic_referer_params'] = $refererParams;
      $data['statistic_request_id'] = NULL;
      $data['statistic_message'] = $message;
      $data['statistic_action_time'] = time();
      $statObj->logEntries[] = $data;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * flushes previously triggered statistic actions buffered in statObj->logEntries
  */
  function flushLog($requestId) {
    $statObj = base_statistic_actions::getInstance();
    if ($requestId != 0 && isset($statObj->logEntries) &&
        is_array($statObj->logEntries) && count($statObj->logEntries) > 0) {
      foreach ($statObj->logEntries as $key => $entry) {
        $statObj->logEntries[$key]['statistic_request_id'] = $requestId;
      }
      $inserted = $statObj->databaseInsertRecords(
        $statObj->tableStatisticActions, $statObj->logEntries
      );
      if (FALSE !== $inserted) {
        $statObj->logEntries = array();
        return TRUE;
      } else {
        $statObj->logMsg(
          MSG_ERROR,
          PAPAYA_LOGTYPE_MODULES,
          'Error storing statistic actions data (Query failed)',
          sprintf('The DB query for inserting action records failed.')
        );
        return FALSE;
      }
    }
    return FALSE;
  }

  /**
   * Fetches n most executed actions for module (guid) and action (actionId)
   *
   * @param integer $n top n results
   * @param string $guid module guid
   * @param integer $actionId id of action for selected module
   * @param null $from
   * @param null $to
   * @access public
   * @return array $result database results:
   *   count, topic_id, categ_id, statistic_moduleaction_params
   */
  function getModuleAction($n, $guid, $actionId, $from = NULL, $to = NULL) {
    $result = array();
    if ($from && $to) {
      $timeCondition = sprintf(
        " AND statistic_action_time >= %d AND statistic_action_time < %d ",
        (int)$from,
        (int)$to
      );
    } else {
      $timeCondition = '';
    }
    $sql = "SELECT COUNT(*) AS
                   count, statistic_moduleaction_id, statistic_message,
                   statistic_referer_page, statistic_referer_params,
                   statistic_action_time
              FROM %s
             WHERE module_guid = '%s' AND statistic_moduleaction_id = '%d'
                   $timeCondition
             GROUP BY statistic_referer_page, statistic_referer_params
             LIMIT 0, %d";
    $params = array($this->tableStatisticActions, $guid, $actionId, $n);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
    }
    return $result;
  }

}
