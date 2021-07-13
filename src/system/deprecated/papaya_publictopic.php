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

use Papaya\Cache;

/**
* Show published pages
*
* @package Papaya
* @subpackage Frontend
*/
class papaya_publictopic extends base_topic {
  /**
  * Papaya database table
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS_PUBLIC;
  /**
  * Papaya database table
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS;
  /**
  * Maximum of versions
  * @var integer $maxVersions
  */
  var $maxVersions = -1;

  /**
  * Check publish period
  *
  * @param integer $topicId
  * @access public
  * @return bool
  */
  function checkPublishPeriod($topicId) {
    if (isset($this->topic) && $this->topicId == $topicId &&
        isset($this->topic['published_from']) && isset($this->topic['published_to'])) {
      $publishFrom = $this->topic['published_from'];
      $publishTo = $this->topic['published_to'];
    } else {
      $publishFrom = -1;
      $publishTo = -1;
      $sql = 'SELECT published_from, published_to
                FROM %s
               WHERE topic_id= %d';
      $params = array($this->tableTopicsPublic, $topicId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $publishFrom = $row['published_from'];
          $publishTo = $row['published_to'];
        }
      }
    }
    $currentTime = time();
    if ($publishFrom < 0 || $publishTo < 0) {
      return FALSE;
    } elseif ($publishFrom >= $publishTo) {
      if ($currentTime >= $publishFrom) {
        return TRUE;
      }
    } elseif ($currentTime >= $publishFrom && $currentTime <= $publishTo) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load output
  *
  * @param integer $topicId
  * @param integer $lngIdent
  * @param integer $versionTime optional, default value 0
  * @access public
  * @return bool
  */
  function loadOutput($topicId, $lngIdent, $versionTime = 0) {
    if (is_integer($lngIdent) && $lngIdent > 0) {
      $lngId = (int)$lngIdent;
    } elseif (!($lngId = $this->languageIdentToId($lngIdent))) {
      $lngId = 0;
    }

    $loaded = FALSE;
    $sql = "SELECT topic_id, prev, prev_path,
                   topic_mainlanguage,
                   topic_created, topic_modified,
                   topic_cachemode, topic_cachetime,
                   topic_expiresmode, topic_expirestime,
                   topic_protocol,
                   published_from, published_to,
                   box_useparent, meta_useparent, surfer_useparent
              FROM %s
             WHERE topic_id = %d";
    $params = array($this->tableTopics, $topicId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topicId = (int)$row['topic_id'];
        $this->topic = $row;
        $loaded = TRUE;
      }
    }

    if ($loaded) {
      $contentLoaded = FALSE;
      //remember languages already tried to load
      $usedLanguages = array();
      //try to load given language
      if ($lngId > 0) {
        //check for a translation in the current language
        $contentLoaded = $this->loadTranslatedData($topicId, $lngId);
        $usedLanguages[] = $lngId;
      }

      //try to load surfer default language
      if (
        (!$contentLoaded) &&
        (!empty($this->papaya()->front->visitorLanguage))
      ) {
        if (($lngId = $this->languageIdentToId($this->papaya()->front->visitorLanguage)) &&
            (!in_array($lngId, $usedLanguages))) {
          $contentLoaded = $this->loadTranslatedData(
            $topicId,
            $lngId
          );
          $usedLanguages[] = $lngId;
        }
      }

      //try to load topic default language
      if ((!$contentLoaded) &&
          $this->topic['topic_mainlanguage'] > 0 &&
          (!in_array($this->topic['topic_mainlanguage'], $usedLanguages))) {
        $contentLoaded = $this->loadTranslatedData(
          $topicId, $this->topic['topic_mainlanguage']
        );
        $usedLanguages[] = $this->topic['topic_mainlanguage'];
      }
      //try to load system default language
      if ((!$contentLoaded) &&
          defined('PAPAYA_CONTENT_LANGUAGE') &&
          PAPAYA_CONTENT_LANGUAGE > 0 &&
          (!in_array(PAPAYA_CONTENT_LANGUAGE, $usedLanguages))) {
        $contentLoaded = $this->loadTranslatedData(
          $topicId, PAPAYA_CONTENT_LANGUAGE
        );
      }
      if ($contentLoaded) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * get the cache id for the current page
  *
  * @see base_plugin::$cacheable
  * @see base_boxeslinks::getBoxCacheId() for a similar function for boxes
  *
  * This calls getCacheId() on the $moduleObj.
  * @see base_actionbox::getCacheId() for an example
  * (The semantics of that function for boxes and pages are the same.)
  *
  * @param base_content object $moduleObj
  * @param bool $pageContent page output or short text
  * @access public
  * @return string
  */
  function getContentCacheId($moduleObj, $pageContent = TRUE) {
    $type = $pageContent ? 'content' : 'teaser';
    if ($status = $this->getCacheDefinition($moduleObj)->getStatus()) {
      $cacheId = sprintf(
        '.%s_%s.xml', $type, md5(serialize($status))
      );
      return $cacheId;
    }
    return FALSE;
  }

  /**
   * get the cache identifer definition object for a page
   *
   * @param object $pagePlugin
   * @return Cache\Identifier\Definition
   */
  function getCacheDefinition($pagePlugin) {
    $pageOptions = $this->papaya()->front->sessionParams;
    $definition = NULL;
    if ($pagePlugin instanceof \Papaya\Plugin\Cacheable) {
      $definition = $pagePlugin->cacheable();
    } elseif (!property_exists($pagePlugin, 'cacheable') || $pagePlugin->cacheable === FALSE) {
      return new Cache\Identifier\Definition\BooleanValue(FALSE);
    } elseif (method_exists($pagePlugin, 'getCacheId')) {
      $definition = new Cache\Identifier\Definition\Callback(array($pagePlugin, 'getCacheId'));
    } else {
      $definition = new Cache\Identifier\Definition\Group(
        new Cache\Identifier\Definition\URL(),
        new Cache\Identifier\Definition\Values($pageOptions)
      );
    }
    if ($definition) {
      return new Cache\Identifier\Definition\Group(
        new Cache\Identifier\Definition\BooleanValue(\Papaya\Utility\Request\Method::isGet()),
        new \Papaya\CMS\Cache\Identifier\Definition\Page(),
        new Cache\Identifier\Definition\Surfer(),
        $definition
      );
    } else {
      return new Cache\Identifier\Definition\BooleanValue(FALSE);
    }
  }

  /**
  * Get cache time for page content
  * @return integer seconds
  */
  function getContentCacheTime() {
    if (defined('PAPAYA_CACHE_PAGES') && PAPAYA_CACHE_PAGES) {
      if (isset($this->topic['topic_cachemode'])) {
        switch ($this->topic['topic_cachemode']) {
        case 1 :
          //system cache time
          if (defined('PAPAYA_CACHE_TIME_PAGES') &&
              PAPAYA_CACHE_TIME_PAGES > 0) {
            return (int)PAPAYA_CACHE_TIME_PAGES;
          }
          break;
        case 2 :
          if (isset($this->topic['topic_cachetime']) &&
              $this->topic['topic_cachetime'] > 0) {
            return (int)$this->topic['topic_cachetime'];
          }
        }
      }
    }
    return 0;
  }

  /**
  * Load cache data for a page content
  *
  * @param string $cacheId
  * @return string
  */
  function getContentCache($cacheId) {
    $cache = \Papaya\CMS\Cache\Cache::getService($this->papaya()->options);
    return $cache->read(
      'pages',
      $this->topicId,
      $cacheId,
      $this->getContentCacheTime(),
      $this->topic['TRANSLATION']['topic_trans_modified']
    );
  }

  /**
  * Write cache data for a page content
  *
  * @param string $cacheId
  * @param string $contentStr
  * @return bool
  */
  function writeContentCache($cacheId, $contentStr) {
    $expires = $this->getContentCacheTime();
    if ($expires > 0) {
      $cache = \Papaya\CMS\Cache\Cache::getService($this->papaya()->options);
      return $cache->write('pages', $this->topicId, $cacheId, $contentStr, $expires);
    }
    return FALSE;
  }

  public function isPublic() {
    return TRUE;
  }
}
