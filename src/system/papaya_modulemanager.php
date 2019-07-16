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

use Papaya\UI;

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
 * table status unknown
 */
define('PAPAYA_MODULE_TABLE_UNKNOWN', 0);
/**
 * table status ok (up to date)
 */
define('PAPAYA_MODULE_TABLE_OK', 1);
/**
 * table status error (not existing, not up to date)
 */
define('PAPAYA_MODULE_TABLE_MISSING', 2);
/**
 * table status error (not existing, not up to date)
 */
define('PAPAYA_MODULE_TABLE_ERROR', 3);

/**
 * Administration Papaya modules
 *
 * @package Papaya
 * @subpackage Core
 */
class papaya_modulemanager extends base_db {
  const ACTION_FIELD_ADD = 'field_add';
  const ACTION_FIELD_REMOVE = 'field_delete';
  const ACTION_FIELD_CHANGE = 'field_update';
  const ACTION_INDEX_ADD = 'index_add';
  const ACTION_INDEX_REMOVE = 'index_delete';
  const ACTION_INDEX_CHANGE = 'index_update';
  /**
   * XML modules list filename
   * @var string $modulesFileName
   */
  var $modulesFileName = 'modules.xml';
  /**
   * Modules parameter names
   * @var array $modulesParamNames
   */
  var $modulesParamNames = array('type', 'guid', 'name', 'class');
  /**
   * Module types
   * @var array $moduleTypes
   */
  var $moduleTypes = \Papaya\Plugin\Types::ALL;

  /**
   * Packages
   * @var array $packages
   */
  var $packages = array();
  /**
   * Modules
   * @var array $modules
   */
  var $modules = array();
  /**
   * Tables
   * @var array $tables
   */
  var $tables = array();

  /**
   * Load tables
   * @var boolean $loadTables
   */
  var $loadTables = FALSE;

  /**
   * Module dialog
   * @var object base_dialog $moduleDialog
   */
  var $moduleDialog = NULL;

  /**
   * name of csv file with import data
   * @var string | NULL
   */
  var $importDataFile = NULL;

  /**
   * Idnetifies the directory with the data file (cache|module)
   * @var string | NULL
   */
  var $importDataPathIdent = NULL;

  /**
   * Prefix all tables (used by installer for system tables)
   * @var boolean
   */
  var $alwaysPrefix = FALSE;

  /**
   * @var NULL|array
   */
  public $module = NULL;
  /**
   * @var NULL|array
   */
  public $_tableStructure = NULL;

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
   * @var base_dialog
   */
  private $dialogTableImport;

  /**
   * Initialize parameters
   *
   * @param string $paramName optional, default value 'mods'
   * @access public
   */
  function initialize($paramName = 'mods') {
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_'.get_class($this).'_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('module_id', array('table', 'field'));
    $this->initializeSessionParam('table', array('module_id', 'field'));
    $this->initializeSessionParam('pkg_id', array('module_id', 'table', 'field'));
    $this->initializeSessionParam('module_mode');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
   * Get the absolute path of directory file
   */
  public function prependModulePath($path) {
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

  /**
   * Strip the base modules path from the absolute path, returning the relative path
   */
  public function stripModulePath($path) {
    if ($position = strpos($path, '/vendor/')) {
      return 'vendor:'.substr($path, $position + 7);
    } elseif ($position = strpos($path, '/src/')) {
      return 'src:'.substr($path, $position + 5);
    } else {
      return $path;
    }
  }

  /**
   * Execute - basic function for handling parameters
   *
   * @access public
   */
  function execute() {
    $this->loadPackages();
    if (isset($this->params['pkg_id'])) {
      if (isset($this->packages[$this->params['pkg_id']])) {
        $this->loadPackageData($this->params['pkg_id']);
      }
    }
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'rpc_check':
        $this->rpcCheckTableStatus();
        break;
      case 'rpc_count':
        $this->rpcGetPackageCount();
        break;
      case 'rpc_import_table_data' :
        $this->rpcImportTableData();
        break;
      case 'scan':
        $this->loadTables = TRUE;
        $this->searchModules();
        $this->updatePackageTable();
        $this->updateModuleTable();
        $this->clearPackageStatus();
        unset($this->params);
        break;
      case 'module_save':
        if (
          isset($this->params['module_id']) &&
          $this->loadModule($this->params['module_id'])
        ) {
          if ($this->initModuleDialog()) {
            if ($this->moduleDialog->modified()) {
              if ($this->saveModule()) {
                $this->addMsg(
                  MSG_INFO,
                  $this->_gtf('%s modified.', $this->_gt('Module'))
                );
              }
            }
          }
        }
        break;
      case 'pkg_table_create':
        $pkgId = $this->params['pkg_id'];
        if (
          isset($this->params['pkg_table_create_confirm']) &&
          isset($this->packages[$pkgId])
        ) {
          $path = $this->getTableDataPath($this->packages[$pkgId]['modulegroup_path']);
          if (
            isset($this->tables) &&
            is_array($this->tables) &&
            count($this->tables) > 0
          ) {
            $tableExists = 0;
            $tableCreated = 0;
            foreach ($this->tables as $tableName => $inDatabase) {
              if ($inDatabase === TRUE) {
                $tableExists = (empty($tableExits)) ? 1 : $tableExits + 1;
              } else {
                $xmlFileName = $path.'table_'.$tableName.'.xml';
                if ($struct = $this->loadTableStructure($xmlFileName, $pkgId)) {
                  $created = $this->getSchema()->createTable(
                    $struct['expected'],
                    $this->getTablePrefixUsage($tableName, $pkgId)
                      ? PAPAYA_DB_TABLEPREFIX : NULL
                  );
                  if ($created) {
                    $tableCreated++;
                    $this->addMsg(
                      MSG_INFO,
                      $this->_gtf('Table "%s" created.', $tableName)
                    );
                    $this->setTableStatus(
                      $this->prependModulePath($this->packages[$pkgId]['modulegroup_path']),
                      $tableName,
                      FALSE
                    );
                  } else {
                    $this->addMsg(
                      MSG_ERROR,
                      $this->_gtf('Could not create table "%s".', $tableName)
                    );
                  }
                  unset($struct);
                } else {
                  $this->addMsg(
                    MSG_ERROR,
                    $this->_gtf('Could not find xml file for table "%s".', $tableName)
                  );
                }
              }
            }
            if ($tableExists + $tableCreated = count($this->tables)) {
              unset($this->params['cmd']);
            }
          }
        }
        break;
      case 'table_create':
        if (
          isset($this->params['table']) &&
          isset($this->params['table_create_confirm'])
        ) {
          $pkgId = $this->params['pkg_id'];
          if (isset($this->packages[$pkgId])) {
            $path = $this->getTableDataPath($this->packages[$pkgId]['modulegroup_path']);
            $xmlFileName = $path.'table_'.$this->params['table'].'.xml';
            if ($struct = $this->loadTableStructure($xmlFileName, $pkgId)) {
              $created = $this->getSchema()->createTable(
                $struct['expected'],
                $this->getTablePrefixUsage($this->params['table'], $pkgId)
                  ? PAPAYA_DB_TABLEPREFIX : ''
              );
              if ($created) {
                unset($this->params['cmd']);
                $this->addMsg(MSG_INFO, $this->_gt('Table created.'));
              }
              unset($struct);
            }
          }
        }
        break;
      case self::ACTION_FIELD_ADD:
      case self::ACTION_FIELD_CHANGE:
        if (
          isset($this->params['field_action_confirm'], $this->params['table']) &&
          $this->params['field_action_confirm'] &&
          \Papaya\Filter\Factory::isTextWithNumbers($this->params['table'])
        ) {
          if (isset($this->packages[$this->params['pkg_id']])) {
            $path = $this->getTableDataPath(
              $this->packages[$this->params['pkg_id']]['modulegroup_path']
            );
            $xmlFileName = $path.'table_'.$this->params['table'].'.xml';
            if ($struct = $this->loadTableStructure($xmlFileName, $this->params['pkg_id'])) {
              $tableFullName = $this->getTableFullName($this->params['table']);
              if (isset($struct['expected']->fields[$this->params['field']])) {
                $field = $struct['expected']->fields[$this->params['field']];
                switch ($this->params['cmd']) {
                case self::ACTION_FIELD_ADD:
                  if ($this->getSchema()->addField($tableFullName, $field)) {
                    unset($this->params['cmd']);
                    $this->addMsg(
                      MSG_INFO,
                      $this->_gt('Field added.')
                    );
                  }
                  break;
                case self::ACTION_FIELD_CHANGE:
                  if ($this->getSchema()->changeField($tableFullName, $field)) {
                    unset($this->params['cmd']);
                    $this->addMsg(
                      MSG_INFO,
                      $this->_gt('Field modified.')
                    );
                  }
                  break;
                }
              }
              unset($struct);
            }
          }
        }
        break;
      case self::ACTION_FIELD_REMOVE:
        if (
          isset($this->params['field_action_confirm'], $this->params['table'], $this->params['field']) &&
          $this->params['field_action_confirm'] &&
          \Papaya\Filter\Factory::isTextWithNumbers($this->params['table']) &&
          \Papaya\Filter\Factory::isTextWithNumbers($this->params['field'])
        ) {
          $changed = $this->getSchema()->dropField(
            $this->getTableFullName($this->params['table']),
            $this->params['field']
          );
          if ($changed) {
            unset($this->params['cmd']);
            $this->addMsg(MSG_INFO, $this->_gt('Field deleted.'));
          }
        }
        break;
      case self::ACTION_INDEX_ADD:
      case self::ACTION_INDEX_CHANGE:
        if (
          isset($this->params['index_action_confirm'], $this->params['table']) &&
          $this->params['index_action_confirm'] &&
          \Papaya\Filter\Factory::isTextWithNumbers($this->params['table'])
        ) {
          if (isset($this->packages[$this->params['pkg_id']])) {
            $path = $this->getTableDataPath(
              $this->packages[$this->params['pkg_id']]['modulegroup_path']
            );
            $xmlFileName = $path.'table_'.$this->params['table'].'.xml';
            if ($struct = $this->loadTableStructure($xmlFileName, $this->params['pkg_id'])) {
              $tableFullName = $this->getTableFullName($this->params['table']);
              if (isset($struct['expected']->indizes[$this->params['index']])) {
                $index = $struct['expected']->indizes[$this->params['index']];
                switch ($this->params['cmd']) {
                case self::ACTION_INDEX_ADD:
                  if ($this->getSchema()->addIndex($tableFullName, $index)) {
                    unset($this->params['cmd']);
                    $this->addMsg(
                      MSG_INFO, $this->_gt('Index added.')
                    );
                  }
                  break;
                case self::ACTION_INDEX_CHANGE:
                  if ($this->getSchema()->changeIndex($tableFullName, $index)) {
                    unset($this->params['cmd']);
                    $this->addMsg(
                      MSG_INFO, $this->_gt('Index modified.')
                    );
                  }
                  break;
                }
              }
              unset($struct);
            }
          }
        }
        break;
      case self::ACTION_INDEX_REMOVE:
        if (
          isset($this->params['index_action_confirm'], $this->params['table'], $this->params['index']) &&
          $this->params['index_action_confirm'] &&
          \Papaya\Filter\Factory::isTextWithNumbers($this->params['table']) &&
          \Papaya\Filter\Factory::isTextWithNumbers($this->params['index'])
        ) {
          $changed = $this->getSchema()->dropIndex(
            $this->getTableFullName($this->params['table']), $this->params['index']
          );
          if ($changed) {
            unset($this->params['cmd']);
            $this->addMsg(MSG_INFO, $this->_gtf('%s deleted.', $this->_gt('Index')));
          }
        }
        break;
      case 'sync':
        if (
          isset($this->params['table_action_confirm']) &&
          $this->params['table_action_confirm'] &&
          isset($this->params['table']) &&
          \Papaya\Filter\Factory::isTextWithNumbers($this->params['table'])
        ) {
          if (isset($this->packages[$this->params['pkg_id']])) {
            $path = $this->getTableDataPath(
              $this->packages[$this->params['pkg_id']]['modulegroup_path']
            );
            $xmlFileName = $path.'table_'.$this->params['table'].'.xml';
            if ($struct = $this->loadTableStructure($xmlFileName)) {
              if ($this->syncTableStructure($struct)) {
                $this->addMsg(MSG_INFO, $this->_gt('Table structure updated!'));
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
              }
              unset($struct);
              unset($this->params['cmd']);
            }
          }
        }
        break;
      case 'name_reset' :
        if (
          isset($this->params['db_action_confirm']) &&
          $this->params['db_action_confirm']
        ) {
          if ($this->resetModuleNames()) {
            $this->addMsg(MSG_INFO, $this->_gt('Modules titles set to default.'));
            unset($this->params['cmd']);
          }
        }
        break;
      case 'pkg_enable' :
        if (
          isset($this->params['pkg_id']) &&
          isset($this->params['confirm_status_change']) &&
          $this->params['confirm_status_change']
        ) {
          if ($this->updatePackageStatus($this->params['pkg_id'], TRUE)) {
            $this->addMsg(MSG_INFO, $this->_gt('Modules enabled.'));
            unset($this->params['cmd']);
          }
        }
        break;
      case 'pkg_disable' :
        if (
          isset($this->params['pkg_id']) &&
          isset($this->params['confirm_status_change']) &&
          $this->params['confirm_status_change']
        ) {
          if ($this->updatePackageStatus($this->params['pkg_id'], FALSE)) {
            $this->addMsg(MSG_INFO, $this->_gt('Modules disabled.'));
            unset($this->params['cmd']);
          }
        }
        break;
      case 'module_enable' :
        if (
          isset($this->params['module_id']) &&
          (strlen($this->params['module_id']) > 0)  &&
          isset($this->params['confirm_status_change']) &&
          $this->params['confirm_status_change']
        ) {
          if ($this->updateModuleStatus($this->params['module_id'], TRUE)) {
            $this->addMsg(MSG_INFO, $this->_gt('Selected module enabled.'));
            unset($this->params['cmd']);
          }
        }
        break;
      case 'module_disable' :
        if (
          isset($this->params['module_id']) &&
          (strlen($this->params['module_id']) > 0)  &&
          isset($this->params['confirm_status_change']) &&
          $this->params['confirm_status_change']
        ) {
          if ($this->updateModuleStatus($this->params['module_id'], FALSE)) {
            $this->addMsg(MSG_INFO, $this->_gt('Selected module disabled.'));
            unset($this->params['cmd']);
          }
        }
        break;
      case 'export_table':
        if ($this->params['table'] != '') {
          $db2xml = new base_database2xml;
          $xml = $db2xml->table2xml($this->getTableFullName($this->params['table']));

          $agentStr = empty($_SERVER["HTTP_USER_AGENT"])
            ? '' : strtolower($_SERVER["HTTP_USER_AGENT"]);
          if (strpos($agentStr, 'opera') !== FALSE) {
            $agent = 'OPERA';
          } elseif (strpos($agentStr, 'msie') !== FALSE) {
            $agent = 'IE';
          } else {
            $agent = 'STD';
          }
          $mimeType = ($agent == 'IE' || $agent == 'OPERA')
            ? 'application/octetstream;' : 'application/octet-stream;';
          $fileName = 'table_'.$this->params['table'].'_'.date('Y-m-d').'.xml';
          if ($agent == 'IE') {
            header('Content-Disposition: inline; filename="'.$fileName.'"');
          } else {
            header('Content-Disposition: attachment; filename="'.$fileName.'"');
          }
          header('Content-type: '.$mimeType);
          header('Content-length: '.strlen($xml));
          echo $xml;
          exit;
        }
        break;
      case 'export_table_data':
        if (isset($this->params['table'])) {
          $this->exportTableData();
        }
        break;
      case 'import_table_data' :
        if (isset($this->params['save']) && $this->params['save']) {
          $this->initTableImportDialog();
          if ($this->dialogTableImport->checkDialogInput()) {
            if (!empty($_FILES[$this->paramName]['tmp_name']['table_data_file'])) {
              $tableDataDirectory = PAPAYA_PATH_CACHE.'tabledata';
              if (!(file_exists($tableDataDirectory) && is_dir($tableDataDirectory))) {
                $oldUmask = umask(0);
                mkdir($tableDataDirectory, 0777);
                umask($oldUmask);
              }
              if (file_exists($tableDataDirectory) && is_dir($tableDataDirectory)) {
                $this->removeOldFiles($tableDataDirectory);
                $dataFile =
                  $tableDataDirectory.'/data_'.$this->params['table'].'_'.date('YmdHis').'.csv';
                $tmpFile = $_FILES[$this->paramName]['tmp_name']['table_data_file'];
                if (move_uploaded_file($tmpFile, $dataFile)) {
                  $this->importDataFile = $dataFile;
                  $this->importDataPathIdent = 'cache';
                }
              }
            } else {
              $defaultFile = $this->getDefaultTableDataFile(
                $this->params['pkg_id'],
                $this->params['table']
              );
              if (!empty($defaultFile) && file_exists($defaultFile['file'])) {
                $this->importDataFile = $defaultFile['file'];
                $this->importDataPathIdent = 'module';
              }
            }
          }
        }
        break;
      case 'modules_show' :
        $this->sessionParams['showmodules'] = TRUE;
        break;
      case 'modules_hide' :
        $this->sessionParams['showmodules'] = FALSE;
        break;
      case 'tables_show' :
        $this->sessionParams['showtables'] = TRUE;
        break;
      case 'tables_hide' :
        $this->sessionParams['showtables'] = FALSE;
        break;
      }
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    }
    $this->loadPackages();
    $this->loadPackageData(empty($this->params['pkg_id']) ? 0 : (int)$this->params['pkg_id']);
    $this->loadModule(empty($this->params['module_id']) ? '' : $this->params['module_id']);
    $this->loadTable(
      empty($this->params['pkg_id']) ? 0 : (int)$this->params['pkg_id'],
      empty($this->params['table']) ? '' : $this->params['table']
    );
  }

  /**
   * Handle a javascript rpc call for table status.
   *
   * @access public
   * @return void
   */
  function rpcCheckTableStatus() {
    if (
      isset($this->params['rpc_pkg_id']) &&
      $this->params['rpc_pkg_id'] > 0 &&
      isset($this->packages[$this->params['rpc_pkg_id']])
    ) {
      $package = $this->packages[$this->params['rpc_pkg_id']];
    } elseif (
      empty($this->params['rpc_pkg_id']) &&
      is_array($this->packages) &&
      count($this->packages) > 0
    ) {
      $package = reset($this->packages);
    } else {
      $package = NULL;
    }
    if (isset($package)) {
      $pkgId = $package['modulegroup_id'];
      $packageName = $package['modulegroup_title'];
      $nextPackageId = NULL;
      $nextPackageName = NULL;
      $nextTableName = NULL;
      reset($this->packages);
      while (list($packageId) = each($this->packages)) {
        if ($pkgId == $packageId) {
          $nextPackageData = current($this->packages);
          $nextPackageId = $nextPackageData['modulegroup_id'];
          $nextPackageName = $nextPackageData['modulegroup_title'];
          break;
        }
      }
      $this->loadTables = TRUE;
      $path = $this->prependModulePath(
        $this->packages[$pkgId]['modulegroup_path']
      );
      $packageFileName = $path.$this->modulesFileName;
      if ($packageData = $this->loadPackageFile($packageFileName, $path)) {
        $this->loadPackageData($pkgId);
        $this->loadTableDBData($packageData['tables']);
        if (
          isset($packageData['tables']) &&
          is_array($packageData['tables']) &&
          count($packageData['tables']) > 0
        ) {
          $packageData['tables'] = array_values(array_unique($packageData['tables']));
          if (
            !empty($this->params['rpc_table']) &&
            in_array($this->params['rpc_table'], $packageData['tables'])
          ) {
            $currentTable = $this->params['rpc_table'];
          } else {
            $currentTable = reset($packageData['tables']);
          }
          $tableNames = array_flip($packageData['tables']);
          if (
            isset($tableNames) &&
            is_array($tableNames) &&
            count($tableNames) > 0
          ) {
            if (isset($tableNames[$currentTable])) {
              $this->loadTable($pkgId, $currentTable);
              $nextIdx = $tableNames[$currentTable] + 1;
              if (isset($packageData['tables'][$nextIdx])) {
                $nextTableName = $packageData['tables'][$nextIdx];
              }
            }
          }
        }
      }
      if (!empty($nextTableName)) {
        header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>'.LF;
        echo '<response>'.LF;
        echo '<method>rpcCallbackDatabaseStructure</method>'.LF;
        echo '<param name="nextPackage" value="'.(int)$pkgId.'" />'.LF;
        echo '<param name="nextPackageName" value="'.
          papaya_strings::escapeHTMLChars($packageName).'" />'.LF;
        echo '<param name="nextTable" value="'.
          papaya_strings::escapeHTMLChars($nextTableName).'" />'.LF;
        echo '</response>'.LF;
        exit;
      } elseif (isset($nextPackageId) && $nextPackageId > 0) {
        header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>'.LF;
        echo '<response>'.LF;
        echo '<method>rpcCallbackDatabaseStructure</method>'.LF;
        echo '<param name="nextPackage" value="'.(int)$nextPackageId.'" />'.LF;
        echo '<param name="nextPackageName" value="'.
          papaya_strings::escapeHTMLChars($nextPackageName).'" />'.LF;
        echo '</response>'.LF;
        exit;
      }
    }
    header('Content-type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>'.LF;
    echo '<response>'.LF;
    echo '<method>rpcCallbackDatabaseStructureFinish</method>'.LF;
    echo '</response>'.LF;
    exit;
  }

  /**
   * Handle a javascript rpc class to get the package count.
   *
   * @access public
   * @return void
   */
  function rpcGetPackageCount() {
    $this->loadPackages();
    $this->sessionParams['modified_tables'] = array();
    $this->sessionParams['modified_packages'] = array();
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    if (isset($this->packages) && is_array($this->packages)) {
      $count = count($this->packages);
    } else {
      $count = 0;
    }
    header('Content-type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>'.LF;
    echo '<response>'.LF;
    echo '<method>rpcCallbackDatabaseStructureInit</method>'.LF;
    echo '<param name="packageCount" value="'.(int)$count.'" />'.LF;
    echo '</response>'.LF;
    exit;
  }

  /**
   * Get XML for administration page
   *
   * @access public
   */
  function getXML() {
    $this->layout->parameters()->set('COLUMNWIDTH_LEFT', '200px');
    $this->layout->parameters()->set('COLUMNWIDTH_CENTER', '400px');
    $this->layout->parameters()->set('COLUMNWIDTH_RIGHT', '100%');
    $this->layout->addScript('<script type="text/javascript" src="script/xmlrpc.js"></script>');
    $this->layout->addScript('<script type="text/javascript" src="script/modules.js"></script>');
    $this->getButtonsXML();
    $this->getPackageListView();
    $this->getPackageContentListView();
    if (!$this->getPackageDialog()) {
      if (isset($this->params['cmd'])) {
        switch ($this->params['cmd']) {
        case 'pkg_enable' :
          $this->getModuleStatusDialogXML(TRUE);
          break;
        case 'pkg_disable' :
          $this->getModuleStatusDialogXML(FALSE);
          break;
        case 'module_enable' :
          if (isset($this->module) && is_array($this->module)) {
            $this->getModuleStatusDialogXML(TRUE, $this->module['module_guid']);
          }
          break;
        case 'module_disable' :
          if (isset($this->module) && is_array($this->module)) {
            $this->getModuleStatusDialogXML(FALSE, $this->module['module_guid']);
          }
          break;
        case 'name_reset' :
          $this->getModuleResetDialogXML();
          break;
        case 'import_table_data' :
          $this->getTableImportDialogXML();
          break;
        }
      }
    }
    if (isset($this->module)) {
      $this->getModuleToolbar();
      if (!isset($this->params['module_mode'])) {
        $this->params['module_mode'] = 0;
      }
      switch ($this->params['module_mode']) {
      case 2 :
        $this->getPluginOptionsDialog();
        break;
      case 1 :
        $this->getModuleDialog();
        break;
      default :
        $this->getPackageInfos();
        break;
      }
    } else {
      $this->getPackageInfos();
    }
    $this->getIndexDialog();
    $this->getFieldDialog();
    $this->getTableDialog();
  }

  /**
   * Get the addtional toolbar for a module.
   *
   * @access public
   * @return void
   */
  function getModuleToolbar() {
    $toolbar = new base_btnbuilder;
    $toolbar->images = $this->papaya()->images;
    $toolbar->addButton(
      'Information',
      $this->getLink(array('module_mode' => 0)),
      'items-page',
      '',
      empty($this->params['module_mode']) || $this->params['module_mode'] == 0
    );
    $toolbar->addButton(
      'Properties',
      $this->getLink(array('module_mode' => 1)),
      'categories-properties',
      '',
      isset($this->params['module_mode']) && $this->params['module_mode'] == 1
    );
    $toolbar->addButton(
      'Options',
      $this->getLink(array('module_mode' => 2)),
      'categories-content',
      '',
      isset($this->params['module_mode']) && $this->params['module_mode'] == 2
    );

    if ($str = $toolbar->getXML()) {
      $this->layout->addRight('<toolbar ident="edit">'.$str.'</toolbar>');
    }
  }

  /**
   * Get buttons XML for menu bar
   *
   * @access public
   */
  function getButtonsXML() {
    $menubar = new base_btnbuilder;
    $menubar->images = $this->papaya()->images;
    $menubar->addButton(
      'Search modules',
      $this->getLink(array('cmd' => 'scan')),
      'actions-disk-scan'
    );
    $menubar->addButton(
      'Check database',
      'javascript:checkDatabaseStructure();',
      'actions-database-scan'
    );
    if (isset($this->params['pkg_id']) && $this->params['pkg_id'] > 0) {
      $menubar->addButton(
        'Check package tables',
        'javascript:checkPackageStructure('.(int)$this->params['pkg_id'].');',
        'actions-database-scan'
      );
    }
    $menubar->addButton(
      'Reset module names',
      $this->getLink(array('cmd' => 'name_reset', 'pkg_id' => 0, 'table' => '')),
      'actions-database-refresh'
    );
    if (isset($this->modules) && is_array($this->modules)) {
      $menubar->addSeparator();
      $hasEnabledModule = FALSE;
      $hasDisabledModule = FALSE;
      foreach ($this->modules as $module) {
        if (isset($module['active']) && $module['active']) {
          $hasEnabledModule = TRUE;
        } else {
          $hasDisabledModule = TRUE;
        }
        if ($hasEnabledModule && $hasDisabledModule) {
          break;
        }
      }
      if ($hasDisabledModule) {
        $menubar->addButton(
          'Enable package',
          $this->getLink(array('cmd' => 'pkg_enable', 'pkg_id' => (int)$this->params['pkg_id'])),
          'items.package',
          'Enable all package modules'
        );
      }
      if ($hasEnabledModule) {
        $menubar->addButton(
          'Disable package',
          $this->getLink(array('cmd' => 'pkg_disable', 'pkg_id' => (int)$this->params['pkg_id'])),
          'items.package.disabled',
          'Disable all package modules'
        );
      }
    }
    $menubar->addSeparator();
    if (
      isset($this->_tableStructure['changes']) &&
      (
        count($this->_tableStructure['changes']['fields']) > 0 ||
        count($this->_tableStructure['changes']['indizes']) > 0
      )
    ) {
      $menubar->addButton(
        'Synchronize',
        $this->getLink(array('cmd' => 'sync')),
        'actions-database-refresh',
        'Synchronize table structure'
      );
    }
    if (isset($this->params['table'])) {
      $menubar->addButton(
        'Export table',
        $this->getLink(array('cmd' => 'export_table')),
        'actions-save-to-disk',
        'Export table structure as xml'
      );
      $menubar->addButton(
        'Import table data',
        $this->getLink(array('cmd' => 'import_table_data')),
        'actions-upload',
        'Import table data from file'
      );
      $menubar->addButton(
        'Export table data',
        $this->getLink(array('cmd' => 'export_table_data')),
        'actions-save-to-disk',
        'Export table data as CSV'
      );
    }

    if ($str = $menubar->getXML()) {
      $this->layout->addMenu('<menu ident="edit">'.$str.'</menu>');
    }
  }

  /**
   * Update data module table
   *
   * @access public
   */
  function updateModuleTable() {
    if (isset($this->modules)) {
      $this->addMsg(MSG_INFO, $this->_gt('Updating modules table.'));
      $this->loadPackages();
      $packages = array();
      if (isset($this->packages) && is_array($this->packages)) {
        foreach ($this->packages as $pkgIdx => $package) {
          if (isset($package['modulegroup_path'])) {
            $packages[$package['modulegroup_path']] = $pkgIdx;
          }
        }
      }
      $modUpdate = array();
      $modDelete = array();
      $sql = "SELECT module_guid, module_type, module_useoutputfilter, module_title,
                     module_title_org, module_description, module_class,
                     module_file, module_path, module_glyph, modulegroup_id
                FROM %s
               ORDER BY module_guid";
      $tableModules = $this->databaseGetTableName(\Papaya\Content\Tables::MODULES);
      if ($res = $this->databaseQueryFmt($sql, $tableModules)) {
        $modInDatabase = array();
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if (isset($this->modules[$row['module_guid']])) {
            $module = $this->modules[$row['module_guid']];
            if (
              $module['type'] !== $row['module_type'] ||
              ($module['outputfilter'] == 'no') == $row['module_useoutputfilter'] ||
              $module['name'] !== $row['module_title_org'] ||
              $module['description'] !== $row['module_description'] ||
              $module['class'] !== $row['module_class'] ||
              $module['file'] !== $row['module_file'] ||
              $module['path'] !== $row['module_path'] ||
              $module['glyph'] !== $row['module_glyph'] ||
              $packages[$module['path']] !== $row['modulegroup_id']
            ) {
              $modUpdate[] = $row['module_guid'];
            } else {
              $modInDatabase[$row['module_guid']] = TRUE;
            }
          } else {
            $modDelete[] = $row['module_guid'];
          }
        }
        $res->free();
        if (is_array($modDelete) && count($modDelete) > 0) {
          $this->databaseDeleteRecord($tableModules, 'module_guid', $modDelete);
          $this->addMsg(
            MSG_INFO,
            sprintf($this->_gt('%s modules deleted.'), count($modDelete))
          );
        }
        if (is_array($modUpdate) && count($modUpdate) > 0) {
          foreach ($modUpdate as $guid) {
            $module = $this->modules[$guid];
            $modInDatabase[$guid] = TRUE;
            $data = array(
              'module_type' => $module['type'],
              'module_useoutputfilter' => ($module['outputfilter'] == 'no') ? 0 : 1,
              'module_title_org' => $module['name'],
              'module_description' => $module['description'],
              'module_class' => $module['class'],
              'module_file' => $module['file'],
              'module_path' => $module['path'],
              'module_glyph' => $module['glyph'],
              'modulegroup_id' => $packages[$module['path']]
            );
            $this->databaseUpdateRecord(
              $tableModules, $data, 'module_guid', $guid
            );
          }
          $this->addMsg(
            MSG_INFO,
            sprintf($this->_gt('%s modules updated.'), count($modUpdate))
          );
        }
        // insert new
        if (is_array($this->modules)) {
          $countNew = 0;
          foreach ($this->modules as $guid => $module) {
            if (!isset($modInDatabase[$guid])) {
              $data = array(
                'module_guid' => $module['guid'],
                'module_type' => $module['type'],
                'module_useoutputfilter' => ($module['outputfilter'] == 'no') ? 0 : 1,
                'module_title' => $module['name'],
                'module_title_org' => $module['name'],
                'module_description' => $module['description'],
                'module_class' => $module['class'],
                'module_file' => $module['file'],
                'module_path' => $module['path'],
                'module_glyph' => $module['glyph'],
                'module_active' => 1,
                'modulegroup_id' => $packages[$module['path']]
              );
              $this->databaseInsertRecord($tableModules, NULL, $data);
              $countNew++;
            }
          }
          if ($countNew > 0) {
            $this->addMsg(
              MSG_INFO,
              sprintf($this->_gt('%s modules added.'), $countNew)
            );
          }
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Update data package table
   *
   * @access public
   */
  function updatePackageTable() {
    if (isset($this->packages) && is_array($this->packages)) {
      if (
        FALSE !== $this->databaseEmptyTable(
          $this->databaseGetTableName(\Papaya\Content\Tables::MODULE_GROUPS)
        )
      ) {
        $idx = 1;
        $data = array();
        foreach ($this->packages as $package) {
          if (
            isset($package['tables']) &&
            is_array($package['tables']) &&
            count($package['tables'])
          ) {
            $tables = implode(',', $package['tables']);
          } else {
            $tables = '';
          }
          $data[] = array(
            'modulegroup_id' => $idx++,
            'modulegroup_title' => $package['modulegroup_title'],
            'modulegroup_path' => $package['modulegroup_path'],
            'modulegroup_prefix' => $package['modulegroup_prefix'],
            'modulegroup_classes' => $package['modulegroup_classes'],
            'modulegroup_tables' => $tables
          );
        }
        if (isset($data) && is_array($data) && count($data) > 0) {
          $this->databaseInsertRecords(
            $this->databaseGetTableName(\Papaya\Content\Tables::MODULE_GROUPS), $data
          );
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Set active/inactive status for all module of a package.
   *
   * @param integer $pkgId
   * @param boolean $activate
   * @access public
   * @return boolean
   */
  function updatePackageStatus($pkgId, $activate) {
    return (
      FALSE !== $this->databaseUpdateRecord(
        $this->databaseGetTableName(\Papaya\Content\Tables::MODULES),
        array('module_active' => ($activate) ? 1 : 0),
        array('modulegroup_id' => $pkgId)
      )
    );
  }

  /**
   * Set active/inactive status for a module.
   *
   * @param string $moduleId
   * @param boolean $activate
   * @access public
   * @return boolean
   */
  function updateModuleStatus($moduleId, $activate) {
    return (
      FALSE !== $this->databaseUpdateRecord(
        $this->databaseGetTableName(\Papaya\Content\Tables::MODULES),
        array('module_active' => ($activate) ? 1 : 0),
        array('module_guid' => $moduleId)
      )
    );
  }

  /**
   * Search modules
   */
  function searchModules() {
    $this->packages = array();
    $this->modules = array();
    $paths = array(
      \Papaya\Utility\File\Path::cleanup(\Papaya\Utility\File\Path::getDocumentRoot().\Papaya\Utility\File\Path::getVendorPath()),
      \Papaya\Utility\File\Path::cleanup(\Papaya\Utility\File\Path::getDocumentRoot().\Papaya\Utility\File\Path::getSourcePath())
    );
    foreach ($paths as $path) {
      if (file_exists($path) && is_dir($path) && is_readable($path)) {
        $this->addMsg(
          \Papaya\Message::SEVERITY_INFO,
          sprintf(
            $this->_gt('Scanning %s'), $path
          )
        );
        $this->scanDirectory($path);
      } else {
        $this->addMsg(
          \Papaya\Message::SEVERITY_INFO,
          sprintf(
            $this->_gt('Not readable: %s'), $path
          )
        );
      }
    }
    uasort($this->packages, array($this, 'comparePackageArrays'));
    uasort($this->modules, array($this, 'compareModuleArrays'));
    if (empty($this->modules)) {
      $this->addMsg(MSG_ERROR, $this->_gt('No modules found.'));
    }
  }

  /**
   * Check trailing slash
   *
   * @param string $path
   * @access public
   * @return string path ends with /
   */
  function checkTrailingSlash($path) {
    if (substr($path, -1) != '/') {
      return $path.'/';
    }
    return $path;
  }

  /**
   * Scan directory
   *
   * @param string $path
   * @access public
   */
  function scanDirectory($path) {
    $path = $this->checkTrailingSlash($path);
    $packageFileName = $path.$this->modulesFileName;
    if (file_exists($packageFileName)) {
      $relativePath = $this->stripModulePath($path);
      $this->packages[$relativePath] = $this->loadPackageFile($packageFileName);
      $this->packages[$relativePath]['modulegroup_path'] = $relativePath;
    }
    if ($dh = opendir($path)) {
      while ($file = readdir($dh)) {
        if (substr($file, 0, 1) != '.') {
          if (is_dir($path.$file) && $file != 'CVS') {
            $this->scanDirectory($path.$file);
          }
        }
      }
      closedir($dh);
    }
  }

  /**
   * Load a package with data from a directory (instead of the DB).
   *
   * This mostly equivalent to loadPackages() and loadPackageData().
   *
   * @see papaya_modulemanager::loadPackages()
   * @see papaya_modulemanager::loadPackageData()
   * @param string $path
   * @return void
   */
  public function loadPackageWithDataFromDirectory($path) {
    $this->packages = array();
    $this->modules = array();
    $i = 1;
    $packageFileName = $path.$this->modulesFileName;
    $this->loadTables = TRUE;
    if ($info = $this->loadPackageFile($packageFileName)) {
      $this->packages[$i]['modulegroup_id'] = $i;
      $this->packages[$i]['modulegroup_path'] = $this->stripModulePath($path);
      $this->packages[$i]['infos'] = $info;
      $this->packages[$i]['modulegroup_title'] = $info['modulegroup_title'];
      $this->packages[$i]['counts'][TRUE] = count($info['modules']);
      foreach ($info['modules'] as $guid) {
        $this->modules[$guid]['error'] = FALSE;
        $this->modules[$guid]['active'] = TRUE;
      }
      $this->loadTableDBData($info['tables']);
    }
  }

  /**
   * Load package file
   *
   * @param string $packageFileName
   * @access public
   * @return array|FALSE array $packageData or boolean FALSE
   */
  function loadPackageFile($packageFileName) {
    $dom = new \Papaya\XML\Document();
    if (file_exists($packageFileName) && $dom->load($packageFileName)) {
      $packageData = array(
        'modulegroup_title' => '',
        'description' => '',
        'modulegroup_authors' => array(),
        'modulegroup_version' => '',
        'modulegroup_url' => '',
        'modulegroup_prefix' => '',
        'modulegroup_classes' => '',
        'modules' => array()
      );
      if ($this->loadTables) {
        $packageData['tables'] = array();
      }
      $xpath = $dom->xpath();
      $packageData['modulegroup_title'] = $xpath->evaluate('string(/*/name)');
      $packageData['description'] = $xpath->evaluate('string(/*/description)');
      $packageData['modulegroup_version'] = $xpath->evaluate('string(/*/version)');
      $packageData['modulegroup_url'] = $xpath->evaluate('string(/*/url)');
      $packageData['modulegroup_prefix'] = $xpath->evaluate('string(/*/prefix)');
      $packageData['modulegroup_classes'] = $xpath->evaluate('string(/*/classes/@file)');
      foreach ($xpath->evaluate('/*/author') as $node) {
        $packageData['modulegroup_authors'][] = $node->nodeValue;
      }
      /** @var \Papaya\XML\Element $moduleNode */
      foreach ($xpath->evaluate('/*/modules/module') as $moduleNode) {
        $moduleData = array(
          'type' => '',
          'guid' => '',
          'path' => '',
          'class' => '',
          'description' => '',
          'outputfilter' => '',
          'glyph' => ''
        );
        $valid = TRUE;
        foreach ($this->modulesParamNames as $moduleParam) {
          if ($moduleNode->hasAttribute($moduleParam)) {
            $moduleData[$moduleParam] =
              $moduleNode->getAttribute($moduleParam);
          } else {
            $valid = FALSE;
          }
        }
        if ($valid && in_array($moduleData['type'], $this->moduleTypes)) {
          $moduleData['file'] = $moduleNode->hasAttribute('file') ?
            $moduleNode->getAttribute('file') : '';
          $moduleData['glyph'] = $moduleNode->hasAttribute('glyph') ?
            $moduleNode->getAttribute('glyph') : '';
          $moduleData['outputfilter'] = $moduleNode->hasAttribute('outputfilter')
            ? $moduleNode->getAttribute('outputfilter') : 'yes';
          $moduleData['description'] = $moduleNode->nodeValue;
          $moduleData['path'] = $this->stripModulePath(dirname($packageFileName).'/');
          if (isset($this->modules[$moduleData['guid']])) {
            $this->addMsg(
              MSG_WARNING,
              sprintf(
                $this->_gt(
                  'Duplicate module guid "%s" for "%s", already used for "%s".'
                ),
                $moduleData['guid'],
                $moduleData['class'],
                $this->modules[$moduleData['guid']]['class']
              )
            );
          } else {
            $this->modules[$moduleData['guid']] = $moduleData;
            $packageData['modules'][] = $moduleData['guid'];
          }
        }
      }
      if (isset($packageData['modules']) && is_array($packageData['modules'])) {
        $packageData['modules'] = array_unique($packageData['modules']);
      }
      if ($this->loadTables) {
        /** @var DOMElement $tableNode */
        foreach ($xpath->evaluate('/*/tables/table[@name]') as $tableNode) {
          $tableName = strtolower($tableNode->getAttribute('name'));
          $packageData['tables'][] = $tableName;
          $packageData['table_properties'][$tableName]['description'] =
            $tableNode->nodeValue;
          $packageData['table_properties'][$tableName]['use_prefix'] =
            $tableNode->getAttribute('prefix') == 'no' ? FALSE : TRUE;
        }
        if (isset($packageData['tables']) && is_array($packageData['tables'])) {
          sort($packageData['tables']);
        }
      }
      return $packageData;
    } else {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('Invalid module file "%s".'),
          $packageFileName
        )
      );
    }
    return FALSE;
  }

  /**
   * compare module arrays
   *
   * @param array $a
   * @param array $b
   * @access public
   * @return integer
   */
  function compareModuleArrays($a, $b) {
    if ($a['name'] != $b['name']) {
      return ($a['name'] > $b['name']) ? 1 : -1;
    } elseif ($a['class'] != $b['class']) {
      return ($a['class'] > $b['class']) ? 1 : -1;
    } else {
      return ($a['guid'] > $b['guid']) ? 1 : -1;
    }
  }

  /**
   * Compare package arrays
   *
   * @param array $a
   * @param array $b
   * @access public
   * @return integer
   */
  function comparePackageArrays($a, $b) {
    if ($a['modulegroup_path'] != $b['modulegroup_path']) {
      return ($a['modulegroup_path'] > $b['modulegroup_path']) ? 1 : -1;
    } elseif ($a['modulegroup_title'] != $b['modulegroup_title']) {
      return ($a['modulegroup_title'] > $b['modulegroup_title']) ? 1 : -1;
    } elseif ($a['modulegroup_description'] != $b['modulegroup_description']) {
      return ($a['modulegroup_description'] > $b['modulegroup_description']) ? 1 : -1;
    }
    return 0;

  }

  /**
   * Load packages
   *
   * @access public
   */
  function loadPackages() {
    unset($this->packages);
    $sql = "SELECT modulegroup_id, modulegroup_title, modulegroup_path,
                   modulegroup_prefix, modulegroup_classes
              FROM %s
             ORDER BY modulegroup_title";
    $groupTable = $this->databaseGetTableName(\Papaya\Content\Tables::MODULE_GROUPS);
    if ($res = $this->databaseQueryFmt($sql, $groupTable)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->packages[(int)$row['modulegroup_id']] = $row;
      }
      $res->free();
      $this->loadPackageCounts();
    }
  }

  /**
   * Load and compile module counts for all packages
   * @return void
   */
  function loadPackageCounts() {
    $sql = "SELECT modulegroup_id, module_active, count(module_guid) as counted
              FROM %s
             GROUP BY modulegroup_id, module_active";
    $tableModules = $this->databaseGetTableName(\Papaya\Content\Tables::MODULES);
    if ($res = $this->databaseQueryFmt($sql, $tableModules)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $active = (bool)$row['module_active'];
        $this->packages[(int)$row['modulegroup_id']]['counts'][$active] = (int)$row['counted'];
      }
      $res->free();
    }
  }

  /**
   * Load package data
   *
   * @param integer $pkgId
   * @access public
   */
  function loadPackageData($pkgId) {
    unset($this->modules);
    if (isset($this->packages[$pkgId])) {
      $path = $this->prependModulePath($this->packages[$pkgId]['modulegroup_path']);
      $packageFileName = $path.$this->modulesFileName;
      $this->loadTables = TRUE;
      $package = $this->loadPackageFile($packageFileName);
      $this->loadModuleDBData();
      $this->loadTableDBData($package['tables']);
      $this->packages[$pkgId]['infos'] = $package;
    }
  }

  /**
   * Load module database data
   *
   * @access public
   */
  function loadModuleDBData() {
    if (isset($this->modules) && is_array($this->modules)) {
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('module_guid', array_keys($this->modules))
      );
      $sql = "SELECT module_guid, module_type, module_useoutputfilter, module_title,
                     module_title_org, module_description, module_class,
                     module_file, module_path, module_glyph,
                     module_active
                FROM %s
               WHERE $filter
               ORDER BY module_guid";
      $tableModules = $this->databaseGetTableName(\Papaya\Content\Tables::MODULES);
      if ($res = $this->databaseQueryFmt($sql, $tableModules)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if (isset($this->modules[$row['module_guid']])) {
            $module = $this->modules[$row['module_guid']];
            if (
              $module['type'] !== $row['module_type'] ||
              $module['name'] !== $row['module_title_org'] ||
              $module['description'] !== $row['module_description'] ||
              $module['class'] !== $row['module_class'] ||
              $module['file'] !== $row['module_file'] ||
              $module['path'] !== $row['module_path'] ||
              $module['glyph'] !== $row['module_glyph']
            ) {
              $this->modules[$row['module_guid']]['error'] = TRUE;
            } else {
              $this->modules[$row['module_guid']]['error'] = FALSE;
            }
            $this->modules[$row['module_guid']]['active'] =
              (boolean)$row['module_active'];
          }
        }
        $res->free();
      }
    }
  }

  /**
   * Load module list
   *
   * @param string $moduleType
   * @access public
   * @return array $modules
   */
  function loadModulesList($moduleType) {
    $modules = array();
    $sql = "SELECT module_guid, module_type, module_title
              FROM %s
             WHERE module_type = '%s' AND module_active = 1";
    $params = array(
      $this->databaseGetTableName(\Papaya\Content\Tables::MODULES),
      $moduleType
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $modules[] = $row;
      }
      $res->free();
    }
    return $modules;
  }

  /**
   * Load module
   *
   * @param integer $moduleId
   * @access public
   * @return boolean
   */
  function loadModule($moduleId) {
    if ($moduleId != '') {
      unset($this->module);
      $sql = "SELECT module_guid, module_type, module_useoutputfilter,
                    module_title, module_title_org, module_description, module_class,
                    module_file, module_path, module_glyph,
                    module_active
                FROM %s
              WHERE module_guid = '%s'";
      $tableModules = $this->databaseGetTableName(\Papaya\Content\Tables::MODULES);
      if ($res = $this->databaseQueryFmt($sql, array($tableModules, $moduleId))) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->module = $row;
        }
        $res->free();
      }
      return isset($this->module);
    }
    return FALSE;
  }

  /**
   * Load table
   *
   * @param integer $pkgId
   * @param string $table
   * @access public
   */
  function loadTable($pkgId, $table) {
    if (
      isset($table) && trim($table) != '' &&
      isset($this->packages[$pkgId]) &&
      is_array($this->packages[$pkgId])
    ) {
      $package = $this->packages[$pkgId];
      $path = $this->prependModulePath($package['modulegroup_path']);
      $tableFile = $this->getTableDataPath($package['modulegroup_path']).'table_'.$table.'.xml';
      $this->_tableStructure = $this->loadTableStructure($tableFile, $pkgId);
      if (
        isset($this->_tableStructure['name']) &&
        isset($this->tables[$this->_tableStructure['name']]) &&
        $this->tables[$this->_tableStructure['name']] === TRUE
      ) {
        if (
          isset($this->_tableStructure['changes']) &&
          (
            count($this->_tableStructure['changes']['fields']) > 0 ||
            count($this->_tableStructure['changes']['indizes']) > 0
          )
        ) {
          $this->setTableStatus($path, $table, TRUE);
        } else {
          $this->setTableStatus($path, $table, FALSE);
        }
      } else {
        $this->setTableStatus($path, $table, FALSE, TRUE);
      }
    }
  }

  /**
   * Load table structure
   *
   * @param string $xmlFileName
   * @return array|FALSE
   * @throws \Papaya\Database\Exception\ConnectionFailed
   */
  public function loadTableStructure($xmlFileName) {
    if (file_exists($xmlFileName)) {
      try {
        $expectedStructure = \Papaya\Database\Schema\Structure\TableStructure::createFromXML(
          \Papaya\XML\Document::createFromXML(file_get_contents($xmlFileName))
        );
      } catch (UnexpectedValueException $exception) {
        return FALSE;
      }

      $requiredChanges = [
        'fields' => [],
        'indizes' => []
      ];
      try {
        $currentStructure = $this->getSchema()->describeTable(
          $expectedStructure->name,
          $expectedStructure->name ? $this->papaya()->options['PAPAYA_DB_TABLEPREFIX'] : ''
        );

        $fieldNames = array_unique(
          array_merge($expectedStructure->fields->keys(), $currentStructure->fields->keys())
        );
        /** @var string $fieldName */
        foreach ($fieldNames as $fieldName) {
          /** @var \Papaya\Database\Schema\Structure\FieldStructure $expectedField */
          $expectedField = isset($expectedStructure->fields[$fieldName]) ? $expectedStructure->fields[$fieldName]
            : NULL;
          /** @var \Papaya\Database\Schema\Structure\FieldStructure $currentField */
          $currentField = isset($currentStructure->fields[$fieldName]) ? $currentStructure->fields[$fieldName] : NULL;
          if (isset($expectedField) && !isset($currentField)) {
            $requiredChanges['fields'][$expectedField->name] = self::ACTION_FIELD_ADD;
          } elseif (!isset($expectedField) && isset($currentField)) {
            $requiredChanges['fields'][$currentField->name] = self::ACTION_FIELD_REMOVE;
            // remove
          } elseif ($this->getSchema()->isFieldDifferent($expectedField, $currentField)) {
            $requiredChanges['fields'][$expectedField->name] = self::ACTION_FIELD_CHANGE;
          }
        }

        $indexNames = array_unique(
          array_merge($expectedStructure->indizes->keys(), $currentStructure->indizes->keys())
        );
        /** @var string $indexName */
        foreach ($indexNames as $indexName) {
          /** @var \Papaya\Database\Schema\Structure\IndexStructure $expectedIndex */
          $expectedIndex = isset($expectedStructure->indizes[$indexName]) ? $expectedStructure->indizes[$indexName]
            : NULL;
          /** @var \Papaya\Database\Schema\Structure\IndexStructure $currentIndex */
          $currentIndex = isset($currentStructure->indizes[$indexName]) ? $currentStructure->indizes[$indexName] : NULL;
          if (isset($expectedIndex) && !isset($currentIndex)) {
            $requiredChanges['indizes'][$expectedIndex->name] = self::ACTION_INDEX_ADD;
          } elseif (!isset($expectedIndex) && isset($currentIndex)) {
            $requiredChanges['indizes'][$currentIndex->name] = self::ACTION_INDEX_REMOVE;
            // remove
          } elseif ($this->getSchema()->isIndexDifferent($expectedIndex, $currentIndex)) {
            $requiredChanges['indizes'][$expectedIndex->name] = self::ACTION_INDEX_CHANGE;
          }
        }
      } catch (\Papaya\Database\Exception\QueryFailed $exception) {
        $currentStructure = NULL;
      }
      return [
        'name' => $expectedStructure->name,
        'expected' => $expectedStructure,
        'current' => $currentStructure,
        'changes' => $requiredChanges
      ];
    }
    return FALSE;
  }

  /**
   * Synchronize a table structure
   *
   * @param array $tableStruct Table stucture
   * @access public
   * @return boolean;
   */
  function syncTableStructure($tableStruct) {
    $tableFullName = $this->getTableFullName($tableStruct['expected']->name);
    foreach ($tableStruct['changes']['fields'] as $fieldName => $action) {
      switch($action) {
      case self::ACTION_FIELD_ADD:
        if ($this->getSchema()->addField($tableFullName, $tableStruct['expected']->fields[$fieldName])) {
          $this->addMsg(
            MSG_INFO, $this->_gt('Field added.').' '.$fieldName
          );
        } else {
          $this->addMsg(
            MSG_INFO, $this->_gt('Cannot add field.').' '.$fieldName
          );
          return FALSE;
        }
        break;
      case self::ACTION_FIELD_REMOVE:
        if ($this->getSchema()->dropField($tableFullName, $fieldName)) {
          $this->addMsg(
            MSG_INFO, $this->_gt('Field deleted.').' '.$fieldName
          );
        } else {
          $this->addMsg(
            MSG_INFO, $this->_gt('Cannot delete field.').' '.$fieldName
          );
          return FALSE;
        }
        break;
      case self::ACTION_FIELD_CHANGE:
        if ($this->getSchema()->changeField($tableFullName, $tableStruct['expected']->fields[$fieldName])) {
          $this->addMsg(
            MSG_INFO, $this->_gt('Field modified.').' '.$fieldName
          );
        } else {
          $this->addMsg(
            MSG_INFO, $this->_gt('Cannot change field.').' '.$fieldName
          );
          return FALSE;
        }
        break;
      }
    }
    foreach ($tableStruct['changes']['indizes'] as $keyName => $action) {
      switch ($action) {
      case self::ACTION_INDEX_ADD:
        if ($this->getSchema()->addIndex($tableFullName,  $tableStruct['expected']->indizes[$keyName])) {
          $this->addMsg(
            MSG_INFO, $this->_gt('Index added.').' '.$keyName
          );
        } else {
          $this->addMsg(
            MSG_INFO, $this->_gt('Can not add index.').' '.$keyName
          );
          return FALSE;
        }
        break;
      case self::ACTION_INDEX_REMOVE:
        if ($this->getSchema()->dropIndex($tableFullName, $keyName)) {
          $this->addMsg(
            MSG_INFO, $this->_gt('Index deleted.').' '.$keyName
          );
        } else {
          $this->addMsg(
            MSG_INFO, $this->_gt('Can not delete index.').' '.$keyName
          );
          return FALSE;
        }
        break;
      case self::ACTION_INDEX_CHANGE:
        if ($this->getSchema()->changeIndex($tableFullName,  $tableStruct['expected']->indizes[$keyName])) {
          $this->addMsg(
            MSG_INFO, $this->_gt('Index modified.').' '.$keyName
          );
        } else {
          $this->addMsg(
            MSG_INFO, $this->_gt('Can not change index.').' '.$keyName
          );
          return FALSE;
        }
        break;
      }
    }
    return TRUE;
  }

  /**
   * Save module
   *
   * @access public
   * @return mixed boolean FALSE or number of affected rows or database result object
   */
  function saveModule() {
    $data = array(
      'module_active' => (int)$this->params['module_active'],
      'module_title' => $this->params['module_title']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->databaseGetTableName(\Papaya\Content\Tables::MODULES),
      $data,
      'module_guid',
      $this->params['module_id']
    );
  }

  /**
   * Load table database data
   *
   * @param $tables
   * @access public
   * @throws \Papaya\Database\Exception\ConnectionFailed
   */
  function loadTableDBData($tables) {
    unset($this->tables);
    if (isset($tables) && is_array($tables)) {
      $this->tables = array_flip($tables);
      foreach ($this->tables as $tableName => $inDatabase) {
        $this->tables[$tableName] = FALSE;
      }
      $dbTables = $this->getSchema()->getTables();
      if (is_array($dbTables)) {
        foreach ($dbTables as $tableName) {
          if (strpos($tableName, PAPAYA_DB_TABLEPREFIX.'_') !== FALSE) {
            $tableName = substr($tableName, strlen(PAPAYA_DB_TABLEPREFIX) + 1);
          }
          if (isset($this->tables[$tableName])) {
            $this->tables[$tableName] = TRUE;
          }
        }
      }
    }
  }

  /**
   * @return \Papaya\Database\Schema
   * @throws \Papaya\Database\Exception\ConnectionFailed
   */
  private function getSchema() {
    return $this->getDatabaseAccess()->schema();
  }

  /**
   * Get package list view
   *
   * @access public
   */
  function getPackageListView() {
    if (isset($this->packages) && is_array($this->packages)) {
      $listview = new \Papaya\UI\ListView();
      $listview->caption = new \Papaya\UI\Text\Translated('Packages');
      $listview->parameterGroup($this->paramName);
      foreach ($this->packages as $package) {

        $activeModules = empty($package['counts'][TRUE]) ? 0 : $package['counts'][TRUE];
        $inactiveModules = empty($package['counts'][FALSE]) ? 0 : $package['counts'][FALSE];
        $summaryModules = $activeModules + $inactiveModules;
        if (
          isset($this->params['pkg_id']) &&
          $package['modulegroup_id'] == $this->params['pkg_id']
        ) {
          $selected = TRUE;
          $itemImage = 'status-package-opened';
        } elseif ($activeModules > 0) {
          $selected = FALSE;
          $itemImage = 'items.package';
        } else {
          $selected = FALSE;
          $itemImage = 'items.package.disabled';
        }
        if ($activeModules == $summaryModules) {
          $moduleStatus = $summaryModules;
        } else {
          $moduleStatus = $activeModules.'/'.$summaryModules;
        }
        $packageStatus = $this->getPackageStatus(
          $this->prependModulePath($package['modulegroup_path'])
        );
        switch ($packageStatus) {
        case PAPAYA_MODULE_TABLE_UNKNOWN :
          $statusImage = FALSE;
          $statusText = '';
          break;
        case PAPAYA_MODULE_TABLE_OK :
          $statusImage = FALSE;
          $statusText = '';
          break;
        case PAPAYA_MODULE_TABLE_MISSING :
          $statusImage = 'status-sign-warning';
          $statusText = new \Papaya\UI\Text\Translated('Missing tables.');
          break;
        case PAPAYA_MODULE_TABLE_ERROR :
          $statusImage = 'status-sign-problem';
          $statusText = new \Papaya\UI\Text\Translated('Invalid table structures.');
          break;
        default :
          $statusImage = FALSE;
          $statusText = '';
          break;
        }

        $item = new \Papaya\UI\ListView\Item(
          $itemImage,
          $package['modulegroup_title'].' ('.$moduleStatus.')',
          array(
            'pkg_id' => (int)$package['modulegroup_id'],
            'module_id' => 0,
            'table' => ''
          ),
          $selected
        );
        $item->subitems[] = new \Papaya\UI\ListView\SubItem\Image($statusImage, $statusText);
        $listview->items[] = $item;
      }
      $this->layout->addLeft($listview->getXML());
    }
  }

  /**
   * Get xml of package infos for the administration interface.
   *
   * @access public
   * @return void
   */
  function getPackageInfos() {
    if (isset($this->params['module_id']) && isset($this->modules[$this->params['module_id']])) {
      $module = $this->modules[$this->params['module_id']];
      $result = '<sheet>';
      $result .= '<header>';
      $result .= '<lines>';
      if (isset($this->module) && isset($this->module['module_title'])) {
        $result .= sprintf(
          '<line class="headertitle">%s</line>',
          papaya_strings::escapeHTMLChars($this->module['module_title'])
        );
        if ($module['name'] != $this->module['module_title']) {
          $result .= sprintf(
            '<line class="headersubtitle">%s: %s</line>',
            papaya_strings::escapeHTMLChars($this->_gt('Original title')),
            papaya_strings::escapeHTMLChars($module['name'])
          );
        }
        if (in_array($this->module['module_type'], array('page', 'box'))) {
          $outputFilter = $this->module['module_useoutputfilter'] ? 'Yes' : 'No';
          $result .= sprintf(
            '<line class="headersubtitle">%s: %s</line>',
            papaya_strings::escapeHTMLChars($this->_gt('Output filter')),
            papaya_strings::escapeHTMLChars($this->_gt($outputFilter))
          );
        }
        $result .= sprintf(
          '<line class="headersubtitle">%s: %s</line>',
          papaya_strings::escapeHTMLChars($this->_gt('Class')),
          papaya_strings::escapeHTMLChars($this->module['module_class'])
        );
        $fileName = str_replace(
          '\\',
          '/',
          $this->papaya()->plugins->getFileName($this->module['module_guid'])
        );
        $root = \Papaya\Utility\File\Path::getDocumentRoot();
        if (0 === strpos($fileName, $root)) {
          $fileName = substr($fileName, strlen($root));
        } elseif ($position = strpos($fileName, '/vendor/')) {
          $fileName = '../'.substr($fileName, $position);
        }
        $result .= sprintf(
          '<line class="headersubtitle">%s: %s</line>',
          papaya_strings::escapeHTMLChars($this->_gt('File')),
          papaya_strings::escapeHTMLChars($fileName)
        );
      } else {
        $result .= sprintf(
          '<line class="headertitle">%s</line>',
          papaya_strings::escapeHTMLChars($module['name'])
        );
      }
      $result .= '</lines>';
      $result .= '</header>';
      $result .= '<text><div style="padding: 10px;"><p>';
      $result .= papaya_strings::escapeHTMLChars($module['description']);
      $result .= '</p></div></text>';
      $result .= '</sheet>';
      $this->layout->addRight($result);
      if (isset($this->module) && is_array($this->module)) {
        if ($counts = $this->loadModuleUseCount($this->module['module_guid'])) {
          $result = sprintf(
            '<listview title="%s">',
            papaya_strings::escapeHTMLChars($this->_gt('Used'))
          );
          $result .= '<cols>';
          $result .= '<col>'.
            papaya_strings::escapeHTMLChars($this->_gt('Table')).'</col>';
          $result .= '<col align="center">'.
            papaya_strings::escapeHTMLChars($this->_gt('Count')).'</col>';
          $result .= '</cols>';
          $result .= '<items>';
          foreach ($counts as $table => $count) {
            $result .= sprintf(
              '<listitem title="%s">',
              papaya_strings::escapeHTMLChars($table)
            );
            $result .= '<subitem align="center">'.(int)$count.'</subitem>';
            $result .= '</listitem>';
          }
          $result .= '</items>';
          $result .= '</listview>';
        }
        $this->layout->addRight($result);
      }
    } elseif (isset($this->params['table']) && isset($this->tables[$this->params['table']])) {
      $package = $this->packages[$this->params['pkg_id']];
      if (!empty($package['infos']['table_properties'][$this->params['table']]['description'])) {
        $result = '<sheet>';
        $result .= '<header>';
        $result .= '<lines>';
        $result .= sprintf(
          '<line class="headertitle">%s</line>',
          papaya_strings::escapeHTMLChars(
            $this->getTableFullName($this->params['table'])
          )
        );
        $result .= '</lines>';
        $result .= '</header>';
        $result .= '<text><div style="padding: 10px;"><p>';
        $result .= papaya_strings::escapeHTMLChars(
          $package['infos']['table_properties'][$this->params['table']]['description']
        );
        $result .= '</p></div></text>';
        $result .= '</sheet>';
        $this->layout->addRight($result);
      }
    } elseif (isset($this->params['pkg_id']) && isset($this->packages[$this->params['pkg_id']])) {
      $package = $this->packages[$this->params['pkg_id']];
      $result = '<sheet>';
      $result .= '<header>';
      $result .= '<lines>';
      $result .= sprintf(
        '<line class="headertitle">%s</line>',
        papaya_strings::escapeHTMLChars($package['infos']['modulegroup_title'])
      );
      if (!empty($package['infos']['modulegroup_version'])) {
        $result .= sprintf(
          '<line class="headersubtitle">%s: %s</line>',
          papaya_strings::escapeHTMLChars($this->_gt('Version')),
          papaya_strings::escapeHTMLChars($package['infos']['modulegroup_version'])
        );
      }
      if (count($package['infos']['modulegroup_authors']) > 0) {
        $result .= sprintf(
          '<line class="headersubtitle">%s: %s</line>',
          papaya_strings::escapeHTMLChars($this->_gt('Author(s)')),
          papaya_strings::escapeHTMLChars(implode(', ', $package['infos']['modulegroup_authors']))
        );
      }
      if (!empty($package['infos']['modulegroup_url'])) {
        $result .= sprintf(
          '<line class="headersubtitle">%s: <a href="%2$s" target="_blank">%2$s</a></line>',
          papaya_strings::escapeHTMLChars($this->_gt('URL')),
          papaya_strings::escapeHTMLChars($package['infos']['modulegroup_url'])
        );
      }
      if (!empty($package['infos']['modulegroup_prefix'])) {
        $result .= sprintf(
          '<line class="headersubtitle">%s: %2$s</line>',
          papaya_strings::escapeHTMLChars($this->_gt('Autoloader prefix')),
          papaya_strings::escapeHTMLChars($package['infos']['modulegroup_prefix'])
        );
      }
      if (!empty($package['infos']['modulegroup_classes'])) {
        $result .= sprintf(
          '<line class="headersubtitle">%s: %2$s</line>',
          papaya_strings::escapeHTMLChars($this->_gt('Autoloader classes')),
          papaya_strings::escapeHTMLChars($package['infos']['modulegroup_classes'])
        );
      }
      $result .= '</lines>';
      $result .= '</header>';
      if (!empty($package['infos']['modulegroup_description'])) {
        $result .= '<text><div style="padding: 10px;"><p>';
        $result .= papaya_strings::escapeHTMLChars($package['infos']['modulegroup_description']);
        $result .= '</p></div></text>';
      }
      $result .= '</sheet>';
      $this->layout->addRight($result);
    }
  }

  /**
   * Get XML content listview (modules and tables) for administration interface.
   *
   * @access public
   * @return void
   */
  function getPackageContentListView() {
    if (
      isset($this->packages) &&
      is_array($this->packages) &&
      (
        (
          isset($this->modules) &&
          is_array($this->modules) &&
          count($this->modules) > 0
        ) ||
        (
          isset($this->tables) &&
          is_array($this->tables) &&
          count($this->tables) > 0
        )
      )
    ) {
      $str = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Package content'))
      );
      $str .= '<items>';
      $str .= $this->getModuleListView();
      $str .= $this->getTablesListView();
      $str .= '</items>';
      $str .= '</listview>';
      $this->layout->add($str);
    }
  }

  /**
   * Get module list items
   *
   * @access public
   */
  function getModuleListView() {
    $str = '';
    if (isset($this->modules) && is_array($this->modules) && count($this->modules) > 0) {
      $images = $this->papaya()->images;
      if (
        (!isset($this->sessionParams['showmodules'])) ||
        (
          isset($this->sessionParams['showmodules']) &&
          $this->sessionParams['showmodules']
        )
      ) {
        $showItems = TRUE;
        $nhref = $this->getLink(array('cmd' => 'modules_hide'));
        $node = 'open';
      } else {
        $showItems = FALSE;
        $nhref = $this->getLink(array('cmd' => 'modules_show'));
        $node = 'close';
      }
      $str = sprintf(
        '<listitem title="%s" node="%s" nhref="%s" span="3"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Modules')),
        papaya_strings::escapeHTMLChars($node),
        papaya_strings::escapeHTMLChars($nhref)
      );
      if ($showItems) {
        foreach ($this->modules as $module) {
          if (isset($module) && is_array($module)) {
            if (
              isset($this->params['module_id']) &&
              $module['guid'] == $this->params['module_id']
            ) {
              $selected = ' selected="selected"';
            } else {
              $selected = '';
            }
            if (isset($module['error']) && $module['error']) {
              $glyph = $images['status-dialog-error'];
            } elseif (trim($module['glyph']) != '') {
              $glyph = \Papaya\Administration\UI::EXTENSIONS_IMAGE.'?module='.urlencode($module['guid']);
            } else {
              switch ($module['type']) {
              case 'alias':
                $glyph = $images['items-alias'];
                break;
              case 'page':
                $glyph = $images['items-page'];
                break;
              case 'box':
                $glyph = $images['items-box'];
                break;
              case 'admin':
                $glyph = $images['items-option'];
                break;
              case 'cronjob':
                $glyph = $images['items-cronjob'];
                break;
              case 'time':
                $glyph = $images['items-time'];
                break;
              case 'date':
                $glyph = $images['items-date'];
                break;
              case 'output':
                $glyph = $images['items-filter-export'];
                break;
              case 'import':
                $glyph = $images['items-filter-import'];
                break;
              case 'image':
                $glyph = $images['items-graphic'];
                break;
              case 'parser':
                $glyph = $images['items-plugin'];
                break;
              case 'datafilter':
                $glyph = $images['items-filter-convert'];
                break;
              default:
                $glyph = $images['items-plugin'];
              }
            }

            $str .= sprintf(
              '<listitem href="%s" image="%s" title="%s" indent="1" %s>'.LF,
              papaya_strings::escapeHTMLChars(
                $this->getLink(array('module_id' => $module['guid']))
              ),
              papaya_strings::escapeHTMLChars($glyph),
              papaya_strings::escapeHTMLChars($module['name']),
              $selected
            );
            $str .= '<subitem align="center">'.
              papaya_strings::escapeHTMLChars($module['type']).'</subitem>';
            $str .= '<subitem align="center">';
            if (isset($module['active']) && $module['active']) {
              $href = $this->getLink(
                array('cmd' => 'module_disable', 'module_id' => $module['guid'])
              );
              $activeGlyph = $images['status-node-checked'];
            } else {
              $href = $this->getLink(
                array('cmd' => 'module_enable', 'module_id' => $module['guid'])
              );
              $activeGlyph = $images['status-node-empty'];
            }
            $str .= sprintf(
              '<a href="%s"><glyph src="%s"/></a>',
              papaya_strings::escapeHTMLChars($href),
              papaya_strings::escapeHTMLChars($activeGlyph)
            );
            $str .= '</subitem>';
            $str .= '</listitem>';
          }
        }
      }
    }
    return $str;
  }

  /**
   * Get tables list items
   *
   * @access public
   */
  function getTablesListView() {
    $str = '';
    if (
      isset($this->tables) &&
      is_array($this->tables) && count($this->tables) > 0
    ) {
      if (isset($this->sessionParams['showtables']) && $this->sessionParams['showtables']) {
        $showModules = TRUE;
        $nhref = $this->getLink(array('cmd' => 'tables_hide'));
        $node = 'open';
      } else {
        $showModules = FALSE;
        $nhref = $this->getLink(array('cmd' => 'tables_show'));
        $node = 'close';
      }
      $str = sprintf(
        '<listitem title="%s" node="%s" nhref="%s" span="3"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Tables')),
        papaya_strings::escapeHTMLChars($node),
        papaya_strings::escapeHTMLChars($nhref)
      );
      if ($showModules) {
        $images = $this->papaya()->images;
        $strPrefixed = '';
        foreach ($this->tables as $tableName => $inDatabase) {
          $imageIdx = 'status-sign-off';
          if (
            isset($this->params['pkg_id']) &&
            isset($this->packages[$this->params['pkg_id']]) &&
            $inDatabase
          ) {
            $modulePath = $this->prependModulePath(
              $this->packages[$this->params['pkg_id']]['modulegroup_path']
            );
            $tableStatus = $this->getTableStatus($modulePath, $tableName);
            switch ($tableStatus) {
            case PAPAYA_MODULE_TABLE_UNKNOWN :
              $imageIdx = 'status-sign-off';
              break;
            case PAPAYA_MODULE_TABLE_OK :
              $imageIdx = 'status-sign-ok';
              break;
            case PAPAYA_MODULE_TABLE_ERROR :
              $imageIdx = 'status-sign-problem';
              break;
            case PAPAYA_MODULE_TABLE_MISSING :
              $imageIdx = 'status-sign-warning';
              break;
            }
          } elseif (!$inDatabase) {
            $imageIdx = 'status-sign-warning';
          }
          $selected = (isset($this->params['table']) && $tableName == $this->params['table'])
            ? ' selected="selected"' : '';
          $strItem = sprintf(
            '<listitem href="%s" image="%s" title="%s" indent="1" span="2"%s>',
            papaya_strings::escapeHTMLChars($this->getLink(array('table' => $tableName))),
            papaya_strings::escapeHTMLChars($images['items-table']),
            papaya_strings::escapeHTMLChars(
              $this->getTableFullName($tableName)
            ),
            $selected
          );
          $activeGlyph = papaya_strings::escapeHTMLChars($images[$imageIdx]);
          $strItem .= '<subitem align="center"><glyph src="'.$activeGlyph.'"/></subitem>';
          $strItem .= '</listitem>';
          $pkgId = isset($this->params['pkg_id']) ? $this->params['pkg_id'] : NULL;
          if ($this->getTablePrefixUsage($tableName, $pkgId)) {
            $strPrefixed .= $strItem;
          } else {
            $str .= $strItem;
          }
        }
        $str .= $strPrefixed;
      }
    }
    return $str;
  }

  /**
   * Exports all data from the currently selected database table as csv. Please note
   * that this function exits on success. An error dialog is displayed when the table
   * parameter is not set.
   */
  function exportTableData() {
    if ($this->params['table'] != '') {
      $sql = 'SELECT * FROM %s';
      $tableName = $this->getTableFullName($this->params['table']);
      $param = array($tableName);
      $res = $this->databaseQueryFmt($sql, $param);
      if ($res) {
        $csvFile = new base_csv();
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $fileName = $tableName.'_'.date('Y-m-d').'.csv';
          $csvFile->outputExportHeaders($fileName);
          $keys = array_keys($row);
          print('"'.implode('","', $keys).'"'.LF);
          do {
            //We need to escape the content of each field by using
            //a custom escape function.
            $row = array_map(array($this, 'escapeForCSV'), $row);
            print(implode(',', $row).LF);
          } while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC));
          exit(0);
        }
      }
    } else {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('No table selected. You need to select a table to export data from.')
      );
    }
  }

  /**
   * This helper methods puts the given string between quotation marks, escapes the given
   * string by putting an additional quotation mark in front of an existing one and returns
   * the string ready to be put into a csv field.
   *
   * @param $str string the string object to be prepared for csv
   * @return string the csv compatible string
   */
  function escapeForCSV($str) {
    return '"'.str_replace('"', '""', $str).'"';
  }

  /**
   * Initialize module dialog
   *
   * @access public
   * @return boolean
   */
  function initModuleDialog() {
    if (!(isset($this->moduleDialog) && is_object($this->moduleDialog))) {
      if (isset($this->module) && is_array($this->module)) {
        $hidden = array(
          'save' => 1,
          'cmd' => 'module_save',
          'module_id' => $this->module['module_guid']
        );
        $data = array(
          'module_active' => $this->module['module_active'],
          'module_title' => $this->module['module_title'],
          'module_title_org' => $this->module['module_title_org'],
          'module_useoutputfilter' => $this->module['module_useoutputfilter'],
        );
        $yesnoArray = array(1 => $this->_gt('Yes'), 0 => $this->_gt('No'));
        $fields = array(
          'module_title' => array(
            'Title', 'isAlphaNumChar', TRUE, 'input', 100, ''),
          'module_active' => array(
            'Active', 'isNum', TRUE, 'combo', $yesnoArray, '', 2)
        );
        $this->moduleDialog = new base_dialog(
          $this, $this->paramName, $fields, $data, $hidden
        );
        $this->moduleDialog->dialogTitle = $this->_gt('Properties');
        $this->moduleDialog->baseLink = $this->baseLink;
        $this->moduleDialog->loadParams();
      } else {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Get module dialog
   *
   * @access public
   */
  function getModuleDialog() {
    if ($this->initModuleDialog()) {
      if ($str = $this->moduleDialog->getDialogXML()) {
        $this->layout->addRight($str);
      }
    }
  }

  /**
   * Get package dialog
   *
   * @access public
   * @return boolean
   */
  function getPackageDialog() {
    if (
      (isset($this->module) && is_array($this->module)) ||
      (isset($this->_tableStructure) && is_array($this->_tableStructure))
    ) {
      return FALSE;
    } elseif (
      isset($this->params['pkg_id']) &&
      isset($this->packages[$this->params['pkg_id']]) &&
      is_array($this->packages[$this->params['pkg_id']])
    ) {
      $missing = FALSE;
      if (isset($this->tables)) {
        foreach ($this->tables as $inDatabase) {
          if ($inDatabase !== TRUE) {
            $missing = TRUE;
            break;
          }
        }
      }
      if ($missing) {
        $hidden = array(
          'cmd' => 'pkg_table_create',
          'pkg_id' => $this->params['pkg_id'],
          'pkg_table_create_confirm' => 1,
        );
        $msg = $this->_gtf(
          'Create missing tables for package "%s"?',
          array($this->packages[$this->params['pkg_id']]['modulegroup_title'])
        );
        $dialog = new base_msgdialog(
          $this, $this->paramName, $hidden, $msg, 'question'
        );
        $dialog->buttonTitle = 'Create';
        if ($str = $dialog->getMsgDialog()) {
          $this->layout->addRight($str);
        }
      }
    }
    return FALSE;
  }

  /**
   * Get confirmation dialog for module (de)activations.
   *
   * @param boolean $activate optional, default value TRUE
   * @param mixed $moduleId optional, default value NULL
   * @access public
   * @return void
   */
  function getModuleStatusDialogXML($activate = TRUE, $moduleId = NULL) {
    if (isset($moduleId)) {
      if ($activate) {
        $cmd = 'module_enable';
        $msg = 'Enable selected module?';
        $btnTitle = 'Enable';
      } else {
        $cmd = 'module_disable';
        $msg = 'Disable selected module?';
        $btnTitle = 'Disable';
      }
      $hidden = array(
        'cmd' => $cmd,
        'confirm_status_change' => 1,
        'module_id' => $moduleId
      );
    } else {
      if ($activate) {
        $cmd = 'pkg_enable';
        $msg = 'Enable all modules in selected package?';
        $btnTitle = 'Enable';
      } else {
        $cmd = 'pkg_disable';
        $msg = 'Disable all modules in selected package?';
        $btnTitle = 'Disable';
      }
      $hidden = array(
        'cmd' => $cmd,
        'confirm_status_change' => 1,
        'pkg_id' => empty($this->params['pkg_id']) ? 0 : (int)$this->params['pkg_id'],
      );
    }

    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $this->_gt($msg), 'question');
    $dialog->buttonTitle = $btnTitle;
    if ($str = $dialog->getMsgDialog()) {
      $this->layout->addRight($str);
    }
  }

  /**
   * Load module use count
   *
   * @param integer $modId
   * @access public
   * @return array|FALSE $result boolean or array
   */
  function loadModuleUseCount($modId) {
    $elements = array(
      PAPAYA_DB_TBL_VIEWS => 'module_guid',
      PAPAYA_DB_TBL_VIEWMODES => 'module_guid',
      PAPAYA_DB_TBL_CRONJOBS => 'cron_module_guid',
      PAPAYA_DB_TBL_CRONJOBS => 'job_module_guid'
    );
    $sql = "SELECT COUNT(*) FROM %s WHERE %s = '%s'";
    $result = FALSE;
    foreach ($elements as $table => $field) {
      $params = array($table, $field, $modId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        list($count) = $res->fetchRow();
        $res->free();
        if (isset($result[$table])) {
          $result[$table] += $count;
        } else {
          $result[$table] = (int)$count;
        }
      }
    }
    return $result;
  }

  /**
   * Get table dialog
   *
   * @access public
   */
  function getTableDialog() {
    if (isset($this->_tableStructure) && is_array($this->_tableStructure)) {
      if (
        isset($this->_tableStructure['expected'], $this->tables[$this->_tableStructure['name']]) &&
        $this->tables[$this->_tableStructure['name']] === TRUE
      ) {
        if (
          isset($this->params['cmd']) &&
          $this->params['cmd'] === 'sync' &&
          count($this->_tableStructure['changes']) > 0
        ) {
          $this->getTableSyncDialog($this->_tableStructure['name']);
        }
        $this->getFieldsListView($this->_tableStructure);
        $this->getKeysListView($this->_tableStructure);
      } else {
        $this->getCreateTableDialog($this->_tableStructure['name']);
      }
    } elseif (isset($this->params['table']) && !empty($this->params['table'])) {
      $this->addMsg(MSG_ERROR, $this->_gt('Could not load table structure file!'));
    }
  }

  /**
   * Get fields list view
   *
   * @param array $structure
   * @access public
   */
  function getFieldsListView($structure) {
    if (isset($structure['expected'])) {
      $images = $this->papaya()->images;
      $listView = new \Papaya\UI\ListView();
      $listView->caption = new Papaya\UI\Text\Translated('Fields');
      $listView->columns[] = new \Papaya\UI\ListView\Column(new \Papaya\UI\Text\Translated('Name'));
      $listView->columns[] = new \Papaya\UI\ListView\Column(
        new \Papaya\UI\Text\Translated('Type'), \Papaya\UI\Option\Align::CENTER
      );
      $listView->columns[] = new \Papaya\UI\ListView\Column(
        new \Papaya\UI\Text\Translated('Size'), \Papaya\UI\Option\Align::CENTER
      );
      $listView->columns[] = new \Papaya\UI\ListView\Column(
        new \Papaya\UI\Text\Translated('Null'), \Papaya\UI\Option\Align::CENTER
      );
      $listView->columns[] = new \Papaya\UI\ListView\Column(
        new \Papaya\UI\Text\Translated('Auto Increment'), \Papaya\UI\Option\Align::CENTER
      );
      $listView->columns[] = new \Papaya\UI\ListView\Column(
        new \Papaya\UI\Text\Translated('Action'), \Papaya\UI\Option\Align::CENTER
      );

      $fieldNames = array_unique(
        array_merge($structure['expected']->fields->keys(), $structure['current']->fields->keys())
      );
      foreach ($fieldNames as $fieldName) {
        /** @var \Papaya\Database\Schema\Structure\FieldStructure $field */
        $field = isset($structure['expected']->fields[$fieldName])
          ? $structure['expected']->fields[$fieldName] : $structure['current']->fields[$fieldName];
        $listView->items[] = $item = new \Papaya\UI\ListView\Item('', $field->name);
        $item->subitems[] = new Papaya\UI\ListView\SubItem\Text($field->type);
        $item->subitems[] = new Papaya\UI\ListView\SubItem\Text($field->size);
        $item->subitems[] = new Papaya\UI\ListView\SubItem\Image(
          $field->allowsNull ? $images['status-node-checked-disabled'] : $images['status-node-empty-disabled']
        );
        $item->subitems[] = new Papaya\UI\ListView\SubItem\Image(
          $field->isAutoIncrement ? $images['status-node-checked-disabled'] : $images['status-node-empty-disabled']
        );
        $action = isset($structure['changes']['fields'][$field->name]) ? $structure['changes']['fields'][$field->name] : NULL;
        switch ($action) {
        case self::ACTION_FIELD_ADD:
          $item->subitems[] = new Papaya\UI\ListView\SubItem\Image(
            $images['actions-generic-add'],
            '',
            [$this->paramName => ['cmd' => $action, 'field' => $field->name]]
          );
          break;
        case self::ACTION_FIELD_REMOVE:
          $item->subitems[] = new Papaya\UI\ListView\SubItem\Image(
            $images['actions-generic-delete'],
            '',
            [$this->paramName => ['cmd' => $action, 'field' => $field->name]]
          );
          break;
        case self::ACTION_FIELD_CHANGE:
          $item->subitems[] = new Papaya\UI\ListView\SubItem\Image(
            $images['actions-edit'],
            '',
            [$this->paramName => ['cmd' => $action, 'field' => $field->name]]
          );
          break;
        default:
          $item->subitems[] = new Papaya\UI\ListView\SubItem\EmptyValue();
        }
      }
      $this->layout->addRight($listView->getXML());
    }
  }

  /**
   * Get Keys list view
   *
   * @param array $structure
   * @access public
   */
  function getKeysListView($structure) {
    if (isset($structure['expected'])) {
      $images = $this->papaya()->images;
      $listView = new UI\ListView();
      $listView->caption = new UI\Text\Translated('Fields');
      $listView->columns[] = new UI\ListView\Column(new UI\Text\Translated('Name'));
      $listView->columns[] = new UI\ListView\Column(
        new UI\Text\Translated('Fields'), UI\Option\Align::CENTER
      );
      $listView->columns[] = new UI\ListView\Column(
        new UI\Text\Translated('Unique'), UI\Option\Align::CENTER
      );
      $listView->columns[] = new UI\ListView\Column(
        new UI\Text\Translated('Fulltext'), UI\Option\Align::CENTER
      );
      $listView->columns[] = new UI\ListView\Column(
        new UI\Text\Translated('Action'), UI\Option\Align::CENTER
      );

      $indexNames = array_unique(
        array_merge($structure['expected']->indizes->keys(), $structure['current']->indizes->keys())
      );
      foreach ($indexNames as $indexName) {
        /** @var \Papaya\Database\Schema\Structure\IndexStructure $index */
        $index = isset($structure['expected']->indizes[$indexName])
          ? $structure['expected']->indizes[$indexName] : $structure['current']->indizes[$indexName];
        $listView->items[] = $item = new UI\ListView\Item('', $index->name);
        $item->subitems[] = new UI\ListView\SubItem\Text(implode(', ',$index->fields->keys()));
        $item->subitems[] = new UI\ListView\SubItem\Image(
          $index->isUnique ? $images['status-node-checked-disabled'] : $images['status-node-empty-disabled']
        );
        $item->subitems[] = new UI\ListView\SubItem\Image(
          $index->isFullText ? $images['status-node-checked-disabled'] : $images['status-node-empty-disabled']
        );
        $action = isset($structure['changes']['indizes'][$index->name]) ? $structure['changes']['indizes'][$index->name] : NULL;
        switch ($action) {
        case self::ACTION_INDEX_ADD:
          $item->subitems[] = new Papaya\UI\ListView\SubItem\Image(
            $images['actions-generic-add'],
            '',
            [$this->paramName => ['cmd' => $action, 'index' => $index->name]]
          );
          break;
        case self::ACTION_INDEX_REMOVE:
          $item->subitems[] = new Papaya\UI\ListView\SubItem\Image(
            $images['actions-generic-delete'],
            '',
            [$this->paramName => ['cmd' => $action, 'index' => $index->name]]
          );
          break;
        case self::ACTION_INDEX_CHANGE:
          $item->subitems[] = new Papaya\UI\ListView\SubItem\Image(
            $images['actions-edit'],
            '',
            [$this->paramName => ['cmd' => $action, 'index' => $index->name]]
          );
          break;
        default:
          $item->subitems[] = new Papaya\UI\ListView\SubItem\EmptyValue();
        }
      }
      $this->layout->addRight($listView->getXML());
    }
  }

  /**
   * Get create table dialog
   *
   * @param string $tableName
   * @access public
   */
  function getCreateTableDialog($tableName) {
    $hidden = array(
      'cmd' => 'table_create',
      'table' => $tableName,
      'table_create_confirm' => 1,
    );
    $msg = $this->_gtf(
      'Create table "%s"?',
      $this->getTableFullName($tableName)
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->buttonTitle = 'Create';
    if ($str = $dialog->getMsgDialog()) {
      $this->layout->addRight($str);
    }
  }

  /**
   * Get table synchronization dialog
   *
   * @param string $table
   * @access public
   */
  function getTableSyncDialog($table) {
    if (
      isset($table) &&
      trim($table != '') &&
      isset($this->_tableStructure) &&
      is_array($this->_tableStructure)
    ) {
      $hidden = array(
        'cmd' => 'sync',
        'table' => $table,
        'table_action_confirm' => 1,
      );
      $msg = $this->_gtf(
        'Synchronize table "%s"?',
        $this->getTableFullName($table)
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->buttonTitle = 'Synchronize';
      if ($str = $dialog->getMsgDialog()) {
        $this->layout->addRight($str);
      }
    }
  }

  /**
   * Get field dialog
   *
   * @access public
   */
  function getFieldDialog() {
    if (
      isset($this->params['cmd'], $this->params['field'], $this->_tableStructure['changes']['fields'][$this->params['field']]) &&
      in_array($this->params['cmd'], array(self::ACTION_FIELD_ADD, self::ACTION_FIELD_REMOVE, self::ACTION_FIELD_CHANGE), TRUE)
    ) {
      $hidden = array(
        'cmd' => $this->params['cmd'],
        'table' => $this->params['table'],
        'field' => $this->params['field'],
        'field_action_confirm' => 1,
      );
      switch ($this->params['cmd']) {
      case self::ACTION_FIELD_ADD:
        $msgPattern = 'Add field "%s" to table "%s"?';
        $btnCaption = 'Add';
        break;
      case self::ACTION_FIELD_REMOVE:
        $msgPattern = 'Delete field "%s" from table "%s"?';
        $btnCaption = 'Delete';
        break;
      case self::ACTION_FIELD_CHANGE:
      default :
        $msgPattern = 'Modify field "%s" in table "%s"?';
        $btnCaption = 'Modify';
        break;
      }
      $msg = $this->_gtf(
        $msgPattern,
        array($this->params['field'], $this->getTableFullName($this->params['table']))
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->buttonTitle = $btnCaption;
      if ($str = $dialog->getMsgDialog()) {
        $this->layout->addRight($str);
      }
    }
  }

  /**
   * Get index dialog
   *
   * @access public
   */
  function getIndexDialog() {
    if (
      isset($this->params['cmd'], $this->params['index'], $this->_tableStructure['changes']['indizes'][$this->params['index']]) &&
      in_array($this->params['cmd'], array(self::ACTION_INDEX_ADD, self::ACTION_INDEX_REMOVE, self::ACTION_INDEX_CHANGE), TRUE)
    ) {
      $hidden = array(
        'cmd' => $this->params['cmd'],
        'table' => $this->params['table'],
        'index' => $this->params['index'],
        'index_action_confirm' => 1,
      );
      switch ($this->params['cmd']) {
      case 'index_add':
        $msgPattern = 'Add index "%s" to table "%s"?';
        $btnCaption = 'Add';
        break;
      case 'index_delete':
        $msgPattern = 'Delete index "%s" from table "%s"?';
        $btnCaption = 'Delete';
        break;
      case 'index_update':
      default :
        $msgPattern = 'Modify index "%s" in table "%s"?';
        $btnCaption = 'Modify';
        break;
      }
      $msg = $this->_gtf(
        $msgPattern,
        array($this->params['index'], $this->getTableFullName($this->params['table']))
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->buttonTitle = $btnCaption;
      if ($str = $dialog->getMsgDialog()) {
        $this->layout->addRight($str);
      }
    }
  }

  /**
   * Get cached table status from session variables
   *
   * @param string $packagePath
   * @param string $tableName
   * @access public
   * @return integer
   */
  function getTableStatus($packagePath, $tableName) {
    if (
      isset($this->sessionParams['modified_tables']) &&
      isset($this->sessionParams['modified_tables'][$packagePath]) &&
      isset($this->sessionParams['modified_tables'][$packagePath][$tableName])
    ) {
      if ($this->sessionParams['modified_tables'][$packagePath][$tableName]) {
        return PAPAYA_MODULE_TABLE_ERROR;
      } else {
        return PAPAYA_MODULE_TABLE_OK;
      }
    } elseif (
      isset($this->sessionParams['missing_tables']) &&
      isset($this->sessionParams['missing_tables'][$packagePath]) &&
      isset($this->sessionParams['missing_tables'][$packagePath][$tableName])
    ) {
      return PAPAYA_MODULE_TABLE_MISSING;
    } else {
      return PAPAYA_MODULE_TABLE_UNKNOWN;
    }
  }

  /**
   * Set cached table status
   *
   * @param string $packagePath
   * @param string $tableName
   * @param boolean $modified optional, default value FALSE
   * @param boolean $missing optional, default value FALSE
   * @access public
   * @return void
   */
  function setTableStatus($packagePath, $tableName, $modified = FALSE, $missing = FALSE) {
    if ($missing) {
      $this->sessionParams['missing_tables'][$packagePath][$tableName] = TRUE;
      if (isset($this->sessionParams['modified_tables'][$packagePath][$tableName])) {
        unset($this->sessionParams['modified_tables'][$packagePath][$tableName]);
      }
      $this->setPackageStatus($packagePath, PAPAYA_MODULE_TABLE_MISSING);
    } elseif ($modified) {
      $this->sessionParams['modified_tables'][$packagePath][$tableName] = TRUE;
      if (isset($this->sessionParams['missing_tables'][$packagePath][$tableName])) {
        unset($this->sessionParams['missing_tables'][$packagePath][$tableName]);
      }
      $this->setPackageStatus($packagePath, PAPAYA_MODULE_TABLE_ERROR);
    } else {
      $this->sessionParams['modified_tables'][$packagePath][$tableName] = FALSE;
      if (isset($this->sessionParams['missing_tables'][$packagePath][$tableName])) {
        unset($this->sessionParams['missing_tables'][$packagePath][$tableName]);
      }
      $newStatus = PAPAYA_MODULE_TABLE_OK;
      if (
        isset($this->sessionParams['missing_tables'][$packagePath]) &&
        is_array($this->sessionParams['missing_tables'][$packagePath])
      ) {
        foreach ($this->sessionParams['missing_tables'][$packagePath] as $status) {
          if ($status) {
            $newStatus = PAPAYA_MODULE_TABLE_MISSING;
            break;
          }
        }
      }
      if (
        isset($this->sessionParams['modified_tables'][$packagePath]) &&
        is_array($this->sessionParams['modified_tables'][$packagePath])
      ) {
        foreach ($this->sessionParams['modified_tables'][$packagePath] as $status) {
          if ($status) {
            $newStatus = PAPAYA_MODULE_TABLE_ERROR;
            break;
          }
        }
      }
      $this->setPackageStatus($packagePath, $newStatus, TRUE);
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
   * Set cached package status.
   *
   * @param $packagePath
   * @param $newStatus
   * @param boolean $force optional, default value FALSE
   * @access public
   * @return void
   */
  function setPackageStatus($packagePath, $newStatus, $force = FALSE) {
    if (
      $force ||
      !isset($this->sessionParams['modified_packages'][$packagePath])
    ) {
      $this->sessionParams['modified_packages'][$packagePath] = $newStatus;
    } elseif ($newStatus > $this->sessionParams['modified_packages'][$packagePath]) {
      $this->sessionParams['modified_packages'][$packagePath] = $newStatus;
    }
  }

  /**
   * Clear cached package and table status from session
   * @return void
   */
  function clearPackageStatus() {
    if (isset($this->sessionParams['missing_tables'])) {
      unset($this->sessionParams['missing_tables']);
    }
    if (isset($this->sessionParams['modified_tables'])) {
      unset($this->sessionParams['modified_tables']);
    }
    if (isset($this->sessionParams['modified_packages'])) {
      unset($this->sessionParams['modified_packages']);
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
   * Get cached package status (from session variables)
   *
   * @param $packagePath
   * @access public
   * @return integer
   */
  function getPackageStatus($packagePath) {
    if (
      isset($this->sessionParams['modified_packages']) &&
      isset($this->sessionParams['modified_packages'][$packagePath])
    ) {
      return $this->sessionParams['modified_packages'][$packagePath];
    } else {
      return PAPAYA_MODULE_TABLE_UNKNOWN;
    }
  }

  /**
   * Get confirmation dialog for dialog names reset.
   *
   * @access public
   * @return void
   */
  function getModuleResetDialogXML() {
    $hidden = array(
      'cmd' => 'name_reset',
      'db_action_confirm' => 1,
    );
    $msg = $this->_gt('Reset module names to default values?');
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->buttonTitle = 'Reset';
    if ($str = $dialog->getMsgDialog()) {
      $this->layout->addRight($str);
    }
  }

  /**
   * Reset all module names to default names (names from modules.xml)
   *
   * @access public
   * @return boolean
   */
  function resetModuleNames() {
    $sql = "UPDATE %s SET module_title = module_title_org";
    $tableModules = $this->databaseGetTableName(\Papaya\Content\Tables::MODULES);
    return (FALSE !== $this->databaseQueryFmtWrite($sql, $tableModules));
  }

  /**
   * Get dialog for module options
   *
   * @access public
   * @return void
   */
  function getPluginOptionsDialog() {
    if (NULL !== $this->module) {
      $pluginObject = $this->papaya()->plugins->get(
        $this->module['module_guid'],
        $this
      );
      if ($pluginObject instanceof \Papaya\Plugin\Configurable\Options) {
        $pluginNode = $this->layout->values()->getValueByPath('/page/rightcol');
        if ($editor = $pluginObject->options()->editor()) {
          $editor->context()->merge(
            array(
              $this->paramName => array(
                'module_id' => $this->module['module_guid']
              )
            )
          );
          $pluginNode->append($editor);
          if ($pluginObject->options()->modified()) {
            $moduleOptions = new papaya_module_options();
            if ($moduleOptions->saveOptions($this->module['module_guid'], (array)$editor->getData())) {
              $this->addMsg(MSG_INFO, $this->_gt('Options modified.'));
            }
          }
          return;
        }
      } elseif ($pluginObject instanceof base_plugin) {
        $hidden = array(
          'module_id' => $this->module['module_guid']
        );
        if ($str = $pluginObject->getPluginOptionsDialog($this->paramName, $hidden)) {
          $this->layout->addRight($str);
          return;
        }
      }
    }
    $this->addMsg(MSG_INFO, $this->_gt('This module has no options.'));
  }

  /**
   * Initialize import table data dialog
   *
   * @access public
   * @return void
   */
  function initTableImportDialog() {
    if (!(isset($this->dialogTableImport) && is_object($this->dialogTableImport))) {
      $hidden = array(
        'save' => 1,
        'cmd' => 'import_table_data',
        'pkg_id' => $this->params['pkg_id'],
        'table' => $this->params['table']
      );
      $data = array();
      $defaultFile = $this->getDefaultTableDataFile(
        $this->params['pkg_id'],
        $this->params['table']
      );
      $fields = array();
      if (!empty($defaultFile) && file_exists($defaultFile['file'])) {
        $fields['table_default_file'] = array(
          'Default data file',
          'isAlphaNumChar',
          FALSE,
          'disabled_input',
          200,
          '',
          $defaultFile['rel_path'].$defaultFile['filename']
        );
      }
      $fields['table_data_file'] = array(
        'CSV file', 'isAlphaNumChar', FALSE, 'file', 200, ''
      );
      $this->dialogTableImport = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogTableImport->dialogTitle = $this->_gt('Import data');
      $this->dialogTableImport->buttonTitle = 'Import';
      $this->dialogTableImport->baseLink = $this->baseLink;
      $this->dialogTableImport->uploadFiles = TRUE;
      $this->dialogTableImport->loadParams();
    }
  }

  /**
   * Get table import dialog or confirmation dialog
   *
   * @access public
   * @return void
   */
  function getTableImportDialogXML() {
    if (isset($this->importDataFile) && is_file($this->importDataFile)) {
      $this->layout->addRight($this->getTableImportConfirmDialogXML());
    } else {
      $this->initTableImportDialog();
      $this->layout->addRight($this->dialogTableImport->getDialogXML());
    }
  }

  /**
   * Get table import confirmation dialog
   *
   * @access public
   * @return string
   */
  function getTableImportConfirmDialogXML() {
    $file = basename($this->importDataFile);
    $pathIdent = $this->importDataPathIdent;
    $result = sprintf(
      '<msgdialog action="#" width="100%%" type="question"'.
      ' onsubmit="importTableData(%d, \'%s\', \'%s\', \'%s\', 0);'.
      ' return false;" method="get">'.LF,
      (int)$this->params['pkg_id'],
      papaya_strings::escapeHTMLChars($this->params['table']),
      papaya_strings::escapeHTMLChars($pathIdent),
      papaya_strings::escapeHTMLChars($file)
    );
    $result .= sprintf(
      '<message>%s</message>'.LF,
      papaya_strings::escapeHTMLChars(
        sprintf(
          $this->_gt('Delete all data in table "%s" and replace with imported data?'),
          $this->getTableFullName($this->params['table'])
        )
      )
    );
    $result .= '<dlgbutton value="Import" />'.LF;
    $result .= '</msgdialog>'.LF;
    return $result;
  }

  /**
   * get the default table data file (from module diectory)
   *
   * @param $pkgId
   * @param $table
   * @access public
   * @return array|FALSE
   */
  function getDefaultTableDataFile($pkgId, $table) {
    $path = $this->getTableDataPath($this->packages[$pkgId]['modulegroup_path']);
    $csvFile = 'table_'.str_replace(array('/', '\\'), '', $table).'.csv';
    if (file_exists($path.$csvFile) && is_readable($path.$csvFile)) {
      return array(
        'file' => $path.$csvFile,
        'filename' => $csvFile,
        'path' => $path
      );
    } else {
      return FALSE;
    }
  }

  /**
   * Search directory with tabel structure/data files. Look for "_DATA" first use "DATA" as
   * a fallback.
   *
   * @param string $modulePath
   * @return string|FALSE
   */
  private function getTableDataPath($modulePath) {
    $allowedPaths = array('_DATA', 'DATA');
    foreach ($allowedPaths as $name) {
      $path = $this->prependModulePath(
        $modulePath.$name.'/'
      );
      if (file_exists($path) && is_dir($path)) {
        return $path;
      }
    }
    return FALSE;
  }

  /**
   * remove old uploaded data files in cache directory
   *
   * @param $directory
   * @access public
   * @return void
   */
  function removeOldFiles($directory) {
    if (file_exists($directory) && is_dir($directory)) {
      if (substr($directory, -1) != '/') {
        $directory .= '/';
      }
      $time = time() - 86400;
      if ($dh = opendir($directory)) {
        while (FALSE !== ($file = readdir($dh))) {
          $fileName = $directory.$file;
          if (
            is_file($fileName) &&
            filectime($fileName) < $time &&
            preg_match('(^data_[\w\d]+\.csv$)', $file)
          ) {
            @unlink($fileName);
          }
        }
      }
    }
  }

  /**
   * handle js rpc for table data import
   *
   * @access public
   * @return void
   */
  function rpcImportTableData() {
    $msg = 'No Package';
    if (!empty($this->params['pkg_id'])) {
      $pkgId = (int)$this->params['pkg_id'];
      if (isset($this->packages[$pkgId])) {
        $msg = 'No Table';
        if (
          !empty($this->params['table']) &&
          is_array($this->packages[$pkgId]['infos']['tables']) &&
          in_array(
            $this->params['table'],
            $this->packages[$pkgId]['infos']['tables']
          )
        ) {
          $table = $this->params['table'];
          if (
            !empty($this->params['path']) &&
            !empty($this->params['file']) &&
            preg_match('(^[\w\d.-]+$)', $this->params['file'])
          ) {
            switch ($this->params['path']) {
            case 'module' :
              $fileData = $this->getDefaultTableDataFile($pkgId, $table);
              $file = $fileData['file'];
              break;
            case 'cache' :
              $file = PAPAYA_PATH_CACHE.'tabledata/'.$this->params['file'];
              break;
            default :
              $file = NULL;
              break;
            }
            $offset = 0;
            if (!empty($this->params['offset']) && $this->params['offset'] > 0) {
              $offset = (int)$this->params['offset'];
            }
            $csvReader = new \Papaya\CSV\Reader($file);
            try {
              $csvReader->isValid(TRUE);
              $emptyTable = ($offset == 0);
              if ($data = $csvReader->fetchAssoc($offset, 1000)) {
                if ($emptyTable) {
                  //we empty the table only when we know that the csv-file we just uploaded
                  //is at least a valid csv file that contains at least one data row.
                  $this->databaseEmptyTable($this->getTableFullName($table));
                }
                $this->databaseInsertRecords($this->getTableFullName($table), $data);
                $fileSize = filesize($file);
                header('Content-type: text/xml');
                echo '<?xml version="1.0" encoding="UTF-8"?>'.LF;
                echo '<response>'.LF;
                if ($offset < $fileSize) {
                  echo '<method>rpcCallbackImportTableData</method>'.LF;
                  echo '<param name="pkg_id" value="'.(int)$pkgId.'" />'.LF;
                  echo '<param name="table" value="'.
                    papaya_strings::escapeHTMLChars($table).'" />'.LF;
                  echo '<param name="path" value="'.
                    papaya_strings::escapeHTMLChars($this->params['path']).'" />'.LF;
                  echo '<param name="file" value="'.
                    papaya_strings::escapeHTMLChars($this->params['file']).'" />'.LF;
                  echo '<param name="offset" value="'.(int)$offset.'" />'.LF;
                  echo '<param name="offset_bytes" value="'.(int)$offset.'" />'.LF;
                  echo '<param name="size_bytes" value="'.(int)$fileSize.'" />'.LF;
                } else {
                  echo '<method>rpcCallbackImportTableDataFinish</method>'.LF;
                }
                echo '</response>'.LF;
                exit;
              } else {
                $msg = 'No data found';
              }
            } catch (Exception $e) {
              $msg = $e->getMessage();
            }
          }
        }
      }
    }
    header('Content-type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>'.LF;
    echo '<response>'.LF;
    echo '<method>rpcCallbackImportTableData</method>'.LF;
    echo '<param name="msg" value="'.
      papaya_strings::escapeHTMLChars($msg).'" />'.LF;
    echo '</response>'.LF;
    exit;
  }

  /**
   * Get Tablename including prefix if needed
   *
   * @param string $tableName
   * @param integer|null $pkgId
   * @return string
   */
  function getTableFullName($tableName, $pkgId = NULL) {
    if (empty($pkgId) && !empty($this->params['pkg_id'])) {
      $pkgId = $this->params['pkg_id'];
    }
    if ($this->getTablePrefixUsage($tableName, $pkgId)) {
      return PAPAYA_DB_TABLEPREFIX.'_'.$tableName;
    } else {
      return $tableName;
    }
  }

  /**
   * Check if tablename need to be prefixed
   *
   * @param string $tableName
   * @param integer $pkgId
   * @return bool
   */
  function getTablePrefixUsage($tableName, $pkgId = NULL) {
    if ($this->alwaysPrefix) {
      return TRUE;
    }
    if (empty($pkgId) && !empty($this->params['pkg_id'])) {
      $pkgId = $this->params['pkg_id'];
    }
    if (
      isset($this->packages[$pkgId]) &&
      isset($this->packages[$pkgId]['infos']['table_properties'][$tableName])
    ) {
      return !empty($this->packages[$pkgId]['infos']['table_properties'][$tableName]['use_prefix']);
    } else {
      return FALSE;
    }
  }
}
