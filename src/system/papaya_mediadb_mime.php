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

use Papaya\Administration;

/**
* Papaya media db administration class - provides the backend
*
* @package Papaya
* @subpackage Media-Database
*/
class papaya_mediadb_mime extends base_mediadb_edit {

  /**
  * position of dialogs (not messages!), 'left', 'right', 'center'
  */
  var $dialogPosition = 'center';

  /**
  * @var object $mimeObj instance of base_mediadb_mimetypes
  */
  var $mimeObj = NULL;

  /**
   * @var base_dialog|base_msgdialog|NULL
   */
  public $dialog = NULL;

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
   * @var base_btnbuilder
   */
  private $menubar;

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
  * php 4 constructur, calls papaya_mediadb::__construct()
  */
  function papaya_mediadb($paramName = 'mdb') {
    $this->__construct($paramName);
  }

  /**
  * initializes sessions, languageSelector, mimtypes object
  */
  function initialize() {
    $this->initializeMimeObject();
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeMimeTypesSessionParams();
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * initializes menu, calls execution of selected mode (files, mimetypes, maintenance)
  */
  function execute() {
    $this->menubar = new base_btnbuilder;
    $this->menubar->images = $this->papaya()->images;

    $administrationUser = $this->papaya()->administrationUser;
    if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_MIMETYPES_MANAGE)) {
      $this->executeMimeTypesHandling();
      $this->initializeMimeTypesLayout();
    } else {
      $this->addMsg(MSG_INFO, $this->_gt('You don\'t have the permission to access mimetypes.'));
    }
  }

  /**
  * initializes xml for menu and dialog
  */
  function getXML() {
    $this->layout->addMenu(sprintf('<menu>%s</menu>'.LF, $this->menubar->getXML()));
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
        case 'left':
          $this->layout->addLeft($dialogXML);
          break;
        case 'right':
          $this->layout->addRight($dialogXML);
          break;
        case 'center':
        default:
          $this->layout->add($dialogXML);
          break;
        }
        $added = TRUE;
      }
    }
  }

  // -------------------------------- MIMETYPES --------------------------------

  /**
  * This section contains the necessary methods for mimetype handling.
  *
  * Info: a list of official mimetypes can be found at http://www.iana.org/assignments/media-types/
  */

  /**
  * initializes mimetype specific session params
  */
  function initializeMimeTypesSessionParams() {
    $this->initializeSessionParam('group_id');
    $this->initializeSessionParam('open_groups');
    $this->checkOpenGroups();
  }

  /**
  * Select the group of the selected mimetype if a different group is selected.
  * Make sure the selected group is opened if it hasn't been closed explicitly.
  */
  function checkOpenGroups() {
    if (isset($this->params['mimetype_id']) && isset($this->params['group_id']) &&
        !isset($this->sessionParams['open_groups'][$this->params['group_id']])) {
      $mimeType = $this->mimeObj->getMimeType((int)$this->params['mimetype_id']);
      if ($mimeType['mimegroup_id'] != $this->params['group_id']) {
        $this->params['group_id'] = $mimeType['mimegroup_id'];
        $this->sessionParams['open_groups'][$mimeType['mimegroup_id']] = 1;
        $this->initializeSessionParam('group_id');
      }
    } elseif (isset($this->params['group_id']) &&
        !(isset($this->params['cmd']) && $this->params['cmd'] == 'close_group') &&
        !isset($this->sessionParams['open_groups'][$this->params['group_id']])) {
      $this->sessionParams['open_groups'][$this->params['group_id']] = 1;
    }
  }

  /**
  * executes mimetype administration actions
  */
  function executeMimeTypesHandling() {
    $administrationUser = $this->papaya()->administrationUser;
    if (isset($this->params['cmd'])) {
      switch($this->params['cmd']) {
      case 'add_group':
        if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_MIMETYPES_EDIT)) {
          $this->initializeMimeGroupEditDialog($this->params['cmd']);
          if (isset($this->params['confirm']) && $this->params['confirm']) {
            if ($this->dialog->checkDialogInput()) {
              $this->addMimeGroup();
            }
          }
        }
        break;
      case 'edit_group':
        $this->initializeMimeGroupEditDialog($this->params['cmd']);
        if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_MIMETYPES_EDIT)) {
          if (isset($this->params['confirm']) && $this->params['confirm']) {
            if ($this->dialog->checkDialogInput()) {
              $this->setMimeGroup();
            }
          }
        } else {
          $this->addMsg(
            MSG_INFO,
            $this->_gt('You don\'t have permission to change this. You can only view it.')
          );
        }
        break;
      case 'del_group':
        if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_MIMETYPES_EDIT)) {
          if (isset($this->params['confirm']) && $this->params['confirm']) {
            $this->delMimeGroup();
          } else {
            $this->getMimeGroupDeleteDialog();
          }
        }
        break;
      case 'add_type':
        if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_MIMETYPES_EDIT)) {
          $this->initializeMimeTypeEditDialog($this->params['cmd']);
          if (isset($this->params['confirm']) && $this->params['confirm']) {
            if ($this->dialog->checkDialogInput()) {
              $this->addMimeType();
              $this->initializeMimeTypeEditDialog('edit_type');
            }
          }
        }
        break;
      case 'edit_type':
        $this->initializeMimeTypeEditDialog($this->params['cmd']);
        if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_MIMETYPES_EDIT)) {
          if (isset($this->params['confirm']) && $this->params['confirm']) {
            if ($this->dialog->checkDialogInput()) {
              $this->setMimeType();
            }
          }
        } else {
          $this->addMsg(
            MSG_INFO,
            $this->_gt('You don\'t have permission to change this. You can only view it.')
          );
        }
        break;
      case 'del_type':
        if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_MIMETYPES_EDIT)) {
          if (isset($this->params['confirm']) && $this->params['confirm']) {
            $this->delMimeType();
          } else {
            $this->getMimeTypeDeleteDialog();
          }
        }
        break;
      case 'add_ext':
        if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_MIMETYPES_EDIT)) {
          $this->initializeMimeTypeExtensionDialog($this->params['cmd']);
          if (isset($this->params['confirm']) && $this->params['confirm']
              && $this->params['extension'] != ''
              && \PapayaFilterFactory::isText($this->params['extension'])) {
            $mimeTypes = $this->mimeObj->getMimeTypeByExtension($this->params['extension']);
            if (count($mimeTypes) > 0 && !isset($this->params['override'])) {
              $this->initializeMimeTypeExtensionConfirmDialog($mimeTypes);
            } else {
              $this->addMimeTypeExtension($this->params['mimetype_id'], $this->params['extension']);
            }
          }
        }
        break;
      case 'del_ext':
        if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_MIMETYPES_EDIT)) {
          if (isset($this->params['mimetype_id']) && $this->params['mimetype_id'] > 0 &&
              isset($this->params['extension']) && $this->params['extension'] != '' &&
              isset($this->params['confirm']) && $this->params['confirm']) {
            $this->delMimeTypeExtension($this->params['mimetype_id'], $this->params['extension']);
          } else {
            $this->getMimeTypeExtensionDeleteDialog();
          }
        }
        break;
      case 'open_group':
        $this->sessionParams['open_groups'][$this->params['open_group_id']] = 1;
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        break;
      case 'close_group':
        unset($this->sessionParams['open_groups'][$this->params['close_group_id']]);
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        break;
      }
    }
  }

  /**
  * initializes mimetype administration screen layout and loads panels
  */
  function initializeMimeTypesLayout() {
    $this->loadMimeTypeMenubar();
    $this->layout->addLeft($this->getMimeGroupPanel());
    $this->layout->addRight($this->loadMimeTypeExtensionsPanel());
  }

  /**
  * generates mimetype menubar
  */
  function loadMimeTypeMenubar() {
    $this->menubar->addSeperator();
    $administrationUser = $this->papaya()->administrationUser;
    if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_MIMETYPES_EDIT)) {
      $this->menubar->addButton(
        'Add Mimegroup',
        $this->getLink(array('cmd' => 'add_group')),
        'actions-mimetype-group-add',
        'Add a mimegroup',
        isset($this->params['cmd']) && $this->params['cmd'] == 'add_group'
      );
      if (isset($this->params['group_id']) && isset($this->params['group_id']) > 0) {
        $this->menubar->addButton(
          'Delete Mimegroup',
          $this->getLink(array('cmd' => 'del_group', 'group_id' => $this->params['group_id'])),
          'actions-mimetype-group-delete'
        );
      }
      $this->menubar->addButton(
        'Add Mimetype',
        $this->getLink(array('cmd' => 'add_type')),
        'actions-mimetype-add',
        'Add a mimetype',
        isset($this->params['cmd']) && $this->params['cmd'] == 'add_type'
      );
      if (isset($this->params['mimetype_id']) && isset($this->params['mimetype_id']) > 0) {
        $this->menubar->addButton(
          'Delete Mimetype',
          $this->getLink(array('cmd' => 'del_type', 'mimetype_id' => $this->params['mimetype_id'])),
          'actions-mimetype-delete'
        );
        $this->menubar->addButton(
          'Add extension',
          $this->getLink(array('cmd' => 'add_ext', 'mimetype_id' => $this->params['mimetype_id'])),
          'actions-generic-add'
        );
      }
    }
    $this->menubar->addSeperator();
  }

  /**
  * generates panel of mimegroups with mimetypes displayed below if group is open
  *
  * @uses papaya_mediadb::getMimeTypeList()
  * @return string $result mimegroup panel xml
  */
  function getMimeGroupPanel() {
    $result = '';
    $groups = $this->mimeObj->getMimeGroups($this->papaya()->administrationLanguage->id, TRUE);
    if (isset($this->sessionParams['open_groups']) &&
        count($this->sessionParams['open_groups']) > 0) {
      $mimeTypes = $this->mimeObj->getMimeTypes(array_keys($this->sessionParams['open_groups']));
    } else {
      $mimeTypes = array();
    }

    if (isset($groups) && is_array($groups) && count($groups) > 0) {
      $result .= sprintf('<listview title="%s">'.LF, $this->_gt('Mimegroups'));
      $result .= '<items>'.LF;
      foreach ($groups as $groupId => $group) {
        if (isset($group['COUNT']) && $group['COUNT'] > 0) {
          if (isset($this->sessionParams['open_groups'][$groupId])) {
            $nodeLink = $this->getLink(array('cmd' => 'close_group', 'close_group_id' => $groupId));
            $node = sprintf(
              ' node="open" nhref="%s"',
              papaya_strings::escapeHTMLChars($nodeLink)
            );
          } else {
            $nodeLink = $this->getLink(array('cmd' => 'open_group', 'open_group_id' => $groupId));
            $node = sprintf(
              ' node="close" nhref="%s"',
              papaya_strings::escapeHTMLChars($nodeLink)
            );
          }
        } else {
          $node = ' node="empty"';
        }
        if (isset($this->params['group_id']) && $this->params['group_id'] == $groupId) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        if (isset($mimeTypes[$groupId])) {
          $mimeTypeList = $this->getMimeTypeList($mimeTypes[$groupId]);
        } else {
          $mimeTypeList = '';
        }

        $link = $this->getLink(array('cmd' => 'edit_group', 'group_id' => $groupId));
        if ($group['mimegroup_icon'] != '') {
          $icon = $group['mimegroup_icon'];
        } else {
          $icon = $this->defaultGroupIcon;
        }
        $result .= sprintf(
          '<listitem href="%s" %s image="%s" title="%s" %s />'.LF,
          papaya_strings::escapeHTMLChars($link),
          $node,
          papaya_strings::escapeHTMLChars(
            $this->mimeObj->getMimeTypeIcon($icon)
          ),
          papaya_strings::escapeHTMLChars($group['mimegroup_title']),
          $selected
        );
        $result .= $mimeTypeList;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * generates list of mimetypes from a given array
  *
  * @param array $mimeTypes list of mimetypes (id, type, icon)
  * @return string $result listitems xml
  */
  function getMimeTypeList($mimeTypes) {
    $result = '';
    if (isset($mimeTypes) && is_array($mimeTypes) && count($mimeTypes) > 0) {
      foreach ($mimeTypes as $typeId => $type) {
        if ($type['mimetype_icon'] != '') {
          $icon = $type['mimetype_icon'];
        } else {
          $icon = $this->defaultTypeIcon;
        }
        if (isset($this->params['mimetype_id']) && (int)$this->params['mimetype_id'] == $typeId) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem indent="1" href="%s" image="%s" title="%s" %s/>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cmd' => 'edit_type', 'mimetype_id' => $typeId))
          ),
          papaya_strings::escapeHTMLChars(
            $this->mimeObj->getMimeTypeIcon($icon)
          ),
          papaya_strings::escapeHTMLChars(
            papaya_strings::truncate($type['mimetype'], 30)
          ),
          $selected
        );
      }
    }
    return $result;
  }

  /**
  * generates panel containing a list of extensions for the current mimetype
  *
  * contains buttons for deleting and adding extensions
  *
  * @return string $result mimetype extensions xml
  */
  function loadMimeTypeExtensionsPanel() {
    $result = '';
    if (isset($this->params['mimetype_id']) && $this->params['mimetype_id'] > 0) {
      $extensions = $this->mimeObj->getMimeTypesExtensions($this->params['mimetype_id']);
      $administrationUser = $this->papaya()->administrationUser;
      $images = $this->papaya()->images;
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Extensions'))
      );
      $result .= '<items>'.LF;
      if (isset($extensions) && is_array($extensions) && count($extensions) > 0) {
        foreach ($extensions as $extension => $mimetype) {
          $result .= sprintf(
            '<listitem title="%s">'.LF,
            $extension
          );
          $allowEdit = $administrationUser->hasPerm(
            Administration\Permissions::SYSTEM_MIMETYPES_EDIT
          );
          if ($allowEdit) {
            $result .= sprintf(
              '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s" /></a></subitem>'.LF,
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'cmd' => 'del_ext',
                    'mimetype_id' => $this->params['mimetype_id'],
                    'extension' => $extension
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($images['actions-generic-delete']),
              papaya_strings::escapeHTMLChars($this->_gt('Delete extension'))
            );
          } else {
            $result .= '<subitem />'.LF;
          }
          $result .= '</listitem>'.LF;
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  // --- Mimegroup dialogs and db methods ---

  /**
  * generates dialog to add and edit a mimegroup (sets $this->dialog)
  *
  * @param string $cmd command to execute afterwards
  */
  function initializeMimeGroupEditDialog($cmd) {
    $data = array();
    $title = $this->_gt('Add mimegroup');
    $languageId = $this->papaya()->administrationLanguage->id;
    $hidden = array(
      'cmd' => $cmd,
      'confirm' => 1,
      'lng_id' => $languageId,
    );

    $path = $this->getBasePath(TRUE).'pics/icons/16x16/mimetypes/';

    $fields = array(
      'Properties',
      'group_title' => array('Title', 'isNoHTML', TRUE, 'input', 100),
      'Language independent',
      'group_icon' => array('Icon', 'isNoHTML', FALSE, 'filecombo',
      array($path, '/^[a-zA-Z0-9\-]+\.(gif|png)$/i', TRUE), ''),
    );
    if ($cmd == 'edit_group' &&
        isset($this->params['group_id']) &&
        (int)$this->params['group_id'] > 0) {
      $hidden['group_id'] = $this->params['group_id'];
      $title = $this->_gt('Edit mimegroup');
      $groupData = $this->mimeObj->getMimeGroup($this->params['group_id']);
      if (isset($groupData[$languageId])) {
        $data = array(
          'group_title' => $groupData[$languageId]['mimegroup_title'],
          'group_icon' => $groupData[$languageId]['mimegroup_icon'],
        );
      } else {
        if ($group = current($groupData)) {
          $data = array('group_icon' => $group['mimegroup_icon']);
        }
      }
    } elseif ($cmd == 'edit_group') {
      $this->addMsg(MSG_WARNING, $this->_gt('No mimegroup selected.'));
      return;
    }

    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $this->dialog->loadParams();
    $this->dialog->dialogTitle = $title;
    $this->dialog->buttonTitle = 'Save';
    $this->dialog->inputFieldSize = 'large';
  }

  /**
  * adds mimegroup to database
  */
  function addMimeGroup() {
    $data = array(
      'mimegroup_icon' => $this->params['group_icon'],
    );
    $groupId = $this->databaseInsertRecord(
      $this->mimeObj->tableMimeGroups, 'mimegroup_id', $data
    );
    if (FALSE !== $groupId) {
      $this->addMsg(
        MSG_INFO,
        sprintf(
          $this->_gt('Mimegroup "%s" (#%d) added.'),
          $this->params['group_title'],
          $groupId
        )
      );
      return $this->addMimeGroupTrans(
        $groupId, $this->params['lng_id'], $this->params['group_title']
      );
    }
    $this->addMsg(MSG_ERROR, $this->_gt('Mimegroup could not be added.'));
    return FALSE;
  }

  /**
  * adds mimegroup translation to database
  */
  function addMimeGroupTrans($mimeGroupId, $lngId, $title) {
    if ($mimeGroupId > 0) {
      $dataTrans = array(
        'mimegroup_id' => $mimeGroupId,
        'lng_id' => $lngId,
        'mimegroup_title' => $title,
      );
      return $this->databaseInsertRecord(
        $this->mimeObj->tableMimeGroupsTrans, NULL, $dataTrans
      );
    }
    return FALSE;
  }

  /**
  * updates mimegroup in database
  */
  function setMimeGroup() {
    if (isset($this->params['group_id'])) {
      $mimeGroup = $this->mimeObj->getMimeGroup($this->params['group_id']);
      $data = array('mimegroup_icon' => $this->params['group_icon']);
      $condition = array('mimegroup_id' => $this->params['group_id']);
      $this->databaseUpdateRecord($this->mimeObj->tableMimeGroups, $data, $condition);
      if (isset($mimeGroup[$this->params['lng_id']])) {
        $dataTrans = array('mimegroup_title' => $this->params['group_title']);
        $conditionTrans = array(
          'mimegroup_id' => $this->params['group_id'],
          'lng_id' => $this->params['lng_id'],
        );
        return FALSE !== $this->databaseUpdateRecord(
          $this->mimeObj->tableMimeGroupsTrans, $dataTrans, $conditionTrans
        );
      } else {
        return FALSE !== $this->addMimeGroupTrans(
          $this->params['group_id'], $this->params['lng_id'], $this->params['group_title']
        );
      }
    }
    return FALSE;
  }

  /**
  * generates confirmation dialog for deletion of a mimegroup
  */
  function getMimeGroupDeleteDialog() {
    if (isset($this->params['group_id']) && $this->params['group_id'] > 0) {
      // make sure there are no mimetypes in this group
      if ($this->mimeObj->getMimeTypes($this->params['group_id'])) {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('There are mimetypes in this group. Deletion is therefore not possible.')
        );
        return FALSE;
      }
      $languageId = $this->papaya()->administrationLanguage->id;
      $mimeGroup = $this->mimeObj->getMimeGroup($this->params['group_id']);
      if (isset($mimeGroup[$languageId])) {
        $title = $mimeGroup[$languageId]['mimegroup_title'];
      } elseif (count($mimeGroup) > 0) {
        $mimeGroupData = current($mimeGroup);
        $title = $mimeGroupData['mimegroup_title'];
      } else {
        $title = $this->_gt('No title');
      }
      $hidden = array(
        'cmd' => $this->params['cmd'],
        'group_id' => $this->params['group_id'],
        'confirm' => 1,
      );
      $this->dialog = new base_msgdialog(
        $this,
        $this->paramName,
        $hidden,
        sprintf(
          $this->_gt('Do you really want to delete mimegroup "%s"?'),
          $title
        ),
        'warning'
      );
      $this->dialog->buttonTitle = 'Delete';
      return $this->dialog;
    }
    return FALSE;
  }

  /**
  * deletes mimegroup from database
  */
  function delMimeGroup() {
    if (isset($this->params['group_id']) && $this->params['group_id'] > 0) {
      // make sure there are no mimetypes in this group
      if ($this->mimeObj->getMimeTypes($this->params['group_id'])) {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt(
            'There are mimetypes in this group. Deletion is therefore not possible.'
          )
        );
        return FALSE;
      }
      $condition = array('mimegroup_id' => $this->params['group_id']);
      if (FALSE !== $this->databaseDeleteRecord($this->mimeObj->tableMimeGroupsTrans, $condition)) {
        $this->addMsg(
          MSG_INFO,
          sprintf(
            $this->_gt('Translations for mimegroup #%d deleted.'),
            $this->params['group_id']
          )
        );
        if (FALSE !== $this->databaseDeleteRecord($this->mimeObj->tableMimeGroups, $condition)) {
          $this->addMsg(
            MSG_INFO,
            sprintf(
              $this->_gt('Mimegroup #%d deleted.'),
              $this->params['group_id']
            )
          );
          return TRUE;
        } else {
          $this->addMsg(
            MSG_ERROR,
            sprintf(
              $this->_gt('Couldn\'t delete mimegroup #%d.'),
              $this->params['group_id']
            )
          );
        }
      } else {
        $this->addMsg(
          MSG_ERROR,
          sprintf(
            $this->_gt('Couldn\'t delete translations for mimegroups #%d.'),
            $this->params['group_id']
          )
        );
      }
    }
    return FALSE;
  }

  // --- Mimetypes ---

  /**
  * generates dialog to add and edit a mimetype (sets $this->dialog)
  *
  * @param string $cmd command to execute afterwards
  */
  function initializeMimeTypeEditDialog($cmd) {
    $data = array();
    $title = $this->_gt('Add mimetype');
    $languageId = $this->papaya()->administrationLanguage->id;
    $hidden = array(
      'cmd' => $cmd,
      'confirm' => 1,
      'lng_id' => $languageId,
    );

    $path = $this->getBasePath(TRUE).'pics/icons/16x16/mimetypes/';

    $mimeGroups = $this->mimeObj->getMimeGroups($languageId);
    $mimeGroupNames = array();
    foreach ($mimeGroups as $groupId => $group) {
      $mimeGroupNames[$groupId] = $group['mimegroup_title'];
    }

    $fields = array(
      'Properties',
      'mimetype' => array('Mimetype', 'isNoHTML', TRUE, 'input', 100),
      'mimegroup_id' => array('Mimegroup', 'isNum', TRUE, 'combo', $mimeGroupNames),
      'mimetype_icon' => array('Icon', 'isNoHTML', FALSE, 'filecombo',
        array($path, '/^[a-zA-Z0-9\-]+\.(gif|png)$/i', TRUE), ''),
      'mimetype_ext' => array('Default extension', 'isAlphaNum', TRUE, 'input', 10),
      'Delivering options',
      'range_support' => array('Range header support', 'isNum', TRUE, 'combo',
        array(0 => $this->_gt('No'), 1 => $this->_gt('Yes')), '', 1),
      'shaping' => array('Bandwidth shaping', 'isNum', TRUE, 'combo',
        array(0 => $this->_gt('No'), 1 => $this->_gt('Yes')), '', 0),
      'shaping_limit' =>
        array('Bandwidth limit',
          '~^\d+\s*(?:bytes|byte|b|bit|kb|kbit|mb|mbit)?(?:/s)?$~i', TRUE, 'input',
        20, 'You can enter units like 1kB or 1Mbit.', '0'),
      'shaping_offset' =>
        array('Bandwidth offset',
          '~^\d+\s*(?:bytes|byte|b|bit|kb|kbit|mb|mbit)?$~i', TRUE, 'input',
        20,
        'This part of the file will be send immediately.'.
          ' You can enter units like 1kB or 1Mbit.',
        '0'),
    );
    if ($cmd == 'edit_type' &&
        isset($this->params['mimetype_id']) &&
        (int)$this->params['mimetype_id'] > 0) {
      $hidden['mimetype_id'] = $this->params['mimetype_id'];
      $title = $this->_gt('Edit mimetype');
      $mimeTypeData = $this->mimeObj->getMimeType((int)$this->params['mimetype_id']);
      $data = array(
        'mimetype' => $mimeTypeData['mimetype'],
        'mimegroup_id' => $mimeTypeData['mimegroup_id'],
        'mimetype_icon' => $mimeTypeData['mimetype_icon'],
        'mimetype_ext' => $mimeTypeData['mimetype_ext'],
        'range_support' => $mimeTypeData['range_support'],
        'shaping' => $mimeTypeData['shaping'],
        'shaping_limit' => $this->formatByteValue($mimeTypeData['shaping_limit']).'/s',
        'shaping_offset' => $this->formatByteValue($mimeTypeData['shaping_offset']),
      );
    } elseif ($cmd == 'edit_group') {
      $this->addMsg(MSG_WARNING, $this->_gt('No mimegroup selected.'));
      return;
    } elseif (isset($this->params['group_id'])) {
      $data['mimegroup_id'] = $this->params['group_id'];
    }

    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $this->dialog->loadParams();
    $this->dialog->dialogTitle = $title;
    $this->dialog->buttonTitle = 'Save';
    $this->dialog->inputFieldSize = 'large';
  }

  /**
  * get bytes in a more readable format if possible
  *
  * @param int $bytes Byte value
  * @access public
  * @return string
  */
  function formatByteValue($bytes) {
    return \PapayaUtilBytes::toString($bytes);
  }

  /**
  * Decode bytes input string with different units to bytes
  * @param string $input
  * @return integer
  */
  function decodeBytesInput($input) {
    return \PapayaUtilBytes::fromString($input);
  }

  /**
  * adds a mimetype to the database
  */
  function addMimeType() {
    $mimeType = $this->mimeObj->getMimeType($this->params['mimetype']);
    if (is_array($mimeType) && count($mimeType) > 0) {
      $mimeGroup = $this->mimeObj->getMimeGroup($mimeType['mimegroup_id']);
      $languageId = $this->papaya()->administrationLanguage->id;
      if (isset($mimeGroup[$languageId])) {
        $mimeGroupTitle = $mimeGroup[$languageId]['mimegroup_title'];
      } elseif (count($mimeGroup) > 0) {
        $currentMimeGroup = current($mimeGroup);
        $mimeGroupTitle = $currentMimeGroup['mimegroup_title'];
      } else {
        $mimeGroupTitle = $this->_gt('No title');
      }
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('Mimetype "%s" already exists in mimegroup "%s".'),
          $this->params['mimetype'],
          $mimeGroupTitle
        )
      );
      return FALSE;
    }
    $data = array(
      'mimetype' => $this->params['mimetype'],
      'mimetype_icon' => $this->params['mimetype_icon'],
      'mimetype_ext' => $this->params['mimetype_ext'],
      'mimegroup_id' => $this->params['mimegroup_id'],
      'range_support' => $this->params['range_support'],
      'shaping' => $this->params['shaping'],
      'shaping_limit' => $this->decodeBytesInput($this->params['shaping_limit']),
      'shaping_offset' => $this->decodeBytesInput($this->params['shaping_offset']),
    );
    $mimeTypeId = $this->databaseInsertRecord(
      $this->mimeObj->tableMimeTypes, 'mimetype_id', $data
    );
    if (FALSE !== $mimeTypeId) {
      $this->addMsg(
        MSG_INFO,
        sprintf(
          $this->_gt('Mimetype "%s" (#%d) added.'),
          $data['mimetype'],
          $mimeTypeId
        )
      );
      $this->params['mimetype_id'] = $mimeTypeId;
      return TRUE;
    }
    $this->addMsg(MSG_ERROR, $this->_gt('Mimetype could not be added.'));
    return FALSE;
  }

  /**
  * updates an existing mimetype in the database
  */
  function setMimeType() {
    $mimeType = $this->mimeObj->getMimeType($this->params['mimetype']);
    if (is_array($mimeType) && count($mimeType) > 0
        && $mimeType['mimetype_id'] != $this->params['mimetype_id']) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('Mimetype "%s" already exists.'),
          $this->params['mimetype']
        )
      );
      return FALSE;
    }
    $data = array(
      'mimetype' => $this->params['mimetype'],
      'mimetype_icon' => $this->params['mimetype_icon'],
      'mimetype_ext' => $this->params['mimetype_ext'],
      'mimegroup_id' => $this->params['mimegroup_id'],
      'range_support' => $this->params['range_support'],
      'shaping' => $this->params['shaping'],
      'shaping_limit' => $this->decodeBytesInput($this->params['shaping_limit']),
      'shaping_offset' => $this->decodeBytesInput($this->params['shaping_offset']),
    );
    $condition = array('mimetype_id' => $this->params['mimetype_id']);
    $updated = $this->databaseUpdateRecord(
      $this->mimeObj->tableMimeTypes, $data, $condition
    );
    if (FALSE !== $updated) {
      $this->addMsg(
        MSG_INFO,
        sprintf(
          $this->_gt('Mimetype "%s" (#%d) updated.'),
          $data['mimetype'],
          $condition['mimetype_id']
        )
      );
      return TRUE;
    }
    $this->addMsg(MSG_ERROR, $this->_gt('Mimetype could not be updated.'));
    return FALSE;
  }

  /**
  * generates confirmation dialog for mimetype deletion
  */
  function getMimeTypeDeleteDialog() {
    // make sure no file is associated with this mimetype
    $row = $this->countFilesOfMimetype($this->params['mimetype_id']);
    if (is_array($row) && count($row) == 1 && $row[0] > 0 ) {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt(
          'There are files associated with this mimetype. '.
          'Deletion is therefore not possible.'
        )
      );
      return FALSE;
    }
    $mimeType = $this->mimeObj->getMimeType((int)$this->params['mimetype_id']);
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'mimetype_id' => $this->params['mimetype_id'],
      'confirm' => 1,
    );
    $this->dialog = new base_msgdialog(
      $this,
      $this->paramName,
      $hidden,
      sprintf(
        $this->_gt('Do you really want to delete mimetype "%s"?'),
        $mimeType['mimetype']
      ),
      'warning'
    );
    $this->dialog->buttonTitle = 'Delete';
    return $this->dialog;
  }

  /**
  * deletes a mimetype
  */
  function delMimeType() {
    if (isset($this->params['mimetype_id']) && $this->params['mimetype_id'] > 0) {
      // make sure no file is associated with this mimetype
      $row = $this->countFilesOfMimetype($this->params['mimetype_id']);
      if ( is_array($row) && count($row) == 1 && $row[0] > 0 ) {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt(
            'There are files associated with this mimetype. '.
            'Deletion is therefore not possible.'
          )
        );
        return FALSE;
      }
      $mimeType = $this->mimeObj->getMimeType((int)$this->params['mimetype_id']);
      $extensions = $this->mimeObj->getMimeTypesExtensions($this->params['mimetype_id']);
      $condition = array('mimetype_id' => $this->params['mimetype_id']);
      if (is_array($mimeType) && count($mimeType) > 0) {
        if (
          count($extensions) == 0 ||
          FALSE !== $this->databaseDeleteRecord(
            $this->mimeObj->tableMimeTypesExtensions, $condition
          )
        ) {
          if (count($extensions) != 0) {
            $this->addMsg(
              MSG_INFO,
              sprintf(
                $this->_gt('Mimetype extensions for mimetype "%s" (#%d) deleted.'),
                $mimeType['mimetype'],
                $this->params['mimetype_id']
              )
            );
          }
          if (FALSE !== $this->databaseDeleteRecord($this->mimeObj->tableMimeTypes, $condition)) {
            $this->addMsg(
              MSG_INFO,
              sprintf(
                $this->_gt('Mimetype "%s" (#%d) deleted.'),
                $mimeType['mimetype'],
                $this->params['mimetype_id']
              )
            );
            unset($this->params['mimetype_id']);
            return TRUE;
          } else {
            $this->addMsg(
              MSG_ERROR,
              sprintf(
                $this->_gt('Couldn\'t delete mimetype "%s" (#%d).'),
                $mimeType['mimetype'],
                $this->params['mimetype_id']
              )
            );
          }
        } else {
          $this->addMsg(
            MSG_ERROR,
            sprintf(
              $this->_gt('Couldn\'t delete extensions for mimetype "%s" (#%d).'),
              $mimeType['mimetype'],
              $this->params['mimetype_id']
            )
          );
        }
      } else {
        $this->addMsg(
          MSG_WARNING,
          sprintf(
            $this->_gt('Mimetype #%d doesn\'t exist.'),
            $this->params['mimetype_id']
          )
        );
      }
    }
    return FALSE;
  }

  // --- Mimetype Extensions ---

  /**
  * generates dialog to add and edit a mimetype extension (sets $this->dialog)
  */
  function initializeMimeTypeExtensionDialog() {
    $data = array();
    $title = $this->_gt('Add mimetype extension');
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'confirm' => 1,
      'mimetype_id' => $this->params['mimetype_id'],
    );

    $fields = array(
      'extension' => array('Extension', 'isAlphaNum', TRUE, 'input', 100),
    );

    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $this->dialog->loadParams();
    $this->dialog->dialogTitle = $title;
    $this->dialog->buttonTitle = 'Add';
    $this->dialog->inputFieldSize = 'large';
  }

  /**
  * generates confirmation dialog for adding a mimetype extension, that is
  * already registered to a mimetype
  *
  * @param array $mimeTypes list of mimetypes the extension is registered to
  *
  */
  function initializeMimeTypeExtensionConfirmDialog($mimeTypes) {
    // check, whether the extension is already registered for the current mimetype
    if ($mimeTypes['mimetype_id'] == $this->params['mimetype_id']) {
      $this->addMsg(
        MSG_WARNING,
        sprintf(
          $this->_gt('Extension "%s" is already registered to this mimetype.'),
          $this->params['extension']
        )
      );
    } else {
      $this->addMsg(
        MSG_WARNING,
        sprintf(
          $this->_gt('Extension "%s" is already registered to mimetype "%s".'),
          $this->params['extension'],
          $mimeTypes['mimetype']
        )
      );
    }
  }

  /**
  * add a mimetype extension to the database
  */
  function addMimeTypeExtension($mimeTypeId, $extension) {
    $data = array(
      'mimetype_id' => $mimeTypeId,
      'mimetype_extension' => strtolower($extension),
    );
    $inserted = $this->databaseInsertRecord(
      $this->mimeObj->tableMimeTypesExtensions, NULL, $data
    );
    if (FALSE !== $inserted) {
      $this->addMsg(
        MSG_INFO,
        sprintf($this->_gt('Extension "%s" added.'), strtolower($extension))
      );
    }
  }

  /**
  * generates a confirmation dialog for deleting a mimetype extension
  */
  function getMimeTypeExtensionDeleteDialog() {
    $mimeType = $this->mimeObj->getMimeType((int)$this->params['mimetype_id']);
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'mimetype_id' => $this->params['mimetype_id'],
      'extension' => $this->params['extension'],
      'confirm' => 1,
    );
    $this->dialog = new base_msgdialog(
      $this,
      $this->paramName,
      $hidden,
      sprintf(
        $this->_gt('Do you really want to delete extension "%s" for mimetype "%s"?'),
        $this->params['extension'],
        $mimeType['mimetype']
      ),
      'question'
    );
    $this->dialog->buttonTitle = 'Delete';
  }

  /**
  * deletes a mimetype extension from the database
  */
  function delMimeTypeExtension($mimeTypeId, $extension) {
    $condition = array(
      'mimetype_id' => $mimeTypeId,
      'mimetype_extension' => $extension,
    );
    $deleted = $this->databaseDeleteRecord($this->mimeObj->tableMimeTypesExtensions, $condition);
    if (FALSE !== $deleted) {
      $this->addMsg(
        MSG_INFO,
        sprintf(
          $this->_gt('Extension "%s" deleted.'),
          $this->params['extension']
        )
      );
    }
  }

  // ------------------------------ END MIMETYPES ------------------------------
}

