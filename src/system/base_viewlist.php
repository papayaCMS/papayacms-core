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
* View list basic class
*
* @package Papaya
* @subpackage Administration
*/
class base_viewlist extends base_db {
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
  * Papaya database table public topics
  * @var string $tableTopicsPublic
  */
  var $tableTopicsPublic = PAPAYA_DB_TBL_TOPICS_PUBLIC;
  /**
  * Papaya database table public topics translations
  * @var string $tableTopicsPublicTRANS
  */
  var $tableTopicsPublicTRANS = PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * Papaya database table groups
  * @var string $tableModuleGroups
  */
  var $tableModuleGroups = PAPAYA_DB_TBL_MODULEGROUPS;


  /**
  * Module groups
  * @var array $moduleGroups
  */
  var $moduleGroups = NULL;
  /**
  * Modules
  * @var array $modules
  */
  var $modules = NULL;

  /**
  * Views
  * @var array $views
  */
  var $views = NULL;
  /**
  * View modes
  * @var array $viewModes
  */
  var $viewModes = NULL;
  /**
  * Output modules
  * @var array $outputModules
  */
  var $outputModules = NULL;

  /**
  * View
  * @var array $view
  */
  var $view = NULL;
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
  * Broken views
  * @var array $brokenViews
  */
  var $brokenViews = NULL;

  /**
  * Open module groups
  * @var array $openModuleGroups
  */
  var $openModuleGroups = array();

  /**
  * Reserved modes
  * @var array $reservedModes
  */
  var $reservedModes = array('php', 'urls', 'xml', 'media', 'thumb',
    'download', 'popup', 'image', 'outputs', 'status');

  var $usageTables = array(
    PAPAYA_DB_TBL_TOPICS_TRANS,
    PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS,
    PAPAYA_DB_TBL_TOPICS_VERSIONS_TRANS,
    PAPAYA_DB_TBL_BOX_TRANS,
    PAPAYA_DB_TBL_BOX_PUBLIC_TRANS,
    PAPAYA_DB_TBL_BOX_VERSIONS_TRANS
  );

  var $usageCounts = NULL;

  /**
   * @var array $limits view limitations
   */
  public $limits = array();

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
   * @var base_dialog
   */
  private $dialogLimit;

  /**
   * @var base_dialog
   */
  private $dialogView;

  /**
   * @var base_dialog
   */
  private $dialogViewMode;

  /**
   * @var papaya_datafilter_list
   */
  protected $dataFilterConf;

  /**
   * @var papaya_import
   */
  protected $importConf;

  /**
  * php5 Constructor
  *
  * @param string $paramName optional, default value 'vl'
  * @access public
  */
  function __construct($paramName='vl') {
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_papaya_views_'.$this->paramName;
  }

  /**
  * Initialize parameters
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('mode', array('cmd'));
    if (!(
          isset($this->sessionParams['open_modulegroups']) &&
          is_array($this->sessionParams['open_modulegroups'])
        )) {
      $this->sessionParams['open_modulegroups'] = array();
    }
    $this->openModuleGroups = &$this->sessionParams['open_modulegroups'];
  }

  /**
  * Basic execution
  *
  * @access public
  */
  function execute() {
    $this->loadModuleGroups();
    switch ($this->params['mode']) {
    case 3:
      if (defined('PAPAYA_DATAFILTER_USE') && PAPAYA_DATAFILTER_USE) {
        $this->dataFilterConf = new papaya_datafilter_list;
        $this->dataFilterConf->layout = $this->layout;
        $this->dataFilterConf->images = $this->papaya()->images;
        $this->dataFilterConf->paramName = $this->paramName;
        $this->dataFilterConf->moduleGroups = $this->moduleGroups;
        $this->dataFilterConf->initialize();
        $this->dataFilterConf->execute();
      }
      break;
    case 2:
      if (defined('PAPAYA_IMPORTFILTER_USE') && PAPAYA_IMPORTFILTER_USE) {
        $this->importConf = new papaya_import;
        $this->importConf->layout = $this->layout;
        $this->importConf->images = $this->papaya()->images;
        $this->importConf->paramName = $this->paramName;
        $this->importConf->moduleGroups = $this->moduleGroups;
        $this->importConf->initialize();
        $this->importConf->execute();
      }
      break;
    case 1:
      $this->loadOutputModulesList();
      if (isset($this->params['cmd'])) {
        switch($this->params['cmd']) {
        case 'viewmode_add':
          //add a new output filter
          $this->initializeViewModeDialog();
          if ($this->dialogViewMode->checkDialogInput() &&
              $this->checkViewModeData()) {
            if ($newId = $this->createViewMode()) {
              $this->addMsg(MSG_INFO, $this->_gt('Output filter added.'));
              $this->params['viewmode_id'] = $newId;
              unset($this->dialogViewMode);
            }
          }
          break;
        case 'viewmode_edit':
          //edit current output filter
          if (isset($this->params['viewmode_id']) &&
              $this->params['viewmode_id'] > 0 &&
              $this->loadViewMode($this->params['viewmode_id'])) {
            $this->initializeViewModeDialog();
            if ($this->dialogViewMode->checkDialogInput() &&
                $this->checkViewModeData()) {
              if ($this->saveViewMode()) {
                $this->addMsg(MSG_INFO, $this->_gt('Output filter modified.'));
                unset($this->viewMode);
                unset($this->dialogViewMode);
              }
            }
          }
          break;
        case 'viewmode_delete':
          //delete a filter
          if (isset($this->params['viewmode_id']) &&
              $this->params['viewmode_id'] > 0 &&
              isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete'] &&
              $this->loadViewMode($this->params['viewmode_id'])) {
            if ($this->deleteViewMode()) {
              $this->addMsg(MSG_INFO, $this->_gt('Output filter deleted.'));
              unset($this->viewMode);
            }
          }
          break;
        }
      }
      $this->loadViewModesList();
      if (isset($this->params['viewmode_id']) &&
          $this->params['viewmode_id'] > 0) {
        $this->loadViewMode($this->params['viewmode_id']);
      }
      break;
    default :
      $this->loadModulesList();
      $this->loadViewModesList();
      if (isset($this->params['cmd'])) {
        switch ($this->params['cmd']) {
        case 'group_open':
          if (isset($this->params['group_id']) && $this->params['group_id'] > 0) {
            $this->openModuleGroups[(int)$this->params['group_id']] = TRUE;
          }
          break;
        case 'group_close':
          if (
            isset($this->params['group_id']) &&
            $this->params['group_id'] > 0 &&
            isset($this->openModuleGroups[(int)$this->params['group_id']])
          ) {
            unset($this->openModuleGroups[(int)$this->params['group_id']]);
          }
          break;
        case 'view_link':
          if (
            isset($this->params['view_id']) &&
            $this->params['view_id'] > 0 &&
            isset($this->params['viewmode_id']) &&
            $this->params['viewmode_id'] > 0
          ) {
            $this->addViewLink($this->params['view_id'], $this->params['viewmode_id']);
          }
          break;
        case 'viewlink_edit':
          if (
            isset($this->params['view_id']) &&
            $this->params['view_id'] > 0 &&
            isset($this->params['viewmode_id']) &&
            $this->params['viewmode_id'] > 0
          ) {
            $this->executeViewLinkEdit(
              $this->params['view_id'], $this->params['viewmode_id']
            );
          }
          break;
        case 'view_unlink':
          if (
            isset($this->params['view_id']) &&
            $this->params['view_id'] > 0 &&
            isset($this->params['viewmode_id']) &&
            $this->params['viewmode_id'] > 0 &&
            $this->loadViewLink(
              $this->params['view_id'], $this->params['viewmode_id']
            ) &&
            isset($this->params['confirm_unlink']) &&
            $this->params['confirm_unlink']
          ) {
            if ($this->deleteViewLink($this->params['view_id'], $this->params['viewmode_id'])) {
              unset($this->viewLink);
            };
          }
          break;
        case 'view_add':
          //add a new view
          $this->initializeViewDialog();
          if ($this->dialogView->checkDialogInput()) {
            if ($newId = $this->createView()) {
              $this->addMsg(MSG_INFO, $this->_gt('View added.'));
              $this->params['view_id'] = $newId;
              unset($this->dialogView);
            }
          }
          break;
        case 'view_edit':
          //edit current view
          if (isset($this->params['view_id']) &&
              $this->params['view_id'] > 0 &&
              $this->loadView($this->params['view_id'])) {
            $this->initializeViewDialog();
            if ($this->dialogView->checkDialogInput()) {
              if ($this->saveView()) {
                $this->addMsg(MSG_INFO, $this->_gt('View modified.'));
                unset($this->view);
                unset($this->dialogView);
              }
            }
          }
          break;
        case 'view_delete':
          //delete a view
          if (isset($this->params['view_id']) &&
              $this->params['view_id'] > 0 &&
              isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete'] &&
              $this->loadView($this->params['view_id'])) {
            if ($this->deleteView()) {
              $this->addMsg(MSG_INFO, $this->_gt('View deleted.'));
              unset($this->view);
            }
          }
          break;
        case 'import_link':
        case 'import_unlink':
        case 'importlink_edit':
          if (defined('PAPAYA_IMPORTFILTER_USE') && PAPAYA_IMPORTFILTER_USE) {
            $this->importConf = new papaya_import;
            $this->importConf->layout = $this->layout;
            $this->importConf->images = $this->papaya()->images;
            $this->importConf->paramName = $this->paramName;
            $this->importConf->initialize();
            $this->importConf->execute();
          }
          break;
        case 'datafilter_link':
        case 'datafilter_unlink':
        case 'datafilterlink_edit':
          if (defined('PAPAYA_DATAFILTER_USE') && PAPAYA_DATAFILTER_USE) {
            $this->dataFilterConf = new papaya_datafilter_list;
            $this->dataFilterConf->layout = $this->layout;
            $this->dataFilterConf->images = $this->papaya()->images;
            $this->dataFilterConf->paramName = $this->paramName;
            $this->dataFilterConf->initialize();
            $this->dataFilterConf->execute();
          }
          break;
        case 'view_limit_add' :
        case 'view_limit_edit' :
          if (isset($this->params['view_id']) &&
              $this->loadView($this->params['view_id'])) {
              $this->initializeLimitDialog();
            if ($this->dialogLimit->checkDialogInput()) {
              if (isset($this->params['page_id']) && $this->params['page_id'] > 0 &&
                $this->pageExists($this->params['page_id'])) {
                if ($pageId = $this->editViewLimit($this->dialogLimit->data)) {
                  switch ($this->params['cmd']) {
                  case 'view_limit_add' :
                    $this->addMsg(MSG_INFO, $this->_gt('View limit added.'));
                    break;
                  case 'view_limit_edit' :
                  default :
                    $this->addMsg(MSG_INFO, $this->_gt('View limit modified.'));
                    break;
                  }
                } else {
                  $this->addMsg(MSG_ERROR, $this->_gt('Database Error!'));
                }
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Page not found!'));
              }
            }
          }
          break;
        case 'view_limit_delete' :
          if (isset($this->params['view_id']) &&
              $this->loadView($this->params['view_id'])) {
            if (isset($this->params['page_id']) && $this->params['page_id'] > 0) {
              if ($this->deleteViewLimit($this->params['page_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('View limit deleted.'));
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Database Error!'));
              }
            }
          }
          break;
        }
      }
      $this->loadViewsList();
      if (isset($this->params['view_id']) && $this->params['view_id'] > 0) {
        if ($this->loadView($this->params['view_id'])) {
          $this->openModuleGroups[(int)$this->view['modulegroup_id']] = TRUE;
        }
      }
      break;
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Execute view link edit
  *
  * @param integer $viewId
  * @param integer $viewModeId
  * @access public
  * @return boolean
  */
  function executeViewLinkEdit($viewId, $viewModeId) {
    if ($this->loadView($viewId) && $this->loadViewLink($viewId, $viewModeId)) {
      if ($this->view['module_useoutputfilter']) {
        $parent = NULL;
        $moduleObj = $this->papaya()->plugins->get(
          $this->viewLink['module_guid'],
          $this,
          $this->viewLink['viewlink_data']
        );
        if (isset($moduleObj) && is_object($moduleObj)) {
          $moduleObj->paramName = $this->paramName;
          $hidden = array(
            'view_id' => $this->viewLink['view_id'],
            'viewmode_id' => $this->viewLink['viewmode_id'],
            'cmd' => 'viewlink_edit'
          );
          if (!empty($this->viewLink['viewmode_path'])) {
            $moduleObj->templatePath = $this->viewLink['viewmode_path'];
          }
          $moduleObj->initializeDialog($hidden);
          if ($moduleObj->modified()) {
            if ($moduleObj->checkData()) {
              $saved = $this->saveViewLinkContent(
                $this->viewLink['view_id'], $this->viewLink['viewmode_id'], $moduleObj->getData()
              );
              if ($saved) {
                $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
              }
            }
          }

          $moduleObj->templatePath = $this->viewLink['viewmode_path'];
          if (
            !$moduleObj->checkConfiguration(
              $this->viewLink['viewmode_type'] == 'page', $this->view['module_class']
            )
          ) {
            $this->addMsg(MSG_WARNING, $moduleObj->errorMessage);
          }

          $this->layout->add($moduleObj->getForm());
          unset($moduleObj);
          return TRUE;
        }
      } else {
        $str = sprintf(
          '<msgdialog type="info" width="100%%"><message>%s</message></msgdialog>',
          $this->_gt('No Configuration needed.')
        );
        $this->layout->add($str);
        return TRUE;
      }
    } else {
      // add error message that there is no link set yet
      $msg = $this->_gt('No stylesheet assigned.');
      $this->addMsg(MSG_ERROR, $msg, TRUE);
    }
    return FALSE;
  }

  /**
  * Load view link
  *
  * @param integer $viewId
  * @param integer $viewModeId
  * @access public
  * @return boolean
  */
  function loadViewLink($viewId, $viewModeId) {
    unset($this->viewLink);
    $sql = "SELECT vl.view_id, vl.viewmode_id, vl.viewlink_data,
                   vm.viewmode_ext, viewmode_type, vm.viewmode_path,
                   m.module_guid, m.module_path, m.module_file,
                   m.module_class, m.module_title
              FROM %s vl
              LEFT OUTER JOIN %s vm ON (vm.viewmode_id = vl.viewmode_id)
              LEFT OUTER JOIN %s m ON (m.module_guid = vm.module_guid)
             WHERE vl.view_id = %d AND vl.viewmode_id = %d";
    $params = array($this->tableViewLinks, $this->tableViewModes,
      $this->tableModules, $viewId, $viewModeId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->viewLink = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Save view link content
  *
  * @see base_db::databaseUpdateRecord
  * @param integer $viewId
  * @param integer $viewModeId
  * @param string $xmlString
  * @access public
  * @return boolean
  */
  function saveViewLinkContent($viewId, $viewModeId, $xmlString) {
    $data = array('viewlink_data' => $xmlString);
    $filter = array('view_id' => $viewId, 'viewmode_id' => $viewModeId);
    return (
      FALSE !== $this->databaseUpdateRecord($this->tableViewLinks, $data, $filter)
    );
  }

  /**
  * Load module groups
  *
  * @access public
  */
  function loadModuleGroups() {
    unset($this->moduleGroups);
    $sql = "SELECT modulegroup_id, modulegroup_title
              FROM %s
             ORDER BY modulegroup_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableModuleGroups)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->moduleGroups[$row['modulegroup_id']] = $row;
      }
    }
  }

  /**
  * Load modules list
  *
  * @access public
  */
  function loadModulesList() {
    unset($this->modules);
    $sql = "SELECT module_guid, module_title, module_type, module_class, modulegroup_id,
                   module_useoutputfilter, module_path, module_file
              FROM %s
             WHERE (module_type = 'page' OR module_type = 'box')
               AND module_active = 1
             ORDER BY module_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableModules)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->modules[$row['module_guid']] = $row;
      }
    }
  }

  /**
  * Load view modes list
  *
  * @access public
  */
  function loadViewModesList() {
    unset($this->viewModes);
    $sql = "SELECT viewmode_id, viewmode_ext, viewmode_type
              FROM %s
             ORDER BY viewmode_ext";
    if ($res = $this->databaseQueryFmt($sql, $this->tableViewModes)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->viewModes[$row['viewmode_id']] = $row;
      }
    }
  }

  /**
  * Load output module list
  *
  * @access public
  */
  function loadOutputModulesList() {
    unset($this->outputModules);
    $sql = "SELECT module_guid, module_class, module_title, modulegroup_id
              FROM %s
             WHERE module_type = 'output' AND module_active = 1
             ORDER BY module_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableModules)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->outputModules[$row['module_guid']] = $row;
      }
    }
  }

  /**
  * Load view mode
  *
  * @param integer $id viewmode id
  * @access public
  * @return boolean
  */
  function loadViewMode($id) {
    unset($this->viewMode);
    $sql = "SELECT viewmode_id, viewmode_ext, viewmode_type, viewmode_charset,
                   viewmode_contenttype, viewmode_path, module_guid,
                   viewmode_sessionmode, viewmode_sessionredirect, viewmode_sessioncache
              FROM %s
             WHERE viewmode_id = %d";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableViewModes, $id))) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->viewMode = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Create / save view mode
  *
  * @see base_db::databaseInsertRecord
  * @access public
  * @return mixed integer insered row id or FALSE error
  */
  function createViewMode() {
    $data = array(
      'viewmode_ext' => $this->params['viewmode_ext'],
      'viewmode_type' => $this->params['viewmode_type'],
      'viewmode_charset' => $this->params['viewmode_charset'],
      'viewmode_contenttype' => $this->params['viewmode_contenttype'],
      'viewmode_path' => $this->params['viewmode_path'],
      'module_guid' => $this->params['module_guid'],
      'viewmode_sessionmode' => (int)$this->params['viewmode_sessionmode'],
      'viewmode_sessionredirect' => (bool)$this->params['viewmode_sessionredirect'],
      'viewmode_sessioncache' => $this->params['viewmode_sessioncache']
    );
    return $this->databaseInsertRecord(
      $this->tableViewModes, 'viewmode_id', $data
    );
  }

  /**
  * Save view mode
  *
  * @see base_db::databaseUpdateRecord
  * @access public
  * @return boolean
  */
  function saveViewMode() {
    $data = array(
      'viewmode_ext' => $this->params['viewmode_ext'],
      'viewmode_type' => $this->params['viewmode_type'],
      'viewmode_charset' => $this->params['viewmode_charset'],
      'viewmode_contenttype' => $this->params['viewmode_contenttype'],
      'viewmode_path' => $this->params['viewmode_path'],
      'module_guid' => $this->params['module_guid'],
      'viewmode_sessionmode' => (int)$this->params['viewmode_sessionmode'],
      'viewmode_sessionredirect' => (bool)$this->params['viewmode_sessionredirect'],
      'viewmode_sessioncache' => $this->params['viewmode_sessioncache']
    );
    return (
      FALSE !== $this->databaseUpdateRecord(
        $this->tableViewModes, $data, 'viewmode_id', $this->viewMode['viewmode_id']
      )
    );
  }

  /**
  * Delete view mode
  *
  * @see base_db::databaseDeleteRecord
  * @access public
  * @return boolean
  */
  function deleteViewMode() {
    return (
      FALSE !== $this->databaseDeleteRecord(
        $this->tableViewLinks, 'viewmode_id', $this->viewMode['viewmode_id']
      ) &&
      FALSE !== $this->databaseDeleteRecord(
        $this->tableViewModes, 'viewmode_id', $this->viewMode['viewmode_id']
      )
    );
  }

  /**
  * Load list
  *
  * @access public
  */
  function loadViewsList() {
    unset($this->brokenViews);
    unset($this->views);
    $sql = "SELECT v.view_id, v.view_title,
                   v.view_is_cacheable, v.view_is_deprecated, v.view_note,
                   v.module_guid, v.view_limits,
                   m.modulegroup_id, m.module_type, m.module_class
              FROM %s v
              LEFT OUTER JOIN %s m ON (m.module_guid = v.module_guid)
             ORDER BY v.view_title";
    $res = $this->databaseQueryFmt(
      $sql, array($this->tableViews, $this->tableModules)
    );
    if ($res) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->views[$row["view_id"]] = $row;
        if (isset($this->moduleGroups[$row['modulegroup_id']])) {
          $this->moduleGroups[$row['modulegroup_id']]['VIEWS'][$row["view_id"]] =
            &$this->views[$row["view_id"]];
        } else {
          $this->brokenViews[$row["view_id"]] = &$this->views[$row["view_id"]];
        }
      }
    }
  }

  /**
  * Load view
  *
  * @see base_db::databaseQueryFmt
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadView($id) {
    unset($this->view);
    if ($id > 0) {
      $sql = "SELECT v.view_id, v.view_title, v.view_name,
                     v.view_is_cacheable, v.view_is_deprecated,
                     v.view_note, v.view_limits, v.view_checksum,
                     m.module_guid, m.module_class, m.module_type, m.modulegroup_id,
                     m.module_useoutputfilter
                FROM %s v
                LEFT OUTER JOIN %s m ON m.module_guid = v.module_guid
               WHERE v.view_id = %d";
      $res = $this->databaseQueryFmt(
        $sql, array($this->tableViews, $this->tableModules, $id)
      );
      if ($res) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->view = $row;
          if (preg_match_all('~\d+~', $row['view_limits'], $matches)) {
            $this->view['LIMITS'] = $matches[0];
          } else {
            $this->view['LIMITS'] = array();
          }
          $this->loadViewLinkedModes();
          $this->validateChecksum();
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Load view linked modes
  *
  * @access public
  */
  function loadViewLinkedModes() {
    if (isset($this->view)) {
      $this->view['MODES'] = NULL;
      $sql = "SELECT view_id, viewmode_id, viewlink_data
                FROM %s
               WHERE view_id = %d";
      $res = $this->databaseQueryFmt(
        $sql, array($this->tableViewLinks, $this->view['view_id'])
      );
      if ($res) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->view['MODES'][$row['viewmode_id']] = $row;
        }
      }
    }
  }

  /**
  * Save view
  *
  * @see base_db::databaseUpdateRecord
  * @access public
  * @return boolean
  */
  function saveView() {
    $data = array(
       'view_title' => $this->params['view_title'],
       'view_name' => $this->params['view_name'],
       'module_guid' => $this->params['module_guid'],
       'view_is_cacheable' => $this->params['view_is_cacheable'],
       'view_is_deprecated' => $this->params['view_is_deprecated'],
       'view_note' => $this->params['view_note'],
       'view_checksum' => $this->getViewChecksum()
    );
    return (
      FALSE !== $this->databaseUpdateRecord(
        $this->tableViews, $data, 'view_id', $this->view['view_id']
      )
    );
  }

  /**
  * Add / create view
  *
  * @see base_db::databaseInsertRecord
  * @access public
  * @return mixed integer insered row id or FALSE error
  */
  function createView() {
    $data = array(
       'view_title' => $this->params['view_title'],
       'module_guid' => $this->params['module_guid'],
       'view_is_cacheable' => $this->params['view_is_cacheable'],
       'view_is_deprecated' => $this->params['view_is_deprecated'],
       'view_note' => $this->params['view_note'],
       'view_checksum' => $this->getViewChecksum()
    );
    return $this->databaseInsertRecord($this->tableViews, 'view_id', $data);
  }

  /**
  * Delete view
  *
  * @see base_db::databaseDeleteRecord
  * @access public
  * @return boolean
  */
  function deleteView() {
    return (
      FALSE !== $this->databaseDeleteRecord(
        $this->tableViewLinks, 'view_id', $this->view['view_id']
      ) &&
      FALSE !== $this->databaseDeleteRecord(
        $this->tableViews, 'view_id', $this->view['view_id']
      )
    );
  }

  /**
  * Add view link
  *
  * @see base_db::databaseInsertRecord
  * @param integer $viewId
  * @param integer $viewModeId
  * @access public
  * @return mixed integer insered row id or FALSE error
  */
  function addViewLink($viewId, $viewModeId) {
    $sql = "SELECT COUNT(*)
              FROM %s WHERE view_id = %d
               AND viewmode_id = %s";
    $params = array($this->tableViewLinks, $viewId, $viewModeId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($res->fetchField() == 0) {
        $data = array(
          'view_id' => (int)$viewId,
          'viewmode_id' => (int)$viewModeId,
          'viewlink_data' => ''
        );
        return $this->databaseInsertRecord($this->tableViewLinks, NULL, $data);
      }
    }
    return FALSE;
  }

  /**
  * Delete view link
  *
  * @param integer $viewId
  * @param integer $viewModeId
  * @access public
  * @return boolean
  */
  function deleteViewLink($viewId, $viewModeId) {
    $filter = array('view_id' => (int)$viewId, 'viewmode_id' => (int)$viewModeId);
    return (FALSE !== $this->databaseDeleteRecord($this->tableViewLinks, $filter));
  }

  /**
  * Get XML buttons
  *
  * @access public
  */
  function getButtonsXML() {
    $toolbar = new base_btnbuilder;
    $toolbar->images = $this->papaya()->images;
    $toolbar->addButton(
      'Views',
      $this->getLink(array('cmd' => 'mode', 'mode' => 0)),
      'items-view',
      '',
      empty($this->params['mode']) || $this->params['mode'] == 0
    );
    $toolbar->addButton(
      'Output filter',
      $this->getLink(array('cmd' => 'mode', 'mode' => 1)),
      'items-filter-export',
      '',
      isset($this->params['mode']) && $this->params['mode'] == 1
    );
    if (defined('PAPAYA_IMPORTFILTER_USE') && PAPAYA_IMPORTFILTER_USE) {
      $toolbar->addButton(
        'Import Filter',
        $this->getLink(array('cmd' => 'mode', 'mode' => 2)),
        'items-filter-import',
        '',
        isset($this->params['mode']) && $this->params['mode'] == 2
      );
    }
    if (defined('PAPAYA_DATAFILTER_USE') && PAPAYA_DATAFILTER_USE) {
      $toolbar->addButton(
        'Data filter',
        $this->getLink(array('cmd' => 'mode', 'mode' => 3)),
        'items-filter-convert',
        '',
        isset($this->params['mode']) && $this->params['mode'] == 3
      );
    }
    $toolbar->addSeperator();
    switch ($this->params['mode']) {
    case 3:
      if (defined('PAPAYA_DATAFILTER_USE') && PAPAYA_DATAFILTER_USE &&
          isset($this->dataFilterConf) && is_object($this->dataFilterConf)) {
        $this->dataFilterConf->getButtonsXML($toolbar);
      }
      break;
    case 2:
      if (defined('PAPAYA_IMPORTFILTER_USE') && PAPAYA_IMPORTFILTER_USE &&
          isset($this->importConf) && is_object($this->importConf)) {
        $this->importConf->getButtonsXML($toolbar);
      }
      break;
    case 1:
      $toolbar->addButton(
        'Add filter',
        $this->getLink(array('viewmode_id' => 0)),
        'actions-filter-add',
        ''
      );
      if (isset($this->viewMode)) {
        $toolbar->addButton(
          'Delete filter',
          $this->getLink(
            array(
              'cmd' => 'viewmode_delete',
              'viewmode_id' => (int)$this->viewMode['viewmode_id']
            )
          ),
          'actions-filter-delete',
          ''
        );
      }
      break;
    default:
      $toolbar->addButton(
        'Add view',
        $this->getLink(array('view_id' => 0)),
        'actions-view-add',
        ''
      );
      if (isset($this->view)) {
        $toolbar->addButton(
          'Delete view',
          $this->getLink(
            array(
              'cmd' => 'view_delete',
              'view_id' => (int)$this->view['view_id']
            )
          ),
          'actions-view-delete',
          ''
        );
        $toolbar->addSeperator();
        $toolbar->addButton(
          'Limit view use',
          $this->getLink(
            array(
              'cmd' => 'view_limit_add',
              'view_id' => (int)$this->view['view_id']
            )
          ),
          'actions-view-limit',
          ''
        );
      }
      break;
    }
    if ($str = $toolbar->getXML()) {
      $this->layout->add('<menu>'.$str.'</menu>', 'menus');
    }
  }

  /**
  * Get XML
  *
  * @access public
  */
  function getXML() {
    switch ($this->params['mode']) {
    case 3:
      if (defined('PAPAYA_DATAFILTER_USE') && PAPAYA_DATAFILTER_USE &&
          isset($this->dataFilterConf) && is_object($this->dataFilterConf)) {
        $this->dataFilterConf->getXML();
      }
      break;
    case 2:
      if (defined('PAPAYA_IMPORTFILTER_USE') && PAPAYA_IMPORTFILTER_USE &&
          isset($this->importConf) && is_object($this->importConf)) {
          $this->importConf->getXML();
      }
      break;
    case 1:
      if (!isset($this->params['cmd'])) {
        $this->params['cmd'] = '';
      }
      switch($this->params['cmd']) {
      case 'viewmode_delete' :
        $this->getViewModeDeleteDialog();
        break;
      default :
        $this->getViewModeDialog();
        break;
      }
      $this->getViewModesList();
      break;
    default:
      if (isset($this->view)) {
        if (defined('PAPAYA_IMPORTFILTER_USE') && PAPAYA_IMPORTFILTER_USE) {
          if (!(isset($this->importConf) && is_object($this->importConf))) {
            $this->importConf = new papaya_import;
            $this->importConf->layout = $this->layout;
            $this->importConf->images = $this->papaya()->images;
            $this->importConf->paramName = $this->paramName;
            $this->importConf->initialize();
            $this->importConf->execute();
          }
          $this->importConf->loadFilterLinks($this->params['view_id']);
        }
        if (defined('PAPAYA_DATAFILTER_USE') && PAPAYA_DATAFILTER_USE) {
          if (!(isset($this->dataFilterConf) && is_object($this->dataFilterConf))) {
            $this->dataFilterConf = new papaya_datafilter_list;
            $this->dataFilterConf->layout = $this->layout;
            $this->dataFilterConf->images = $this->papaya()->images;
            $this->dataFilterConf->paramName = $this->paramName;
            $this->dataFilterConf->initialize();
            $this->dataFilterConf->execute();
          }
          $this->dataFilterConf->loadFilterLinks($this->params['view_id']);
        }
      }
      if (!isset($this->params['cmd'])) {
        $this->params['cmd'] = '';
      }
      switch($this->params['cmd']) {
      case 'import_link':
      case 'import_unlink':
      case 'importlink_edit':
        $this->importConf->getImportLinkXML($this->view['view_id']);
        break;
      case 'datafilter_link':
      case 'datafilter_unlink':
      case 'datafilterlink_edit':
        $this->dataFilterConf->getDataFilterLinkXML($this->view['view_id']);
        break;
      case 'view_delete':
        $this->getViewDeleteDialog();
        break;
      case 'view_unlink':
        $this->getViewUnlinkDialog();
        break;
      case 'view_link':
      case 'viewlink_edit':
        break;
      case 'view_limit_add' :
      case 'view_limit_edit' :
        $this->getLimitDialog();
        $this->layout->add($this->getViewLimitList());
        $this->layout->add($this->getViewUseList());
        break;
      default :
        $this->getViewDialog();
        $this->layout->add($this->getViewDuplicates());
        $this->layout->add($this->getViewLimitList());
        $this->layout->add($this->getViewUseList());
        break;
      }
      $this->getViewsList();
      $this->getViewModuleInfos();
      $this->getViewLinkInfos();
      if (isset($this->view)) {
        if (defined('PAPAYA_IMPORTFILTER_USE') && PAPAYA_IMPORTFILTER_USE) {
          $this->importConf->getImportLinkInfos($this->view['view_id']);
        }
        if (defined('PAPAYA_DATAFILTER_USE') && PAPAYA_DATAFILTER_USE) {
          $this->dataFilterConf->getDataFilterLinkInfos($this->view['view_id']);
        }
      }
      break;
    }
    $this->getButtonsXML();
  }

  /**
  * Get view modes list
  *
  * @access public
  */
  function getViewModesList() {
    if (isset($this->viewModes) && is_array($this->viewModes) &&
        count($this->viewModes) > 0) {
      $images = $this->papaya()->images;
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Output filter'))
      );
      $result .= '<items>';
      foreach ($this->viewModes as $viewMode) {
        if (isset($this->params['viewmode_id']) &&
            $this->params['viewmode_id'] == $viewMode['viewmode_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s" %s/>',
          papaya_strings::escapeHTMLChars($viewMode['viewmode_ext']),
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('viewmode_id' => (int)$viewMode['viewmode_id']))
          ),
          papaya_strings::escapeHTMLChars($images['items-filter-export']),
          $selected
        );
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addLeft($result);
    }
  }

  /**
  * Initialize view mode dialog
  *
  * @access public
  */
  function initializeViewModeDialog() {
    if (!(isset($this->dialogViewMode) && is_object($this->dialogViewMode))) {
      if (isset($this->viewMode) && is_array($this->viewMode)) {
        $data = $this->viewMode;
        $hidden = array(
          'viewmode_id' => (int)$this->viewMode['viewmode_id'],
          'cmd' => 'viewmode_edit'
        );
        $btnCaption = 'Edit';
      } else {
        $data = array();
        $hidden = array(
          'cmd' => 'viewmode_add'
        );
        $btnCaption = 'Add';
      }
      $viewModules = array();
      if (isset($this->outputModules) && is_array($this->outputModules) &&
          count($this->outputModules) > 0) {
        foreach ($this->outputModules as $module) {
          if (isset($this->moduleGroups[$module['modulegroup_id']])) {
            $viewModules[$module['module_guid']] = '['.
               $this->moduleGroups[$module['modulegroup_id']]['modulegroup_title'].
               '] '.
               $module['module_title'];
          } else {
            $viewModules[$module['module_guid']] = $module['module_title'];
          }
        }
        asort($viewModules);
      }
      $sessionModes = array(
        '0' => $this->_gt('Default'),
        '1' => $this->_gt('Read Only'),
        '2' => $this->_gt('No Session'),
      );
      $systemSessionCache = defined('PAPAYA_SESSION_CACHE') ? PAPAYA_SESSION_CACHE : 'private';
      $sessionCacheModes = array(
        '0' => $this->_gt('System').' ('.$systemSessionCache.')',
        '1' => $this->_gt('private'),
        '2' => $this->_gt('no cache'),
      );
      $viewTypes = array(
        'page' => $this->_gt('Page'),
        'feed' => $this->_gt('Feed'),
        'hidden' => $this->_gt('Hidden')
      );
      $templateHandler = new \PapayaTemplateXsltHandler();
      $fields = array(
        'viewmode_ext' => array('Extension', '/^[a-z]{1,20}$/',
          TRUE, 'input', 20, ''),
        'viewmode_type' => array('Type', '/^[a-z]{1,10}$/',
          TRUE, 'combo', $viewTypes, '', 'page'),
        'viewmode_charset' => array('Charset', 'isAlphaNumChar', TRUE, 'combo',
          array('utf-8' => 'utf-8', 'iso-8859-1' => 'iso-8859-1'), '', 'utf-8'),
        'viewmode_contenttype' => array('Content-type', '~^(x-)?[a-z]+/[a-z_+-\.]+$~',
          TRUE, 'input', 50, '', 'text/html'),
        'module_guid' => array('Filter Module', 'isGuid',
          TRUE, 'combo', $viewModules, ''),
        'viewmode_path' => array('Template directory', 'isAlphaNum', TRUE, 'dircombo',
           array($templateHandler->getLocalPath(), '', '(^[^_\.].*$)')),
        'Options',
        'viewmode_sessionmode' => array('Session mode', 'isNum',
          TRUE, 'combo', $sessionModes, '', 0),
        'viewmode_sessionredirect' => array('Session redirects', 'isNum',
          TRUE, 'yesno', NULL, '', 1),
        'viewmode_sessioncache' => array('Session cache', 'isNum',
          TRUE, 'combo', $sessionCacheModes, '', 1)
      );
      $this->dialogViewMode = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogViewMode->loadParams();
      $this->dialogViewMode->dialogTitle = $this->_gt('Output filter');
      $this->dialogViewMode->buttonTitle = $btnCaption;
      $this->dialogViewMode->dialogDoubleButtons = FALSE;
    }
  }

  /**
  * Get view mode dialog
  *
  * @access public
  */
  function getViewModeDialog() {
    $this->initializeViewModeDialog();
    $this->layout->add($this->dialogViewMode->getDialogXML());
  }


  /**
  * Get view mode delete dialog
  *
  * @access public
  */
  function getViewModeDeleteDialog() {
    if (isset($this->viewMode)) {
      $hidden = array(
        'cmd' => 'viewmode_delete',
        'viewmode_id' => $this->viewMode['viewmode_id'],
        'confirm_delete' => 1
      );
      $msg = sprintf(
        $this->_gt('Delete filter "%s" (%s)?'),
        $this->viewMode['viewmode_ext'],
        (int)$this->viewMode['viewmode_id']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->dialogTitle = $this->_gt('Delete page');
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Checks input params for view mode - extension has to be unique
  *
  * @access public
  * @return boolean;
  */
  function checkViewModeData() {
    if (in_array($this->params['viewmode_ext'], $this->reservedModes)) {
      $this->addMsg(
        MSG_ERROR,
        'This filter extension is reserved. Please choose another.'
      );
    } else {
      if (isset($this->viewMode) && is_array($this->viewMode)) {
        $sql = "SELECT COUNT(*)
                  FROM %s
                 WHERE viewmode_ext = '%s' AND (NOT(viewmode_id = %d))";
        $params = array($this->tableViewModes, $this->params['viewmode_ext'],
          $this->viewMode['viewmode_id']);
      } else {
        $sql = "SELECT COUNT(*)
                  FROM %s
                 WHERE viewmode_ext = '%s'";
        $params = array($this->tableViewModes, $this->params['viewmode_ext']);
      }
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        list($count) = $res->fetchRow();
        if ($count > 0) {
          $this->addMsg(MSG_ERROR, 'The filter extension must be unique.');
        } else {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Get viewlist
  *
  * @access public
  */
  function getViewsList() {
    $images = $this->papaya()->images;
    if (isset($this->moduleGroups) && is_array($this->moduleGroups) && (
          (isset($this->views) && is_array($this->views)) ||
          (isset($this->brokenViews) && is_array($this->brokenViews))
        )) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Views'))
      );
      $result .= '<items>';
      if (isset($this->brokenViews) && count($this->brokenViews) > 0) {
        $result .= sprintf(
          '<listitem title="%s" image="%s"/>',
          papaya_strings::escapeHTMLChars($this->_gt('Unknown')),
          papaya_strings::escapeHTMLChars($images['status-folder-open'])
        );
        foreach ($this->brokenViews as $view) {
          if (isset($this->params['view_id']) && $this->params['view_id'] == $view['view_id']) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $imageIdx = 'status-page-warning';
          $result .= sprintf(
            '<listitem title="%s" node="empty" image="%s" href="%s" indent="1"%s/>',
            papaya_strings::escapeHTMLChars($view['view_title']),
            papaya_strings::escapeHTMLChars($images[$imageIdx]),
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array(
                  'cmd' => 'view_select',
                  'view_id' => (int)$view['view_id']
                )
              )
            ),
            $selected
          );
        }
      }
      foreach ($this->moduleGroups as $moduleGroup) {
        if (isset($moduleGroup['VIEWS']) && count($moduleGroup['VIEWS']) > 0) {
          if (isset($this->openModuleGroups[$moduleGroup['modulegroup_id']])) {
            $node = 'open';
            $nodeHref = $this->getLink(
              array(
                'cmd' => 'group_close',
                'group_id' => (int)$moduleGroup['modulegroup_id']
              )
            );
            $imageIdx = 'status-folder-open';
          } else {
            $node = 'close';
            $nodeHref = $this->getLink(
              array(
                'cmd' => 'group_open',
                'group_id' => (int)$moduleGroup['modulegroup_id']
              )
            );
            $imageIdx = 'items-folder';
          }
          $result .= sprintf(
            '<listitem title="%s" image="%s" href="%s" node="%s" nhref="%s"/>',
            papaya_strings::escapeHTMLChars($moduleGroup['modulegroup_title']),
            papaya_strings::escapeHTMLChars($images[$imageIdx]),
            papaya_strings::escapeHTMLChars($nodeHref),
            papaya_strings::escapeHTMLChars($node),
            papaya_strings::escapeHTMLChars($nodeHref)
          );
          if (isset($this->openModuleGroups[$moduleGroup['modulegroup_id']])) {
            foreach ($moduleGroup['VIEWS'] as $view) {
              if (isset($this->params['view_id']) && $this->params['view_id'] == $view['view_id']) {
                $selected = ' selected="selected"';
              } else {
                $selected = '';
              }
              if ($view['view_is_deprecated']) {
                $imageIdx = 'status-sign-off';
              } elseif ($view['view_is_cacheable']) {
                $imageIdx = ($view['module_type'] == 'page')
                  ? 'status-page-published' : 'status-box-published';
              } else {
                $imageIdx = ($view['module_type'] == 'page')
                  ? 'items-page' : 'items-box';
              }

              $result .= sprintf(
                '<listitem title="%s" node="empty" image="%s" href="%s" indent="1"%s/>',
                papaya_strings::escapeHTMLChars($view['view_title']),
                papaya_strings::escapeHTMLChars($images[$imageIdx]),
                papaya_strings::escapeHTMLChars(
                  $this->getLink(
                    array(
                      'cmd' => 'view_select',
                      'view_id' => (int)$view['view_id']
                    )
                  )
                ),
                $selected
              );
            }
          }
        }
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addLeft($result);
    }
  }

  /**
   * Output listview with some module informations
   */
  public function getViewModuleInfos() {
    if (isset($this->view) && isset($this->modules[$this->view['module_guid']])) {
      $module = $this->modules[$this->view['module_guid']];
      $listview = new \Papaya\Ui\Listview($module);
      $listview->caption = new \Papaya\Ui\Text\Translated('Module');
      $listview->items[] = $item =
        new \Papaya\Ui\Listview\Item(
          $module['module_type'] == 'page' ? 'items-page' : 'items-box',
          $module['module_title']
        );
      $item->reference()->setRelative('modules.php');
      $item->reference()->setParameters(
        array(
          'pkg_id' => $module['modulegroup_id'],
          'module_id' => $module['module_guid'],
          'table' => ''
        ),
        'mods'
      );
      $item->columnSpan = 2;
      $listview->items[] = $item =
        new \Papaya\Ui\Listview\Item('', new \Papaya\Ui\Text\Translated('Path'));
      $item->indentation = 1;
      $item->subitems[] = new \Papaya\Ui\Listview\Subitem\Text(
        \Papaya\Utility\Text::truncate($module['module_path'], 30, '...')
      );
      $listview->items[] = $item =
        new \Papaya\Ui\Listview\Item('', new \Papaya\Ui\Text\Translated('Class'));
      $item->indentation = 1;
      $item->subitems[] = new \Papaya\Ui\Listview\Subitem\Text($module['module_class']);
      if ($plugin = $this->papaya()->plugins->get($module['module_guid'])) {
        if ($plugin instanceof \Papaya\Plugin\Cacheable) {
          $listview->items[] = $item =
            new \Papaya\Ui\Listview\Item('', new \Papaya\Ui\Text\Translated('Cacheable interface'));
          $item->indentation = 1;
          $item->columnSpan = 2;
          $sources = new \Papaya\Cache\Identifier\Sources($plugin->cacheable()->getSources());
          $listview->items[] = $item = new \Papaya\Ui\Listview\Item('', (string)$sources);
          $item->indentation = 3;
          $item->columnSpan = 2;
        } elseif ($plugin instanceof base_content && method_exists($plugin, 'getCacheId')) {
          $listview->items[] = $item = new \Papaya\Ui\Listview\Item('', 'getCacheId()');
          $item->indentation = 1;
          $item->columnSpan = 2;
        } elseif ($plugin instanceof base_actionbox) {
          if (method_exists($plugin, 'getCacheId')) {
            $listview->items[] = $item = new \Papaya\Ui\Listview\Item('', 'getCacheId()');
            $item->indentation = 1;
            $item->columnSpan = 2;
          }
          if (property_exists($plugin, 'cacheable') && $plugin->cacheable) {
            $listview->items[] = $item = new \Papaya\Ui\Listview\Item('', '$cacheable');
            $item->indentation = 1;
            $item->columnSpan = 2;
            if (
              property_exists($plugin, 'cacheDependency') &&
              is_array($plugin->cacheDependency)
            ) {
              $titles = array(
                'querystring' => 'Querystring',
                'page' => 'Page',
                'surfer' => 'Surfer'
              );
              foreach ($plugin->cacheDependency as $key => $active) {
                if ($active) {
                  $title = empty($titles[$key]) ? $key : $titles[$key];
                  $listview->items[] = $item = new \Papaya\Ui\Listview\Item('', $title);
                  $item->indentation = 2;
                  $item->columnSpan = 2;
                }
              }
            }
          }
        }
      }
      $this->layout->addRight($listview->getXml());
    }
  }

  /**
  * Get view link information
  *
  * @access public
  */
  function getViewLinkInfos() {
    if (isset($this->view) && isset($this->viewModes)) {
      $images = $this->papaya()->images;
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Output filter'))
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
      foreach ($this->viewModes as $viewMode) {
        $href = $this->getLink(
          array(
            'cmd' => 'viewlink_edit',
            'view_id' => $this->view['view_id'],
            'viewmode_id' => $viewMode['viewmode_id']
          )
        );
        if (isset($this->params['viewmode_id']) &&
            $this->params['viewmode_id'] == $viewMode['viewmode_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s"%s>',
          papaya_strings::escapeHTMLChars($viewMode['viewmode_ext']),
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($images['items-filter-export']),
          $selected
        );
        if (isset($this->view['MODES'][$viewMode['viewmode_id']])) {
          $activeImage = 'status-node-checked';
          $activeString = 'Yes';
          $cmd = 'view_unlink';
        } else {
          $activeImage = 'status-node-empty';
          $activeString = 'No';
          $cmd = 'view_link';
        }
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" alt="%s"/></a></subitem>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd' => $cmd,
                'view_id' => $this->view['view_id'],
                'viewmode_id' => $viewMode['viewmode_id']
              )
            )
          ),
          papaya_strings::escapeHTMLChars($images[$activeImage]),
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
  * Initialize view dialog
  *
  * @access public
  */
  function initializeViewDialog() {
    if (!(isset($this->dialogView) && is_object($this->dialogView))) {
      $moduleTypeLimit = FALSE;
      $moduleType = '';
      if (isset($this->view) && is_array($this->view)) {
        $data = $this->view;
        $hidden = array(
          'view_id' => (int)$this->view['view_id'],
          'cmd' => 'view_edit'
        );
        $btnCaption = 'Edit';
        $css = 'large';

        $this->loadViewUsage();
        if (isset($this->usageCounts) && is_array($this->usageCounts)) {
          switch ($this->view['module_type']) {
          case 'box' :
            if ($this->usageCounts[PAPAYA_DB_TBL_BOX_TRANS]['SUM'] > 0 ||
                $this->usageCounts[PAPAYA_DB_TBL_BOX_PUBLIC_TRANS]['SUM'] > 0 ||
                $this->usageCounts[PAPAYA_DB_TBL_BOX_VERSIONS_TRANS]['SUM'] > 0) {
              $moduleTypeLimit = TRUE;
              $moduleType = 'box';
            }
            break;
          case 'page' :
          default :
            if ($this->usageCounts[PAPAYA_DB_TBL_TOPICS_TRANS]['SUM'] > 0 ||
                $this->usageCounts[PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS]['SUM'] > 0 ||
                $this->usageCounts[PAPAYA_DB_TBL_TOPICS_VERSIONS_TRANS]['SUM'] > 0) {
              $moduleTypeLimit = TRUE;
              $moduleType = 'page';
            }
            break;
          }
        }
      } else {
        $data = array();
        $hidden = array(
          'cmd' => 'view_add'
        );
        $btnCaption = 'Add';
        $css = 'x-large';
        $moduleType = '';
      }
      $viewModules = array();
      if (isset($this->modules) &&
          is_array($this->modules) &&
          count($this->modules) > 0) {
        foreach ($this->modules as $module) {
          if ((!$moduleTypeLimit) || $module['module_type'] == $moduleType) {
            if (isset($this->moduleGroups[$module['modulegroup_id']])) {
              $group =
                $this->moduleGroups[$module['modulegroup_id']]['modulegroup_title'];
              $viewModules[$group][$module['module_guid']] = sprintf(
                '[%s, %s] %s',
                $module['module_type'],
                $module['module_class'],
                $module['module_title']
              );
            } else {
              $viewModules[$this->_gt('Unknown')][$module['module_guid']] = sprintf(
                '[%s, %s] %s',
                $module['module_type'],
                $module['module_class'],
                $module['module_title']
              );
            }
          }
        }
        ksort($viewModules);
        foreach ($viewModules as $modTitle => $modElements) {
          asort($viewModules[$modTitle]);
        }
      }
      $fields = array(
        'view_title' => array('Title', 'isNoHTML', TRUE, 'input', 100, ''),
        'view_name' => array('Name', 'isNoHTML', FALSE, 'input', 100, 'Name/identifier for templates'),
        'module_guid' => array('Module', 'isGuid', TRUE, 'combo', $viewModules, ''),
        'Information',
        'view_is_cacheable' => array('Cacheable', 'isNum', TRUE, 'yesno', NULL, '', 0),
        'view_note' => array('Note', 'isSomeText', FALSE, 'textarea', 10, '', ''),
        'view_is_deprecated' => array(
          'Deprecated',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'Marks a view as deprecated, assigned view can still work, but the view is not
           not selectable any more.',
          0
        )
      );
      $this->dialogView = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogView->loadParams();
      $this->dialogView->dialogTitle = $this->_gt('View');
      $this->dialogView->buttonTitle = $btnCaption;
      $this->dialogView->inputFieldSize = $css;
      $this->dialogView->dialogDoubleButtons = FALSE;
    }
  }

  /**
  * Get view dialog
  *
  * @access public
  */
  function getViewDialog() {
    $this->initializeViewDialog();
    $this->layout->add($this->dialogView->getDialogXML());
  }

  /**
  * Load view count
  *
  * @param string $tableName
  * @access public
  * @return array $result row count with language id index
  */
  function loadViewCount($tableName) {
    $result = array();
    $sql = "SELECT COUNT(*) AS counted, lng_id
              FROM %s
             WHERE view_id = '%d'
             GROUP BY lng_id";
    $res = $this->databaseQueryFmt(
      $sql, array($tableName, $this->view['view_id'])
    );
    if ($res) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['lng_id']] = $row['counted'];
      }
      if (count($result) > 0) {
        $result['SUM'] = (int)array_sum($result);
      } else {
        $result['SUM'] = 0;
      }
    }
    return $result;
  }

  /**
   * Fetch the view duplicates from database
   */
  public function loadViewDuplicates() {
    if (isset($this->view) && $this->view['view_id'] > 0) {
      $views = new \Papaya\Content\Views();
      $views->activateLazyLoad(array('checksum' => $this->view['view_checksum']));
      return $views;
    }
    return NULL;
  }

  /**
   * Show the view duplicates for the curent view
   */
  public function getViewDuplicates() {
    if (($duplicates = $this->loadViewDuplicates()) && count($duplicates) > 1) {
      $listview = new \Papaya\Ui\Listview();
      $listview->caption = new \Papaya\Ui\Text\Translated('Duplicates');
      $listview->columns[] = new \Papaya\Ui\Listview\Column('');
      foreach ($duplicates as $view) {
        if ($view['id'] != $this->view['view_id']) {
          $listview->items[] = $item = new \Papaya\Ui\Listview\Item(
            $this->view['module_type'] == 'box' ? 'items-box' : 'items-page',
            $view['title']
          );
          $item->reference()->setParameters(
            array(
              'cmd' => 'view_select',
              'view_id' => $view['id']
            ),
            $this->paramName
          );
        }
      }
      return $listview->getXml();
    }
    return '';
  }

  /**
  * aggregate usage statistics for current view
  * @return void
  */
  function loadViewUsage() {
    if ((!isset($this->usageCounts)) &&
          isset($this->view) &&
          $this->view['view_id'] > 0) {
      foreach ($this->usageTables as $tableName) {
        $this->usageCounts[$tableName] = $this->loadViewCount($tableName);
      }
    }
  }

  /**
  * Get view use list
  *
  * @access public
  * @return string $result xml or ''
  */
  function getViewUseList() {
    if (isset($this->view) && $this->view['view_id'] > 0) {
      $this->loadViewUsage();
      $listview = new \Papaya\Ui\Listview();
      $listview->caption = new \Papaya\Ui\Text\Translated('Usage overview');
      $listview->columns[] = new \Papaya\Ui\Listview\Column('');
      $listview->columns[] = new \Papaya\Ui\Listview\Column(
        new \Papaya\Ui\Text\Translated('Current'),
        \Papaya\Ui\Option\Align::CENTER
      );
      $listview->columns[] = new \Papaya\Ui\Listview\Column(
        new \Papaya\Ui\Text\Translated('Published'),
        \Papaya\Ui\Option\Align::CENTER
      );
      $listview->columns[] = new \Papaya\Ui\Listview\Column(
        new \Papaya\Ui\Text\Translated('Versions'),
        \Papaya\Ui\Option\Align::CENTER
      );
      switch ($this->view['module_type']) {
      case 'box' :
        $listview->items[] = $item = new \Papaya\Ui\Listview\Item(
          'items-box', new \Papaya\Ui\Text\Translated('Boxes')
        );
        $item->columnSpan = 4;
        foreach ($this->papaya()->languages as $lngId => $language) {
          if ($language['is_content']) {
            $listview->items[] = $item = new \Papaya\Ui\Listview\Item(
              './pics/language/'.$language['image'], $language['title'].' ('.$language['code'].')'
            );
            $item->indentation = 1;
            $item->subitems[] = new \Papaya\Ui\Listview\Subitem\Text(
              empty($this->usageCounts[PAPAYA_DB_TBL_BOX_TRANS][$lngId])
                ? 0 : (int)$this->usageCounts[PAPAYA_DB_TBL_BOX_TRANS][$lngId]
            );
            $item->subitems[] = new \Papaya\Ui\Listview\Subitem\Text(
              empty($this->usageCounts[PAPAYA_DB_TBL_BOX_PUBLIC_TRANS][$lngId])
                ? 0 : (int)$this->usageCounts[PAPAYA_DB_TBL_BOX_PUBLIC_TRANS][$lngId]
            );
            $item->subitems[] = new \Papaya\Ui\Listview\Subitem\Text(
              empty($this->usageCounts[PAPAYA_DB_TBL_BOX_VERSIONS_TRANS][$lngId])
                ? 0 : (int)$this->usageCounts[PAPAYA_DB_TBL_BOX_VERSIONS_TRANS][$lngId]
            );
          }
        }
        break;
      case 'page':
      default:
        $listview->items[] = $item = new \Papaya\Ui\Listview\Item(
          'items-page', new \Papaya\Ui\Text\Translated('Pages')
        );
        $item->columnSpan = 4;
        foreach ($this->papaya()->languages as $lngId => $language) {
          if ($language['is_content']) {
            $listview->items[] = $item = new \Papaya\Ui\Listview\Item(
              './pics/language/'.$language['image'], $language['title'].' ('.$language['code'].')'
            );
            $item->indentation = 1;
            $item->subitems[] = new \Papaya\Ui\Listview\Subitem\Text(
              empty($this->usageCounts[PAPAYA_DB_TBL_TOPICS_TRANS][$lngId])
                ? 0 : (int)$this->usageCounts[PAPAYA_DB_TBL_TOPICS_TRANS][$lngId]
            );
            $item->subitems[] = new \Papaya\Ui\Listview\Subitem\Text(
              empty($this->usageCounts[PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS][$lngId])
                ? 0 : (int)$this->usageCounts[PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS][$lngId]
            );
            $item->subitems[] = new \Papaya\Ui\Listview\Subitem\Text(
              empty($this->usageCounts[PAPAYA_DB_TBL_TOPICS_VERSIONS_TRANS][$lngId])
                ? 0 : (int)$this->usageCounts[PAPAYA_DB_TBL_TOPICS_VERSIONS_TRANS][$lngId]
            );
          }
        }
        break;
      }
      return $listview->getXml();
    }
    return '';
  }

  /**
  * Get delete form
  *
  * @access public
  */
  function getViewDeleteDialog() {
    if (isset($this->view) && is_array($this->view)) {
      $hidden = array(
        'cmd' => 'view_delete',
        'view_id' => $this->view['view_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete view "%s" (%s)?'),
        $this->view['view_title'],
        (int)$this->view['view_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }
  /**
  * Get delete form
  *
  * @access public
  */
  function getViewUnlinkDialog() {
    if (isset($this->viewLink) && is_array($this->viewLink)) {
      $hidden = array(
        'cmd' => 'view_unlink',
        'view_id' => $this->viewLink['view_id'],
        'viewmode_id' => $this->viewLink['viewmode_id'],
        'confirm_unlink' => 1
      );
      $msg = sprintf(
        $this->_gt('Unlink output filter "%s" (%d) from view "%s" (%d)?'),
        $this->viewLink['viewmode_ext'],
        (int)$this->viewLink['viewmode_id'],
        $this->view['view_title'],
        (int)$this->viewLink['view_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->buttonTitle = 'Unlink';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * load view usage limits
  * @param array $pageLimitIds
  * @return void
  */
  function loadViewLimits($pageLimitIds) {
    $this->limits = array();
    $filter = $this->databaseGetSQLCondition('t.topic_id', $pageLimitIds);
    $sql = "SELECT t.topic_id, t.topic_modified, tt.topic_title,
                   tp.topic_modified as topic_published,
                   v.view_id, v.view_title
              FROM %s t
              LEFT OUTER JOIN %s tt
                ON (tt.topic_id = t.topic_id AND tt.lng_id = %d)
              LEFT OUTER JOIN %s tp ON tp.topic_id = t.topic_id
              LEFT OUTER JOIN %s v ON (v.view_id = tt.view_id)
             WHERE $filter
             ORDER BY tt.topic_title, t.topic_id";
    $params = array(
      $this->tableTopics, $this->tableTopicsTrans,
      $this->papaya()->administrationLanguage->id,
      $this->tableTopicsPublic, $this->tableViews, $this->tableViewModes);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->limits[$row['topic_id']] = $row;
      }
    }
  }

  /**
  * Get xml listview for view limits
  * @return string
  */
  function getViewLimitList() {
    if (isset($this->view) && $this->view['view_id'] > 0 &&
        $this->view['module_type'] == 'page' && isset($this->view['LIMITS'])) {
      $this->loadViewLimits($this->view['LIMITS']);
      if (is_array($this->limits) && count($this->limits) > 0) {
        $images = $this->papaya()->images;
        $result = sprintf(
          '<listview title="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Limits'))
        );
        $result .= '<items>'.LF;
        foreach ($this->limits as $viewLimit) {
          $imageIdx = 'items-page';
          if (!empty($viewLimit['topic_title'])) {
            $pageTitle = $viewLimit['topic_title'];
          } elseif ($viewLimit['topic_id'] > 0) {
            $pageTitle = $this->_gt('No title');
          } else {
            $pageTitle = $this->_gt('Page not found');
            $imageIdx = 'status-page-warning';
          }
          if ($viewLimit['topic_id'] > 0) {
            if (isset($this->params['page_id']) &&
                $this->params['page_id'] == $viewLimit['topic_id']) {
              $selected = ' selected="selected"';
            } else {
              $selected = '';
            }
            $result .= sprintf(
              '<listitem title="%s #%d" href="%s" image="%s"%s>',
              papaya_strings::escapeHTMLChars($viewLimit['topic_title']),
              (int)$viewLimit['topic_id'],
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'cmd' => 'view_limit_edit',
                    'view_id' => (int)$this->view['view_id'],
                    'page_id' => (int)$viewLimit['topic_id']
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($images[$imageIdx]),
              $selected
            );
            $result .= sprintf(
              '<subitem align="center"><a href="%s" title="%s">'.
                '<glyph src="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'cmd' => 'view_limit_edit',
                    'view_id' => (int)$this->view['view_id'],
                    'page_id' => (int)$viewLimit['topic_id']
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($this->_gt('Edit')),
              papaya_strings::escapeHTMLChars($images['actions-edit'])
            );
            $result .= sprintf(
              '<subitem align="center"><a href="%s" title="%s">'.
                '<glyph src="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array('page_id' => (int)$viewLimit['topic_id']),
                  'tt',
                  'topic.php'
                )
              ),
              papaya_strings::escapeHTMLChars($this->_gt('View Page')),
              papaya_strings::escapeHTMLChars($images['categories-preview'])
            );
            $result .= sprintf(
              '<subitem align="center"><a href="%s" title="%s">'.
              '<glyph src="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'cmd' => 'view_limit_delete',
                    'view_id' => (int)$this->view['view_id'],
                    'page_id' => (int)$viewLimit['topic_id']
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($this->_gt('Delete Limit')),
              papaya_strings::escapeHTMLChars($images['actions-generic-delete'])
            );
          } else {
            $result .= sprintf(
              '<listitem title="%s #%d" image="%s">',
              papaya_strings::escapeHTMLChars($pageTitle),
              (int)$viewLimit['topic_id'],
              papaya_strings::escapeHTMLChars($images[$imageIdx])
            );
            $result .= '<subitem/>';
            $result .= '<subitem/>';
            $result .= '<subitem/>';
          }
          $result .= '</listitem>';
        }
        $result .= '</items>'.LF;
        $result .= '</listview>'.LF;
        return $result;
      }
    }
    return '';
  }

  /**
  * Initialize the view limit edit/add dialog
  * @return void
  */
  function initializeLimitDialog() {
    if (!(isset($this->dialogLimit) && is_array($this->dialogLimit))) {
      if (isset($this->view) && is_array($this->view)  &&
          isset($this->view['LIMITS']) && is_array($this->view['LIMITS']) &&
          isset($this->params['page_id']) &&
          in_array($this->params['page_id'], $this->view['LIMITS'])) {
        $data = $this->view;
        $hidden = array(
          'view_id' => (int)$this->view['view_id'],
          'page_id' => (int)$this->params['page_id'],
          'old_page_id' => empty($this->params['old_page_id'])
            ? $this->params['old_page_id'] : $this->params['page_id'],
          'cmd' => 'view_limit_edit'
        );
        $btnCaption = 'Edit';
        $css = 'large';
      } else {
        $data = array();
        $hidden = array(
          'view_id' => (int)$this->view['view_id'],
          'cmd' => 'view_limit_add'
        );
        $btnCaption = 'Add';
        $css = 'large';
      }
      $fields = array(
        'page_id' => array('Page Id', 'isNum', TRUE, 'pageid', 10, ''),
      );
      $this->dialogLimit = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogLimit->loadParams();
      $this->dialogLimit->dialogTitle = $this->_gt('Limit');
      $this->dialogLimit->buttonTitle = $btnCaption;
      $this->dialogLimit->inputFieldSize = $css;
      $this->dialogLimit->dialogDoubleButtons = FALSE;
    }
  }

  /**
  * Get the view limit edit/add dialog and add it to the layout object.
  * @return void
  */
  function getLimitDialog() {
    $this->initializeLimitDialog();
    $this->layout->add($this->dialogLimit->getDialogXML());
  }

  /**
  * check if a given page id exists
  * @param integer $pageId
  * @return boolean
  */
  function pageExists($pageId) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE topic_id = %d";
    $params = array($this->tableTopics, (int)$pageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField() > 0;
    }
    return FALSE;
  }

  /**
  * save changed view limits to database
  * @param $values
  * @return boolean
  */
  function editViewLimit($values) {
    if (isset($this->view)) {
      if (isset($this->view['LIMITS']) && is_array($this->view['LIMITS'])) {
        $ids = array_flip($this->view['LIMITS']);
      } else {
        $ids = array();
      }
      if (isset($this->params['old_page_id']) && $this->params['old_page_id'] > 0) {
        unset($ids['old_page_id']);
      }
      $ids[(int)$values['page_id']] = TRUE;
      $data = array(
        'view_limits' => implode(',', array_keys($ids))
      );
      return (
        FALSE !== $this->databaseUpdateRecord(
          $this->tableViews, $data, 'view_id', (int)$this->view['view_id']
        )
      );
    }
    return FALSE;
  }

  /**
   * delete a view limit from database
   * @param $pageId
   * @internal param $values
   * @return boolean
   */
  function deleteViewLimit($pageId) {
    if (isset($this->view)) {
      if (isset($this->view['LIMITS']) && is_array($this->view['LIMITS'])) {
        $ids = array_flip($this->view['LIMITS']);
      } else {
        $ids = array();
      }
      unset($ids[$pageId]);
      $data = array(
        'view_limits' => implode(',', array_keys($ids))
      );
      return (
        FALSE !== $this->databaseUpdateRecord(
          $this->tableViews, $data, 'view_id', (int)$this->view['view_id']
        )
      );
    }
    return FALSE;
  }

  /**
   * validate the checksum and update it if neccessary.
   */
  function validateChecksum() {
    $checksum = $this->getViewChecksum();
    if ($checksum != $this->view['view_checksum']) {
      $this->databaseUpdateRecord(
        $this->tableViews,
        array('view_checksum' => $checksum),
        array('view_id' => $this->view['view_id'])
      );
    }
  }

  /**
   * Compile a checksum for the view, to allow to find duplicates.
   *
   * @return string
   */
  function getViewChecksum() {
    $limits = array();
    if (is_array($this->view['LIMITS'])) {
      $limits = $this->view['LIMITS'];
      sort($limits);
    }
    $modes = array();
    if (is_array($this->view['MODES'])) {
      foreach ($this->view['MODES'] as $mode) {
        $options = \Papaya\Utility\Text\Xml::unserializeArray($mode['viewlink_data']);
        ksort($options);
        $modes[$mode['viewmode_id']] = $options;
      }
      ksort($modes);
    }
    $data = array(
      'limits' => $limits,
      'modes' => $modes
    );
    return $this->view['module_guid'].':'.md5(serialize($data));
  }
}
