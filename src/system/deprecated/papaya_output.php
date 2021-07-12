<?php
/**
* manage an output filter
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
* @version $Id: papaya_output.php 39666 2014-03-20 16:03:22Z weinert $
*/

/**
* manage an output filter
* @package Papaya
* @subpackage Core
*/
class papaya_output extends base_db {
  /**
  * Papaya database table views
  * @var string $tableViews
  */
  var $tableViews = PAPAYA_DB_TBL_VIEWS;
  /**
  * Papaya database table view modes
  * @var string $tableViewModes
  */
  var $tableViewModes = PAPAYA_DB_TBL_VIEWMODES;
  /**
  * Papaya database table view links
  * @var string $tableViewLinks
  */
  var $tableViewLinks = PAPAYA_DB_TBL_VIEWLINKS;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;


  /**
  * View mode
  * @var array $viewMode
  */
  var $viewMode = NULL;
  /**
  * View link
  * @var array $viewLink
  */
  var $viewLink = NULL;

  /**
  * use a static variable to cache view mode data
  * @var boolean
  */
  var $cacheViewModes = TRUE;

  /**
  * use a static variable to cache view link data
  * @var boolean
  */
  var $cacheViewLinks = TRUE;


  /**
  * Load view mode data
  *
  * @param string $ext
  * @param mixed $id optional integer, default value NULL
  * @access public
  * @return boolean
  */
  function loadViewModeData($ext, $id = NULL) {
    if ($this->viewMode = $this->rememberViewMode(NULL, $ext, $id)) {
      return TRUE;
    }
    if (isset($id)) {
      $sql = "SELECT viewmode_id, viewmode_ext, viewmode_type,
                     viewmode_sessionmode, viewmode_sessionredirect, viewmode_sessioncache,
                     viewmode_charset, viewmode_contenttype, viewmode_path, module_guid
                FROM %s
               WHERE viewmode_id = '%d'";
      $params = array($this->tableViewModes, $id);
    } else {
      $sql = "SELECT viewmode_id, viewmode_ext, viewmode_type,
                     viewmode_sessionmode, viewmode_sessionredirect, viewmode_sessioncache,
                     viewmode_charset, viewmode_contenttype, viewmode_path, module_guid
                FROM %s
               WHERE viewmode_ext = '%s'";
      $params = array($this->tableViewModes, $ext);
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->viewMode = $row;
        $this->rememberViewMode($row);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Send header
  *
  * @access public
  */
  function sendHeader() {
    if (isset($this->viewMode)) {
      $contentType = (trim($this->viewMode['viewmode_contenttype']) != '') ?
        $this->viewMode['viewmode_contenttype'] : 'text/html';
      if (substr($contentType, -4) == '+xml' &&
          isset($_SERVER['HTTP_ACCEPT'])) {
        $acceptTypes = explode(',', $_SERVER['HTTP_ACCEPT']);
        if (!in_array($contentType, $acceptTypes)) {
          if (in_array('application/xml', $acceptTypes)) {
            $contentType = 'application/xml';
          } elseif (in_array('text/xml', $acceptTypes)) {
            $contentType = 'text/xml';
          }
        }
      }
      $charset = (trim($this->viewMode['viewmode_charset']) != '') ?
        $this->viewMode['viewmode_charset'] : 'utf-8';
      $headerStr = 'Content-type: '.$contentType.'; charset='.$charset;
      $page = $this->papaya()->front;
      if ($page instanceof papaya_page) {
        $page->sendHeader($headerStr);
      } else {
        header($headerStr);
      }
    }
  }

  /**
  * Load view link data
  *
  * @param integer $viewId
  * @param integer $viewModeId
  * @access public
  * @return boolean
  */
  function loadViewLinkData($viewId, $viewModeId) {
    if ($this->viewLink = $this->rememberViewLink(NULL, $viewId, $viewModeId)) {
      return TRUE;
    }
    $sql = "SELECT vl.view_id, vl.viewmode_id, vl.viewlink_data,
                   vm.viewmode_ext, vm.viewmode_type, vm.viewmode_contenttype,
                   m.module_guid, m.module_path, m.module_file,
                   m.module_class, m.module_title,
                   mc.module_useoutputfilter
              FROM %s vl
              JOIN %s v ON (v.view_id = vl.view_id)
              JOIN %s mc ON (mc.module_guid = v.module_guid)
              LEFT OUTER JOIN %s vm ON (vm.viewmode_id = vl.viewmode_id)
              LEFT OUTER JOIN %s m ON (m.module_guid = vm.module_guid)
             WHERE vl.view_id = %d AND vl.viewmode_id = %d";
    $params = array(
      $this->tableViewLinks,
      $this->tableViews,
      $this->tableModules,
      $this->tableViewModes,
      $this->tableModules,
      $viewId,
      $viewModeId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->viewLink = $row;
        $this->rememberViewLink($row);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * preload view link data for later use in the current request
  *
  * @param integer $viewModeId
  * @param integer|array $viewIds
  * @access public
  * @return boolean
  */
  function preloadViewLinkData($viewModeId, $viewIds) {
    if ($filter = $this->databaseGetSQLCondition('vl.view_id', $viewIds)) {
      $sql = "SELECT vl.view_id, vl.viewmode_id, vl.viewlink_data,
                     vm.viewmode_ext, vm.viewmode_type, vm.viewmode_contenttype,
                     m.module_guid, m.module_path, m.module_file,
                     m.module_class, m.module_title,
                     mc.module_useoutputfilter
                FROM %s vl
                JOIN %s v ON (v.view_id = vl.view_id)
                JOIN %s mc ON (mc.module_guid = v.module_guid)
                LEFT OUTER JOIN %s vm ON (vm.viewmode_id = vl.viewmode_id)
                LEFT OUTER JOIN %s m ON (m.module_guid = vm.module_guid)
               WHERE vl.viewmode_id = %d AND $filter";
      $params = array(
        $this->tableViewLinks,
        $this->tableViews,
        $this->tableModules,
        $this->tableViewModes,
        $this->tableModules,
        $viewModeId
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->rememberViewLink($row);
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load views list
  *
  * @param integer $viewId
  * @access public
  * @return array $result
  */
  function loadViewsList($viewId) {
    $result = array();
    $sql = "SELECT vl.view_id, vl.viewmode_id,
                   vm.viewmode_ext, vm.viewmode_type, vm.viewmode_contenttype
              FROM %s vl, %s vm
             WHERE vl.view_id = %d AND vm.viewmode_id = vl.viewmode_id";
    $params = array($this->tableViewLinks, $this->tableViewModes, $viewId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['viewmode_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * Get views list
  *
  * @param integer $viewId
  * @param integer $pageId
  * @param string $pageTitle optional, default value ''
  * @access public
  * @return string $result xml
  */
  function getViewsList($viewId, $pageId, $pageTitle = '') {
    $result = '';
    $data = $this->loadViewsList($viewId);
    if (is_array($data) && count($data) > 0) {
      foreach ($data as $mode) {
        $link = $this->getWebLink(
          $pageId, NULL, $mode['viewmode_ext'], NULL, NULL, $pageTitle
        );
        if (isset($_SERVER['QUERY_STRING']) && trim($_SERVER['QUERY_STRING'] != '')) {
          $queryString = $this->recodeQueryString($_SERVER['QUERY_STRING']);
          if (strlen($queryString) > 0 && substr($queryString, 0, 1) != '?') {
            $link .= '?'.$queryString;
          } else {
            $link .= $queryString;
          }
        }
        if (
          isset($this->viewMode) &&
          $this->viewMode &&
          $this->viewMode['viewmode_ext'] === $mode['viewmode_ext']
        ) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<viewmode ext="%s" href="%s" type="%s" contenttype="%s" %s/>',
          papaya_strings::escapeHTMLChars($mode['viewmode_ext']),
          papaya_strings::escapeHTMLChars($link),
          papaya_strings::escapeHTMLChars($mode['viewmode_type']),
          papaya_strings::escapeHTMLChars($mode['viewmode_contenttype']),
          $selected
        );
      }
    }
    return $result;
  }

  /**
  * Get Filter
  *
  * @param integer $viewId
  * @access public
  * @return mixed object uses viewLink['module_class'] for class name or NULL
  */
  function getFilter($viewId) {
    $result = FALSE;
    if (isset($this->viewMode) &&
        $this->loadViewLinkData($viewId, $this->viewMode['viewmode_id'])) {
      $parent = NULL;
      if ($this->viewLink['module_useoutputfilter']) {
        $result = $this->papaya()->plugins->get(
          $this->viewLink['module_guid'],
          $parent,
          $this->viewLink['viewlink_data']
        );
        if ($result) {
          if (!empty($this->viewMode['viewmode_path'])) {
            $result->templatePath = $this->viewMode['viewmode_path'];
          }
        }
      } else {
        $result = new papaya_filter_passthru($parent);
      }
    }
    return $result;
  }

  /**
  * cache view mode data in a static variable
  *  (for all instances of thhis class in a request)
  *
  * @param NULL | array $viewModeData optional, default value NULL
  * @param NULL | string $ext optional, default value NULL
  * @param NULL | integer $id optional, default value NULL
  * @access public
  * @return array | FALSE
  */
  function rememberViewMode($viewModeData = NULL, $ext = NULL, $id = NULL) {
    static $viewModeCache;
    if ($this->cacheViewModes) {
      if (isset($viewModeData) && is_array($viewModeData)) {
        $viewModeCache[$viewModeData['viewmode_id']] = $viewModeData;
        $viewModeCache['.ext'][$viewModeData['viewmode_ext']] =
          &$viewModeCache[$viewModeData['viewmode_id']];
      }
      if (isset($id) &&
          isset($viewModeCache[$id])) {
        return $viewModeCache[$id];
      }
      if (isset($ext) &&
          isset($viewModeCache['.ext']) &&
          isset($viewModeCache['.ext'][$ext])) {
        return $viewModeCache['.ext'][$ext];
      }
    }
    return FALSE;
  }

  /**
  * Cache view link data in a static variable
  * (for all instances of this class in a request)
  *
  * @param NULL | array $viewLinkData optional, default value NULL
  * @param NULL | integer $viewId optional, default value NULL
  * @param NULL | integer $viewModeId optional, default value NULL
  * @access public
  * @return array | FALSE
  */
  function rememberViewLink($viewLinkData = NULL, $viewId = NULL, $viewModeId = NULL) {
    static $viewLinkCache;
    if ($this->cacheViewLinks) {
      if (isset($viewLinkData) && is_array($viewLinkData)) {
        $viewLinkCache[$viewLinkData['viewmode_id']][$viewLinkData['view_id']] =
        $viewLinkData;
      }
      if (isset($viewId) &&
          isset($viewModeId) &&
          isset($viewLinkCache[$viewModeId]) &&
          isset($viewLinkCache[$viewModeId][$viewId])) {
        return $viewLinkCache[$viewModeId][$viewId];
      }
    }
    return FALSE;
  }
}

