<?php
/**
* Page import handler class
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
* @subpackage Administration
* @version $Id: papaya_import.php 39818 2014-05-13 13:15:13Z weinert $
*/

/**
 * Page import handler class
*
 * @package Papaya
* @subpackage Administration
*/
class papaya_import extends base_import {
  /**
  * Filters
  * @var array $filters
  */
  var $filters = array();
  /**
  * Filter links
  * @var array $filterLinks
  */
  var $filterLinks = array();
  /**
  * Single filter link
  * @var array $filterLink
  */
  var $filterLink = NULL;

  /**
  * Parameter
  * @var string $paramName
  */
  var $paramName = 'imp';

  /**
   * @var array
   */
  public $images = array();

  /**
   * @var PapayaTemplate
   */
  public $layout = NULL;

  /**
   * @var array
   */
  public $moduleGroups = array();

  /**
   * @var array
   */
  public $importModules = array();

  /**
   * @var base_dialog
   */
  private $filterDialog;

  /**
  * Initialization
  *
  * @see papaya_import::initializeParams()
  * @access public
  */
  function initialize() {
    $this->initializeParams();
  }

  /**
  * Execution - handling parameters
  *
  * @access public
  */
  function execute() {
    $this->loadImportModulesList();
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'importfilter_add':
        $this->initializeFilterDialog();
        if ($this->filterDialog->checkDialogInput()) {
          if (!$this->filterExtUsed($this->params['importfilter_ext'])) {
            if ($newId = $this->addFilter()) {
              $this->params['filter_id'] = $newId;
              unset($this->filterDialog);
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error.'));
            }
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Extension already used.'));
          }
        }
        break;
      case 'importfilter_edit':
        if (isset($this->params['filter_id']) && $this->params['filter_id'] > 0 &&
            $this->loadFilter($this->params['filter_id'])) {
          $this->initializeFilterDialog();
          if ($this->filterDialog->checkDialogInput()) {
            if ($this->filter['importfilter_ext'] !=
                  strToLower($this->params['importfilter_ext']) &&
                (!$this->filterExtUsed($this->params['importfilter_ext']))) {
              if ($this->saveFilter($this->filter['importfilter_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Filter modified.'));
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Database error.'));
              }
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Extension already used.'));
            }
          }
        }
        break;
      case 'importfilter_delete':
        if (isset($this->params['filter_id']) && $this->params['filter_id'] > 0 &&
            isset($this->params['confirm_delete']) && $this->params['confirm_delete'] &&
            $this->loadFilter($this->params['filter_id'])) {
          if ($this->deleteFilter($this->filter['importfilter_id'])) {
            unset($this->filter);
            unset($this->params['cmd']);
            unset($this->params['filter_id']);
            $this->addMsg(MSG_INFO, $this->_gt('Filter deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error.'));
          }
        }
        break;
      case 'import_link':
        if (isset($this->params['view_id']) &&
            $this->params['view_id'] > 0 &&
            isset($this->params['filter_id']) &&
            $this->params['filter_id'] > 0) {
          $this->addFilterLink($this->params['view_id'], $this->params['filter_id']);
        }
        break;
      case 'importlink_edit':
        if (isset($this->params['view_id']) &&
            $this->params['view_id'] > 0 &&
            isset($this->params['filter_id']) &&
            $this->params['filter_id'] > 0) {
          $this->executeFilterLinkEdit(
            $this->params['view_id'], $this->params['filter_id']
          );
        }
        break;
      case 'import_unlink':
        if (isset($this->params['view_id']) &&
            $this->params['view_id'] > 0 &&
            isset($this->params['filter_id']) &&
            $this->params['filter_id'] > 0 &&
            $this->loadFilterLink($this->params['view_id'], $this->params['filter_id']) &&
            isset($this->params['confirm_unlink']) &&
            $this->params['confirm_unlink']) {
          if ($this->deleteFilterLink($this->params['view_id'], $this->params['filter_id'])) {
            unset($this->filterLink);
          }
        }
        break;
      }
    }
    if (isset($this->params['filter_id']) && $this->params['filter_id'] > 0) {
      $this->loadFilter($this->params['filter_id']);
    }
    $this->loadFilters();
  }

  /**
   * Execute view link edit
   *
   * @param integer $viewId
   * @param integer $filterId
   * @access public
   * @return boolean
   */
  function executeFilterLinkEdit($viewId, $filterId) {
    if ($this->loadFilterLink($viewId, $filterId)) {
      $parent = NULL;
      /**
       * @var base_importfilter $moduleObj
       */
      $moduleObj = $this->papaya()->plugins->get(
        $this->filterLink['module_guid'],
        $parent,
        $this->filterLink['importfilter_data']
      );
      if (isset($moduleObj) && is_object($moduleObj)) {
        $moduleObj->paramName = $this->paramName;
        $hidden = array(
          'view_id' => $this->filterLink['view_id'],
          'filter_id' => $this->filterLink['importfilter_id'],
          'cmd' => 'importlink_edit'
        );
        $moduleObj->initializeDialog($hidden);
        if ($moduleObj->modified()) {
          if ($moduleObj->checkData()) {
            $saved = $this->saveFilterLinkContent(
              $this->filterLink['view_id'],
              $this->filterLink['importfilter_id'],
              $moduleObj->getData()
            );
            if ($saved) {
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
            }
          }
        }
        $this->layout->add($moduleObj->getForm($hidden));
        unset($moduleObj);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get xml
  *
  * @see papaya_import::getXMLFilterList()
  * @access public
  */
  function getXML() {
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    switch ($this->params['cmd']) {
    case 'importfilter_delete':
      $this->getFilterDeleteDialog();
      break;
    case 'import_unlink':
      $this->getFilterUnlinkDialog();
      break;
    default :
      $this->getFilterDialog();
      break;
    }
    $this->getXMLFilterList();
  }

  /**
  * Get buttons' xml
  *
  * @param base_btnbuilder $toolbar
  * @access public
  */
  function getButtonsXML($toolbar) {
    $toolbar->addButton(
      'Add filter',
      $this->getLink(array('viewmode_id' => 0)),
      'actions-filter-add',
      ''
    );
    if (isset($this->filter) && is_array($this->filter)) {
      $toolbar->addButton(
        'Delete filter',
        $this->getLink(
          array(
            'cmd' => 'importfilter_delete',
            'filter_id' => (int)$this->filter['importfilter_id']
          )
        ),
        'actions-filter-delete',
        ''
      );
    }
  }

  /**
  * Get import link xml
  *
  * @access public
  */
  function getImportLinkXML() {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'import_unlink':
        $this->getFilterUnlinkDialog();
        break;
      }
    }
  }

  /**
  * Filter extension used?
  *
  * @param string $filterExt
  * @access public
  * @return boolean used or not
  */
  function filterExtUsed($filterExt) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE importfilter_ext = '%s'";
    $params = array($this->tableImportFilter, StrToLower($filterExt));
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() > 0);
    }
    return FALSE;
  }

  /**
  * Load filters into $this->filters array
  *
  * @access public
  */
  function loadFilters() {
    $this->filters = array();
    $sql = "SELECT importfilter_id, importfilter_ext
              FROM %s
             ORDER BY importfilter_ext";
    if ($res = $this->databaseQueryFmt($sql, $this->tableImportFilter)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->filters[$row['importfilter_id']] = $row;
      }
    }
  }

  /**
  * Load import modules list into $this->importModules array
  *
  * @access public
  */
  function loadImportModulesList() {
    unset($this->importModules);
    $sql = "SELECT module_guid, module_title, modulegroup_id
              FROM %s
             WHERE module_type = 'import' AND module_active = 1
             ORDER BY module_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableModules)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->importModules[$row['module_guid']] = $row;
      }
    }
  }

  /**
  * Load filter into $this->filter by id
  *
  * @param integer $filterId
  * @access public
  * @return boolean loaded or not
  */
  function loadFilter($filterId) {
    unset($this->filter);
    $sql = "SELECT importfilter_id, importfilter_ext, module_guid
              FROM %s
             WHERE importfilter_id = '%d'";
    $params = array($this->tableImportFilter, (int)$filterId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->filter = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Add filter to database by id
  *
  * @access public
  * @return mixed FALSE or Id of new record
  */
  function addFilter() {
    $data = array(
      'importfilter_ext' => $this->params['importfilter_ext'],
      'module_guid' => $this->params['module_guid']
    );
    return $this->databaseInsertRecord(
      $this->tableImportFilter, 'importfilter_id', $data
    );
  }

  /**
  * Save filter changes to database by id
  *
  * @param integer $filterId
  * @access public
  * @return boolean saved or not
  */
  function saveFilter($filterId) {
    $data = array(
      'importfilter_ext' => $this->params['importfilter_ext'],
      'module_guid' => $this->params['module_guid']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableImportFilter, $data, 'importfilter_id', (int)$filterId
    );
  }

  /**
  * Delete filter from database by id
  *
  * @param integer $filterId
  * @access public
  * @return boolean
  */
  function deleteFilter($filterId) {
    return (
      FALSE !== $this->databaseDeleteRecord(
        $this->tableImportFilterLinks, 'importfilter_id', (int)$filterId
      ) &&
      FALSE !== $this->databaseDeleteRecord(
        $this->tableImportFilter, 'importfilter_id', (int)$filterId
      )
    );
  }

  /**
  * Load filter links to $this->filterLinks array
  *
  * @param integer $viewId
  * @access public
  * @return boolean loaded or not
  */
  function loadFilterLinks($viewId) {
    $this->filterLinks = array();
    $sql = "SELECT fl.view_id, fl.importfilter_id, im.importfilter_ext
              FROM %s fl
              LEFT OUTER JOIN %s im ON (im.importfilter_id = fl.importfilter_id)
             WHERE fl.view_id = %d
             ORDER BY importfilter_ext";
    $params = array($this->tableImportFilterLinks, $this->tableImportFilter, $viewId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->filterLinks[$row['importfilter_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load filter link to $this->filterLink array
  *
  * @param integer $viewId
  * @param integer $filterId
  * @access public
  * @return boolean loaded or not
  */
  function loadFilterLink($viewId, $filterId) {
    unset($this->filterLink);
    $sql = "SELECT fl.view_id, fl.importfilter_id, fl.importfilter_data,
                   im.importfilter_ext,
                   m.module_guid, m.module_path, m.module_file,
                   m.module_class, m.module_title
              FROM %s fl
              LEFT OUTER JOIN %s im ON (im.importfilter_id = fl.importfilter_id)
              LEFT OUTER JOIN %s m ON (m.module_guid = im.module_guid)
             WHERE fl.view_id = %d AND fl.importfilter_id = %d";
    $params = array($this->tableImportFilterLinks, $this->tableImportFilter,
      $this->tableModules, $viewId, $filterId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->filterLink = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Add filter link to database
  *
  * @param integer $viewId
  * @param integer $filterId
  * @access public
  * @return boolean added or not
  */
  function addFilterLink($viewId, $filterId) {
    $data = array(
      'view_id' => $viewId,
      'importfilter_id' => $filterId
    );
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE ".$this->databaseGetSQLCondition($data);
    if ($res = $this->databaseQueryFmt($sql, $this->tableImportFilterLinks)) {
      if ($res->fetchField() < 1) {
        return FALSE !== $this->databaseInsertRecord(
          $this->tableImportFilterLinks, NULL, $data
        );
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Save filter link content changes to database
  *
  * @param integer $viewId
  * @param integer $filterId
  * @param string $content xml filter link content
  * @access public
  * @return boolean saved or not
  */
  function saveFilterLinkContent($viewId, $filterId, $content) {
    $cond = array(
      'view_id' => $viewId,
      'importfilter_id' => $filterId
    );
    $data = array(
      'importfilter_data' => $content
    );
    return (FALSE !== $this->databaseUpdateRecord($this->tableImportFilterLinks, $data, $cond));
  }

  /**
  * Delete filter link from database
  *
  * @param integer $viewId
  * @param integer $filterId
  * @access public
  * @return boolean deleted or not
  */
  function deleteFilterLink($viewId, $filterId) {
    $cond = array(
      'view_id' => $viewId,
      'importfilter_id' => $filterId
    );
    return (FALSE !== $this->databaseDeleteRecord($this->tableImportFilterLinks, $cond));
  }

  /**
  * Get filter list from $this->filters to xml
  *
  * @access public
  */
  function getXMLFilterList() {
    if (isset($this->filters) && is_array($this->filters) &&
        count($this->filters) > 0) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Import Filter'))
      );
      $result .= '<items>';
      foreach ($this->filters as $filterId => $filter) {
        $selected = (isset($this->filter) && $this->filter['importfilter_id'] == $filterId)
          ? ' selected="selected"' : '';
        $result .= sprintf(
          '<listitem href="%s" title="%s" image="%s" %s/>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cmd' => 'show', 'filter_id' => (int)$filterId))
          ),
          papaya_strings::escapeHTMLChars($filter['importfilter_ext']),
          papaya_strings::escapeHTMLChars($this->images['items-filter-import']),
          $selected
        );
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addLeft($result);
    }
  }

  /**
  * Initialize filter dialog
  *
  * @access public
  */
  function initializeFilterDialog() {
    if (!(isset($this->filterDialog) && is_object($this->filterDialog))) {
      if (isset($this->filter) && is_array($this->filter)) {
        $data = $this->filter;
        $hidden = array(
          'cmd' => 'importfilter_edit',
          'filter_id' => (int)$this->filter['importfilter_id']
        );
        $btnCaption = 'Save';
      } else {
        $data = array();
        $hidden = array(
          'cmd' => 'importfilter_add'
        );
        $btnCaption = 'Add';
      }
      $viewModules = array();
      if (isset($this->importModules) && is_array($this->importModules) &&
          count($this->importModules) > 0) {
        foreach ($this->importModules as $module) {
          if (isset($this->moduleGroups[$module['modulegroup_id']])) {
            $viewModules[$module['module_guid']] =
              '['.$this->moduleGroups[$module['modulegroup_id']]['modulegroup_title'].'] '.
                $module['module_title'];
          } else {
            $viewModules[$module['module_guid']] = $module['module_title'];
          }
        }
        asort($viewModules);
      }
      $fields = array(
        'importfilter_ext' => array('Extension', 'isAlphaNum', TRUE, 'input', 200),
        'module_guid' => array('Filter Module', 'isGuid', TRUE, 'combo',
          $viewModules, '')
      );
      $this->filterDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->filterDialog->loadParams();
      $this->filterDialog->dialogTitle = $this->_gt('Import Filter');
      $this->filterDialog->buttonTitle = $btnCaption;
      $this->filterDialog->dialogDoubleButtons = FALSE;
    }
  }

  /**
  * Get import filter dialog
  *
  * @access public
  */
  function getFilterDialog() {
    $this->initializeFilterDialog();
    $this->layout->add($this->filterDialog->getDialogXML());
  }


  /**
  * Get delete form
  *
  * @access public
  */
  function getFilterDeleteDialog() {
    if (isset($this->filter) && is_array($this->filter)) {
      $hidden = array(
        'cmd' => 'importfilter_delete',
        'filter_id' => $this->filter['importfilter_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete filter "%s" (%s)?'),
        $this->filter['importfilter_ext'],
        (int)$this->filter['importfilter_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }



  /**
  * Get view link information
  *
  * @access public
  */
  function getImportLinkInfos($viewId) {
    if (isset($this->filters) && is_array($this->filters) &&
        count($this->filters) > 0) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Import filter'))
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Extension'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Linked'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->filters as $filter) {
        $href = $this->getLink(
          array(
            'cmd' => 'importlink_edit',
            'view_id' => $viewId,
            'filter_id' => $filter['importfilter_id']
          )
        );
        if (isset($this->params['filter_id']) &&
            $this->params['filter_id'] == $filter['importfilter_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s"%s>',
          papaya_strings::escapeHTMLChars($filter['importfilter_ext']),
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($this->images['items-filter-import']),
          $selected
        );
        if (isset($this->filterLinks[$filter['importfilter_id']])) {
          $activeImage = 'status-node-checked';
          $activeString = 'Yes';
          $cmd = 'import_unlink';
        } else {
          $activeImage = 'status-node-empty';
          $activeString = 'No';
          $cmd = 'import_link';
        }
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" alt="%s"/></a></subitem>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd' => $cmd,
                'view_id' => $viewId,
                'filter_id' => $filter['importfilter_id']
              )
            )
          ),
          papaya_strings::escapeHTMLChars($this->images[$activeImage]),
          papaya_strings::escapeHTMLChars($this->_gt($activeString))
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addRight($result);
    }
  }

  /**
  * Get delete form
  *
  * @access public
  */
  function getFilterUnlinkDialog() {
    if (isset($this->filterLink) && is_array($this->filterLink)) {
      $hidden = array(
        'cmd' => 'import_unlink',
        'view_id' => $this->filterLink['view_id'],
        'filter_id' => $this->filterLink['importfilter_id'],
        'confirm_unlink' => 1
      );
      $msg = sprintf(
        $this->_gt('Unlink import filter "%s" (%d)?'),
        $this->filterLink['importfilter_ext'],
        (int)$this->filterLink['importfilter_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->buttonTitle = 'Unlink';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Check if import view record exists
  * @param integer $viewId
  * @return boolean
  */
  function hasImportView($viewId) {
    if ($viewId > 0) {
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE view_id = '%d'";
      $params = array($this->tableImportFilterLinks, $viewId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        return ($res->fetchField() > 0);
      }
    }
    return FALSE;
  }
}


