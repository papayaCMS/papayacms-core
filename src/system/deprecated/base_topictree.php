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

use Papaya\CMS\Administration;
use Papaya\Cache;

/**
* Basic topic tree object
* @package Papaya
* @subpackage Administration
*/
class base_topic_tree extends base_db {

  /**
   * @var \Papaya\Template
   */
  public $layout = NULL;

  /**
   * @var array
   */
  protected $rootTopics = array();

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = '';
  /**
  * Session parameter name
  * @var string $sessionParamName
  */
  var $sessionParamName = '';
  /**
  * Parameters
  * @var array $params
  */
  var $params;
  /**
  * Base link
  * @var string $baseLink
  */
  var $baseLink = '';
  /**
  * Topics
  * @var array $topics
  */
  var $topics;
  /**
  * Subtopic count
  * @var array $subtopicCount
  */
  var $subtopicCount;
  /**
  * Opened
  * @var array $opened
  */
  var $opened;

  /**
  * Maximum indent
  * @var integer $maxIndent
  */
  var $maxIndent = 50;
  /**
   * @var array
   */
  private $nodes = array();

  /**
   * @var array
   */
  public $topicLinks;

  /**
   * @var int
   */
  public $topicId = 0;

  /**
   * @var string
   */
  public $tableTopics;

  /**
   * @var string
   */
  public $tableTopicsTrans;

  /**
   * @var string
   */
  public $tableTopicsPublic;

  /**
  * Constructor
  *
  * @param string $paramName optional, default value 'tt'
  * @access public
  */
  function __construct($paramName = 'tt') {
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_'.$paramName;
  }

  /**
  * Initialize parameters
  *
  * @param mixed $id optional, default value NULL
  * @access public
  */
  function initialize($id = NULL) {
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    if (isset($this->params['page_id']) && ($this->params['page_id'] > 0)) {
      $this->sessionParams['page_id'] = (int)$this->params['page_id'];
    } elseif (isset($id) && ($id > 0)) {
      $this->params['page_id'] = (int)$id;
      $this->sessionParams['page_id'] = (int)$this->params['page_id'];
    } elseif (isset($this->sessionParams['page_id'])) {
      $this->params['page_id'] = (int)$this->sessionParams['page_id'];
    } else {
      $this->params['page_id'] = 0;
    }
    $this->topicId = $this->params['page_id'];

    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * execute - basic function for handling parameters
  *
  * @access public
  */
  function execute() {
    $administrationUser = $this->papaya()->administrationUser;
    if (!(
          $this->topicId &&
          $this->hasParent($administrationUser->user['start_node'])
        )) {
      if ($this->params['page_id'] != $administrationUser->user['start_node']) {
        $protocol = \Papaya\Utility\Server\Protocol::get();
        // Set the current user's personal allowed start page
        if ($administrationUser->user['start_node'] > 0) {
          $toURL = $protocol."://".$_SERVER['HTTP_HOST'].$this->getBasePath().
            $this->getLink(array('page_id' => $administrationUser->user['start_node']));
          $this->sessionParams['page_id'] = $administrationUser->user['start_node'];
        } else {
          $toURL = $protocol."://".$_SERVER['HTTP_HOST'].$this->getBasePath().$this->baseLink;
          $this->sessionParams['page_id'] = 0;
        }
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
          header('X-Papaya-Status: redirecting to allowed subtree');
        }
        header("Location: $toURL");
        exit;
      }
    }
    if (!empty($this->sessionParams['opened']) &&
        is_array($this->sessionParams['opened'])) {
      $this->opened = $this->sessionParams['opened'];
    } else {
      $this->opened = array();
    }
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'open':
        if ($this->params['page_id'] > 0) {
          $this->opened[$this->params['page_id']] = TRUE;
        }
        break;
      case 'close':
        if (isset($this->opened[$this->params['page_id']])) {
          unset($this->opened[$this->params['page_id']]);
        }
        break;
      case 'regenerate':
        if ($administrationUser->hasPerm(Administration\Permissions::PAGE_REPAIR_INDEX)) {
          $this->regeneratePath();
        }
        break;
      case 'refreshpages':
        if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_CACHE_CLEAR)) {
          $this->refreshPages();
        }
        break;
      }
    }
    $this->openPrevs($this->topicId);
    $this->sessionParams['opened'] = $this->opened;
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Open sub ID's
  *
  * @param integer $baseId
  * @access public
  */
  function openSubIds($baseId) {
    $sql = "SELECT topic_id, topic_weight, topic_created
              FROM %s t
             WHERE t.prev = '%s'
             ORDER BY topic_weight ASC, topic_created ASC";
    $params = array($this->tableTopics, $baseId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow()) {
        $this->opened[$row[0]] = TRUE;
      }
    }
  }

  /**
   * Load
   *
   * @param integer $baseId
   * @param integer $lngId
   * @access public
   */
  function load($baseId, $lngId) {
    unset($this->topics);
    unset($this->topicLinks);
    //tt.topic_trans_created, tt.topic_trans_modified,
    $baseSql = "SELECT t.topic_id, t.prev, t.prev_path, t.linktype_id,
                     t.topic_weight, t.topic_created, t.topic_modified, t.author_id,
                     t.linktype_id, t.is_deleted,
                     t.topic_unpublished_languages,
                     tt.lng_id, tt.topic_title,
                     ttm.topic_title AS mlang_topic_title,
                     ttp.topic_modified AS topic_published,
                     ttp.published_from, ttp.published_to
                FROM %s t
                LEFT OUTER JOIN %s tt ON (tt.topic_id = t.topic_id AND tt.lng_id = %d)
                LEFT OUTER JOIN %s ttm
                     ON (ttm.topic_id = t.topic_id AND ttm.lng_id = t.topic_mainlanguage)
                LEFT OUTER JOIN %s ttp ON (ttp.topic_id = t.topic_id)
               WHERE t.topic_id > 0 \n";
    $params = array(
      $this->tableTopics,
      $this->tableTopicsTrans,
      (int)$lngId,
      $this->tableTopicsTrans,
      $this->tableTopicsPublic
    );
    $prevs = array();
    if (isset($this->opened)) {
      foreach ($this->opened as $id => $active) {
        $prevs[(int)$id] = $active;
      }
    }
    $prevs[$baseId] = TRUE;
    $prevs = array_keys($prevs);
    $sql = $baseSql;
    $sql .= " AND ".$this->databaseGetSQLCondition('t.prev', $prevs);
    $administrationUser = $this->papaya()->administrationUser;
    if (!$administrationUser->hasPerm(Administration\Permissions::PAGE_TRASH_MANAGE)) {
      $sql .= "AND t.is_deleted = 0 \n";
    }
    $sql .= " ORDER BY t.topic_weight ASC, t.topic_created ASC";
    $counterMin = 100001;
    $counter = array();
    $updatePositions = array();
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (isset($counter[$row['prev']])) {
          ++$counter[$row['prev']];
        } else {
          $counter[$row['prev']] = $counterMin;
        }
        if ($row['topic_weight'] != $counter[$row['prev']]) {
          $row['topic_weight'] = $counter[$row['prev']];
          $updatePositions[$row['topic_id']] = $counter[$row['prev']];
        }
        $row['topic_status'] = $this->getTopicStatus($row);
        $this->topics[$row['topic_id']] = $row;
        $this->topicLinks[$row['prev']]['children'][] = $row['topic_id'];
      }
      $this->saveTopicPositions($updatePositions);
    }
    if ($baseId > 0) {
      $sql = $baseSql;
      $sql = $sql." AND t.topic_id = '".(int)$baseId."'";
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $row['topic_status'] = $this->getTopicStatus($row);
          $this->topics[$row['topic_id']] = $row;
          $this->topicLinks[$row['prev']]['children'][] = $row['topic_id'];
        }
      }
    }
    if (isset($this->topics) && is_array($this->topics) && count($this->topics) > 0) {
      $filter = $this->databaseGetSQLCondition('prev', array_keys($this->topics));
      $sql = "SELECT prev, COUNT(topic_id) as tcount
                FROM %s
               WHERE $filter
               GROUP BY prev";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableTopics))) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->subtopicCount[$row['prev']] = $row['tcount'];
        }
      }
      $this->optimizeList($baseId);
    }
  }

  /**
  * write updated topic positions to database
  *
  * @param array $positions
  * @access public
  * @return boolean
  */
  function saveTopicPositions($positions) {
    if (is_array($positions) && count($positions) > 0) {
      foreach ($positions as $topicId => $position) {
        $data = array(
          'topic_weight' => $position
        );
        $filter = array(
          'topic_id' => $topicId
        );
        if (FALSE === $this->databaseUpdateRecord($this->tableTopics, $data, $filter)) {
          return FALSE;
        }
      }
      return $this->syncTablePositions(array_keys($positions));
    }
    return TRUE;
  }

  /**
   * Syncronize page positions in edit and public tables.
   *
   * @param int|array $topicIds
   * @return boolean
   */
  function syncTablePositions($topicIds) {
    if ($this->tableTopics != $this->tableTopicsPublic) {
      $filter = $this->databaseGetSQLCondition($this->tableTopicsPublic.'.topic_id', $topicIds);
      $sql = "UPDATE %1\$s
                 SET topic_weight = (SELECT %2\$s.topic_weight
                                       FROM %2\$s
                                      WHERE $filter
                                        AND %1\$s.topic_id = %2\$s.topic_id)
               WHERE $filter";
      $params = array(
        $this->tableTopicsPublic,
        $this->tableTopics,
      );
      return FALSE !== $this->databaseQueryFmtWrite($sql, $params);
    }
    return TRUE;
  }

  /**
  * check several data of topic record and return a numeric page status
  *
  * @param array $topicRecord
  * @access public
  * @return integer
  */
  function getTopicStatus($topicRecord) {
    if ($topicRecord['is_deleted']) {
      return 4; //deleted
    } elseif (isset($topicRecord['topic_published'])) {
      $now = time();
      if ($topicRecord['topic_published'] < $topicRecord['topic_modified']) {
        if ($topicRecord['published_from'] < $now &&
            (
             $topicRecord['published_to'] == 0 ||
             $topicRecord['published_to'] == $topicRecord['published_from'] ||
             $topicRecord['published_to'] > $now
            )
           ) {
          return 3; //published and modified
        } else {
          return 7; //published, blocked, modified
        }
      } else {
        if ($topicRecord['published_from'] < $now &&
            (
             $topicRecord['published_to'] == 0 ||
             $topicRecord['published_to'] == $topicRecord['published_from'] ||
             $topicRecord['published_to'] > $now
            )
           ) {
          if ($topicRecord['topic_unpublished_languages'] > 0) {
            return 5; //published and - but not all languages
          } else {
            return 2; //published and up to date
          }
        } else {
          return 6; //published, blocked
        }
      }
    } else {
      return 1; //created
    }
  }

  /**
  * Is entry parent of $id ?
  *
  * @param integer $id
  * @access public
  * @return boolean $result
  */
  function hasParent($id) {
    $iId = (integer)$id;
    if ($iId == 0) {
      return TRUE;
    }
    if (isset($this->topics[$this->topicId])) {
      $topic = $this->topics[$this->topicId];
    } else {
      $sql = "SELECT topic_id, prev, prev_path
                FROM %s
               WHERE topic_id = %d";
      $params = array($this->tableTopics, $this->topicId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $topic = $row;
        } else {
          return FALSE;
        }
      } else {
        return FALSE;
      }
    }
    if (preg_match_all('/\d+/', $topic['prev_path'], $regs, PREG_PATTERN_ORDER)) {
      $ids = array_flip($regs[0]);
    } else {
      return FALSE;
    }
    if (isset($ids[$iId]) || ($topic['prev'] == $iId) ||
        ($this->topicId == $iId) || ($iId == 0)) {
      $result = TRUE;
    } else {
      $result = FALSE;
    }

    return $result;
  }

  /**
  * Load simply all
  *
  * @access public
  */
  function loadSimplyAll($lngId) {
    unset($this->topics);
    unset($this->topicLinks);
    $administrationUser = $this->papaya()->administrationUser;
    if ($administrationUser->hasPerm(Administration\Permissions::PAGE_TRASH_MANAGE)) {
      $filter = '';
    } else {
      $filter = "WHERE t.is_deleted = 0";
    }
    $sql = "SELECT t.topic_id, t.prev, t.prev_path, t.linktype_id,
                   t.topic_weight, t.topic_created, t.topic_modified,
                   t.linktype_id, t.is_deleted,
                   t.topic_unpublished_languages,
                   tt.lng_id, tt.topic_title,
                   tp.topic_modified AS topic_published
              FROM %s t
              LEFT OUTER JOIN %s tt ON (tt.topic_id = t.topic_id AND tt.lng_id = %d)
              LEFT OUTER JOIN %s tp ON (tp.topic_id = t.topic_id)
              $filter
             ORDER BY t.topic_weight ASC, t.topic_created ASC, tt.topic_title ASC";
    $params = array(
      $this->tableTopics,
      $this->tableTopicsTrans,
      $lngId,
      $this->tableTopicsPublic
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $id = (int)$row['topic_id'];
        if ($id > 0) {
          $prev = (int)$row['prev'];
          $this->topics[$id] = $row;
          if (isset($this->topicLinks[$prev]['children']) &&
              is_array($this->topicLinks[$prev]['children'])) {
            $this->topicLinks[$prev]['children'][] = $id;
          } else {
            $this->topicLinks[$prev]['children'] = array($id);
          }
        }
      }
    }
  }

  /**
  * Regenerate path
  *
  * @access public
  */
  function regeneratePath() {
    $sql = "SELECT t.topic_id, t.prev, t.prev_path,
                   tp.topic_id AS public_topic_id,
                   tp.prev AS public_prev,
                   tp.prev_path AS public_prev_path
              FROM %s t
              LEFT OUTER JOIN %s tp ON t.topic_id = tp.topic_id
             ORDER BY t.topic_id";
    $params = array($this->tableTopics, $this->tableTopicsPublic);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->nodes[$row['topic_id']] = $row;
      }
    }
    if (isset($this->nodes) && is_array($this->nodes)) {
      reset($this->nodes);
      $counter = 0;
      while (list($key) = each($this->nodes)) {
        $this->generatePrevPath($key);
        if (isset($this->nodes[$key]['new_path'])) {
          $newPath = $this->nodes[$key]['new_path'].';';
        } else {
          $newPath = ';';
        }
        $data = array(
          'prev_path' => $newPath,
          'prev' => (int)$this->nodes[$key]['prev']
        );
        if ($newPath != $this->nodes[$key]['prev_path']) {
          $updated = $this->databaseUpdateRecord(
            $this->tableTopics, array('prev_path' => $newPath), 'topic_id', $key
          );
          if (FALSE !== $updated) {
            if ($this->nodes[$key]['public_topic_id'] > 0) {
              $this->databaseUpdateRecord($this->tableTopicsPublic, $data, 'topic_id', $key);
            }
            $counter++;
          }
        } elseif ($this->nodes[$key]['public_topic_id'] > 0 && (
            $newPath != $this->nodes[$key]['public_prev_path'] ||
            $this->nodes[$key]['public_prev'] != $this->nodes[$key]['prev'])) {
          $updated = $this->databaseUpdateRecord(
            $this->tableTopicsPublic, $data, 'topic_id', $key
          );
          if (FALSE !== $updated) {
            $counter++;
          }
        }
      }
      if ($counter > 0) {
        $this->addMsg(
          MSG_INFO,
          sprintf($this->_gt('%s paths repaired.'), $counter)
        );
      } else {
        $this->addMsg(MSG_INFO, $this->_gt('Path index checked.'));
      }
    }
    unset($this->nodes);
  }

  /**
  * Generate preview path
  *
  * @param integer $id
  * @access public
  * @return string path or ''
  */
  function generatePrevPath($id) {
    if (isset($this->nodes[$id]) && (!isset($this->nodes[$id]['new_path']))) {
      if (isset($this->nodes[$this->nodes[$id]['prev']])) {
        $parentNode = $this->nodes[$this->nodes[$id]['prev']];
        $this->nodes[$id]['new_path'] =
          $this->generatePrevPath($this->nodes[$id]['prev']).";".
          (empty($parentNode['prev']) ? 0 : (int)$parentNode['prev']);
      }
    }
    if (isset($this->nodes[$id]['new_path'])) {
      return $this->nodes[$id]['new_path'];
    } else {
      return '';
    }
  }

  /**
  * Open previews
  *
  * @param integer $id
  * @access public
  */
  function openPrevs($id) {
    $sql = "SELECT prev, prev_path
             FROM %s
            WHERE topic_id = '%d'";
    $params = array($this->tableTopics, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (preg_match_all('/\d+/', $row['prev_path'], $matches, PREG_PATTERN_ORDER)) {
          $prevs = $matches[0];
        }
        $prevs[] = $row['prev'];
        $prevs = array_unique($prevs);
      }
    }
    if (isset($prevs) && is_array($prevs)) {
      foreach ($prevs as $prevId) {
        if (!(isset($this->opened[$prevId]) && $this->opened[$prevId])) {
          $this->opened[$prevId] = TRUE;
        }
      }
    }
  }

  /**
   * Connect all children to the parents and build array of entries ids with no parents.
   *
   * @param int $baseId ID of Top Entry
   * @access public
   * @return array $result list of base entry ids
   */
  function optimizeList($baseId = 0) {
    $result = FALSE;
    if (isset($this->topics) && is_array($this->topics)) {
      if ($baseId > 0) {
        preg_match_all('/([\d]+)/', $this->topics[$baseId]['prev_path'], $ignorePrevs);
      }
      foreach ($this->topics as $key => $val) {
        if (isset($this->topics[$val['prev']]) && $baseId == 0) {
          if (preg_match_all('/([\d]+)/', $val['prev_path'], $prevReg)) {
            array_unshift($prevReg[1], $val['prev']);
            $this->topics[$key]['ALLPREV'] = $prevReg[1];
          } else {
            $this->topics[$key]['ALLPREV'] = array($val['prev']);
          }
        } elseif ($baseId > 0) {
          if (preg_match_all('/([\d]+)/', $val['prev_path'], $prevReg)) {
            array_unshift($prevReg[1], $val['prev']);
            $prevKeys = $prevReg[1];
          } else {
            $prevKeys = array($val['prev']);
          }
          foreach ($prevKeys as $prevKey) {
            if (!isset($ignorePrevs[$prevKey])) { // de-intent tree
              $this->topics[$key]['ALLPREV'][] = $prevKey;
            }
          }
        } elseif ((int)$val['prev'] == $baseId) {
          $result[] = $key;
        } else {
          $this->topics[$key] = FALSE;
        }
      }
    }
    $this->rootTopics = $result;
    return $result;
  }

  /**
  * Get XML
  *
  * @access public
  * @return string $result
  */
  function getItemsXML() {
    $result = '<items>';
    if (isset($this->topicLinks) && is_array($this->topicLinks)) {
      $result .= $this->getXMLSubTree(0, 0);
    }
    $result .= '</items>';
    return $result;
  }

  /**
  * Get XML sub tree
  *
  * @param integer $id
  * @param integer $indent
  * @access public
  * @return string $result
  */
  function getXMLSubTree($id, $indent) {
    $result = '';
    if (isset($this->topicLinks[$id]['children']) &&
        is_array($this->topicLinks[$id]['children']) && $indent < $this->maxIndent) {
      foreach ($this->topicLinks[$id]['children'] as $cid) {
        $result .= $this->getXMLElement($cid, $indent);
        $result .= $this->getXMLSubTree($cid, $indent + 1);
      }
    }
    return $result;
  }

  /**
  * Get XML element
  *
  * @param integer $id
  * @param integer $indent
  * @access public
  * @return string $result
  */
  function getXMLElement($id, $indent) {
    $result = '';
    $topic = $this->topics[$id];
    if (isset($topic) && is_array($topic)) {
      $linktypeObj = new base_linktypes;
      $visibility = $linktypeObj->getLinkTypesVisibility();
      if (isset($visibility[$topic['linktype_id']]) && $visibility[$topic['linktype_id']]) {
        $visible = 'TRUE';
      } else {
        $visible = 'FALSE';
      }
      if ($topic['topic_published']) {
        if ($topic['topic_published'] >= $topic['topic_modified']) {
          $public = 1;
        } else {
          $public = 2;
        }
      } else {
        $public = 0;
      }

      if (isset($topic['topic_title']) && trim($topic['topic_title']) != '') {
        $title = papaya_strings::escapeHTMLChars($topic['topic_title']);
      } else {
        $title = '';
      }
      $result .= sprintf(
        '<item id="%d" prev="%d" indent="%s" visible="%s" public="%s" title="%s"'.
          ' modified="%s" published="%s" weight="%s" />',
        (int)$topic['topic_id'],
        (int)$topic['prev'],
        papaya_strings::escapeHTMLChars($indent),
        papaya_strings::escapeHTMLChars($visible),
        papaya_strings::escapeHTMLChars($public),
        papaya_strings::escapeHTMLChars($title),
        date('d.m.Y H:i', $topic['topic_modified']),
        date('d.m.Y H:i', $topic['topic_published']),
        papaya_strings::escapeHTMLChars($topic['topic_weight'])
      );
    }
    return $result;
  }

  /**
  * Get buttons
  *
  * @access public
  * @return mixed $result  boolean or array
  */
  function getButtonsXML() {
    $menubar = new base_btnbuilder;
    $menubar->images = $this->papaya()->images;
    $administrationUser = $this->papaya()->administrationUser;
    if ($administrationUser->hasPerm(Administration\Permissions::PAGE_REPAIR_INDEX)) {
      $menubar->addButton(
        'Check index',
        $this->getLink(array('cmd' => 'regenerate')),
        'actions-tree-scan',
        'Check and correct path index',
        FALSE
      );
    }
    if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_CACHE_CLEAR)) {
      $menubar->addButton(
        'Empty cache',
        $this->getLink(array('cmd' => 'refreshpages')),
        'actions-edit-clear',
        'Empty output cache',
        FALSE
      );
    }
    if ($result = $menubar->getXML()) {
      $this->layout->add('<menu>'.$result.'</menu>', 'menus');
    }
  }

  /**
  * Refresh pages
  *
  * @access public
  */
  function refreshPages() {
    $cache = \Papaya\CMS\Cache\Cache::getService($this->papaya()->options);
    $counter = $cache->delete();
    if ($counter === TRUE) {
      $this->addMsg(MSG_INFO, 'Cache deleted or invalidated.');
    } elseif ($counter === FALSE) {
      $this->addMsg(MSG_WARNING, 'Invalid/Broken cache configuration.');
    } elseif ($counter > 0) {
      $this->addMsg(MSG_INFO, sprintf($this->_gt('%s files deleted.'), $counter));
    } else {
      $this->addMsg(MSG_INFO, $this->_gt('Cache was empty - no files deleted.'));
    }
    $mediaDB = new papaya_mediadb();
    $fileCounter = $mediaDB->clearCacheDirectory();
    if ($fileCounter > 0) {
      $this->addMsg(
        MSG_INFO, sprintf($this->_gt('%d media file softlinks deleted.'), $fileCounter)
      );
    }
  }

  /**
  * Is topic editable
  * @param integer $topicId
  * @access public
  * @return boolean $result
  */
  function topicEditable($topicId) {
    if (isset($this->topics[$topicId])) {
      $topic = $this->topics[$topicId];
      //edit entries allowed
      $administrationUser = $this->papaya()->administrationUser;
      if ($administrationUser->hasPerm(Administration\Permissions::PAGE_MANAGE)) {
        if ($administrationUser->subLevel == 0) {
          return TRUE;
        }
        //Indent level ok
        if (isset($topic['ALLPREVS']) && is_array($topic['ALLPREVS'])) {
          return (count($topic['ALLPREVS']) <= $administrationUser->subLevel);
        } else {
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}


