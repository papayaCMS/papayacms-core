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
* Administration user authentification
*
* Verifying and change user data for admin interface
*
* @package Papaya
* @subpackage Authentication
*/
class base_auth extends base_db {

  /**
  * papaya database table authentification options
  * @var string $tableOptions
  */
  var $tableOptions = PAPAYA_DB_TBL_AUTHOPTIONS;

  /**
  * papaya database table authentification user
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;
  /**
  * papaya database table authentification groups
  * @var string $tableAuthGroups
  */
  var $tableAuthGroups = PAPAYA_DB_TBL_AUTHGROUPS;
  /**
  * papaya database table authentification link
  * @var string $tableAuthUserLinks
  */
  var $tableAuthUserLinks = PAPAYA_DB_TBL_AUTHLINK;

  /**
  * papaya database table authentification permission
  * @var string $tableAuthPermissions
  */
  var $tableAuthPermissions = PAPAYA_DB_TBL_AUTHPERM;
  /**
  * papaya database table authentification module permissions
  * @var string $tableModulePermissions
  */
  var $tableModulePermissions = PAPAYA_DB_TBL_AUTHMODPERMS;
  /**
  * papaya database table authentification module permission links
  * @var string $tableModulePermissionsLinks
  */
  var $tableModulePermissionsLinks = PAPAYA_DB_TBL_AUTHMODPERMLINKS;

  /**
  * papaya database table surfer
  * @var string $tableCommunitySurfers
  */
  var $tableCommunitySurfers = PAPAYA_DB_TBL_SURFER;

  /**
  * parameter name
  * @var string $paramName
  */
  var $paramName = 'usr';
  /**
  * parameters
  * @var array $params
  */
  var $params = NULL;
  /**
  * session parameter name
  * @var string $sessionParamName
  */
  var $sessionParamName = 'PAPAYA_SESS_usr';
  /**
  * session parameter
  * @var array $sessionParams
  */
  var $sessionParams = NULL;

  /**
  * user
  * @var array $user
  */
  var $user = NULL;
  /**
  * user id
  * @var string $userId
  */
  var $userId = '';
  /**
  * user permissions
  * @var array $userPerms
  */
  var $userPerms = NULL;
  /**
  * start node
  * @var integer $startNode
  */
  var $startNode = 0;
  /**
  * sub level
  * @var integer $subLevel
  */
  var $subLevel = 0;

  /**
  * is valid status
  * @var boolean $isValid
  */
  var $isValid = NULL;

  /**
  * users
  * @var array $users
  */
  var $users = NULL;
  /**
  * groups
  * @var array $groups
  */
  var $groups = NULL;
  /**
  * parmissions
  * @var array $perms
  */
  var $perms = NULL;
  /**
  * group tree
  * @var array
  */
  var $groupTree = NULL;

  /**
  * layout
  *
  * @var \Papaya\Template $layout
  */
  var $layout = NULL;

  /**
  * Password fields
  * @var array $fieldsPassword
  */
  var $fieldsPassword = array(
    'username' => array ('Login', 'isNoHTML', TRUE, 'input', 30, '', ''),
    'password' => array ('Password', 'isPassword', FALSE, 'password', 30, '', ''),
    'password2' => array ('Repetition', 'isPassword', FALSE, 'password', 30,
      'Please input your password again.', '')
  );

  /**
  * user options
  * @var array
  */
  var $userOptions = array(
    'PAPAYA_UI_LANGUAGE', 'PAPAYA_CONTENT_LANGUAGE', 'PAPAYA_USE_RICHTEXT',
    'PAPAYA_OVERVIEW_ITEMS_UNPUBLISHED', 'PAPAYA_OVERVIEW_ITEMS_PUBLISHED',
    'PAPAYA_OVERVIEW_ITEMS_MESSAGES', 'PAPAYA_OVERVIEW_ITEMS_TASKS'
  );
  /**
  * options
  * @var \Papaya\BaseObject\Parameters
  */
  var $options;

  /**
  * list of user modules
  * @var array
  */
  var $userModules = array();

  /**
  * default username
  * @var string
  */
  var $defaultUsername = 'new';

  /**
   * @var boolean
   */
  public $isAuthuser = FALSE;

  /**
   * @var array
   */
  public $data = array();

  /**
   * @var array
   */
  protected $modulePerms = array();

  /**
   * @var array
   */
  protected $modulePermLinks = array();

  /**
   * @var Administration\Permissions
   */
  private $_permissions = NULL;

  /**
   * @var base_auth_secure
   */
  private $_passwordApi = NULL;

  /**
   * @var array
   */
  protected $fieldErrors = array();

  public function __construct() {
    parent::__construct();
    $this->options = new Papaya\BaseObject\Parameters();
  }

  /**
  * Initialisation of parameters
  *
  * @param string $paramName optional, default value 'usr'
  * @access public
  */
  function initialize($paramName = 'usr') {
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_auth_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
  }

  /**
  * Execute Login
  *
  * @access public
  * @return boolean Login successful
  */
  function execLogin() {
    $globalVariableName = $this->paramName.'_chgpass';
    if (isset($_GET[$globalVariableName]) &&
        preg_match('/^[a-fA-F\d]{32}$/', $_GET[$globalVariableName])) {
      $this->params['chgpass'] = $_GET[$globalVariableName];
    }
    if (isset($this->params['chgpass']) &&
        preg_match('/^[a-fA-F\d]{32}$/', $this->params['chgpass'])) {
      if ($userId = $this->checkChangePasswordId($this->params['chgpass'])) {
        return $this->changeForgottenPassword($userId);
      } else {
        $this->papaya()->messages->dispatch(
          new \Papaya\Message\Display(\Papaya\Message::SEVERITY_ERROR, 'Invalid password change token')
        );
        return FALSE;
      }
    } elseif (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'logout':
        if ($this->restoreLogin()) {
          $this->logout();
        }
        break;
      case 'forgot':
        $this->forgotPassword();
        return FALSE;
      }
    }
    return $this->login();
  }

  /**
  * Login function
  *
  * @access public
  * @return boolean
  */
  function login() {
    if (isset($this->params['login'])) {
      if ($this->checkLoginTry($this->params['username'])) {
        if ($this->loadLogin($this->params['username'], $this->params['password'])) {
          $this->logMsg(
            MSG_INFO,
            PAPAYA_LOGTYPE_USER,
            sprintf(
              'User "%s" logged in.',
              papaya_strings::escapeHTMLChars($this->user['fullname'])
            )
          );
          $this->setSessionToken($this->user['user_id']);
          if ($redirect = $this->papaya()->session->regenerateId()) {
            $redirect->send();
            exit();
          }
          return TRUE;
        } else {
          $this->logMsg(
            MSG_WARNING,
            PAPAYA_LOGTYPE_USER,
            sprintf(
              'Login of "%s" failed.',
              papaya_strings::escapeHTMLChars($this->params['username'])
            )
          );
        }
      } else {
        $this->logMsg(
          MSG_WARNING,
          PAPAYA_LOGTYPE_USER,
          sprintf(
            'Login of "%s" temporary disabled.',
            papaya_strings::escapeHTMLChars($this->params['username'])
          )
        );
      }
    } else {
      if ($this->restoreLogin()) {
        return TRUE;
      }
    }
    $this->getLoginXML();
    return FALSE;
  }

  /**
  * Store user guid in session
  * @param string $userGuid
  * @return void
  */
  function setSessionToken($userGuid) {
    $this->sessionParams['user_guid'] = $userGuid;
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Logout function
  *
  * @access public
  */
  function logout() {
    $this->logMsg(
      MSG_INFO,
      PAPAYA_LOGTYPE_USER,
      sprintf(
        'User "%s" logged out.',
        papaya_strings::escapeHTMLChars($this->user['fullname'])
      )
    );
    $this->isValid = FALSE;
    unset($this->user);
    $this->setSessionToken(0);
  }

  /**
  * Load login data
  * @param string $username
  * @param string $password
  * @return boolean user valid?
  */
  function loadLogin($username, $password) {
    $this->user = array();
    $this->isValid = FALSE;
    $sql = "SELECT user_id, user_password
              FROM %s
             WHERE username = '%s'
               AND active = '1'";
    $params = array($this->tableAuthUser, $username, $password);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($this->passwordApi()->verifyPassword($password, $row['user_password'])) {
          if ($hash = $this->passwordApi()->rehashPassword($password, $row['user_password'])) {
            $this->databaseUpdateRecord(
              $this->tableAuthUser,
              array('user_password' => $hash),
              array('user_id' => $row['user_id'])
            );
          }
          $this->load($row['user_id'], TRUE);
          $this->setSessionToken($row['user_id']);
        }
      } elseif ($username == 'admin' && $password == '') {
        if ($this->addDefaultAdminUser()) {
          return $this->loadLogin($username, '');
        }
      }
    }
    return $this->isValid;
  }

  /**
  * Login aus Sessiondaten wiederherstellen
  *
  * @access public
  * @return boolean Login restore successful
  */
  function restoreLogin() {
    $this->user = array();
    $this->isValid = FALSE;
    $this->load($this->sessionParams['user_guid'], TRUE);
    return $this->isValid;
  }

  /**
  * Load user data
  * @param integer $uid
  * @param boolean $login
  * @return boolean loading successful
  */
  function load($uid, $login = FALSE) {
    unset($this->user);
    $this->isValid = FALSE;
    $active = ($login) ? " AND active = '1'" : '';
    $sql = "SELECT user_id, group_id, username, surname, givenname,
                   user_password, email, start_node, sub_level, active, userperm, handoff_group_id
              FROM %s
             WHERE user_id = '%s' $active";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableAuthUser, $uid))) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['perms'] = $this->decodePermStr($row['userperm']);
        $row['fullname'] = $row['givenname'].' '.$row['surname'];
        $this->user = $row;
        $this->userId = $row['user_id'];
        $this->isValid = TRUE;
        $this->startNode = (int)$row['start_node'];
        $this->subLevel = (int)$row['sub_level'];
        $this->userPerms = $this->loadUserPerms($login);
        if ($row['user_password'] == $this->passwordApi()->getPasswordHash('')) {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('User has empty password. Please change it.')
          );
        }
      }
    }
    $this->loadOptions();
    return $this->isValid;
  }

  /**
  * Load user data by name
  * @param string $username
  * @param boolean $login
  * @return boolean
  */
  function loadByUserName($username, $login = FALSE) {
    unset($this->user);
    $this->isValid = FALSE;
    $active = ($login) ? ' AND active = 1' : '';
    $sql = "SELECT user_id, group_id, username, surname, givenname, email
              FROM %s
             WHERE username = '%s' $active";
    $params = array($this->tableAuthUser, $username);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['fullname'] = $row['givenname'].' '.$row['surname'];
        $this->user = $row;
        $this->userId = $row['user_id'];
        $this->isValid = TRUE;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load options for current user
  * @return void
  */
  function loadOptions() {
    $this->options->clear();
    if ($this->isValid) {
      $sql = "SELECT opt_name, opt_value
                FROM %s
               WHERE user_id = '%s'";
      $params = array($this->tableOptions, $this->userId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ($row['opt_name'] == 'PAPAYA_USER_MODULES') {
            if (preg_match_all('~[a-fA-F\d]{32}~', $row['opt_value'], $matches)) {
              $this->userModules = $matches[0];
            }
          } elseif (in_array($row['opt_name'], $this->userOptions)) {
            if (!isset($this->options[$row['opt_name']])) {
              $this->options[$row['opt_name']] = $row['opt_value'];
            }
          }
        }
      }
    }
    foreach ($this->userOptions as $opt) {
      if (!isset($this->options[$opt])) {
        $this->options[$opt] = $this->papaya()->options[$opt];
      }
    }
  }



  /**
  * save an user option to database
  *
  * @param $optionName
  * @param $optionValue
  * @access public
  * @return boolean
  */
  function saveUserOption($optionName, $optionValue) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE user_id = '%s'
               AND opt_name = '%s'";
    $params = array($this->tableOptions, $this->userId, $optionName);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($res->fetchField() > 0) {
        $data = array(
          'opt_value' => $optionValue
        );
        $filter = array(
          'opt_name' => $optionName,
          'user_id' => $this->userId
        );
        return (FALSE !== $this->databaseUpdateRecord($this->tableOptions, $data, $filter));
      } else {
        $data = array(
          'opt_value' => $optionValue,
          'opt_name' => $optionName,
          'user_id' => $this->userId
        );
        return (FALSE !== $this->databaseInsertRecord($this->tableOptions, NULL, $data));
      }
    }
    return FALSE;
  }

  /**
  * Load user permissions
  *
  * @param boolean $login optional, default value FALSE
  * @access public
  * @return array permissions
  */
  function loadUserPerms($login = FALSE) {
    $perms = NULL;
    $this->isAuthuser = $login;
    if ($this->isValid) {
      // Load user to group mapping
      $sql = "SELECT user_id, group_id
                FROM %s
               WHERE user_id = '%s'";
      $params = array($this->tableAuthUserLinks, $this->userId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->user['groups'][] = $row['group_id'];
        }
        if (isset($this->user['groups']) && is_array($this->user['groups'])) {
          if (!in_array($this->user['group_id'], $this->user['groups'])) {
            $this->user['groups'][] = $this->user['group_id'];
          }
        } else {
          $this->user['groups'] = array($this->user['group_id']);
        }
      }
      // Load groups (if not explictly loaded yet)
      if (!(isset($this->groups) && is_array($this->groups))) {
        $this->loadGroups($this->user['groups']);
      }

      // Admin?
      if (isset($this->user['groups']) && is_array($this->user['groups'])) {
        $activePerms = array();
        if ($this->isAdmin() && $login) {
          $sql = "SELECT perm_id FROM %s WHERE perm_active = '1' OR permgroup_id = '%d'";
          $params = array(
            $this->tableAuthPermissions,
            Administration\Permission\Groups::SYSTEM
          );
          if ($res = $this->databaseQueryFmt($sql, $params)) {
            while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
              $activePerms[] = (int)$row['perm_id'];
            }
            $perms = $activePerms;
          }
          if (!(isset($perms) && is_array($perms) && count($perms) > 0)) {
            $perms = array_keys($this->permissions()->toArray());
          }
        } else {
          // User permissions
          $perms = $this->user['perms'];
          settype($perms, 'array');

          // Add group permissions
          if (isset($this->user['groups']) && is_array($this->user['groups'])) {
            foreach ($this->user['groups'] as $groupId) {
              if (isset($this->groups[(int)$groupId]) &&
                  is_array($this->groups[(int)$groupId]) &&
                  isset($this->groups[(int)$groupId]['perms']) &&
                  is_array($this->groups[(int)$groupId]['perms'])) {
                $perms = array_merge($perms, $this->groups[(int)$groupId]['perms']);
              }
            }
          }
          $perms = array_unique($perms);
          // Remove permissions deactivated at login
          if ($login && is_array($perms)) {
            $sql = "SELECT perm_id FROM %s WHERE perm_active = '1'";
            $params = array($this->tableAuthPermissions);
            if ($res = $this->databaseQueryFmt($sql, $params)) {
              while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $activePerms[] = (int)$row['perm_id'];
              }
              $perms = array_intersect($perms, $activePerms);
            }
          }
        }
      }
    }
    return $perms;
  }

  /**
  * Load groups
  *
  * @param array $groupIds optional, default value NULL
  * @access public
  */
  function loadGroups($groupIds = NULL) {
    unset($this->groups);
    if (isset($groupIds) && is_array($groupIds)) {
      $filter = 'WHERE '.
        str_replace('%', '%%', $this->databaseGetSQLCondition('group_id', $groupIds));
    } else {
      $filter = '';
    }
    //special group for admins - not in database
    if (isset($groupIds[-1]) || !isset($groupIds)) {
      $this->groups[-1] = array(
        'group_id' => -1,
        'grouptitle' => $this->_gt('Administrator'),
        'groupperm' => ''
      );
    }
    //load user defined groups
    $sql = "SELECT group_id, grouptitle, groupperm
              FROM %s
              $filter
             ORDER BY grouptitle, group_id";
    $params = array($this->tableAuthGroups);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['perms'] = $this->decodePermStr($row['groupperm']);
        $this->groups[(int)$row["group_id"]] = $row;
      }
    }
  }

  /**
  * Load Module permissions
  *
  * @param integer $moduleId  optional, default value  NULL
  * @return boolean
  */
  function loadModulePerms($moduleId = NULL) {
    $sql = "SELECT module_id, modperm_id, modperm_active
              FROM %s ";
    if (isset($moduleId)) {
      $sql .= " WHERE module_id = '%s' ";
      if (isset($this->modulePerms[$moduleId])) {
        unset($this->modulePerms[$moduleId]);
      }
    } else {
      unset($this->modulePerms);
    }
    $sql .= ' ORDER BY module_id, modperm_id';
    $params = array($this->tableModulePermissions, $moduleId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->modulePerms[$row['module_id']][$row['modperm_id']] = $row['modperm_active'];
      }
      $res->free();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load Module permission Links
  *
  * @param integer $groupId optional, default value undefined
  * @param integer $userId optional, default value undefined
  * @param integer $moduleId  optional, default value  NULL
  *
  * @return boolean
  */
  function loadModulePermLinks($groupId, $userId, $moduleId = NULL) {
    $this->loadModulePerms($moduleId);
    $sql = "SELECT group_id, user_id, module_id, module_perms
              FROM %s
             WHERE ((group_id = %d AND user_id = '0')
                OR (group_id = 0 AND user_id = '%s') ";
    if (isset($this->user['groups']) && is_array($this->user['groups'])) {
      $sql .= " OR (";
      $sql .= str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('group_id', $this->user['groups'])
      );
      $sql .= " AND (user_id = '0'))";
    }
    $sql .= ') ';
    if (isset($moduleId)) {
      $sql .= " AND module_id = '%s' ";
      $this->modulePermLinks[$moduleId] = array();
    } else {
      unset($this->modulePermLinks);
    }
    $sql .= ' ORDER BY module_id';
    $params = array($this->tableModulePermissionsLinks, $groupId, $userId, $moduleId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $perms = $this->decodePermStr($row['module_perms']);
        if ($row['user_id'] == $userId) {
          $this->modulePermLinks[$row['module_id']]['USER'] = $perms;
        } elseif ($row['group_id'] == $groupId) {
          $this->modulePermLinks[$row['module_id']]['GROUP'] = $perms;
        }
        if (isset($this->modulePermLinks[$row['module_id']]['ALL']) &&
            is_array($this->modulePermLinks[$row['module_id']]['ALL'])) {
          if (is_array($perms)) {
            $this->modulePermLinks[$row['module_id']]['ALL'] =
              array_merge($this->modulePermLinks[$row['module_id']]['ALL'], $perms);
          }
        } else {
          $this->modulePermLinks[$row['module_id']]['ALL'] = $perms;
        }
      }
      $res->free();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Check permissions
  *
  * @access public
  * @return boolean
  */
  function checkPerms() {
    $permissions = array();
    $sql = "SELECT perm_id
              FROM %s";
    $parameters = array($this->tableAuthPermissions);
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $permissions[(int)$row["perm_id"]] = TRUE;
      }
    }
    $data = array();
    $iterator = new RecursiveIteratorIterator(
      $this->permissions()->groups(), RecursiveIteratorIterator::SELF_FIRST
    );
    $groupId = 0;
    foreach ($iterator as $id => $value) {
      if ($iterator->getDepth() == 0) {
        $groupId = $id;
      } elseif ($iterator->getDepth() == 1) {
        foreach ($value as $permissionId => $permissionTitle) {
          if (!isset($permissions[$permissionId])) {
            $data[] = array(
              'perm_id' => (int)$permissionId,
              'perm_title' => $permissionTitle,
              'permgroup_id' => (int)$groupId
            );
          }
        }
      }
    }
    if (count($data) > 0) {
      return (
        FALSE !== $this->databaseInsertRecords($this->tableAuthPermissions, $data)
      );
    }
    return TRUE;
  }

  /**
  * Check if user ID exists
  *
  * @param string $id
  * @access public
  * @return boolean
  */
  function existUserID($id) {
    $sql = "SELECT COUNT(*)
             FROM %s
            WHERE user_id = '%s'";
    $params = array($this->tableAuthUser, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Check if surfer ID exists
  *
  * @param string $id
  * @access public
  * @return boolean
  */
  function existSurferID($id) {
    $sql = "SELECT COUNT(*)
             FROM %s
            WHERE surfer_id = '%s'";
    $params = array($this->tableCommunitySurfers, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Create & verify new ID (unique upon any surfer || user)
  *
  * @access public
  * @return string
  * @version kersken 2007-04-26
  */
  function createId() {
    srand((double)microtime() * 10000000);
    do {
      $id = (string)md5(uniqid(rand()));
    } while ($this->existUserID($id) || $this->existSurferID($id));
    return $id;
  }

  /**
  * This method returns the surfer id of the current backend user
  *
  * @return string $result the auth users surferId
  *
  * @author David Rekowski <info@papaya-cms.com>
  * @since 2008-07-09
  */
  function getSurferId() {
    if ((!isset($this->surferId) || $this->surferId == '') && isset($this->user)) {
      $sql = "SELECT auth_user_id, surfer_id, surfer_handle
                FROM %s
               WHERE auth_user_id = '%s'";
      $params = array($this->tableCommunitySurfers, $this->user['user_id']);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ($row['surfer_handle'] == $this->user['username']) {
            $this->surferId = $row['surfer_id'];
          } else {
            $this->logMsg(
              MSG_ERROR,
              PAPAYA_LOGTYPE_USER,
              'Found inconsistency in user database.',
              sprintf(
                'User with auth user id "%s" has got community handle "%s" but username "%s".',
                $this->user['user_id'],
                $row['surfer_handle'],
                $this->user['username']
              )
            );
          }
        }
      }
    }
    return $this->surferId;
  }

  /**
  * synchronize Surfer
  *
  * @param integer $userId
  * @access public
  */
  function synchronizeSurfer($userId) {
    if (isset($this->user)) {
      $sql = "SELECT auth_user_id, surfer_id FROM %s WHERE surfer_handle = '%s'";
      $params = array($this->tableCommunitySurfers, $this->user['username']);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ($row['auth_user_id'] != $userId) {
            $this->databaseDeleteRecord(
              $this->tableCommunitySurfers, 'surfer_id', $row['surfer_id']
            );
          }
        }
      }
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE auth_user_id = '%s'";
      $params = array($this->tableCommunitySurfers, $userId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          $data = array(
            "surfer_handle" => $this->user['username'],
            "surfer_givenname" => $this->user['givenname'],
            "surfer_surname" => $this->user['surname'],
            "surfer_email" => $this->user['email'],
            "surfer_password" => $this->user['user_password'],
          );
          if ($row[0] > 0) {
            $this->databaseUpdateRecord(
              $this->tableCommunitySurfers, $data, 'auth_user_id', $userId
            );
          } else {
            $data['surfer_id'] = $this->createId();
            $data['auth_user_id'] = $userId;
            $this->databaseInsertRecord(
              $this->tableCommunitySurfers, NULL, $data
            );
          }
        }
      }
    }
    $this->databaseDeleteRecord($this->tableAuthUserLinks, array('user_id' => $userId, 'group_id' => 0 ));
  }

  /**
  * Check user is in database
  *
  * @param string $username
  * @access public
  * @return string|FALSE valid user id or FALSE
  */
  function userExists($username) {
    if (trim($username) != '') {
      $sql = "SELECT user_id
                FROM %s
               WHERE username = '%s'";
      $params = array($this->tableAuthUser, $username);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          return $row[0];
        }
      }
    }
    return FALSE;
  }

  /**
  * Check Surfer is in database
  *
  * @param integer $surferName
  * @access public
  * @return mixed surfer id or FALSE
  */
  function surferExists($surferName) {
    if (trim($surferName) != '') {
      $sql = "SELECT surfer_id
                FROM %s
               WHERE surfer_handle = '%s'";
      $params = array($this->tableCommunitySurfers, $surferName);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          return $row[0];
        }
      }
    }
    return FALSE;
  }

  /**
  * Save login data
  *
  * @param integer $userId
  * @access public
  * @return boolean
  */
  function saveLoginData($userId) {
    $result = FALSE;
    if ($chkUserId = $this->userExists($this->params['username'])) {
      if ($chkUserId == $userId) {
        if (trim($this->params['password']) != '') {
          $data = array(
            'user_password' => $this->passwordApi()->getPasswordHash($this->params['password']),
            'chg_id' => '',
            'chg_time' => '0'
          );
          $updated = $this->databaseUpdateRecord(
            $this->tableAuthUser, $data, "user_id", $userId
          );
          if (FALSE !== $updated) {
            $this->addMsg(
              MSG_INFO,
              $this->_gt('Password modified.')
            );
            $result = TRUE;
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
      } else {
        $this->addMsg(
          MSG_ERROR,
          sprintf(
            $this->_gt('Login "%s" already in use.'),
            $this->params['username']
          )
        );
      }
    } elseif ($this->surferExists($this->params['username'])) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('Login "%s" already in use for community user.'),
          $this->params['username']
        )
      );
    } else {
      if (trim($this->params['password']) != '') {
        $data = array(
          'username' => $this->params['username'],
          'user_password' => $this->passwordApi()->getPasswordHash($this->params['password']),
          'chg_id' => '',
          'chg_time' => 0
        );
        $updated = $this->databaseUpdateRecord(
          $this->tableAuthUser, $data, "user_id", $userId
        );
        if (FALSE !== $updated) {
          $this->addMsg(
            MSG_INFO,
            $this->_gt('Login and password modified.')
          );
          $result = TRUE;
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error! Changes not saved.')
          );
        }
      } else {
        $data = array(
          'username' => $this->params['username'],
          'chg_id' => '',
          'chg_time' => '0'
        );
        $updated = $this->databaseUpdateRecord(
          $this->tableAuthUser, $data, "user_id", $userId
        );
        if (FALSE !== $updated) {
          $this->addMsg(
            MSG_INFO,
            $this->_gt('Login modified.')
          );
          $result = TRUE;
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error! Changes not saved.')
          );
        }
      }
    }
    if ($result == TRUE) {
      $this->load($userId);
      $this->synchronizeSurfer($userId);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
  * Save change password id
  *
  * @param integer $userId
  * @param integer $chgId
  * @access public
  * @return string|FALSE user id or FALSE
  */
  function saveChangePasswordId($userId, $chgId) {
    if (preg_match('/^[a-fA-F\d]{32}$/', $userId)) {
      $validUntil = time() + 86400;
      $data = array(
        'chg_id' => $chgId,
        'chg_time' => $validUntil
      );
      return (
        FALSE !== $this->databaseUpdateRecord($this->tableAuthUser, $data, "user_id", $userId)
      );
    }
    return FALSE;
  }

  /**
  * Check change password id
  *
  * @param string $chgId
  * @access public
  * @return mixed user id or FALSE
  */
  function checkChangePasswordId($chgId) {
    if (preg_match('/^[a-fA-F\d]{32}$/', $chgId)) {
      $sql = "SELECT user_id
                FROM %s
               WHERE chg_id = %d
                 AND chg_time > %d";
      $params = array($this->tableAuthUser, $chgId, time());
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          return $row[0];
        }
      }
    }
    return FALSE;
  }

  /**
  * Decode permission string
  *
  * @param string $str
  * @access public
  * @return mixed
  */
  function decodePermStr($str) {
    if (preg_match_all("/\d+/", $str, $matches)) {
      return $matches[0];
    } else {
      return array();
    }
  }

  /**
  * Add a default administration user if no one exists
  * @return boolean
  */
  function addDefaultAdminUser() {
    $sql = "SELECT COUNT(*)
              FROM %s";
    $params = array($this->tableAuthUser);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      list($userCount) = $res->fetchRow();
      if ($userCount == 0) {
        $userId = $this->createId();
        $data = array(
          'user_id' => $userId,
          'username' => 'admin',
          'givenname' => 'Default',
          'surname' => $this->_gt('Administrator'),
          'group_id' => -1,
          'active' => TRUE,
          'start_node' => 0,
          'sub_level' => 0,
          'user_password' => $this->passwordApi()->getPasswordHash('')
        );
        if (FALSE !== $this->databaseInsertRecord($this->tableAuthUser, NULL, $data)) {
          $this->addMsg(MSG_INFO, $this->_gt('New user created.'));
          $this->params['uid'] = $userId;
          //ok now we change all resourcen to this user - because it is the only one.
          $data = array(
            'author_id' => $userId,
            'author_group' => -1
          );
          $this->databaseUpdateRecord(PAPAYA_DB_TBL_TOPICS, $data, array(1 => 1));
          $this->databaseUpdateRecord(PAPAYA_DB_TBL_TOPICS_PUBLIC, $data, array(1 => 1));
          $data = array(
            'version_author_id' => $userId
          );
          $this->databaseUpdateRecord(PAPAYA_DB_TBL_TOPICS_VERSIONS, $data, array(1 => 1));
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Check login input
  *
  * @param boolean $setPassword optional, default value FALSE
  * @access public
  * @return boolean
  */
  function checkLoginInput($setPassword = FALSE) {
    $result = FALSE;
    if (isset($this->params['uid']) && (!$this->isValid)) {
      $this->load($this->params['uid']);
    }
    if (isset($this->user) && is_array($this->user)) {
      if (isset($this->papaya()->administrationUser) &&
          $this->papaya()->administrationUser->userId == $this->userId &&
          $this->params['username'] != $this->user['username']) {
        $this->addMsg(
          MSG_WARNING,
          $this->_gt('You can not change your own username.')
        );
        $this->params['username'] = $this->user['username'];
      }
      if ((trim($this->params['password']) == '') && $setPassword) {
        $this->addMsg(MSG_ERROR, $this->_gt('Please enter a password.'));
        return FALSE;
      } elseif ($this->params['password'] != $this->params['password2']) {
        $this->addMsg(MSG_ERROR, $this->_gt('The passwords didn`t match!'));
        return FALSE;
      }
      return TRUE;
    }
    return $result;
  }

  /**
  * Check dialog input
  *
  * @param array $fields
  * @access public
  * @return boolean
  */
  function checkDialogInput($fields) {
    unset($this->fieldErrors);
    $result = TRUE;
    if (isset($checkFunctions) && is_array($checkFunctions) &&
        isset($fields) && is_array($fields)) {
      foreach ($fields as $key => $val) {
        $checkFunction = strtolower($val[1]);
        if (checkit::has($checkFunction)) {
          $paramValue = isset($this->params[$key]) ? $this->params[$key] : '';
          if (checkit::validate($paramValue, $checkFunction, $val[2])) {
            $this->fieldErrors[$key] = 0;
            $this->data[$key] = $paramValue;
          } else {
            $this->addMsg(
              MSG_ERROR,
              sprintf(
                $this->_gt('The input in field "%s" is not correct.'),
                $this->_gt($val[0])
              )
            );
            $this->fieldErrors[$key] = 1;
            $result = FALSE;
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get login dialog XML
  *
  * @access public
  */
  function getLoginXML() {
    if (isset($this->layout) && is_object($this->layout)) {
      $result = sprintf(
        '<login title="%s" action="%s" is-secure="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Login')),
        papaya_strings::escapeHTMLChars($this->baseLink),
        \Papaya\Utility\Server\Protocol::isSecure() ? 'yes' : 'no'
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[login]" value="1" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      if (isset($this->params["login"])) {
        $result .= sprintf(
          '<message>%s.</message>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Login failed'))
        );
      }
      $result .= sprintf(
        '<hint><a href="%s">%s</a></hint>'.LF,
        papaya_strings::escapeHTMLChars($this->getLink(array('cmd' => 'forgot'))),
        papaya_strings::escapeHTMLChars($this->_gt('Forgot password?'))
      );
      if (!\Papaya\Utility\Server\Protocol::isSecure() &&
          $this->papaya()->options->get('PAPAYA_UI_SECURE_WARNING', TRUE)) {
        $url = new \Papaya\URL\Current();
        $url->setScheme('https');
        $result .= sprintf(
          '<hint><p>%s</p><a href="%s">%s</a></hint>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->_gt('If possible, please use https to access the administration interface.')
          ),
          papaya_strings::escapeHTMLChars($url->getURL()),
          papaya_strings::escapeHTMLChars($this->_gt('Switch to Https!'))
        );
      }

      $result .= '<fields>';
      $result .= sprintf(
        '<field ident="username" name="%s[username]" title="%s" value="%s"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt('Username')),
        empty($this->params['username'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['username'])
      );
      $result .= sprintf(
        '<field ident="password" name="%s[password]" title="%s" />',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt('Password'))
      );

      $result .= '</fields>';
      $result .= sprintf(
        '<button title="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('OK'))
      );
      $result .= '</login>';

      $this->layout->parameters()->set('PAGE_TITLE', $this->_gt('Login'));
      $this->layout->parameters()->set(
        'PAGE_ICON',
        $this->papaya()->images['status-system-locked']
      );
      $this->layout->add($result);
    }
  }

  /**
  * forgot password
  *
  * @access public
  */
  function forgotPassword() {
    if (isset($this->params['send_password']) &&
        !empty($this->params['username'])) {
      if ($this->loadByUserName($this->params['username'])) {
        srand((double)microtime() * 1000000);
        $chgId = md5(uniqid(rand()));

        if ($this->saveChangePasswordId($this->userId, $chgId)) {
          $href = $this->getLink(array($this->paramName.'_chgpass' => $chgId), '');
          $subject = PAPAYA_PROJECT_TITLE.' - '.$this->_gt('Password change requested');
          $mailMessage = $this->_gtfile('auth_change.txt');
          if (empty($mailMessage)) {
            $mailMessage = "Hello {%NAME%},\n\n".
              "You get this mail because here was a password change request for you".
              " at {%PROJECT%}\n\n".
              "Please ignore this mail if you do not like to change you password.\n\n".
              "To change your password please click the link:\n\n".
              "<{%LINK%}>";
          }
          $mailValues = array(
            'name' => $this->user['givenname'].' '.$this->user['surname'],
            'project' => PAPAYA_PROJECT_TITLE,
            'link' => $this->getAbsoluteURL($href, '', FALSE)
          );

          $email = new email();
          $email->setSubject($subject);
          $email->setBody($mailMessage, $mailValues);
          $email->addAddress(
            $this->user['email'], $this->user['givenname'].' '.$this->user['surname']
          );

          if (\Papaya\Filter\Factory::isEmail($this->user['email'], TRUE)) {
            $email->send();
          }
        }
      }
      $message = $this->_gt(
        'The system sent you an email with a link to change your password.
         Please remember that this link is only valid for 24 hours. If you do not get an
         email the username might be invalid or here is a technical problem. Please contact
         the site administrator if you do not get an email in a reasonable time.'
      );
      $result = sprintf(
        '<login title="%s" action="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Request password change')),
        papaya_strings::escapeHTMLChars($this->baseLink)
      );
      $result .= sprintf(
        '<message>%s</message>'.LF,
        papaya_strings::escapeHTMLChars($message)
      );
      $result .= sprintf(
        '<button title="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Show login'))
      );
      $result .= '</login>';
      $this->layout->parameters()->set('PAGE_TITLE', $this->_gt('Password forgotten'));
      $this->layout->parameters()->set(
        'PAGE_ICON', $this->papaya()->images['status-system-locked']
      );
      $this->layout->add($result);
    } else {
      $result = sprintf(
        '<login title="%s" action="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Request password change')),
        papaya_strings::escapeHTMLChars($this->baseLink)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="forgot" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[send_password]" value="1" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );

      $result .= sprintf(
        '<message>%s</message>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->_gt(
            'The system will send you an email with a link to change your password.'.
            ' This link is valid for 24 hours.'
          )
        )
      );
      $result .= '<fields>';
      $result .= sprintf(
        '<field ident="username" name="%s[username]" title="%s" value="%s"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt('Username')),
        empty($this->params['username'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['username'])
      );

      $result .= '</fields>';
      $result .= sprintf(
        '<button title="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Request email'))
      );
      $result .= '</login>';

      $this->layout->parameters()->set('PAGE_TITLE', $this->_gt('Password forgotten'));
      $this->layout->parameters()->set(
        'PAGE_ICON', $this->papaya()->images['status-system-locked']
      );
      $this->layout->add($result);
    }
  }

  /**
  * Change forgotten password
  *
  * @param integer $userId
  * @access public
  * @return boolean
  */
  function changeForgottenPassword($userId) {
    $this->load($userId);
    if (isset($this->user)) {
      $changed = FALSE;
      $this->params['username'] = $this->user['username'];
      if (isset($this->params['change_password']) &&
          $this->checkDialogInput($this->fieldsPassword) && $this->checkLoginInput(TRUE)) {
          $changed = $this->saveLoginData($userId);
      }
      if ($changed) {
        return $this->login();
      } else {
        $result = sprintf(
          '<login title="%s" action="%s">',
          papaya_strings::escapeHTMLChars($this->_gt('Request password change')),
          papaya_strings::escapeHTMLChars($this->baseLink)
        );
        $result .= sprintf(
          '<input type="hidden" name="%s[chgpass]" value="%s" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          empty($this->params['chgpass'])
            ? '' : papaya_strings::escapeHTMLChars($this->params['chgpass'])
        );
        $result .= sprintf(
          '<input type="hidden" name="%s[change_password]" value="1" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName)
        );
        $result .= sprintf(
          '<message>%s</message>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->_gt(
              'Please input the new password in both input fields!'.
              ' The password must be at least 8 characters and contain two nonletter characters.'
            )
          )
        );
        $result .= '<fields>';
        $result .= sprintf(
          '<field ident="password" name="%s[password]" title="%s"/>',
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($this->_gt('Password'))
        );
        $result .= sprintf(
          '<field ident="password2" name="%s[password2]" title="%s"/>',
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($this->_gt('Repetition'))
        );

        $result .= '</fields>';
        $result .= sprintf(
          '<button title="%s"/>',
          papaya_strings::escapeHTMLChars($this->_gt('Change'))
        );
        $result .= '</login>';

        $this->layout->parameters()->set('PAGE_TITLE', $this->_gt('Change password'));
        $this->layout->parameters()->set(
          'PAGE_ICON', $this->papaya()->images['status-system-locked']
        );
        $this->layout->add($result);
      }
    }
    return FALSE;
  }

  /**
  * Has module permission
  *
  * @param integer $permId
  * @param integer $moduleId
  * @access public
  * @return mixed
  */
  function hasModulePerm($permId, $moduleId) {
    if (!isset($this->modulePermLinks[$moduleId])) {
      $this->loadModulePermLinks(
        $this->user['group_id'], $this->user['user_id'], $moduleId
      );
    }
    if ($this->isAdmin() && $this->isModulePermActive($permId, $moduleId)) {
      return TRUE;
    } elseif (isset($this->modulePermLinks[$moduleId]['ALL']) &&
              is_array($this->modulePermLinks[$moduleId]['ALL']) &&
              in_array($permId, $this->modulePermLinks[$moduleId]['ALL'])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Is module permission active
  *
  * @param integer $permId
  * @param integer $modId
  * @access public
  * @return boolean
  */
  function isModulePermActive($permId, $modId) {
    if (isset($this->modulePerms[$modId][$permId]) &&
        $this->modulePerms[$modId][$permId] == FALSE) {
      //module permission deactivated
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
  * Has permission
  *
  * @param integer $permId
  * @param mixed $moduleId optional, default value NULL
  * @access public
  * @return boolean
  */
  function hasPerm($permId, $moduleId = NULL) {
    if (isset($moduleId)) {
      return $this->hasModulePerm($permId, $moduleId);
    } elseif (isset($this->userPerms) &&
              is_array($this->userPerms) &&
              in_array($permId, $this->userPerms)) {
      return TRUE;
    } elseif ($this->isAdmin() &&
              $this->permissions()->exists($permId, Administration\Permission\Groups::SYSTEM)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Is in group
  *
  * @param integer|array $groupIds
  * @access public
  * @return boolean
  */
  function inGroup($groupIds) {
    if ($this->isValid && isset($this->user) && is_array($this->user)) {
      if (is_array($groupIds)) {
        if (in_array($this->user['group_id'], $groupIds)) {
          return TRUE;
        } elseif (isset($this->user['groups']) && is_array($this->user['groups'])) {
          return (count(array_intersect($groupIds, $this->user['groups'])) > 0);
        }
      } else {
        if ((int)$this->user['group_id'] == $groupIds) {
          return TRUE;
        } elseif (isset($this->user['groups']) &&
                  is_array($this->user['groups']) &&
                  in_array($groupIds, $this->user['groups'])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * is administrator
  *
  * @access public
  * @return boolean
  */
  function isAdmin() {
    return $this->inGroup(-1);
  }

  public function passwordApi($api = NULL) {
    if (isset($api)) {
      $this->_passwordApi = $api;
    } else {
      $this->_passwordApi = new base_auth_secure();
    }
    return $this->_passwordApi;
  }

  /**
  * Check login try
  *
  * @param string $username
  * @access public
  * @return boolean
  */
  function checkLoginTry($username) {
    $secureLoginObj = new base_auth_secure();
    $result = $secureLoginObj->checkLoginTry($username, 'admin');
    unset($secureLoginObj);
    return $result;
  }

  /**
  * return TRUE if user is valid (logged in) FALSE if not
  *
  * @return boolean
  */
  public function isLoggedIn() {
    return (boolean)$this->isValid;
  }

  /**
  * return user id
  *
  * @return string
  */
  public function getUserId() {
    return $this->userId;
  }

  /**
  * return user display name (fullname or handle)
  *
  * @return string
  */
  public function getDisplayName() {
    if (empty($this->user['fullname'])) {
      return $this->user['username'];

    } else {
      return $this->user['fullname'];
    }
  }

  public function permissions(Administration\Permissions $permissions = NULL) {
    if (isset($permissions)) {
      $this->_permissions = $permissions;
    } elseif (NULL === $this->_permissions) {
      $this->_permissions = new Administration\Permissions();
      $this->_permissions->papaya($this->papaya());
      $this->_permissions->activateLazyLoad();
    }
    return $this->_permissions;
  }
}
