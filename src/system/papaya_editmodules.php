<?php
/**
* Administration class for change modules
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
* @version $Id: papaya_editmodules.php 39818 2014-05-13 13:15:13Z weinert $
*/

/**
* Administration class for change modules
* @package Papaya
* @subpackage Administration
*/
class papaya_editmodules extends base_db {
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * Papaya database table module groups
  * @var string $tableModulegroups
  */
  var $tableModulegroups = PAPAYA_DB_TBL_MODULEGROUPS;

  /**
  * Maximum favorite links in main menu bar
  * @var int
  */
  var $favoriteMax = 5;

  /**
   * @var PapayaTemplate
   */
  public $layout = NULL;

  /**
   * @var array|NULL
   */
  public $modules = NULL;

  /**
   * @var array|NULL
   */
  private $module = NULL;

  /**
   * @var base_module
   */
  private $_moduleInstance;

  /**
  * Constructor
  *
  * @param string $moduleClass optional, default value ''
  * @access public
  */
  function __construct($moduleClass = '') {
    $this->moduleClass = $moduleClass;
  }

  /**
  * Load modules list
  *
  * @access public
  */
  function loadModulesList() {
    unset($this->modules);
    $sql = "SELECT module_guid, module_title, module_class,
                   module_file, module_path, module_glyph
              FROM %s
             WHERE module_type = 'admin' AND module_active = 1
             ORDER BY module_title";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableModules))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->modules[$row["module_guid"]] = $row;
      }
    }
  }

  /**
  * Get button array
  *
  * @access public
  * @return mixed
  */
  function getButtonArray() {
    $result = NULL;
    if (isset($this->modules) && is_array($this->modules)) {
      $administrationUser = $this->papaya()->administrationUser;
      foreach ($this->modules as $id => $module) {
        if (isset($administrationUser->userModules) &&
            is_array($administrationUser->userModules) &&
            in_array($id, $administrationUser->userModules)) {
          if ($administrationUser->isAdmin() || $administrationUser->hasModulePerm(1, $id)) {
            if (trim($module['module_glyph']) != '') {
              $glyph = 'modglyph.php?module='.urlencode($module['module_guid']);
            } else {
              $glyph = '';
            }
            $result[] = array($module['module_title'], $module['module_title'],
              $glyph, 0,
              'module_'.$module['module_class'].'.php', '_self',
              $this->moduleClass == $module['module_class'], NULL, TRUE);
          }
        }

      }
      $result[] = array(
        $this->_gt('Applications'),
        $this->_gt('Applications list'),
        $this->papaya()->images['categories-applications'],
        0,
        'module.php',
        '_self',
        FALSE,
        NULL,
        TRUE
      );
    }
    return $result;
  }

  /**
  * Load module
  *
  * @access public
  * @return boolean
  */
  function loadModule() {
    unset($this->module);
    $sql = "SELECT m.module_guid, m.module_title, m.module_class, m.module_file,
                   m.module_path, m.module_glyph, m.modulegroup_id,
                   mg.modulegroup_tables
              FROM %s m
              LEFT OUTER JOIN %s mg ON mg.modulegroup_id = m.modulegroup_id
             WHERE module_class = '%s' AND module_active = 1";
    $params = array($this->tableModules,
      $this->tableModulegroups, $this->moduleClass);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->module = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Initialize parameters
  *
  * @access public
  * @return boolean
  */
  function initialize() {
    if ($this->loadModule()) {
      if ($this->checkTables($this->module['modulegroup_tables'])) {
        $this->_moduleInstance = $this->papaya()->plugins->get(
          $this->module['module_guid']
        );
        if (isset($this->_moduleInstance) && is_object($this->_moduleInstance)) {
          $this->_moduleInstance->layout = $this->layout;
          $this->_moduleInstance->images = $this->papaya()->images;
          $this->_moduleInstance->authUser = $this->papaya()->administrationUser;
          $this->layout->parameters()->assign(
            array(
              'PAGE_TITLE' =>
                $this->_gt('Applications').' - '.$this->module['module_title'],
              'PAGE_ICON' =>
              './modglyph.php?size=22&module='.urlencode($this->module['module_guid'])
            )
          );
          return TRUE;
        }
      }
    } else {
      $this->loadModulesList();
      if (isset($this->modules) &&
          is_array($this->modules) &&
          count($this->modules) > 0) {
        $this->initializeParams();
        $this->layout->parameters()->assign(
          array(
            'PAGE_TITLE' => $this->_gt('Applications'),
            'PAGE_ICON' => $this->papaya()->images['categories-applications']
          )
        );
      } else {
        $this->addMsg(MSG_INFO, 'No modules installed');
      }
    }
    return FALSE;
  }

  /**
  * Execute
  *
  * @access public
  */
  function execute() {
    if (isset($this->_moduleInstance) && is_object($this->_moduleInstance)) {
      $this->_moduleInstance->execModule();
    } elseif (isset($this->modules) &&
       is_array($this->modules) && count($this->modules) > 0) {
      $administrationUser = $this->papaya()->administrationUser;
      $currentUserModules = array_intersect(
        $administrationUser->userModules, array_keys($this->modules)
      );
      $administrationUser->userModules = $currentUserModules;
      if (isset($this->params['cmd'])) {
        switch ($this->params['cmd']) {
        case 'switch_favorite' :
          if (isset($this->params['module_id']) &&
              isset($this->modules[$this->params['module_id']])) {
            if (in_array($this->params['module_id'], $administrationUser->userModules)) {
              $moduleIndex = array_search(
                $this->params['module_id'], $administrationUser->userModules
              );
              unset($administrationUser->userModules[$moduleIndex]);
              if (
                $administrationUser->saveUserOption(
                  'PAPAYA_USER_MODULES', implode(',', $administrationUser->userModules)
                )
              ) {
                $this->addMsg(MSG_INFO, $this->_gt('Favorite removed.'));
              } else {
                $administrationUser->userModules = $currentUserModules;
                $this->addMsg(MSG_INFO, $this->_gt('Database Error.'));
              }
            } else {
              if ($this->favoriteMax > count($administrationUser->userModules)) {
                $administrationUser->userModules[] = $this->params['module_id'];
                if (
                  $administrationUser->saveUserOption(
                    'PAPAYA_USER_MODULES', implode(',', $administrationUser->userModules)
                  )
                ) {
                  $this->addMsg(MSG_INFO, $this->_gt('Favorite added.'));
                } else {
                  $administrationUser->userModules = $currentUserModules;
                  $this->addMsg(MSG_INFO, $this->_gt('Database Error.'));
                }
              } else {
                $this->addMsg(MSG_INFO, $this->_gt('Favorite limit reached.'));
              }
            }
          }
          break;
        }
      }
      $this->getModulesListView();
    }
  }

  /**
  * Check database tables
  *
  * @param string $tableString
  * @access public
  * @return boolean
  */
  function checkTables($tableString) {
    $result = TRUE;
    if (trim($tableString) != '') {
      $tables = explode(',', $tableString);
      if (isset($tables) && is_array($tables) && count($tables) > 0 &&
          is_array($dbTables = $this->databaseQueryTableNames())) {
        $dbTables = array_flip($dbTables);
        foreach ($tables as $tableName) {
          if (!isset($dbTables[PAPAYA_DB_TABLEPREFIX.'_'.$tableName])) {
            $hidden = array(
              'pkg_id' => (int)$this->module['modulegroup_id'],
              'module_id' => 0,
              'table' => '',
            );
            $msg = $this->_gt('Missing tables - Go to module managment?');
            $dialog = new base_msgdialog($this, 'mods', $hidden, $msg, 'warning');
            $dialog->buttonTitle = 'Goto';
            $dialog->baseLink = 'modules.php';
            if ($str = $dialog->getMsgDialog()) {
              $this->layout->add($str);
            }
            return FALSE;
          }
        }
        $data = array('modulegroup_tables' => '');
        $this->databaseUpdateRecord(
          $this->tableModulegroups,
          $data,
          'modulegroup_id',
          (int)$this->module['modulegroup_id']
        );
      }
    }
    return $result;
  }

  /**
  * Get modules listview xml
  * @return void
  */
  function getModulesListView() {
    $result = '';
    if (isset($this->modules) &&
      is_array($this->modules) && count($this->modules) > 0) {
      $administrationUser = $this->papaya()->administrationUser;
      $modules = array();
      foreach ($this->modules as $id => $module) {
        if ($administrationUser->isAdmin() || $administrationUser->hasModulePerm(1, $id)) {
          $modules[] = $id;
        }
      }
      if (count($modules) > 0) {
        $images = $this->papaya()->images;
        $result = sprintf(
          '<listview title="%s" mode="tile">',
          papaya_strings::escapeHTMLChars($this->_gt('Applications'))
        );
        $result .= '<items>';
        foreach ($modules as $id) {
          $module = $this->modules[$id];
          if (trim($module['module_glyph']) != '') {
            $glyph = './modglyph.php?module='.urlencode($module['module_guid']);
          } else {
            $glyph = '';
          }
          list($basePath) = explode('/', $module['module_path'], 2);
          $result .= sprintf(
            '<listitem image="%s" href="%s" title="%s" subtitle="%s">',
            papaya_strings::escapeHTMLChars($glyph),
            papaya_strings::escapeHTMLChars('module_'.$module['module_class'].'.php'),
            papaya_strings::escapeHTMLChars($module['module_title']),
            papaya_strings::escapeHTMLChars($basePath.'/...')
          );
          if (in_array($id, $administrationUser->userModules)) {
            $result .= sprintf(
              '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(array('cmd' => 'switch_favorite', 'module_id' => $id))
              ),
              papaya_strings::escapeHTMLChars($images['items-favorite']),
              papaya_strings::escapeHTMLChars($this->_gt('Remove from menu'))
            );
          } elseif (count($administrationUser->userModules) < $this->favoriteMax) {
            $result .= sprintf(
              '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(array('cmd' => 'switch_favorite', 'module_id' => $id))
              ),
              papaya_strings::escapeHTMLChars($images['status-favorite-disabled']),
              papaya_strings::escapeHTMLChars($this->_gt('Add to menu'))
            );
          }
          $result .= '</listitem>';

        }
        $result .= '</items>';
        $result .= '</listview>';
      }
      $this->layout->add($result);
    }
  }
}

