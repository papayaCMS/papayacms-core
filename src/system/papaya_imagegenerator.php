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
* papaya object to configure dynamic imageConfs
*
* @package Papaya
* @subpackage Images-Dynamic
*/
class papaya_imagegenerator extends base_imagegenerator {

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'imgdyn';

  /**
  * papaya database table image configurations
  * @var string $tableImageConfs
  */
  var $tableImageConfs = PAPAYA_DB_TBL_IMAGES;
  /**
  * papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * papaya database table module groups
  * @var string $tableModuleGroups
  */
  var $tableModuleGroups = PAPAYA_DB_TBL_MODULEGROUPS;
  /**
  * List of image configurations
  * @var array $imageConfs
  */
  var $imageConfs = NULL;
  /**
  * List of module groups
  * @var array $modules
  */
  var $moduleGroups = NULL;
  /**
  * List of dynamic image modules
  * @var array $modules
  */
  var $modules = NULL;

  /**
   * @var \Papaya\Template
   */
  public $layout = NULL;

  /**
   * @var array|\Papaya\UI\Images
   */
  public $images = NULL;

  /**
   * @var base_dialog
   */
  private $dialogImageConf;

  /**
  * compile data
  *
  * @access public
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('mode');
    $this->initializeSessionParam('image_id');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * use data
  *
  * @access public
  */
  function execute() {
    $this->loadModules();
    $this->loadModuleGroups();
    if (!isset($this->params['mode'])) {
      $this->params['mode'] = 0;
    }
    switch ($this->params['mode']) {
    case 2:
      if (isset($this->params['image_id']) && $this->params['image_id'] > 0 &&
          $this->loadImageConf($this->params['image_id'])) {
        $this->executeImageConfModulePreview();
      }
      break;
    case 1:
      if (isset($this->params['image_id']) && $this->params['image_id'] > 0 &&
          $this->loadImageConf($this->params['image_id'])) {
        $this->executeImageConfModuleEdit();
      }
      break;
    default:
      if (isset($this->params['cmd'])) {
        switch ($this->params['cmd']) {
        case 'image_add':
          $this->initializeImageConfDialog();
          if ($this->dialogImageConf->checkDialogInput() &&
              $this->checkImageConfIdent()) {
            if ($newId = $this->createImageConf()) {
              unset($this->dialogImageConf);
              $this->params['image_id'] = $newId;
              $this->addMsg(MSG_INFO, sprintf($this->_gt('%s added.'), 'Image'));
            }
          }
          break;
        case 'image_edit':
          if (isset($this->params['image_id']) && $this->params['image_id'] > 0 &&
              $this->loadImageConf($this->params['image_id'])) {
            $this->initializeImageConfDialog();
            if ($this->dialogImageConf->checkDialogInput() &&
                $this->checkImageConfIdent($this->params['image_id'])) {
              $oldIdent = $this->imageConf['image_ident'];
              if ($this->saveImageConf()) {
                unset($this->dialogImageConf);
                $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
                $this->deleteCache($oldIdent);
              }
            }
          }
          break;
        case 'image_delete':
          if (isset($this->params['confirm_delete']) && $this->params['confirm_delete'] &&
              isset($this->params['image_id']) && $this->params['image_id'] > 0 &&
                $this->loadImageConf($this->params['image_id'])) {
            $this->deleteCache($this->imageConf['image_ident']);
            if ($this->deleteImageConf()) {
              $this->params['cmd'] = '';
              unset($this->imageConf);
              unset($this->params['image_id']);
            }
          }
          break;
        case 'delete_cache':
          if (isset($this->params['image_id']) && $this->params['image_id'] > 0 &&
                $this->loadImageConf($this->params['image_id'])) {
            if ($counter = $this->deleteCache($this->imageConf['image_ident'])) {
              $this->addMsg(
                MSG_INFO,
                sprintf($this->_gt('%s files deleted.'), $counter)
              );
            } else {
              $this->addMsg(
                MSG_INFO,
                $this->_gt('Cache was empty - no files deleted.')
              );
            }
          }
          break;
        }
      }
      break;
    }
    $this->loadImageConfs();
    if (isset($this->params['image_id']) && $this->params['image_id'] > 0) {
      $this->loadImageConf($this->params['image_id']);
    }
  }

  /**
  * Use image config module, editor
  *
  * @access public
  * @return boolean
  */
  function executeImageConfModuleEdit() {
    if (isset($this->imageConf) && isset($this->imageConf['module_guid'])) {
      $parent = NULL;
      $moduleObj = $this->papaya()->plugins->get(
        $this->imageConf['module_guid'],
        $parent,
        $this->imageConf['image_data']
      );
      if (isset($moduleObj) && is_object($moduleObj)) {
        $moduleObj->images = $this->images;
        $moduleObj->paramName = $this->paramName;
        $hidden = array(
          'image_id' => $this->imageConf['image_id'],
          'cmd' => 'image_data_edit'
        );
        $moduleObj->initializeDialog($hidden);
        if ($moduleObj->modified()) {
          if ($moduleObj->checkData()) {
            if ($this->saveImageConfContent($this->imageConf['image_id'], $moduleObj->getData())) {
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
              $this->deleteCache($this->imageConf['image_ident']);
            }
          }
        }
        $this->layout->add($moduleObj->getForm());
        unset($moduleObj);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Use image config module, preview
  *
  * @access public
  */
  function executeImageConfModulePreview() {
    if (isset($this->imageConf) && isset($this->imageConf['module_guid'])) {
      $parent = NULL;
      $moduleObj = $this->papaya()->plugins->get(
        $this->imageConf['module_guid'],
        $parent,
        $this->imageConf['image_data']
      );
      if (isset($moduleObj) && is_object($moduleObj)) {
        $moduleObj->images = $this->images;
        $moduleObj->paramName = $this->paramName;
        $this->layout->add($moduleObj->getAttributeDialog());
        foreach ($moduleObj->attributeFields as $fieldName => $field) {
          if (!isset($moduleObj->attributes[$fieldName]) && isset($field[6])) {
            $moduleObj->attributes[$fieldName] = $field[6];
          }
        }
        if (isset($moduleObj->attributeFields['image'])) {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt(
              'There may not be an attribute named \'image\'.'.
              ' This image generator won\'t work correclty.'
            )
          );
        }
        $extension = $this->getFileExtension($this->imageConf['image_format']);
        $url = '../'.$this->escapeForFilename($this->imageConf['image_ident'], 'dynamic').
          '.image.'.$extension.'.preview'.$this->encodeQueryString($moduleObj->attributes, 'img');
        $iFrame = sprintf(
          '<panel width="100%%" title="%s"><iframe width="100%%"'.
          ' noresize="noresize" hspace="0" vspace="0" align="center" scrolling="auto"'.
          ' height="400" src="%s" class="inset" id="preview" /></panel>',
          papaya_strings::escapeHTMLChars($this->_gt('Preview')),
          papaya_strings::escapeHTMLChars($url)
        );
        $this->layout->add($iFrame);
      }
    }
  }

  /**
  * generate output xml for admin interface
  *
  * @access public
  */
  function getXML() {
    $this->getButtonsXML();
    $this->getImageConfList();
    if (!isset($this->params['mode'])) {
      $this->params['mode'] = 0;
    }
    switch ($this->params['mode']) {
    case '2': //preview
      break;
    case '1': //edit module options
      break;
    default:
      if (isset($this->params['cmd']) &&
          $this->params['cmd'] == 'image_delete' &&
          isset($this->imageConf)) {
        $this->getImageConfDeleteDialog();
      } else {
        $this->getImageConfDialog();
      }
      break;
    }
  }

  /**
  * Get menubar and toolbar XML
  *
  * @access public
  */
  function getButtonsXML() {
    $menubar = new base_btnbuilder;
    $menubar->images = $this->images;
    $menubar->addButton(
      'Add image',
      $this->getLink(
        array(
          'cmd' => 'image_add',
          'mode' => 0,
          'image_id' => 0
        )
      ),
      'actions-graphic-add'
    );
    if (isset($this->imageConf)) {
      $menubar->addButton(
        'Delete',
        $this->getLink(
          array(
            'cmd' => 'image_delete',
            'mode' => 0,
            'image_id' => (int)$this->imageConf['image_id']
          )
        ),
        'actions-graphic-delete'
      );
      $menubar->addSeperator();
      $menubar->addButton(
        'Delete Cache',
        $this->getLink(
          array(
            'cmd' => 'delete_cache',
            'mode' => 0,
            'image_id' => (int)$this->imageConf['image_id']
          )
        ),
        'actions-edit-clear'
      );
      $toolbar = new base_btnbuilder;
      $toolbar->images = $this->images;
      $toolbar->addButton(
        'Properties',
        $this->getLink(
          array(
            'cmd' => 'chg_mode',
            'mode' => 0,
            'image_id' => (int)$this->imageConf['image_id']
          )
        ),
        'categories-properties',
        '',
        isset($this->params['mode']) && $this->params['mode'] == 0
      );
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Content',
        $this->getLink(
          array(
            'cmd' => 'chg_mode',
            'mode' => 1,
            'image_id' => (int)$this->imageConf['image_id']
          )
        ),
        'categories-content',
        'Edit content',
        isset($this->params['mode']) && $this->params['mode'] == 1
      );
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Preview',
        $this->getLink(
          array(
            'cmd' => 'chg_mode',
            'mode' => 2,
            'image_id' => (int)$this->imageConf['image_id']
          )
        ),
        'categories-preview',
        'Page preview',
        isset($this->params['mode']) && $this->params['mode'] == 2
      );

      if ($str = $toolbar->getXML()) {
        $this->layout->add('<toolbar>'.$str.'</toolbar>', 'toolbars');
      }
    }
    $this->layout->addMenu(
      sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $menubar->getXML())
    );
  }

  /**
  * Load image configurations
  *
  * @access public
  */
  function loadImageConfs() {
    unset($this->imageConfs);
    $sql = "SELECT image_id, image_ident, image_title
              FROM %s
             ORDER BY image_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableImageConfs)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->imageConfs[$row['image_id']] = $row;
      }
    }
  }

  /**
  * Load dynamic image modules
  *
  * @access public
  */
  function loadModules() {
    unset($this->modules);
    $sql = "SELECT module_guid, module_title, module_type, modulegroup_id
              FROM %s
             WHERE (module_type = 'image') AND module_active = 1
             ORDER BY module_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableModules)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->modules[$row['module_guid']] = $row;
      }
    }
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
  * check if the image_ident parameter is unique or the ident of the selected dynamic image
  *
  * @param integer $imageId
  * @return boolean
  */
  function checkImageConfIdent($imageId = 0) {
    $sql = "SELECT image_id
              FROM %s
             WHERE image_ident = '%s'";
    $params = array($this->tableImageConfs, $this->params['image_ident']);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        if ($imageId == $row[0]) {
          return TRUE;
        } else {
          $this->addMsg(MSG_ERROR, 'Image ident must be unique.');
        }
      } else {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load a single image using the record id
  *
  * @param integer $imageId
  * @access public
  * @return boolean
  */
  function loadImageConf($imageId) {
    unset($this->imageConf);
    $sql = "SELECT i.image_id, i.image_ident, i.image_title, i.image_data, i.image_modified,
                   i.image_format, i.image_cachemode, i.image_cachetime,
                   m.module_guid, m.module_path, m.module_file, m.module_class, m.module_title
              FROM %s i
              LEFT OUTER JOIN %s m ON (m.module_guid = i.module_guid)
             WHERE i.image_id = '%d'";
    $params = array($this->tableImageConfs, $this->tableModules, (int)$imageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->imageConf = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * create a new dynamic image configuration
  *
  * @access public
  * @return mixed integer id or FALSE
  */
  function createImageConf() {
    $data = array(
      'image_ident' => $this->params['image_ident'],
      'image_title' => $this->params['image_title'],
      'image_modified' => time(),
      'image_format' => 0,
      'image_cachemode' => 0,
      'image_cachetime' => 0,
      'module_guid' => $this->params['module_guid']
    );
    return $this->databaseInsertRecord($this->tableImageConfs, 'image_id', $data);
  }

  /**
  * Save new data for an existing dynamic image configuration
  *
  * @access public
  * @return boolean
  */
  function saveImageConf() {
    $data = array(
      'image_ident' => $this->params['image_ident'],
      'image_title' => $this->params['image_title'],
      'image_modified' => time(),
      'image_format' => $this->params['image_format'],
      'image_cachemode' => $this->params['image_cachemode'],
      'image_cachetime' => $this->params['image_cachetime'],
      'module_guid' => $this->params['module_guid']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableImageConfs,
      $data,
      'image_id',
      $this->params['image_id']
    );
  }

  /**
   * Save new data for an existing dynamic image configuration
   *
   * @access public
   * @param $id
   * @param $xmlStr
   * @return boolean
   */
  function saveImageConfContent($id, $xmlStr) {
    $data = array(
      'image_modified' => time(),
      'image_data' => $xmlStr
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableImageConfs,
      $data,
      'image_id',
      $id
    );
  }

  /**
  * delete dynamic image configuration
  *
  * @access public
  * @return boolean
  */
  function deleteImageConf() {
    return FALSE !== $this->databaseDeleteRecord(
      $this->tableImageConfs,
      'image_id',
      $this->params['image_id']
    );
  }

  /**
  * Initialize dialog object for  an image configuration
  *
  * @access public
  */
  function initializeImageConfDialog() {
    if (!(isset($this->dialogImageConf) && is_object($this->dialogImageConf))) {
      if (isset($this->imageConf) && is_array($this->imageConf)) {
        $data = $this->imageConf;
        $hidden = array(
          'image_id' => (int)$this->imageConf['image_id'],
          'cmd' => 'image_edit'
        );
        $btnCaption = 'Edit';
      } else {
        $data = array();
        $hidden = array(
          'image_id' => 0,
          'cmd' => 'image_add'
        );
        $btnCaption = 'Add';
      }
      $imageModules = array();
      if (isset($this->modules) && is_array($this->modules) && count($this->modules) > 0) {
        foreach ($this->modules as $module) {
          if (isset($this->moduleGroups[$module['modulegroup_id']])) {
            $imageModules[$module['module_guid']] =
              '['.$this->moduleGroups[$module['modulegroup_id']]['modulegroup_title'].
              '] '.$module['module_title'];
          } else {
            $imageModules[$module['module_guid']] = $module['module_title'];
          }
        }
        asort($imageModules);
      }
      $systemImageFormat = $this->papaya()->options->get('PAPAYA_THUMBS_FILETYPE');
      $imageFormats = array(
        0 => $this->_gt('System').': '.$this->_validFormats[$systemImageFormat],
        IMAGETYPE_GIF => $this->_validFormats[IMAGETYPE_GIF],
        IMAGETYPE_JPEG => $this->_validFormats[IMAGETYPE_JPEG],
        IMAGETYPE_PNG => $this->_validFormats[IMAGETYPE_PNG]
      );
      $systemCacheTime = $this->papaya()->options->get('PAPAYA_CACHE_TIME_FILES');
      $cacheModes = array(
        0 => $this->_gt('No Cache'),
        1 => $this->_gt('System time').': '.$systemCacheTime,
        2 => $this->_gt('Own time'),
      );
      $fields = array(
        'image_title' => array('Title', 'isNoHTML', TRUE, 'input', 100, ''),
        'image_ident' =>
           array('Ident', '~^[a-z\d]+$~', TRUE, 'input', 20, ''.
             'The "ident value" is used in the image url to identify it.'),
        'module_guid' => array('Module', 'isGuid', TRUE, 'combo', $imageModules, ''),
        'image_format' => array(
          'Format', 'isNum', TRUE, 'combo', $imageFormats, '', $systemImageFormat
        ),
        'Cache',
        'image_cachemode' => array(
          'Mode', 'isNum', TRUE, 'combo', $cacheModes, '', 1
        ),
        'image_cachetime' => array('Time (seconds)', 'isNum', TRUE, 'input', 10, '', 0)
      );
      $this->dialogImageConf = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogImageConf->loadParams();
      $this->dialogImageConf->dialogTitle = $this->_gt('Properties');
      $this->dialogImageConf->buttonTitle = $btnCaption;
      $this->dialogImageConf->inputFieldSize = 'x-large';
      $this->dialogImageConf->dialogDoubleButtons = FALSE;
    }
  }

  /**
  * get XML output of image configuration dialog
  *
  * @access public
  */
  function getImageConfDialog() {
    $this->initializeImageConfDialog();
    $this->layout->add($this->dialogImageConf->getDialogXML());
  }

  /**
  * Get delete confirmation dialog
  *
  * @access public
  */
  function getImageConfDeleteDialog() {
    $hidden = array(
      'cmd' => 'image_delete',
      'image_id' => $this->imageConf['image_id'],
      'confirm_delete' => 1,
    );
    $msg = sprintf(
      $this->_gt('Delete image "%s" (%s)?'),
      papaya_strings::escapeHTMLChars($this->imageConf['image_title']),
      (int)$this->imageConf['image_id']
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->buttonTitle = 'Delete';
    $this->layout->add($dialog->getMsgDialog());
  }

  /**
  * get XML output of image configuration listview
  *
  * @access public
  */
  function getImageConfList() {
    $result = sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Images'))
    );
    $result .= '<items>';
    if (isset($this->imageConfs) && is_array($this->imageConfs)) {
      foreach ($this->imageConfs as $imageConf) {
        if (isset($this->params['image_id']) &&
            $imageConf['image_id'] == $this->params['image_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s"%s/>',
          papaya_strings::escapeHTMLChars($imageConf['image_title']),
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('image_id' => $imageConf['image_id']))
          ),
          papaya_strings::escapeHTMLChars($this->images['items-graphic']),
          $selected
        );
      }
    }
    $result .= '</items>';
    $result .= '</listview>';
    $this->layout->addLeft($result);
  }

  /**
  * delete cache for an ident
  *
  * @param string $ident
  * @access public
  * @return integer $counter
  */
  function deleteCache($ident) {
    $identStr = 'image_'.$this->escapeForFilename($ident).'_';
    $counter = 0;
    if ($dh = opendir(PAPAYA_PATH_CACHE)) {
      while ($file = readdir($dh)) {
        if (is_file(PAPAYA_PATH_CACHE.$file) && strpos($file, $identStr) === 0) {
          unlink(PAPAYA_PATH_CACHE.$file);
          $counter++;
        }
      }
    }
    return $counter;
  }
}

