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
* data filter plugins administration
*
* @package Papaya
* @subpackage Core
*/
class papaya_datafilter_list extends base_datafilter_list {

  /**
  * Filters
  * @var array $filters
  */
  var $filters = array();

  /**
  * Single filter link
  * @var array $filterLink
  */
  var $filterLink = NULL;

  /**
  * Filter links
  * @var array $filterLinks
  */
  var $filterLinks = array();

  /**
  * Filter dialog object
  * @var object $filterDialog base_dialog
  */
  var $filterDialog = NULL;

  /**
  * Data filter modules
  * @var array $dataFilterModules
  */
  var $dataFilterModules = NULL;

  /**
  * Parameter
  * @var string $paramName
  */
  var $paramName = 'daf';

  /**
   * @var array
   */
  public $filter = NULL;
  /**
   * @var array
   */
  public $moduleGroups = array();

  /**
   * @var \Papaya\Template
   */
  public $layout = NULL;
  /**
   * @var array
   */
  public $images = array();

  /**
   * Initialization
   *
   * @access public
   * @param null $contentObj
   */
  function initialize($contentObj = NULL) {
    $this->initializeParams();
  }

  /**
  * Execution - handling parameters
  *
  * @access public
  */
  function execute() {
    $this->loadDataFilterModulesList();
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'datafilter_add':
        $this->initializeFilterDialog();
        if ($this->filterDialog->checkDialogInput()) {
          if ($this->filterTitleUsed($this->params['datafilter_title'])) {
            $this->addMsg(MSG_ERROR, $this->_gt('Title already used.'));
          } elseif ($this->filterModuleUsed($this->params['module_guid'])) {
            $this->addMsg(MSG_ERROR, $this->_gt('Filter module already used.'));
          } else {
            if ($newId = $this->addFilter()) {
              $this->params['datafilter_id'] = $newId;
              unset($this->filterDialog);
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error.'));
            }
          }
        }
        break;
      case 'datafilter_edit':
        if (isset($this->params['datafilter_id']) &&
            $this->params['datafilter_id'] > 0 &&
            $this->loadFilter($this->params['datafilter_id'])) {
          $this->initializeFilterDialog();
          if ($this->filterDialog->checkDialogInput()) {
            $filterModuleUsed = $this->filterModuleUsed($this->params['module_guid']);
            $filterModuleChanged = strtolower($this->filter['module_guid']) !=
              strtolower($this->params['module_guid']);
            $filterTitleUsed = $this->filterTitleUsed($this->params['datafilter_title']);
            $filterTitleChanged = strtolower($this->filter['datafilter_title']) !=
              strtolower($this->params['datafilter_title']);
            if ($filterTitleChanged && $filterTitleUsed) {
              $this->addMsg(MSG_ERROR, $this->_gt('Title already used.'));
            } elseif ($filterModuleChanged && $filterModuleUsed) {
              $this->addMsg(MSG_ERROR, $this->_gt('Filter module already used.'));
            } elseif ($filterTitleChanged ||$filterModuleChanged) {
              if ($this->saveFilter($this->filter['datafilter_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Filter modified.'));
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Database error.'));
              }
            }
          }
        }
        break;
      case 'datafilter_delete':
        if (isset($this->params['datafilter_id']) &&
            $this->params['datafilter_id'] > 0 &&
            isset($this->params['confirm_delete']) &&
            $this->params['confirm_delete'] &&
            $this->loadFilter($this->params['datafilter_id'])) {
          if (isset($this->filter['datafilter_id']) &&
              $this->deleteFilter($this->filter['datafilter_id'])) {
            unset($this->filter);
            unset($this->params['cmd']);
            unset($this->params['datafilter_id']);
            $this->addMsg(MSG_INFO, $this->_gt('Filter deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error.'));
          }
        }
        break;
      case 'datafilter_link':
        if (isset($this->params['view_id']) &&
            $this->params['view_id'] > 0 &&
            isset($this->params['datafilter_id']) &&
            $this->params['datafilter_id'] > 0) {
          $this->addFilterLink(
            $this->params['view_id'], $this->params['datafilter_id']
          );
        }
        break;
      case 'datafilterlink_edit':
        if (isset($this->params['view_id']) &&
            $this->params['view_id'] > 0 &&
            isset($this->params['datafilter_id']) &&
            $this->params['datafilter_id'] > 0) {
          $this->executeFilterLinkEdit(
            $this->params['view_id'], $this->params['datafilter_id']
          );
        }
        break;
      case 'datafilter_unlink':
        if (isset($this->params['view_id']) &&
            $this->params['view_id'] > 0 &&
            isset($this->params['datafilter_id']) &&
            $this->params['datafilter_id'] > 0 &&
            $this->loadFilterLink(
              $this->params['view_id'], $this->params['datafilter_id']
            ) &&
            isset($this->params['confirm_unlink']) &&
            $this->params['confirm_unlink']) {
          if ($this->deleteFilterLink($this->params['view_id'], $this->params['datafilter_id'])) {
            unset($this->filterLink);
          }
        }
        break;
      }
    }
    if (isset($this->params['datafilter_id']) &&
        $this->params['datafilter_id'] > 0) {
      $this->loadFilter($this->params['datafilter_id']);
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
      $plugin = $this->papaya()->plugins->get(
        $this->filterLink['module_guid'],
        $parent,
        $this->filterLink['datafilter_data']
      );
      if ($plugin instanceof PapayaPluginEditable) {
        $pluginNode = $this->layout->values()->getValueByPath('/page/centercol');
        if ($plugin->content()->editor()) {
          $plugin->content()->editor()->context()->merge(
            array(
              $this->paramName => array(
                'view_id' => $this->filterLink['view_id'],
                'datafilter_id' => $this->filterLink['datafilter_id'],
                'cmd' => 'datafilterlink_edit'
              )
            )
          );
          $pluginNode->append($plugin->content()->editor());
          if ($plugin->content()->modified()) {
            $saved = $this->saveFilterLinkContent(
              $this->filterLink['view_id'], $this->filterLink['datafilter_id'], $plugin->content()->getXml()
            );
            if ($saved) {
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
            }
          }
        }
        return TRUE;
      } elseif ($plugin instanceof base_datafilter) {
        $plugin->paramName = $this->paramName;
        $hidden = [
          'view_id' => $this->filterLink['view_id'],
          'datafilter_id' => $this->filterLink['datafilter_id'],
          'cmd' => 'datafilterlink_edit'
        ];
        $plugin->initializeDialog($hidden);
        if ($plugin->modified()) {
          if ($plugin->checkData()) {
            if (
            $this->saveFilterLinkContent(
              $this->filterLink['view_id'],
              $this->filterLink['datafilter_id'],
              $plugin->getData()
            )
            ) {
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
            }
          }
        }
        $this->layout->add($plugin->getForm());
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get xml
  *
  * @access public
  */
  function getXML() {
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    switch ($this->params['cmd']) {
    case 'datafilter_delete':
      $this->getFilterDeleteDialog();
      break;
    default:
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
            'cmd' => 'datafilter_delete',
            'datafilter_id' => (int)$this->filter['datafilter_id']
          )
        ),
        'actions-filter-delete',
        ''
      );
    }
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
        papaya_strings::escapeHTMLChars($this->_gt('Data filter'))
      );
      $result .= '<items>';
      foreach ($this->filters as $filterId => $filter) {
        $selected = (isset($this->filter) && $this->filter['datafilter_id'] == $filterId)
          ? ' selected="selected"' : '';
        $result .= sprintf(
          '<listitem href="%s" title="%s" image="%s" %s/>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cmd' => 'show', 'datafilter_id' => (int)$filterId))
          ),
          papaya_strings::escapeHTMLChars($filter['datafilter_title']),
          papaya_strings::escapeHTMLChars($this->images['items-filter-convert']),
          $selected
        );
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get data filter link xml
  *
  * @access public
  */
  function getDataFilterLinkXML() {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'datafilter_unlink':
        $this->getFilterUnlinkDialog();
        break;
      }
    }
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
      'datafilter_id' => $filterId
    );
    $data = array(
      'datafilter_data' => $content
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableDataFilterLinks, $data, $cond
    );
  }
  /**
  * Get unlink form
  *
  * @access public
  */
  function getFilterUnlinkDialog() {
    if (isset($this->filterLink) && is_array($this->filterLink)) {
      $hidden = array(
        'cmd' => 'datafilter_unlink',
        'view_id' => $this->filterLink['view_id'],
        'datafilter_id' => $this->filterLink['datafilter_id'],
        'confirm_unlink' => 1
      );
      $msg = sprintf(
        $this->_gt('Unlink data filter "%s" (%d)?'),
        papaya_strings::escapeHTMLChars($this->filterLink['datafilter_title']),
        (int)$this->filterLink['datafilter_id']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->buttonTitle = 'Unlink';
      $this->layout->add($dialog->getMsgDialog());
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
  * Get view link information
  *
  * @access public
  */
  function getDataFilterLinkInfos($viewId) {
    if (isset($this->filters) && is_array($this->filters) &&
        count($this->filters) > 0) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Data filter'))
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Title'))
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
            'cmd' => 'datafilterlink_edit',
            'view_id' => $viewId,
            'datafilter_id' => $filter['datafilter_id']
          )
        );
        if (isset($this->params['datafilter_id']) &&
            $this->params['datafilter_id'] == $filter['datafilter_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s"%s>',
          papaya_strings::escapeHTMLChars($filter['datafilter_title']),
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($this->images['items-filter-convert']),
          $selected
        );
        if (isset($this->filterLinks[$filter['datafilter_id']])) {
          $activeImage = 'status-node-checked';
          $activeString = 'Yes';
          $cmd = 'datafilter_unlink';
        } else {
          $activeImage = 'status-node-empty';
          $activeString = 'No';
          $cmd = 'datafilter_link';
        }
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" alt="%s"/></a></subitem>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd' => $cmd,
                'view_id' => $viewId,
                'datafilter_id' => $filter['datafilter_id']
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
  function getFilterDeleteDialog() {
    if (isset($this->filter) && is_array($this->filter)) {
      $hidden = array(
        'cmd' => 'datafilter_delete',
        'datafilter_id' => $this->filter['datafilter_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete filter "%s" (%s)?'),
        $this->filter['datafilter_title'],
        (int)$this->filter['datafilter_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
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
          'cmd' => 'datafilter_edit',
          'datafilter_id' => (int)$this->filter['datafilter_id']
        );
        $btnCaption = 'Save';
      } else {
        $data = array();
        $hidden = array(
          'cmd' => 'datafilter_add'
        );
        $btnCaption = 'Add';
      }
      $viewModules = array();
      if (isset($this->dataFilterModules) && is_array($this->dataFilterModules) &&
          count($this->dataFilterModules) > 0) {
        foreach ($this->dataFilterModules as $module) {
          if (isset($this->moduleGroups[$module['modulegroup_id']])) {
            $viewModules[$module['module_guid']] = '['.
              $this->moduleGroups[$module['modulegroup_id']]['modulegroup_title'].'] '.
              $module['module_title'];
          } else {
            $viewModules[$module['module_guid']] = $module['module_title'];
          }
        }
        asort($viewModules);
      }
      $fields = array(
        'datafilter_title' => array('Title', 'isAlphaNum', TRUE, 'input', 200),
        'module_guid' => array('Filter Module', 'isGuid', TRUE, 'combo',
          $viewModules, '')
      );
      $this->filterDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->filterDialog->loadParams();
      $this->filterDialog->dialogTitle = $this->_gt('Data filter');
      $this->filterDialog->buttonTitle = $btnCaption;
      $this->filterDialog->dialogDoubleButtons = FALSE;
    }
  }

  /**
  * Filter title used?
  *
  * @param string $filterTitle tile of filter
  * @access public
  * @return boolean used or not
  */
  function filterTitleUsed($filterTitle) {
    $sql = "SELECT COUNT(datafilter_id)
              FROM %s
             WHERE datafilter_title = '%s'";
    $params = array($this->tableDataFilter, strtolower($filterTitle));
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() > 0);
    }
    return FALSE;
  }

  /**
   * Filter module used?
   *
   * @param string $moduleGuid
   * @access public
   * @return boolean used or not
   */
  function filterModuleUsed($moduleGuid) {
    $sql = "SELECT COUNT(datafilter_id)
              FROM %s
             WHERE module_guid = '%s'";
    $params = array($this->tableDataFilter, $moduleGuid);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() > 0);
    }
    return FALSE;
  }

  /**
  * Load filter modules list into $this->dataFilterModules array
  *
  * @access public
  */
  function loadDataFilterModulesList() {
    unset($this->dataFilterModules);
    $sql = "SELECT module_guid, module_title, modulegroup_id
              FROM %s
             WHERE module_type = 'datafilter' AND module_active = 1
             ORDER BY module_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableModules)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->dataFilterModules[$row['module_guid']] = $row;
      }
    }
  }

  /**
  * Load filters into $this->filters array
  *
  * @access public
  */
  function loadFilters() {
    $this->filters = array();
    $sql = "SELECT datafilter_id, datafilter_title
              FROM %s
             ORDER BY datafilter_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableDataFilter)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->filters[$row['datafilter_id']] = $row;
      }
    }
  }

  /**
  * Load filter into $this->filter by id
  *
  * @param integer $filterId data filter id
  * @access public
  * @return boolean loaded or not
  */
  function loadFilter($filterId) {
    unset($this->filter);
    $sql = "SELECT datafilter_id, datafilter_title, module_guid
              FROM %s
             WHERE datafilter_id = '%d'";
    $params = array($this->tableDataFilter, (int)$filterId);
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
      'datafilter_title' => $this->params['datafilter_title'],
      'module_guid' => $this->params['module_guid']
    );
    return $this->databaseInsertRecord(
      $this->tableDataFilter, 'datafilter_id', $data
    );
  }

  /**
  * Save filter changes to database by id
  *
  * @param integer $filterId data filter id
  * @access public
  * @return boolean saved or not
  */
  function saveFilter($filterId) {
    $data = array(
      'datafilter_title' => $this->params['datafilter_title'],
      'module_guid' => $this->params['module_guid']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableDataFilter, $data, 'datafilter_id', (int)$filterId
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
        $this->tableDataFilterLinks, 'datafilter_id', (int)$filterId
      ) &&
      FALSE !== $this->databaseDeleteRecord(
        $this->tableDataFilter, 'datafilter_id', (int)$filterId
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
    $sql = "SELECT fl.view_id, fl.datafilter_id, df.datafilter_title
              FROM %s fl
              LEFT OUTER JOIN %s df ON (df.datafilter_id = fl.datafilter_id)
             WHERE fl.view_id = %d
             ORDER BY df.datafilter_title";
    $params = array($this->tableDataFilterLinks, $this->tableDataFilter,
      $viewId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->filterLinks[$row['datafilter_id']] = $row;
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
    $sql = "SELECT fl.view_id, fl.datafilter_id, fl.datafilter_data,
                   df.datafilter_title,
                   m.module_guid, m.module_path, m.module_file,
                   m.module_class, m.module_title
              FROM %s fl
              LEFT OUTER JOIN %s df ON (df.datafilter_id = fl.datafilter_id)
              LEFT OUTER JOIN %s m ON (m.module_guid = df.module_guid)
             WHERE fl.view_id = %d AND fl.datafilter_id = %d";
    $params = array($this->tableDataFilterLinks, $this->tableDataFilter,
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
      'datafilter_id' => $filterId,
      'datafilter_data' => ''
    );
    $sql = "SELECT COUNT(view_id)
              FROM %s
             WHERE ".$this->databaseGetSQLCondition($data);
    if ($res = $this->databaseQueryFmt($sql, $this->tableDataFilterLinks)) {
      if ($res->fetchField() < 1) {
        return FALSE !== $this->databaseInsertRecord(
          $this->tableDataFilterLinks, NULL, $data
        );
      }
      return TRUE;
    }
    return FALSE;
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
      'datafilter_id' => $filterId
    );
    return FALSE !== $this->databaseDeleteRecord(
      $this->tableDataFilterLinks, $cond
    );
  }
}

