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

use Papaya\Administration\Permissions;

/**
* User class for papaya
* @package Papaya
* @subpackage Authentication
*/
class papaya_user extends base_auth {
  /**
  * Papaya database table auth user
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;
  /**
  * Papaya database table auth groups
  * @var string $tableAuthGroups
  */
  var $tableAuthGroups = PAPAYA_DB_TBL_AUTHGROUPS;
  /**
  * Papaya database table auth link
  * @var string $tableAuthUserLinks
  */
  var $tableAuthUserLinks = PAPAYA_DB_TBL_AUTHLINK;
  /**
  * Papaya database table auth permission
  * @var string $tableAuthPermissions
  */
  var $tableAuthPermissions = PAPAYA_DB_TBL_AUTHPERM;
  /**
  * Papaya database table auth options
  * @var string $tableOptions
  */
  var $tableOptions = PAPAYA_DB_TBL_AUTHOPTIONS;

  /**
  * Input field size
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'medium';

  /**
  * Group fields
  * @var array $fieldsGroup
  */
  var $fieldsGroup = array(
    'grouptitle' => array('Name', 'isalphaNumChar', TRUE, 'input',
      30, '', 'New group')
  );
  /**
  * User fields
  * @var array $fieldsUser
  */
  var $fieldsUser = array(
    'General',
    'givenname' => array ('Givenname', 'isNoHTML', TRUE, 'input', 60, '', ''),
    'surname' => array('Surname', 'isNoHTML', TRUE, 'input', 60, '', ''),
    'email' => array('Email', 'isEMail', TRUE, 'input', 60, '', ''),
    'Login',
    'active' => array('Active', 'isNum', TRUE, 'yesno', '', '', 0, 'center'),
    'username' => array('Login', 'isNoHTML', TRUE, 'input', 30, '', ''),
    'password' => array('Password', \Papaya\Filter\Password::class, FALSE, 'password', 30,
      'The password needs at least 8 chars long and at least 2
       need to be numbers or punctuation chars.', ''),
    'password2' => array('Repetition', \Papaya\Filter\Password::class, FALSE, 'password', 30,
      'Please input your password again.', ''),
    'Permissions',
    'group_id' => array('Group', 'isNum', TRUE, 'function',
      'getGroupListCombo', '', ''),
    'start_node' => array('Base page', 'isNum', TRUE, 'pageid', 5, '', 0),
    'sub_level' => array('Page depth', 'isNum', TRUE, 'input', 5, '', 0),
    'handoff_group_id' => array('Handoff group', 'isNum', TRUE, 'function',
       'getGroupListOrAnyCombo', '', '')
  );

  /**
   * @var base_dialog
   */
  private $optionDialog;

  /**
  * Base Function for handling parameters
  *
  * @access public
  */
  function execute() {
    $this->checkPerms();
    $administrationUser = $this->papaya()->administrationUser;
    if (isset($this->params['uid'])) {
      $this->load($this->params['uid']);
    }
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    switch ($this->params['cmd']) {
    case 'group_open':
      $this->sessionParams['groupsopened'][(int)$this->params['gid']] = TRUE;
      break;
    case 'group_close':
      if (isset($this->sessionParams['groupsopened'][(int)$this->params['gid']])) {
        unset($this->sessionParams['groupsopened'][(int)$this->params['gid']]);
      }
      break;
    case 'group_edit':
      if ($administrationUser->hasPerm(Permissions::USER_GROUP_MANAGE)) {
        if ($this->checkDialogInput($this->fieldsGroup)) {
          if ($this->saveGroupData((int)$this->params['gid'])) {
            $this->addMsg(
              MSG_INFO,
              sprintf($this->_gt('%s modified.'), $this->_gt('Group'))
            );
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
      }
      break;
    case 'group_add':
      if ($administrationUser->hasPerm(Permissions::USER_GROUP_MANAGE)) {
        $this->addGroup();
      }
      break;
    case 'group_del':
      if ($administrationUser->hasPerm(Permissions::USER_GROUP_MANAGE)) {
        $this->delGroup();
      }
      break;
    case 'group_in':
      if ($this->editable()) {
        if (
          $this->addGroupLink(
            (int)$this->params['gid'], $this->params['uid']
          )
        ) {
          $this->addMsg(MSG_INFO, $this->_gt('User added to group.'));
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error! Changes not saved.')
          );
        }
      }
      break;
    case 'group_out':
      if ($this->editable()) {
        if (
          $this->delGroupLink(
            (int)$this->params['gid'], $this->params['uid']
          )
        ) {
          $this->addMsg(MSG_INFO, $this->_gt('User removed from group.'));
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error! Changes not saved.')
          );
        }
      }
      break;
    case 'user_edit':
      if ($this->editable()) {
        $userDialog = $this->getDialogObject(
          'Change user',
          array('cmd' => 'user_edit', 'uid' => $this->params['uid']),
          $this->fieldsUser
        );
        if ($userDialog->checkDialogInput() &&
            $this->checkLoginInput()) {
          if ($this->saveUserData($this->params['uid'])) {
            $this->saveLoginData($this->params['uid']);
            $this->addMsg(
              MSG_INFO,
              sprintf($this->_gt('%s modified.'), $this->_gt('User'))
            );
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
      }
      break;
    case 'user_add':
      $this->addUser();
      break;
    case 'user_del':
      if ($this->editable()) {
        $this->delUser();
      }
      break;
    case 'permgroup_open':
      $this->sessionParams['permgroupsopened'][(int)$this->params['pgid']] = TRUE;
      break;
    case 'permgroup_close':
      if (isset($this->sessionParams['permgroupsopened'][(int)$this->params['pgid']])) {
        unset($this->sessionParams['permgroupsopened'][(int)$this->params['pgid']]);
      }
      break;
    case 'permmod_open':
      $this->sessionParams['permgroupsopened'][$this->params['pmod']] = TRUE;
      break;
    case 'permmod_close':
      if (isset($this->sessionParams['permgroupsopened'][$this->params['pmod']])) {
        unset($this->sessionParams['permgroupsopened'][$this->params['pmod']]);
      }
      break;
    case 'perm_on':
      if ($administrationUser->isAdmin()) {
        $this->setPermStatus((int)$this->params['pid'], TRUE);
      }
      break;
    case 'perm_off':
      if ($administrationUser->isAdmin()) {
        $this->setPermStatus((int)$this->params['pid'], FALSE);
      }
      break;
    case 'modperm_on':
      if ($administrationUser->isAdmin()) {
        $this->setPermStatus(
          (int)$this->params['pid'], TRUE, $this->params['mod_id']
        );
      }
      break;
    case 'modperm_off':
      if ($administrationUser->isAdmin()) {
        $this->setPermStatus(
          (int)$this->params['pid'], FALSE, $this->params['mod_id']
        );
      }
      break;
    case 'perm_in':
      $this->setPerm(TRUE);
      break;
    case 'perm_out':
      $this->setPerm(FALSE);
      break;
    case 'modperm_in':
      $this->setModPerm(TRUE);
      break;
    case 'modperm_out':
      $this->setModPerm(FALSE);
      break;
    case 'opt_chg':
      if (isset($this->params['uid'])) {
        $this->load($this->params['uid']);
        if (isset($this->params['opt']) &&
            in_array($this->params['opt'], $this->userOptions)) {
          $this->loadOptions();
          $this->initOptionDialog($this->params['opt']);
          if ($this->optionDialog->checkDialogInput()) {
            $saved = $this->setUserOption(
              $this->params['opt'], $this->params[$this->params['opt']]
            );
            if ($saved) {
              $this->addMsg(
                MSG_INFO,
                sprintf($this->_gt('%s modified.'), $this->_gt('option'))
              );
            }
          }
          unset($this->optionDialog);
        }
      }
      break;
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    $this->loadPermTree();
    unset($this->groups);
    $this->loadGroups();
    $this->loadUsers();
    $this->loadGroupTree();
    if (isset($this->params['uid'])) {
      $this->load($this->params['uid']);
    }
  }

  /**
  * Load all user
  *
  * @access public
  */
  function loadUsers() {
    unset($this->users);
    $sql = "SELECT user_id, username, surname, givenname, group_id, active
              FROM %s
             ORDER BY surname, givenname, username, user_id";
    $params = array($this->tableAuthUser);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['fullname'] = $row['givenname'].' '.$row['surname'];
        $this->users[$row["user_id"]] = $row;
      }
    }
  }

  /**
  * Load group tree
  *
  * @access public
  */
  function loadGroupTree() {
    unset($this->groupTree);
    $sql = "SELECT user_id, group_id
              FROM %s";
    $params = array($this->tableAuthUserLinks);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->groupTree[(int)$row["group_id"]][] = $row['user_id'];
      }
    }
    if (isset($this->users) && is_array($this->users)) {
      foreach ($this->users as $aUserId => $user) {
        $groupId = (int)$user["group_id"];
        if ($groupId != 0) {
          if (isset($this->groupTree[$groupId])&&
              is_array($this->groupTree[$groupId])) {
            if (!in_array($aUserId, $this->groupTree[$groupId])) {
              $this->groupTree[$groupId][] = $aUserId;
            }
          } else {
            $this->groupTree[$groupId] = array($aUserId);
          }
        }
      }
    }
  }

  /**
  * Load permission tree
  *
  * @access public
  */
  function loadPermTree() {
    $this->perms = array();
    $iterator = new RecursiveIteratorIterator(
      $this->permissions()->groups(),
      RecursiveIteratorIterator::SELF_FIRST
    );
    $groupId = 0;
    foreach ($iterator as $id => $value) {
      if ($iterator->getDepth() == 0) {
        $groupId = $id;
        $this->perms[$groupId] = array(
          'permgroup_title' => $value,
          'perms' => array()
        );
      } elseif ($groupId > 0 && $iterator->getDepth() == 1) {
        foreach ($value as $permissionId => $permissionTitle) {
          $this->perms[$groupId]['perms'][$permissionId] = array(
            'perm_id' => $permissionId,
            'perm_active' => $this->permissions()->isActive($permissionId),
            'perm_title' => $permissionTitle,
            'permgroup_id' => $groupId
          );
        }
      }
    }
  }

  /**
  * Save group data
  *
  * @param integer $groupId
  * @access public
  * @return boolean
  */
  function saveGroupData($groupId) {
    $data = array('grouptitle' => $this->params['grouptitle']);
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableAuthGroups, $data, "group_id", (int)$groupId
    );
  }

  /**
  * Save user data
  *
  * @access public
  * @return boolean
  */
  function saveUserData() {
    if ($this->params['group_id'] != $this->user['group_id']) {
      $this->addGroupLink($this->user['group_id'], $this->userId);
    }
    $data = array(
      "group_id" => (int)$this->params['group_id'],
      "surname" => $this->params['surname'],
      "givenname" => $this->params['givenname'],
      "email" => $this->params['email'],
      "start_node" => (int)$this->params['start_node'],
      "sub_level" => (int)$this->params['sub_level'],
      "active" => (int)$this->params['active'],
      "handoff_group_id" => (int)$this->params['handoff_group_id']
    );
    // Check email in user AND surfer table
    $emailInUse = FALSE;
    $email = $data['email'];
    if ($this->user['email'] != $email) {
      $sql = "SELECT COUNT(*) FROM %s WHERE email = '%s'";
      $res = $this->databaseQueryFmt(
        $sql,
        array($this->tableAuthUser, $email)
      );
      if ($res) {
        $emailInUse = ($res->fetchField() > 0);
        if (!$emailInUse) {
          $sql = "SELECT COUNT(*) FROM %s WHERE surfer_email= '%s'";
          $res = $this->databaseQueryFmt(
            $sql,
            array($this->tableCommunitySurfers, $email)
          );
          if ($res) {
            $emailInUse = ($res->fetchField() > 0);
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt(
                'Error in SQL query: Failed to check whether the email address is already in use.'
              )
            );
            return FALSE;
          }
        }
      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt(
            'Error in SQL query: Failed to check whether the email address is already in use.'
          )
        );
        return FALSE;
      }
    }
    if ($emailInUse) {
      $this->addMsg(MSG_ERROR, $this->_gt('Email address already in use.'));
      return FALSE;
    }
    $updated = $this->databaseUpdateRecord(
      $this->tableAuthUser, $data, 'user_id', $this->userId
    );
    if (FALSE !== $updated) {
      $this->load($this->userId);
      $this->synchronizeSurfer($this->userId);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
  * Add group link
  *
  * @param $groupId
  * @param $aUserId
  * @access public
  * @return boolean
  */
  function addGroupLink($groupId, $aUserId) {
    if (preg_match('/^[a-fA-F\d]{32}$/', $aUserId)) {
      $sql = "SELECT count(*)
                FROM %s
               WHERE user_id = '%s' AND group_id = %d";
      $params = array($this->tableAuthUserLinks, $aUserId, $groupId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        list($count) = $res->fetchRow();
        if ($count == 0) {
          $data = array(
              'user_id' => $aUserId,
              'group_id' => (int)$groupId
          );
          return FALSE !== $this->databaseInsertRecord(
            $this->tableAuthUserLinks, NULL, $data
          );
        } else {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Delete Group Link
  *
  * @param integer $groupId
  * @param integer $aUserId
  * @access public
  * @return boolean
  */
  function delGroupLink($groupId, $aUserId) {
    if (preg_match('/^[a-fA-F\d]{32}$/', $aUserId)) {
      $filter = array(
        'user_id' => $aUserId,
        'group_id' => (int)$groupId
      );
      return FALSE !== $this->databaseDeleteRecord(
        $this->tableAuthUserLinks, $filter
      );
    }
    return FALSE;
  }

  /**
  * set permissions
  *
  * @param string $addPerm
  * @access public
  */
  function setPerm($addPerm) {
    $administrationUser = $this->papaya()->administrationUser;
    if (isset($this->params['pid']) &&
        $this->params['pid'] > 0 &&
        ($administrationUser->hasPerm($this->params['pid']) || $administrationUser->isAdmin())) {
      if (isset($this->params['uid']) &&
          preg_match('/^[a-fA-F\d]{32}$/', $this->params['uid'])) {
        $this->setPermUser(
          $addPerm, (int)$this->params['pid'], $this->params['uid']
        );
      } elseif (isset($this->params['gid']) && ($this->params['gid'] > 0)) {
        $this->setPermGroup(
          $addPerm, (int)$this->params['pid'], (int)$this->params['gid']
        );
      }
    } else {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('You can not change this permission.')
      );
    }
  }

  /**
  * Set permission user
  *
  * @param string $addPerm
  * @param integer $permId
  * @access public
  * @return boolean
  */
  function setPermUser($addPerm, $permId) {
    if ($this->editable()) {
      $sql = "SELECT userperm
                FROM %s
               WHERE user_id = '%s'";
      $params = array($this->tableAuthUser, $this->userId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          $permString = $this->changePerms($row[0], $permId, $addPerm);
          if ($permString != $row[0]) {
            $data = array('userperm' => $permString);
            $updated = $this->databaseUpdateRecord(
              $this->tableAuthUser, $data, "user_id", $this->userId
            );
            if (FALSE !== $updated) {
              $this->addMsg(
                MSG_INFO,
                sprintf($this->_gt('%s modified.'), $this->_gt('User permissions'))
              );
              return TRUE;
            } else {
              $this->addMsg(
                MSG_ERROR,
                $this->_gt('Database error! Changes not saved.')
              );
            }
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Set permission group
  *
  * @param string $addPerm
  * @param integer $permId
  * @param integer $groupId
  * @access public
  * @return boolean
  */
  function setPermGroup($addPerm, $permId,  $groupId) {
    $sql = "SELECT groupperm
              FROM %s
             WHERE group_id = %d";
    $params = array($this->tableAuthGroups, $groupId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        $permString = $this->changePerms($row[0], $permId, $addPerm);
        if ($permString != $row[0]) {
          $data = array(
            'groupperm' => $permString
          );
          $updated = $this->databaseUpdateRecord(
            $this->tableAuthGroups, $data, "group_id", (int)$groupId
          );
          if (FALSE !== $updated) {
            $this->addMsg(
              MSG_INFO,
              sprintf($this->_gt('%s modified.'), $this->_gt('Group permissions'))
            );
            return TRUE;
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Set Module permission
  *
  * @param string $addPerm
  * @access public
  */
  function setModPerm($addPerm) {
    $administrationUser = $this->papaya()->administrationUser;
    if (isset($this->params['mod_id']) &&
        isset($this->params['pid']) &&
        $this->params['pid'] > 0 &&
        (
          $administrationUser->hasPerm($this->params['pid'], $this->params['mod_id']) ||
          $administrationUser->isAdmin()
        )) {
      if (isset($this->params['uid']) &&
          preg_match('/^[a-fA-F\d]{32}$/', $this->params['uid'])) {
        $this->changeModPerm(
          $addPerm, $this->params['mod_id'], $this->params['pid'], $this->params['uid'], 0
        );
      } elseif (isset($this->params['gid']) && ($this->params['gid'] > 0)) {
        $this->changeModPerm(
          $addPerm, $this->params['mod_id'], $this->params['pid'], 0, $this->params['gid']
        );
      }
    } else {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('You can not change this permission.')
      );
    }
  }

  /**
  * Change module path
  *
  * @param string $addPerm
  * @param integer $moduleId
  * @param integer $permId
  * @param integer $groupId
  * @param integer $userId
  * @access public
  * @return boolean
  */
  function changeModPerm($addPerm, $moduleId, $permId, $userId = 0, $groupId = 0) {
    $sql = "SELECT module_perms
              FROM %s
             WHERE module_id = '%s'
               AND user_id = '%s'
               AND group_id = %d";
    $params = array(
      $this->tableModulePermissionsLinks, $moduleId, $userId, $groupId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        $perms = array_flip($this->decodePermStr($row[0]));
        if ($addPerm) {
          $perms[$permId] = TRUE;
        } elseif (isset($perms[$permId])) {
          unset($perms[$permId]);
        }
        $data = array(
          'module_perms' => implode(';', array_keys($perms))
        );
        $filter = array(
          'module_id' => $moduleId,
          'user_id' => $userId,
          'group_id' => (int)$groupId
        );
        return FALSE !== $this->databaseUpdateRecord(
          $this->tableModulePermissionsLinks, $data, $filter
        );
      } else {
        $data = array(
          'module_id' => $moduleId,
          'user_id' => $userId,
          'group_id' => (int)$groupId,
          'module_perms' => (int)$permId
        );
        return FALSE !== $this->databaseInsertRecord(
          $this->tableModulePermissionsLinks, NULL, $data
        );
      }
    }
    return FALSE;
  }

  /**
  * Change permission
  *
  * @param string $permString
  * @param integer $permId
  * @param string $addPerm
  * @access public
  * @return string imploded $perms
  */
  function changePerms($permString, $permId, $addPerm) {
    $perms = $this->decodePermStr($permString);
    if ($addPerm) {
      if (!isset($perms)) {
        $perms = array($permId);
      } elseif (!is_array($perms)) {
        $perms = array($permId);
      } elseif (!in_array($permId, $perms)) {
        $perms[] = $permId;
      }
    } else {
      if (isset($perms) && is_array($perms)) {
        $perms = array_flip($perms);
        unset($perms[$permId]);
        $perms = array_flip($perms);
      }
    }
    if (is_array($perms)) {
      return implode(';', $perms);
    }
    return '';
  }

  /**
  * Set permission status
  *
  * @param $id
  * @param $active
  * @param mixed $moduleId optional, default value NULL
  * @access public
  * @return boolean
  */
  function setPermStatus($id, $active, $moduleId = NULL) {
    if (isset($moduleId)) {
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE module_id = '%s' AND modperm_id = '%d'";
      $params = array($this->tableModulePermissions, $moduleId, $id);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        $count = $res->fetchField();
        $data = array(
          'modperm_active' => (int)(bool)$active
        );
        $filter = array(
          'module_id' => $moduleId,
          'modperm_id' => (int)$id
        );
        if ($count > 0) {
          return FALSE !== $this->databaseUpdateRecord(
            $this->tableModulePermissions, $data, $filter
          );
        } else {
          return FALSE !== $this->databaseInsertRecord(
            $this->tableModulePermissions, NULL, array_merge($data, $filter)
          );
        }
      }
      return FALSE;
    } else {
      $data = array('perm_active' => (int)(bool)$active);
      return FALSE !== $this->databaseUpdateRecord(
        $this->tableAuthPermissions, $data, "perm_id", (int)$id
      );
    }
  }

  /**
  * Add user
  *
  * @access public
  */
  function addUser() {
    $sql = "SELECT user_id
              FROM %s
             WHERE username = '%s'";
    $params = array($this->tableAuthUser, $this->defaultUsername);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        $this->params['uid'] = $row[0];
        $this->addMsg(
          MSG_WARNING,
          $this->_gt('Already created a new user. Please configure this user first.')
        );
      } else {
        srand((double)microtime() * 1000000);
        $newUserId = md5(uniqid(rand()));
        $defPass = md5(uniqid(rand()));
        $data = array(
          'user_id' => $newUserId,
          'username' => $this->defaultUsername,
          'givenname' => '',
          'surname' => $this->_gt('New user'),
          'active' => 0,
          'start_node' => 0,
          'sub_level' => 0,
          'user_password' => $defPass,
          'userperm' => '',
          'email' => ''
        );
        if (FALSE !==
              $this->databaseInsertRecord($this->tableAuthUser, NULL, $data)) {
          $this->addMsg(MSG_INFO, $this->_gt('New user created.'));
          $this->params['uid'] = $newUserId;
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Couldn`t create new user').' '.$this->_gt('Database error!')
          );
        }
      }
    }
  }

  /**
  * Add group
  *
  * @access public
  */
  function addGroup() {
    $sql = "SELECT MAX(group_id)
              FROM %s";
    $params = array($this->tableAuthGroups);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        $groupId = ($row[0] > 0) ? $row[0] + 1 : 1;
        $data['grouptitle'] = 'New group';
        $data['group_id'] = $groupId;
        $data['groupperm'] = '';
        if (FALSE !==
              $this->databaseInsertRecord($this->tableAuthGroups, NULL, $data)) {
          $this->addMsg(MSG_INFO, $this->_gt('New group created.'));
          $this->params['gid'] = $groupId;
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Couldn`t create new group').' '.$this->_gt('Database error!')
          );
        }
      }
    }
  }

  /**
  * Delete user
  *
  * @access public
  */
  function delUser() {
    if (preg_match('/^[a-fA-F\d]{32}$/', $this->params['uid']) &&
        (isset($this->params['confirm_delete']))) {
      $deleted = $this->databaseDeleteRecord(
        $this->tableAuthUserLinks, 'user_id', $this->params['uid']
      );
      if (FALSE !== $deleted) {
        $deleted = $this->databaseDeleteRecord(
          $this->tableAuthUser, 'user_id', $this->params['uid']
        );
        if (FALSE !== $deleted) {
          $this->addMsg(MSG_INFO, $this->_gt('User deleted.'));
          $this->databaseDeleteRecord(
            $this->tableCommunitySurfers, 'auth_user_id', $this->params['uid']
          );
          unset($this->params['uid']);
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Couldn`t delete user').' '.
            $this->_gt('Group links deleted only.').' '.
            $this->_gt('Database error!')
          );
        }
      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('Couldn`t delete user').' '.$this->_gt('Database error!')
        );
      }
    }
  }

  /**
  * Delete group
  *
  * @access public
  */
  function delGroup() {
    if (isset($this->params['gid']) && ($this->params['gid'] > 0) &&
        (isset($this->params['confirm_delete']))) {
      $deleted = $this->databaseDeleteRecord(
        $this->tableAuthUserLinks, 'group_id', (int)$this->params['gid']
      );
      if (FALSE !== $deleted) {
        $deleted = $this->databaseDeleteRecord(
          $this->tableAuthGroups, 'group_id', (int)$this->params['gid']
        );
        if (FALSE !== $deleted) {
          $this->addMsg(MSG_INFO, $this->_gt('Group deleted.'));
          unset($this->params['gid']);
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Couldn`t delete group').' '.
            $this->_gt('Group links deleted only.').' '.
            $this->_gt('Database error!')
          );
        }
      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('Couldn`t delete group').' '.$this->_gt('Database error!')
        );
      }
    }
  }

  /**
  * Group opened
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function groupOpened($id) {
    if (($id != 0) && isset($this->sessionParams['groupsopened']) &&
        is_array($this->sessionParams['groupsopened']) &&
        isset($this->sessionParams['groupsopened'][(int)$id]) &&
        ($this->sessionParams['groupsopened'][(int)$id])) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
  * Permission group opened
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function permGroupOpened($id) {
    if (($id != '') && isset($this->sessionParams['permgroupsopened']) &&
        is_array($this->sessionParams['permgroupsopened']) &&
        isset($this->sessionParams['permgroupsopened'][$id]) &&
        ($this->sessionParams['permgroupsopened'][$id])) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
  * Get XML function
  *
  * @access public
  */
  function getXML() {
    if (isset($this->layout) && is_object($this->layout)) {
      $this->getGroupTreeXML();
      if (isset($this->params['uid'])) {
        if ($this->editable()) {
          if (isset($this->params['cmd']) &&
              $this->params['cmd'] == 'user_del') {
            $this->getDelUserForm();
          } elseif (isset($this->params['opt']) &&
                    isset($this->options[$this->params['opt']])) {
            $this->getUserOptionsForm();
          } else {
            $this->getUserFormXML();
            $this->getUserGroupListXML();
          }
          $this->getPermTreeXML('user');
          $this->getUserOptionsListXML();
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('You can not change this user.')
          );
        }
      } elseif (isset($this->params['gid']) &&
                ((int)$this->params['gid'] != 0) &&
                isset($this->params['gid'])) {
        if ($this->papaya()->administrationUser->isAdmin()) {
          if (isset($this->params['cmd']) &&
              $this->params['cmd'] == 'group_del') {
            $this->getDelGroupForm((int)$this->params['gid']);
          }
          $this->getGroupFormXML((int)$this->params['gid']);
          $this->getPermTreeXML('group');
        }
      } else {
        $this->getPermTreeXML();
      }
      $this->getButtonsXML();
    }
  }

  /**
  * Get group tree XML
  *
  * @access public
  * @return string $result XML
  */
  function getGroupTreeXML() {
    $result = '';
    if (isset($this->groups) && is_array($this->groups)) {
      $result .= sprintf(
        '<listview title="%s/%s" hint="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Groups')),
        papaya_strings::escapeHTMLChars($this->_gt('Users')),
        papaya_strings::escapeHTMLChars($this->_gt('A user can be in several groups.'))
      );
      $result .= '<items>';
      $group = array('grouptitle' => $this->_gt('All users'));
      $result .= $this->getGroupTreeGroupXML(
        -2, $group, (isset($this->users) && is_array($this->users))
      );
      if ($this->groupOpened(-2)) {
        if (isset($this->users) && is_array($this->users)) {
          foreach ($this->users as $auserId => $user) {
            $result .= $this->getGroupTreeUserXML($auserId, $user);
          }
        }
      }
      $administrationUser = $this->papaya()->administrationUser;
      foreach ($this->groups as $groupId => $group) {
        if ($administrationUser->isAdmin() ||
            $administrationUser->inGroup((int)$groupId)) {
          if (isset($this->groupTree[$groupId]) &&
              is_array($this->groupTree[$groupId])) {
            $result .= $this->getGroupTreeGroupXML($groupId, $group, TRUE);
            if ($this->groupOpened($groupId)) {
              foreach ($this->groupTree[$groupId] as $aUserId) {
                $result .= $this->getGroupTreeUserXML(
                  $aUserId, $this->users[$aUserId]
                );
              }
            }
          } else {
            $result .= $this->getGroupTreeGroupXML($groupId, $group, FALSE);
          }
        }
      }

      $result .= '</items>';
      $result .= '</listview>';
    }
    $this->layout->addLeft($result);
  }

  /**
  * Group tree - XML for user item
  *
  * @param integer $aUserId
  * @param array $userData
  * @access public
  * @return string $result XML
  */
  function getGroupTreeUserXML($aUserId, $userData) {
    $result = '';
    if (isset($userData) && is_array($userData)) {
      $selected = (isset($this->params['uid']) && $this->params['uid'] == $aUserId)
        ? ' selected="selected"' : '';
      $userIcon = ($userData['active'] > 0)
        ? $this->papaya()->images['items-user']
        : $this->papaya()->images['status-user-disabled'];
      $result = sprintf(
        '<listitem title="%s" href="%s" image="%s" indent="2"%s/>',
        papaya_strings::escapeHTMLChars($userData['fullname']),
        papaya_strings::escapeHTMLChars($this->getLink(array('uid' => $aUserId))),
        papaya_strings::escapeHTMLChars($userIcon),
        $selected
      );
    }
    return $result;
  }

  /**
  * Group tree - XML for group item
  *
  * @param integer $groupId
  * @param array $group
  * @param boolean $hasUsers
  * @access public
  * @return string $result XML
  */
  function getGroupTreeGroupXML($groupId, $group, $hasUsers) {
    $result = '';
    if (isset($group) && is_array($group)) {
      $selected = (isset($this->params['gid']) && $this->params['gid'] == $groupId)
        ? ' selected="selected"' : '';
      if ($hasUsers) {
        $indent = '';
        if ($this->groupOpened($groupId)) {
          $nodeParams = array(
            'cmd' => 'group_close',
            'gid' => $groupId
          );
          $node = sprintf(
            ' node="open" nhref="%s"',
            papaya_strings::escapeHTMLChars($this->getLink($nodeParams))
          );
        } else {
          $nodeParams = array(
            'cmd' => 'group_open',
            'gid' => $groupId
          );
          $node = sprintf(
            ' node="close" nhref="%s"',
            papaya_strings::escapeHTMLChars($this->getLink($nodeParams))
          );
        }
      } else {
        $node = '';
        $indent = ' indent="1"';
      }
      $result .= sprintf(
        '<listitem title="%s" href="%s" image="%s"%s%s%s/>',
        papaya_strings::escapeHTMLChars($group['grouptitle']),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('gid' => $groupId))
        ),
        papaya_strings::escapeHTMLChars($this->papaya()->images['items-user-group']),
        $node,
        $indent,
        $selected
      );
    }
    return $result;
  }

  /**
  * Get permission tree XML
  *
  * @param string $mode optional, default value 'perm'
  * @access public
  * @return string $result XML
  */
  function getPermTreeXML($mode = 'perm') {
    if (isset($this->perms) && is_array($this->perms)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Permissions'))
      );
      switch ($mode) {
      case 'group':
      case 'user':
        $result .= sprintf(
          '<cols><col /><col align="center">%s</col><col align="center">%s</col></cols>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Active')),
          papaya_strings::escapeHTMLChars($this->_gt('Assigned'))
        );
        break;
      default:
        $result .= sprintf(
          '<cols><col /><col align="center">%s</col></cols>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Active'))
        );
      }
      $result .= '<items>'.LF;
      foreach ($this->perms as $permGroupId => $permGroup) {
        if ($permGroupId == 7) {
          $result .= $this->getPermTreeGroupXML(
            $permGroupId, $permGroup, TRUE, $mode
          );
          if ($this->permGroupOpened($permGroupId)) {
            $result .= $this->getModulePermsXML($mode);
          }
        } elseif (isset($permGroup['perms']) && is_array($permGroup['perms'])) {
          $result .= $this->getPermTreeGroupXML(
            $permGroupId, $permGroup, TRUE, $mode
          );
          if ($this->permGroupOpened($permGroupId)) {
            uasort($permGroup['perms'], array($this, 'comparePermissionsByName'));
            foreach ($permGroup['perms'] as $permId => $perm) {
              switch ($mode) {
              case 'group':
                $result .= $this->getPermGroupTreePermXML($permId, $perm);
                break;
              case 'user':
                $result .= $this->getPermUserTreePermXML($permId, $perm);
                break;
              default:
                $result .= $this->getPermAdminTreePermXML($permId, $perm);
              }
            }
          }
        } else {
          $result .= $this->getPermTreeGroupXML(
            $permGroupId, $permGroup, FALSE, $mode
          );
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addRight($result);
    }
  }

  /**
  * Compare two permissions by title
  * @param array $permOne
  * @param array $permTwo
  * @return integer
  */
  function comparePermissionsByName($permOne, $permTwo) {
    return strnatcasecmp($permOne['perm_title'], $permTwo['perm_title']);
  }

  /**
  * Get permission tree group XML
  *
  * @param integer $permGroupId
  * @param array $permGroup
  * @param boolean $hasChildren
  * @param string $mode
  * @access public
  * @return string $result XML
  */
  function getPermTreeGroupXML($permGroupId, $permGroup, $hasChildren, $mode) {
    $result = '';
    if (isset($permGroup) && is_array($permGroup)) {
      switch ($mode) {
      case 'user':
        $addParam = array(
          'uid' => $this->params['uid']
        );
        $cols = 3;
        break;
      case 'group':
        $addParam = array(
          'gid' => $this->params['gid']
        );
        $cols = 3;
        break;
      default:
        $addParam = array();
        $cols = 2;
      }
      if ($hasChildren) {
        $indent = '';
        if ($this->permGroupOpened($permGroupId)) {
          $nodeParams = array(
            'cmd' => 'permgroup_close',
            'pgid' => $permGroupId
          );
          $nodeStatus = 'open';
          $iconIdx = 'status-folder-open';
        } else {
          $nodeParams = array(
            'cmd' => 'permgroup_open',
            'pgid' => $permGroupId
          );
          $nodeStatus = 'close';
          $iconIdx = 'items-folder';
        }
      } else {
        $nodeStatus = 'empty';
        $nodeParams = '';
        $indent = ' indent="1"';
        $iconIdx = 'items-folder';
      }
      $result .= sprintf(
        '<listitem title="%s" image="%s" node="%s" nhref="%s"%s>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->_gt($permGroup['permgroup_title'])
        ),
        papaya_strings::escapeHTMLChars($this->papaya()->images[$iconIdx]),
        papaya_strings::escapeHTMLChars($nodeStatus),
        empty($nodeParams)
          ? ''
          : papaya_strings::escapeHTMLChars($this->getLink(array_merge($nodeParams, $addParam))),
        $indent
      );
      $result .= str_repeat('<subitem/>', $cols - 1);
      $result .= '</listitem>'.LF;
    }
    return $result;
  }

  /**
  * Get permissions admin tree Permissions XML
  *
  * @param integer $permId
  * @param array $perm
  * @param mixed $module optional, default value NULL
  * @access public
  * @return string $result XML
  */
  function getPermAdminTreePermXML($permId, $perm, $module = NULL) {
    $result = '';
    if (isset($perm) && is_array($perm) && ($perm['perm_title'] != '')) {
      $administrationUser = $this->papaya()->administrationUser;
      if ($administrationUser->isAdmin() || $perm['perm_active']) {
        $indent = (isset($module)) ? 3 : 2;
        $result .= sprintf(
          '<listitem title="%s" image="%s" indent="%d">',
          papaya_strings::escapeHTMLChars($this->_gt($perm['perm_title'])),
          papaya_strings::escapeHTMLChars($this->papaya()->images['items-permission']),
          (int)$indent
        );
        $result .= '<subitem align="center">';
        if ($administrationUser->isAdmin()) {
          if ($perm['perm_active'] && isset($module)) {
            $idx = 'status-node-checked';
            $href = $this->getLink(
              array(
                'cmd' => 'modperm_off',
                'pid' => $permId,
                'mod_id' => $module
              )
            );
          } elseif (isset($module)) {
            $idx = 'status-node-empty';
            $href = $this->getLink(
              array(
                'cmd' => 'modperm_on',
                'pid' => $permId,
                'mod_id' => $module
              )
            );
          } elseif ($perm['perm_active']) {
            $idx = 'status-node-checked';
            $href = $this->getLink(
              array(
                'cmd' => 'perm_off',
                'pid' => $permId
              )
            );
          } else {
            $idx = 'status-node-empty';
            $href = $this->getLink(
              array(
                'cmd' => 'perm_on',
                'pid' => $permId
              )
            );
          }
          $result .= sprintf(
            '<a href="%s"><glyph src="%s"/></a>',
            papaya_strings::escapeHTMLChars($href),
            papaya_strings::escapeHTMLChars($this->papaya()->images[$idx])
          );
        } else {
          $idx = ($perm['perm_active'])
            ? 'status-node-checked-disabled' : 'status-node-empty-disabled';
          $result .= sprintf(
            '<glyph src="%s"/>',
            papaya_strings::escapeHTMLChars($this->papaya()->images[$idx])
          );
        }
        $result .= '</subitem>';
        $result .= '</listitem>'.LF;
      }
    }
    return $result;
  }

  /**
  * Get permission group tree permission XML
  *
  * @param integer $permId
  * @param array $perm
  * @param mixed $module optional, default value NULL
  * @access public
  * @return string $result XML
  */
  function getPermGroupTreePermXML($permId, $perm, $module = NULL) {
    $result = '';
    if (isset($perm) && is_array($perm) && ($perm['perm_title'] != '')) {
      $administrationUser = $this->papaya()->administrationUser;
      $images = $this->papaya()->images;
      if ($administrationUser->isAdmin() ||
         ($perm['perm_active'] && ($administrationUser->hasPerm($permId, $module)))) {
        $indent = (isset($module)) ? 3 : 2;
        $result .= sprintf(
          '<listitem title="%s" image="%s" indent="%d">',
          papaya_strings::escapeHTMLChars($this->_gt($perm['perm_title'])),
          papaya_strings::escapeHTMLChars($images['items-permission']),
          (int)$indent
        );
        $result .= '<subitem align="center">';
        $idx = ($perm['perm_active'])
          ? 'status-node-checked-disabled' : 'status-node-empty-disabled';
        $result .= sprintf(
          '<glyph src="%s"/>',
          papaya_strings::escapeHTMLChars($images[$idx])
        );
        $result .= '</subitem>';
        $result .= '<subitem align="center">';
        $groupId = (int)$this->params['gid'];
        $perms = isset($this->groups[$groupId]['perms']) ?
          $this->groups[$groupId]['perms'] : NULL;
        if ($groupId == -1) {
          $idx = 'status-node-checked-disabled';
          $result .= sprintf(
            '<glyph src="%s"/>',
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        } elseif ($groupId < 0) {
          $idx = 'status-node-empty-disabled';
          $result .= sprintf(
            '<glyph src="%s"/>',
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        } else {
          if (isset($module)) {
            if (isset($this->modulePermLinks[$module]['GROUP']) &&
                is_array($this->modulePermLinks[$module]['GROUP']) &&
                in_array($permId, $this->modulePermLinks[$module]['GROUP'])) {
              $idx = 'status-node-checked';
              $href = $this->getLink(
                array(
                  'cmd' => 'modperm_out',
                  'pid' => $permId,
                  'gid' => $groupId,
                  'mod_id' => $module
                )
              );
            } else {
              $idx = 'status-node-empty';
              $href = $this->getLink(
                array(
                  'cmd' => 'modperm_in',
                  'pid' => $permId,
                  'gid' => $groupId,
                  'mod_id' => $module
                )
              );
            }
          } else {
            if (isset($perms) && is_array($perms) && in_array($permId, $perms)) {
              $idx = 'status-node-checked';
              $href = $this->getLink(
                array(
                  'cmd' => 'perm_out',
                  'pid' => $permId,
                  'gid' => $groupId
                )
              );
            } else {
              $idx = 'status-node-empty';
              $href = $this->getLink(
                array(
                  'cmd' => 'perm_in',
                  'pid' => $permId,
                  'gid' => $groupId
                )
              );
            }
          }
          $result .= sprintf(
            '<a href="%s"><glyph src="%s"/></a>',
            papaya_strings::escapeHTMLChars($href),
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        }
        $result .= '</subitem>';
        $result .= '</listitem>'.LF;
      }
    }
    return $result;
  }

  /**
  * Get permission user tree permission XML
  *
  * @param integer $permId
  * @param array $perm
  * @param mixed $module optional, default value NULL
  * @access public
  * @return string $result XML
  */
  function getPermUserTreePermXML($permId, $perm, $module = NULL) {
    $result = '';
    if (isset($perm) && is_array($perm) && ($perm['perm_title'] != '')) {
      $administrationUser = $this->papaya()->administrationUser;
      $images = $this->papaya()->images;
      if ($administrationUser->isAdmin() ||
          ($perm['perm_active'] && ($administrationUser->hasPerm($permId)))) {
        $indent = (isset($module)) ? 3 : 2;
        $result .= sprintf(
          '<listitem title="%s" image="%s" indent="%d">',
          papaya_strings::escapeHTMLChars($this->_gt($perm['perm_title'])),
          papaya_strings::escapeHTMLChars($images['items-permission']),
          (int)$indent
        );
        $result .= '<subitem align="center">';
        $idx = ($perm['perm_active'])
          ? 'status-node-checked-disabled' : 'status-node-empty-disabled';
        $result .= sprintf(
          '<glyph src="%s"/>',
          papaya_strings::escapeHTMLChars($images[$idx])
        );
        $result .= '</subitem>';
        $result .= '<subitem align="center">';
        $perms = $this->user['perms'];
        if ($this->isAdmin()) {
          $idx = 'status-node-checked-disabled';
          $result .= sprintf(
            '<glyph src="%s"/>',
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        } elseif (isset($module) &&
                  isset($this->modulePermLinks[$module]['USER']) &&
                  is_array($this->modulePermLinks[$module]['USER']) &&
                  in_array($permId, $this->modulePermLinks[$module]['USER'])) {
          $idx = 'status-node-checked';
          $href = $this->getLink(
            array(
              'cmd' => 'modperm_out',
              'pid' => $permId,
              'uid' => $this->userId,
              'mod_id' => $module
            )
          );
          $result .= sprintf(
            '<a href="%s"><glyph src="%s"/></a>',
            papaya_strings::escapeHTMLChars($href),
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        } elseif (isset($module) && $this->hasPerm($permId, $module)) {
          $idx = 'status-node-checked-disabled';
          $result .= sprintf(
            '<glyph src="%s"/>',
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        } elseif (isset($module)) {
          $idx = 'status-node-empty';
          $href = $this->getLink(
            array(
              'cmd' => 'modperm_in',
              'pid' => $permId,
              'uid' => $this->userId,
              'mod_id' => $module
            )
          );
          $result .= sprintf(
            '<a href="%s"><glyph src="%s"/></a>',
            papaya_strings::escapeHTMLChars($href),
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        } elseif (isset($perms) && is_array($perms) && in_array($permId, $perms)) {
          $idx = 'status-node-checked';
          $href = $this->getLink(
            array(
              'cmd' => 'perm_out',
              'pid' => $permId,
              'uid' => $this->userId
            )
          );
          $result .= sprintf(
            '<a href="%s"><glyph src="%s"/></a>',
            papaya_strings::escapeHTMLChars($href),
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        } elseif (isset($moduleId) && $this->hasPerm($permId, $moduleId)) {
          $idx = 'status-node-checked-disabled';
          $result .= sprintf(
            '<glyph src="%s"/>',
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        } elseif ($this->hasPerm($permId)) {
          $idx = 'status-node-checked-disabled';
          $result .= sprintf(
            '<glyph src="%s"/>',
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        } else {
          $idx = 'status-node-empty';
          $href = $this->getLink(
            array(
              'cmd' => 'perm_in',
              'pid' => $permId,
              'uid' => $this->userId
            )
          );
          $result .= sprintf(
            '<a href="%s"><glyph src="%s"/></a>',
            papaya_strings::escapeHTMLChars($href),
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        }

        $result .= '</subitem>';
        $result .= '</listitem>'.LF;
      }
    }
    return $result;
  }

  /**
  * Get module permissions XML
  *
  * @param string $mode optional, default value 'perm'
  * @access public
  * @return mixed XML
  */
  function getModulePermsXML($mode = 'perm') {
    $result = '';
    if (isset($_GET['p_module'])) {
      $obj = new papaya_editmodules($_GET['p_module']);
    } else {
      $obj = new papaya_editmodules(NULL);
    }
    $obj->loadModulesList();
    if ($mode == 'group' || $mode == 'user') {
      $this->loadModulePermLinks(
        (isset($this->params['gid'])) ? (int)$this->params['gid'] : 0,
        (isset($this->params['uid'])) ? $this->params['uid'] : ''
      );
    }
    if (isset($obj->modules) && is_array($obj->modules)) {
      $result = $this->loadModulePerms();
      foreach ($obj->modules as $modId => $module) {
        switch ($mode) {
        case 'user':
          $addParam = array(
            'uid' => $this->params['uid']
          );
          $cols = 3;
          break;
        case 'group':
          $addParam = array(
            'gid' => $this->params['gid']
          );
          $cols = 3;
          break;
        default:
          $addParam = array();
          $cols = 2;
        }
        if ($this->permGroupOpened($modId)) {
          $nodeParams = array(
            'cmd' => 'permmod_close',
            'pmod' => $modId
          );
          $nodeStatus = 'open';
          $iconIdx = 'status-folder-open';
        } else {
          $nodeParams = array(
            'cmd' => 'permmod_open',
            'pmod' => $modId
          );
          $nodeStatus = 'close';
          $iconIdx = 'items-folder';
        }
        $indent = 1;
        $result .= sprintf(
          '<listitem title="%s" image="%s" indent="%d" node="%s" nhref="%s">'.LF,
          papaya_strings::escapeHTMLChars($module['module_title']),
          papaya_strings::escapeHTMLChars($this->papaya()->images[$iconIdx]),
          (int)$indent,
          papaya_strings::escapeHTMLChars($nodeStatus),
          papaya_strings::escapeHTMLChars($this->getLink(array_merge($nodeParams, $addParam)))
        );
        $result .= str_repeat('<subitem/>', $cols - 1);
        $result .= '</listitem>'.LF;

        if ($this->permGroupOpened($modId)) {
          $parent = $this->layout;
          $moduleObject = $this->papaya()->plugins->get(
            $module['module_guid'], $parent
          );
          if (property_exists($moduleObject, 'permissions')) {
            $perms = $moduleObject->permissions;
            if (isset($perms) && is_array($perms)) {
              uasort($perms, 'strnatcasecmp');
              foreach ($perms as $permId => $permTitle) {
                if (isset($this->modulePerms[$modId][$permId]) &&
                    ($this->modulePerms[$modId][$permId] == FALSE)) {
                  $permActive = FALSE;
                } else {
                  $permActive = TRUE;
                }
                $perm = array(
                  'perm_title' => $permTitle,
                  'perm_active' => $permActive
                );
                switch ($mode) {
                case 'group':
                  $result .= $this->getPermGroupTreePermXML(
                    $permId, $perm, $modId
                  );
                  break;
                case 'user':
                  $result .= $this->getPermUserTreePermXML(
                    $permId, $perm, $modId
                  );
                  break;
                default:
                  $result .= $this->getPermAdminTreePermXML(
                    $permId, $perm, $modId
                  );
                }
              }
            }
          } else {
            $this->addMsg(
              MSG_ERROR,
              papaya_strings::escapeHTMLChars(
                sprintf($this->_gt('Cannot initialize module class "%s"'), $module['module_class'])
              )
            );
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get group form XML
  *
  * @param integer $groupId
  * @access public
  * @return mixed XML
  */
  function getGroupFormXML($groupId) {
    if (isset($groupId) && $groupId > 0 &&
        isset($this->groups[$groupId]) &&
        is_array($this->groups[$groupId])) {
      $this->layout->add(
        $this->getDialog(
          'Change group',
          array('cmd' => 'group_edit', 'gid' => $groupId),
          $this->fieldsGroup,
          $this->groups[$groupId]
        )
      );
    }
  }

  /**
  * Get user form XML
  *
  * @access public
  * @return mixed XML
  */
  function getUserFormXML() {
    if (isset($this->user) && is_array($this->user)) {
      $this->layout->add(
        $this->getDialog(
          'Change user',
          array('cmd' => 'user_edit', 'uid' => $this->userId),
          $this->fieldsUser,
          $this->user
        )
      );
    }
  }

  /**
  * Get user group list XML
  *
  * @access public
  */
  function getUserGroupListXML() {
    $result = '';
    if (isset($this->groups) && is_array($this->groups) &&
        isset($this->user) && is_array($this->user)) {
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Groups'))
      );
      $result .= sprintf(
        '<cols><col>%s</col><col align="center">%s</col></cols>',
        papaya_strings::escapeHTMLChars($this->_gt('Group')),
        papaya_strings::escapeHTMLChars($this->_gt('Member'))
      );
      $result .= '<items>'.LF;
      $administrationUser = $this->papaya()->administrationUser;
      $images = $this->papaya()->images;
      foreach ($this->groups as $groupId => $group) {
        $result .= sprintf(
          '<listitem title="%s" image="%s">'.LF,
          papaya_strings::escapeHTMLChars($group['grouptitle']),
          papaya_strings::escapeHTMLChars($images['items-user-group'])
        );
        $result .= '<subitem align="center">';
        if (($administrationUser->isAdmin() || $administrationUser->inGroup($groupId)) &&
            $groupId != $this->user['group_id']) {
          if ($this->inGroup($groupId)) {
            $href = $this->getLink(
              array(
                'cmd' => 'group_out',
                'gid' => $groupId,
                'uid' => $this->userId
              )
            );
            $idx = 'status-node-checked';
          } else {
            $href = $this->getLink(
              array(
                'cmd' => 'group_in',
                'gid' => $groupId,
                'uid' => $this->userId
              )
            );
            $idx = 'status-node-empty';
          }
          $result .= sprintf(
            '<a href="%s"><glyph src="%s"/></a>',
            papaya_strings::escapeHTMLChars($href),
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        } else {
          $idx = ($this->inGroup($groupId))
            ? 'status-node-checked-disabled' : 'status-node-empty-disabled';
          $result .= sprintf(
            '<glyph src="%s"/>',
            papaya_strings::escapeHTMLChars($images[$idx])
          );
        }
        $result .= '</subitem>'.LF;
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    $this->layout->addRight($result);
  }

  /**
  * Get delete user form
  *
  * @access public
  */
  function getDelUserForm() {
    if ((!isset($this->params['confirm_delete'])) && isset($this->user) && is_array($this->user)) {
      $hidden = array(
        'cmd' => 'user_del',
        'uid' => $this->userId,
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete user "%s" (%s)?'),
        $this->user['fullname'],
        $this->user['username']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get delete group form
  *
  * @param integer $groupId
  * @access public
  */
  function getDelGroupForm($groupId) {
    if ((!isset($this->params['confirm_delete'])) &&
        isset($this->groups[$groupId]) &&
        is_array($this->groups[$groupId])) {
      $hidden = array(
        'cmd' => 'group_del',
        'gid' => $groupId,
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete group "%s" (%s)?'),
        $this->groups[$groupId]['grouptitle'],
        $groupId
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get dialog
  *
  * @see base_dialog::getDialogXML
  * @param string $title
  * @param array $params
  * @param array $fields
  * @param array $current
  * @access public
  * @return base_dialog
  */
  function getDialogObject($title, $params, $fields, $current = array()) {
    if (isset($fields) && is_array($fields) && count($fields) > 0) {
      $hidden = $params;
      $dialog = new base_dialog(
        $this, $this->paramName, $fields, $current, $hidden
      );
      $dialog->loadParams();
      $dialog->inputFieldSize = $this->inputFieldSize;
      $dialog->baseLink = $this->baseLink;
      $dialog->dialogTitle = $this->_gt($title);
      $dialog->dialogDoubleButtons = FALSE;
      $dialog->textYes = 'Yes';
      $dialog->textNo = 'No';
      if (isset($this->fieldErrors) && is_array($this->fieldErrors)) {
        $dialog->inputErrors = $this->fieldErrors;
      }
      return $dialog;
    }
    return NULL;
  }

  public function getDialog($title, $params, $fields, $current = array()) {
    $dialog = $this->getDialogObject($title, $params, $fields, $current);
    if (is_object($dialog)) {
      return $dialog->getDialogXML();
    }
    return '';
  }

  /**
  * Get group list combo
  *
  * @param string $name
  * @param array $element
  * @param mixed $data
  * @access public
  * @return string $result
  */
  function getGroupListCombo($name, $element, $data) {
    $result = '';
    if (isset($this->groups) && is_array($this->groups)) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale" fid="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name),
        papaya_strings::escapeHTMLChars($name)
      );
      foreach ($this->groups as $groupId => $group) {
        if ($this->papaya()->administrationUser->isAdmin() ||
            $this->papaya()->administrationUser->inGroup($groupId) ||
            $this->inGroup($groupId)) {
          $selected = ($groupId == $data) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%s"%s>%s</option>'.LF,
            papaya_strings::escapeHTMLChars($groupId),
            $selected,
            papaya_strings::escapeHTMLChars($group['grouptitle'])
          );
        }
      }
      $result .= '</select>'.LF;
    }
    return $result;
  }

  /**
   * Get group list combo including "any group"
   *
   * @param string $name
   * @param array $element
   * @param mixed $data
   * @access public
   * @return string $result
   */
  function getGroupListOrAnyCombo($name, $element, $data) {
    $result = '';
    if (isset($this->groups) && is_array($this->groups)) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale" fid="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name),
        papaya_strings::escapeHTMLChars($name)
      );
      $selected = ($data == 0) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="0"%s>[%s]</option>'.LF,
        $selected,
        $this->_gt('Any group')
      );
      foreach ($this->groups as $groupId => $group) {
        $selected = ($groupId == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($groupId),
          $selected,
          papaya_strings::escapeHTMLChars($group['grouptitle'])
        );
      }
      $result .= '</select>'.LF;
    }
    return $result;
  }

  /**
  * Get buttons XML
  *
  * @access public
  */
  function getButtonsXML() {
    $menubar = new base_btnbuilder;
    $menubar->images = $this->papaya()->images;
    $menubar->addButton(
      'Permissions', $this->getLink(), 'items-permission', '(De)activate permissions'
    );
    $menubar->addSeperator();
    $administrationUser = $this->papaya()->administrationUser;
    if ($administrationUser->hasPerm(Permissions::USER_GROUP_MANAGE)) {
      $menubar->addButton(
        'Add group',
        $this->getLink(array('cmd' => 'group_add')),
        'actions-user-group-add',
        ''
      );
      if (isset($this->params['gid']) && ($this->params['gid'] > 0)) {
        $menubar->addButton(
          'Delete group',
          $this->getLink(array('cmd' => 'group_del', 'gid' => $this->params['gid'])),
          'actions-user-group-delete',
          ''
        );
      }
    }
    $menubar->addSeperator();
    $menubar->addButton(
      'Add user',
      $this->getLink(array('cmd' => 'user_add')),
      'actions-user-add',
      ''
    );
    if ($this->editable() && isset($this->params['uid'])) {
      $menubar->addButton(
        'Delete user',
        $this->getLink(array('cmd' => 'user_del', 'uid' => $this->params['uid'])),
        'actions-user-delete',
        ''
      );
    }
    if ($str = $menubar->getXML()) {
      $this->layout->add('<menu>'.$str.'</menu>', 'menus');
    }
  }

  /**
  * Get user options list xml
  *
  * @access public
  */
  function getUserOptionsListXML() {
    $images = $this->papaya()->images;
    $result = sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Options'))
    );
    $result .= '<items>';
    foreach ($this->userOptions as $optName) {
      $optUserValue = $this->options[$optName];
      $params = array('uid' => $this->userId, 'opt' => $optName);
      $href = $this->getLink($params);
      if (isset($this->params['opt']) && $this->params['opt'] == $optName) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $optionField = papaya_options::$optFields[$optName];
      if (is_array($optionField) && $optionField[2] == 'combo') {
        $optUserValue = $optionField[3][$optUserValue];
      }
      $result .= sprintf(
        '<listitem title="%s" href="%s" image="%s"%s>',
        papaya_strings::escapeHTMLChars($optName),
        papaya_strings::escapeHTMLChars($href),
        papaya_strings::escapeHTMLChars($images['items-option']),
        $selected
      );
      $result .= sprintf(
        '<subitem>%s</subitem>',
        papaya_strings::escapeHTMLChars($optUserValue)
      );
      $result .= '</listitem>';
    }
    $result .= '</items>';
    $result .= '</listview>';
    $this->layout->add($result);
  }

  /**
  * Edit possible
  *
  * @access public
  * @return boolean
  */
  function editable() {
    if (!$this->papaya()->administrationUser->isAdmin()) {
      if (!($this->isValid || empty($this->params['user_id']))) {
        $this->load($this->params['user_id']);
      }
      if (isset($this->user) && is_array($this->user) &&
          isset($this->user['groups']) && is_array($this->user['groups'])) {
        foreach ($this->user['groups'] as $groupId) {
          if (!$this->papaya()->administrationUser->inGroup((int)$groupId)) {
            return FALSE;
          }
        }
      }
    }
    return TRUE;
  }

  /**
  * Get user options form
  *
  * @access public
  */
  function getUserOptionsForm() {
    if (isset($this->params['opt']) &&
        isset($this->options[$this->params['opt']])) {
      $optName = $this->params['opt'];
      if (isset(papaya_options::$optFields[$optName])) {
        $this->initOptionDialog($optName);
        $this->layout->add($this->optionDialog->getDialogXML());
        $globalOptions = new papaya_options();
        $this->layout->add(
          $globalOptions->getOptionHelp(
            $optName,
            $this->papaya()->administrationUser->options['PAPAYA_UI_LANGUAGE']
          )
        );
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Cannot find option data.'));
      }
    }
  }

  /**
  * Set user option
  *
  * @param string $optName option name
  * @param mixed $optValue
  * @access public
  * @return boolean
  */
  function setUserOption($optName, $optValue) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE user_id = '%s' AND opt_name = '%s'";
    $params = array($this->tableOptions, $this->userId, $optName);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      list($count) = $res->fetchRow();
      if ($count > 0) {
        $data = array(
          'opt_value' => $optValue
        );
        $filter = array(
          'user_id' => $this->userId,
          'opt_name' => $optName
        );
        return FALSE !== $this->databaseUpdateRecord(
          $this->tableOptions, $data, $filter
        );
      } else {
        $data = array(
          'user_id' => $this->userId,
          'opt_name' => $optName,
          'opt_value' => $optValue
        );
        return FALSE !== $this->databaseInsertRecord(
          $this->tableOptions, NULL, $data
        );
      }
    }
    return FALSE;
  }

  /**
  * Initialize option dialog
  *
  * @param string $optName option name
  * @access public
  */
  function initOptionDialog($optName) {
    if (!(isset($this->optionDialog) && is_object($this->optionDialog))) {
      $hidden = array(
        'save' => 1,
        'cmd' => 'opt_chg',
        'uid' => $this->userId,
        'opt' => $optName
      );
      $data = array();
      $fields = array(
        'opt_name' => array('Name', '', FALSE, 'info', 0, '', $optName)
      );
      if (is_array($optionField = papaya_options::$optFields[$optName])) {
        $fields[$optName] = array(
          'Value',
          $optionField[1],
          TRUE,
          $optionField[2],
          $optionField[3],
          '',
          $this->options[$optName]
        );
      } else {
        $fields[$optName] = array(
          'Value',
          '',
          TRUE,
          'info',
          '',
          '',
          empty($this->options[$optName]) ? $this->options[$optName] : ''
        );
      }
      $this->optionDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->optionDialog->dialogTitle = $this->_gt('Option');
      $this->optionDialog->baseLink = $this->baseLink;
      $this->optionDialog->loadParams();
    }
  }

  /**
  * Get interface language combo
  *
  * @param string $name
  * @param array $element
  * @param mixed $data
  * @access public
  * @return string $result
  */
  function getInterfaceLanguageCombo($name, $element, $data) {
    $sql = "SELECT lng_short, lng_title
              FROM %s
             WHERE is_interface_lng = 1
             ORDER BY lng_title";
    $result = '';
    if ($res = $this->databaseQueryFmt($sql, PAPAYA_DB_TBL_LNG)) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $selected = ($row['lng_short'] == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s (%s)</option>'.LF,
          papaya_strings::escapeHTMLChars($row['lng_short']),
          $selected,
          papaya_strings::escapeHTMLChars($row['lng_title']),
          papaya_strings::escapeHTMLChars($row['lng_short'])
        );
      }
      $result .= '</select>'.LF;
      $res->free();
    } else {
      $result = sprintf(
        '<input type="text" disabled="disabled" value="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('No language found'))
      );
    }
    return $result;
  }

  /**
  * Get language combo
  *
  * @param string $name
  * @param array $element
  * @param mixed $data
  * @access public
  * @return string XML
  */
  function getContentLanguageCombo($name, $element, $data) {
    $sql = "SELECT lng_id, lng_short, lng_title
              FROM %s
             WHERE is_content_lng = 1
             ORDER BY lng_title";
    $result = '';
    if ($res = $this->databaseQueryFmt($sql, PAPAYA_DB_TBL_LNG)) {
      $languages = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $languages[$row['lng_id']] = $row;
      }
      if (is_array($languages) && count($languages) > 0) {
        if (!isset($languages[$data])) {
          if (defined('PAPAYA_CONTENT_LANGUAGE')) {
            $data = PAPAYA_CONTENT_LANGUAGE;
          } else {
            $data = min(array_keys($languages));
          }
        }
        $result .= sprintf(
          '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name)
        );
        foreach ($languages as $lngId => $lng) {
          $selected = ($data > 0 && $lngId == $data) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%d"%s>%s (%s)</option>'.LF,
            papaya_strings::escapeHTMLChars($lng['lng_id']),
            $selected,
            papaya_strings::escapeHTMLChars($lng['lng_title']),
            papaya_strings::escapeHTMLChars($lng['lng_short'])
          );
        }
        $result .= '</select>'.LF;
        $res->free();
      } else {
        $result = '<input type="text" disabled="disabled" value="No language found"/>';
      }
    }
    return $result;
  }
}

