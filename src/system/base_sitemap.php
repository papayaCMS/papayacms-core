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
* Sitemap basic class
* @package Papaya
* @subpackage Core
*/
class base_sitemap extends base_db {
  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'tt';
  /**
  * Session parameter name
  * @var string $sessionParamname
  */
  var $sessionParamname;

  /**
  * Parameter
  * @var array $params
  */
  var $params;
  /**
  * Session parameter
  * @var array $sessionParams
  */
  var $sessionParams;

  /**
  * Base link
  * @var string $baseLink
  */
  var $baseLink;

  /**
  * Use surfer
  * @var boolean $useSurfer
  */
  var $useSurfer = TRUE;
  /**
  * Use cookie
  * @var boolean $useCookie
  */
  var $useCookie = FALSE;

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
  * Papaya database table table languages
  * @var string $tableLanguages
  */
  var $tableLanguages = PAPAYA_DB_TBL_LNG;

  /**
  * Papaya database table view links
  * @var string $tableViewLinks
  */
  var $tableViewLinks = PAPAYA_DB_TBL_VIEWLINKS;
  /**
  * Papaya database table view modes
  * @var string $tableViewModes
  */
  var $tableViewModes = PAPAYA_DB_TBL_VIEWMODES;

  /**
  * configurations
  *@var array $data
  */
  var $data;

  /**
  * Surfer
  * @var object base_surfer $surfer
  */
  var $surfer;

  /**
  * Topic minimum
  * @var integer $topicMin
  */
  var $topicMin;
  /**
  * Topic maximum
  * @var integer $topicMax
  */
  var $topicMax;
  /**
  * Topic focused
  * @var integer $topicFocused
  */
  var $topicFocused;

  /**
  * Topic object
  * @var object base_topic $topicObj
  */
  var $topicObj;
  /**
  * Previous topic object
  * @var object base_topic $topicObjPrevs
  */
  var $topicObjPrevs;

  /**
  * Topic root
  * @var array $topicRoot
  */
  var $topicRoot;

  /**
  * Topics
  * @var array $topics
  */
  var $topics;

  /**
  * Topic tree
  * @var array $topicTree
  */
  var $topicTree;
  /**
  * Root ids
  * @var array $rootIds
  */
  var $rootIds;

  /**
  * Open nodes
  * @var array $openNodes
  */
  var $openNodes;

  /**
  * Base URL
  * @var string $baseURL
  */
  var $baseURL;

  /**
  * View mode
  * @var string
  */
  var $viewMode = NULL;

  /**
  * Link types
  * @var array
  */
  var $linkTypes = array();

  /**
  * Topic path root
  * @var array
  */
  private $topicPathRoot = array();

  /**
  * Topic positions
  * @var array
  */
  private $topicPositions = array();

  /**
  * Tags object
  * @var base_tags
  */
  private $_tags = NULL;

  /**
  * Tag list
  * @var array
  */
  private $tagList = array();

  /**
   * base sitemap
   *
   * @param base_topic $topic
   * @param boolean|array $fields optional, default value FALSE
   * @param string $baseURL
   * @param null $viewMode
   * @access public
   */
  function __construct($topic, $fields = FALSE, $baseURL = '', $viewMode = NULL) {
    $this->baseURL = $baseURL;
    $this->topicObj = $topic;
    $this->tableTopics = $this->topicObj->tableTopics;
    $this->tableTopicsTrans = $this->topicObj->tableTopicsTrans;
    $this->topicObjPrevs = $this->idStringToArray(
      empty($this->topicObj->topic['prev_path']) ? '' : $this->topicObj->topic['prev_path'],
      empty($this->topicObj->topic['prev']) ? '' : $this->topicObj->topic['prev']
    );
    $tagModes = array('basic', 'full');
    $addTags = 'none';
    if (isset($fields['add_tags']) && in_array($fields['add_tags'], $tagModes)) {
      $addTags = $fields['add_tags'];
    }
    $this->data = array(
      'xslfile' => (isset($fields['xslfile'])) ? $fields['xslfile'] : '',
      'title' => (isset($fields['title'])) ? $fields['title'] : '',
      'root' => empty($fields['root']) ?  0 : (int)$fields['root'],
      'focus' => empty($fields['focus']) ? '' : $fields['focus'],
      'forstart' => empty($fields['forstart']) ? 0 : (int)$fields['forstart'],
      'forend' => empty($fields['forend']) ? 0 : (int)$fields['forend'],
      'foclevels' => empty($fields['foclevels']) ? 0 : (int)$fields['foclevels'],
      'format' => empty($fields['format']) ? '' : $fields['format'],
      'sort' => (isset($fields['sort'])) ? $fields['sort'] : FALSE,
      'tags' => $addTags
    );
    $this->paramName = $this->paramName.'_'.$this->data['root'];
    $this->sessionParamName = 'PAPAYA_SESS_sitemap_'.$this->paramName;
    $this->initializeParams();
    $this->baseLink = 'index'.PAPAYA_URL_EXTENSION;
    if (!empty($viewMode)) {
      $this->viewMode = $viewMode;
    }
  }

  /**
  * initialize session data
  *
  * @access public
  */
  function initializeSessionData() {
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->openNodes = $this->sessionParams['opened'];
    if (isset($this->params['open'])) {
      $this->openNodes[(int)$this->params['open']] = TRUE;
    } elseif (isset($this->params['close'])) {
      unset($this->openNodes[(int)$this->params['close']]);
    }
    $this->sessionParams['opened'] = $this->openNodes;
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Initialize cookie data
  *
  * @access public
  */
  function initializeCookieData() {
    $cookieName = 'c_sitemap_'.$this->paramName.'_opened';
    $this->openNodes = array();
    if (!empty( $_COOKIE['cookiename'])) {
      if (preg_match_all('#\d+#', $_COOKIE['cookiename'], $matches, PREG_PATTERN_ORDER)) {
        $this->openNodes = array_flip($matches[0]);
      }
    }
    if (isset($this->params['open'])) {
      $this->openNodes[(int)$this->params['open']] = TRUE;
      $cookieData = implode(';', array_keys($this->openNodes));
      setcookie($cookieName, $cookieData);
    } elseif (isset($this->params['close']) &&
              isset($this->openNodes[(int)$this->params['close']])) {
      unset($this->openNodes[(int)$this->params['close']]);
      $cookieData = implode(';', array_keys($this->openNodes));
      setcookie($cookieName, $cookieData);
    }
  }

  /**
  * Get XML
  *
  * @param boolean $navigationOnly optional, default value TRUE
  * @param boolean $includeRootElement optional, default value TRUE
  * @access public
  * @return string ''
  */
  function getXML($navigationOnly = TRUE, $includeRootElement = TRUE) {
    if ($this->initializeNavTree($navigationOnly)) {
      $result = $this->getSitemap($includeRootElement);
      return $result;
    }
    return '';
  }

  /**
  * Get urls
  *
  * @access public
  * @return string
  */
  function getUrls() {
    $sql = "SELECT tt.topic_id,
                   l.lng_id, l.lng_ident, vm.viewmode_ext
              FROM %s tt, %s l, %s vl, %s vm
             WHERE l.lng_id = tt.lng_id
               AND vl.view_id = tt.view_id
               AND vm.viewmode_id = vl.viewmode_id
               AND vm.viewmode_ext = '%s'";
    $params = array(
      $this->tableTopicsTrans,
      $this->tableLanguages,
      $this->tableViewLinks,
      $this->tableViewModes,
      $this->papaya()->options->get('PAPAYA_URL_EXTENSTION', 'html')
    );
    header('Content-Type: text/html; charset=utf-8');
    echo '<html><head>'.
      '<title>URLs</title>'.
      '<meta name="robots" content="noindex, follow">'.
      '</head><body>'.LF;
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $pages = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $pages[] = $row;
      }
      foreach ($pages as $page) {
        $link = $this->baseURL.
          $this->getWebLink(
            $page['topic_id'],
            $page['lng_ident'],
            $page['viewmode_ext']
          );
        $href = $this->getAbsoluteURL($link, NULL, FALSE);
        printf(
          '<a href="%1$s">%1$s</a>'.LF,
          papaya_strings::escapeHTMLChars($href)
        );
      }
    }
    echo '</body></html>';
    return '';
  }

  /**
  * Initialize nav tree
  *
  * @param boolean $navigationOnly optional, default value TRUE
  * @access public
  * @return boolean
  */
  function initializeNavTree($navigationOnly = TRUE) {
    //get content language
    $lngId = $this->topicObj->getContentLanguageId();
    //verschiedene Einstellungen nach Typ pruefen und abarbeiten
    switch ($this->data['format']) {
    case 'breadcrumb':
    case 'path':
      $this->checkDepth(0);
      $this->topicPathRoot = $this->topicObj->getParentIDFromMaster(
        $this->data['root'], $this->data['forstart'] - 1
      );
      break;
    case 'active':
      //Beim dynamischen Baum  muessen die Knoten initialisiert werden
      if ($this->useCookie) {
        //entweder aus einem Cookie
        $this->initializeCookieData();
      } else {
        //oderaus einer Session
        $this->initializeSessionData();
      }
      $this->checkDepth(1);
      break;
    case 'static':
      $this->checkDepth(1);
      break;
    }
    //Basis laden
    if ($this->loadBase()) {
      //eventuell Surfer laden
      $this->initializeSurfer();
      //minimale Generationstiefe (Anzahl der Vorgaenger)
      $this->topicMin = $this->data['forstart'] + count($this->topicRoot['prevs']);
      //maximale Generationstiefe
      $this->topicMax = $this->topicMin + $this->data['depth'];
      //Focus-Id ermittlen
      $this->topicFocused = $this->getFocusId();

      if ($this->data['format'] == 'breadcrumb') {
        return $this->loadPrevTree($lngId, $navigationOnly);
      } elseif ($this->loadTree($lngId, $navigationOnly)) {
        return TRUE;
      }
    } elseif ($this->data['root'] == 0) {
      //eventuell Surfer laden
      $this->initializeSurfer();
      //minimale Generationstiefe (Anzahl der Vorgaenger)
      $this->topicMin = 0;
      //maximale Generationstiefe
      $this->topicMax = 999;
      //Focus-Id ermittlen
      $this->topicFocused = 0;
      $this->data['subpath'] = ';0;';
      if ($this->data['format'] == 'breadcrumb') {
        return $this->loadPrevTree($lngId, $navigationOnly);
      } elseif ($this->loadTree($lngId, $navigationOnly)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Check depth
  *
  * @param integer $min
  * @access public
  */
  function checkDepth($min) {
    $this->data['depth'] = $this->data['forend'] - $this->data['forstart'];
    if ($this->data['depth'] < $min) {
      $this->data['forend'] = $this->data['forend'] + $min;
      $this->data['depth'] = $min;
    }
    if ($this->data['forstart'] <= 0) {
      $this->data['forstart'] = 1;
    }
  }

  /**
  * Check path
  *
  * @param integer $id
  * @param array &$row
  * @access public
  * @return array
  */
  function checkPath($id, &$row) {
    if (isset($row) && is_array($row)) {
      if ($this->topicObj->topicId == $id) {
        $row['focuspath'] = TRUE;
        return TRUE;
      } elseif (isset($row['prevs']) && is_array($row['prevs']) &&
                in_array($this->topicPathRoot, $row['prevs'])) {
        if (isset($row['prevs']) && is_array($row['prevs']) &&
            in_array($this->topicObj->topicId, $row['prevs'])) {
          if ($row['prev'] == $this->topicObj->topicId) {
            $row['focuspath'] = FALSE;
            return TRUE;
          }
        } elseif (isset($this->topicObjPrevs) && is_array($this->topicObjPrevs) &&
                  in_array($row['prev'], $this->topicObjPrevs)) {
          if (in_array($id, $this->topicObjPrevs)) {
            $row['focuspath'] = TRUE;
          } else {
            $row['focuspath'] = FALSE;
          }
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Check open node
  *
  * @param array $row
  * @access public
  * @return boolean
  */
  private function checkOpenNode($row) {
    if (isset($row) && is_array($row)) {
      if (isset($this->openNodes) && is_array($this->openNodes) &&
          isset($this->openNodes[$row['prev']])) {
        return TRUE;
      }
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
  private function checkFilter($id, &$row) {
    if (isset($row) && is_array($row)) {
      if ($this->useSurfer && is_object($this->surfer)) {
        if (!$this->surfer->canView($id)) {
          return FALSE;
        }
      }
      $depth = count($row['prevs']);
      if (isset($this->topicTree[$row['prev']]['childcount'])) {
        $this->topicTree[$row['prev']]['childcount']++;
      } else {
        $this->topicTree[$row['prev']]['childcount'] = 1;
      }

      if (isset($GLOBALS['PAPAYA_PAGE']) && !$GLOBALS['PAPAYA_PAGE']->public) {
        //preview - no domain restrictions
        $domainRestriction = FALSE;
      } elseif (strpos(strtolower($this->baseURL), 'http://') === 0) {
        //base url provided that starts with http:// - no domain restrictions
        $domainRestriction = FALSE;
      } elseif (defined('PAPAYA_PAGEID_DOMAIN_ROOT') && PAPAYA_PAGEID_DOMAIN_ROOT > 0) {
        //domain root id found - restrict navigation to subpages
        if ($row['prev'] != PAPAYA_PAGEID_DOMAIN_ROOT &&
            $row['topic_id'] != PAPAYA_PAGEID_DOMAIN_ROOT &&
            isset($row['prevs']) && is_array($row['prevs']) &&
            !in_array(PAPAYA_PAGEID_DOMAIN_ROOT, $row['prevs'])) {
          $domainRestriction = TRUE;
        } else {
          $domainRestriction = FALSE;
        }
      } else {
        $domainRestriction = FALSE;
      }
      if (isset($domainRestriction) && $domainRestriction === TRUE) {
        return FALSE;
      }

      if ($this->topicMin > $depth) {
        return FALSE;
      } elseif ($this->topicMax <= $depth) {
        switch ($this->data['format']) {
        case 'breadcrumb':
        case 'path':
          if (!$this->checkPath($id, $row)) {
            return FALSE;
          }
          break;
        case 'active':
          if (!$this->checkOpenNode($row)) {
            return FALSE;
          }
          break;
        default:
          return FALSE;
        }
      } else {
        switch ($this->data['format']) {
        case 'breadcrumb':
        case 'path':
          $this->checkPath($id, $row);
          break;
        case 'active':
          $this->checkOpenNode($row);
          break;
        }
      }
      if ($this->topicMin == $depth) {
        $this->rootIds[] = $id;
      } else {
        $this->topicTree[$row['prev']]['children'][] = $id;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * ids to array
  *
  * @param string $idString
  * @param integer $id
  * @access public
  * @return array
  */
  function idStringToArray($idString, $id) {
    if (preg_match_all('#\d+#', $idString, $matches, PREG_PATTERN_ORDER)) {
      $result = $matches[0];
      $result[] = $id;
    } else {
      $result = array($id);
    }
    return $result;
  }

  /**
  * Load base
  *
  * @access public
  * @return boolean
  */
  function loadBase() {
    unset($this->topicRoot);
    $sql = "SELECT topic_id, prev, prev_path
              FROM %s
             WHERE topic_id = %d";
    $params = array($this->tableTopics, $this->data['root']);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['prevs'] = $this->idStringToArray($row['prev_path'], $row['prev']);
        $row['subpath'] = ';'.implode(';', $row['prevs']).';';
        $this->topicRoot = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load tree internal
  *
  * @param integer $lngId
  * @param string $filter
  * @param string $navigationFilter
  * @param string $sort
  * @access public
  * @return boolean
  */
  function loadTreeInternal($lngId, $filter, $navigationFilter, $sort) {
    $trashFilter = ($this->tableTopics == PAPAYA_DB_TBL_TOPICS)
      ? ' AND t.is_deleted = 0' : '';
    $sql = "SELECT t.topic_id, tt.topic_title, t.prev, t.prev_path,
                   t.topic_weight, t.topic_created, t.topic_protocol, t.linktype_id,
                   t.topic_changefreq, t.topic_priority, t.topic_modified
              FROM %s t, %s tt
             WHERE tt.topic_id = t.topic_id
               AND tt.lng_id = %d
               AND ".str_replace('%', '%%', $filter)."
               AND ".str_replace('%', '%%', $navigationFilter)."
               $trashFilter
             ORDER BY t.topic_weight %s, t.topic_created %s";
    $sortString = (($sort == 'DESC') ? 'DESC' : 'ASC');
    $params = array($this->tableTopics, $this->tableTopicsTrans,
      $lngId, $sortString, $sortString);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $rows = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $rows[] = $row;
      }
      $linkTypeIds = array();
      foreach ($rows as $row) {
        $row['prevs'] = $this->idStringToArray($row['prev_path'], $row['prev']);
        if (isset($this->topicPositions[$row['prev']])) {
          $this->topicPositions[$row['prev']][$row['topic_id']] = count(
            $this->topicPositions[$row['prev']]
          );
        } else {
          $this->topicPositions[$row['prev']][$row['topic_id']] = 0;
        }
        if ($this->checkFilter($row['topic_id'], $row)) {
          $this->topics[$row['topic_id']] = $row;
          $linkTypeIds[$row['linktype_id']] = 1;
        }
      }
      if (!empty($linkTypeIds)) {
        if (!isset($this->linkTypeObj)) {
          $this->linkTypeObj = new base_linktypes();
        }
        $this->linkTypes = \PapayaUtilArray::merge(
          $this->linkTypes,
          $this->linkTypeObj->getCompleteLinkTypes(array_keys($linkTypeIds))
        );
      }
      if (!empty($this->topics)) {
        $this->papaya()->pageReferences->preload($lngId, array_keys($this->topics));
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Compare two nodes by the position inside their common ancestor
   * @param integer $idOne
   * @param integer $idTwo
   * @return int
   */
  function compareSitemapRootNodes($idOne, $idTwo) {
    $nodeOne = $this->topics[$idOne];
    $nodeTwo = $this->topics[$idTwo];
    $positionOne = 0;
    $positionTwo = 0;
    if ($nodeOne['prev'] == $nodeTwo['prev']) {
      if (isset($this->topicPositions[$nodeOne['prev']]) &&
          isset($this->topicPositions[$nodeOne['prev']][$nodeOne['topic_id']])) {
        $positionOne = $this->topicPositions[$nodeOne['prev']][$nodeOne['topic_id']];
      }
      if (isset($this->topicPositions[$nodeTwo['prev']]) &&
          isset($this->topicPositions[$nodeTwo['prev']][$nodeTwo['topic_id']])) {
        $positionTwo = $this->topicPositions[$nodeTwo['prev']][$nodeTwo['topic_id']];
      }
    } else {
      $levelsOne = count($nodeOne['prevs']);
      $levelsTwo = count($nodeTwo['prevs']);
      if ($levelsOne > $levelsTwo) {
        $maxLevels = $levelsTwo;
      } else {
        $maxLevels = $levelsOne;
      }
      for ($i = $maxLevels - 1; $i > 0; $i--) {
        $prevOne = $nodeOne['prevs'][$i - 1];
        $prevTwo = $nodeTwo['prevs'][$i - 1];
        if ($prevOne == $prevTwo) {
          if (isset($this->topicPositions[$prevOne]) &&
              isset($this->topicPositions[$prevOne][$nodeOne['prevs'][$i]])) {
            $positionOne = $this->topicPositions[$prevOne][$nodeOne['prevs'][$i]];
          }
          if (isset($this->topicPositions[$prevTwo]) &&
              isset($this->topicPositions[$prevTwo][$nodeTwo['prevs'][$i]])) {
            $positionTwo = $this->topicPositions[$prevTwo][$nodeTwo['prevs'][$i]];
          }
          break;
        }
      }
    }
    if ($positionOne == $positionTwo) {
      return 0;
    } elseif ($positionOne > $positionTwo) {
      return 1;
    } else {
      return -1;
    }
  }

  /**
  * Load tree
  *
  * @param integer $lngId
  * @param boolean $navigationOnly optional, default value TRUE
  * @access public
  * @return boolean
  */
  function loadTree($lngId, $navigationOnly = TRUE) {
    unset($this->topics);
    unset($this->topicTree);
    unset($this->rootIds);
    $this->topicPositions = array();
    $sort = ($this->data['sort']) ? 'DESC' : 'ASC';
    $navigationFilter = $this->getNavigationFilter($navigationOnly);
    if (empty($this->data['subpath']) || $this->data['subpath'] != ';0;') {
      $filter = " t.prev = ".(int)$this->data['root']." ";
      if ($this->loadTreeInternal($lngId, $filter, $navigationFilter, $sort)) {
        $filter = " t.prev_path LIKE '".$this->topicRoot['subpath'].$this->data['root'].";%'";
        if ($this->loadTreeInternal($lngId, $filter, $navigationFilter, $sort)) {
          $this->loadLinkData($lngId, $this->viewMode);
          if (isset($this->rootIds) && is_array($this->rootIds) && count($this->rootIds) > 1) {
            usort($this->rootIds, array($this, 'compareSitemapRootNodes'));
          }
          return TRUE;
        }
      }
    } else {
      $filter = '1=1';
      if ($this->loadTreeInternal($lngId, $filter, $navigationFilter, $sort)) {
        $this->loadLinkData($lngId, $this->viewMode);
        if (isset($this->rootIds) && is_array($this->rootIds) && count($this->rootIds) > 1) {
          usort($this->rootIds, array($this, 'compareSitemapRootNodes'));
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load additional view mode data for link generation.
  *
  * @param integer $lngId
  * @param string $viewMode
  */
  function loadLinkData($lngId, $viewMode) {
    if (isset($this->topics) &&
        is_array($this->topics) &&
        count($this->topics) > 0) {
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSqlCondition('tt.topic_id', array_keys($this->topics))
      );
      $sql = "SELECT tt.topic_id
                FROM %s tt, %s vl, %s vm
               WHERE $filter
                 AND tt.lng_id = '%d'
                 AND vl.view_id = tt.view_id
                 AND vm.viewmode_id = vl.viewmode_id
                 AND vm.viewmode_ext = '%s'";
      $params = array(
        $this->tableTopicsTrans,
        $this->tableViewLinks,
        $this->tableViewModes,
        $lngId,
        $viewMode
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->topics[$row['topic_id']]['viewmodes'][$viewMode] = TRUE;
        }
      }
    }
  }

  /**
  * Load previous tree
  *
  * @param integer $lngId
  * @param boolean $navigationOnly optional, default value TRUE
  * @access public
  * @return boolean
  */
  function loadPrevTree($lngId, $navigationOnly = TRUE) {
    unset($this->topics);
    $sort = 'ASC';
    $navigationFilter = $this->getNavigationFilter($navigationOnly);
    $filter = ' t.topic_id IN (';
    if (!empty($this->topicObj->topic['prev_path'])) {
      if (preg_match_all('/\d+/', $this->topicObj->topic['prev_path'], $regs, PREG_PATTERN_ORDER)) {
        foreach ($regs[0] as $id) {
          $filter .= (int)$id . ',';
        }
      }
    }
    if (!empty($this->topicObj->topic['prev'])) {
      $filter .= (int)$this->topicObj->topic['prev'].',';
    }
    $filter .= (int)$this->topicObj->topicId.') ';
    return $this->loadTreeInternal($lngId, $filter, $navigationFilter, $sort);
  }

  /**
  * Get link type condition limit sql
  * @param boolean $navigationOnly
  * @return string
  */
  function getNavigationFilter($navigationOnly) {
    $result = '1=1';
    if ($navigationOnly) {
      $linkTypeObj = new base_linktypes;
      $visibleLinkTypes = $linkTypeObj->getLinkTypesByVisibility(1, TRUE);
      if ($visibleLinkTypes > 0) {
        $result = $this->databaseGetSQLCondition('t.linktype_id', array_keys($visibleLinkTypes));
      }
    }
    return $result;
  }

  /**
  * Initialize surfer
  *
  * @access public
  */
  function initializeSurfer() {
    if ($this->useSurfer) {
      $this->surfer = $this->papaya()->surfer;
      $this->surfer->loadTopicIdList();
    }
  }

  /**
  * Get focus id
  *
  * @access public
  * @return integer
  */
  function getFocusId() {
    $result = 0;
    switch ($this->data['focus']) {
    case 'root':
      $result = $this->topicObj->getParentIDFromMaster(
        $this->data['root'], $this->data['foclevels']
      );
      break;
    case 'dyna':
      if ($this->data['foclevels'] == 0) {
        $result = $this->topicObj->topicId;
      } else {
        $f = count($this->topicObjPrevs) - $this->data['foclevels'];
        $result = isset($this->topicObjPrevs[$f]) ? $this->topicObjPrevs[$f] : 0;
      }
      break;
    }
    return $result;
  }

  /**
  * Get sitemap
  *
  * @param boolean $includeRootElement optional, default TRUE
  * @return string
  */
  function getSitemap($includeRootElement = TRUE) {
    $result = '';
    if ($includeRootElement) {
      $result .= sprintf(
        '<sitemap format="%s" date="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->data['format']),
        papaya_strings::escapeHTMLChars(date('Y-m-d H:i:s'))
      );
    }
    if (isset($this->rootIds) && is_array($this->rootIds)) {
      $result .= $this->getItems($this->rootIds);
    }
    if ($includeRootElement) {
      $result .= '</sitemap>'.LF;
    }
    return $result;
  }
  /**
  * Initialize tags
  *
  */
  function initializeTags() {
    if ($this->data['tags'] != 'none') {
      $tags = $this->tags();
      $lngId = $this->topicObj->getContentLanguageId();
      $this->tagList = $tags->getTagsByTypeAndLinkIds(
        'topic',
        array_keys($this->topics),
        $this->data['tags'] == 'basic' ? NULL : $lngId
      );
    }
  }

  /**
  * Initialize/get the tags object
  *
  * @return base_tags
  */
  function tags() {
    if ($this->_tags === NULL) {
      $this->_tags = new base_tags();
    }
    return $this->_tags;
  }

  /**
  * Get items
  *
  * @param array $nodes
  * @access public
  * @return string
  */
  function getItems($nodes) {
    $this->initializeTags();
    $result = '';
    if (isset($nodes) && is_array($nodes)) {
      foreach ($nodes as $id) {
        $counter = 0;
        $gen = 0;
        $result .= $this->getItem($id, $counter, $gen);
      }
    }
    return $result;
  }

  /**
  * Get item
  *
  * @param integer $id
  * @param integer &$counter
  * @param integer &$gen
  * @access public
  * @return string
  */
  function getItem($id, &$counter, &$gen) {
    $result = '';
    $row = $this->topics[$id];
    if (isset($row) && is_array($row)) {
      if (empty($this->viewMode) ||
          (
           isset($row['viewmodes']) &&
           isset($row['viewmodes'][$this->viewMode]) &&
           $row['viewmodes'][$this->viewMode]
          )
         ) {
        if (empty($this->baseURL)) {
          $href = $this->getWebLink(
            $id,
            '',
            empty($this->viewMode) ? 'page' : $this->viewMode,
            NULL,
            NULL,
            $row['topic_title'],
            0
          );
          if (defined('PAPAYA_ADMIN_SESSION') && PAPAYA_ADMIN_SESSION &&
              defined('PAPAYA_UI_SECURE') && PAPAYA_UI_SECURE) {
            $linkProtocol = 2;
          } elseif (isset($row['topic_protocol']) && $row['topic_protocol'] > 0) {
            $linkProtocol = $row['topic_protocol'];
          } elseif (defined('PAPAYA_DEFAULT_PROTOCOL') && PAPAYA_DEFAULT_PROTOCOL > 0) {
            $linkProtocol = PAPAYA_DEFAULT_PROTOCOL;
          } else {
            $linkProtocol = 0;
          }
          if ($linkProtocol > 0) {
            $currentProtocol = (\PapayaUtilServerProtocol::isSecure()) ? 2 : 1;
            /* if the session fallback is active, we should not create absolute links because
             * that whould make caching impossible. So we keep the link without the protocol
             * and redirect later. */
            $session = $this->papaya()->session;
            if ($currentProtocol != $linkProtocol &&
                !(
                  $session->isActive() &&
                  $session->id()->existsIn(\Papaya\Session\Id::SOURCE_PATH)
                )
               ) {
              $href = $this->getAbsoluteURL(
                $href,
                '',
                TRUE,
                $linkProtocol == 2 ? 'https' : 'http'
              );
            }
          }
        } else {
          $href = $this->getWebLink(
            $id,
            '',
            empty($this->viewMode) ? 'page' : $this->viewMode,
            NULL,
            NULL,
            $row['topic_title'],
            0
          );
          if (
            !empty($this->baseURL) &&
            0 !== strpos($href, $this->baseURL)
          ) {
            if (preg_match('(^(https?)://[^/]+/(.*))', $href, $matches)) {
              $href = $this->baseURL.(isset($matches[2]) ? $matches[2] : '');
              if (!preg_match('(^(https?):)', $href)) {
                $href = $matches[1].$href;
              }
            } else {
              $href = $this->baseURL.$href;
            }
          }
        }
      } else {
        $href = NULL;
      }
      if (isset($row['focuspath']) && $row['focuspath']) {
        $focusPath = ' focuspath="focuspath"';
      } else {
        $focusPath = '';
      }
      if ($id == $this->topicFocused) {
        $focus = ' focus="focus"';
      } else {
        $focus = '';
      }
      if (empty($this->linkTypes[$row['linktype_id']]['linktype_target'])) {
        $linkTarget = '';
      } else {
        $linkTarget = $this->linkTypes[$row['linktype_id']]['linktype_target'];
      }
      switch ($linkTarget) {
      case 1:
        $target = ' target="_blank"';
        break;
      case 2:
        $target = ' target="_parent"';
        break;
      case 0:
      default:
        $target = '';
        break;
      }
      if (!empty($href) &&
          isset($this->linkTypes[$row['linktype_id']]['linktype_is_popup']) &&
          $this->linkTypes[$row['linktype_id']]['linktype_is_popup']) {
        $isPopup = 1;
        $cfgData = $this->linkTypes[$row['linktype_id']]['popup_config'];
        $target = sprintf(
          ' target="%s"',
          \PapayaUtilStringXml::escapeAttribute(
            $this->linkTypes[$row['linktype_id']]['linktype_name']
          )
        );
        $popupData = sprintf(
          ' data-popup="%s"',
          papaya_strings::escapeHTMLChars(
            papaya_parser::getDataPopupAttribute(
              \PapayaUtilArray::get($cfgData, 'width'),
              \PapayaUtilArray::get($cfgData, 'height'),
              \PapayaUtilArray::get($cfgData, 'scrollbars'),
              \PapayaUtilArray::get($cfgData, 'resizable'),
              \PapayaUtilArray::get($cfgData, 'toolbar'),
              \PapayaUtilArray::get($cfgData, 'top'),
              \PapayaUtilArray::get($cfgData, 'left'),
              \PapayaUtilArray::get($cfgData, 'menubar'),
              \PapayaUtilArray::get($cfgData, 'location'),
              \PapayaUtilArray::get($cfgData, 'status')
            )
          )
        );
      } else {
        $isPopup = 0;
        $popupData = '';
      }
      $subResult = '';
      $subCounter = 0;
      if (isset($this->topicTree[$id]['children']) &&
          is_array($this->topicTree[$id]['children'])) {
        foreach ($this->topicTree[$id]['children'] as $subId) {
          $subResult .= $this->getItem($subId, $subCounter, $gen);
          $gen++;
        }
      }
      $topicChangeFrequency = base_statictables::getChangeFrequencyValues();
      $topicPriority = number_format($row['topic_priority'] / 100, 1, '.', '');
      $linkClassAttribute = '';
      if (!empty($this->linkTypes[$row['linktype_id']]['linktype_class'])) {
        $linkClassAttribute = sprintf(
          'class="%s"',
          papaya_strings::escapeHTMLChars($this->linkTypes[$row['linktype_id']]['linktype_class'])
        );
      }
      $result .= sprintf(
        '<mapitem id="%d" %s title="%s" enctitle="%s" children="%d"'.
          ' allchildren="%d" visible="1" gens="%d" is_popup="%s" %s %s %s %s'.
          ' changefreq="%s" priority="%s" lastmod="%s" %s>'.LF,
        $id,
        empty($href) ? '' : ' href="'.papaya_strings::escapeHTMLChars($href).'"',
        papaya_strings::escapeHTMLChars($row['topic_title']),
        papaya_strings::escapeHTMLChars(rawurlencode($row['topic_title'])),
        isset($this->topicTree[$id]['childcount'])
          ? (int)$this->topicTree[$id]['childcount'] : 0,
        $subCounter,
        $gen,
        $isPopup,
        $popupData,
        $target,
        $focus,
        $focusPath,
        $topicChangeFrequency[$row['topic_changefreq']],
        $topicPriority,
        date('Y-m-d H:i:s', $row['topic_modified']),
        $linkClassAttribute
      );
      if ($this->data['tags'] != 'none' && isset($this->tagList[$id])) {
        $result .= '<tags>'.LF;
        foreach ($this->tagList[$id] as $tagId => $tagData) {
          if ($this->data['tags'] == 'basic') {
            $result .= sprintf(
              '<tag id="%d" uri="%s" />'.LF,
              $tagId,
              papaya_strings::escapeHTMLChars($tagData['tag_uri'])
            );
          } else {
            $tagTitle = isset($tagData['tag_title']) ? $tagData['tag_title'] : '';
            $tagDescription = isset($tagData['tag_description']) ? $tagData['tag_description'] : '';
            $tagImage = isset($tagData['tag_image']) ? $tagData['tag_image'] : '';
            $result .= sprintf(
              '<tag id="%d" uri="%s" title="%s" description="%s" image="%s" />'.LF,
              $tagId,
              $tagData['tag_uri'],
              papaya_strings::escapeHTMLChars($tagTitle),
              papaya_strings::escapeHTMLChars($tagDescription),
              papaya_strings::escapeHTMLChars($tagImage)
            );
          }
        }
        $result .= '</tags>'.LF;
      }
      $result .= $subResult;
      $result .= '</mapitem>'.LF;
      $counter += $subCounter;
      $counter++;
    }
    return $result;
  }
}

