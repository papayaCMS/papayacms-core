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

use Papaya\CMS\Administration;

/**
* Papaya media db administration class - provides the backend
*
* @package Papaya
* @subpackage Media-Database
*/
class papaya_mediadb extends base_mediadb_edit {

  /**
  * position of dialogs (not messages!), 'left', 'right', 'center'
  */
  var $dialogPosition = 'center';

  /**
  * @var object $mimeObj instance ob base_mediadb_mimetypes
  */
  var $mimeObj = NULL;

  /**
   * @var base_btnbuilder $menubar
   */
  public $menubar = NULL;

  /**
   * @var base_btnbuilder $fileToolBar
   */
  public $fileToolBar = NULL;

  /**
   * @var integer $lngId
   */
  public $lngId = 0;

  /**
   * @var array|NULL $currentFile
   */
  public $currentFile = NULL;

  /**
   * @var array|NULL $currentFileData
   */
  public $currentFileData = NULL;

  /**
   * @var array|NULL $currentFolder
   */
  public $currentFolder = NULL;

  /**
   * @var array|NULL $category
   */
  public $category = NULL;

  /**
   * @var array|NULL $categories
   */
  public $categories = NULL;

  /**
   * @var array|NULL $categoryTree
   */
  public $categoryTree = NULL;

  /**
   * @var array|NULL $folders
   */
  public $folders = NULL;

  /**
   * @var array|NULL $alternativeFolderNames
   */
  public $alternativeFolderNames = NULL;

  /**
   * @var array|NULL $derivations
   */
  public $derivations = NULL;

  /**
   * @var array|NULL $derivationFiles
   */
  public $derivationFiles = NULL;

  /**
   * @var array|NULL $fileVersions
   */
  public $fileVersions = NULL;

  /**
   * @var base_msgdialog|base_dialog|NULL $dialog
   */
  public $dialog = NULL;

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
   * @var array
   */
  public $folderTree = array();

  /**
   * @var imgconv_common
   */
  public $imageConverter;

  /**
   * @var array
   */
  private $surfer;

  /*
  * Info: Additional attributes may be found in the f&f and mime section below
  */

  /**
  * constructur, sets paramName
  */
  function __construct($paramName = 'mdb') {
    parent::__construct();
    $this->paramName = $paramName;
  }
  /**
  * initializes sessions, languageSelector, mimtypes object
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);

    $this->initializeSessionParam('mode');
    if (isset($this->params['batch_cmd']) && is_array($this->params['batch_cmd'])) {
      $cmd = key($this->params['batch_cmd']);
      $this->params['cmd'] = 'batch_'.$cmd;
    }
    if (!(
          isset($this->params['cmd']) &&
          in_array($this->params['cmd'], array('crop_image_window'))
        )) {
      $this->initializeSessionParam('cmd');
    }
    if (!isset($this->params['mode'])) {
      $this->params['mode'] = 'files';
    }
    $this->initializeFilesSessionParams();

    $this->setSessionValue($this->sessionParamName, $this->sessionParams);

    $this->initializeMimeObject();
  }

  /**
  * initializes menu
  */
  function execute() {
    if (isset($_GET['upload_progress_id'])) {
      $rpcObj = new papaya_mediadb_rpc;
      $rpcObj->executeUploadProgressRPC($_GET['upload_progress_id']);
      exit;
    }

    if (isset($_SERVER['CONTENT_LENGTH']) &&
        $_SERVER['CONTENT_LENGTH'] > $this->iniGetSize('post_max_size')) {
      $this->addMsg(MSG_ERROR, $this->_gt('Uploaded data is to large.'));
    }
    $this->menubar = new base_btnbuilder;
    $this->menubar->images = $this->papaya()->images;

    $this->executeFilesManagement();
    $this->initializeFilesLayout();
  }

  /**
  * Clear public links directory
  * @param string $directory
  * @return integer
  */
  function clearCacheDirectory($directory = NULL) {
    $counter = 0;
    if (empty($directory)) {
      if (defined('PAPAYA_PATH_PUBLICFILES') &&
          trim(PAPAYA_PATH_PUBLICFILES) != '') {
        $directory = $_SERVER['DOCUMENT_ROOT'].PAPAYA_PATH_PUBLICFILES;
      }
    }
    if (is_dir($directory)) {
      if ($dh = opendir($directory)) {
        while (FALSE !== ($file = readdir($dh))) {
          if ($file != '.' && $file != '..') {
            if (is_dir($directory.$file)) {
              $counter += $this->clearCacheDirectory($directory.$file.'/');
              @rmdir($directory.$file);
            } elseif (is_file($directory.$file)) {
              @unlink($directory.$file);
              ++$counter;
            }
          }
        }
      }
    }
    return $counter;
  }

  /**
  * initializes xml for menu and dialog
  */
  function getXML() {
    $this->layout->addMenu(sprintf('<menu>%s</menu>'.LF, $this->menubar->getXML()));
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'edit_file':
      case 'file_tags':
      case 'file_versions':
      case 'file_derivations':
      case 'restore_version':
      case 'delete_version':
      case 'papaya_tag':
        $this->layout->addRight($this->getFilesToolbar());
        break;
      }
    }
    $this->addDialogXML();
  }

  /**
  * load and add xml for dialogs
  */
  function addDialogXML() {
    static $added = FALSE;
    // get dialog XML if a dialog exists
    if (!($added) && isset($this->dialog)) {
      if (is_object($this->dialog)) {
        switch (get_class($this->dialog)) {
        case 'base_dialog':
          $dialogXML = $this->dialog->getDialogXML();
          break;
        case 'base_msgdialog':
          $dialogXML = $this->dialog->getMsgDialog();
          break;
        default:
          $this->addMsg(MSG_ERROR, $this->_gt('Internal Error: Invalid dialog.'));
          break;
        }
      } elseif (is_string($this->dialog) && $this->dialog != '') {
        $dialogXML = $this->dialog;
      }

      // add dialog xml to prefered dialog position (differs for mime and f&f)
      if (isset($dialogXML) && $dialogXML != '') {
        switch ($this->dialogPosition) {
        case 'right':
          $this->layout->addRight($dialogXML);
          break;
        case 'left':
        case 'center':
        default:
          $this->layout->add($dialogXML);
          break;
        }
        $added = TRUE;
      }
    }
  }

  // ----------------------------- FILES & FOLDERS -------------------------------

  /**
  * This section contains the necessary methods for files and folders handling.
  */

  /**
  * @var object $mediaDB instance ob base_mediadb_edit
  */
  var $mediaDB = NULL;

  /**
  * @var array $defaultPanelState default state of panel expansion
  */
  var $defaultPanelState = array(
    'search' => 'close',
    'tags' => 'close',
    'folders' => 'open',
    'clipboard' => 'open',
  );

  /**
  * @var integer $numUploadFields number of upload fields to be displayed initially
  */
  var $numUploadFields = PAPAYA_NUM_UPLOAD_FIELDS;

  /**
  * @var integer $defaultLimit default number of entries in paging listviews
  */
  var $defaultLimit = 10;

  /**
  * @var array $pagingSteps list of display numbers to choose from for paging
  */
  var $pagingSteps = array(
    10 => 10,
    20 => 20,
    50 => 50,
    100 => 100
  );

  /**
  * @var integer $filePreviewWidth maximum width of file preview in the backend
  */
  var $filePreviewWidth = 200;

  var $filePreviewHeight = 200;

  /**
  * @var array $permissionModes list of available permissions modes for folders
  */
  var $permissionModes = array();

  /**
  * @var array $permissions list of available permission types
  */
  var $permissions = array(
    'user_edit' => 1,
    'user_view' => 1,
    'surfer_view' => 1,
    'surfer_add' => 1,
  );

  /**
  * initialize session parameters
  */
  function initializeFilesSessionParams() {
    $this->initializeSessionParam('mode_view');
    $this->initializeSessionParam('search', array('file_id'));
    $this->initializeSessionParam('folder_id', array('file_id', 'batch_files'));
    $this->initializeSessionParam('file_id', array('batch_files'));

    $this->initializeSessionParam('file_sort');
    $this->initializeSessionParam('file_order');
    $this->initializeSessionParam('offset_files');
    $this->initializeSessionParam('offset_clipboard');
    $this->initializeSessionParam('limit_clipboard', array('offset_clipboard'));
    $this->initializeSessionParam('limit_files', array('offset_files'));
    $this->initializeSessionParam('tag_id', array('file_id'));
    $this->initializeSessionParam('filter_mode', array('file_id', 'offset_files'));

    if (isset($this->params['clear_search'])) {
      $this->params['folder_id'] = 0;
      $this->params['offset'] = 0;
      $this->params['search'] = NULL;
      $this->initializeSessionParam('folder_id', array('offset', 'search'));
    }
    if (!isset($this->params['folder_id'])) {
      $this->params['folder_id'] = 0;
    }
    if (!isset($this->params['limit_files']) || $this->params['limit_files'] == 0) {
      $this->params['limit_files'] = $this->defaultLimit;
    }
    if (!isset($this->params['offset_files'])) {
      $this->params['offset_files'] = 0;
    }

    if (!isset($this->params['limit_clipboard']) || $this->params['limit_clipboard'] == 0) {
      $this->params['limit_clipboard'] = $this->defaultLimit;
    }
    if (!isset($this->params['offset_clipboard'])) {
      $this->params['offset_clipboard'] = 0;
    }

    if (!isset($this->params['file_order'])) {
      $this->params['file_order'] = 'asc';
    }

    if (!isset($this->params['file_sort'])) {
      $this->params['file_sort'] = 'name';
    }

    if (!isset($this->sessionParams['panel_state']) ||
        !is_array($this->sessionParams['panel_state']) ||
        count($this->sessionParams['panel_state']) <= 0) {
      $this->sessionParams['panel_state'] = $this->defaultPanelState;
    }

    $this->initializeSessionParam('open_folders');
    if (!(
          isset($this->sessionParams) &&
          isset($this->sessionParams['open_folders']) &&
          is_array($this->sessionParams['open_folders'])
        )) {
      $this->sessionParams['open_folders'] = array(0 => 0);
    }
    $this->checkOpenFolders();

    $this->initializeSessionParam('cat_id');
    if (!isset($this->params['cat_id']) || $this->params['cat_id'] == '') {
      $this->sessionParams['cat_id'] = 0;
      $this->params['cat_id'] = 0;
    }

    $this->permissionModes = array(
      'inherited' => $this->_gt('Inherited'),
      'own' => $this->_gt('Own'),
      'additional' => $this->_gt('Additional'),
    );
  }

  /**
  * execute commands for files and folders management
  */
  function executeFilesManagement() {
    $this->dialogPosition = 'right';

    if (!isset($this->params['cmd'])) {
      if (isset($this->params['file_id']) && $this->params['file_id'] != '') {
        $this->params['cmd'] = 'edit_file';
      } elseif (isset($this->params['folder_id']) && $this->params['folder_id'] > 0) {
        $this->params['cmd'] = 'edit_folder';
      } elseif (isset($this->params['folder_id']) && $this->params['folder_id'] == 0) {
        $this->params['cmd'] = 'add_folder';
      }
    }
    $administrationUser = $this->papaya()->administrationUser;
    $this->lngId = $this->papaya()->administrationLanguage->id;
    $this->loadFileData();
    $this->loadFolderData();

    if (!empty($this->params['cmd'])) {
      switch($this->params['cmd']) {
      /* FOLDERS */
      case 'import_folder':
        $this->getUploadToolbar();
        if ($administrationUser->hasPerm(Administration\Permissions::FILE_IMPORT)) {
          if (defined('PAPAYA_PATH_MEDIADB_IMPORT') && PAPAYA_PATH_MEDIADB_IMPORT != '') {
            if (is_dir(PAPAYA_PATH_MEDIADB_IMPORT)) {
              $this->layout->addRight($this->getLocalFolderXML());
              if (isset($this->params['confirm']) && $this->params['folder'] != '' &&
                  $this->loadSurferData()) {
                $this->importLocalFolder(
                  $this->params['folder'],
                  $this->params['target_folder_id'],
                  $this->surfer['surfer_id']
                );
              } elseif (isset($this->params['folder'])) {
                $this->layout->addRight($this->getLocalFolderProperties($this->params['folder']));
              }
            } else {
              $this->addMsg(
                MSG_ERROR,
                sprintf($this->_gt('Path "%s" not found.'), PAPAYA_PATH_MEDIADB_IMPORT)
              );
            }
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Please set the mediadb import path in the configuration.')
            );
          }
        } else {
          $this->addMsg(MSG_INFO, $this->_gt('You are not allowed to import a local folder.'));
        }
        break;
      case 'add_folder':
        $parentId = empty($this->params['parent_id']) ? 0 : (int)$this->params['parent_id'];
        if ($this->checkActionPermission('edit_folder', NULL, $parentId)) {
          $this->initializeFolderEditDialog($this->params['cmd']);
          if (isset($this->params['confirm']) && $this->params['confirm'] &&
              isset($this->params['parent_id']) &&
              $this->dialog->checkDialogInput()) {
            if ($folderId = $this->addNewFolder()) {
              $this->switchFolder($folderId);
              $this->initializeFolderEditDialog('edit_folder');
              $this->layout->addRight($this->dialog->getDialogXML());
              $this->layout->addRight($this->getPermissionListXML());
              unset($this->dialog);
            }
          }
        } elseif ($parentId > 0) {
          $this->addMsg(MSG_ERROR, $this->_gt('Permission denied.'));
        }
        break;
      case 'edit_folder':
        if ($this->checkActionPermission('edit_folder', NULL, $this->params['folder_id'])) {
          $this->initializeFolderEditDialog($this->params['cmd']);
          if (isset($this->params['folder_id']) && $this->params['folder_id'] > 0 &&
              isset($this->params['confirm']) && $this->params['confirm']) {
            if ($this->dialog->checkDialogInput()) {
              $this->updateFolder();
            }
          } elseif (isset($this->params['perm_action']) &&
                    isset($this->params['folder_id']) &&
                    isset($this->params['perm_type']) &&
                    isset($this->params['perm_id'])) {
            if ($this->params['perm_action'] == 'add_perm') {
              $this->addPermission(
                $this->params['folder_id'], $this->params['perm_type'], $this->params['perm_id']
              );
            } elseif ($this->params['perm_action'] == 'del_perm') {
              $this->delPermission(
                $this->params['folder_id'], $this->params['perm_type'], $this->params['perm_id']
              );
            }
          }

          $this->loadFolderData();
          if (isset($this->dialog) && is_object($this->dialog)) {
            $this->layout->addRight($this->dialog->getDialogXML());
            unset($this->dialog);
          }
          $this->layout->addRight($this->getPermissionListXML());
        }
        break;
      case 'del_folder':
        if ($this->checkActionPermission('edit_folder', NULL, $this->params['folder_id'])) {
          if (isset($this->params['folder_id']) && $this->params['folder_id'] > 0 &&
              isset($this->params['confirm']) && $this->params['confirm']) {
            $this->delFolder($this->params['folder_id']);
          } else {
            $this->initializeDeleteFolderDialog();
          }
        }
        break;
      case 'cut_folder':
        if ($this->checkActionPermission('edit_folder', NULL, $this->currentFolder['parent_id'])) {
          if (isset($this->params['folder_id']) && $this->params['folder_id'] > 0) {
            if (isset($this->params['confirm']) && $this->params['confirm']) {
              if (($parentFolderId = $this->moveFolder($this->params['folder_id'], -1)) &&
                  FALSE !== $parentFolderId) {
                $this->switchFolder($parentFolderId);
              }
            } else {
              $this->initializeCutFolderDialog();
            }
          }
        }
        break;
      case 'paste_folder':
        if ($this->checkActionPermission('edit_folder', NULL, $this->params['target_folder_id'])) {
          if (isset($this->params['folder_id']) && $this->params['folder_id'] > 0 &&
              isset($this->params['target_folder_id']) && $this->params['target_folder_id'] >= 0
              && $this->params['folder_id'] != $this->params['target_folder_id']) {
            $this->moveFolder($this->params['folder_id'], $this->params['target_folder_id']);
          }
        }
        break;
      /* FILES */
      case 'upload':
        $this->layout->addScript(
          '<script type="text/javascript">
            if (window.parent &&
                window.parent != window &&
                window.parent.disableUploadProgress) {
              window.parent.disableUploadProgress();
            }
           </script>');
        if (isset($this->params['confirm']) && $this->params['confirm'] &&
            $this->checkActionPermission('upload_file', NULL, $this->params['folder_id'])) {
          if ($this->processUpload()) {
            $this->params['cmd'] = 'edit_file';
            $this->layout->addRight($this->getFilePropertiesPanel());
          }
          if (isset($_POST['UPLOAD_JAVASCRIPT']) && $_POST['UPLOAD_JAVASCRIPT']) {
            printf(
              '<html><body><script type="text/javascript">'.
              'parent.uploadFinished("?%s[cmd]=edit_file");</script></body></html>',
              papaya_strings::escapeHTMLChars($this->paramName)
            );
            exit;
          } else {
            $this->initializeFileEditDialog();
          }
        } else {
          $this->getUploadToolbar();
          $this->getUploadDialog();
        }
        break;
      case 'upload_files' :
        $this->getUploadToolbar();
        $this->getUploadDialog();
        break;
      case 'get_file':
        $this->getUploadToolbar();
        $folderId = empty($this->params['folder_id']) ? 0 : (int)$this->params['folder_id'];
        if ($this->checkActionPermission('upload_file', NULL, $folderId)) {
          if (isset($this->params['web_file']) && $this->params['web_file'] != '') {
            if ($this->loadSurferData()) {
              $fileId = $this->getFileFromWeb(
                $this->params['web_file'],
                empty($this->params['folder_id']) ? 0 : (int)$this->params['folder_id'],
                $this->surfer['surfer_id'],
                empty($this->params['replace']) ? FALSE : (bool)$this->params['replace'],
                empty($this->params['file_id']) ? '' : $this->params['file_id']
              );
              if ($fileId) {
                $this->switchFile($fileId);
                $this->addMsg(
                  MSG_INFO,
                  sprintf(
                    $this->_gt('File "%s" (%s) imported from "%s".'),
                    $this->currentFile['file_name'],
                    $fileId,
                    $this->params['web_file']
                  )
                );
                $this->layout->addRight($this->getFilePropertiesPanel());
                break;
              }
            }
          }
          $this->initializeGetFileFromWebDialog();
        }
        break;
      case 'edit_file':
        if (isset($this->currentFile['file_id']) &&
            !$this->isFileInClipboard() &&
            $this->checkActionPermission('edit_file', $this->currentFile['file_id'])) {
          $this->initializeFileEditDialog();
          if (isset($this->params['confirm']) && $this->params['confirm'] &&
              $this->dialog->checkDialogInput()) {
            if ($this->updateFile($this->params, $this->papaya()->administrationLanguage->id)) {
              $this->addMsg(MSG_INFO, $this->_gt('File properties saved.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Failed to save file properties.'));
            }
          }
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      case 'delete_file':
        if ($this->checkActionPermission('delete_file', $this->params['file_id'])) {
          if (isset($this->params['confirm']) && $this->params['confirm'] &&
              isset($this->params['file_id']) && $this->params['file_id'] != '') {
            if ($this->deleteFile($this->params['file_id'])) {
              unset($this->currentFile);
              unset($this->params['file_id']);
              $this->addMsg(
                MSG_INFO,
                $this->_gt('File deleted.')
              );
            } else {
              $this->logMsg(
                MSG_ERROR,
                PAPAYA_LOGTYPE_SYSTEM,
                'Failed to delete file.'
              );
            }
          } else {
            $this->initializeDeleteFileDialog();
          }
        }
        break;
      case 'fix_file_ext':
        if ($this->checkActionPermission('edit_file', $this->params['file_id'])) {
          if (isset($this->params['file_id']) && $this->params['file_id'] != '') {
            if (isset($this->params['confirm']) && $this->params['confirm']) {
              $this->changeExtension();
              unset($this->dialog);
            } else {
              $this->initializeFixExtensionDialog();
            }
          }
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      case 'cut_file':
        if ($this->checkActionPermission('edit_file', $this->params['file_id'])) {
          if (isset($this->params['file_id']) && $this->params['file_id'] != '') {
            $this->moveFile($this->params['file_id'], -1);
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('You don\'t have the permission to edit the file.')
          );
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      case 'paste_file':
        if ($this->isFolderSelected() &&
            $this->isFileInClipboard() &&
            $this->checkActionPermission('upload_file', 0, $this->currentFolder['folder_id'])) {
          $this->moveFile($this->params['file_id'], $this->params['folder_id']);
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('You don\'t have the permission to paste the file here.')
          );
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      case 'copy_file':
        if ($this->checkActionPermission('edit_file', $this->params['file_id'])) {
          if ($fileId = $this->copyFile($this->params['file_id'])) {
            $this->switchFile($fileId);
            $this->initializeFileEditDialog($this->params['cmd']);
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('You don\'t have the permission to edit the file.')
          );
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      case 'convert_image':
        if ($this->checkActionPermission('edit_file', $this->params['file_id'])) {
          $this->initializeconvertImageDialog();
          if (isset($this->params['confirm']) && $this->params['confirm'] &&
              $this->dialog->checkDialogInput()) {
            $newFileId = $this->convertImage(
              $this->params['file_id'],
              $this->params['target_format'],
              $this->params['folder_id']
            );
            if ($newFileId) {
              unset($this->dialog);
              $this->switchFile($newFileId);
              $this->layout->addRight($this->getFilePropertiesPanel());
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Could not convert file.'));
            }
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('You don\'t have the permission to edit the file.')
          );
        }
        break;
      case 'papaya_tag':
        $this->layout->addRight($this->getPapayaTagCreator($this->params['file_id']));
        break;
      case 'file_tags':
        if (isset($this->params['file_id'])) {
          $this->initializeTagDialog();
        }
        break;
      case 'file_derivations':
        if (isset($this->params['file_id'])) {
          $this->layout->addRight($this->getFileDerivationsListXML());
          $this->layout->addRight($this->getFilePropertiesPanel());
        }
        break;
      case 'file_versions':
        if (isset($this->params['file_id'])) {
          $this->layout->addRight($this->getFileVersionsListXML());
          $this->layout->addRight($this->getFilePropertiesPanel());
        }
        break;
      case 'restore_version':
        if ($this->checkActionPermission('edit_file', $this->params['file_id'])) {
          if (isset($this->params['file_id'])) {
            if (isset($this->params['confirm']) && isset($this->params['version_id'])) {
              if ($this->restoreVersion($this->params['file_id'], $this->params['version_id'])) {
                $this->loadFileData();
                $this->addMsg(MSG_INFO, $this->_gt('Version restored.'));
              } else {
                $this->getErrorForType($this->lastError);
              }
            } else {
              $this->layout->addRight($this->getRestoreVersionDialog());
            }
            $this->layout->addRight($this->getFileVersionsListXML());
            $this->layout->addRight($this->getFilePropertiesPanel());
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('You don\'t have the permission to edit the file.')
          );
        }
        break;
      case 'delete_version':
        if ($this->checkActionPermission('delete_file', $this->params['file_id'])) {
          if (isset($this->params['file_id']) && isset($this->params['version_id'])) {
            if (isset($this->params['confirm'])) {
              $this->deleteVersion($this->params['file_id'], $this->params['version_id']);
              unset($this->params['version_id']);
              $this->layout->addRight($this->getFileVersionsListXML());
              $this->layout->addRight($this->getFilePropertiesPanel());
            } else {
              $this->layout->addRight($this->getDeleteVersionDialog());
              $this->layout->addRight($this->getFileVersionsListXML());
              $this->layout->addRight($this->getFilePropertiesPanel());
            }
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('You don\'t have the permission to delete the file.')
          );
        }
        break;
      case 'restore_meta':
        if ($this->checkActionPermission('edit_file', $this->params['file_id'])) {
          if (isset($this->params['file_id']) && $this->params['file_id'] != '') {
            $this->restoreMetadata($this->params['file_id']);
          }
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      /* batch actions */
      case 'paste_all':
        if ($this->checkActionPermission('edit_file', NULL, $this->params['folder_id'])) {
          if (isset($this->params['folder_id'])) {
            if ($this->moveFiles(-1, $this->params['folder_id'])) {
              $this->addMsg(MSG_INFO, $this->_gt('Pasted all files to the current folder.'));
            }
          }
        }
        break;
      case 'batch_delete':
        if (isset($this->params['batch_files']) && count($this->params['batch_files']) > 0) {
          if (isset($this->params['confirm']) && $this->params['confirm']) {
            $this->deleteMultipleFiles(array_keys($this->params['batch_files']));
            unset($this->params['batch_files']);
            unset($this->sessionParams['batch_files']);
            $this->setSessionValue($this->sessionParamName, $this->sessionParams);
          } else {
            $this->initializeDeleteMultipleFilesDialog();
          }
        } else {
          $this->addMsg(MSG_INFO, $this->_gt('No files selected.'));
        }
        break;
      case 'batch_move':
        if (isset($this->params['batch_files']) && count($this->params['batch_files']) > 0) {
          if (isset($this->params['confirm']) && $this->params['confirm'] &&
              isset($this->params['target_folder_id'])) {
            $moved = $this->moveMultipleFiles(
              array_keys($this->params['batch_files']),
              $this->params['target_folder_id']
            );
            if ($moved) {
              unset($this->params['batch_files']);
              unset($this->sessionParams['batch_files']);
              $this->setSessionValue($this->sessionParamName, $this->sessionParams);
            }
          } else {
            $this->initializeMoveMultipleFilesDialog();
          }
        } else {
          $this->addMsg(MSG_INFO, $this->_gt('No files selected.'));
        }
        break;
      case 'batch_cut':
        if (isset($this->params['batch_files']) && count($this->params['batch_files']) > 0) {
          if (isset($this->params['confirm']) && $this->params['confirm']) {
            if ($this->moveMultipleFiles(array_keys($this->params['batch_files']), -1)) {
              unset($this->params['batch_files']);
              unset($this->sessionParams['batch_files']);
              $this->setSessionValue($this->sessionParamName, $this->sessionParams);
            }
          } else {
            $this->initializeCutMultipleFilesDialog();
          }
        } else {
          $this->addMsg(MSG_INFO, $this->_gt('No files selected.'));
        }
        break;
      case 'batch_tags':
        if (isset($this->params['batch_files']) && count($this->params['batch_files']) > 0) {
          $this->initializeTagMultipleFilesDialog();
        } else {
          $this->addMsg(MSG_INFO, $this->_gt('No files selected.'));
        }
        break;
      /* open/close panel, folder, categories, ... */
      case 'close_panel':
        if (isset($this->params['panel'])) {
          $this->sessionParams['panel_state'][$this->params['panel']] = 'closed';
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      case 'open_panel':
        if (isset($this->params['panel'])) {
          $this->sessionParams['panel_state'][$this->params['panel']] = 'open';
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      case 'open_folder':
        if (isset($this->params['open_folder_id'])) {
          $this->sessionParams['open_folders'][$this->params['open_folder_id']] = 1;
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      case 'close_folder':
        if (isset($this->params['close_folder_id'])) {
          unset($this->sessionParams['open_folders'][$this->params['close_folder_id']]);
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      case 'open_category':
        if (isset($this->params['cat_open_id']) && $this->params['cat_open_id'] > 0) {
          $this->sessionParams['open_categories'][(int)$this->params['cat_open_id']] =
            (int)$this->params['cat_open_id'];
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      case 'close_category':
        if (isset($this->params['cat_close_id']) && $this->params['cat_close_id'] > 0) {
          unset($this->sessionParams['open_categories'][(int)$this->params['cat_close_id']]);
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      default:
        if (isset($this->params['cmd'])) {
          $this->addMsg(MSG_INFO, sprintf('Unknown command "%s".', $this->params['cmd']));
        }
        $this->layout->addRight($this->getFilePropertiesPanel());
        break;
      }
    } else {
      $this->layout->addRight($this->getFilePropertiesPanel());
    }
  }

  /**
  * switch to a different folder
  *
  * @param integer $folderId id of folder to switch to
  */
  function switchFolder($folderId) {
    if (!isset($this->params['folder_id']) || $this->params['folder_id'] != $folderId) {
      $this->params['folder_id'] = $folderId;
      $this->sessionParams['folder_id'] = $folderId;
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    }
    $this->loadFolderData();
  }

  function copyFile($fileId, $folderId = -1) {
    if (parent::copyFile($fileId, $folderId)) {
      $this->switchFile($fileId, 1);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * switch to a different file
  *
  * @param string $fileId id of file to switch to
  * @param integer $versionId id of version to switch to
  */
  function switchFile($fileId, $versionId = NULL) {
    if (!isset($this->params['file_id']) ||
        $this->params['file_id'] != $fileId) {
      $this->params['file_id'] = $fileId;
      $this->sessionParams['file_id'] = $fileId;
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    }
    if ($versionId > 0) {
      $this->params['version_id'] = $versionId;
    }
    $this->loadFileData();
  }

  /**
  * load data for currently selected folder if set
  */
  function loadFolderData() {
    if (isset($this->params['folder_id'])) {
      $folder = $this->getFolder($this->params['folder_id']);
      if (isset($folder[$this->papaya()->administrationLanguage->id])) {
        $this->currentFolder = $folder[$this->papaya()->administrationLanguage->id];
      } else {
        $this->currentFolder = current($folder);
      }
    }
  }

  /**
  * load data for currently selected file if set
  */
  function loadFileData() {
    if (isset($this->params['file_id'])) {
      if (isset($this->params['version_id']) && $this->params['version_id'] > 0) {
        $this->currentFile = $this->getFile($this->params['file_id'], $this->params['version_id']);
      } else {
        $this->currentFile = $this->getFile($this->params['file_id']);
      }
      $fileData = $this->getFileTrans(
        $this->params['file_id'], $this->papaya()->administrationLanguage->id
      );
      if (isset($fileData[$this->params['file_id']])) {
        $this->currentFileData = $fileData[$this->params['file_id']];
      } else {
        unset($this->currentFileData);
      }
    }
  }

  /**
   * calculates whether a user may execute a specific action
   *
   * use this method to find out whether a user may add/edit/delete file(s) or folder(s)
   *
   * @param string $action edit_folder, upload_file, edit_file, delete_file, view_file
   * @param mixed $fileId a file id or an array thereof (either this or folderId must have a value)
   * @param mixed $folderId a folder id or an array thereof
   *   (either this or fileId must have a value)
   *
   * @return array|bool
   */
  function checkActionPermission($action, $fileId = NULL, $folderId = NULL) {
    $administrationUser = $this->papaya()->administrationUser;
    switch ($action) {
    case 'edit_folder':
      if ($administrationUser->hasPerm(Administration\Permissions::FILE_FOLDER_MANAGE) &&
          NULL !== $folderId &&
          ($folderPermissions = $this->calculateFolderPermissions($folderId))) {
        return $this->checkActionPermissionGroup($folderPermissions['user_edit']);
      }
      break;
    case 'upload_file':
      if ($administrationUser->hasPerm(Administration\Permissions::FILE_UPLOAD) &&
          NULL !== $folderId &&
          ($folderPermissions = $this->calculateFolderPermissions($folderId))) {
        return $this->checkActionPermissionGroup($folderPermissions['user_edit']);
      }
      break;
    case 'edit_file':
      if ($administrationUser->hasPerm(Administration\Permissions::FILE_EDIT)) {
        if (!is_array($fileId) && ($folderPermissions = $this->calculateFilePermission($fileId))) {
          if (isset($folderPermissions['user_edit'])) {
            foreach ($administrationUser->user['groups'] as $groupId) {
              if (isset($folderPermissions['user_edit'][$groupId]) &&
                  $folderPermissions['user_edit'][$groupId]) {
                return TRUE;
              }
            }
            return FALSE;
          }
        } elseif (is_array($fileId)) {
          $allowed = array();
          if ($folderPermissions = $this->calculateFilePermission($fileId)) {
            $files = $this->getFilesById($fileId);
            $allowed = array();
            foreach ($files as $id => $file) {
              $checked = $this->checkActionPermissionGroup(
                $folderPermissions[$file['folder_id']]['user_edit']
              );
              if ($checked) {
                $allowed[$id] = $id;
              }
            }
          }
          return $allowed;
        } elseif (NULL !== $folderId &&
                  ($folderPermissions = $this->calculateFolderPermissions($folderId))) {
          return (
            $administrationUser->hasPerm(Administration\Permissions::FILE_EDIT) &&
            $this->checkActionPermissionGroup($folderPermissions['user_edit'])
          );
        }
      }
      break;
    case 'delete_file':
      if ($administrationUser->hasPerm(Administration\Permissions::FILE_DELETE)) {
        $allowed = FALSE;
        if (!is_array($fileId) && ($folderPermissions = $this->calculateFilePermission($fileId))) {
          return $this->checkActionPermissionGroup($folderPermissions['user_edit']);
        } elseif (is_array($fileId)) {
          if ($folderPermissions = $this->calculateFilePermission($fileId)) {
            $files = $this->getFilesById($fileId);
            $allowed = array();
            foreach ($files as $id => $file) {
              $check = $this->checkActionPermissionGroup(
                $folderPermissions[$file['folder_id']]['user_edit']
              );
              if ($check) {
                $allowed[$id] = $id;
              }
            }
          }
          return $allowed;
        } elseif (is_array($fileId) &&
                  ($folderPermissions = $this->calculateFilePermission($fileId))) {
          return $this->checkActionPermissionGroup($folderPermissions['user_edit']);
        }
      }
      break;
    case 'view_file':
      if (!is_array($fileId) && ($folderPermissions = $this->calculateFilePermission($fileId))) {
        return $this->checkActionPermissionGroup($folderPermissions['user_view']);
      }
      break;
    }
    return FALSE;
  }

  /**
  * checks the authuser is in one of the groups and it is a permitted group
  *
  * @param array $permittedGroups
  * @access public
  * @return boolean
  * @see papaya_mediaddb::checkActionPermission()
  */
  function checkActionPermissionGroup($permittedGroups) {
    if (is_array($permittedGroups) && count($permittedGroups) > 0) {
      foreach ($permittedGroups as $groupId => $allowed) {
        if ($allowed && $groupId == -2) {
          return TRUE;
        } elseif ($allowed && $this->papaya()->administrationUser->inGroup($groupId)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * calculates the permissions for a given fileid
  */
  function calculateFilePermission($fileId) {
    if (NULL != $fileId && !is_array($fileId) && ($file = $this->getFile($fileId))) {
      if ($folderPermissions = $this->calculateFolderPermissions($file['folder_id'])) {
        return $folderPermissions;
      }
    } elseif (is_array($fileId)) {
      // get all folder ids of the files
      $files = $this->getFilesById($fileId);
      foreach ($files as $file) {
        $folders[$file['folder_id']] = $file['folder_id'];
      }
      if (isset($folders) && is_array($folders) && count($folders) > 0) {
        return $this->calculateMultipleFolderPermissions($folders);
      }
    }
    return FALSE;
  }


  /**
  * initialize layout for files and folders administration
  */
  function initializeFilesLayout() {
    $this->loadFilesMenubar();
    // navigation stuff
    $this->layout->add($this->getSearchPanel());
    $this->layout->add($this->getTagsPanel());
    $this->layout->add($this->getFolderPanel());
    // file list
    $this->layout->add($this->getClipboardPanel());
    $this->layout->add($this->getFilesPanel());
    // dialog
    $this->layout->parameters()->set('COLUMNWIDTH_CENTER', '50%');
    $this->layout->parameters()->set('COLUMNWIDTH_RIGHT', '50%');
  }

  /**
  * generate menu for files and folders administration
  */
  function loadFilesMenubar() {
    $this->menubar->addSeparator();
    $isFolderEditable = $this->checkActionPermission(
      'edit_folder', NULL, empty($this->params['folder_id']) ? 0 : (int)$this->params['folder_id']
    );
    $administrationUser = $this->papaya()->administrationUser;
    if ($administrationUser->hasPerm(Administration\Permissions::FILE_FOLDER_MANAGE) &&
        $isFolderEditable) {
      $this->menubar->addButton(
        'Add folder',
        $this->getLink(
          array(
            'cmd' => 'add_folder',
            'parent_id' => empty($this->params['folder_id']) ? 0 : (int)$this->params['folder_id']
          )
        ),
        'actions-folder-add'
      );
      if (isset($this->params['folder_id']) && $this->params['folder_id'] > 0) {
        if (empty($this->params['file_id'])) {
          $this->menubar->addButton(
            'Cut folder',
            $this->getLink(array('cmd' => 'cut_folder', 'folder_id' => $this->params['folder_id'])),
            'actions-edit-cut'
          );
          $this->menubar->addButton(
            'Delete folder',
            $this->getLink(array('cmd' => 'del_folder', 'folder_id' => $this->params['folder_id'])),
            'actions-folder-delete'
          );
        }
      }
    }
    $this->menubar->addSeparator();
    if ($this->isFolderSelected() && $isFolderEditable) {
      $this->menubar->addButton(
        'Upload files',
        $this->getLink(
          array(
            'cmd' => 'upload_files',
            'file_id' => empty($this->params['file_id']) ? '' : $this->params['file_id']
          )
        ),
        'actions-upload',
        'Upload files from your computer',
        isset($this->params['cmd']) && $this->params['cmd'] == 'upload_files'
      );
    }
    if (isset($this->currentFile) &&
        is_array($this->currentFile) &&
        count($this->currentFile) > 0 &&
        $this->checkActionPermission('edit_file', $this->currentFile['file_id'])) {
      $this->menubar->addSeparator();
      if ($administrationUser->hasPerm(Administration\Permissions::FILE_DELETE)) {
        $this->menubar->addButton(
          'Delete file',
          $this->getLink(array('cmd' => 'delete_file', 'file_id' => $this->params['file_id'])),
          'places-trash',
          'Delete the file'
        );
      }

      if (!$this->isFileInClipboard()) {
        if ($administrationUser->hasPerm(Administration\Permissions::FILE_EDIT)) {
          $copyParams = array (
            'cmd' => 'copy_file',
            'file_id' => $this->params['file_id'],
          );
          if (isset($this->currentFile['version_id'])) {
            $copyParams['version_id'] = $this->currentFile['version_id'];
          }
          $this->menubar->addButton(
            'Copy file',
            $this->getLink($copyParams),
            'actions-edit-copy',
            'Copy file to clipboard'
          );
          if ($this->currentFile['mimetype_ext'] == 'jpg' ||
              $this->currentFile['mimetype_ext'] == 'gif' ||
              $this->currentFile['mimetype_ext'] == 'png') {
            $convertParams = array(
              'cmd' => 'convert_image',
              'file_id' => $this->params['file_id'],
            );
            $this->menubar->addButton(
              'Convert',
              $this->getLink($convertParams),
              'actions-edit-convert',
              'Convert image file',
              isset($this->params['cmd']) && $this->params['cmd'] == 'convert_image'
            );
          }
        }

        $this->menubar->addSeparator();

        if ($administrationUser->hasPerm(Administration\Permissions::FILE_EDIT)) {
          $this->menubar->addButton(
            'Restore metadata',
            $this->getLink(array('cmd' => 'restore_meta', 'file_id' => $this->params['file_id'])),
            'actions-disk-scan',
            'Restore file information from binary data.'
          );
        }
      }

      $this->menubar->addSeparator();
      $this->menubar->addButton(
        'Download',
        $this->getWebMediaLink(
          $this->currentFile['file_id'].'v'.$this->currentFile['current_version_id'],
          'download',
          $this->currentFile['file_name']
        ),
        'actions-download',
        'Download file'
      );
    }
    $this->menubar->addSeparator();
  }

  // ----------------------- GENERAL PANEL DISPOSITION -------------------------

  /**
  * generate panel to search for files
  *
  * @return string $result search panel dialog xml
  */
  function getSearchPanel() {
    $result = '';
    if (isset($this->sessionParams['panel_state']['search']) &&
        $this->sessionParams['panel_state']['search'] == 'open') {
      $resize = sprintf(
        ' minimize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'close_panel', 'panel' => 'search'))
        )
      );
      $result .= sprintf(
        '<dialog action="%s#mediaFilesList" method="post" title="%s" %s>'.LF,
        papaya_strings::escapeHTMLChars($this->getLink()),
        papaya_strings::escapeHTMLChars($this->_gt('Search')),
        $resize
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[filter_mode]" value="search" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[offset_files]" value="0" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= '<lines class="dialogSmall">'.LF;
      $result .= sprintf(
        '<line caption="%s" hint="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Text')),
        papaya_strings::escapeHTMLChars(
          $this->_gt('Id, Filename, Source, Keywords, Title, Description')
        )
      );
      $result .= sprintf(
        '<input type="text" name="%s[search][q]" value="%s" class="dialogInput dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['search']['q'])
          ? '' :  papaya_strings::escapeHTMLChars($this->params['search']['q'])
      );
      $result .= '</line>'.LF;

      $result .= sprintf(
        '<line caption="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Filetype'))
      );
      $result .= sprintf(
        '<select name="%s[search][mimegroup]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $mimegroups = $this->mimeObj->getMimeGroups(
        $this->papaya()->administrationLanguage->id, FALSE
      );
      $result .= sprintf(
        '<option value="">%s</option>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('All'))
      );
      foreach ($mimegroups as $mimegroupId => $mimegroup) {
        if (isset($this->params['search']['mimegroup']) &&
            $this->params['search']['mimegroup'] == $mimegroupId) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<option value="%s" %s>%s</option>'.LF,
          (int)$mimegroupId,
          $selected,
          papaya_strings::escapeHTMLChars($mimegroup['mimegroup_title'])
        );
      }
      $result .= '</select>'.LF;
      $result .= '</line>'.LF;

      $result .= sprintf(
        '<line caption="%s &gt;">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Date'))
      );
      $result .= sprintf(
        '<input type="text" name="%s[search][younger]" value="%s"'.
        ' class="dialogInputDateTime dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['search']['younger'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['search']['younger'])
      );
      $result .= '</line>'.LF;

      $result .= sprintf(
        '<line caption="%s &lt;">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Date'))
      );
      $result .= sprintf(
        '<input type="text" name="%s[search][older]" value="%s"'.
        ' class="dialogInputDateTime dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['search']['older'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['search']['older'])
      );
      $result .= '</line>'.LF;
      $result .= sprintf(
          '<line caption="%s (kB) &gt;">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('size'))
      );
      $result .= sprintf(
          '<input type="text" name="%s[search][smaller]" value="%s"'.
          ' class="dialogInput dialogScale"/>',
          papaya_strings::escapeHTMLChars($this->paramName),
          empty($this->params['search']['smaller'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['search']['smaller'])
      );
      $result .= '</line>'.LF;
      $result .= sprintf(
          '<line caption="%s (kB) &lt;">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('size'))
      );
      $result .= sprintf(
          '<input type="text" name="%s[search][bigger]" value="%s"'.
          ' class="dialogInput dialogScale"/>',
          papaya_strings::escapeHTMLChars($this->paramName),
          empty($this->params['search']['bigger'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['search']['bigger'])
      );
      $result .= '</line>'.LF;
      $result .= sprintf(
        '<line caption="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Owner'))
      );
      $result .= sprintf(
        '<input type="text" name="%s[search][owner]" value="%s" class="dialogInput dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['search']['owner'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['search']['owner'])
      );
      $result .= '</line>'.LF;
      $result .= '</lines>'.LF;
      $result .= sprintf(
        '<dlgbutton align="left" name="%s[clear_search]" caption="%s" type="submit" />',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt('Clear'))
      );
      $result .= sprintf(
        '<dlgbutton value="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Find'))
      );
      $result .= '</dialog>'.LF;
    } else {
      $resize = sprintf(
        ' maximize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'open_panel', 'panel' => 'search'))
        )
      );
      $result .= sprintf(
        '<listview title="%s" %s />'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Search')),
        $resize
      );
    }

    return $result;
  }

  /**
  * generate panel to filter files by tags
  *
  * @return string $result tags panel listview xml
  */
  function getTagsPanel() {
    $result = '';
    if (isset($this->sessionParams['panel_state']['tags'])
        && $this->sessionParams['panel_state']['tags'] == 'open') {
      $resize = sprintf(
        ' minimize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'close_panel', 'panel' => 'tags'))
        )
      );
      $tagsXML = $this->getTagTreeXML();
    } else {
      $resize = sprintf(
        ' maximize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'open_panel', 'panel' => 'tags'))
        )
      );
      $tagsXML = '';
    }

    $result .= sprintf(
      '<listview title="%s" %s>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Tags')),
      $resize
    );
    $result .= '<items>'.LF;
    $result .= $tagsXML;
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * get an instance of base_tags
  *
  * @param base_tags $setInstance
  * @return base_tags instance
  */
  public function getBaseTags(base_tags $setInstance = NULL) {
    static $obj;
    if (is_object($setInstance) && $setInstance instanceof base_tags) {
      $obj = $setInstance;
    } elseif (!is_object($obj)) {
      $obj = new base_tags;
    }
    return $obj;
  }

  /**
  * generate tag categories tree xml
  *
  * @uses papaya_mediadb::getXMLCategorySubTree()
  * @return string $result tag categories xml
  */
  function getTagTreeXML() {
    $result = '';
    $tagObj = $this->getBaseTags();
    $images = $this->papaya()->images;
    if ($this->params['cat_id'] > 0) {
      $this->category = $tagObj->getCategory(
        $this->params['cat_id'],
        $this->papaya()->administrationLanguage->id
      );
      $parentPath = \Papaya\Utility\Arrays::decodeIdList($this->category['parent_path']);
      array_pop($parentPath);
      $preParentId = (int)array_pop($parentPath);
      $catIds = array(
        $this->category['parent_id'],
        $this->category['category_id'],
        $preParentId
      );
    } else {
      $preParentId = 0;
      $catIds = array(0);
    }
    if (!isset($this->category) ||
        $this->category['parent_id'] == 0 ||
        $preParentId == 0) {
      $selected = ($this->params['cat_id'] == 0) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<listitem title="%s" href="%s" image="%s" node="empty" %s/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Base')),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cat_id' => 0))
        ),
        papaya_strings::escapeHTMLChars($images['items-folder']),
        $selected
      );
    }
    if ($preParentId != 0) {
      $result .= sprintf(
        '<listitem title="%s" href="%s" image="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Parent category')),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cat_id' => $preParentId))
        ),
        papaya_strings::escapeHTMLChars($images['actions-go-superior'])
      );
    }
    if (isset($this->sessionParams['open_categories'])) {
      $catIds = array_merge($catIds, array_keys($this->sessionParams['open_categories']));
    }
    $this->categories = $tagObj->getSubCategories(
      $catIds,
      $this->papaya()->administrationLanguage->id
    );
    $tagObj->loadCategoryCounts($this->categories);
    foreach ($this->categories as $categoryId => $category) {
      $this->categoryTree[(int)$category['parent_id']][] = $categoryId;
    }
    $result .= $this->getXMLCategorySubTree((int)$preParentId, 1);
    return $result;
  }

  /**
  * generate tag categories tree branch xml
  *
  * @uses papaya_mediadb::getXMLCategoryEntry()
  * @access private
  * @param integer $parentId tag category id to create subtree for
  * @param integer $indent current indent depth
  * @return string $result tag categories subtree xml
  */
  function getXMLCategorySubTree($parentId, $indent) {
    $result = '';
    if (isset($this->categoryTree[$parentId]) &&
        is_array($this->categoryTree[$parentId]) &&
        (isset($this->sessionParams['open_categories'][$parentId]) || ($parentId == 0))) {
      foreach ($this->categoryTree[$parentId] as $categoryId) {
        $result .= $this->getXMLCategoryEntry($categoryId, $indent);
      }
    }
    return $result;
  }

  /**
  * generate tag categories tree listitem xml
  *
  * @access private
  * @param integer $categoryId tag category id
  * @param integer $indent current indent depth
  * @return string $result tag categories entry listitem xml
  */
  function getXMLCategoryEntry($categoryId, $indent) {
    $result = '';
    $images = $this->papaya()->images;
    if (isset($this->categories[$categoryId]) && is_array($this->categories[$categoryId])) {
      if (isset($this->sessionParams['open_categories'][$categoryId]) &&
          isset($this->categories[$categoryId]['CATEG_COUNT']) &&
          $this->categories[$categoryId]['CATEG_COUNT'] > 0) {
        $opened = TRUE;
      } else {
        $opened = FALSE;
      }
      if (empty($this->categories[$categoryId]['CATEG_COUNT']) ||
          $this->categories[$categoryId]['CATEG_COUNT'] < 1) {
        $node = ' node="empty"';
        if (isset($this->sessionParams['move_category']) &&
           $categoryId == (int)$this->sessionParams['move_category']) {
          $imageIndex = 'items-folder'; // 178;
        } else {
          $imageIndex = 'items-folder'; // 56;
        }
      } elseif ($opened) {
        $nodeHref = $this->getLink(
          array('cmd' => 'close_category', 'cat_close_id' => (int)$categoryId)
        );
        $node = sprintf(' node="open" nhref="%s"', $nodeHref);
        if (isset($this->sessionParams['move_category']) &&
            $categoryId == (int)$this->sessionParams['move_category']) {
          $imageIndex = 'items-folder'; // 179;
        } else {
          $imageIndex = 'items-folder'; // 57;
        }
      } else {
        $nodeHref = $this->getLink(
          array('cmd' => 'open_category', 'cat_open_id' => (int)$categoryId)
        );
        $node = sprintf(' node="close" nhref="%s"', $nodeHref);
        if (isset($this->sessionParams['move_category']) &&
            $categoryId == (int)$this->sessionParams['move_category']) {
          $imageIndex = 'items-folder'; // 178;
        } else {
          $imageIndex = 'items-folder'; // 56;
        }
      }
      if (!isset($this->categories[$categoryId]) ||
          !isset($this->categories[$categoryId]['category_title']) ||
          $this->categories[$categoryId]['category_title'] == "") {
        if (isset($this->alternativeCategoryNames) &&
            is_array($this->alternativeCategoryNames) &&
            isset($this->alternativeCategoryNames[$categoryId])) {
          $title = '['.$this->alternativeCategoryNames[$categoryId].']';
        } else {
          $title = $this->_gt('No Title');
        }
      } else {
        $title = $this->categories[$categoryId]['category_title'];
      }
      if (isset($this->params) && isset($this->params['cat_id'])) {
        if (!isset($this->params['tag_id'])) {
          $selected = ($this->params['cat_id'] == $categoryId) ? ' selected="selected"' : '';
        } else {
          $selected = '';
        }
        $tagResult = $this->getTagListXML($categoryId, $indent + 1);
      } else {
        $selected = '';
        $tagResult = '';
      }
      if (isset($this->params['cat_id']) && $this->params['cat_id'] == $categoryId) {
        if (isset($this->sessionParams['move_category']) &&
            $categoryId == (int)$this->sessionParams['move_category']) {
          $imageIndex = 'items-folder'; // 179;
        } else {
          $imageIndex = 'items-folder'; // 57;
        }
      }
      $result .= sprintf(
        '<listitem href="%s" title="%s" indent="%d" image="%s" %s %s>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cat_id' => (int)$categoryId))
        ),
        papaya_strings::escapeHTMLChars($title),
        $indent,
        papaya_strings::escapeHTMLChars($images[$imageIndex]),
        $node,
        $selected
      );
      $result .= '</listitem>'.LF;
      $result .= $tagResult;
      $result .= $this->getXMLCategorySubTree($categoryId, $indent + 1);
    }
    return $result;
  }

  /**
  * generate tags listitems
  *
  * @param integer $categoryId tag category id
  * @param integer $indent current indent depth
  * @return string $result tag listitem xml
  */
  function getTagListXML($categoryId, $indent) {
    $result = '';
    $tags = $this->getBaseTags()->getTagsByCategory(
      $categoryId,
      $this->papaya()->administrationLanguage->id
    );
    $images = $this->papaya()->images;
    if (is_array($tags) && count($tags) > 0) {
      foreach ($tags[$categoryId] as $tagId => $tag) {
        if (isset($this->params['tag_id']) && $tagId == $this->params['tag_id']) {
          $selected = ' selected="%s"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem image="%s" title="%s" href="%s" indent="%s" %s/>'.LF,
          papaya_strings::escapeHTMLChars($images['items-tag']),
          papaya_strings::escapeHTMLChars($tag['tag_title']),
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('filter_mode' => 'tags', 'tag_id' => $tagId))
          ),
          $indent,
          $selected
        );
      }
    }
    return $result;
  }

  /**
  * generate folders panel
  *
  * @return string $result folders panel listview xml
  */
  function getFolderPanel() {
    $result = '';
    $images = $this->papaya()->images;
    if (!(
          isset($this->sessionParams['panel_state']['folders']) &&
          $this->sessionParams['panel_state']['folders'] == 'open'
        )) {
      $result .= sprintf(
        '<listview title="%s" maximize="%s" />'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Folders')),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'open_panel', 'panel' => 'folders'))
        )
      );
      return $result;
    } else {
      $foldersList = $this->getFoldersListXML();
      $result .= sprintf(
        '<listview title="%s" minimize="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Folders')),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'close_panel', 'panel' => 'folders'))
        )
      );
      $result .= '<items>'.LF;
      if (empty($this->params['folder_id'])) {
        $selected = ' selected="selected"';
        $folderIcon = 'status-folder-open';
      } else {
        $selected = '';
        $folderIcon = 'items-folder';
      }
      $result .= sprintf(
        '<listitem node="open" nhref="#" title="%s" image="%s" href="%s#mediaFilesList"'.
        ' %s><subitem /></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Desktop')),
        papaya_strings::escapeHTMLChars($images[$folderIcon]),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('folder_id' => 0, 'cmd' => 'add_folder'))
        ),
        $selected
      );
      if ($foldersList) {
        $result .= $foldersList;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * generate clipboard listview
  *
  * @return string $result clipboard panel listview xml
  */
  function getClipboardPanel() {
    $result = '';
    $folders = $this->getSubFolders($this->papaya()->administrationLanguage->id, -1, TRUE);
    $files = $this->getFiles(
      -1, $this->params['limit_clipboard'] - count($folders), $this->params['offset_clipboard']
    );
    if ((is_array($folders) && count($folders) > 0) ||
        (is_array($files) && count($files) > 0)) {

      if (isset($this->sessionParams['panel_state']['clipboard'])
          && $this->sessionParams['panel_state']['clipboard'] == 'open') {
        $images = $this->papaya()->images;
        $result .= sprintf(
          '<listview title="%s" minimize="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Clipboard')),
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cmd' => 'close_panel', 'panel' => 'clipboard'))
          )
        );
        $foldersResult = '';
        foreach ($folders as $folderId => $folder) {
          $foldersResult .= sprintf(
            '<listitem image="%s" title="%s">'.LF,
            papaya_strings::escapeHTMLChars($images['items-folder']),
            papaya_strings::escapeHTMLChars($folder['folder_name'])
          );
          $foldersResult .= '<subitem />'.LF;
          $foldersResult .= sprintf(
            '<subitem align="right"><a href="%s"><glyph src="%s" /></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array(
                  'cmd' => 'paste_folder',
                  'folder_id' => $folderId,
                  'target_folder_id' => $this->params['folder_id']
                )
              )
            ),
            papaya_strings::escapeHTMLChars($images['actions-edit-paste'])
          );
          $foldersResult .= '</listitem>'.LF;
        }

        $result .= $this->getFilesPagingBar(
          $this->absCount,
          $this->params['limit_clipboard'],
          $this->params['offset_clipboard'],
          'clipboard'
        );

        $result .= '<items>'.LF;
        $result .= $foldersResult;

        if (is_array($files) && count($files) > 0) {
          foreach ($files as $fileId => $file) {
            $editLink = $this->getLink(array('cmd' => 'edit_file', 'file_id' => $fileId));
            if ($file['mimetype_icon'] != '') {
              $icon = $file['mimetype_icon'];
            } else {
              $icon = $this->defaultTypeIcon;
            }
            $selected = (isset($this->params['file_id']) && $this->params['file_id'] == $fileId)
               ? ' selected="selected"' : '';
            if (strlen($file['file_name']) > 20) {
              $hint = sprintf(
                ' hint="%s"',
                papaya_strings::escapeHTMLChars($file['file_name'])
              );
              $name = papaya_strings::escapeHTMLChars(
                papaya_strings::truncate(
                  $file['file_name'],
                  20,
                  ' ',
                  '...'.substr($file['file_name'], -7)
                )
              );
            } else {
              $hint = '';
              $name = $file['file_name'];
            }
            $result .= sprintf(
              '<listitem title="%s" %s image="%s" href="%s" %s>'.LF,
              papaya_strings::escapeHTMLChars($name),
              $hint,
              papaya_strings::escapeHTMLChars($this->mimeObj->getMimeTypeIcon($icon)),
              papaya_strings::escapeHTMLChars($editLink),
              $selected
            );
            $result .= sprintf(
              '<subitem align="right">%s</subitem>'.LF,
              papaya_strings::escapeHTMLChars($this->formatFileSize($file['file_size']))
            );
            if ($this->isFolderSelected()) {
              $result .= sprintf(
                '<subitem align="right"><a href="%s" title="%s"><glyph src="%s"/></a></subitem>'.LF,
                  papaya_strings::escapeHTMLChars(
                  $this->getLink(
                    array(
                      'cmd' => 'paste_file',
                      'file_id' => $fileId,
                      'folder_id' => $this->params['folder_id']
                    )
                  )
                ),
                papaya_strings::escapeHTMLChars($this->_gt('Paste file to the current folder')),
                papaya_strings::escapeHTMLChars($images['actions-edit-paste'])
              );
            }
            $result .= '</listitem>'.LF;
          }
          if ($this->isFolderSelected()) {
            $result .= sprintf(
              '<listitem title="%s" href="%s" hint="%s">'.LF,
              papaya_strings::escapeHTMLChars($this->_gt('Paste all files to the current folder')),
              papaya_strings::escapeHTMLChars(
                $this->getLink(array('cmd' => 'paste_all'))
              ),
              papaya_strings::escapeHTMLChars(
                $this->_gt('Paste all files from the clipboard to the current folder.')
              )
            );
            $result .= '<subitem />'.LF;
            $result .= sprintf(
              '<subitem align="right"><a title="%s" href="%s"><glyph src="%s" /></a></subitem>'.LF,
              papaya_strings::escapeHTMLChars(
                $this->_gt('Paste all files from the clipboard to the current folder.')
              ),
              papaya_strings::escapeHTMLChars(
                $this->getLink(array('cmd' => 'paste_all'))
              ),
              papaya_strings::escapeHTMLChars($images['actions-edit-paste'])
            );
            $result .= '</listitem>'.LF;
          }
        }
        $result .= '</items>'.LF;
        $result .= '</listview>'.LF;
      } else {
        $result .= sprintf(
          '<listview title="%s" maximize="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Clipboard')),
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cmd' => 'open_panel', 'panel' => 'clipboard'))
          )
        );
        $result .= '</listview>'.LF;
      }
    }
    return $result;
  }

  /**
  * generate files toolbar xml
  *
  * @return string $result files toolbar xml
  */
  function getFilesToolbar() {
    $this->fileToolBar = new base_btnbuilder;
    $this->fileToolBar->images = $this->papaya()->images;
    if (isset($this->currentFile) &&
        is_array($this->currentFile) &&
        count($this->currentFile) > 0 &&
        $this->currentFile['folder_id'] >= 0) {
      $this->fileToolBar->addButton(
        'Properties',
        $this->getLink(
          array(
            'cmd' => 'edit_file',
            'file_id' => $this->params['file_id']
          )
         ),
         'categories-properties',
        'Show/Edit file properties',
        ($this->params['cmd'] == 'edit_file')
      );
      $this->fileToolBar->addButton(
        'Tags',
        $this->getLink(
          array(
            'cmd' => 'file_tags', 'file_id' => $this->params['file_id']
          )
        ),
        'items-tag',
        'Edit file tags',
        ($this->params['cmd'] == 'file_tags')
      );
      $this->fileToolBar->addSeparator();
      if ($this->currentFile['current_version_id'] > 1) {
        $this->fileToolBar->addButton(
          'Versions',
          $this->getLink(
            array(
              'cmd' => 'file_versions', 'file_id' => $this->params['file_id']
            )
          ),
          'items-time',
          'Show file versions',
          ($this->params['cmd'] == 'file_versions')
        );
      }
      if (
        (isset($this->currentFile['DERIVED']) && $this->currentFile['DERIVED']) ||
        (isset($this->currentFile['DERIVATIONS']) && count($this->currentFile['DERIVATIONS']) > 0)
      ) {
        $this->fileToolBar->addButton(
          'Derivations',
          $this->getLink(
            array(
              'cmd' => 'file_derivations', 'file_id' => $this->params['file_id']
            )
          ),
          'status-file-inherited',
          'Show file derivations',
          ($this->params['cmd'] == 'file_derivations')
        );
      }
      $this->fileToolBar->addSeparator();
      $this->fileToolBar->addButton(
        'Papaya tag',
        $this->getLink(
          array(
            'cmd' => 'papaya_tag', 'file_id' => $this->params['file_id']
          )
        ),
        'items-dialog',
        'Create papaya tag',
        ($this->params['cmd'] == 'papaya_tag')
      );
      return sprintf('<toolbar>%s</toolbar>'.LF, $this->fileToolBar->getXML());
    }
    return '';
  }

  /**
  * generate files listview xml
  *
  * @return string $result files panel listview xml
  */
  function getFilesPanel() {
    $result = '';
    $title = '';
    if ($this->params['filter_mode'] == 'tags' && isset($this->params['tag_id'])) {
      $files = $this->getFilesByTag(
        $this->params['tag_id'],
        $this->params['limit_files'],
        $this->params['offset_files'],
        $this->params['file_sort'],
        $this->params['file_order']
      );
      $tag = $this->getBaseTags()->getTags(
        $this->params['tag_id'],
        $this->papaya()->administrationLanguage->id
      );
      $title = sprintf($this->_gt('Files by tag "%s"'), $tag[$this->params['tag_id']]['tag_title']);
    } elseif ($this->params['filter_mode'] == 'search') {
      if (isset($this->params['search']['q'])) {
        $this->params['search']['q'] = trim($this->params['search']['q']);
      } else {
        $this->params['search']['q'] = '';
      }
      $files = $this->findFiles(
        $this->params['search'],
        $this->params['limit_files'],
        $this->params['offset_files'],
        $this->params['file_sort'],
        $this->params['file_order']
      );
      $title = $this->_gt('Files for search');
    } elseif (isset($this->params['folder_id'])) {
      $files = $this->getFiles(
        $this->params['folder_id'],
        $this->params['limit_files'],
        $this->params['offset_files'],
        $this->params['file_sort'],
        $this->params['file_order']
      );
      if ($this->params['folder_id'] == 0) {
        $title = $this->_gt('Desktop');
      } else {
        $title = $this->currentFolder['folder_name'];
      }
    }

    if (isset($files) && is_array($files) && count($files) > 0) {
      $viewMode = 'list';
      if (isset($this->params['mode_view'])) {
        switch ($this->params['mode_view']) {
        case 'tile' :
        case 'list' :
        case 'thumbs' :
          $viewMode = $this->params['mode_view'];
          break;
        }
      }
      $result .= sprintf(
        '<dialog title="%s" action="%s#top" id="mediaFilesList">'.LF,
        papaya_strings::escapeHTMLChars($title),
        papaya_strings::escapeHTMLChars($this->getBaseLink())
      );
      $result .= sprintf(
        '<listview mode="%s">'.LF,
        papaya_strings::escapeHTMLChars($viewMode)
      );

      $result .= $this->getFilesPagingBar(
        $this->absCount, $this->params['limit_files'], $this->params['offset_files']
      );

      if (is_array($files) && count($files) > 0) {
        if ($this->params['file_sort'] == 'date') {
          if ($this->params['file_order'] == 'asc') {
            $sortLinkDateParams = array('file_sort' => 'date', 'file_order' => 'desc');
            $sortImageDate = 'asc';
          } else {
            $sortLinkDateParams = array('file_sort' => 'date', 'file_order' => 'asc');
            $sortImageDate = 'desc';
          }
          $sortImageName = 'none';
          $sortImageSize = 'none';
          $sortLinkNameParams = array('file_sort' => 'name', 'file_order' => 'asc');
          $sortLinkSizeParams = array('file_sort' => 'size', 'file_order' => 'asc');
        } elseif ($this->params['file_sort'] == 'name') {
          if ($this->params['file_order'] == 'asc') {
            $sortLinkNameParams = array('file_sort' => 'name', 'file_order' => 'desc');
            $sortImageName = 'asc';
          } else {
            $sortLinkNameParams = array('file_sort' => 'name', 'file_order' => 'asc');
            $sortImageName = 'desc';
          }
          $sortImageDate = 'none';
          $sortImageSize = 'none';
          $sortLinkDateParams = array('file_sort' => 'date', 'file_order' => 'asc');
          $sortLinkSizeParams = array('file_sort' => 'size', 'file_order' => 'asc');
        } else {
          if ($this->params['file_order'] == 'asc') {
            $sortLinkSizeParams = array('file_sort' => 'size', 'file_order' => 'desc');
            $sortImageSize = 'asc';
          } else {
            $sortLinkSizeParams = array('file_sort' => 'size', 'file_order' => 'asc');
            $sortImageSize = 'desc';
          }
          $sortImageDate = 'none';
          $sortImageName = 'none';
          $sortLinkDateParams = array('file_sort' => 'date', 'file_order' => 'asc');
          $sortLinkNameParams = array('file_sort' => 'name', 'file_order' => 'asc');
        }

        $result .= '<cols>'.LF;
        if ($viewMode != 'tile') {
          $result .= '<col />'.LF;
          $result .= '<col />'.LF;
        }
        $result .= sprintf(
          '<col href="%s" sort="%s">%s</col>'.LF,
          papaya_strings::escapeHTMLChars($this->getLink($sortLinkNameParams)),
          papaya_strings::escapeHTMLChars($sortImageName),
          papaya_strings::escapeHTMLChars($this->_gt('Filename'))
        );
        $result .= sprintf(
          '<col href="%s" sort="%s">%s</col>'.LF,
          papaya_strings::escapeHTMLChars($this->getLink($sortLinkDateParams)),
          papaya_strings::escapeHTMLChars($sortImageDate),
          papaya_strings::escapeHTMLChars($this->_gt('Uploaded'))
        );
        $result .= sprintf(
          '<col href="%s" sort="%s">%s</col>'.LF,
          papaya_strings::escapeHTMLChars($this->getLink($sortLinkSizeParams)),
          papaya_strings::escapeHTMLChars($sortImageSize),
          papaya_strings::escapeHTMLChars($this->_gt('Filesize'))
        );
        $result .= '</cols>'.LF;
        $result .= '<items>'.LF;
        foreach ($files as $fileId => $file) {
          $editLink = $this->getLink(array('cmd' => 'edit_file', 'file_id' => $fileId));
          if ($file['mimetype_icon'] != '') {
            $icon = $file['mimetype_icon'];
          } else {
            $icon = $this->defaultTypeIcon;
          }
          $selected = (isset($this->params['file_id']) && $this->params['file_id'] == $fileId)
            ? ' selected="selected"' : '';
          if (strlen($file['file_name']) > 30) {
            $name = papaya_strings::escapeHTMLChars(
              papaya_strings::truncate(
                $file['file_name'], 30, '', '...'.substr($file['file_name'], -7)
              )
            );
          } else {
            $name = papaya_strings::escapeHTMLChars($file['file_name']);
          }
          $hint = papaya_strings::escapeHTMLChars($file['file_name']);
          if (in_array($viewMode, array('tile', 'thumbs'))) {
            $icon = $this->mimeObj->getMimeTypeIcon($icon);
            if (in_array($file['mimetype'], $this->imageMimeTypes)) {
              $thumbnail = new base_thumbnail;
              $thumbFile = $thumbnail->getThumbnail(
                $file['file_id'],
                $file['current_version_id'],
                ($viewMode == 'thumbs') ? 100 : 48,
                ($viewMode == 'thumbs') ? 80 : 48
              );
              if ($thumbFile) {
                $icon = '../'.$thumbFile;
              }
            } elseif ($file['mimetype'] === 'image/svg+xml') {
              $icon = $this->getWebMediaLink($file['file_id']);
            }
            $result .= sprintf(
              '<listitem href="%s" image="%s" title="%s" subtitle="%s" hint="%s" %s>'.LF,
              papaya_strings::escapeHTMLChars($editLink),
              papaya_strings::escapeHTMLChars($icon),
              papaya_strings::escapeHTMLChars($name),
              date('Y-m-d H:i', $file['file_date']),
              papaya_strings::escapeHTMLChars($hint),
              $selected
            );
            $checked = (isset($this->params['batch_files'][$fileId])) ? ' checked="checked"' : '';
            $idName = 'mdb_batch_files_'.$fileId;
            $result .= sprintf(
              '<subitem align="center"><input type="checkbox" name="%s[batch_files][%s]"'.
              ' id="%s" value="1" %s/></subitem>'.LF,
              papaya_strings::escapeHTMLChars($this->paramName),
              papaya_strings::escapeHTMLChars($fileId),
              papaya_strings::escapeHTMLChars($idName),
              $checked
            );
            $result .= '</listitem>'.LF;
          } else {
            $result .= sprintf(
              '<listitem href="%s" image="%s" %s>'.LF,
              papaya_strings::escapeHTMLChars($editLink),
              papaya_strings::escapeHTMLChars($this->mimeObj->getMimeTypeIcon($icon)),
              $selected
            );
            $checked = (isset($this->params['batch_files'][$fileId])) ? ' checked="checked"' : '';
            $idName = 'mdb_batch_files_'.$fileId;
            $result .= sprintf(
              '<subitem align="center"><input type="checkbox" name="%s[batch_files][%s]"'.
              ' id="%s" value="1" %s/></subitem>'.LF,
              papaya_strings::escapeHTMLChars($this->paramName),
              papaya_strings::escapeHTMLChars($fileId),
              papaya_strings::escapeHTMLChars($idName),
              $checked
            );
            $result .= sprintf(
              '<subitem overflow="hidden"><a href="%s" title="%s"'.
              ' style="display: block; overflow: hidden;">%s</a></subitem>'.LF,
              papaya_strings::escapeHTMLChars($editLink),
              papaya_strings::escapeHTMLChars($hint),
              papaya_strings::escapeHTMLChars($name)
            );
            $result .= sprintf(
              '<subitem align="center" wrap="nowrap">%s</subitem>'.LF,
              date('Y-m-d H:i', $file['file_date'])
            );
            $result .= sprintf(
                '<subitem align="center" wrap="nowrap">%s</subitem>'.LF,
                papaya_strings::escapeHTMLChars($this->formatFileSize($file['file_size']))
            );
            $result .= '</listitem>'.LF;
          }
        }
        $result .= '</items>'.LF;
      }
      $result .= '</listview>'.LF;
      $images = $this->papaya()->images;
      if ($viewMode != 'thumbs') {
        $result .= sprintf(
          '<dlgbutton type="button" caption="%s" hint="%s"'.
            ' onclick="invertCheckBoxes(this);" image="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Invert Selection')),
          papaya_strings::escapeHTMLChars($this->_gt('Invert Selection')),
          papaya_strings::escapeHTMLChars($images['status-node-checked'])
        );
        $result .= sprintf(
          '<dlgbutton name="%s[batch_cmd][cut]" value="1" caption="%s" hint="%s" image="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($this->_gt('Cut')),
          papaya_strings::escapeHTMLChars($this->_gt('Cut')),
          papaya_strings::escapeHTMLChars($images['actions-edit-cut'])
        );
        $result .= sprintf(
          '<dlgbutton name="%s[batch_cmd][move]" value="1" caption="%s" hint="%s" image="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($this->_gt('Move')),
          papaya_strings::escapeHTMLChars($this->_gt('Move')),
          papaya_strings::escapeHTMLChars($images['actions-page-move'])
        );
        $result .= sprintf(
          '<dlgbutton name="%s[batch_cmd][delete]" value="1" caption="%s"'.
          ' hint="%s" image="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($this->_gt('Delete')),
          papaya_strings::escapeHTMLChars($this->_gt('Delete')),
          papaya_strings::escapeHTMLChars($images['places-trash'])
        );
        $result .= sprintf(
          '<dlgbutton name="%s[batch_cmd][tags]" value="1" caption="%s" hint="%s" image="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($this->_gt('Tag')),
          papaya_strings::escapeHTMLChars($this->_gt('Tag')),
          papaya_strings::escapeHTMLChars($images['items-tag'])
        );
      }
      $result .= '</dialog>'.LF;
    }
    return $result;
  }

  /**
   * generate paging bar for files
   *
   * @param integer $absCount total number of files
   * @param integer $step
   * @param integer $offset
   * @param string $offsetSuffix
   * @return string $result paging buttons xml
   */
  function getFilesPagingBar($absCount, $step, $offset, $offsetSuffix = 'files') {
    $offsetName = 'offset_'.$offsetSuffix;
    $limitName = 'limit_'.$offsetSuffix;
    $result = '<buttons>'.LF;
    if ($absCount > 10) {
      $result .= papaya_paging_buttons::getPagingButtons(
        $this,
        array(),
        $offset,
        $step,
        $this->absCount,
        9,
        $offsetName,
        'left'
      );
      $result .= papaya_paging_buttons::getButtons(
        $this,
        array('cmd' => $this->params['cmd']),
        $this->pagingSteps,
        $step,
        $limitName,
        'right'
      );
    }
    $images = $this->papaya()->images;
    $result .= papaya_paging_buttons::getButtons(
      $this,
      array('cmd' => $this->params['cmd']),
      array(
        'list' => array(
          $this->_gt('List'), $images['categories-view-list']
        ),
        'tile' => array(
          $this->_gt('Tiles'), $images['categories-view-tiles']
        ),
        'thumbs' => array(
          $this->_gt('Thumbnails'), $images['categories-view-icons']
        )
      ),
      empty($this->params['mode_view']) ? 'list' : $this->params['mode_view'],
      'mode_view',
      'right'
    );
    $result .= '</buttons>'.LF;
    return $result;
  }

  /**
  * generate panel of file properties including file preview
  *
  * @return string $result file properties dialog xml
  */
  function getFilePropertiesPanel() {
    $result = '';
    $images = $this->papaya()->images;
    if (isset($this->params['file_id']) && $this->params['file_id'] != '') {
      if (isset($this->params['cmd']) && $this->params['cmd'] == 'file_versions' &&
          isset($this->params['version_id'])) {
        $title = $this->_gt('File version properties');
        $file = $this->getFile($this->params['file_id'], $this->params['version_id']);
        $versionId = $this->params['version_id'];
      } elseif (is_array($this->currentFile) && count($this->currentFile) > 0) {
        $file = $this->currentFile;
        $title = $this->_gt('File properties');
        $versionId = $file['current_version_id'];
        if ((!(isset($this->dialog) && is_object($this->dialog))) &&
            $this->checkActionPermission('edit_file', $this->params['file_id']) &&
            !$this->params['cmd'] == 'file_versions') {
          $this->initializeFileEditDialog();
        }
      } else {
        return '';
      }
      if (is_array($file) && count($file) > 0) {
        if ($file['folder_id'] > 0) {
          $folder = $this->getFolder($file['folder_id']);
          $defaultLanguage = $this->papaya()->options['PAPAYA_CONTENT_LANGUAGE'];
          if (isset($folder[$this->papaya()->administrationLanguage->id])) {
            $folderName = $folder[$this->papaya()->administrationLanguage->id]['folder_name'];
          } elseif (isset($folder[$defaultLanguage])) {
            $folderName = $folder[$defaultLanguage]['folder_name'];
          }
        } elseif ($file['folder_id'] == 0) {
          $folderName = $this->_gt('Desktop');
        } elseif ($file['folder_id'] == -1) {
          $folderName = $this->_gt('Clipboard');
        }
        $result .= sprintf(
          '<dialog title="%s">'.LF,
          papaya_strings::escapeHTMLChars($title)
        );
        $result .= '<lines><line>'.LF;
        $result .= '<layout>'.LF;
        $result .= '<row>'.LF;
        $result .= sprintf(
          '<cell width="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->filePreviewWidth)
        );
        $file['version_id'] = $versionId;
        $result .= $this->getFilePreview(
          $file,
          $this->getWebMediaLink(
            $file['file_id'].'v'.$versionId,
            'media',
            $file['file_name'],
            $file['mimetype_ext']
          )
        );
        $result .= '</cell>'.LF;
        $result .= '<cell>&#160;</cell>'.LF;
        $result .= '<cell>'.LF;
        $result .= '<listview>'.LF;
        $result .= '<items>'.LF;
        $mimeType = $this->mimeObj->getMimeTypeByExtension(
          $this->getFileExtension($file['file_name'])
        );
        if (is_array($mimeType) &&
            (
             (isset($mimeType['mimetype']) && $file['mimetype'] != $mimeType['mimetype']) ||
              count($mimeType) == 0
            ) &&
            $versionId == $this->currentFile['current_version_id']) {
          $info = sprintf(
            '<a href="%s" title="%s"><glyph src="%s" /></a>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('cmd' => 'fix_file_ext', 'file_id' => $file['file_id']))
            ),
            papaya_strings::escapeHTMLChars(
              $this->_gt('Extension does not match mimetype of the file. Click here to fix this.')
            ),
            papaya_strings::escapeHTMLChars($images['status-dialog-warning'])
          );
        } else {
          $info = '';
        }
        $fileName = papaya_strings::escapeHTMLChars(
          papaya_strings::truncate(
            $file['file_name'], 40, ' ', '...'.substr($file['file_name'], -7)
          )
        );
        if (!is_file($file['FILENAME']) || !is_readable($file['FILENAME'])) {
          $this->addMSG(MSG_WARNING, $this->_gt('Binary file not found or not accessible.'));
        }
        $result .= sprintf(
          '<listitem title="%s"><subitem>%s</subitem></listitem>',
          papaya_strings::escapeHTMLChars($this->_gt('File Id')),
          papaya_strings::escapeHTMLChars($file['file_id'])
        );
        $result .= sprintf(
          '<listitem title="%s"><subitem>%s %s</subitem></listitem>',
          papaya_strings::escapeHTMLChars($this->_gt('Filename')),
          papaya_strings::escapeHTMLChars($fileName),
          $info
        );
        $result .= sprintf(
          '<listitem title="%s"><subitem>%s</subitem></listitem>',
          papaya_strings::escapeHTMLChars($this->_gt('Filesize')),
          papaya_strings::escapeHTMLChars($this->formatFileSize($file['file_size']))
        );
        $result .= sprintf(
          '<listitem title="%s"><subitem>%s</subitem></listitem>',
          papaya_strings::escapeHTMLChars($this->_gt('Uploaded')),
          date('Y-m-d H:i:s', $file['file_date'])
        );
        $result .= sprintf(
          '<listitem title="%s"><subitem>%s</subitem></listitem>',
          papaya_strings::escapeHTMLChars($this->_gt('Created')),
          papaya_strings::escapeHTMLChars($file['file_created'])
        );
        $result .= sprintf(
          '<listitem title="%s"><subitem>%s</subitem></listitem>',
          papaya_strings::escapeHTMLChars($this->_gt('Filetype')),
          papaya_strings::escapeHTMLChars($file['mimetype'])
        );
        $result .= sprintf(
          '<listitem title="%s"><subitem>#%s</subitem></listitem>',
          papaya_strings::escapeHTMLChars($this->_gt('Fileversion')),
          papaya_strings::escapeHTMLChars($versionId)
        );
        if (isset($file['WIDTH']) && $file['WIDTH'] > 0 &&
            isset($file['HEIGHT']) && $file['HEIGHT'] > 0) {
          $result .= sprintf(
            '<listitem title="%s"><subitem>%s %s</subitem></listitem>',
            papaya_strings::escapeHTMLChars($this->_gt('Image width')),
            papaya_strings::escapeHTMLChars($file['WIDTH']),
            papaya_strings::escapeHTMLChars($this->_gt('Pixels'))
          );
          $result .= sprintf(
            '<listitem title="%s"><subitem>%s %s</subitem></listitem>',
            papaya_strings::escapeHTMLChars($this->_gt('Image height')),
            papaya_strings::escapeHTMLChars($file['HEIGHT']),
            papaya_strings::escapeHTMLChars($this->_gt('Pixels'))
          );
        }
        // don't show that the clipboard has folder id -1
        if (!isset($folderName)) {
          $folderName = $this->_gt('No title');
        }
        if ($file['folder_id'] > 0) {
          $folderTitle = sprintf('%s (#%d)', $folderName, $file['folder_id']);
        } elseif (isset($folderName)) {
          $folderTitle = $folderName;
        } else {
          $folderTitle = '';
        }
        $result .= sprintf(
          '<listitem title="%s"><subitem><a href="%s">%s</a></subitem></listitem>',
          papaya_strings::escapeHTMLChars($this->_gt('Folder')),
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('folder_id' => $file['folder_id'], 'filter_mode' => 'folder'))
          ),
          papaya_strings::escapeHTMLChars($folderTitle)
        );
        $result .= '</items>'.LF;
        $result .= '</listview>'.LF;
        $result .= '</cell>'.LF;
        $result .= '</row>'.LF;
        $result .= '</layout>'.LF;
        $result .= '</line></lines>'.LF;
        $result .= '</dialog>'.LF;
      }
    }
    return $result;
  }

  /**
   * generate preview for a file
   *
   * hopefully preview of other file formats will be possible to support later on
   * especially svg should be possible when browser support is acceptable
   *
   * @param array $file file data (e.g. result of getFile)
   * @param string $link
   * @return string $result file preview xml
   */
  function getFilePreview($file, $link) {
    $result = '';
    // if it is an image
    if (isset($file['WIDTH']) &&
        isset($file['HEIGHT']) &&
        $file['WIDTH'] > 0 &&
        $file['HEIGHT'] > 0) {
      $vspace = 0;
      if ($file['WIDTH'] < $this->filePreviewWidth) {
        $width = $file['WIDTH'];
      } else {
        $width = $this->filePreviewWidth;
      }
      if ($file['HEIGHT'] < $this->filePreviewHeight) {
        $height = $file['HEIGHT'];
      } else {
        $height = $this->filePreviewHeight;
      }
      $newHeight = $file['HEIGHT'] / $file['WIDTH'] * $width;
      if ($newHeight < $this->filePreviewHeight) {
        $vspace = (int)(($this->filePreviewHeight - ($newHeight)) / 2);
      }

      $tag = sprintf(
        '<papaya:media src="%s" version_id="%d" width="%s" height="%s"'.
        ' resize="max" tspace="%d" bspace="%d" href="%s" target="_blank"/>'.LF,
        papaya_strings::escapeHTMLChars($file['file_id']),
        papaya_strings::escapeHTMLChars($file['version_id']),
        papaya_strings::escapeHTMLChars($width),
        papaya_strings::escapeHTMLChars($height),
        (int)$vspace,
        $vspace + 1,
        papaya_strings::escapeHTMLChars($link)
      );
      $parser = new papaya_parser;
      $result = $parser->parse($tag, NULL);
    } elseif ($this->getFileExtension($file['file_name']) == 'flv' ||
              $file['mimetype_ext'] == 'flv') {
      $result .= $this->getFlvViewer(
        $file['file_id'].'v'.$file['version_id'],
        '100%',
        $this->filePreviewHeight,
        $this->_gt('Flash not found.'),
        NULL,
        array('displayheight' => $this->filePreviewHeight)
      );
    } else {
      if ($file['mimetype_icon'] != '') {
        $icon = $file['mimetype_icon'];
      } else {
        $icon = $this->defaultTypeIcon;
      }
      $iconSize = 48;
      $style = sprintf(
        'margin: %dpx %dpx; width: %dpx; height: %dpx',
        (($this->filePreviewHeight - $iconSize) / 4),
        (($this->filePreviewWidth - $iconSize) / 2),
        $iconSize,
        $iconSize
      );
      $link = $this->getWebMediaLink(
        $file['file_id'].'v'.$file['version_id'],
        'download',
        $file['file_name'],
        $file['mimetype_ext']
      );
      $imageTag = sprintf(
        '<img src="%s" style="%s" alt="%s" />'.LF,
        papaya_strings::escapeHTMLChars($this->mimeObj->getMimeTypeIcon($icon, $iconSize)),
        papaya_strings::escapeHTMLChars($style),
        papaya_strings::escapeHTMLChars($this->_gt('Download file'))
      );
      if (is_file($file['FILENAME']) &&
          is_readable($file['FILENAME'])) {
        $result .= sprintf(
          '<a href="%s">%s</a>'.LF,
          papaya_strings::escapeHTMLChars($link),
          $imageTag
        );
      } else {
        $result .= $imageTag;
      }
    }
    return $result;
  }

  // --------------------------- FOLDER PANEL DETAILS --------------------------

  /**
  * check open folder state and set session params as necessary
  */
  function checkOpenFolders() {
    if (isset($this->params['folder_id']) && isset($this->params['folder_id']) > 0) {
      // make sure a deep link to a folder opens all parent folders
      $folderData = $this->getFolder($this->params['folder_id']);
      $folder = current($folderData);
      $parentIds = \Papaya\Utility\Arrays::decodeIdList((string)$folder['parent_path']);
      foreach ($parentIds as $parentId) {
        if ($parentId > 0) {
          $this->sessionParams['open_folders'][$parentId] = 1;
        }
      }
      if (!isset($this->sessionParams['open_folders'][$this->params['folder_id']])) {
        $this->sessionParams['open_folders'][$this->params['folder_id']] = 1;
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
      }
    }
  }

  /**
  * load folders (fills $this->folders, $this->folderTree, $this->alternativeFolderNames)
  *
  * this methods loads all folders, checks whether they are named in the current
  * language and if not, it tries to loads the papaya_content_language text as
  * an alternative folder name
  */
  function loadFolders() {
    unset($this->folders);
    unset($this->folderTree);
    unset($this->alternativeFolderNames);
    $this->folders = $this->getFolders($this->papaya()->administrationLanguage->id);
    $this->countSubFolders($this->folders);
    foreach ($this->folders as $folderId => $folder) {
      $this->folderTree[$folder['parent_id']][$folderId] = $folderId;
      if (!isset($folder['folder_name'])) {
        $foldersWithoutName[] = $folderId;
      }
    }
    $defaultLanguage = $this->papaya()->options['PAPAYA_CONTENT_LANGUAGE'];
    if ($defaultLanguage != $this->papaya()->administrationLanguage->id &&
        isset($foldersWithoutName) && is_array($foldersWithoutName) &&
        count($foldersWithoutName) > 0) {
      $this->alternativeFolderNames = $this->getFolders(
        $defaultLanguage, $foldersWithoutName
      );
    }
  }

  /**
  * generate folders listview xml
  *
  * @uses papaya_mediadb::getFolderSubTreeXML()
  * @return string folder listview xml
  */
  function getFoldersListXML() {
    $this->loadFolders();
    if (isset($this->folders) && is_array($this->folders) && count($this->folders) > 0) {
      return $this->getFoldersSubTreeXML(0, 1);
    }
    return FALSE;
  }

  /**
  * generate folder listview subtree xml
  *
  * @access private
  * @uses papaya_mediadb::getFolderEntryXML()
  * @param integer $parentId parent folder id to generate subtree of
  * @param integer $indent current indent depth
  * @return string $result folder subtree xml
  */
  function getFoldersSubTreeXML($parentId, $indent) {
    $result = '';
    if (isset($this->folderTree[$parentId]) &&
        is_array($this->folderTree[$parentId]) &&
        count($this->folderTree[$parentId]) > 0 &&
        (isset($this->sessionParams['open_folders'][$parentId]) || $parentId == 0)) {
      foreach ($this->folderTree[$parentId] as $folderId) {
        $this->validateAncestors($folderId, TRUE);
        $result .= $this->getFolderEntryXML($folderId, $indent);
      }
    }
    return $result;
  }

  /**
  * generate folder entry listitem xml
  *
  * @access private
  * @param integer $folderId folder id to generate entry of
  * @param integer $indent current indent depth
  * @return string $result folder entry xml
  */
  function getFolderEntryXML($folderId, $indent) {
    $images = $this->papaya()->images;
    $result = '';
    if (isset($this->folders[$folderId]) && is_array($this->folders[$folderId])) {
      $folder = $this->folders[$folderId];
      $folderIcon = 'items-folder';
      if (isset($folder['COUNT']) && $folder['COUNT'] > 0) {
        if (isset($this->sessionParams['open_folders'][$folderId])) {
          $node = 'open';
          $nodeHref = sprintf(
            ' nhref="%s"',
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array(
                  'cmd' => 'close_folder',
                  'close_folder_id' => $folderId
                )
              )
            )
          );
        } else {
          $node = 'close';
          $nodeHref = sprintf(
            ' nhref="%s"',
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array(
                  'cmd' => 'open_folder',
                  'open_folder_id' => $folderId
                )
              )
            )
          );
        }
      } else {
        $node = 'empty';
        $nodeHref = '';
      }
      if (isset($this->params['folder_id']) && $this->params['folder_id'] == $folderId) {
        $selected = ' selected="selected"';
        $folderIcon = 'status-folder-open';
        $offset = empty($this->params['offset_files']) ? 0 : $this->params['offset_files'];
      } else {
        $selected = '';
        $offset = 0;
      }

      if (isset($folder['folder_name'])) {
        $folderName = $folder['folder_name'];
      } elseif (isset($this->alternativeFolderNames[$folderId])) {
        $folderName = '['.$this->alternativeFolderNames[$folderId]['folder_name'].']';
      } else {
        $folderName = '['.papaya_strings::escapeHTMLChars($this->_gt('No title')).']';
      }

      $result .= sprintf(
        '<listitem node="%s" %s image="%s" indent="%d" title="%s" href="%s#mediaFilesList" %s>'.LF,
        papaya_strings::escapeHTMLChars($node),
        $nodeHref,
        papaya_strings::escapeHTMLChars($images[$folderIcon]),
        (int)$indent,
        papaya_strings::escapeHTMLChars($folderName),
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'filter_mode' => 'folder',
              'cmd' => 'edit_folder',
              'folder_id' => $folderId,
              'offset_files' => $offset
            )
          )
        ),
        $selected
      );
      switch ($folder['permission_mode']) {
      case 'own':
        $result .= sprintf(
          '<subitem align="right"><glyph src="%s" hint="%s"/></subitem>'.LF,
          papaya_strings::escapeHTMLChars($images['items-permission']),
          papaya_strings::escapeHTMLChars($this->_gt('own permissions'))
        );
        break;
      case 'additional':
        $result .= sprintf(
          '<subitem align="right"><glyph src="%s" hint="%s"/></subitem>'.LF,
          papaya_strings::escapeHTMLChars($images['items-permission']),
          papaya_strings::escapeHTMLChars($this->_gt('additional permissions'))
        );
        break;
      case 'inherited':
        $result .= sprintf(
          '<subitem align="right"><glyph src="%s" hint="%s"/></subitem>'.LF,
          papaya_strings::escapeHTMLChars($images['status-permission-inherited']),
          papaya_strings::escapeHTMLChars($this->_gt('inherited permissions'))
        );
        break;
      }
      $result .= '</listitem>'.LF;
      $result .= $this->getFoldersSubTreeXML($folderId, $indent + 1);
    }
    return $result;
  }

  /**
  * generate a list of the files derivation structure if exists
  *
  * @uses papaya_mediadb::getDerivationsSubTree()
  * @return string $result listview of file derivations
  */
  function getFileDerivationsListXML() {
    $result = '';
    $this->derivations = $this->getFileDerivations($this->params['file_id']);
    $headFileId = $this->getDerivationHeadId($this->params['file_id']);
    foreach ($this->derivations as $parentId => $childIds) {
      $fileIds[$parentId] = 1;
      foreach ($childIds as $childId => $null) {
        $fileIds[$childId] = 1;
      }
    }
    if (isset($fileIds) && is_array($fileIds)) {
      $this->derivationFiles = $this->getFilesById(array_keys($fileIds));
    }

    $result .= sprintf('<listview title="%s">'.LF, $this->_gt('Derivations'));
    $result .= sprintf(
      '<cols><col>%s</col><col>%s</col><col>%s</col><col>%s</col><col></col></cols>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('File')),
      papaya_strings::escapeHTMLChars($this->_gt('Size')),
      papaya_strings::escapeHTMLChars($this->_gt('Date')),
      papaya_strings::escapeHTMLChars($this->_gt('Type'))
    );
    $result .= '<items>'.LF;
    $result .= $this->getDerivationsEntry($headFileId, 0);
    $result .= '</items>'.LF;
    $result .= '</listview>';
    return $result;
  }

  /**
   * generate derivations subtree xml
   *
   * @access private
   * @uses papaya_mediadb::getDerivationsEntry()
   * @param string $fileId file id
   * @param integer $indent current indent depth
   * @return string $result derivations subtree xml
   */
  function getDerivationsSubTree($fileId, $indent) {
    $result = '';
    if (isset($this->derivations[$fileId])) {
      foreach ($this->derivations[$fileId] as $currentId => $null) {
        $result .= $this->getDerivationsEntry($currentId, $indent);
      }
    }
    return $result;
  }

  /**
   * generate derivations entry xml
   *
   * @access private
   * @param string $fileId file id
   * @param integer $indent current indent depth
   * @return string $result derivations entry xml
   */
  function getDerivationsEntry($fileId, $indent) {
    $result = '';
    $images = $this->papaya()->images;
    if (isset($this->derivationFiles[$fileId])) {
      $selected = ($fileId == $this->params['file_id']) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<listitem image="%s" indent="%d" title="%s" href="%s" %s>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->mimeObj->getMimeTypeIcon($this->derivationFiles[$fileId]['mimetype_icon'])
        ),
        (int)$indent,
        papaya_strings::escapeHTMLChars($this->derivationFiles[$fileId]['file_name']),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'file_derivations', 'file_id' => $fileId))
        ),
        $selected
      );
      $result .= sprintf(
        '<subitem align="right">%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->formatFileSize($this->derivationFiles[$fileId]['file_size'])
        )
      );
      $result .= sprintf(
        '<subitem align="center">%s</subitem>'.LF,
        date('Y-m-d H:i:s', $this->derivationFiles[$fileId]['file_date'])
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($this->derivationFiles[$fileId]['mimetype'])
      );
      $result .= sprintf(
        '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'edit_file', 'file_id' => $fileId))
        ),
        papaya_strings::escapeHTMLChars($images['actions-edit']),
        $this->_gt('Open detail view')
      );
      $result .= '</listitem>'.LF;
    } else {
      $result .= sprintf(
        '<listitem image="%s" indent="%d" title="[%s]" >'.LF,
        papaya_strings::escapeHTMLChars($images['places-trash']),
        (int)$indent,
        papaya_strings::escapeHTMLChars($fileId)
      );
      $result .= '<subitem />'.LF;
      $result .= '<subitem />'.LF;
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('deleted'))
      );
      $result .= '<subitem />'.LF;
      $result .= '</listitem>'.LF;
    }
    $result .= $this->getDerivationsSubTree($fileId, $indent + 1);
    return $result;
  }

  /**
  * generate file versions listview xml
  *
  * @return string file versions listview xml
  */
  function getFileVersionsListXML() {
    $images = $this->papaya()->images;
    $result = '';
    $this->fileVersions = $this->getFileVersions($this->params['file_id']);
    $result .= sprintf('<listview title="%s">'.LF, $this->_gt('Versions'));
    if (isset($this->currentFile['version_id'])) {
      $file = $this->getFile($this->params['file_id']);
    } else {
      $file = $this->currentFile;
    }

    if (is_array($this->fileVersions) && count($this->fileVersions) > 0) {
      $result .= sprintf(
        '<cols><col>%s (%s)</col><col>%s</col><col>%s</col><col /><col /><col /></cols>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Date')),
        papaya_strings::escapeHTMLChars($this->_gt('Version')),
        papaya_strings::escapeHTMLChars($this->_gt('Filename')),
        papaya_strings::escapeHTMLChars($this->_gt('Filesize'))
      );
      $result .= '<items>'.LF;

      $mimeTypeIcon = ($file['mimetype_icon'] != '')
        ? $file['mimetype_icon'] : $this->defaultTypeIcon;
      $selected = (isset($this->params['version_id']) && $this->params['version_id'] == '')
        ? ' selected="selected"' : '';

      $currentLink = $this->getLink(
        array('cmd' => 'file_versions', 'file_id' => $this->params['file_id'])
      );
      $result .= sprintf(
        '<listitem href="%s" image="%s" title="%s (#%d, %s)" %s>'.LF,
        papaya_strings::escapeHTMLChars($currentLink),
        papaya_strings::escapeHTMLChars(
          $this->mimeObj->getMimeTypeIcon($mimeTypeIcon)
        ),
        papaya_strings::escapeHTMLChars(date('Y-m-d H:i:s', $file['file_date'])),
        papaya_strings::escapeHTMLChars($file['current_version_id']),
        papaya_strings::escapeHTMLChars($this->_gt('current')),
        $selected
      );
      $result .= sprintf(
        '<subitem><a href="%s">%s</a></subitem>'.LF,
        papaya_strings::escapeHTMLChars($currentLink),
        papaya_strings::escapeHTMLChars($file['file_name'])
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($this->formatFileSize($file['file_size']))
      );
      $result .= sprintf(
        '<subitem align="center"><a href="%s"><glyph src="%s" /></a></subitem>'.LF,
        papaya_strings::escapeHTMLChars($currentLink),
        papaya_strings::escapeHTMLChars($images['categories-preview'])
      );
      $result .= '<subitem /><subitem />'.LF;
      $result .= '</listitem>'.LF;
      foreach ($this->fileVersions as $versionId => $version) {
        $mimeTypeIcon = ($version['mimetype_icon'] != '')
          ? $version['mimetype_icon'] : $this->defaultTypeIcon;
        if (isset($this->params['version_id']) &&
            (int)$this->params['version_id'] == $versionId) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $link = $this->getLink(
          array(
            'cmd' => 'file_versions',
            'version_id' => $versionId,
            'file_id' => $this->params['file_id'],
          )
        );
        $result .= sprintf(
          '<listitem href="%s" image="%s" title="%s (#%d)" align="center" %s>'.LF,
          papaya_strings::escapeHTMLChars($link),
          papaya_strings::escapeHTMLChars(
            $this->mimeObj->getMimeTypeIcon($mimeTypeIcon)
          ),
          date('Y-m-d H:i:s', $version['file_date']),
          papaya_strings::escapeHTMLChars($versionId),
          $selected
        );
        $result .= sprintf(
          '<subitem><a href="%s">%s</a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($link),
          papaya_strings::escapeHTMLChars($version['file_name'])
        );
        $result .= sprintf(
          '<subitem>%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars($this->formatFileSize($version['file_size']))
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s" title="%s"><glyph src="%s" /></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd' => 'file_versions',
                'file_id' => $this->params['file_id'],
                'version_id' => $versionId
              )
            )
          ),
          papaya_strings::escapeHTMLChars($this->_gt('View')),
          papaya_strings::escapeHTMLChars($images['categories-preview'])
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s" title="%s"><glyph src="%s" /></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd' => 'restore_version',
                'file_id' => $this->params['file_id'],
                'version_id' => $versionId
              )
            )
          ),
          papaya_strings::escapeHTMLChars($this->_gt('Restore')),
          papaya_strings::escapeHTMLChars($images['actions-recycle'])
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s" title="%s"><glyph src="%s" /></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd' => 'delete_version',
                'file_id' => $this->params['file_id'],
                'version_id' => $versionId
              )
            )
          ),
          papaya_strings::escapeHTMLChars($this->_gt('Delete')),
          papaya_strings::escapeHTMLChars($images['places-trash'])
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
    } else {
      $result .= '<items>'.LF;
      $result .= sprintf(
        '<listitem title="%s"><subitem cols="6"/></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('No versions found.'))
      );
      $result .= '</items>'.LF;
    }
    $result .= '</listview>'.LF;
    return $result;
  }

  // ------------------------- FOLDERS & FILES DIALOGS ---------------------------

  /**
  * generate dialog to edit folder properties or add a folder
  *
  * @param string $cmd command to pass as hidden field (also determines state of dialog: new/edit)
  */
  function initializeFolderEditDialog($cmd) {
    $data = array();
    $title = $this->_gt('Add folder');
    $button = 'Add';
    if ($cmd == 'edit_folder' &&
        isset($this->currentFolder['parent_id'])) {
      $parentId = (int)$this->currentFolder['parent_id'];
    } elseif (isset($this->params['parent_id'])) {
      $parentId = (int)$this->params['parent_id'];
    } else {
      $parentId = 0;
    }
    $hidden = array(
      'cmd' => $cmd,
      'confirm' => 1,
      'parent_id' => $parentId,
    );

    if ($cmd == 'edit_folder' &&
        isset($this->params['folder_id']) &&
        (int)$this->params['folder_id'] > 0) {
      $hidden['folder_id'] = $this->params['folder_id'];

      if (isset($this->currentFolder['folder_name'])) {
        $folder = array(
          'folder_name' => $this->currentFolder['folder_name'],
          'permission_mode' => $this->currentFolder['permission_mode'],
        );
      } else {
        $folder = array(
          'folder_name' => $this->_gt('No title'),
          'permission_mode' => \Papaya\CMS\Content\Media\Folder::PERMISSION_MODE_INHERIT,
        );
      }

      $data = array(
        'folder_name' => $folder['folder_name'],
        'permission_mode' => $folder['permission_mode'],
      );

      $title = $this->_gt('Edit folder');
      $button = 'Save';
    }

    if ($parentId > 0) {
      $fields = array(
        'Properties',
        'folder_name' => array('Name', 'isSomeText', TRUE, 'input', 100),
        'Permissions',
        'permission_mode' => array(
          'Permission mode', 'isNoHTML', TRUE, 'combo', $this->permissionModes
        ),
      );
    } else {
      $fields = array(
        'Properties',
        'folder_name' => array('Name', 'isSomeText', TRUE, 'input', 100)
      );
      $hidden['permission_mode'] = \Papaya\CMS\Content\Media\Folder::PERMISSION_MODE_DEFINE;
    }

    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $this->dialog->loadParams();
    $this->dialog->dialogTitle = $title;
    $this->dialog->buttonTitle = $button;
    $this->dialog->inputFieldSize = 'large';
    $this->dialog->dialogIcon = $this->papaya()->images['items-folder'];
  }

  /**
  * generate list of permissions of a folder with clickable checkboxes
  *
  * this method is rather complex as it has to consider permissions availability
  * and status
  *
  * @return string $result permissions listview xml
  */
  function getPermissionListXML() {
    $result = '';

    if (isset($this->currentFolder) && $this->currentFolder) {
      $images = $this->papaya()->images;
      $folder = $this->currentFolder;
      $surferPerms = $this->getSurferPermissions();
      $groups = $this->getGroups();
      $folderPerms = $this->calculateFolderPermissions($this->params['folder_id'], TRUE);
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('User permissions'))
      );
      $result .= sprintf(
        '<cols><col>%s</col><col align="center">%s</col><col align="center">%s</col></cols>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Group')),
        papaya_strings::escapeHTMLChars($this->_gt('Add/Edit')),
        papaya_strings::escapeHTMLChars($this->_gt('View/Use'))
      );
      $result .= '<items>'.LF;

      foreach ($groups as $groupId => $groupTitle) {
        if (isset($folderPerms['user_edit'][$groupId])) {
          if ($folderPerms['user_edit'][$groupId] == 'own') {
            $addFileIcon = $images['status-node-checked'];
            $addFileLink = $this->getLink(
              array(
                'cmd' => 'edit_folder',
                'perm_action' => 'del_perm',
                'folder_id' => $folder['folder_id'],
                'perm_type' => 'user_edit',
                'perm_id' => $groupId
              )
            );
          } else {
            $addFileIcon = $images['status-node-checked-disabled'];
            $addFileLink = '';
          }
        } else {
          if ($folder['permission_mode'] !== \Papaya\CMS\Content\Media\Folder::PERMISSION_MODE_INHERIT) {
            $addFileIcon = $images['status-node-empty'];
            $addFileLink = $this->getLink(
              array(
                'cmd' => 'edit_folder',
                'perm_action' => 'add_perm',
                'folder_id' => $folder['folder_id'],
                'perm_type' => 'user_edit',
                'perm_id' => $groupId,
              )
            );
          } else {
            $addFileIcon = $images['status-node-empty-disabled'];
            $addFileLink = '';
          }
        }
        if (isset($folderPerms['user_view'][$groupId])) {
          if ($folderPerms['user_view'][$groupId] == 'own') {
            $viewFileIcon = $images['status-node-checked'];
            $viewFileLink = $this->getLink(
              array(
                'cmd' => 'edit_folder',
                'perm_action' => 'del_perm',
                'folder_id' => $folder['folder_id'],
                'perm_type' => 'user_view',
                'perm_id' => $groupId,
              )
            );
          } else {
            $viewFileIcon = $images['status-node-checked-disabled'];
            $viewFileLink = '';
          }
        } else {
          if ($folder['permission_mode'] !== \Papaya\CMS\Content\Media\Folder::PERMISSION_MODE_INHERIT) {
            $viewFileIcon = $images['status-node-empty'];
            $viewFileLink = $this->getLink(
              array(
                'cmd' => 'edit_folder',
                'perm_action' => 'add_perm',
                'folder_id' => $folder['folder_id'],
                'perm_type' => 'user_view',
                'perm_id' => $groupId,
              )
            );
          } else {
            $viewFileIcon = $images['status-node-empty-disabled'];
            $viewFileLink = '';
          }
        }
        $result .= sprintf(
          '<listitem image="%s" title="%s">'.LF,
          papaya_strings::escapeHTMLChars($images['items-permission']),
          papaya_strings::escapeHTMLChars($groupTitle)
        );
        if (isset($addFileLink) && $addFileLink != '') {
          $result .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" /></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($addFileLink),
            papaya_strings::escapeHTMLChars($addFileIcon)
          );
        } else {
          $result .= sprintf(
            '<subitem align="center"><glyph src="%s" /></subitem>'.LF,
            papaya_strings::escapeHTMLChars($addFileIcon)
          );
        }
        if (isset($viewFileLink) && $viewFileLink != '') {
          $result .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" /></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($viewFileLink),
            papaya_strings::escapeHTMLChars($viewFileIcon)
          );
        } else {
          $result .= sprintf(
            '<subitem align="center"><glyph src="%s" /></subitem>'.LF,
            papaya_strings::escapeHTMLChars($viewFileIcon)
          );
        }
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;

      /*********************
      * Surfer permissions *
      *********************/
      if (is_array($surferPerms) && count($surferPerms) > 0) {
        $result .= sprintf(
          '<listview title="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Surfer permissions'))
        );
        $result .= '<cols>'.LF;
        $result .= sprintf(
          '<col>%s</col>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Permission'))
        );
        $result .= sprintf(
          '<col align="center">%s</col>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Active'))
        );
        $result .= sprintf(
          '<col align="center">%s</col>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Add/Edit'))
        );
        $result .= sprintf(
          '<col align="center">%s</col>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('View/Use'))
        );
        $result .= '</cols>'.LF;
        $result .= '<items>'.LF;
        foreach ($surferPerms as $permId => $permission) {
          $result .= sprintf(
            '<listitem image="%s" title="%s">'.LF,
            papaya_strings::escapeHTMLChars($images['items-permission']),
            papaya_strings::escapeHTMLChars($permission['surferperm_title'])
          );
          if (isset($folderPerms['surfer_add'][$permId])) {
            if ($folderPerms['surfer_add'][$permId] === \Papaya\CMS\Content\Media\Folder::PERMISSION_MODE_DEFINE &&
                $permission['surferperm_active'] == 1) {
              $addFileIcon = $images['status-node-checked'];
              $addFileLink = $this->getLink(
                array(
                  'cmd' => 'edit_folder',
                  'perm_action' => 'del_perm',
                  'folder_id' => $folder['folder_id'],
                  'perm_type' => 'surfer_add',
                  'perm_id' => $permId,
                )
              );
            } else {
              $addFileIcon = $images['status-node-checked-disabled'];
              $addFileLink = '';
            }
          } elseif ($folder['permission_mode'] !== \Papaya\CMS\Content\Media\Folder::PERMISSION_MODE_INHERIT &&
                    $permission['surferperm_active'] == 1) {
            $addFileIcon = $images['status-node-empty'];
            $addFileLink = $this->getLink(
              array(
                'cmd' => 'edit_folder',
                'perm_action' => 'add_perm',
                'folder_id' => $folder['folder_id'],
                'perm_type' => 'surfer_add',
                'perm_id' => $permId,
              )
            );
          } else {
            $addFileIcon = $images['status-node-empty-disabled'];
            $addFileLink = '';
          }
          if (isset($folderPerms['surfer_view'][$permId])) {
            if ($folderPerms['surfer_view'][$permId] === \Papaya\CMS\Content\Media\Folder::PERMISSION_MODE_DEFINE &&
                $permission['surferperm_active'] == 1) {
              $viewFileIcon = $images['status-node-checked'];
              $viewFileLink = $this->getLink(
                array(
                  'cmd' => 'edit_folder',
                  'perm_action' => 'del_perm',
                  'folder_id' => $folder['folder_id'],
                  'perm_type' => 'surfer_view',
                  'perm_id' => $permId,
                )
              );
            } else {
              $viewFileIcon = $images['status-node-checked-disabled'];
              $viewFileLink = '';
            }
          } elseif ($folder['permission_mode'] !== \Papaya\CMS\Content\Media\Folder::PERMISSION_MODE_INHERIT &&
                    $permission['surferperm_active'] == 1) {
            $viewFileIcon = $images['status-node-empty'];
            $viewFileLink = $this->getLink(
              array(
                'cmd' => 'edit_folder',
                'perm_action' => 'add_perm',
                'folder_id' => $folder['folder_id'],
                'perm_type' => 'surfer_view',
                'perm_id' => $permId,
              )
            );
          } else {
            $viewFileIcon = $images['status-node-empty-disabled'];
            $viewFileLink = '';
          }
          if ($permission['surferperm_active'] == 1) {
            $activeIcon = $images['status-node-checked-disabled'];
          } else {
            $activeIcon = $images['status-node-empty-disabled'];
          }
          $result .= sprintf(
            '<subitem align="center"><glyph src="%s" /></subitem>'.LF,
            papaya_strings::escapeHTMLChars($activeIcon)
          );
          if (isset($addFileLink) && $addFileLink != '') {
            $result .= sprintf(
              '<subitem  align="center"><a href="%s"><glyph src="%s" /></a></subitem>'.LF,
              papaya_strings::escapeHTMLChars($addFileLink),
              papaya_strings::escapeHTMLChars($addFileIcon)
            );
          } else {
            $result .= sprintf(
              '<subitem  align="center"><glyph src="%s" /></subitem>'.LF,
              papaya_strings::escapeHTMLChars($addFileIcon)
            );
          }
          if (isset($viewFileLink) && $viewFileLink != '') {
            $result .= sprintf(
              '<subitem  align="center"><a href="%s"><glyph src="%s" /></a></subitem>'.LF,
              papaya_strings::escapeHTMLChars($viewFileLink),
              papaya_strings::escapeHTMLChars($viewFileIcon)
            );
          } else {
            $result .= sprintf(
              '<subitem  align="center"><glyph src="%s" /></subitem>'.LF,
              papaya_strings::escapeHTMLChars($viewFileIcon)
            );
          }

          $result .= '</listitem>'.LF;
        }
        $result .= '</items>'.LF;
        $result .= '</listview>'.LF;
      }
    }
    return $result;
  }

  /**
  * initialize confirmation dialog for folder deletion
  * (give message if folder is not empty)
  */
  function initializeDeleteFolderDialog() {
    $title = $this->currentFolder['folder_name'];
    $subFolders = $this->getSubFolders(
      $this->papaya()->administrationLanguage->id,
      $this->currentFolder['folder_id']
    );
    $files = $this->getFiles(
      $this->currentFolder['folder_id'],
      1,
      0
    );
    // make sure the folder doesn't contain any subfolders or files
    if ((is_array($subFolders) && count($subFolders) > 0) ||
        (is_array($files) && count($files) > 0)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('Folder "%s" cannot be deleted, it contains subfolders or files.'),
          $title
        )
      );
      return FALSE;
    }

    $hidden = array(
      'cmd' => $this->params['cmd'],
      'folder_id' => $this->params['folder_id'],
      'confirm' => 1,
    );

    $this->dialog = new base_msgdialog(
      $this,
      $this->paramName,
      $hidden,
      sprintf(
        $this->_gt('Do you really want to delete folder "%s"?'), $title
      ),
      'warning'
    );
    $this->dialog->buttonTitle = 'Delete';
    return $this->dialog;
  }

  /**
  * initialize confirmation dialog for cutting a folder
  */
  function initializeCutFolderDialog() {
    $title = $this->currentFolder['folder_name'];
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'folder_id' => $this->params['folder_id'],
      'confirm' => 1,
    );

    $this->dialog = new base_msgdialog(
      $this,
      $this->paramName,
      $hidden,
      sprintf(
        $this->_gt('Do you really want to move folder "%s" to the clipboard?'), $title
      ),
      'warning'
    );
    $this->dialog->buttonTitle = 'Move';
  }

  /**
  * add a folder
  *
  * @return mixed folder id on success, otherwise FALSE
  */
  function addNewFolder() {
    if (isset($this->currentFolder)) {
      $ancestors = \Papaya\Utility\Arrays::decodeIdList($this->currentFolder['parent_path']);
      $ancestors[] = $this->currentFolder['folder_id'];
    } else {
      $ancestors = array(0);
    }
    $folderId = $this->addFolder(
      $this->params['parent_id'],
      \Papaya\Utility\Arrays::encodeAndQuoteIdList($ancestors),
      $this->params['permission_mode']
    );
    if ($folderId) {
      $this->addMsg(MSG_INFO, $this->_gt('Folder added.'));
      $saved = $this->addFolderTranslation(
        $folderId, $this->papaya()->administrationLanguage->id, $this->params['folder_name']
      );
      if ($saved) {
        $this->addMsg(MSG_INFO, $this->_gt('Folder translation added.'));
        return $folderId;
      }
    }

    return FALSE;
  }

  /**
  * update properties of an existing folder
  */
  function updateFolder() {
    if (isset($this->params['folder_id']) && $this->params['folder_id'] > 0) {
      $folderData = $this->getFolder($this->params['folder_id']);
      $data = array(
        'permission_mode' => $this->params['permission_mode'],
      );
      $params = array(
        'folder_id' => $this->params['folder_id'],
      );
      if ($this->params['permission_mode'] === \Papaya\CMS\Content\Media\Folder::PERMISSION_MODE_INHERIT) {
        $this->databaseDeleteRecord(
          $this->tableFoldersPermissions,
          'folder_id',
          $this->params['folder_id']
        );
      }
      if ($this->databaseUpdateRecord($this->tableFolders, $data, $params)) {
        $this->addMsg(MSG_INFO, $this->_gt('Folder updated.'));
      }
      if (isset($folderData[$this->papaya()->administrationLanguage->id])) {
        $dataTrans = array(
          'folder_name' => $this->params['folder_name'],
        );
        $paramsTrans = array(
          'folder_id' => $this->params['folder_id'],
          'lng_id' => $this->papaya()->administrationLanguage->id,
        );
        if ($this->databaseUpdateRecord($this->tableFoldersTrans, $dataTrans, $paramsTrans)) {
          $this->addMsg(MSG_INFO, $this->_gt('Folder translation updated.'));
        }
      } else {
        $dataTrans = array(
          'folder_id' => $this->params['folder_id'],
          'lng_id' => $this->papaya()->administrationLanguage->id,
          'folder_name' => $this->params['folder_name'],
        );
        if ($this->databaseInsertRecord($this->tableFoldersTrans, NULL, $dataTrans)) {
          $this->addMsg(MSG_INFO, $this->_gt('Folder translation added.'));
        }
      }
    } else {
      $this->addMsg(MSG_INFO, $this->_gt('No folder selected.'));
    }
  }

  /**
  * delete a folder
  *
  * @param integer $folderId id of folder to be deleted
  */
  function delFolder($folderId) {
    if ($this->deleteFolder($folderId)) {
      $this->sessionParams['folder_id'] = 0;
      $this->sessionParams['cmd'] = '';
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
      $this->addMsg(
        MSG_INFO,
        sprintf(
          $this->_gt('Folder "%s" deleted.'), $this->currentFolder['folder_name']
        )
      );
    }
  }

  /**
  * Get upload modes toolbar
  * @return void
  */
  function getUploadToolbar() {
    $administrationUser = $this->papaya()->administrationUser;
    if ((
          $administrationUser->hasPerm(Administration\Permissions::FILE_UPLOAD) ||
          $administrationUser->hasPerm(Administration\Permissions::FILE_IMPORT)
        ) &&
        $this->isFolderSelected()) {
      $toolbar = new base_btnbuilder;
      $toolbar->images = $this->papaya()->images;
      $toolbar->addButton(
        'Upload files',
        $this->getLink(
          array(
            'cmd' => 'upload_files',
            'file_id' => empty($this->params['file_id']) ? '' : $this->params['file_id']
          )
        ),
        'places-computer',
        'Upload files from your computer',
        isset($this->params['cmd']) && $this->params['cmd'] == 'upload_files'
      );
      if ($administrationUser->hasPerm(Administration\Permissions::FILE_IMPORT)) {
        $toolbar->addButton(
          'Import folder',
          $this->getLink(array('cmd' => 'import_folder')),
          'places-network-server',
          'Import local folder on webserver',
          isset($this->params['cmd']) && $this->params['cmd'] == 'import_folder'
        );
      }
      if ($administrationUser->hasPerm(Administration\Permissions::FILE_UPLOAD)) {
        $toolbar->addButton(
          'Get webfile',
          $this->getLink(array('cmd' => 'get_file')),
          'items-publication',
          'Get a file from the internet',
          isset($this->params['cmd']) && $this->params['cmd'] == 'get_file'
        );
      }
      $this->layout->addRight(sprintf('<toolbar>%s</toolbar>'.LF, $toolbar->getXML()));
    }
  }

  /**
  * generate dialog for uploading files
  *
  * @return string $result upload dialog xml
  */
  function getUploadDialog() {
    $administrationUser = $this->papaya()->administrationUser;
    $images = $this->papaya()->images;
    if ($administrationUser->hasPerm(Administration\Permissions::FILE_UPLOAD)) {
      $result = '';
      $fields = array();
      $data = array();
      $hidden = array(
        'confirm' => 1,
        'folder_id' => $this->params['folder_id'],
        'cmd' => 'upload',
        'upload' => 1,
      );
      if (isset($this->params['file_id'])) {
        $hidden['file_id'] = $this->params['file_id'];
      }
      $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $dialog->loadParams();

      if (function_exists('uploadprogress_get_info')) {
        $progressBar = TRUE;
      } else {
        $progressBar = FALSE;
      }

      $this->layout->addScript('<script type="text/javascript" src="script/xmlrpc.js"></script>');
      $this->layout->addScript('<script type="text/javascript" src="script/upload.js"></script>');
      $this->layout->addScript(
        sprintf(
          '<script type="text/javascript">
          var progressDialogTitle = "%s";
          var progressDialogStartMsg = "%s";
          var progressNoFileMsg = "%s";
          </script>',
          papaya_strings::escapeHTMLChars($this->_gt('Upload files')),
          papaya_strings::escapeHTMLChars($this->_gt('Starting upload...')),
          papaya_strings::escapeHTMLChars($this->_gt('No files to upload!'))
        )
      );

      if ($progressBar) {
        $result .= sprintf(
          '<dialog action="%s" name="uploadform" id="uploadform" title="%s"'.
          ' onsubmit="return startFileUpload(this);" width="100%%" type="file"'.
          ' enctype="multipart/form-data">'.LF,
          papaya_strings::escapeHTMLChars($this->getLink()),
          papaya_strings::escapeHTMLChars($this->_gt('Upload files'))
        );
        $result .= '<input type="hidden" name="UPLOAD_IDENTIFIER" id="UPLOAD_IDENTIFIER"'.
          ' value="uniquid" />'.LF;
        $result .= '<input type="hidden" name="UPLOAD_JAVASCRIPT" id="UPLOAD_JAVASCRIPT"'.
          ' value="0" />'.LF;
      } else {
        $result .= sprintf(
          '<dialog action="%s" name="uploadform" id="uploadform" title="%s" '.
          ' width="100%%" type="file" enctype="multipart/form-data">'.LF,
          papaya_strings::escapeHTMLChars($this->getLink()),
          papaya_strings::escapeHTMLChars($this->_gt('Upload files'))
        );
      }
      $result .= sprintf(
        '<input type="hidden" name="MAX_FILE_SIZE" value="%d" />'.LF,
        (int)$this->getMaxUploadSize()
      );
      $result .= $dialog->getHidden();
      $result .= '<lines  class="dialogSmall">'.LF;
      for ($i = 0; $i < $this->numUploadFields; $i++) {
        $result .= sprintf(
          '<line caption="%s %d">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('File')),
          $i + 1
        );
        $result .= sprintf(
          '<input type="file" size="18" class="dialogScale dialogFile" id="%s_upload_%d"'.
          ' name="%s[upload][%d]" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          (int)$i,
          papaya_strings::escapeHTMLChars($this->paramName),
          (int)$i
        );
        $result .= '</line>'.LF;
        if ($i == 0 && isset($this->currentFile) && is_array($this->currentFile)) {
          $result .= sprintf(
            '<line caption="%s" align="center">'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('Replace current file'))
          );
          $result .= sprintf(
            '<input type="checkbox" name="%s[replace]" value="1" />'.LF,
            papaya_strings::escapeHTMLChars($this->paramName)
          );
          $result .= '</line>'.LF;
        }
      }
      $result .= '</lines>'.LF;
      $result .= sprintf(
        '<dlgbutton align="left" value="" caption="%s" type="button"'.
        ' hint="%s" onclick="addUploadButton(\'%s\');" image="%s" mode="both"/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Add upload field')),
        papaya_strings::escapeHTMLChars(
          $this->_gt('Click this button to add another upload field.')
        ),
        papaya_strings::escapeHTMLChars($this->_gt('File')),
        papaya_strings::escapeHTMLChars($images['actions-generic-add'])
      );
      $result .= sprintf(
        '<dlgbutton value="%s" id="upsub" name="upsub" />'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Upload'))
      );
      $result .= '</dialog>'.LF;
      $this->layout->addRight($result);
    }
  }

  /**
  * process file upload: add files to media db, replace if necessary, give errors
  */
  function processUpload() {
    $result = FALSE;
    $files = array();
    if (isset($this->params['folder_id']) && isset($_FILES) && is_array($_FILES) &&
        isset($_FILES[$this->paramName]) && is_array($_FILES[$this->paramName])) {
      $maxUploadSize = $this->getMaxUploadSize();
      $result = TRUE;
      for ($i = 0, $c = count($_FILES[$this->paramName]['name']['upload']); $i < $c; $i++) {
        if ($_FILES[$this->paramName]['tmp_name']['upload'][$i] != '') {
          $file['tempname'] = $_FILES[$this->paramName]['tmp_name']['upload'][$i];
          $file['size'] = $_FILES[$this->paramName]['size']['upload'][$i];
          $file['name'] = $_FILES[$this->paramName]['name']['upload'][$i];
          $file['type'] = $_FILES[$this->paramName]['type']['upload'][$i];
          $file['error'] = $_FILES[$this->paramName]['error']['upload'][$i];
          if ($i == 0 &&
              isset($this->params['file_id']) && $this->params['file_id'] != '' &&
              isset($this->currentFile) && isset($this->currentFile['current_version_id']) &&
              isset($this->params['replace']) && $this->params['replace']) {
            $this->replaceUploadedFile($file['tempname'], $file['name'], $file['type']);
          } else {
            $files[$i] = $file;
          }
        }
        if ($_FILES[$this->paramName]['error']['upload'][$i] != 0 &&
            $_FILES[$this->paramName]['error']['upload'][$i] != 4) {
          $result = FALSE;
          switch ($_FILES[$this->paramName]['error']['upload'][$i]) {
          case 1:
            $this->addMsg(
              MSG_ERROR,
              sprintf(
                $this->_gt('File #%d is too large (PHP_INI, %s).'),
                $i + 1,
                $this->formatFileSize($maxUploadSize)
              )
            );
            break;
          case 2:
            $this->addMsg(
              MSG_ERROR,
              sprintf(
                $this->_gt('File #%d is too large (HTML_FORM, %s).'),
                $i + 1,
                $this->formatFileSize($maxUploadSize)
              )
            );
            break;
          case 3:
            $this->addMsg(
              MSG_ERROR,
              sprintf($this->_gt('File #%d was only partially uploaded.'), $i + 1)
            );
            break;
          case 6:
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Temporary folder not found.')
            );
            break;
          case 7:
            $this->addMsg(
              MSG_ERROR,
              sprintf($this->_gt('File #%d could not be written to disk.'), $i + 1)
            );
            break;
          case 8:
            $this->addMsg(
              MSG_ERROR,
              sprintf($this->_gt('Extension of file #%d is blocked.'), $i + 1)
            );
            break;
          default:
            $this->addMsg(MSG_ERROR, $this->_gt('An unknown error occurred.'));
            break;
          }
        } elseif ($_FILES[$this->paramName]['size']['upload'][$i] > $maxUploadSize) {
          $this->addMsg(
            MSG_ERROR,
            sprintf(
              $this->_gt('File #%d "%s" is too large (PAPAYA_MAX_UPLOAD_SIZE, %s)'),
              $i + 1,
              $_FILES[$this->paramName]['name']['upload'][$i],
              $this->formatFileSize($maxUploadSize)
            )
          );
          unset($files[$i]);
        }
      }

      if (isset($files) && is_array($files) && count($files) > 0) {
        foreach ($files as $file) {
          $fileId = $this->addFile(
            $file['tempname'],
            $file['name'],
            empty($this->params['folder_id']) ? 0 : (int)$this->params['folder_id'],
            $this->surfer ? $this->surfer['surfer_id'] : NULL,
            $file['type'],
            'uploaded_file'
          );
          if ($fileId) {
            $this->addMsg(
              MSG_INFO,
              sprintf($this->_gt('File "%s" (%s) uploaded.'), $file['name'], $fileId)
            );
            $this->switchFile($fileId);
          } else {
            $this->getErrorForType($this->lastError);
          }
        }
      }
    } else {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('Upload failed.').' '.sprintf(
          $this->_gt('File may not be larger than %s.'),
          $this->formatFileSize($this->getMaxUploadSize())
        )
      );
    }
    return $result;
  }

  /**
  * replace a file with an uploaded file
  *
  * @param string $fileLocation location of uploaded file on disk
  * @param string $fileName name of the file
  * @param string $fileType filetype the browser sent, just to check
  */
  function replaceUploadedFile($fileLocation, $fileName, $fileType) {
    $replaced = $this->replaceFile(
      $this->params['file_id'],
      $fileLocation,
      $fileName,
      $this->surfer ? $this->surfer['surfer_id'] : NULL,
      $fileType,
      'uploaded_file'
    );
    if ($replaced) {
      $this->addMsg(MSG_INFO, $this->_gt('File replaced.'));
      $this->loadFileData();
    } else {
      $this->getErrorForType($this->lastError);
    }
  }

  /**
  * get the corresponding error for an error type returned by add/replaceFile
  *
  * @param string $errorType error Type
  */
  function getErrorForType($errorType) {
    switch($errorType) {
    default:
    case 'no_error':
      $this->addMsg(
        MSG_WARNING,
        $this->_gt('The action failed, but no error was triggered. Something`s wrong here.')
      );
      break;
    case 'path_not_found':
      $this->addMsg(MSG_ERROR, $this->_gt('Path could not be created.'));
      break;
    case 'mode_not_set':
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('Could not determine, whether file was uploaded or local.')
      );
      break;
    case 'file_not_moved':
      $this->addMsg(MSG_ERROR, $this->_gt('Could not move file.'));
      break;
    case 'no_properties':
      $this->addMsg(MSG_ERROR, $this->_gt('Could not find out file properties.'));
      break;
    case 'empty_file':
      $this->addMsg(MSG_ERROR, $this->_gt('Wouldn`t add empty file.'));
      break;
    case 'db_add_file_failed':
      $this->addMsg(MSG_ERROR, $this->_gt('File could not be added.'));
      break;
    case 'db_update_file_failed':
      $this->addMsg(MSG_ERROR, $this->_gt('File could not be updated.'));
      break;
    case 'create_version_failed':
      $this->addMsg(MSG_ERROR, $this->_gt('Version could not be created.'));
      break;
    case 'load_file_failed':
      $this->addMsg(MSG_ERROR, $this->_gt('Could not load file.'));
      break;
    case 'import_failed':
      $this->addMsg(MSG_ERROR, $this->_gt('Failed to import file.'));
      break;
    case 'no_temp_file':
      $this->addMsg(MSG_ERROR, $this->_gt('Temporary file not found.'));
      break;
    case 'open_remote_file_failed':
      $this->addMsg(MSG_ERROR, $this->_gt('Could not open remote file.'));
      break;
    case 'open_temp_file_failed':
      $this->addMsg(MSG_ERROR, $this->_gt('Could not open temporary file.'));
      break;
    case 'derivations_lost':
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('Derivation of file could not be saved, relation is lost.')
      );
      break;
    case 'translations_copy_failed':
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('Translated data could not be copied to new file.')
      );
      break;
    case 'copy_file_failed':
      $this->addMsg(MSG_ERROR, $this->_gt('Could not copy file.'));
      break;
    case 'create_path_failed':
      $this->addMsg(MSG_ERROR, $this->_gt('Path could not be created.'));
      break;
    case 'file_not_found':
      $this->addMsg(MSG_ERROR, $this->_gt('File not found.'));
      break;
    case 'version_not_found':
      $this->addMsg(MSG_ERROR, $this->_gt('Version not found.'));
    }
  }

  /**
  * load properties of the current authUser (fills $this->surfer)
  *
  * use this function to get the surfer_id to use as the owner id for a new file
  *
  * @return boolean TRUE if surfer could be loaded, otherwise FALSE
  */
  function loadSurferData() {
    if (!(isset($this->surfer) && is_array($this->surfer) && count($this->surfer) > 0)) {
      $administrationUser = $this->papaya()->administrationUser;
      if ($surferId = $administrationUser->getSurferId()) {
        $surfer = new base_surfer();
        $surfer->load($surferId);
        if ($surfer->surfer) {
          $this->surfer = $surfer->surfer;
          $this->surfer['surfer_id'] = $surferId;
          return TRUE;
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Could not load surfer details.'));
        }
      }
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
  * initialize dialog for adding or editing a file
  */
  function initializeFileEditDialog() {
    if (isset($this->params['file_id']) && $this->params['file_id'] != '' &&
        is_array($this->currentFile) && count($this->currentFile) > 0) {
      $data = $this->currentFile;
      $hidden = array(
        'cmd' => 'edit_file',
        'confirm' => 1,
        'file_id' => $this->params['file_id'],
      );

      $captionFileTitle = 'Title';
      $captionFileDesrciption = 'Description';
      if (in_array($this->currentFile['mimetype'], $this->imageMimeTypes)) {
        if (defined('PAPAYA_MEDIA_CUTLINE_MODE') && PAPAYA_MEDIA_CUTLINE_MODE == 1) {
          $captionFileTitle = 'Title (Cutline)';
        }
        if (defined('PAPAYA_MEDIA_ALTTEXT_MODE') && PAPAYA_MEDIA_ALTTEXT_MODE == 1) {
          $captionFileDesrciption = 'Description (Alternative Text)';
        }
      }
      if (isset($this->currentFileData) &&
          is_array($this->currentFileData)) {
        if (isset($this->currentFileData['file_title']) &&
            trim($this->currentFileData['file_title']) != '') {
          $data['file_title'] = (string)$this->currentFileData['file_title'];
        }
        if (isset($this->currentFileData['file_description'])) {
          $data['file_description'] = $this->currentFileData['file_description'];
        }
      }
      if (isset($this->currentFile['file_sort'])) {
        $data['file_sort'] = $this->currentFile['file_sort'];
      }

      $owner = $this->getFileOwnerData($this->currentFile['file_id']);
      if (is_array($owner) && count($owner) > 0) {
        $surferName = '';
        if ($owner['surfer_givenname'] != '' && $owner['surfer_surname'] != '') {
          $surferName = $owner['surfer_surname'].', '.$owner['surfer_givenname'].' ';
        } elseif ($owner['surfer_surname'] != '' || $owner['surfer_givenname'] != '') {
          $surferName = $owner['surfer_givenname'].$owner['surfer_surname'].' ';
        }
        $surferName .= '['.$owner['surfer_handle'].']';
      } else {
        $surferName = $this->_gt('Unknown');
      }
      $fields = array(
        'Language independent',
        'file_name' => array('Filename', 'isNoHTML', TRUE, 'input', 100),
        'file_created' => array('Created', 'isISODateTime', FALSE, 'datetime', 16),
        'file_keywords' => array('Keywords', 'isNoHTML', FALSE, 'textarea', 6),
        'owner' => array('Owner', 'isNoHTML', FALSE, 'disabled_input', 100, '', $surferName),
        'file_sort' => array('Sorting', 'isNoHTML', TRUE, 'input', 100),
        'Source',
        'file_source' => array('Text', 'isNoHTML', FALSE, 'input', 400),
        'file_source_url' => array('URL', 'isHttpX', FALSE, 'input', 1000),
        'Properties',
        'file_title' => array($captionFileTitle, 'isNoHTML', FALSE, 'input', 255),
        'file_description' => array(
          $captionFileDesrciption, 'isSomeText', FALSE, 'simplerichtext', 10
        )
      );

      $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->dialog->loadParams();
      $this->dialog->dialogTitle = $this->_gt('Edit file');
      $this->dialog->buttonTitle = 'Save';
      $this->dialog->inputFieldSize = 'large';
    }
  }

  /**
  * initialize dialog for getting a file from the web
  */
  function initializeGetFileFromWebDialog() {
    $data = array();
    $title = $this->_gt('Get file from the internet');
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'confirm' => 1,
    );

    $fields = array(
      'web_file' => array('File location', 'isHTTPX', TRUE, 'input', 2000, '', 'http://'),
    );
    if (isset($this->params['file_id']) && $this->params['file_id'] != '') {
      $fields['replace'] = array(
        'Replace current file', 'isNum', FALSE, 'checkbox', 1, '', 0, 'left'
      );
    }

    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $this->dialog->dialogTitle = $title;
    $this->dialog->buttonTitle = 'Save';
    $this->dialog->inputFieldSize = 'large';
  }

  /**
  * initialize dialog for tagging a file
  */
  function initializeTagDialog() {
    if ($tags = papaya_taglinks::getInstance($this, 'tg')) {
      $tags->setLinkParams(
        $this->paramName,
        array(
          'cmd' => $this->params['cmd'],
          'file_id' => $this->params['file_id'],
        )
      );
      $this->layout->addRight($tags->getTagLinker('media', $this->params['file_id'], TRUE));
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('You don\'t have the permission to tag files.'));
    }
  }

  /**
  * generate restore version confirmation dialog
  */
  function getRestoreVersionDialog() {
    if ($file = $this->getFile($this->params['file_id'], $this->params['version_id'])) {
      $hidden = array(
        'cmd' => $this->params['cmd'],
        'confirm' => 1,
        'file_id' => $this->params['file_id'],
        'version_id' => $this->params['version_id'],
      );

      $file = $this->getFile($this->params['file_id'], $this->params['version_id']);

      $dialog = new base_msgdialog(
        $this,
        $this->paramName,
        $hidden,
        sprintf(
          $this->_gt('Do you want to restore version #%d ("%s") of this file?'),
          $this->params['version_id'],
          $file['file_name']
        ),
        'question'
      );
      $dialog->buttonTitle = 'restore';
      return $dialog->getMsgDialog();
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Version not found.'));
      return '';
    }
  }

  /**
  * generate delete version confirmation dialog
  */
  function getDeleteVersionDialog() {
    if ($file = $this->getFile($this->params['file_id'], $this->params['version_id'])) {
      $hidden = array(
        'cmd' => $this->params['cmd'],
        'confirm' => 1,
        'file_id' => $this->params['file_id'],
        'version_id' => $this->params['version_id'],
      );
      $dialog = new base_msgdialog(
        $this,
        $this->paramName,
        $hidden,
        sprintf(
          $this->_gt('Do you want to delete version #%d ("%s") of this file?'),
          $this->params['version_id'],
          $file['file_name']
        ),
        'question'
      );
      $dialog->buttonTitle = 'delete';
      return $dialog->getMsgDialog();
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Version not found.'));
      return '';
    }
  }

  /**
  * initialize fix extension dialog
  *
  * This method asks the user to choose which extension the file should have or
  * restore its metadata if the extension is correct.
  */
  function initializeFixExtensionDialog() {
    if (isset($this->params['file_id']) && $this->params['file_id'] != '') {
      $extensions = $this->mimeObj->getMimeTypesExtensions($this->currentFile['mimetype_id']);
      $result = '';
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Fix extension'))
      );
      $result .= '<items>'.LF;

      $result .= '<listitem>'.LF;
      $result .= sprintf(
        '<subitem><a href="%s">%s</a></subitem>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'restore_meta', 'file_id' => $this->params['file_id']))
        ),
        papaya_strings::escapeHTMLChars(
          $this->_gt('Click here to restore metadata for the file.')
        )
      );
      $result .= '</listitem>'.LF;
      if (isset($extensions) && is_array($extensions) && count($extensions) > 0) {
        if ($pos = strrpos($this->currentFile['file_name'], '.')) {
          $baseFileName = substr($this->currentFile['file_name'], 0, $pos);
        } else {
          $baseFileName = $this->currentFile['file_name'];
        }
        foreach ($extensions as $extension) {
          $result .= sprintf(
            '<listitem title="%s">'.LF,
            papaya_strings::escapeHTMLChars($extension)
          );
          $result .= sprintf(
            '<subitem><a href="%s">%s %s</a></subitem>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array(
                  'cmd' => $this->params['cmd'],
                  'confirm' => 1,
                  'file_id' => $this->params['file_id'],
                  'extension' => $extension
                )
              )
            ),
            papaya_strings::escapeHTMLChars($this->_gt('Click here to rename file to')),
            papaya_strings::escapeHTMLChars($baseFileName.'.'.$extension)
          );
          $result .= '</listitem>'.LF;
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addRight($result);
    }
  }

  /**
  * fix the extension of a file to match its mimetype
  *
  * this methods changes the extension of a file
  */
  function changeExtension() {
    if (isset($this->params['file_id']) && $this->params['file_id'] != '' &&
        isset($this->params['extension']) && $this->params['extension'] != '' &&
        isset($this->currentFile) && is_array($this->currentFile) &&
        isset($this->currentFile['file_name'])) {
      $extensions = $this->mimeObj->getMimeTypesExtensions($this->currentFile['mimetype_id']);
      if (isset($extensions[$this->params['extension']])) {
        if ($pos = papaya_strings::strrpos($this->currentFile['file_name'], '.')) {
          $newFileName = substr($this->currentFile['file_name'], 0, $pos + 1);
          $newFileName .= $this->params['extension'];
        } else {
          $newFileName = $this->currentFile['file_name'].'.'.$this->params['extension'];
        }
        $data = array(
          'file_name' => $newFileName,
        );
        $condition = array(
          'file_id' => $this->params['file_id'],
        );
        if ($this->databaseUpdateRecord($this->tableFiles, $data, $condition)) {
          $this->addMsg(
            MSG_INFO, $this->_gt('File extension corrected.')
          );
          $this->loadFileData();
        }
      } else {
        $this->addMsg(
          MSG_ERROR,
          sprintf(
            $this->_gt('Invalid extension "%s" for filetype "%s".'),
            $this->params['extension'],
            $this->currentFile['mimetype']
          )
        );
      }
    }
  }

  /**
  * Initialize object for image convert dialog
  * @return void
  */
  function initializeConvertImageDialog() {
    $srcFileName = $this->getFileName(
      $this->params['file_id'], $this->currentFile['current_version_id']
    );
    if (is_file($srcFileName)) {
      if ($this->initializeImageConverter($srcFileName)) {
        $srcFormat = $this->imageConverter->getFileFormat($srcFileName);
        if ($this->imageConverter->canConvert($srcFormat)) {
          $hidden = array(
            'cmd' => 'convert_image',
            'file_id' => $this->params['file_id'],
            'confirm' => 1
          );
          $formats = array('jpg' => 'JPEG', 'png' => 'PNG', 'gif' => 'GIF');
          if (isset($formats[$srcFormat])) {
            unset($formats[$srcFormat]);
          }
          foreach ($formats as $format => $formatTitle) {
            if (!$this->imageConverter->canConvert($srcFormat, $format)) {
              unset($formats[$format]);
            }
          }

          $data = array();
          $fields = array(
            'target_format' => array('Target format', 'isAlphaNum', TRUE, 'combo', $formats)
          );
          $this->dialog = new base_dialog(
            $this, $this->paramName, $fields, $data, $hidden
          );
          $this->dialog->dialogTitle = $this->_gt('Convert image');
          $this->dialog->loadParams();
        } else {
          $this->addMsg(
            MSG_ERROR,
            sprintf(
              $this->_gt('Cannot convert "%s". Unsupported file format.'),
              $this->currentFile['file_name']
            )
          );
        }
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Cannot initialize image converter.'));
      }
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Could not find the file in the filesystem.'));
    }
  }

  /**
  * Initialize image converter for file
  * @param string $fileName
  * @return boolean
  */
  function initializeImageConverter($fileName) {
    if ($this->imageConverter = papaya_imageconvert::getConverter($fileName)) {
      $this->imageConverter->initialize();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Convert image to target format (Creates a copy of the current image)
  *
  * @param string $fileId
  * @param string $targetFormat
  * @return string|FALSE
  */
  function convertImage($fileId, $targetFormat) {
    $file = $this->getFile($fileId);
    $srcFileName = $this->getFileName($fileId, $file['current_version_id']);
    $srcFormat = $this->imageConverter->getFileFormat($srcFileName);
    if ($this->imageConverter->canConvert($srcFormat, $targetFormat)) {
      $tempFileName = tempnam(PAPAYA_PATH_CACHE, '.mdb_convert');

      if ($this->imageConverter->convert($srcFileName, $tempFileName, $targetFormat)) {
        if ($pos = strrpos($file['file_name'], '.')) {
          $fileName = substr($file['file_name'], 0, $pos + 1).$targetFormat;
        } else {
          $fileName = $this->currentFile['file_name'].'.'.$this->params['target_format'];
        }
        $newFileId = $this->addFile(
          $tempFileName,
          $fileName,
          -1,
          $this->surfer ? $this->surfer['surfer_id'] : NULL,
          '',
          'local_file',
          $this->currentFile
        );
        if ($newFileId) {
          $this->addMsg(MSG_INFO, $this->_gt('File converted.'));
          $this->copyTranslatedData($fileId, $newFileId);
          if (!$this->addDerivation($newFileId, $fileId, $file['current_version_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Derivation could not be created.'));
          }
          return $newFileId;
        }
      }
    }
    return FALSE;
  }

  /**
  * generate confirmation dialog to delete a file
  */
  function initializeDeleteFileDialog() {
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'file_id' => $this->params['file_id'],
      'confirm' => 1,
    );
    $this->dialog = new base_msgdialog(
      $this,
      $this->paramName,
      $hidden,
      sprintf(
        $this->_gt('Do you really want to delete file "%s"?'),
        $this->currentFile['file_name']
      ),
      'warning'
    );
    $this->dialog->buttonTitle = 'Delete';
  }

  /**
  * restore metadata for a file
  *
  * Useful when changes to mimetypes occur or to restore the mimetype for a file
  * which didn't have an extension before.
  *
  * @param string $fileId id of file whose metadata shall be restored
  */
  function restoreMetadata($fileId) {
    if (isset($this->currentFile) && $this->currentFile['file_id'] == $fileId) {
      $file = $this->currentFile;
    } else {
      $file = $this->getFile($fileId);
    }
    $data = $this->getFileProperties($file['FILENAME'], $file['file_name']);
    $fileData = array(
      'mimetype_id' => $data['mimetype_id'],
      'file_size' => $data['size'],
      'metadata' => $data['metadata'],
      'width' => empty($data['width']) ? 0 : (int)$data['width'],
      'height' => empty($data['height']) ? 0 : (int)$data['height'],
    );
    if (!empty($data['file_created'])) {
      $fileData['file_created'] = $data['file_created'];
    }
    $condition = array('file_id' => $file['file_id']);
    if (FALSE !== $this->databaseUpdateRecord($this->tableFiles, $fileData, $condition)) {
      $this->addMsg(MSG_INFO, $this->_gt('File metadata restored.'));
      $this->currentFile = $this->getFile($file['file_id']);
    }
  }

  /**
  * papaya tag generator
  *
  * this dialog is used to generate a papaya tag for use with a disabled richtext
  * editor or plain textareas
  *
  * @param string $fileId a file id
  * @return string $result tag creation dialog XML
  */
  function getPapayaTagCreator($fileId) {
    $result = '';
    if ($file = $this->getFile($fileId)) {
      $imageWidth = (int)$file['width'];
      $imageHeight = (int)$file['height'];
      //dialog
      $javaScript = <<<HEREDOC
<script type="text/javascript" src="script/imgbrowser.js"></script>
<script type="text/javascript">
<![CDATA[
var imgheight = $imageHeight;
var imgwidth = $imageWidth;
var imgurl = '{$file['file_id']}';
]]>
</script>
HEREDOC;
      $this->layout->addScript($javaScript);
      $result .= sprintf(
        '<dialog action="#" title="%s [%s]" id="imgform" align="center">',
        papaya_strings::escapeHTMLChars($this->_gt('Integration')),
        papaya_strings::escapeHTMLChars($file['file_name'])
      );
      $result .= '<lines class="dialogSmall">';
      $result .= sprintf(
        '<linegroup caption="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Tag'))
      );
      $result .= '<line align="center"><textarea class="dialogTextarea dialogScale" '.
        'cols="30" rows="4" id="imgtag" name="imgtag"></textarea></line>'.LF;
      $result .= sprintf(
        '</linegroup><linegroup caption="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Type'))
      );
      $result .= sprintf(
        '<line caption="%s">
          <select name="imgdownload" onchange="createMediaImageTag();"'.
          ' class="dialogSelect dialogScale">
            <option value="yes">%s</option>
            <option value="no" selected="selected">%s</option>
          </select>
        </line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Download')),
        papaya_strings::escapeHTMLChars($this->_gt('Yes')),
        papaya_strings::escapeHTMLChars($this->_gt('No'))
      );
      $result .= sprintf(
        '</linegroup><linegroup caption="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Text'))
      );
      $result .= sprintf(
        '<line caption="%s"><input type="text" name="imgsubtitle"'.
        ' onkeyup="createMediaImageTag();" class="dialogInput dialogScale" /></line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Subtitle'))
      );
      $result .= sprintf(
        '<line caption="%s"><input type="text" name="imgalt"'.
        ' onkeyup="createMediaImageTag();" class="dialogInput dialogScale" /></line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Alternative text'))
      );
      $result .= sprintf(
        '</linegroup><linegroup caption="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Scaling'))
      );
      $result .= sprintf(
        '<line caption="%s"><input type="text" name="imgwidth"'.
        ' onkeyup="createMediaImageTag();" class="dialogInput dialogScale" value="" /></line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Width'))
      );
      $result .= sprintf(
        '<line caption="%s"><input type="text" name="imgheight"'.
        ' onkeyup="createMediaImageTag();" class="dialogInput dialogScale" value="" /></line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Height'))
      );
      $result .= sprintf(
        '<line caption="%s">
          <select name="imgresize" onchange="createMediaImageTag();"'.
          ' class="dialogInput dialogScale">
            <option value="abs">%s</option>
            <option value="max" selected="selected">%s</option>
            <option value="min">%s</option>
            <option value="mincrop">%s</option>
          </select>
        </line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Scaling mode')),
        papaya_strings::escapeHTMLChars($this->_gt('Absolute')),
        papaya_strings::escapeHTMLChars($this->_gt('Maximum')),
        papaya_strings::escapeHTMLChars($this->_gt('Minimum')),
        papaya_strings::escapeHTMLChars($this->_gt('Minimum crop'))
      );
      $result .= sprintf(
        '</linegroup><linegroup caption="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Linking'))
      );
      $result .= sprintf(
        '<line caption="%s"><input type="text" name="imglink"'.
        ' onkeyup="createMediaImageTag();" class="dialogInput dialogScale" /></line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Link'))
      );
      $result .= sprintf(
        '<line caption="%s">
          <select name="imgtarget" onchange="createMediaImageTag();"'.
          ' class="dialogSelect dialogScale">
            <option value="">%s</option>
            <option value="_self">%s</option>
            <option value="_parent">%s</option>
            <option value="_top">%s</option>
            <option value="_blank">%s</option>
          </select>
        </line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Link target')),
        papaya_strings::escapeHTMLChars($this->_gt('Default')),
        papaya_strings::escapeHTMLChars($this->_gt('Current window and frame')),
        papaya_strings::escapeHTMLChars($this->_gt('Current window and parent frame')),
        papaya_strings::escapeHTMLChars($this->_gt('Current window and top frame')),
        papaya_strings::escapeHTMLChars($this->_gt('New window'))
      );
      $result .= sprintf(
        '</linegroup><linegroup caption="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Formatting'))
      );
      $result .= sprintf(
        '<line caption="%s">
          <select name="imgalign" onchange="createMediaImageTag();"'.
          ' class="dialogSelect dialogScale">
            <option value="">%s</option>
            <option value="left">%s</option>
            <option value="right">%s</option>
            <option value="center">%s</option>
            <option value="middle">%s</option>
          </select>
        </line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Alignment')),
        papaya_strings::escapeHTMLChars($this->_gt('None')),
        papaya_strings::escapeHTMLChars($this->_gt('Left')),
        papaya_strings::escapeHTMLChars($this->_gt('Right')),
        papaya_strings::escapeHTMLChars($this->_gt('Center (horizontal)')),
        papaya_strings::escapeHTMLChars($this->_gt('Middle (vertical)'))
      );
      $result .= sprintf(
        '<line caption="%s"><input type="text" name="imglspace"'.
        ' onkeyup="createMediaImageTag();" class="dialogInput dialogScale" value="" /></line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Left space'))
      );
      $result .= sprintf(
        '<line caption="%s"><input type="text" name="imgtspace"'.
        ' onkeyup="createMediaImageTag();" class="dialogInput dialogScale" value="" /></line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Top space'))
      );
      $result .= sprintf(
        '<line caption="%s"><input type="text" name="imgrspace"'.
        ' onkeyup="createMediaImageTag();" class="dialogInput dialogScale" value="" /></line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Right space'))
      );
      $result .= sprintf(
        '<line caption="%s"><input type="text" name="imgbspace"'.
        ' onkeyup="createMediaImageTag();" class="dialogInput dialogScale" value="" /></line>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Bottom space'))
      );
      $result .= '</linegroup></lines>';
      $result .= '</dialog>';
      $result .= '<script type="text/javascript">
        <![CDATA[
          createMediaImageTag();
        ]]>
        </script>';
    }
    return $result;
  }

  // ------------------------------ batch actions ------------------------------

  /**
  * initialize confirmation dialog to delete multiple files
  */
  function initializeDeleteMultipleFilesDialog() {
    $files = $this->getFilesById(array_keys($this->params['batch_files']));
    if (is_array($files) && count($files) > 0) {
      $hidden = array(
        'cmd' => $this->params['cmd'],
        'batch_files' => $this->params['batch_files'],
        'confirm' => 1,
      );

      $fileNames = array();
      foreach ($files as $file) {
        $fileNames[] = $file['file_name'];
      }

      $fileNamesList = '"'.implode('", "', $fileNames).'"';

      $this->dialog = new base_msgdialog(
        $this,
        $this->paramName,
        $hidden,
        sprintf(
          $this->_gt('Do you really want to delete %s ?'),
          $fileNamesList
        ),
        'warning'
      );
      $this->dialog->buttonTitle = 'Delete';
    } else {
      $this->addMSG(MSG_INFO, $this->_gt('No files found.'));
    }
  }

  /**
  * delete multiple files
  *
  * @param array $fileIds list of file ids to be deleted
  */
  function deleteMultipleFiles($fileIds) {
    if ($allowed = $this->checkActionPermission('delete_file', $fileIds)) {

      $files = $this->getFilesById($fileIds);

      foreach ($fileIds as $fileId) {
        if (!isset($allowed[$fileId]) && isset($files['file_name'])) {
          $notAllowed[] = $files['file_name'];
        }
      }

      if (is_array($files) && count($files) > 0) {
        foreach ($allowed as $fileId) {
          $file = $files[$fileId];
          if ($this->deleteFile($fileId)) {
            $deleted[] = $file['file_name'];
          } else {
            $failed[] = $file['file_name'];
          }
          if (isset($this->params['file_id']) && $this->params['file_id'] == $fileId) {
            unset($this->currentFile);
            unset($this->params['file_id']);
          }
        }

        if (isset($deleted)) {
          if (count($deleted) == 1) {
            $this->addMsg(MSG_INFO, sprintf($this->_gt('File "%s" deleted.'), $deleted[0]));
          } elseif (count($deleted) > 1) {
            $fileNames = implode('", "', $deleted);
            $this->addMsg(MSG_INFO, sprintf($this->_gt('Files "%s" deleted.'), $fileNames));
          }
        }
        if (isset($failed) && count($failed) > 0) {
          $fileNames = implode('", "', $failed);
          $this->addMsg(MSG_ERROR, sprintf($this->_gt('Failed to delete "%s".'), $fileNames));
        }
        if (isset($notAllowed) && is_array($notAllowed) > 0) {
          $this->addMsg(
            MSG_ERROR,
            sprintf($this->_gt('Failed to delete "%s", permission denied.'), $notAllowed)
          );
        }
      }
    }
  }

  /**
  * move multiple files to a single destination folder
  *
  * @param array $fileIds list of file ids
  * @param integer $targetFolderId folder id of folder to move the files to
  * @return boolean TRUE on success, otherwise FALSE, adds user messages as well
  */
  function moveMultipleFiles($fileIds, $targetFolderId) {
    if ($this->checkActionPermission('edit_file', NULL, $targetFolderId)) {
      $allowed = $this->checkActionPermission('edit_file', $fileIds);
      foreach ($fileIds as $fileId) {
        if (!isset($allowed[$fileId])) {
          $notAllowed[$fileId] = $fileId;
        }
      }

      if ($targetFolderId == -1) {
        $folderName = $this->_gt('Clipboard');
      } elseif ($targetFolderId == 0) {
        $folderName = $this->_gt('Desktop');
      } else {
        $folderData = $this->getFolder($targetFolderId);
        if (isset($folderData[$this->papaya()->administrationLanguage->id])) {
          $folderName = $folderData[$this->papaya()->administrationLanguage->id]['folder_name'];
        } elseif (isset($folderData[PAPAYA_CONTENT_LANGUAGE])) {
          $folderName = '['.$folderData[PAPAYA_CONTENT_LANGUAGE]['folder_name'].']';
        } else {
          $folderName = $this->_gt('No title');
        }
      }
      $files = $this->getFilesById($fileIds);
      foreach ($allowed as $fileId) {
        if ($files[$fileId]['folder_id'] == $targetFolderId) {
          $sameFolder[$fileId] = $fileId;
        } else {
          $needToMove[$fileId] = $fileId;
        }
      }
      if (isset($needToMove) && is_array($needToMove) && count($needToMove) > 0) {
        $succeeded = parent::moveMultipleFiles($allowed, $targetFolderId);
      }

      foreach ($files as $fileId => $file) {
        if (isset($succeeded) && $succeeded) {
          $this->addMsg(
            MSG_INFO,
            sprintf(
              $this->_gt('Moved file "%s" (%s) to folder "%s".'),
              $file['file_name'],
              $fileId,
              $folderName
            )
          );
        } elseif (isset($sameFolder) && is_array($sameFolder) && isset($sameFolder[$fileId])) {
          $this->addMsg(
            MSG_WARNING,
            sprintf(
              $this->_gt('No need to move "%s" (%s), it`s already in the target folder.'),
              $file['file_name'],
              $fileId
            )
          );
        } elseif (isset($notAllowed) && is_array($notAllowed) && isset($notAllowed[$fileId])) {
          $this->addMsg(
            MSG_WARNING,
            sprintf(
              $this->_gt('Could not move "%s" (%s), permission denied.'),
              $file['file_name'],
              $fileId
            )
          );
        } else {
          $this->addMsg(
            MSG_INFO,
            sprintf(
              $this->_gt('Failed to move file "%s" (%s) to "%s".'),
              $file['file_name'],
              $fileId,
              $folderName
            )
          );
        }
      }
    }
  }

  /**
  * initialize dialog to move multiple files
  */
  function initializeMoveMultipleFilesDialog() {
    $data = array();
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'confirm' => 1,
      'batch_files' => $this->params['batch_files'],
    );

    $fields = array(
      'target_folder_id' => array(
        'Target folder', 'isNum', TRUE, 'combo',
        $this->getFolderComboArray($this->papaya()->administrationLanguage->id)
      ),
    );

    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $this->dialog->dialogTitle = $this->_gt('Move files');
    $this->dialog->buttonTitle = 'Move';
    $this->dialog->inputFieldSize = 'large';
  }

  /**
  * initialize confirmation dialog to put multiple files on the clipboard
  */
  function initializeCutMultipleFilesDialog() {
    $files = $this->getFilesById(array_keys($this->params['batch_files']));
    if (is_array($files) && count($files) > 0) {
      $hidden = array(
        'cmd' => $this->params['cmd'],
        'batch_files' => $this->params['batch_files'],
        'confirm' => 1,
      );

      $fileNames = array();
      foreach ($files as $file) {
        $fileNames[] = $file['file_name'];
      }

      $fileNamesList = '"'.implode('", "', $fileNames).'"';

      $this->dialog = new base_msgdialog(
        $this,
        $this->paramName,
        $hidden,
        sprintf(
          $this->_gt('Do you really want to move %s to the clipboard?'),
          $fileNamesList
        ),
        'question'
      );
      $this->dialog->buttonTitle = 'Move';
    } else {
      $this->addMSG(MSG_INFO, $this->_gt('No files found.'));
    }
  }

  /**
  * initialize dialog to tag multiple files at once
  */
  function initializeTagMultipleFilesDialog() {
    $fileIds = array_keys($this->params['batch_files']);
    sort($fileIds);
    $allowed = array_keys($this->checkActionPermission('edit_file', array_keys($this->params['batch_files'])));
    sort($allowed);
    if ($allowed) {
      if ($allowed === $fileIds) {
        //if getInstance returns NULL, we get a FALSE value
        if ($tags = papaya_taglinks::getInstance($this, 'tg')) {
          $tags->setLinkParams(
            $this->paramName,
            array(
              'cmd' => $this->params['cmd'],
              'batch_files' => $this->params['batch_files'],
            )
          );
          $this->layout->addRight(
            $tags->getTagLinker('media', $fileIds, TRUE)
          );
        } else {
          $this->addMsg(MSG_WARNING, $this->_gt('You don\'t have the permission to tag files.'));
        }
      }
    } else {
      $this->addMsg(
        MSG_WARNING,
        $this->_gt('You don\'t have the permission to modify some of the selected files.')
      );
    }
  }

  // ------------------------------- import local folder -----------------------

  /**
  * load subfolders of a given folder
  *
  * @param string $folder folder location
  * @return array $result recursive structure of subfolders
  */
  function getLocalSubFolders($folder) {
    $result = array();
    if (isset($folder) && is_dir($folder) && $dh = dir($folder)) {
      while (FALSE !== ($file = $dh->read())) {
        if ($file != '.' && $file != '..' &&
            is_dir($folder.'/'.$file)) {
          $result[$file] = $folder.'/'.$file;
        }
      }
      foreach ($result as $name => $path) {
        $result[$name] = $this->getLocalSubFolders($path);
      }
    }
    return $result;
  }

  /**
  * generate a hierarchical list of folders that are located below the import folder
  *
  * @access private
  * @return string $result folder list xml
  */
  function getLocalFolderXML() {
    $result = '';
    if (is_dir(PAPAYA_PATH_MEDIADB_IMPORT)) {
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Folder list'))
      );
      $baseFolder = basename(PAPAYA_PATH_MEDIADB_IMPORT);
      $result .= '<items>'.LF;
      if (empty($this->params['folder']) || $this->params['folder'] == '/') {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $result .= sprintf(
        '<listitem node="open" image="%s" href="%s" title="%s" %s/>'.LF,
        papaya_strings::escapeHTMLChars($this->papaya()->images['items-folder']),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'import_folder', 'folder' => '/'))
        ),
        papaya_strings::escapeHTMLChars($baseFolder),
        $selected
      );
      $list = $this->getLocalSubFolders(PAPAYA_PATH_MEDIADB_IMPORT);
      if (is_array($list) && count($list) > 0) {
        $result .= $this->getLocalFolderItemsXML($list, 1, '');
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
   * generate a listitem entry for a folder
   *
   * @access private
   * @param $list
   * @param $indent
   * @param $parentPath
   * @return string $result folder listitem xml
   */
  function getLocalFolderItemsXML($list, $indent, $parentPath) {
    $result = '';
    if (is_array($list) && count($list) > 0) {
      $images = $this->papaya()->images;
      foreach ($list as $folderName => $subFolders) {
        if (is_array($subFolders) && count($subFolders) > 0) {
          $node = 'open';
        } else {
          $node = 'close';
        }
        if (isset($this->params['folder']) &&
            $this->params['folder'] == $parentPath.'/'.$folderName) {
          $selected = ' selected="selected"';
        } else {
          $selected = ' ';
        }
        $result .= sprintf(
          '<listitem node="%s" image="%s" indent="%d" title="%s" %s href="%s" />'.LF,
          papaya_strings::escapeHTMLChars($node),
          papaya_strings::escapeHTMLChars($images['items-folder']),
          (int)$indent,
          papaya_strings::escapeHTMLChars($folderName),
          $selected,
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array('cmd' => 'import_folder', 'folder' => $parentPath.'/'.$folderName)
            )
          )
        );
        $result .= $this->getLocalFolderItemsXML(
          $subFolders, $indent + 1, $parentPath.'/'.$folderName
        );
      }
    }
    return $result;
  }

  /**
   * generate a listview with information on the files located in the selected folder
   *
   * @param string $folder
   * @return string $result files information listview xml
   */
  function getLocalFolderProperties($folder) {
    $result = '';
    $folderLocation = realpath(PAPAYA_PATH_MEDIADB_IMPORT.$folder).'/';
    $folderLocation = str_replace('\\', '/', $folderLocation); // Windows
    // prevent user to escape from the import path
    if (0 === strpos($folderLocation, PAPAYA_PATH_MEDIADB_IMPORT)) {
      if (is_dir($folderLocation)) {
        $result .= sprintf(
          '<listview title="%s">'.LF,
          papaya_strings::escapeHTMLChars(str_replace('//', '/', $folder))
        );
        $files = $this->getLocalFolderFiles($folderLocation);
        if (is_array($files) && count($files) > 0) {
          $extensions = array();
          foreach ($files as $file) {
            $extension = strtolower($this->getFileExtension($file));
            if (isset($extensions[$extension])) {
              $extensions[$extension]++;
            } else {
              $extensions[$extension] = 1;
            }
          }
          $result .= sprintf(
            '<cols><col>%s</col><col>%s</col><col>%s</col></cols>'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('File extension')),
            papaya_strings::escapeHTMLChars($this->_gt('File type')),
            papaya_strings::escapeHTMLChars($this->_gt('Number of files'))
          );
          $result .= '<items>'.LF;
          foreach ($extensions as $extension => $count) {
            if ($extension == '') {
              $extension = $this->_gt('no extension');
              $mimeType = $this->_gt('unknown');
            } else {
              $mimeTypeData = $this->mimeObj->getMimeTypeByExtension($extension);
              $mimeType = $mimeTypeData['mimetype'];
            }
            if (isset($mimeTypeData) && $mimeTypeData['mimetype_icon'] != '') {
              $image = $this->mimeObj->getMimeTypeIcon($mimeTypeData['mimetype_icon']);
            } else {
              $image = $this->mimeObj->getMimeTypeIcon($this->defaultTypeIcon);
            }
            $result .= sprintf(
              '<listitem image="%s" title="%s">'.LF,
              papaya_strings::escapeHTMLChars($image),
              papaya_strings::escapeHTMLChars($extension)
            );
            $result .= sprintf(
              '<subitem>%s</subitem>'.LF,
              papaya_strings::escapeHTMLChars($mimeType)
            );
            $result .= sprintf(
              '<subitem>%d</subitem>'.LF,
              papaya_strings::escapeHTMLChars($count)
            );
            $result .= '</listitem>'.LF;
          }
          $result .= '</items>'.LF;
          $this->initializeImportLocalFolderDialog();
        } else {
          $result .= '<items>'.LF;
          $result .= sprintf(
            '<listitem title="%s" />'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('No files found.'))
          );
          $result .= '</items>'.LF;
        }
        $result .= '</listview>'.LF;
      }
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Import of folder outside import path denied.'));
    }
    return $result;
  }

  /**
  * initialize dialog to import a folder to the media db
  */
  function initializeImportLocalFolderDialog() {
    $data = array();
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'confirm' => 1,
      'folder' => $this->params['folder'],
    );

    $fields = array(
      'target_folder_id' => array(
        'Target folder', 'isNum', TRUE, 'combo',
        $this->getFolderComboArray($this->papaya()->administrationLanguage->id)
      ),
    );

    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $this->dialog->dialogTitle = $this->_gt('Import folder');
    $this->dialog->buttonTitle = 'Import';
    $this->dialog->inputFieldSize = 'large';
  }

  /**
  * import the files in a local folder to the media db
  *
  * this method is used to import files in a given local folder to a mediadb folder
  *
  * @param string $folder path of folder to import
  * @param integer $targetFolderId media db folder to add the files to
  * @param integer $surferId id of surfer to set as file owner
   * @return bool
   */
  function importLocalFolder($folder, $targetFolderId, $surferId) {
    $folderLocation = realpath(PAPAYA_PATH_MEDIADB_IMPORT.$folder).'/';
    $folderLocation = str_replace('\\', '/', $folderLocation);
    // prevent user to escape from the import path
    if (0 === strpos($folderLocation, PAPAYA_PATH_MEDIADB_IMPORT)) {
      if (isset($targetFolderId)) {
        $files = $this->getLocalFolderFiles($folderLocation);
        if (is_array($files) && count($files) > 0) {
          foreach ($files as $file) {
            $fileId = $this->addFile($folderLocation.'/'.$file, $file, $targetFolderId, $surferId);
            if ($fileId) {
              $this->addMsg(MSG_INFO, sprintf($this->_gt('Added file "%s" (%s).'), $file, $fileId));
            } else {
              $this->addMsg(MSG_ERROR, sprintf($this->_gt('Could not add file "%s".'), $file));
            }
          }
          return TRUE;
        }
      }
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Import of folder outside import path denied.'));
    }
    return FALSE;
  }

  /**
  * get a list of files located in a given local folder
  *
  * @param string $path folder to find files in
   * @return array
   */
  function getLocalFolderFiles($path) {
    $result = array();
    if ($dh = dir($path)) {
      while (FALSE !== ($file = $dh->read())) {
        if ($file != '.' && $file != '..' &&
            !is_dir($path.'/'.$file)) {
          $result[$file] = $file;
        }
      }
    }
    return $result;
  }

  public function isFolderSelected() {
    return (
      isset($this->currentFolder) &&
      is_array($this->currentFolder) &&
      $this->currentFolder['folder_id'] > 0
    );
  }

  public function isFileInClipboard() {
    return (isset($this->currentFile) && $this->currentFile['folder_id'] < 0);
  }

  /**
   * Validate the ancestors path of the given folder id and repair it if neccessary.
   *
   * @param integer $folderId
   * @param bool $repair
   * @return array
   */
  public function validateAncestors($folderId, $repair = FALSE) {
    if (isset($this->folders[$folderId])) {
      $folder = $this->folders[$folderId];
      if (empty($folder['ancestors_validated'])) {
        $ancestors = $this->validateAncestors($folder['parent_id']);
        $ancestors[] = $folder['parent_id'];
        $this->folders[$folderId]['ancestors_validated'] = $ancestors;
      } else {
        $ancestors = $folder['ancestors_validated'];
      }
      if ($repair) {
        $newPath = \Papaya\Utility\Arrays::encodeAndQuoteIdList($ancestors);
        if ($newPath != $folder['parent_path']) {
          $data = array(
            'parent_path' => $newPath
          );
          $filter = array(
            'folder_id' => $folderId
          );
          $this->databaseUpdateRecord($this->tableFolders, $data, $filter);
          $this->folders[$folderId]['parent_path'] = $folder['parent_path'] = $newPath;
        }
      }
      return $ancestors;
    }
    return array();
  }
}
