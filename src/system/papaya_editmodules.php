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
if (!defined('IMAGETYPE_SWC')) {
  /**
   * Fallback to ensure the existence of constant IMAGETYPE_SWC. It comes usually with PHP
   * but is missing in some sporadic versions.
   *
   * @ignore
   * IMAGETYPE_SWC Contains integer type for shockwave formats
   */
  define('IMAGETYPE_SWC', 13);
}
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
   * @var string
   */
  private $_moduleGuid;

  /**
   * @var \Papaya\Administration\UI
   */
  public $administrationUI;

  /**
   * Constructor
   *
   * @param string $moduleGuid
   * @access public
   */
  function __construct($moduleGuid = '') {
    $this->_moduleGuid = $moduleGuid;
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
              $glyph = \Papaya\Administration\UI\Route::EXTENSIONS_IMAGE.'?module='.urlencode($module['module_guid']);
            } else {
              $glyph = '';
            }
            $result[] = array($module['module_title'], $module['module_title'],
              $glyph, 0,
              'module_'.$module['module_guid'].'.php', '_self',
              $this->_moduleGuid == $module['module_guid'], NULL, TRUE);
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
             WHERE module_guid = '%s' AND module_active = 1";
    $params = array($this->tableModules,
      $this->tableModulegroups, $this->_moduleGuid);
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
          $this->module['module_guid'], $this->administrationUI
        );
        if ($this->_moduleInstance instanceof \Papaya\Administration\Page) {

        } elseif ($this->_moduleInstance instanceof base_module) {
          $this->_moduleInstance->administrationUI = $this->administrationUI;
          $this->_moduleInstance->layout = $this->administrationUI->template();
          $this->_moduleInstance->images = $this->papaya()->images;
          $this->_moduleInstance->authUser = $this->papaya()->administrationUser;
        }
        if (NULL !== $this->_moduleInstance) {
          $this->administrationUI->template()->parameters()->assign(
            array(
              'PAGE_TITLE' =>
                $this->_gt('Applications').' - '.$this->module['module_title'],
              'PAGE_ICON' =>
              \Papaya\Administration\UI\Route::EXTENSIONS_IMAGE.'?size=22&module='.urlencode($this->module['module_guid'])
            )
          );
          return TRUE;
        }
      }
    } else {
      $this->loadModulesList();
      if (
        is_array($this->modules) &&
        count($this->modules) > 0
      ) {
        $this->initializeParams();
        $this->administrationUI->template()->parameters()->assign(
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
      if ($this->_moduleInstance instanceof Papaya\Administration\Page) {
        $this->_moduleInstance->execute();
      } elseif (method_exists($this->_moduleInstance, 'execModule')) {
        $this->_moduleInstance->execModule();
      }
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
            $dialog->baseLink = \Papaya\Administration\UI\Route::ADMINISTRATION_PLUGINS;
            if ($str = $dialog->getMsgDialog()) {
              $this->administrationUI->template()->add($str);
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
            $glyph = \Papaya\Administration\UI\Route::EXTENSIONS_IMAGE.'?module='.urlencode($module['module_guid']);
          } else {
            $glyph = '';
          }
          list($basePath) = explode('/', $module['module_path'], 2);
          $result .= sprintf(
            '<listitem image="%s" href="%s" title="%s" subtitle="%s">',
            papaya_strings::escapeHTMLChars($glyph),
            papaya_strings::escapeHTMLChars(
              Papaya\Administration\UI\Route::EXTENSIONS.'.'.$module['module_guid']
            ),
            papaya_strings::escapeHTMLChars($module['module_title']),
            papaya_strings::escapeHTMLChars($basePath.'/...')
          );
          if (in_array($id, $administrationUser->userModules)) {
            $result .= sprintf(
              '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                sprintf(
                  Papaya\Administration\UI\Route::EXTENSIONS.'?p/cmd=%s&p/module_id=%s',
                  'switch_favorite',
                  $id
                )
              ),
              papaya_strings::escapeHTMLChars($images['items-favorite']),
              papaya_strings::escapeHTMLChars($this->_gt('Remove from menu'))
            );
          } elseif (count($administrationUser->userModules) < $this->favoriteMax) {
            $result .= sprintf(
              '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                sprintf(
                  Papaya\Administration\UI\Route::EXTENSIONS.'?p/cmd=%s&p/module_id=%s',
                  'switch_favorite',
                  $id
                )
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
      $this->administrationUI->template()->add($result);
    }
  }

  /**
   * Get glyph, swf or js file from module directory
   *
   * @access public
   * @return void
   */
  function getGlyph() {
    if (isset($_GET['module']) && preg_match('/^[a-fA-F\d]{32}$/D', $_GET['module'])) {
      $sql = "SELECT module_path, module_glyph
                FROM %s
               WHERE module_guid = '%s'";
      $tableModules = $this->databaseGetTableName(\Papaya\Content\Tables::MODULES);
      if ($res = $this->databaseQueryFmt($sql, array($tableModules, $_GET['module']))) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $pattern = '(^((?:script)/)([\w-]+\.(js|vbs|css|html|xml))$)D';
          if (isset($_GET['src']) && preg_match($pattern, $_GET['src'], $regs)) {
            $scriptPath = $this->prependModulePath($row['module_path'].'script/');
            $scriptFileName = $scriptPath.$regs[2];
            if (
              file_exists($scriptFileName) &&
              is_file($scriptFileName) &&
              is_readable($scriptFileName)
            ) {
              header(
                'Last-Modified: '.gmdate('D, d M Y H:i:s', @filemtime($scriptFileName)).' GMT'
              );
              header('Expires: '.gmdate('D, d M Y H:i:s', (time() + 2592000)).' GMT');
              switch ($regs[3]) {
              case 'vbs' :
                header('Content-type: text/vbscript');
                break;
              case 'css' :
                header('Content-type: text/css');
                break;
              case 'html' :
                header('Content-type: text/html');
                break;
              case 'xml' :
                header('Content-type: application/xml');
                break;
              case 'js' :
              default :
                header('Content-type: text/javascript');
                break;
              }
              readfile($scriptFileName);
            } else {
              header('Content-type: text/javascript');
              printf(
                'if (console.error) { console.error("Script file %%s not found", "%s"); }',
                $regs[2]
              );
            }
            exit;
          } elseif (
            isset($_GET['src']) &&
            preg_match('~^((?:flash)/)?([\w-]+\.(?:swf))$~D', $_GET['src'], $regs)
          ) {
            $imageFileName = $regs[2];
            $imagePath = $row['module_path'].'flash/';
            $imageFiles = array(
              $imagePath.$imageFileName
            );
          } else {
            $imagePath = $this->prependModulePath($row['module_path'].'pics/');
            $imageFilePattern = '(^((?:pics|images)/)?([\w-]+\.(?:gif|png|jpg|jpeg))$)D';
            if (
              isset($_GET['src']) &&
              preg_match($imageFilePattern, $_GET['src'], $regs)
            ) {
              $imageFileName = $regs[2];
            } else {
              $imageFileName = $row['module_glyph'];
            }
            if (isset($_GET['size']) && in_array((int)$_GET['size'], array(16, 20, 22, 48))) {
              $imageSizePath = (int)$_GET['size'].'x'.(int)$_GET['size'].'/';
            } else {
              $imageSizePath = '16x16/';
            }
            $imageFiles = array(
              $imagePath.$imageSizePath.$imageFileName,
              $imagePath.$imageFileName
            );
          }
          foreach ($imageFiles as $imageFile) {
            if (file_exists($imageFile) && is_file($imageFile) && is_readable($imageFile)) {
              list(, , $type) = getImageSize($imageFile);
              $imageTypes = array(
                IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_SWF, IMAGETYPE_SWC
              );
              if (in_array($type, $imageTypes)) {
                if ($fh = @fopen($imageFile, 'r')) {
                  header(
                    'Last-Modified: '.gmdate('D, d M Y H:i:s', @filemtime($imageFile)).' GMT'
                  );
                  header('Expires: '.gmdate('D, d M Y H:i:s', (time() + 2592000)).' GMT');
                  header('Content-type: '.image_type_to_mime_type($type));
                  fpassthru($fh);
                  fclose($fh);
                  exit;
                }
              }
            }
          }
        }
      }
    }
    header('Content-type: image/gif');
    // @codingStandardsIgnoreStart
    printf(
      '%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%',
      71, 73, 70, 56, 57, 97, 1, 0, 1, 0, 128, 255, 0, 192, 192, 192, 0, 0, 0, 33,
      249, 4, 1, 0, 0, 0, 0, 44, 0, 0, 0, 0, 1, 0, 1, 0, 0, 2, 2, 68, 1, 0, 59
    );
    // @codingStandardsIgnoreEnd
    exit;
  }

  /**
   * Get the absolute path of directory file
   */
  private function prependModulePath($path) {
    $map = array(
      'vendor:' => \Papaya\Utility\File\Path::getVendorPath(),
      'src:' => \Papaya\Utility\File\Path::getSourcePath()
    );
    foreach ($map as $prefix => $mapPath) {
      if (0 === strpos($path, $prefix)) {
        $basePath = \Papaya\Utility\File\Path::getDocumentRoot().$mapPath;
        $relativePath = substr($path, strlen($prefix));
        return \Papaya\Utility\File\Path::cleanup(
          $basePath.$relativePath, TRUE
        );
      }
    }
    return realpath($path);
  }
}

