<?php
/**
* Basic object for topic navigation
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Core
* @version $Id: base_topic_navigation.php 39595 2014-03-17 16:48:43Z weinert $
*/

/**
* Basic object for topic navigation
* @package Papaya
* @subpackage Core
*/
class base_topic_navigation extends base_db {

  /**
  * Papaya database table topics
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;
  /**
  * Papaya database table topics translations
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;
  /**
  * Papaya database table link types
  * @var string $tableLinkTypes
  */
  var $tableLinkTypes = PAPAYA_DB_TBL_LINKTYPES;

  var $useSurfer = TRUE;

  /**
   * @var base_surfer
   */
  protected $surfer = NULL;

  /**
   * @var base_topic
   */
  protected $topicObj = NULL;

  /**
   * @var array
   */
  protected $topics = array();

  /**
   * @var int
   */
  protected $prevId = 0;

  /**
   * @var int
   */
  protected $nextId = 0;

  /**
   * @var int
   */
  protected $firstId = 0;

  /**
   * @var int
   */
  protected $lastId = 0;

  /**
  * PHP5 constructor
  *
  * @param base_topic $topic
  * @param boolean|array $fields optional, default value FALSE
  * @access public
  */
  function __construct($topic, $fields = FALSE) {
    $this->topicObj = $topic;
    $this->tableTopics = $this->topicObj->tableTopics;
    $this->tableTopicsTrans = $this->topicObj->tableTopicsTrans;
    $this->data = array(
      'root' => (int)$this->topicObj->topic['prev'],
      'sort' => isset($fields['sort']) ? $fields['sort'] : 'ASC'
    );
  }

  /**
  * Initialize surfer
  *
  * @access public
  */
  function initializeSurfer() {
    if ($this->useSurfer) {
      $this->surfer = $this->papaya()->surfer;
    }
  }

  /**
   * Load navigation
   *
   * @param $lngId
   * @param bool $desc
   * @internal param int $parentTopicId active topic-ID
   * @access public
   * @return boolean
   */
  function load($lngId, $desc=FALSE) {
    $this->initializeSurfer();
    unset($this->prevId);
    unset($this->nextId);
    unset($this->firstId);
    unset($this->lastId);
    $order = ($desc) ? 'DESC' : 'ASC';
    $dummy = NULL;
    $sql = "SELECT t.topic_id, tt.topic_title, t.topic_created,
                   t.topic_weight, t.linktype_id
              FROM %s t, %s tt, %s l
             WHERE t.prev = '%d'
               AND t.topic_id = tt.topic_id
               AND tt.lng_id = '%d'
               AND l.linktype_id = t.linktype_id
               AND l.linktype_is_visible = 1
             ORDER BY t.topic_weight $order, t.topic_created $order";
    $params = array(
      $this->tableTopics,
      $this->tableTopicsTrans,
      $this->tableLinkTypes,
      $this->data['root'],
      $lngId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($this->checkFilter($row['topic_id'], $row)) {
          $this->topics[$row['topic_id']] = $row;
          if ($row['topic_id'] == $this->topicObj->topicId) {
            $this->prevId = $dummy['topic_id'];
          }
          if ($dummy['topic_id'] == $this->topicObj->topicId) {
            $this->nextId = $row['topic_id'];
          }
          $dummy = end($this->topics);
        }
      }
      if (isset($this->topics) && is_array($this->topics)) {
        $dummy = reset($this->topics);
        if (isset($this->prevId) && ($this->prevId > 0) &&
        ($dummy['topic_id'] != $this->prevId)) {
          $this->firstId = $dummy['topic_id'];
        }
        $dummy = end($this->topics);
        if (isset($this->nextId) && ($this->nextId > 0) &&
        ($dummy['topic_id'] != $this->nextId)) {
          $this->lastId = $dummy['topic_id'];
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Check filter
  *
  * @param integer $id
  * @param array $row
  * @access public
  * @return boolean
  */
  function checkFilter($id, $row) {
    if (isset($row) && is_array($row)) {
      if ($this->useSurfer && is_object($this->surfer)) {
        if (!$this->surfer->canView($id)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    return FALSE;
  }


  /**
  * Get XML
  *
  * @access public
  * @return string
  */
  function getXML() {
    if ($this->load($this->topicObj->getContentLanguageId(), $this->data['sort'])) {
      $this->initializeSurfer();
      return $this->getSitemap();
    }
    return '';
  }

  /**
  * Get Sitemap
  *
  * @access public
  * @return string $result
  */
  function getSitemap() {
    $result = sprintf(
      '<sitemap title="%s">'.LF,
      empty($this->data['title']) ? '' : papaya_strings::escapeHTMLChars($this->data['title'])
    );
    if (isset($this->topics) && is_array($this->topics)) {
      if (isset($this->firstId) && ($this->firstId > 0)) {
        $result .= $this->getItem($this->firstId, 'first');
      }
      if (isset($this->prevId) && ($this->prevId > 0)) {
        $result .= $this->getItem($this->prevId, 'prev');
      }
      if (isset($this->nextId) && ($this->nextId > 0)) {
        $result .= $this->getItem($this->nextId, 'next');
      }
      if (isset($this->lastId) && ($this->lastId > 0)) {
        $result .= $this->getItem($this->lastId, 'last');
      }
    }
    $result .= '</sitemap>'.LF;
    return $result;
  }

  /**
  * Get item
  *
  * @param integer $id
  * @param integer $pos
  * @access public
  * @return string $result
  */
  function getItem($id, $pos) {
    $result = '';
    $row = $this->topics[$id];
    if (isset($row) && is_array($row)) {
      if ($id == $this->topicObj->topicId) {
        $focus = ' focus="focus"';
      } else {
        $focus = '';
      }
      $result .= sprintf(
        '<mapitem id="%d" href="%s" title="%s" position="%s" visible="1" %s>'.LF,
        (int)$id,
        papaya_strings::escapeHTMLChars(
          $this->getWebLink(
            $id,
            NULL,
            NULL,
            NULL,
            NULL,
            $row['topic_title']
          )
        ),
        papaya_strings::escapeHTMLChars($row['topic_title']),
        papaya_strings::escapeHTMLChars($pos),
        $focus
      );
      $result .= '</mapitem>'.LF;
    }
    return $result;
  }

}

