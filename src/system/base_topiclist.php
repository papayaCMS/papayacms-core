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
* Create topic list
*
* @package Papaya
* @subpackage Core
*/
class base_topiclist extends base_db {

  const SORT_RANDOM = 2;

  const SORT_DEFAULT_ASCENDING = 0;
  const SORT_DEFAULT_DESCENDING = 1;
  const SORT_WEIGHT_ASCENDING = 3;
  const SORT_WEIGHT_DESCENDING = 4;
  const SORT_CREATED_ASCENDING = 5;
  const SORT_CREATED_DESCENDING = 6;
  const SORT_PUBLISHED_ASCENDING = 7;
  const SORT_PUBLISHED_DESCENDING = 8;

  /**
  * Database table topics
  *
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;

  /**
  * Database table public topics
  *
  * @var string $tableTopics
  */
  var $tableTopicsPublic = PAPAYA_DB_TBL_TOPICS_PUBLIC;
  /**
  * Database table topics translations
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;

  /**
  * Database table public topics translations
  *
  * @var string $tableTopics
  */
  var $tableTopicsPublicTrans = PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS;
  /**
  * Topics
  * @var array $topics
  */
  var $topics;
  /**
  * Parent topic id
  * @var integer $parentTopicId
  */
  var $parentTopicId = 0;
  /**
  * Lanuage id
  * @var integer $lngId
  */
  var $lngId = 0;

  var $tableTagLinks = PAPAYA_DB_TBL_TAG_LINKS;
  /**
  * Table with Module classes
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * Table with views
  * @var string $tableViews
  */
  var $tableViews = PAPAYA_DB_TBL_VIEWS;
  /**
  * Table with users
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;

  /**
  * Constructor
  *
  * @access public
  */
  function __construct() {
    $this->baseLink = $this->getBaseLink();
  }

  /**
   * Load list from database
   *
   * @param $parentTopicId
   * @param $lngId
   * @param boolean $publishedOnly optional, default value TRUE
   * @param bool|int $sort optional, default value 0 (ASC)
   * @param integer $max optional, default value 1000
   * @param int $offset
   * @access public
   */
  function loadList(
    $parentTopicId, $lngId, $publishedOnly = TRUE, $sort = 0, $max = 1000, $offset = 0
  ) {
    $this->lngId = $lngId;
    $iPrevTopicId = (int)$parentTopicId;
    $this->parentTopicId = $iPrevTopicId;
    switch ((int)$sort) {
    case self::SORT_DEFAULT_ASCENDING :
      $order = $this->getOrderBySql(self::SORT_WEIGHT_ASCENDING);
      break;
    case self::SORT_DEFAULT_DESCENDING :
      $order = $this->getOrderBySql(self::SORT_WEIGHT_DESCENDING);
      break;
    default :
      $order = $this->getOrderBySql($sort);
      break;
    }
    if ($publishedOnly) {
      $tableTopics = $this->tableTopicsPublic;
      $tableTrans = $this->tableTopicsPublicTrans;
      $publishedFilter = " AND (t.published_from = t.published_to
        OR (t.published_from <= '%d' AND t.published_to >= '%d'))";
    } else {
      $publishedFilter = '';
      $tableTopics = $this->tableTopics;
      $tableTrans = $this->tableTopicsTrans;
    }
    $sql = "SELECT t.topic_id, t.prev, t.prev_path, t.topic_weight,
                   t.topic_mainlanguage,
                   t.topic_created, t.topic_modified,
                   t.topic_cachemode, t.topic_cachetime,
                   t.box_useparent, t.meta_useparent, t.surfer_useparent,
                   tt.topic_title
              FROM %s t, %s tt
             WHERE t.prev = '%s' AND tt.topic_id = t.topic_id
               AND tt.lng_id = %d
               $publishedFilter
             ORDER BY $order";
    $now = time();
    $params = array(
      $tableTopics,
      $tableTrans,
      $iPrevTopicId,
      $lngId,
      $now,
      $now
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $max, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topics[$row['topic_id']] = $row;
      }
    }
  }

  /**
  * Loads a list of topics by tag id.
  *
  * @param $tagId int ID of the tag linked with the topics
  * @param $lngId int ID of the content language.
  * @param $publishedOnly boolean TRUE (default value) if only published topics have to be selected,
  *        otherwise FALSE.
  * @param $sort int Defines order in which topics will be sorted. '0' is ascending,
  *        '1' is descending, and '2' is random order.
  * @param $max int Maximum numbers of topics to load. Default value is "1000".
  * @param $offset int Default value is 0
  */
  function loadListByTag(
    $tagId, $lngId, $publishedOnly = TRUE, $sort = 0, $max = 1000, $offset = 0
  ) {
    $this->lngId = $lngId;
    switch ((int)$sort) {
    case self::SORT_DEFAULT_ASCENDING :
      $order = $this->getOrderBySql(self::SORT_CREATED_ASCENDING);
      break;
    case self::SORT_DEFAULT_DESCENDING :
      $order = $this->getOrderBySql(self::SORT_CREATED_DESCENDING);
      break;
    default :
      $order = $this->getOrderBySql($sort);
      break;
    }
    if ($publishedOnly) {
      $tableTopics = $this->tableTopicsPublic;
      $tableTrans = $this->tableTopicsPublicTrans;
      $publishedFilter = " AND (t.published_from = t.published_to
        OR (t.published_from <= '%d' AND t.published_to >= '%d'))";
    } else {
      $publishedFilter = ' AND t.is_deleted = 0 ';
      $tableTopics = $this->tableTopics;
      $tableTrans = $this->tableTopicsTrans;
    }
    $tagCondition = ' AND '.$this->databaseGetSQLCondition('tag_id', $tagId);
    $sql = "SELECT t.topic_id, t.prev, t.prev_path, t.topic_weight,
                   t.topic_mainlanguage,
                   t.topic_created, t.topic_modified,
                   t.topic_cachemode, t.topic_cachetime,
                   t.box_useparent, t.meta_useparent, t.surfer_useparent,
                   tt.topic_title
              FROM (%s t, %s tt)
              LEFT OUTER JOIN %s tg ON (tg.link_type = 'topic' AND tg.link_id = t.topic_id)
             WHERE tt.topic_id = t.topic_id
               AND tt.lng_id = %d
               $publishedFilter
               $tagCondition
             ORDER BY $order";
    $now = time();
    $params = array(
      $tableTopics,
      $tableTrans,
      $this->tableTagLinks,
      $lngId,
      $now,
      $now
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $max, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topics[$row['topic_id']] = $row;
      }
    }
  }

  private function getOrderBySql($sort) {
    switch ($sort) {
    case self::SORT_RANDOM :
      return $this->databaseGetSQLSource('RANDOM');
    case self::SORT_WEIGHT_ASCENDING :
      return "t.topic_weight ASC, tt.topic_title ASC";
    case self::SORT_WEIGHT_DESCENDING :
      return "t.topic_weight DESC, tt.topic_title ASC";
    case self::SORT_CREATED_ASCENDING :
      return "t.topic_created ASC, tt.topic_title ASC";
    case self::SORT_CREATED_DESCENDING :
      return "t.topic_created DESC, tt.topic_title ASC";
    case self::SORT_PUBLISHED_ASCENDING :
      return "t.topic_modified ASC, tt.topic_title ASC";
    case self::SORT_PUBLISHED_DESCENDING :
      return "t.topic_modified DESC, tt.topic_title ASC";
    }
    return 'tt.topic_title ASC';
  }

  /**
  * Loads the actual content of the current topics
  *
  * @return boolean TRUE on success, otherwise FALSE
  */
  function loadTopicTranslatedData() {
    if (isset($this->topics) &&
        is_array($this->topics) &&
        count($this->topics) > 0) {
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('topic_id', array_keys($this->topics))
      );
      $sql = "SELECT tt.topic_id, tt.topic_title, tt.topic_content, tt.lng_id,
                     tt.topic_trans_created, tt.topic_trans_modified,
                     tt.meta_title, tt.meta_keywords, tt.meta_descr,
                     tt.view_id, v.view_title, v.view_name,
                     m.module_guid , m.module_title, m.module_path,
                     m.module_file, m.module_class,
                     u.user_id, u.username, u.givenname, u.surname, u.group_id
                FROM %s tt
                LEFT OUTER JOIN %s u ON u.user_id = tt.author_id
                LEFT OUTER JOIN %s v ON v.view_id = tt.view_id
                LEFT OUTER JOIN %s m ON m.module_guid = v.module_guid
               WHERE $filter
                 AND tt.lng_id = %d";
      $params = array(
        $this->tableTopicsTrans,
        $this->tableAuthUser,
        $this->tableViews,
        $this->tableModules,
        $this->lngId
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->topics[$row['topic_id']]['TRANSLATION'] = $row;
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get List
  *
  * @param string $topicClass
  * @param integer $max optional, default value 1000
  * @access public
  * @return string $result XML
  */
  function getList($topicClass, $max = 1000) {
    $result = '';
    if (isset($this->topics) && is_array($this->topics) && count($this->topics) > 0) {
      $this->loadTopicTranslatedData();
      $counter = 0;
      foreach ($this->topics as $topicId => $topicData) {
        $page = $this->papaya()->front;
        if ($page instanceof papaya_page && !$page->validateAccess($topicId)) {
          continue;
        }
        if (class_exists($topicClass, FALSE)) {
          /**
           * @var base_topic $topic
           */
          $topic = new $topicClass();
          if (is_object($topic) && method_exists($topic, 'loadOutput')) {
            $topic->topicId = $topicId;
            $topic->topic = $this->topics[$topicId];
            $topic->loadCurrentLanguage($this->lngId);
            if ($topic->topicId) {
              if ($str = $topic->parseContent(FALSE)) {
                $result .= $str.LF;
                $counter++;
                if ($counter >= $max) {
                  break;
                }
              }
            }
          }
        }
      }
      if ($counter > 0) {
        $result = sprintf(
          '<subtopics xmlns:papaya="http://www.papaya-cms.com/ns/papayacms">%s</subtopics>',
          $result
        );
      }
    }
    return $result;
  }
}

