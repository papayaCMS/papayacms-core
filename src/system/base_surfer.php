<?php
/**
* Base surfer (community user)
*
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
* @subpackage User-Community
* @version $Id: base_surfer.php 39732 2014-04-08 15:34:45Z weinert $
*/

/**
* Unknown surfer error
*/
define('SURFER_ERROR_UNKNOWN', 700);
/**
* Surfer password error
*/
define('SURFER_ERROR_PASSWORD', 701);
/**
* Surfer username error
*/
define('SURFER_ERROR_USERNAME', 702);
/**
* Surfer blocked error
*/
define('SURFER_ERROR_BLOCKED', 703);
/**
* Surfer permissions error
*/
define('SURFER_ERROR_PERMISSIONS', 704);

/**
* Online status
*/
if (!defined('SURFER_OFFLINE')) {
  define('SURFER_OFFLINE', 0);
}
if (!defined('SURFER_ONLINE')) {
  define('SURFER_ONLINE', 1);
}


/**
* Surfer object for load and check users
*
 * @property string $id
 * @property integer $groupId
 * @property boolean $isValid
 *
* @package Papaya
* @subpackage User-Community
*/
class base_surfer extends base_db {

  const LOGIN_NONE = 0;
  const LOGIN_DIALOG = 1;
  const LOGIN_COOKIE = 2;
  const LOGIN_APIKEY = 3;

  /**
  * Table name surfer
  * @var string $tableSurfers
  */
  var $tableSurfers = PAPAYA_DB_TBL_SURFER;

  /**
  * Table name group
  * @var string $tableSurferGroups
  */
  var $tableSurferGroups = PAPAYA_DB_TBL_SURFERGROUPS;

  /**
  * Table name rights
  * @var string $tableSurferPermissions
  */
  var $tableSurferPermissions = PAPAYA_DB_TBL_SURFERPERM;

  /**
  * Table name activity
  * @var string $tableSurferActivity
  */
  var $tableSurferActivity = PAPAYA_DB_TBL_SURFERACTIVITY;

  /**
  * Surfer data
  * @var array|null $surfer
  */
  var $surfer = NULL;

  /**
  * Link table name surfer<->right
  * @var string $tableSurferLinks
  */
  var $tableSurferLinks = PAPAYA_DB_TBL_SURFERPERMLINK;

  /**
  * Table name surfer change requests
  * @var string $tableChangeRequests
  */
  var $tableChangeRequests = PAPAYA_DB_TBL_SURFERCHANGEREQUESTS;

  /**
  * Table name topic
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;

  /**
  * Session variable name for surfer handle
  * @var string $surfernameVar
  */
  var $surfernameVar = 'papaya_surfer_handle';

  /**
  * Session variable name for surfer mail
  * @var string $surfermailVar
  */
  var $surfermailVar = 'papaya_surfer_email';

  /**
  * Session variable name for surfer id
  * @var string $surferidVar
  */
  var $surferidVar = 'papaya_surfer_id';

  /**
  * Session variable name for login method (email or handle)
  * @var string $loginVar
  */
  var $loginVar = 'papaya_login_method';

  /**
  * Session variable name for login mode (apikey, dialog, cookie)
  * @var string $loginModeVar
  */
  var $loginModeVar = 'papaya_login_mode';

  /**
  * Session variable name for topics that the surfer can see
  * @var string $topiclistVar
  */
  var $topiclistVar = 'surfer_topics';

  /**
  * Param name for login form fields
  * @var string $logformVar
  */
  var $logformVar = 'surf';

  /**
  * Visitor name
  * @var string $surferHandle
  */
  var $surferHandle = "";

  /**
   * @var string
   */
  public $surferEMail = '';

  /**
   * @var string
   */
  public $surferAvatar = '';

  /**
   * @var array
   */
  public $livePerms = array();

  /**
  * Valid surfer
  * which also ensures that $surferId and $surferHandle are not empty
  * @var boolean $isValid
  */
  private $_isValid = FALSE;

  /**
  * Surfer id
  * @var int $id
  */
  var $surferId = "";

  /**
  * Surfer acessible topics
  * @var array $topicList
  */
  var $topicList = FALSE;
  /**
  * Topic rights
  * @var array $topicPermissions
  */
  var $topicPermissions = array();
  /**
  * Temporary
  * @var array $tempTopicPermissions
  */
  var $tempTopicPermissions = array();

  /**
  * Errors
  * @var array $errors
  */
  var $errors = array();

  /**
  * Activity already stored
  * @var boolean $activitySaved
  */
  var $activitySaved = FALSE;

  /**
  * Current login mode
  * @var integer
  */
  private $_loginMode = FALSE;

  /**
  * Password API
  * @var base_auth_secure
  */
  private $_passwordApi = NULL;

  /**
  * Is this an automatic login after double opt-in?
  * @var boolean
  */
  private $_autoLogin = FALSE;

  /**
  * Optional page to redirect surfers to after automatic login
  * @var string
  */
  private $_autoLoginRedirect = '';

  /**
   * @var array
   */
  public $surferGroups = array();

  /**
  * Constructor base surfer
  *
  * @param boolean $login optional, default value NULL
  * @param string $surferMail optional, default value ''
  * @param boolean $requestPassword optional, default value FALSE
  * @access public
  */
  function __construct($login = NULL, $surferMail = '', $requestPassword = FALSE) {
    if (isset($this->papaya()->session)) {
      $loginBy = $this->getSessionValue($this->loginVar);
      if ($login === NULL) {
        if ((!isset($loginBy) || empty($loginBy)) &&
            defined('PAPAYA_COMMUNITY_AUTOLOGIN') && PAPAYA_COMMUNITY_AUTOLOGIN === FALSE) {
          $login = FALSE;
        } else {
          $login = TRUE;
        }
      }
      if ($login) {
        $this->executeLogin($requestPassword);
      } elseif (!empty($surferMail)) {
        $this->baseLink = $this->getBaseLink();
        $this->loadLogin($surferMail, FALSE, '', $requestPassword);
      }
      // Store surfer's activity and latest action if valid
      if ($this->_isValid) {
        $this->setStatusCookie(TRUE);
        $this->storeLastAction();
        $this->recordActivity();
      }
    }
  }

  /**
  * Execute the login
  *
  * @param boolean $requestPassword optional, default value FALSE
  * @param mixed $autoLogin optional, NULL for none, string surfer ID otherwise
  * @param string $redirectPage optional, default '' for no redirect on auto-login
  */
  public function executeLogin($requestPassword = FALSE, $autoLogin = NULL, $redirectPage = '') {
    $mail = '';
    $password = '';
    $redirection = '';
    $this->baseLink = $this->getBaseLink();
    // Is this a logout attempt?
    if (isset($_POST[$this->logformVar.'_logout']) &&
        $_POST[$this->logformVar.'_logout'] == 1) {
      $this->logout(
        empty($_POST[$this->logformVar.'_redirection'])
          ? '' : $_POST[$this->logformVar.'_redirection']
      );
    } elseif (isset($_GET[$this->logformVar.'_logout']) &&
        $_GET[$this->logformVar.'_logout'] == 1) {
      $this->logout(
        empty($_GET[$this->logformVar.'_redirection'])
          ? '' : $_GET[$this->logformVar.'_redirection']
      );
    } else {
      // Determine the current login method
      $userId = NULL;
      if ($autoLogin !== NULL) {
        $loginBy = 'id';
        $userId = $autoLogin;
        $this->setSessionValue($this->loginVar, 'id');
        $this->_autoLogin = TRUE;
        $this->_autoLoginRedirect = $redirectPage;
      } else {
        $loginBy = $this->getSessionValue($this->loginVar);
      }
      if (isset($loginBy) && trim($loginBy) != '') {
        $this->_loginMode = $this->getSessionValue($this->loginModeVar);
        if ($loginBy == 'id') {
          // Preferred method: Load login by id
          if ($userId === NULL) {
            $userId = $this->getSessionValue($this->surferidVar);
          }
          if (isset($userId) && trim($userId) != '') {
            $this->loadLoginBy('id', $userId, FALSE, '', $requestPassword);
          }
        } elseif ($loginBy == 'handle') {
          // Load login by handle
          $userHandle = $this->getSessionValue($this->surfernameVar);
          if (isset($userHandle) && trim($userHandle) != '') {
            $this->loadLoginBy('handle', $userHandle, FALSE, '', $requestPassword);
          }
        } else {
          // Load login by email
          $userMail = $this->getSessionValue($this->surfermailVar);
          if (isset($userMail) && trim($userMail) != '') {
            $this->loadLogin($userMail, FALSE, '', $requestPassword);
          }
        }
      }
      // If we don't have a valid surfer yet, try to log in
      if (!$this->_isValid) {
        // Is automatic re-login by cookie enabled?
        if (defined('PAPAYA_COMMUNITY_RELOGIN') && PAPAYA_COMMUNITY_RELOGIN != FALSE) {
          if (isset($_COOKIE['relogin']) && trim($_COOKIE['relogin'] != '')) {
            $token = $_COOKIE['relogin'];
            $this->loginByCookie($token);
            if ($this->_isValid) {
              $this->setStatusCookie(TRUE);
              return;
            }
          }
        }
        // Login by email, by handle, or by any of them, according to parameters
        if (isset($_POST[$this->logformVar.'_email']) ||
            isset($_POST[$this->logformVar]['email'])) {
          // Try login by email first
          if (isset($_POST[$this->logformVar.'_email'])) {
            $mail = $_POST[$this->logformVar.'_email'];
            $password = empty($_POST[$this->logformVar.'_password'])
              ? '' : $_POST[$this->logformVar.'_password'];
            $redirection = empty($_POST[$this->logformVar.'_redirection'])
              ? '' : $_POST[$this->logformVar.'_redirection'];
          } elseif (isset($_POST[$this->logformVar]['email'])) {
            $mail = $_POST[$this->logformVar]['email'];
            $password = empty($_POST[$this->logformVar]['password'])
              ? '' : $_POST[$this->logformVar]['password'];
            $redirection = empty($_POST[$this->logformVar]['redirection'])
              ? '' : $_POST[$this->logformVar]['redirection'];
          }
          if (isset($mail) && trim($mail) != '') {
            $this->setSessionValue($this->loginVar, 'id');
            // successful login will terminate script after login()
            $this->login($mail, $password, $redirection);
          }
        } elseif (isset($_POST[$this->logformVar.'_handle']) ||
                  isset($_POST[$this->logformVar]['handle'])) {
          // Now try login by handle
          if (isset($_POST[$this->logformVar.'_handle'])) {
            $handle = $_POST[$this->logformVar.'_handle'];
            $password = empty($_POST[$this->logformVar.'_password'])
              ? '' : $_POST[$this->logformVar.'_password'];
            $redirection = empty($_POST[$this->logformVar.'_redirection'])
              ? '' : $_POST[$this->logformVar.'_redirection'];
          } elseif (isset($_POST[$this->logformVar]['handle'])) {
            $handle = $_POST[$this->logformVar]['handle'];
            $password = empty($_POST[$this->logformVar]['password'])
              ? '' : $_POST[$this->logformVar]['password'];
            $redirection = empty($_POST[$this->logformVar]['redirection'])
              ? '' : $_POST[$this->logformVar]['redirection'];
          }
          if (isset($handle) && trim($handle) != '') {
            $this->setSessionValue($this->loginVar, 'id');
            // successful login will terminate script after loginByHandle()
            $this->loginByHandle($handle, $password, $redirection);
          }
        } elseif (isset($_POST[$this->logformVar.'_login']) ||
                  isset($_POST[$this->logformVar]['login'])) {
          if (isset($_POST[$this->logformVar.'_login'])) {
            $login = $_POST[$this->logformVar.'_login'];
            $password = empty($_POST[$this->logformVar.'_password'])
              ? '' : $_POST[$this->logformVar.'_password'];
            $redirection = empty($_POST[$this->logformVar.'_redirection'])
              ? '' : $_POST[$this->logformVar.'_redirection'];
          } elseif (isset($_POST[$this->logformVar]['login'])) {
            $login = $_POST[$this->logformVar]['login'];
            $password = empty($_POST[$this->logformVar]['password'])
              ? '' : $_POST[$this->logformVar]['password'];
            $redirection = empty($_POST[$this->logformVar]['redirection'])
              ? '' : $_POST[$this->logformVar]['redirection'];
          }
          if (isset($login) && trim($login) != '') {
            if (PapayaFilterFactory::isEmail($login)) {
              $this->setSessionValue($this->loginVar, 'id');
              // successful login will terminate script after login()
              $this->login($login, $password, $redirection);
            } else {
              $this->setSessionValue($this->loginVar, 'id');
              // successful login will terminate script after loginByHandle()
              $this->loginByHandle($login, $password, $redirection);
            }
          }
        } else {
          $this->loginByApiKey();
        }
      }
    }
  }

  /**
  * Starting to introduce some new dynamic properties that will be used in the refactored object.
  *
  * @param string $name;
  * @return mixed
  */
  public function __get($name) {
    $identifer = PapayaUtilStringIdentifier::toUnderscoreLower($name);
    switch ($identifer) {
    case 'id' :
      return $this->surferId;
    case 'group_id' :
      return $this->surfer['surfergroup_id'];
    case 'is_valid' :
      return $this->_isValid;
    }
    if (isset($this->$name) || property_exists($this, $name)) {
      if (is_array($this->$name)) {
        return (array)$this->$name;
      }
      return $this->$name;
    }
    return NULL;
  }

  /**
  * If the user is authenticated, make sure the cookie is set. If the user is anonym delete
  * the cookie if it exists.
  *
  * @param boolean $authenticated
  */
  public function setStatusCookie($authenticated) {
    if ($authenticated) {
      if (empty($_COOKIE['auth']) || $_COOKIE['auth'] != 'yes') {
        setcookie('auth', 'yes');
      }
    } else {
      if (!empty($_COOKIE['auth'])) {
        setcookie('auth', 'no', time() - 3600);
      }
    }
  }

  /**
  * Get instance of surfer object
  *
  * @param boolean $login optional, default value NULL
  * @access public
  * @return object surfer base_surfer
  */
  public static function getInstance($login = NULL) {
    static $surferObj = NULL;
    if (!(isset($surferObj) && is_object($surferObj))) {
      $surferObj = new base_surfer($login);
      $GLOBALS['PAPAYA_SURFER'] = $surferObj;
    }
    return $surferObj;
  }

  /**
  * Get list of change requests for current surfer
  *
  * @access public
  * @return mixed: array of change requests or NULL
  */
  function getChangeRequests() {
    $result = NULL;
    if ($this->_isValid) {
      $sql = "SELECT s.surferchangerequest_id, s.surferchangerequest_type,
                     s.surferchangerequest_data, s.surferchangerequest_time,
                     s.surferchangerequest_expiry
                FROM %s As s
               WHERE s.surferchangerequest_surferid='%s'
                 AND s.surferchangerequest_type != 'passwd'
               ORDER BY s.surferchangerequest_time DESC";
      $params = array($this->tableChangeRequests, $this->surferId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ($result == NULL) {
            $result = array();
          }
          $changeRequest = array(
            'id' => $row['surferchangerequest_id'],
            'type' => $row['surferchangerequest_type'],
            'data' => $row['surferchangerequest_data'],
            'time' => $row['surferchangerequest_time'],
            'expiry' => $row['surferchangerequest_expiry']);
          $result[] = $changeRequest;
        }
      }
    }
    return $result;
  }

  /**
  * Load login by...
  *
  * @param string $field ('email', 'handle', 'id', 'relogin')
  * @param string $value
  * @param boolean $login optional, default value FALSE
  * @param string $password optional, default value ""
  * @param boolean $requestPassword optional, default value FALSE
  * @param boolean $useApiKey optional, default value FALSE
  * @return boolean
  */
  function loadLoginBy(
    $field, $value, $login = FALSE, $password = '', $requestPassword = FALSE, $useApiKey = FALSE
  ) {
    // Return FALSE if field is none of the legal ones
    if (!in_array($field, array('email', 'handle', 'id', 'relogin'))) {
      return FALSE;
    }
    // For legal fields, prepend 'surfer_'
    $field = 'surfer_'.$field;
    $this->_isValid = FALSE;
    $this->errors = array();
    if (trim($field) != '') {
      $sql = "SELECT  s.surfer_id, s.surfergroup_id,
                      s.surfer_handle, s.surfer_password,
                      s.surfer_givenname, s.surfer_surname,
                      s.surfer_gender, s.surfer_status,
                      s.surfer_email, s.surfer_avatar,
                      s.surfer_valid, sg.surfergroup_title,
                      sg.surfergroup_redirect_page,
                      sg.surfergroup_profile_page,
                      s.surfer_lastaction, s.surfer_relogin,
                      s.surfer_reloginby,  s.surfer_apikey,
                      s.surfer_registration, s.surfer_lastlogin,
                      s.surfer_language
                 FROM %s s
                 LEFT OUTER JOIN %s sg ON sg.surfergroup_id = s.surfergroup_id
                WHERE s.%s = '%s'";
      if ($field == 'surfer_relogin') {
        $cryptToken = $this->getCookieHash($value);
        $sqlParams = array($this->tableSurfers, $this->tableSurferGroups, $field, $cryptToken);
      } else {
        $sqlParams = array(
          $this->tableSurfers,
          $this->tableSurferGroups,
          $field,
          strtolower($value)
        );
      }
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $loginPossible = $row['surfer_valid'] == 1;
          $loginValid = FALSE;
          $this->_loginMode = self::LOGIN_NONE;
          if ($useApiKey) {
            $this->_loginMode = self::LOGIN_APIKEY;
            $loginValid = $this->passwordApi()->verifyPassword(
              $password, $row['surfer_apikey']
            );
            if ($loginValid) {
              $passwordUpdate = $this->passwordApi()->rehashPassword(
                $password, $row['surfer_apikey']
              );
              if ($passwordUpdate) {
                $this->databaseUpdateRecord(
                  $this->tableSurfers,
                  array('surfer_apikey' => $passwordUpdate),
                  array('surfer_id' => $row['surfer_id'])
                );
              }
            }
          } elseif ($field == 'surfer_relogin') {
            $this->_loginMode = self::LOGIN_COOKIE;
            $loginValid = TRUE;
          } elseif ($login &&
                    $this->passwordApi()->verifyPassword($password, $row['surfer_password'])) {
            $this->_loginMode = self::LOGIN_DIALOG;
            $loginValid = TRUE;
            $passwordUpdate = $this->passwordApi()->rehashPassword(
              $password, $row['surfer_password']
            );
            if ($passwordUpdate) {
              $this->databaseUpdateRecord(
                $this->tableSurfers,
                array('surfer_password' => $passwordUpdate),
                array('surfer_id' => $row['surfer_id'])
              );
            }
          }
          if (!$login || ($loginPossible && $loginValid)) {
            $this->surfer = $row;
            $this->surferId = $row['surfer_id'];
            $this->surferHandle = $row['surfer_handle'];
            $this->surferEMail = $row['surfer_email'];
            $this->surferAvatar = $row['surfer_avatar'];
            $this->_isValid = ($row['surfer_valid'] == 1);
            $this->loadTopicIdList(FALSE, TRUE);
            if ($login || $this->_autoLogin) {
              // Check whether there is a status change
              if ($this->surfer['surfer_status'] == SURFER_OFFLINE) {
                // If yes, set surfer to online
                $this->surfer['surfer_status'] = SURFER_ONLINE;
                $this->setStatus(SURFER_ONLINE);
              }
              $this->setSessionValue($this->surfernameVar, $this->surferHandle);
              $this->setSessionValue($this->surfermailVar, $this->surferEMail);
              $this->setSessionValue($this->surferidVar, $this->surferId);
              $this->setSessionValue($this->loginModeVar, $this->_loginMode);
              if ($field == 'surfer_relogin' &&
                  in_array($row['surfer_reloginby'], array('id', 'mail', 'handle'))) {
                $this->setSessionValue($this->loginVar, $row['surfer_reloginby']);
              }
              // Set cookie and database record for relogin if appropriate
              if (defined('PAPAYA_COMMUNITY_RELOGIN') && PAPAYA_COMMUNITY_RELOGIN != FALSE) {
                $this->setReloginCookie($_SESSION[$this->loginVar]);
              }
            }
            if ($this->_autoLogin && $this->_isValid && $this->_autoLoginRedirect != '') {
              @header("Location: ".$this->_autoLoginRedirect);
              printf(
                '<html><head><meta http-equiv="refresh" content="0; URL=%s"></head></html>',
                papaya_strings::escapeHTMLChars($this->_autoLoginRedirect)
              );
              exit;
            }
          } elseif ($loginPossible) {
            if ($requestPassword === FALSE) {
              $this->errors[SURFER_ERROR_PASSWORD] = 'Invalid password';
            }
          } else {
            $this->errors[SURFER_ERROR_BLOCKED] = 'Account blocked';
          }
        } else {
          if ($requestPassword === FALSE) {
            $this->errors[SURFER_ERROR_USERNAME] = 'Invalid username';
          }
        }
      } else {
        $this->errors[SURFER_ERROR_USERNAME] = 'Invalid username';
      }
    }
    return $this->_isValid;
  }

  /**
  * default load login method
  *
  * @param string $surferEMail
  * @param boolean $login
  * @param string $password
  * @param boolean $requestPassword
  * @return boolean
  */
  function loadLogin($surferEMail, $login = FALSE, $password = '', $requestPassword = FALSE) {
    /*
    * If your login doesn't work, check whether PAPAYA_SESSION_START is ON!
    */
    return $this->loadLoginBy('email', $surferEMail, $login, $password, $requestPassword);
  }

  /**
  * Load function
  *
  * loads surfer by id or handle
  *
  * @access public
  *
  * @param string $surferId
  * @param string $surferHandle optional
  * @return boolean
  */
  function load($surferId, $surferHandle = NULL) {
    // Exit if no or invalid data is given
    if (!((isset($surferId) || isset($surferHandle))) ||
        ($surferId == NULL && $surferHandle == NULL)) {
      return FALSE;
    }

    // Call loadLoginBy() depending on paramaters
    if ($surferId != NULL) {
      return $this->loadLoginBy('id', $surferId);
    } else {
      return $this->loadLoginBy('handle', $surferHandle);
    }
  }

  /**
  * Load By Handle function
  *
  * loads surfer by handle
  *
  * @access public
  *
  * @param string $surferHandle
  * @return boolean
  */
  function loadByHandle($surferHandle) {
    return $this->load(NULL, $surferHandle);
  }

  /**
   * Redirect on successful login
   *
   * @param string $redirectionUrl
   */
  function redirectOnLogin($redirectionUrl) {
    $application = $this->papaya();
    $request = $application->getObject('Request');
    $queryString = $request->getParameter(
      $this->logformVar.'_query_string',
      $request->getParameter(
        $this->logformVar.'[query_string]',
        '',
        NULL,
        PapayaRequest::SOURCE_BODY
      ),
      NULL,
      PapayaRequest::SOURCE_BODY
    );
    $defaultHost = strtolower(PAPAYA_DEFAULT_HOST);
    if (!$redirectionUrl) {
      if ($this->surfer['surfergroup_redirect_page'] > 0) {
        $redirectionUrl = $this->getAbsoluteUrl(
          $this->getWebLink($this->surfer['surfergroup_redirect_page'])
        );
      } elseif (defined('PAPAYA_COMMUNITY_REDIRECT_PAGE') &&
                PAPAYA_COMMUNITY_REDIRECT_PAGE > 0) {
        $redirectionUrl = $this->getAbsoluteUrl(
          $this->getWebLink(PAPAYA_COMMUNITY_REDIRECT_PAGE)
        );
      } else {
        $redirectionUrl = $this->papaya()->request->getUrl()->getPathUrl();
      }
    }
    $newQueryString = $this->recodeQueryString($queryString);
    if (!preg_match('(^\?)', $newQueryString)) {
      $redirectionUrl .= '?';
    }
    $redirectionUrl .= $newQueryString;
    $targetUrl = NULL;
    if ($this->validateRedirectHost($redirectionUrl, $defaultHost)) {
      $targetUrl = $redirectionUrl;
    }
    /**
     * @var PapayaSession $session
     */
    $session = $this->papaya()->session;
    if ($redirect = $session->regenerateId($targetUrl)) {
      $redirect->send();
      $redirect->end();
    }
  }

  /**
  * Validate the redirection URL
  *
  * Hostname can be the default host or one of the configured domains
  *
  * @param string $redirectionUrl
  * @param string $defaultHost
  * @return boolean
  */
  public function validateRedirectHost($redirectionUrl, $defaultHost) {
    $redirectionString = strtolower($redirectionUrl);
    if (FALSE !== strpos($redirectionString, "\n") &&
        FALSE !== strpos($redirectionString, "\r")) {
      return FALSE;
    }
    if (0 === strpos($redirectionString, 'http://'.$defaultHost) ||
        0 === strpos($redirectionString, 'https://'.$defaultHost)) {
      return TRUE;
    }
    $domains = new base_domains();
    $url = new PapayaUrl($redirectionUrl);
    if ($domains->load($url->host, 0)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Login attempt
  *
  * @param string $surferEMail
  * @param string $password
  * @param string $redirectionURL optional url to redirect to after login
  * @param boolean $requestPassword optional, default value FALSE
  * @return boolean login valid?
  */
  function login($surferEMail, $password, $redirectionURL = NULL, $requestPassword = FALSE) {
    if ($this->checkLoginTry($surferEMail)) {
      $this->setSessionValue($this->topiclistVar, NULL);
      if (!$this->getSessionValue($this->loginVar)) {
        $this->setSessionValue($this->loginVar, 'id');
      }
      $this->loadLogin($surferEMail, TRUE, $password, $requestPassword);
      if ($this->_isValid) {
        $this->logLoginTime();
        $this->redirectOnLogin($redirectionURL);
      }
    }
  }

  /**
  * Login by handle
  *
  * @param string $surferHandle
  * @param string $password
  * @param string $redirectionURL optional url to redirect to after login
  * @param boolean $requestPassword optional, default value FALSE
  * @return boolean login valid?
  */
  function loginByHandle(
    $surferHandle, $password, $redirectionURL = NULL, $requestPassword = FALSE
  ) {
    if ($this->checkLoginTry($surferHandle)) {
      $this->setSessionValue($this->topiclistVar, NULL);
      if (!$this->getSessionValue($this->loginVar)) {
        $this->setSessionValue($this->loginVar, 'id');
      }
      $this->loadLoginBy('handle', $surferHandle, TRUE, $password, $requestPassword);
      if ($this->_isValid) {
        $this->logLoginTime();
        $this->redirectOnLogin($redirectionURL);
      }
    }
  }

  /**
  * Login by API key
  */
  function loginByApiKey() {
    $credentialString = '';
    if (isset($_SERVER['HTTP_X_PAPAYA_API_LOGIN'])) {
      $credentialString = $_SERVER['HTTP_X_PAPAYA_API_LOGIN'];
    }
    if (empty($credentialString)) {
      if ($this->papaya()->request->method == 'post') {
        $source = PapayaRequest::SOURCE_QUERY | PapayaRequest::SOURCE_BODY;
      } else {
        $source = PapayaRequest::SOURCE_QUERY;
      }
      $credentialString = $this->papaya()->request->getParameter('api_login', '', NULL, $source);
    }
    if (!empty($credentialString)) {
      $pattern = '(^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{4}|[A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{2}==)$)';
      if (preg_match($pattern, $credentialString)) {
        $credentialString = base64_decode($credentialString);
      }
      $credentials = explode(':', rawurldecode($credentialString));
      if (count($credentials) == 2) {
        $this->setSessionValue($this->topiclistVar, NULL);
        $apiLogin = defined('PAPAYA_COMMUNITY_API_LOGIN') ? PAPAYA_COMMUNITY_API_LOGIN : 0;
        switch ($apiLogin) {
        case 0:
          $this->loadLoginBy('handle', $credentials[0], TRUE, $credentials[1], FALSE, TRUE);
          break;
        case 1:
          $this->loadLoginBy('email', $credentials[0], TRUE, $credentials[1], FALSE, TRUE);
          break;
        case 2:
          if (PapayaFilterFactory::isEmail($credentials[0])) {
            $this->loadLoginBy('email', $credentials[0], TRUE, $credentials[1], FALSE, TRUE);
          } else {
            $this->loadLoginBy('handle', $credentials[0], TRUE, $credentials[1], FALSE, TRUE);
          }
        }
        if ($this->_isValid) {
          $this->setSessionValue($this->loginVar, 'id');
          $this->logLoginTime();
          $this->redirectOnLogin(NULL);
        }
      }
    }
  }

  /**
  * Re-login by cookie
  *
  * @param string $token
  * @return boolean login valid?
  */
  function loginByCookie($token) {
    $this->setSessionValue($this->topiclistVar, NULL);
    $this->loadLoginBy('relogin', $token, TRUE);
  }

  /**
  * Set relogin cookie
  *
  * @param $reloginBy string
  */
  function setReloginCookie($reloginBy) {
    // Only set the cookie if we've got either a user option to do so or an existing cookie
    if ((
         isset($_POST[$this->logformVar.'_relogin']) &&
         $_POST[$this->logformVar.'_relogin'] == 1
        ) ||
        (
         isset($_POST[$this->logformVar]['relogin']) &&
         $_POST[$this->logformVar]['relogin'] == 1
        ) ||
        (
         isset($_COOKIE['relogin']) &&
         trim($_COOKIE['relogin'] != '')
        )) {
      // Determine the field on which to check the surfer
      if (!in_array($reloginBy, array('id', 'mail', 'handle'))) {
        $reloginBy = 'mail';
      }
      // Create the cookie value
      srand((double)microtime() * 1000000);
      $token = md5(uniqid(rand()));
      // Store the hashed cookie and the relogin method in the database
      $data = array(
        'surfer_relogin' => $this->getCookieHash($token),
        'surfer_reloginby' => $reloginBy
      );
      $this->databaseUpdateRecord($this->tableSurfers, $data, 'surfer_id', $this->surferId);
      // Set the cookie itself
      if (defined('PAPAYA_COMMUNITY_RELOGIN_EXP_DAYS')) {
        $days = PAPAYA_COMMUNITY_RELOGIN_EXP_DAYS;
      } else {
        $days = 7;
      }
      setcookie('relogin', $token, time() + $days * 86400);
    }
  }

  /**
  * Set surfer status
  *
  * @param integer $status optional
  * @param string  $mail   optional
  */
  function setStatus($status = SURFER_OFFLINE, $mail = '') {
    if ($mail) {
      // Use email address for logout
      $compareField = 'surfer_email';
      $compareValue = $mail;
    } elseif (isset($this->surferId)) {
      // Use surfer id for login
      $compareField = 'surfer_id';
      $compareValue = $this->surferId;
    } else {
      // No surfer identified: Nothing to do
      return;
    }
    $data = array('surfer_status' => $status);
    $this->databaseUpdateRecord(
      $this->tableSurfers, $data, $compareField, $compareValue
    );
  }

  /**
   * Load list of all accessible sites
   *
   * @param boolean $default optional, default value FALSE
   * @param bool $forceLoading
   * @access public
   */
  function loadTopicIdList($default = FALSE, $forceLoading = FALSE) {
    if ((!$default) && isset($this->surferId) && ($this->surferId != '')) {
      $surferId = $this->surferId;
    } else {
      $surferId = '';
    }
    $currentTime = time();
    if (!is_array($this->topicList)) {
      if (!$forceLoading && isset($GLOBALS['PAPAYA_PAGE']) && $GLOBALS['PAPAYA_PAGE']->public) {
        $topicListStatus = $this->getSessionValue($this->topiclistVar.'_status');
        if (isset($topicListStatus['surfer_id']) &&
            $topicListStatus['surfer_id'] == $surferId &&
            isset($topicListStatus['cache_time']) &&
            $topicListStatus['cache_time'] > 0) {
          $sql = "SELECT MAX(topic_modified)
                    FROM %s";
          if ($res = $this->databaseQueryFmt($sql, $this->tableTopics)) {
            if ($topicListStatus['cache_time'] >= $res->fetchField()) {
              $this->topicList = $this->getSessionValue($this->topiclistVar);
            }
          }
        }
      }
    }
    if ($forceLoading || !is_array($this->topicList)) {
      $this->topicPermissions = array();
      $this->tempTopicPermissions = array();
      $this->livePerms = array();
      if ($surferId != '') {
        $sql = "SELECT l.surfer_permid
                  FROM %s l, %s s, %s p
                 WHERE l.surfergroup_id = s.surfergroup_id
                   AND s.surfer_id = '%s'
                   AND l.surfer_permid = p.surferperm_id
                   AND p.surferperm_active > 0";
        $params = array($this->tableSurferLinks, $this->tableSurfers,
          $this->tableSurferPermissions, $this->surferId);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $this->livePerms[$row['surfer_permid']] = $row['surfer_permid'];
          }
        }
      }
      $sql = "SELECT topic_id, surfer_useparent, surfer_permids, prev
                FROM %s";
      if ($res = $this->databaseQueryFmt($sql, $this->tableTopics)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ($row['surfer_useparent'] == 1) {
            $this->topicPermissions[$row['topic_id']] =
              $this->permStr2Array($row['surfer_permids']);
          } else {
            $this->tempTopicPermissions[$row['topic_id']] = $row;
          }
        }
      }
      //get rights for nodes
      if (isset($this->tempTopicPermissions) &&
          is_array($this->tempTopicPermissions)) {
        foreach ($this->tempTopicPermissions as $key => $val) {
          if (!isset($this->topicPermissions[$key])) {
            $this->topicPermissions[$key] = $this->getNodeRights($key);
          }
        }
      }
      $this->tempTopicPermissions = array();
      //check rights
      if (isset($this->topicPermissions) && is_array($this->topicPermissions)) {
        foreach ($this->topicPermissions as $id => $rights) {
          if ($rights) {
            if (count(array_intersect($rights, $this->livePerms))) {
              $this->topicList[$id] = TRUE;
            }
          } else {
            $this->topicList[$id] = TRUE;
          }
        }
      }
      $this->topicPermissions = array();
    }
    $topicListStatus = array(
      'surfer_id' => $surferId,
      'cache_time' => $currentTime,
    );
    $this->setSessionValue($this->topiclistVar.'_status', $topicListStatus);
    $this->setSessionValue($this->topiclistVar, $this->topicList);
  }

  /**
  * Load permission list
  *
  * @param boolean $forceReload optional, default value FALSE
  * @access public
  * @return boolean
  */
  function loadPermissionList($forceReload = FALSE) {
    if ($forceReload && isset($this->permissions)) {
      unset($this->permissions);
    }
    if (!isset($this->permissions)) {
      $this->permissions = array();
      $sql = "SELECT surferperm_id, surferperm_title, surferperm_active
                FROM %s
            ORDER BY surferperm_title, surferperm_id";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableSurferPermissions))) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->permissions[$row['surferperm_id']] = $row;
        }
        return TRUE;
      }
    } else {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load SurferGroup list
  *
  * @param boolean $forceReload optional, default value FALSE
  * @access public
  * @return boolean
  */
  function loadSurferGroupsList($forceReload = FALSE) {
    if (empty($this->surferGroups) || $forceReload) {
      $this->surferGroups = array();
      $sql = "SELECT surfergroup_id, surfergroup_title
                FROM %s
            ORDER BY surfergroup_title";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableSurferGroups))) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->surferGroups[$row['surfergroup_id']] = $row;
        }
        return TRUE;
      }
    } else {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Check user by data
  *
  * @param string $username
  * @param string $userEmail
  * @param mixed $requestBy optional
  * @access public
  * @return mixed
  */
  function checkUserByData($username, $userEmail, $requestBy = 'email') {
    // If both username and email address are needed but only one of them is provided,
    // we can return false right now
    if (($requestBy == 'both' || $requestBy === TRUE)
        && (trim($username) == '' || trim($userEmail) == '')) {
      return FALSE;
    }
    // The same applies if we need an email address and don't have one,
    // or a handle
    if (($requestBy == 'email' && trim($userEmail) == '')
        || ($requestBy == 'handle' && trim($username) == '')) {
      return FALSE;
    }
    // Finally, we also need to deny access if we do not have any data
    if (trim($userEmail) == '' && trim($username) == '') {
      return FALSE;
    }
    // Build the query based on which of the fields are set
    // and how they are to be combined
    $sql = "SELECT surfer_id, surfer_handle, surfer_email
              FROM %s";
    if ($requestBy == 'both' || $requestBy === TRUE) {
      $sql .= " WHERE surfer_handle = '%s' AND surfer_email = '%s'";
      $sqlParams = array($this->tableSurfers, $username, $userEmail);
    } elseif ($requestBy == 'handle') {
      $sql .= " WHERE surfer_handle = '%s'";
      $sqlParams = array($this->tableSurfers, $username);
    } elseif ($requestBy == 'email') {
      $sql .= " WHERE surfer_email = '%s'";
      $sqlParams = array($this->tableSurfers, $userEmail);
    } else {
      if (trim($username) != '' && trim($userEmail) != '') {
        $sql .= " WHERE surfer_handle = '%s' OR surfer_email = '%s'";
        $sqlParams = array($this->tableSurfers, $username, $userEmail);
      } elseif (trim($username) != '') {
        $sql .= " WHERE surfer_handle = '%s'";
        $sqlParams = array($this->tableSurfers, $username);
      } else {
        $sql .= " WHERE surfer_email = '%s'";
        $sqlParams = array($this->tableSurfers, $userEmail);
      }
    }
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        return $row['surfer_id'];
      }
    }
    return FALSE;
  }

  /**
   * Request password change
   *
   * @access public
   * @param array $maildata optional
   * @param mixed $requestBy optional
   * @param null $page
   * @return boolean
   */
  function requestPasswordChange($maildata = NULL, $requestBy = 'email', $page = NULL) {
    if (isset($maildata)) {
      $mail_from = $maildata['Password_Mail_From'];
      $mail_subject = $maildata['Password_Mail_Subject'];
      $mail_body = $maildata['Password_Mail_Body'];
    } else {
      $mail_from = NULL;
      $mail_subject = NULL;
      $mail_body = NULL;
    }
    srand((double)microtime() * 1000000);
    $chgId = md5(uniqid(rand()));
    $username = empty($_POST[$this->logformVar]['username'])
      ? '' : trim($_POST[$this->logformVar]['username']);
    $userEmail = empty($_POST[$this->logformVar]['email'])
      ? '' : trim($_POST[$this->logformVar]['email']);
    if ($userId = $this->checkUserByData($username, $userEmail, $requestBy)) {
      if ($this->saveChangePasswordId($userId, $chgId)) {
        $aSurfer = new base_surfer(FALSE, '', TRUE);
        if ((!empty($userEmail) && $aSurfer->loadLogin($userEmail, FALSE, '', TRUE)) ||
            ($requestBy != 'email' && $aSurfer->loadByHandle($username))) {
          $project = $this->papaya()->options['PAPAYA_PROJECT_TITLE'];
          $mail_to = $aSurfer->surfer['surfer_givenname'].''.
            $aSurfer->surfer['surfer_surname'].' <'.$aSurfer->surfer['surfer_email'].'>';
          if (isset($mail_subject) && trim($mail_subject) != '') {
            $subject = $mail_subject;
          } else {
            $subject = papaya_strings::escapeHTMLChars($project).' - '.
              'Password forgotten';
          }
          if (isset($mail_body) && trim($mail_body) != '') {
            $msg = $mail_body."\n\n";
          } else {
            $msg = "You get this email because a password change was requested for ".
              $project.". ";
            $msg .= "If you do not want to change your password, please ignore this email.\n\n";
            $msg .= "To change your password please click the link:\n\n";
          }
          if ($page == NULL || !is_numeric($page)) {
            $href = $this->getAbsoluteURL($this->baseLink).'?'.$this->logformVar.'_chg='.$chgId;
          } else {
            $href = $this->getAbsoluteURL(
              $this->getWebLink(
                $page,
                NULL,
                NULL,
                array($this->logformVar.'_chg' => $chgId)
              )
            );
          }
          $email = new email();
          $email->addAddress($mail_to);
          if (isset($mail_from) && trim($mail_from) != '') {
            $email->setSender($mail_from);
          }
          $email->setSubject($subject);
          $fillValues = array(
            'NAME' => $username,
            'LINK' => $href
          );

          $email->setBody($msg, $fillValues, 70);
          $success = $email->send();
          if ($success) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Get password request form
   *
   * @access public
   * @param string $resetBy
   * @return string
   */
  function getPasswordRequestForm($resetBy = 'email') {
    $this->baseLink = $this->getBaseLink();
    $result = sprintf(
      '<passwordrequest action="%s" name="%s">',
      papaya_strings::escapeHTMLChars($this->getBaselink()),
      papaya_strings::escapeHTMLChars($this->logformVar)
    );
    $result .= '<element type="hidden" name="newpwd" value="2"/>';
    $result .= sprintf(
      '<element type="hidden" name="reset_by" value="%s"/>',
      papaya_strings::escapeHTMLChars($resetBy)
    );
    if ($resetBy == 'handle' || $resetBy == 'both') {
      $result .= sprintf(
        '<element type="text" name="username" value="%s"/>',
        empty($_POST[$this->logformVar]['username'])
          ? '' : papaya_strings::escapeHTMLChars($_POST[$this->logformVar]['username'])
      );
    }
    if ($resetBy == 'email' || $resetBy == 'both') {
      $result .= sprintf(
        '<element type="text" name="email" value="%s"/>',
        empty($_POST[$this->logformVar]['email'])
          ? '' : papaya_strings::escapeHTMLChars($_POST[$this->logformVar]['email'])
      );
    }
    $result .= '</passwordrequest>';
    return $result;
  }

  /**
   * Save change password id
   *
   * @param string $userId
   * @param integer $chgId
   * @access public
   * @return boolean
   */
  function saveChangePasswordId($userId, $chgId) {
    // look up surfer id by email
    $now = time();
    $validUntil = $now + 86400;
    $data = array(
      'surferchangerequest_surferid' => $userId,
      'surferchangerequest_type' => 'passwd',
      'surferchangerequest_token' => $chgId,
      'surferchangerequest_time' => $now,
      'surferchangerequest_expiry' => $validUntil
    );
    return $this->databaseInsertRecord(
      $this->tableChangeRequests, 'surferchangerequest_id', $data
    );
  }

  /**
  * update last valid login tim  in database
  *
  * @return boolean
  */
  function logLoginTime() {
    if ($this->_isValid) {
      $data = array('surfer_lastlogin' => time());
      return FALSE !== $this->databaseUpdateRecord(
        $this->tableSurfers, $data, 'surfer_id', $this->surferId
      );
    }
    return FALSE;
  }

  /**
  * Check change password id
  *
  * @param integer $chgId
  * @access public
  * @return mixed
  */
  function checkChangePasswordId($chgId) {
    if (preg_match('/^[a-fA-F\d]{32}$/', $chgId)) {
      $sql = "SELECT s.surfer_id, s.surfer_email
                FROM %s s, %s sc
               WHERE s.surfer_id = sc.surferchangerequest_surferid
                 AND sc.surferchangerequest_token = '%s'
                 AND sc.surferchangerequest_expiry >= %d";
      $params = array(
        $this->tableSurfers,
        $this->tableChangeRequests,
        $chgId,
        time()
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          return $row;
        }
      }
    }
    return FALSE;
  }

  /**
  * Get node rights
  *
  * @param integer $id
  * @access public
  * @return mixed array or boolean
  */
  function getNodeRights($id) {
    if (!isset($this->topicPermissions[$id])) {
      if (isset($this->tempTopicPermissions[$id])) {
        $row = $this->tempTopicPermissions[$id];
      } else {
        $row = NULL;
      }
      if (isset($row) && is_array($row)) {
        if (isset($row['PERMS'])) {
          return $row['PERMS'];
        }
        $rights = $this->getNodeRights($row['prev']);
        if ($row['surfer_useparent'] == 3) {
          $addRights = $this->permStr2Array($row['surfer_permids']);
        } else {
          $addRights = FALSE;
        }
        if (isset($rights) && is_array($rights) && is_array($addRights)) {
          $result = array_merge($rights, $addRights);
        } elseif (isset($rights) && is_array($rights)) {
          $result = $rights;
        } elseif (isset($addRights) && is_array($addRights)) {
          $result = $addRights;
        } else {
          $result = FALSE;
        }
        $this->tempTopicPermissions[$id]['PERMS'] = $result;
        return $result;
      } else {
        return FALSE;
      }
    } else {
      return $this->topicPermissions[$id];
    }
  }

  /**
  * Permission string to array
  *
  * @param string $permString
  * @access public
  * @return mixed
  */
  function permStr2Array($permString) {
    if (preg_match_all('#\d+#', $permString, $matches, PREG_PATTERN_ORDER)) {
      return $matches[0];
    } else {
      return FALSE;
    }
  }

  /**
   * Test if surfer can enter a given topic
   *
   * @access public
   * @param integer $topicId
   * @return bool
   */
  function canView($topicId) {
    $this->loadTopicIdList();
    return (isset($this->topicList[$topicId]) && $this->topicList[$topicId]);
  }


  /**
  * Get viewable
  *
  * @access public
  * @return array
  */
  function getViewable() {
    $this->loadTopicIdList();
    return array_keys($this->topicList);
  }

  /**
   * Surfer logout, clean up variable, redirection is optional
   * Also unset the relogin cookie, if necessary
   *
   * @param string $redirectionURL optional URL to redirect to after logout
   * @access public
   */
  function logout($redirectionURL = NULL) {
    // Figure out surfer id if email is stored in session
    $mail = $this->getSessionValue($this->surfermailVar);
    if ($mail) {
      $this->setStatus(SURFER_OFFLINE, $mail);
    }
    $this->setSessionValue($this->loginVar, NULL);
    $this->setSessionValue($this->surfernameVar, NULL);
    $this->setSessionValue($this->surfermailVar, NULL);
    $this->setSessionValue($this->surferidVar, NULL);
    $this->setSessionValue($this->topiclistVar, NULL);
    unset($this->surferId);
    unset($this->surferHandle);
    unset($this->surferEMail);
    $this->surfer = NULL;
    $this->_isValid = FALSE;
    $this->loadTopicIdList(FALSE, TRUE);
    if (defined('PAPAYA_COMMUNITY_RELOGIN') && PAPAYA_COMMUNITY_RELOGIN != FALSE) {
      setcookie('relogin', '', time() - 86400);
    }
    $this->setStatusCookie(FALSE);
    if ($redirectionURL) {
      if (isset($GLOBALS['PAPAYA_PAGE']) && is_object($GLOBALS['PAPAYA_PAGE'])) {
        /** @var papaya_page $page */
        $page = $GLOBALS['PAPAYA_PAGE'];
        $page->logRequest();
        if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
          header('X-Papaya-Redirect-Note: surfer logout');
        }
        $page->protectedRedirect(302, $redirectionURL);
      } else {
        if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
          header('X-Papaya-Status: surfer logout with redirect but no PAPAYA_PAGE object');
        }
      }
    }
  }

  /**
  * Get form XML
  *
  * @param boolean $login optional, default value TRUE
  * @param string $redirectionURL optional url to redirect after login / logout
  * @param string $emailTitle optional caption for the email field
  * @param string $passwordTitle optional caption for the password field
  * @param string $reloginTitle optional caption for the relogin checkbox
  * @param array $errors optional array of error messages
  * @access public
  * @return string
  */
  function getFormXML(
    $login = TRUE,
    $redirectionURL = NULL,
    $emailTitle = 'E-Mail',
    $passwordTitle = 'Password',
    $reloginTitle = 'Stay logged-in',
    $errors = array()
  ) {
    if ($login) {
      // If there was a delegated error from the login box, add it here
      if ((
           isset($_GET[$this->logformVar]['error']) &&
           $_GET[$this->logformVar]['error'] == 1
          ) ||
          (
           defined('PAPAYA_URL_LEVEL_SEPARATOR') &&
           PAPAYA_URL_LEVEL_SEPARATOR != '' &&
           isset($_GET[$this->logformVar.PAPAYA_URL_LEVEL_SEPARATOR.'error']) &&
           $_GET[$this->logformVar.PAPAYA_URL_LEVEL_SEPARATOR.'error'] == 1
          )
         ) {
        if (isset($errors['error_email'])) {
          $this->errors[SURFER_ERROR_USERNAME] = $errors['error_email'];
        } else {
          $this->errors[SURFER_ERROR_USERNAME] = 'Invalid email/password';
        }
      }
      // Permission error?
      if ($this->_isValid) {
        if (isset($errors['error_permissions'])) {
          $this->errors[SURFER_ERROR_PERMISSIONS] = $errors['error_permissions'];
        } else {
          $this->errors[SURFER_ERROR_PERMISSIONS] = 'Invalid Permissions';
        }
      }
      $this->baseLink = $this->getBaseLink();
      $userEmail = empty($_POST[$this->logformVar.'_email'])
        ? '' : $_POST[$this->logformVar.'_email'];
      $changeHref = $this->getLink(array('newpwd' => 1), $this->logformVar);
      $return = sprintf(
        '<login name="%s" action="%s" title="%s" chglink="%s">',
        papaya_strings::escapeHTMLChars($this->logformVar),
        papaya_strings::escapeHTMLChars($this->baseLink),
        'Please fill in your data.',
        papaya_strings::escapeHTMLChars($changeHref)
      );
      if (!empty($redirectionURL)) {
        $return .= sprintf(
          '<element type="hidden" name="redirection" value="%s"/>',
          papaya_strings::escapeHTMLChars($redirectionURL)
        );
      }
      $serverQueryString = '';
      if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
        $serverQueryString = $_SERVER['QUERY_STRING'];
      }
      $application = $this->papaya();
      $request = $application->getObject('Request');
      $queryString = $request->getParameter(
        $this->logformVar.'_query_string',
        $request->getParameter(
          isset($this->logformVar['query_string']) ? $this->logformVar['query_string'] : NULL,
          $serverQueryString,
          NULL,
          PapayaRequest::SOURCE_BODY
        ),
        NULL,
        PapayaRequest::SOURCE_BODY
      );
      if ($queryString != '') {
        $return .= sprintf(
          '<element type="hidden" name="query_string" value="%s"/>',
          papaya_strings::escapeHTMLChars($queryString)
        );
      }
      $return .= sprintf(
        '<element type="text" title="%s" name="email" value="%s"/>',
        papaya_strings::escapeHTMLChars($emailTitle),
        papaya_strings::escapeHTMLChars($userEmail)
      );
      $return .= sprintf(
        '<element type="password" title="%s" name="password" value=""/>',
        papaya_strings::escapeHTMLChars($passwordTitle)
      );
      if (defined('PAPAYA_COMMUNITY_RELOGIN') && PAPAYA_COMMUNITY_RELOGIN != FALSE) {
        $return .= sprintf(
          '<element type="checkbox" title="%s" name="relogin" value="1"/>',
          papaya_strings::escapeHTMLChars($reloginTitle)
        );
      }
      foreach ($this->errors as $errorNumber => $error) {
        $return .= sprintf(
          '<error no="%d">%s</error>',
          (int)$errorNumber,
          papaya_strings::escapeHTMLChars($error)
        );
      }
      $return .= '</login>';
    } else {
      $return = sprintf(
        '<logout name="%s" action="%s" username="%s" givenname="%s" surname="%s" fullname="%s %s">',
        papaya_strings::escapeHTMLChars($this->logformVar),
        papaya_strings::escapeHTMLChars($this->baseLink),
        papaya_strings::escapeHTMLChars($this->getSessionValue($this->surfernameVar)),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_givenname']),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_surname']),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_givenname']),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_surname'])
      );
      $return .= sprintf(
        '<element type="hidden" name="%s_logout" value="1"/>',
        $this->logformVar
      );
      if ($redirectionURL) {
        $return .= sprintf(
          '<element type="hidden" name="redirection" value="%s"/>',
          papaya_strings::escapeHTMLChars($redirectionURL)
        );
      }
      $linkParams = array($this->logformVar.'_logout' => 1);
      if ($redirectionURL) {
        $linkParams[$this->logformVar.'_redirection'] = $redirectionURL;
      }
      $return .= sprintf(
        '<logout-link href="%s"/>',
        papaya_strings::escapeHTMLChars($this->getWebLink(NULL, NULL, NULL, $linkParams))
      );
      $return .= '</logout>';
    }
    return $return;
  }

  /**
  * Get form XML to login by handle
  *
  * @param boolean $login optional, default value TRUE
  * @param string $redirectionURL optional url to redirect after login / logout
  * @param string $handleTitle optional caption for the handle field
  * @param string $passwordTitle optional caption for the password field
  * @param string $reloginTitle optional caption for the relogin checkbox
  * @param array $errors optional array of error messages
  * @access public
  * @return string
  */
  function getHandleFormXML(
    $login = TRUE,
    $redirectionURL = NULL,
    $handleTitle = 'Username',
    $passwordTitle = 'Password',
    $reloginTitle = 'Stay logged-in',
    $errors = array()
  ) {
    if ($login) {
      // If there was a delegated error from the login box, add it here
      if ((
           isset($_GET[$this->logformVar]['error']) &&
           $_GET[$this->logformVar]['error'] == 1) ||
          (
           defined('PAPAYA_URL_LEVEL_SEPARATOR') &&
           PAPAYA_URL_LEVEL_SEPARATOR != '' &&
           isset($_GET[$this->logformVar.PAPAYA_URL_LEVEL_SEPARATOR.'error']) &&
           $_GET[$this->logformVar.PAPAYA_URL_LEVEL_SEPARATOR.'error'] == 1
          )
         ) {
        if (isset($errors['error_handle'])) {
          $this->errors[SURFER_ERROR_USERNAME] = $errors['error_handle'];
        } else {
          $this->errors[SURFER_ERROR_USERNAME] = 'Invalid username/password';
        }
      }
      // Permission error?
      if ($this->_isValid) {
        if (isset($errors['error_permissions'])) {
          $this->errors[SURFER_ERROR_PERMISSIONS] = $errors['error_permissions'];
        } else {
          $this->errors[SURFER_ERROR_PERMISSIONS] = 'Invalid Permissions';
        }
      }
      $this->baseLink = $this->getBaseLink();
      $userHandle = empty($_POST[$this->logformVar.'_handle'])
        ? '' : $_POST[$this->logformVar.'_handle'];
      $changeHref = $this->getLink(array('newpwd' => 1), $this->logformVar);
      $return = sprintf(
        '<login name="%s" action="%s" title="%s" chglink="%s">',
        papaya_strings::escapeHTMLChars($this->logformVar),
        papaya_strings::escapeHTMLChars($this->baseLink),
        'Please fill in your data.',
        papaya_strings::escapeHTMLChars($changeHref)
      );
      if ($redirectionURL) {
        $return .= sprintf(
          '<element type="hidden" name="redirection" value="%s"/>',
          papaya_strings::escapeHTMLChars($redirectionURL)
        );
      }
      $serverQueryString = '';
      if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
        $serverQueryString = $_SERVER['QUERY_STRING'];
      }
      $application = $this->papaya();
      $request = $application->getObject('Request');
      $queryString = $request->getParameter(
        $this->logformVar.'_query_string',
        $request->getParameter(
          isset($this->logformVar['query_string']) ? $this->logformVar['query_string'] : NULL,
          $serverQueryString,
          NULL,
          PapayaRequest::SOURCE_BODY
        ),
        NULL,
        PapayaRequest::SOURCE_BODY
      );
      if ($queryString != '') {
        $return .= sprintf(
          '<element type="hidden" name="query_string" value="%s"/>',
          papaya_strings::escapeHTMLChars($queryString)
        );
      }
      $return .= sprintf(
        '<element type="text" title="%s" name="handle" value="%s"/>',
        papaya_strings::escapeHTMLChars($handleTitle),
        papaya_strings::escapeHTMLChars($userHandle)
      );
      $return .= sprintf(
        '<element type="password" title="%s" name="password" value=""/>',
        papaya_strings::escapeHTMLChars($passwordTitle)
      );
      if (defined('PAPAYA_COMMUNITY_RELOGIN') && PAPAYA_COMMUNITY_RELOGIN != FALSE) {
        $return .= sprintf(
          '<element type="checkbox" title="%s" name="relogin" value="1"/>',
          papaya_strings::escapeHTMLChars($reloginTitle)
        );
      }
      foreach ($this->errors as $errorNumber => $error) {
        $return .= sprintf(
          '<error no="%d">%s</error>',
          (int)$errorNumber,
          papaya_strings::escapeHTMLChars($error)
        );
      }
      $return .= '</login>';
    } else {
      $return = sprintf(
        '<logout name="%s" action="%s" username="%s" givenname="%s" surname="%s" fullname="%s %s">',
        papaya_strings::escapeHTMLChars($this->logformVar),
        papaya_strings::escapeHTMLChars($this->baseLink),
        papaya_strings::escapeHTMLChars($this->getSessionValue($this->surfernameVar)),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_givenname']),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_surname']),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_givenname']),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_surname'])
      );
      $return .= sprintf(
        '<element type="hidden" name="%s_logout" value="1"/>',
        $this->logformVar
      );
      if ($redirectionURL) {
        $return .= sprintf(
          '<element type="hidden" name="redirection" value="%s"/>',
          papaya_strings::escapeHTMLChars($redirectionURL)
        );
      }
      $linkParams = array($this->logformVar.'_logout' => 1);
      if ($redirectionURL) {
        $linkParams[$this->logformVar.'_redirection'] = $redirectionURL;
      }
      $return .= sprintf(
        '<logout-link href="%s"/>',
        papaya_strings::escapeHTMLChars($this->getWebLink(NULL, NULL, NULL, $linkParams))
      );
      $return .= '</logout>';
    }
    return $return;
  }

  /**
  * Get form XML to login by email handle
  *
  * @param boolean $login optional, default value TRUE
  * @param string $redirectionURL optional url to redirect after login / logout
  * @param string $handleTitle optional caption for the handle field
  * @param string $passwordTitle optional caption for the password field
  * @param string $reloginTitle optional caption for the relogin checkbox
  * @param array $errors optional array of error messages
  * @access public
  * @return string
  */
  function getEmailOrHandleFormXML(
    $login = TRUE,
    $redirectionURL = NULL,
    $handleTitle = 'Username or email',
    $passwordTitle = 'Password',
    $reloginTitle = 'Stay logged-in',
    $errors = array()
  ) {
    if ($login) {
      // If there was a delegated error from the login box, add it here
      if ((
           isset($_GET[$this->logformVar]['error']) &&
           $_GET[$this->logformVar]['error'] == 1) ||
          (
           defined('PAPAYA_URL_LEVEL_SEPARATOR') &&
           PAPAYA_URL_LEVEL_SEPARATOR != '' &&
           isset($_GET[$this->logformVar.PAPAYA_URL_LEVEL_SEPARATOR.'error']) &&
           $_GET[$this->logformVar.PAPAYA_URL_LEVEL_SEPARATOR.'error'] == 1
          )
         ) {
        if (isset($errors['error_handle'])) {
          $this->errors[SURFER_ERROR_USERNAME] = $errors['error_handle'];
        } else {
          $this->errors[SURFER_ERROR_USERNAME] = 'Invalid username/password';
        }
      }
      // Permission error?
      if ($this->_isValid) {
        if (isset($errors['error_permissions'])) {
          $this->errors[SURFER_ERROR_PERMISSIONS] = $errors['error_permissions'];
        } else {
          $this->errors[SURFER_ERROR_PERMISSIONS] = 'Invalid Permissions';
        }
      }
      $this->baseLink = $this->getBaseLink();
      $userLogin = empty($_POST[$this->logformVar.'_login'])
        ? '' : $_POST[$this->logformVar.'_login'];
      $changeHref = $this->getLink(array('newpwd' => 1), $this->logformVar);
      $return = sprintf(
        '<login name="%s" action="%s" title="%s" chglink="%s">',
        papaya_strings::escapeHTMLChars($this->logformVar),
        papaya_strings::escapeHTMLChars($this->baseLink),
        'Please fill in your data.',
        papaya_strings::escapeHTMLChars($changeHref)
      );
      if ($redirectionURL) {
        $return .= sprintf(
          '<element type="hidden" name="redirection" value="%s"/>',
          papaya_strings::escapeHTMLChars($redirectionURL)
        );
      }
      $serverQueryString = '';
      if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
        $serverQueryString = $_SERVER['QUERY_STRING'];
      }
      $application = $this->papaya();
      $request = $application->getObject('Request');
      $queryString = $request->getParameter(
        $this->logformVar.'_query_string',
        $request->getParameter(
          $this->logformVar['query_string'],
          $serverQueryString,
          NULL,
          PapayaRequest::SOURCE_BODY
        ),
        NULL,
        PapayaRequest::SOURCE_BODY
      );
      if ($queryString != '') {
        $return .= sprintf(
          '<element type="hidden" name="query_string" value="%s"/>',
          papaya_strings::escapeHTMLChars($queryString)
        );
      }
      $return .= sprintf(
        '<element type="text" title="%s" name="login" value="%s"/>',
        papaya_strings::escapeHTMLChars($handleTitle),
        papaya_strings::escapeHTMLChars($userLogin)
      );
      $return .= sprintf(
        '<element type="password" title="%s" name="password" value=""/>',
        papaya_strings::escapeHTMLChars($passwordTitle)
      );
      if (defined('PAPAYA_COMMUNITY_RELOGIN') && PAPAYA_COMMUNITY_RELOGIN != FALSE) {
        $return .= sprintf(
          '<element type="checkbox" title="%s" name="relogin" value="1"/>',
          papaya_strings::escapeHTMLChars($reloginTitle)
        );
      }
      foreach ($this->errors as $errorNumber => $error) {
        $return .= sprintf(
          '<error no="%d">%s</error>',
          (int)$errorNumber,
          papaya_strings::escapeHTMLChars($error)
        );
      }
      $return .= '</login>';
    } else {
      $return = sprintf(
        '<logout name="%s" action="%s" username="%s" givenname="%s" surname="%s" fullname="%s %s">',
        papaya_strings::escapeHTMLChars($this->logformVar),
        papaya_strings::escapeHTMLChars($this->baseLink),
        papaya_strings::escapeHTMLChars($this->getSessionValue($this->surfernameVar)),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_givenname']),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_surname']),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_givenname']),
        papaya_strings::escapeHTMLChars($this->surfer['surfer_surname'])
      );
      $return .= sprintf(
        '<element type="hidden" name="%s_logout" value="1"/>',
        $this->logformVar
      );
      if ($redirectionURL) {
        $return .= sprintf(
          '<element type="hidden" name="redirection" value="%s"/>',
          papaya_strings::escapeHTMLChars($redirectionURL)
        );
      }
      $linkParams = array($this->logformVar.'_logout' => 1);
      if ($redirectionURL) {
        $linkParams[$this->logformVar.'_redirection'] = $redirectionURL;
      }
      $return .= sprintf(
        '<logout-link href="%s"/>',
        papaya_strings::escapeHTMLChars($this->getWebLink(NULL, NULL, NULL, $linkParams))
      );
      $return .= '</logout>';
    }
    return $return;
  }

  /**
  * Get password form XML
  *
  * @param string $token
  * @access public
  * @return string
  */
  function getPassFormXML($token) {
    $this->baseLink = $this->getBaseLink();
    $result = sprintf(
      '<passwordchange name="%s" action="%s" title="%s">',
      papaya_strings::escapeHTMLChars($this->logformVar),
      papaya_strings::escapeHTMLChars($this->baseLink),
      'Please input your new password.'
    );
    $result .= sprintf(
      '<element type="hidden" name="chg" value="%s"/>',
      papaya_strings::escapeHTMLChars($token)
    );
    $result .= '<element type="hidden" name="save" value="1"/>';
    $result .= '<element type="password" name="password_1" value=""/>';
    $result .= '<element type="password" name="password_2" value=""/>';
    $result .= '</passwordchange>';
    return $result;
  }

  /**
  * Has permission ?
  *
  * @param integer $permId
  * @access public
  * @return boolean
  */
  function hasPerm($permId) {
    if (!$this->_isValid) {
      return FALSE;
    }
    if (!isset($this->livePerms)) {
      $this->loadTopicIdList(FALSE, TRUE);
    }
    return (
      isset($this->livePerms) &&is_array($this->livePerms) && isset($this->livePerms[$permId])
    );
  }

  /**
  * Has one permissions of
  *
  * @param integer|array $permIds
  * @access public
  * @return mixed
  */
  function hasOnePermOf($permIds) {
    if (isset($permIds) && is_array($permIds)) {
      if (count($permIds) >= 1) {
        $filter = $this->databaseGetSQLCondition('p.surferperm_id', $permIds);
      } else {
        return TRUE;
      }
    } else {
      return TRUE;
    }
    $sql = "SELECT COUNT(*)
              FROM %s sl, %s p
             WHERE surfergroup_id = '%d'
               AND sl.surfer_permid = p.surferperm_id
               AND p.surferperm_active = 1
               AND $filter";
    $params = array(
      $this->tableSurferLinks,
      $this->tableSurferPermissions,
      empty($this->surfer['surfergroup_id']) ? 0 : (int)$this->surfer['surfergroup_id']
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ($row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Store time of surfer's latest action
  *
  * @access public
  */
  function storeLastAction() {
    // Only do this if surfer is valid
    if ($this->_isValid) {
      //rounded value
      $lastActionTimeFrame = 60;
      $sessionVariableName = get_class($this).'_surfer_last_action_time';
      // Current time
      $lastActionTime = time();
      $lastActionTimeSession = $this->getSessionValue($sessionVariableName);

      if (isset($lastActionTimeSession) &&
          ($lastActionTime - $lastActionTimeSession) < $lastActionTimeFrame) {
        // do not write - get the value from session
        $this->surfer['surfer_lastaction'] = $lastActionTimeSession;
      } else {
        $data = array('surfer_lastaction' => $lastActionTime);
        // Update database record
        $this->databaseUpdateRecord(
          $this->tableSurfers,
          $data,
          'surfer_id',
          $this->surfer['surfer_id']
        );
        $this->surfer['surfer_lastaction'] = $lastActionTime;
        $this->setSessionValue($sessionVariableName, $lastActionTime);
      }
    }
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
  * Get cookie hash
  *
  * @param string $token
  * @access public
  * @return mixed string|boolean
  */
  function getCookieHash($token) {
    if (defined('PAPAYA_COMMUNITY_RELOGIN_SALT')) {
      $token = PAPAYA_COMMUNITY_RELOGIN_SALT.$token;
    }
    if (defined('PAPAYA_PASSWORD_METHOD')) {
      $method = PAPAYA_PASSWORD_METHOD;
    } else {
      $method = 'md5';
    }
    switch ($method) {
    case 'sha1':
      return substr(sha1($token), 0, 32);
    case 'md5':
      return md5($token);
    default:
      if (extension_loaded('hash') && function_exists('hash')) {
        if ($hash = @hash(strtolower($method), $token)) {
          return substr($hash, 0, 32);
        }
      } elseif ($method == 'sha256') {
        if (function_exists('sha256')) {
          return substr(sha256($token), 0, 32);
        } else {
          trigger_error(
            'Unknown hash method: sha256 - You don\'t have ext/hash or Suhosin.',
            E_USER_WARNING
          );
          return FALSE;
        }
      }
    }
    trigger_error(
      'Unknown hash method: '.htmlspecialchars($method),
      E_USER_WARNING
    );
    return FALSE;
  }

  /**
  * Check login try
  *
  * @see base_auth_secure::checkLoginTry
  * @param string $usermail
  * @access public
  * @return boolean
  */
  function checkLoginTry($usermail) {
    $secureLoginObj = new base_auth_secure();
    $result = $secureLoginObj->checkLoginTry($usermail, 'community');
    unset($secureLoginObj);
    return $result;
  }

  /**
  * record surfer activity if current surfer is valid
  *
  * @return void
  */
  function recordActivity() {
    // Don't do anything if the surfer object is invalid
    // or if the last activity is less than 1 second ago
    if (!$this->_isValid ||
        $this->activitySaved ||
        $this->surfer['surfer_lastaction'] > time() - 1) {
      return;
    }
    $this->activitySaved = TRUE;
    // Get current date
    $now = time();
    // Get rid of time
    $date = (int)($now / 86400) * 86400;
    // Try to get current value for today
    $sql = "SELECT surferactivity_active,
                   surferactivity_id,
                   surferactivity_surferid,
                   surferactivity_date
              FROM %s
             WHERE surferactivity_surferid = '%s'
               AND surferactivity_date = %d";
    $sqlParams = array($this->tableSurferActivity, $this->surferId, $date);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        // If there is a value, add 1 to it
        $data = array('surferactivity_active' => $row['surferactivity_active'] + 1);
        $this->databaseUpdateRecord(
          $this->tableSurferActivity,
          $data,
          'surferactivity_id',
          $row['surferactivity_id']
        );
        // Our work is done, so let's get out of here
        return;
      }
    }
    // If we're still here, we need to insert a new record
    $data = array(
      'surferactivity_surferid' => $this->surferId,
      'surferactivity_date' => $date,
      'surferactivity_active' => 1
    );
    $this->databaseInsertRecord($this->tableSurferActivity, 'surferactivity_id', $data);
  }

  /**
   * Return the login mode (user, apikey, cookie)
   * @return boolean
   */
  public function getLoginMode() {
    return $this->_loginMode;
  }
}


