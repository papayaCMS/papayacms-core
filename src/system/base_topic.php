<?php
/**
* page superclass
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
* @version $Id: base_topic.php 39807 2014-05-09 14:32:14Z weinert $
*/

/**
* file owner
*/
define('PERM_OWNER', 1);
/**
* file group
*/
define('PERM_GROUP', 2);
/**
* Alle rights
*/
define('PERM_ALL', 4);

/**
* Read permission
*/
define('PERM_READ', 0);
/**
* Write permission
*/
define('PERM_WRITE', 1);
/**
* Create permission
*/
define('PERM_CREATE', 2);


/**
* page superclass
*
* @package Papaya
* @subpackage Core
*/
class base_topic extends base_db {
  /**
  * Paramter name for forms
  * @var string $paramName
  */
  var $paramName = 'tt';
  /**
  * Table with pages (topics)
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;
  /**
  * Table with public pages (Topics)
  * @var string $tableTopics
  */
  var $tableTopicsPublic = PAPAYA_DB_TBL_TOPICS_PUBLIC;
  /**
  * Table with released version of topics
  * @var string $tableTopicsVersions
  */
  var $tableTopicsVersions = PAPAYA_DB_TBL_TOPICS_VERSIONS;
  /**
  * Table with language content for pages
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;
  /**
  * Table with language content for page version
  * @var string $tableTopicsVersionsTrans
  */
  var $tableTopicsVersionsTrans = PAPAYA_DB_TBL_TOPICS_VERSIONS_TRANS;
  /**
  * Table with language content for public pages
  * @var string $tableTopicsPublicTrans
  */
  var $tableTopicsPublicTrans = PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS;
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
  * Links to boxes
  * @var string $tableBoxesLinks
  */
  var $tableBoxesLinks = PAPAYA_DB_TBL_BOXLINKS;
  /**
  * Table with users
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;
  /**
  * Table contains groups
  * @var string $tableAuthGroups
  */
  var $tableAuthGroups = PAPAYA_DB_TBL_AUTHGROUPS;
  /**
  * Table contains languages
  * @var string $tableLanguages
  */
  var $tableLanguages = PAPAYA_DB_TBL_LNG;

  /**
  * Topic-ID - number of entries
  * @var integer $topicId
  */
  var $topicId = 0;

  /**
  * Topic - all data as array
  * @var array $topic
  */
  var $topic = NULL;

  /**
  * Processing content-module
  * @var object base_content $moduleObj
  */
  var $moduleObj = FALSE;

  /**
  * Number of topic versons
  * @var integer $maxVersions
  */
  var $maxVersions = -1;

  /**
  * Storable in cache?
  * @var boolean $cachable
  */
  var $cacheable = TRUE;

  /**
  * current content language
  * @var PapayaContentLanguage $currentLanguage
  */
  var $currentLanguage = NULL;

  /**
  * current sub page identifier - set in parseContent
  * @var string $currentSubPageIdentifier
  */
  var $currentSubPageIdentifier = '';

  /**
   * @var array
   */
  public $topicTranslations = NULL;

  private $_language = NULL;

  /**
   * load topic basics
   *
   * @param integer $topicId
   * @param integer $lngId
   * @return boolean
   */
  public function loadOutput($topicId, $lngId) {
    $sql = "SELECT topic_id, prev, prev_path,
                   topic_mainlanguage, is_deleted,
                   topic_created, topic_modified,
                   topic_cachemode, topic_cachetime,
                   topic_expiresmode, topic_expirestime,
                   topic_sessionmode,
                   topic_protocol, linktype_id,
                   box_useparent, meta_useparent, surfer_useparent
              FROM %s
             WHERE topic_id = %d";
    $params = array($this->tableTopics, $topicId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topicId = (int)$row['topic_id'];
        $this->topic = $row;
        if (isset($lngId)) {
          $this->loadTranslatedData($topicId, $lngId);
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get last publication/validation time
  *
   * @param integer $topicId
   * @param integer $lngId
  * @access public
  * @return integer | NULL result field or null
  */
  function getLastPublicationTime($topicId, $lngId = 0) {
    if ($lngId > 0) {
      $sql = "SELECT MAX(v.version_time) topic_published,
                     MAX(v.topic_audited) topic_audited
                FROM %s AS v, %s AS vt
               WHERE vt.version_id = v.version_id AND v.topic_id = %d AND vt.lng_id = %d";
      $params = array(
        $this->tableTopicsVersions,
        $this->tableTopicsVersionsTrans,
        $topicId,
        $lngId
      );
    } else {
      $sql = "SELECT MAX(version_time) topic_published,
                     MAX(topic_audited) topic_audited
                FROM %s
               WHERE topic_id = %d";
      $params = array(
        $this->tableTopicsVersions,
        $topicId
      );
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
    return NULL;
  }

  /**
  * Language ident to id
  *
  * @param string $lngIdent
  * @access public
  * @return integer
  */
  function languageIdentToId($lngIdent) {
    if (!empty($lngIdent)) {
      $sql = "SELECT lng_id
                FROM %s
               WHERE lng_ident = '%s'";
      $params = array($this->tableLanguages, trim($lngIdent));
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          return (int)$row[0];
        }
      }
    }
    return 0;
  }

  /**
  * Load current language
  *
  * @param integer $lngId
  * @param boolean $forceReload force sql query (skip static variable cache)
  * @access public
  * @return boolean
  */
  function loadCurrentLanguage($lngId = NULL, $forceReload = FALSE) {
    if (empty($lngId)) {
      $lngId = $this->getContentLanguageId();
    }
    if (isset($this->papaya()->languages[$lngId])) {
      $this->currentLanguage = $this->papaya()->languages->getLanguage($lngId);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Get view id
  *
  * @access public
  * @return integer
  */
  function getViewId() {
    if (isset($this->topic) && !empty($this->topic['TRANSLATION'])) {
      return (int)$this->topic['TRANSLATION']['view_id'];
    } else {
      return 0;
    }
  }

  /**
  * Check path
  *
  * @access public
  * @return string $result
  */
  function checkPath() {
    if ($this->topic['prev'] == 0) {
      $result = ';';
    } elseif (preg_match_all('/\d+/', $this->topic['prev_path'], $regs, PREG_PATTERN_ORDER)) {
      $result = ';'.implode(';', $regs[0]).';';
    } else {
      $result = ';0;';
    }
    return $result;
  }

  /**
   * Get module output
   *
   * @param boolean $pageContent page output or short text
   * @param array $parseParams parase parameter from output filter
   * @param bool $topicTag
   * @access public
   * @return string
   */
  function parseContent($pageContent = TRUE, $parseParams = array(), $topicTag = TRUE) {
    $result = FALSE;
    $this->moduleObj = $this->papaya()->plugins->get(
      $this->topic['TRANSLATION']['module_guid'],
      $this,
      $this->topic['TRANSLATION']['topic_content'],
      $this->topic['TRANSLATION']['module_class'],
      $this->topic['TRANSLATION']['module_path'].$this->topic['TRANSLATION']['module_file']
    );
    if (isset($this->moduleObj) && is_object($this->moduleObj)) {
      if ($this->moduleObj instanceof PapayaPluginEditable) {
        $this->moduleObj->content()->setXml($this->topic['TRANSLATION']['topic_content']);
      }
      $cacheId = $this->getContentCacheId($this->moduleObj, $pageContent);
      if ($cacheId && $result = $this->getContentCache($cacheId)) {
        return $result;
      } else {
        if ($this->moduleObj instanceof PapayaPluginConfigurable && !empty($parseParams)) {
          $this->moduleObj->configuration()->merge($parseParams);
        }
        if (!$pageContent) {
          $teaser = FALSE;
          if ($this->moduleObj instanceof PapayaPluginQuoteable) {
            $dom = new PapayaXmlDocument();
            $node = $dom->appendElement('content');
            $this->moduleObj->appendQuoteTo($node);
            $teaser = $node->saveFragment();
          } elseif (method_exists($this->moduleObj, 'getParsedTeaser')) {
            $teaser = $this->moduleObj->getParsedTeaser($parseParams);
          }
          if (FALSE !== $teaser) {
            if ($topicTag) {
              $result .= $this->getContentTopicTag('subtopic', FALSE);
            }
            $result .= $teaser;
            if ($topicTag) {
              $result .= '</subtopic>';
            }
          }
        } else {
          $str = FALSE;
          if ($this->moduleObj instanceof PapayaPluginAppendable) {
            $dom = new PapayaXmlDocument();
            $node = $dom->appendElement('content');
            $this->moduleObj->appendTo($node);
            $str = $node->saveFragment();
          } elseif (method_exists($this->moduleObj, 'getParsedData')) {
            $str = $this->moduleObj->getParsedData($parseParams);
          }
          if (FALSE !== $str) {
            $parser = new papaya_parser;
            if (isset($parseParams['link_outputmode'])) {
              $parser->setLinkOutputMode($parseParams['link_outputmode']);
            }
            $parser->tableTopics = $this->tableTopics;
            $parser->tableTopicsTrans = $this->tableTopicsTrans;
            if ($topicTag) {
              $result .= $this->getContentTopicTag('topic', FALSE);
            }
            $result .= $parser->parse((string)$str, $this->getContentLanguageId());
            if ($topicTag) {
              $result .= '</topic>'.LF;
            }
            if ($str = $parser->getParsedData()) {
              $result .= '<parser>';
              $result .= $str;
              $result .= '</parser>';
            }
            if ($cacheId) {
              $this->writeContentCache($cacheId, $result);
            } else {
              $this->cacheable = FALSE;
            }
          }
        }
      }
      if ($this->moduleObj instanceof base_content &&
          ($subPage = $this->moduleObj->getSubPageIdentifier())) {
        $this->currentSubPageIdentifier = $subPage;
      }
      unset($parser);
    }
    return $result;
  }

  /**
  * Compile the expires time, 0 means no browser cache
  * @return integer
  */
  function getExpires() {
    if (!isset($_SERVER['REQUEST_METHOD'])
        || strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET'
    ) {
      return 0;
    }
    if ($this->papaya()->session->isActive() &&
        $this->papaya()->options->get('PAPAYA_SESSION_CACHE', 'nocache') == 'nocache') {
      //browser cache disabled by session option
      return 0;
    } elseif ($this->topic['topic_expiresmode'] == 0) {
      //browser cache disabled by page property
      return 0;
    } elseif ($this->topic['topic_expiresmode'] == 1) {
      return $this->papaya()->options->get('PAPAYA_CACHE_TIME_BROWSER', 0);
    } elseif ($this->topic['topic_expiresmode'] == 2) {
      return (int)$this->topic['topic_expirestime'];
    } else {
      return 0;
    }
  }

  /**
  * get the current cache id
  *
  * implemented in child to activate caching
  *
  * @param object base_topic $moduleObj
  * @param boolean $pageContent optional, default value TRUE
  * @access protected
  * @return boolean | string
  */
  function getContentCacheId($moduleObj, $pageContent = TRUE) {
    return FALSE;
  }

  /**
  * get the cache time for this page
  * implemented in child to activate caching
  *
  * @access protected
  * @return integer seconds
  */
  function getContentCacheTime() {
    return 0;
  }

  /**
  * read the cache
  * implemented in child to activate caching
  *
  * @param string $cacheId
  * @return boolean|string
  */
  protected function getContentCache($cacheId) {
    return FALSE;
  }

  /**
  * write the cache
  * implemented in child to activate caching
  *
  * @param string $cacheId
  * @param string $contentStr
  * @return boolean
  */
  protected function writeContentCache($cacheId, $contentStr) {
    return FALSE;
  }

  /**
  * get meta data for content as xml tag
  *
  * @param string $tagName optional, default value 'topic'
  * @param boolean $emptyTag optional, default value FALSE
  * @access public
  * @return string
  */
  function getContentTopicTag($tagName = 'topic', $emptyTag = FALSE) {
    $published = 0;
    $audited = 0;
    if (!empty($this->topic['TRANSLATION']['topic_trans_modified'])) {
      $published = $this->topic['TRANSLATION']['topic_trans_modified'];
    }
    if ($publicationTimes = $this->getLastPublicationTime($this->topicId, $this->getPageLanguage()->id)) {
      if (empty($published)) {
        $publicationTimes['topic_published'];
      }
      $audited = $publicationTimes['topic_audited'];
    }
    if (empty($published)) {
      $published = $this->topic['topic_modified'];
    }
    $result = sprintf(
      '<%s no="%s" title="%s" href="%s" author="%s %s" created="%s"'.
      ' createdRFC822="%s" published="%s" audited="%s"'.
      ' module="%s" guid="%s"%s>'.LF,
      papaya_strings::escapeHTMLChars($tagName),
      (int)$this->topicId,
      papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['topic_title']),
      $this->getWebLink(
        (int)$this->topicId, NULL, NULL, NULL, NULL, $this->topic['TRANSLATION']['topic_title']
      ),
      papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['givenname']),
      papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['surname']),
      empty($this->topic['topic_created'])
        ? '' : PapayaUtilDate::timestampToString((int)$this->topic['topic_created']),
      empty($this->topic['topic_created'])
        ? '' : date('D, d M Y H:i:s O', (int)$this->topic['topic_created']),
      empty($published) ? '' : PapayaUtilDate::timestampToString($published),
      empty($audited) ? '' : PapayaUtilDate::timestampToString($audited),
      papaya_strings::escapeHTMLChars(get_class($this->moduleObj)),
      papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['module_guid']),
      $emptyTag ? '/' : ''
    );
    return $result;
  }

  /**
  * Load translation data
  *
  * @access public
  * @return array $topicTranslations
  */
  function loadTranslationsData() {
    $this->topicTranslations = array();
    $sql = "SELECT tt.topic_id, tt.lng_id, tt.topic_title,
                   l.lng_short, l.lng_title, l.lng_ident
              FROM %s tt, %s l
             WHERE tt.topic_id = %d AND tt.lng_id = l.lng_id
             ORDER BY l.lng_title";
    $params = array($this->tableTopicsTrans, $this->tableLanguages, (int)$this->topicId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topicTranslations[$row['lng_id']] = $row;
      }
    }
    return $this->topicTranslations;
  }

  /**
  * Get translations data
  *
  * @param integer $currentLngId optional, default value 0
  * @access public
  * @return string $result
  */
  function getTranslationsData($currentLngId = 0) {
    $result = '';
    if ($this->topicId && ($translations = $this->loadTranslationsData())) {
      if (isset($_SERVER['QUERY_STRING']) &&
          trim($_SERVER['QUERY_STRING']) != '') {
        $queryString = $this->recodeQueryString($_SERVER['QUERY_STRING']);
      } elseif (isset($_GET) && is_array($_GET)) {
        $queryString = $this->encodeQueryString($_GET);
      } else {
        $queryString = '';
      }
      foreach ($translations as $translation) {
        $selected = ($translation['lng_id'] == $currentLngId) ?
          ' selected="selected"' : '';
        $href = $this->getWebLink(
          $this->topicId, $translation['lng_ident'], NULL, NULL, NULL, $translation['topic_title']
        );
        $result .= sprintf(
          '<translation lng_short="%s" lng_title="%s" href="%s"%s>%s</translation>',
          papaya_strings::escapeHTMLChars($translation['lng_short']),
          papaya_strings::escapeHTMLChars($translation['lng_title']),
          papaya_strings::escapeHTMLChars($href.$queryString),
          $selected,
          papaya_strings::escapeHTMLChars($translation['topic_title'])
        );
      }
    }
    return $result;
  }

  /**
  * Load translated data
  *
  * @param integer $id topic id
  * @param integer $lng language id
  * @access public
  * @return boolean $result
  */
  function loadTranslatedData($id, $lng) {
    $result = FALSE;
    if ($id > 0 && $lng > 0) {
      $sql = "SELECT t.topic_id, t.topic_title, t.topic_content, t.lng_id,
                     t.topic_trans_created, t.topic_trans_modified,
                     t.meta_title, t.meta_keywords, t.meta_descr,
                     t.view_id, v.view_title,
                     m.module_guid , m.module_title, m.module_path,
                     m.module_file, m.module_class,
                     u.user_id, u.username, u.givenname, u.surname, u.group_id
                FROM %s t
                LEFT OUTER JOIN %s u ON t.author_id = u.user_id
                LEFT OUTER JOIN %s v ON v.view_id = t.view_id
                LEFT OUTER JOIN %s m ON m.module_guid = v.module_guid
               WHERE t.topic_id = %d AND t.lng_id = %d";
      $params = array(
        $this->tableTopicsTrans, $this->tableAuthUser,
        $this->tableViews, $this->tableModules, $id, $lng);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->topic['TRANSLATION'] = $row;
          $this->loadCurrentLanguage($row['lng_id']);
          $result = TRUE;
        }
        $res->free();
      }
    }
    return $result;
  }

  /**
  * Load meta data
  *
  * @access public
  * @return mixed array $result meta data or boolean FALSE
  */
  function loadMetaData() {
    if ($this->topicId && !empty($this->currentLanguage)) {
      $metaDate = date('Y-m-d\TH:i:s+0200', $this->topic['topic_modified']);
      if (!$this->topic['meta_useparent']) {
        $metaTitle = empty($this->topic['TRANSLATION']['meta_title'])
          ? '' : $this->topic['TRANSLATION']['meta_title'];
        $metaKeywords = empty($this->topic['TRANSLATION']['meta_keywords'])
          ? '' : $this->topic['TRANSLATION']['meta_keywords'];
        $metaDescr = empty($this->topic['TRANSLATION']['meta_descr'])
          ? '' : $this->topic['TRANSLATION']['meta_descr'];
      } else {
        $previousIds = FALSE;
        $metaTitle = FALSE;
        $metaKeywords = FALSE;
        $metaDescr = FALSE;
        if (isset($this->topic['prev_path']) &&
            preg_match_all('/\d+/', $this->topic['prev_path'], $matches, PREG_PATTERN_ORDER)) {
          $previousIds = $matches[0];
        }
        if (isset($this->topic['prev']) && $this->topic['prev'] > 0) {
          $previousIds[] = $this->topic['prev'];
        }
        if (isset($previousIds) && is_array($previousIds)) {
          $previousIds = array_unique($previousIds);
          $filter = str_replace(
            '%', '%%', $this->databasegetSQLCondition('t.topic_id', $previousIds)
          );
          $sql = "SELECT t.topic_id, tt.lng_id, tt.meta_keywords,
                         tt.meta_descr, tt.meta_title
                    FROM %s t
                    LEFT OUTER JOIN %s tt
                      ON (tt.topic_id = t.topic_id AND tt.lng_id = %d)
                   WHERE $filter
                     AND t.meta_useparent = 0";
          $rows = array();
          $params = array(
            $this->tableTopics,
            $this->tableTopicsTrans,
            $this->currentLanguage['id']
          );
          if ($res = $this->databaseQueryFmt($sql, $params)) {
            while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
              $rows[$row['topic_id']] = $row;
            }
          }
          if (isset($rows) && is_array($rows)) {
            for ($i = count($previousIds); $i > 0; $i--) {
              if (isset($previousIds[$i]) && isset($rows[$previousIds[$i]])) {
                $no = $previousIds[$i];
                $metaKeywords = $rows[$no]['meta_keywords'];
                $metaDescr = $rows[$no]['meta_descr'];
                $metaTitle = $rows[$no]['meta_title'];
                break;
              }
            }
          }
        }
      }
      $result = array(
        'meta_date' => $metaDate,
        'meta_keywords' => $metaKeywords,
        'meta_descr' => $metaDescr,
        'meta_title' => $metaTitle,
        'meta_language' => $this->currentLanguage['code']
      );
      return $result;
    }
    return FALSE;
  }

  /**
  * get meta informations for the used topic
  *
  * @access public
  * @return string $result XML
  */
  function getMetaInfos() {
    $result = '';
    if ($this->topicId) {
      $data = $this->loadMetaData();
      $result .= '<metatags>'.LF;
      if (isset($data['meta_title']) && $data['meta_title'] != '') {
        $result .= sprintf(
          '<pagetitle>%s</pagetitle>'.LF,
          papaya_strings::escapeHTMLChars($data['meta_title'])
        );
      }
      if (isset($data['meta_language'])) {
        $result .= sprintf(
          '<language>%s</language>'.LF,
          papaya_strings::escapeHTMLChars($data['meta_language'])
        );
      }
      $result .= sprintf(
        '<metatag type="date">%s</metatag>'.LF,
        papaya_strings::escapeHTMLChars($data['meta_date'])
      );
      if (isset($data['meta_keywords']) && $data['meta_keywords'] != '') {
        $result .= sprintf(
          '<metatag type="keywords">%s</metatag>'.LF,
          papaya_strings::escapeHTMLChars($data['meta_keywords'])
        );
      }
      if (isset($data['meta_descr']) && $data['meta_descr'] != '') {
        $result .= sprintf(
          '<metatag type="description">%s</metatag>'.LF,
          papaya_strings::escapeHTMLChars($data['meta_descr'])
        );
      }
      $result .= '</metatags>';
    }
    return $result;
  }

  /**
  * Check if entry is $id child
  *
  * @param integer $id
  * @access public
  * @return boolean $result
  */
  function hasParent($id) {
    if ($id == 0) {
      return TRUE;
    }
    if (preg_match_all('/\d+/', $this->topic['prev_path'], $regs, PREG_PATTERN_ORDER)) {
      $ids = array_flip($regs[0]);
    } else {
      $ids = array();
    }
    if (isset($ids[$id]) || ((int)$this->topic['prev'] == $id) ||
        ($this->topicId === $id) || ($id == 0)) {
      $result = TRUE;
    } else {
      $result = FALSE;
    }
    return $result;
  }

  /**
  * Get distance to startnode
  *
  * @param integer $startId
  * @access public
  * @return integer $counted counted elements
  */
  function getLevel($startId) {
    if ($startId == $this->topicId) {
      return 0;
    }
    if ($this->topic['prev']) {
      $path = ";0".$this->topic['prev_path'].";".$this->topic['prev'].";";
    } else {
      $path = ";0;";
    }
    $path = str_replace(";;", ";", $path);
    $path = substr($path, strpos($path, ";$startId;"));
    $elements = explode(";", $path);
    $counted = count($elements) - 2;
    return $counted;
  }

  /**
  * Detect parent ID
  *
  * @param integer $level level higher
  * @access public
  * @return integer parent id
  */
  function getParentID($level = 0) {
    if ($level <= 0) {
      return $this->topicId;
    } elseif ($level == 1) {
      return $this->topic['prev'];
    } else {
      if (preg_match_all('#\d+#', $this->topic['prev_path'], $regs, PREG_PATTERN_ORDER)) {
        $ups = $regs[0];
      } else {
        return 0;
      }
      $idx = count($ups) - $level + 1;
      return $ups[$idx];
    }
  }

  /**
  * Get parent id from master
  *
  * @param integer $masterNode id of given node
  * @param integer $level optional, default value 0
  * @access public
  * @return string
  */
  function getParentIDFromMaster($masterNode, $level = 0) {
    $result = $masterNode;
    if ($level > 0) {
      $pathList = array();
      if (isset($this->topic['prev_path'])) {
        if (preg_match_all('/\d+/', $this->topic['prev_path'], $matches, PREG_PATTERN_ORDER)) {
          $pathList = $matches[0];
        }
      }
      if (isset($this->topic['prev'])) {
        $pathList[] = $this->topic['prev'];
      }
      $pathList[] = $this->topicId;
      if (isset($pathList) && is_array($pathList)) {
        foreach ($pathList as $key => $val) {
          if ($val == $masterNode) {
            $idx = $key + $level;
            if (isset($pathList[$idx])) {
              $result = $pathList[$idx];
            }
            break;
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get box parent
  *
  * @access public
  * @return mixed integer parent box id or boolean FALSE
  */
  public function getBoxParent() {
    return $this->getAncestorId(
      array(
        'box_useparent' => array(base_boxeslinks::INHERIT_NONE, base_boxeslinks::INHERIT_GROUPS)
      )
    );
  }

  public function getBoxGroupParent() {
    return $this->getAncestorId(
      array(
        'box_useparent' => array(base_boxeslinks::INHERIT_NONE, base_boxeslinks::INHERIT_BOXES)
      )
    );
  }

  private function getAncestorId(array $filter) {
    if (isset($this->topic)) {
      $previousIds = PapayaUtilArray::decodeIdList(PapayaUtilArray::get($this->topic, 'prev_path'));
      $previousIds[] = PapayaUtilArray::get($this->topic, 'prev');
    } else {
      $previousIds = array();
    }
    if (!empty($previousIds)) {
      $filter['topic_id'] = $previousIds;
      $filterSql = $this->databaseGetSQLCondition($filter);
      $sql = "SELECT topic_id
                FROM %s
               WHERE $filterSql";
      $rows = array();
      $params = array($this->tableTopics);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $rows[$row['topic_id']] = $row;
        }
      }
      if (isset($rows) && is_array($rows)) {
        //go trough the path looking for the first usable parent page id
        for ($i = (count($previousIds) - 1); $i >= 0; $i--) {
          if (isset($previousIds[$i])) {
            $no = $previousIds[$i];
          } else {
            $no = 0;
          }
          //page id is loaded and usable?
          if (isset($rows[$no])) {
            return $no;
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Get boxes topic id
  *
  * @access public
  * @return integer
  */
  function getBoxesTopicId() {
    if (
         in_array(
           (int)$this->topic['box_useparent'],
           array(base_boxeslinks::INHERIT_NONE, base_boxeslinks::INHERIT_GROUPS)
         )
       ) {
      return $this->topicId;
    } else {
      return $this->getBoxParent();
    }
  }

  /**
  * Get boxes topic id
  *
  * @access public
  * @return integer
  */
  function getBoxGroupsTopicId() {
    if (
         in_array(
           $this->topic['box_useparent'],
           array(base_boxeslinks::INHERIT_NONE, base_boxeslinks::INHERIT_BOXES)
         )
       ) {
      return $this->topicId;
    } else {
      return $this->getBoxGroupParent();
    }
  }

  /**
  * Get surfer permission ids
  *
  * @access public
  * @return mixed
  */
  function getSurferPermIDs() {
    if (preg_match_all('/\d+/', $this->topic['prev_path'], $matches, PREG_PATTERN_ORDER)) {
      $previousIds = $matches[0];
    }
    if ($this->topic['prev'] > 0) {
      $previousIds[] = $this->topic['prev'];
    }
    if ($this->topic['surfer_useparent'] != 1) {
      if ($this->topic['surfer_useparent'] > 2) {
        $perms = $this->topic['surfer_permids'];
      } else {
        $perms = '';
      }

      if (isset($previousIds) && is_array($previousIds)) {
        $previousIds = array_unique($previousIds);
      }
      if (isset($previousIds) && is_array($previousIds)) {
        $filter = str_replace('%', '%%', $this->databaseGetSQLCondition('topic_id', $previousIds));
      } else {
        $filter = "topic_id = '0'";
      }
      $sql = "SELECT topic_id, surfer_useparent, surfer_permids FROM %s
              WHERE (NOT(surfer_useparent = 2))
                AND $filter
              ORDER BY topic_id DESC";
      $rows = array();
      if ($res = $this->databaseQueryFmt($sql, array($this->tableTopics))) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $rows[$row['topic_id']] = $row;
        }
      }
      if (isset($rows) && is_array($rows) &&
          isset($previousIds) && is_array($previousIds)) {
        for ($i = count($previousIds) - 1; $i >= 0; $i--) {
          $no = $previousIds[$i];
          if (isset($rows[$no]) && is_array($rows[$no])) {
            $perms .= ';'.$rows[$no]['surfer_permids'];
            if ($rows[$no]['surfer_useparent'] == 1) {
              break;
            }
          }
        }
      }
    } else {
      $perms = $this->topic['surfer_permids'];
    }
    if (preg_match_all('/\d+/', $perms, $matches, PREG_PATTERN_ORDER)) {
      $permIds = array_unique($matches[0]);
      return $permIds;
    }
    return FALSE;
  }


  /**
  * Topic exists
  *
  * @param $id
  * @access public
  * @return mixed
  */
  function topicExists($id) {
    $sql = "SELECT COUNT(topic_id)
              FROM %s
             WHERE topic_id = %d";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableTopics, (int)$id))) {
      if ($row = $res->fetchRow()) {
        return ($row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Delete cache
  *
  * @return boolean|integer
  */
  public function deleteCache() {
    $cache = PapayaCache::getService($this->papaya()->options);
    return $cache->delete('pages', $this->topicId);
  }

  /**
  * Get content language id
  *
  * @return integer language id
  */
  public function getContentLanguageId() {
    if (isset($this->topic)) {
      if (isset($this->topic['TRANSLATION'])) {
        return (int)$this->topic['TRANSLATION']['lng_id'];
      } elseif (isset($this->topic['topic_mainlanguage']) &&
                $this->topic['topic_mainlanguage'] > 0) {
        return (int)$this->topic['topic_mainlanguage'];
      }
    }
    return (int)PAPAYA_CONTENT_LANGUAGE;
  }

  /**
   * checks the current URL filename
   *
   * by default only the normalized page title and 'index' are allowed,
   * but page modules can define a own check function
   *
   * @param string $currentFileName file name part of the current url
   * @param $outputMode
   * @access public
   * @return mixed - redirect target or false
   */
  function checkURLFileName($currentFileName, $outputMode) {
    if (!empty($currentFileName)) {
      $pageFileName = $this->escapeForFilename(
        $this->topic['TRANSLATION']['topic_title'],
        'index',
        $this->currentLanguage['code']
      );
      if ($this->moduleObj instanceof PapayaPluginAddressable) {
        $url = $this->moduleObj->validateUrl($this->papaya()->request);
      } elseif (isset($this->moduleObj) && is_object($this->moduleObj) &&
          method_exists($this->moduleObj, 'checkURLFileName')) {
        $url = $this->moduleObj->checkURLFileName($currentFileName, $outputMode);
      } elseif ($currentFileName != $pageFileName) {
        $url = $this->getWebLink(
          $this->topicId, NULL, $outputMode, NULL, NULL, $pageFileName
        );
        $queryString = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : '';
        $url = $this->getAbsoluteURL($url).$this->recodeQueryString($queryString);
      } else {
        $url = FALSE;
      }
      if ($url) {
        $allowFixation = $this->papaya()->options->get('PAPAYA_URL_FIXATION', FALSE);
        if ($allowFixation) {
          if ($this->papaya()->request->getMethod() != 'get') {
            //url fixation should only be used on GET requests, not POST ...
            $allowFixation = FALSE;
          }
          if (isset($GLOBALS['PAPAYA_PAGE']) && !$GLOBALS['PAPAYA_PAGE']->public) {
            //url fixation should not be used while in preview mode
            $allowFixation = FALSE;
          }
        }
        // if the strict url fixation is disabled 'index' and $pageFileName are alyways allowed
        if (!$allowFixation &&
            ($currentFileName == 'index' || $currentFileName == $pageFileName)) {
          return FALSE;
        }
        return $url;
      }
    }
    return FALSE;
  }

  public function isPublic() {
    return FALSE;
  }

  public function getPageId() {
    return $this->topicId;
  }

  public function getPageViewId() {
    if (isset($this->topic['TRANSLATION']['view_id'])) {
      return $this->topic['TRANSLATION']['view_id'];
    }
    return 0;
  }

  public function getPageLanguage() {
    if ($this->_language instanceof PapayaContentLanguage) {
      return $this->_language;
    } elseif (0 < ($languageId = $this->getContentLanguageId())) {
      return $this->_language = $this->papaya()->languages->getLanguage($languageId);
    }
    return NULL;
  }
}

