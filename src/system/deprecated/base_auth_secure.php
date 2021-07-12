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
* password bc function library for php < 5.5
*/
require_once(PAPAYA_INCLUDE_PATH.'external/password/password.php');

/**
* User autentification secure functions
*
* Check logins if there are a brute force attemps
*
* @package Papaya
* @subpackage Authentication
*/
class base_auth_secure extends base_db {

  /**
  * papaya database table authentification try
  * @var string $tableAuthTry
  */
  var $tableAuthTry = PAPAYA_DB_TBL_AUTHTRY;
  /**
  * papaya database table authentification ip
  * @var string $tableAuthIp
  */
  var $tableAuthIp = PAPAYA_DB_TBL_AUTHIP;

  /**
  * try group of authentification
  * @var string $authTryGroup
  */
  var $authTryGroup = 'default';

  /**
  * get password hash
  *
  * @param string $password
  * @access public
  * @return string
  */
  public function getPasswordHash($password) {
    $algorithm = $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::PASSWORD_ALGORITHM, 0);
    if ($algorithm > 0) {
      return password_hash($password, $algorithm);
    } else {
      return password_hash($password, PASSWORD_DEFAULT);
    }
  }

  /**
   * verify the password against the hash
   *
   * @param string $password
   * @param string $hash
   * @return boolean
   */
  public function verifyPassword($password, $hash) {
    $info = password_get_info($hash);
    if (empty($info['algo'])) {
      return $this->getOldPasswordHash($password) === $hash;
    } else {
      return password_verify($password, $hash);
    }
  }

  /**
   * Check if the password needs rehashing
   *
   * @param string $password
   * @param string $hash
   * @return string|FALSE new password if rehashing is needed
   */
  public function rehashPassword($password, $hash) {
    $rehash = $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::PASSWORD_REHASH, FALSE);
    if ($rehash) {
      $algorithm = $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::PASSWORD_ALGORITHM, 0);
      if (empty($algorithm)) {
        $algorithm = PASSWORD_DEFAULT;
      }
      if (password_needs_rehash($hash, $algorithm)) {
        return $this->getPasswordHash($password);
      }
    }
    return FALSE;
  }

  /**
   * Get a password hash using the old, deprecated way
   *
   * @param string $password
   * @return string|boolean
   */
  private function getOldPasswordHash($password) {
    $method = $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::PASSWORD_METHOD, 'md5');
    $password = (
      $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::PASSWORD_PREFIX, '').
      $password.
      $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::PASSWORD_SUFFIX, '')
    );
    switch ($method) {
    case 'sha1':
      return substr(sha1($password), 0, 32);
    case 'md5':
      return md5($password);
    default:
      if (extension_loaded('hash') && function_exists('hash')) {
        if ($hash = @hash(strtolower($method), $password)) {
          return substr($hash, 0, 32);
        }
      } elseif ($method == 'sha256') {
        if (function_exists('sha256')) {
          return substr(sha256($password), 0, 32);
        } else {
          trigger_error(
            'Unknown hash method: sha256 - You don\'t have ext/hash or Suhosin.',
            E_USER_ERROR
          );
          return FALSE;
        }
      }
    }
    trigger_error('Unknown hash method: '.htmlspecialchars($method), E_USER_ERROR);
    return FALSE;
  }

  /**
  * check login try
  *
  * @param string $userName
  * @param mixed $group optional, default value NULL
  * @access public
  * @return boolean
  */
  function checkLoginTry($userName, $group = NULL) {
    if (defined('PAPAYA_LOGIN_RESTRICTION') && PAPAYA_LOGIN_RESTRICTION > 0) {
      if (isset($group)) {
        $this->authTryGroup = $group;
      }

      $ip = empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
      if (defined('PAPAYA_LOGIN_CHECKTIME') && PAPAYA_LOGIN_CHECKTIME > 0) {
        $time = time() - PAPAYA_LOGIN_CHECKTIME;
      } else {
        $time = time() - 600;
      }
      $countIp = 0;
      $countUser = 0;

      $sql = "SELECT COUNT(*)
              FROM %s
             WHERE authtry_time > %d
               AND authtry_ip = '%s'";
      $res = $this->databaseQueryFmt($sql, array($this->tableAuthTry, $time, $ip));
      if ($res) {
        $countIp = $res->fetchField();
      }

      $sql = "SELECT COUNT(*)
              FROM %s
             WHERE authtry_time > %d
               AND authtry_username = '%s'";
      $res = $this->databaseQueryFmt($sql, array($this->tableAuthTry, $time, $userName));
      if ($res) {
        $countUser = $res->fetchField();
      }

      $ipStatus = $this->getIpStatus($ip);
      $allowLoginTry = TRUE;
      switch (PAPAYA_LOGIN_RESTRICTION) {
      case 3 : // hard whitelist restriction
        if ($ipStatus != 1) {
          $allowLoginTry = FALSE;
        }
        break;
      case 2 : // blacklist restriction and whitelist allowed
      case 1 : // blacklist restriction
        if ($ipStatus == 2) {
          $allowLoginTry = FALSE;
        }
        break;
      }
      if ($allowLoginTry && !(PAPAYA_LOGIN_RESTRICTION == 2 && $ipStatus == 1)) {
        $notifyCount = defined('PAPAYA_LOGIN_NOTIFYCOUNT') ? PAPAYA_LOGIN_NOTIFYCOUNT : 10;
        $blockCount = defined('PAPAYA_LOGIN_BLOCKCOUNT') ? PAPAYA_LOGIN_BLOCKCOUNT : 20;
        if ($countIp >= $blockCount || $countUser >= $blockCount) {
          $allowLoginTry = FALSE;
        } elseif ($countIp >= $notifyCount || $countUser >= $notifyCount) {
          if (defined('PAPAYA_LOGIN_NOTIFYFACTOR') && PAPAYA_LOGIN_NOTIFYFACTOR >= 1) {
            $factor = (int)PAPAYA_LOGIN_NOTIFYFACTOR;
          } else {
            $factor = 10;
          }
          if ($countIp % $factor == 0 || $countUser % $factor == 0) {
            $this->sendNotify($_SERVER['REMOTE_ADDR'], $countIp, $userName, $countUser);
          }
        }
      }
      if ($allowLoginTry) {
        $this->addLoginTry($userName, $ip);
        $this->loginTryGC();
        return TRUE;
      } else {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
  * Garbage Collection for login try table. The function uses a rand() so
  * that it is not always called.
  *
  * PAPAYA_LOGIN_GC_ACTIVE = activate gc
  * PAPAYA_LOGIN_GC_TIME = records older then this option will get deleted
  * PAPAYA_LOGIN_GC_DIVISOR = 1/divisor is the probability the query will be executed
  *
  * @access public
  * @return boolean;
  */
  function loginTryGC() {
    if (defined('PAPAYA_LOGIN_GC_ACTIVE') && PAPAYA_LOGIN_GC_ACTIVE &&
        defined('PAPAYA_LOGIN_GC_TIME') && PAPAYA_LOGIN_GC_TIME > 0) {
      if (defined('PAPAYA_LOGIN_GC_DIVISOR') && PAPAYA_LOGIN_GC_DIVISOR > 0) {
        $divisor = PAPAYA_LOGIN_GC_DIVISOR;
      } else {
        $divisor = 50;
      }
      if ($divisor > 1) {
        $gc = (rand(1, $divisor) == 1);
      } else {
        $gc = TRUE;
      }
      if ($gc) {
        $sql = "DELETE FROM %s
                 WHERE authtry_time < %d";
        $params = array(
          $this->tableAuthTry,
          time() - PAPAYA_LOGIN_GC_TIME
        );
        return (FALSE !== $this->databaseQueryFmtWrite($sql, $params));
      }
    }
    return TRUE;
  }

  /**
  * add login try
  *
  * @param string $userName
  * @param string $ip
  * @access public
  */
  function addLoginTry($userName, $ip) {
    $data = array(
      'authtry_username' => (string)$userName,
      'authtry_ip' => (string)$ip,
      'authtry_time' => time(),
      'authtry_group' => (string)$this->authTryGroup
    );
    $this->databaseInsertRecord($this->tableAuthTry, 'authtry_id', $data);
  }

  /**
  * send notification
  *
  * @param string $Ip
  * @param integer $countIp
  * @param string $userName
  * @param integer $countUser
  * @access public
  */
  function sendNotify($Ip, $countIp, $userName, $countUser) {
    if (defined('PAPAYA_LOGIN_NOTIFYEMAIL') &&
        \Papaya\Filter\Factory::isEmail(PAPAYA_LOGIN_NOTIFYEMAIL, TRUE)) {
      $emailObj = new email;

      $bodyTemplate =
        $this->_gt('IP').': {%IP%} ({%IP_HOST%})'.LF.
        $this->_gt('Requests').': {%IP_COUNT%} '.LF.LF.
        $this->_gt('Username').': {%USERNAME%} ({%GROUP%})'.LF.
        $this->_gt('Requests').': {%USER_COUNT%} '.LF.LF.
        $this->_gt('User agent'.': {%USERAGENT%}');
      $subjectTemplate =
        $this->_gt('WARNING! Mass login request on').' {%HOST%}';

      $data = array(
        'IP' => $Ip,
        'IP_HOST' => gethostbyaddr($Ip),
        'IP_COUNT' => $countIp,
        'USERNAME' => $userName,
        'USERAGENT' => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
        'USER_COUNT' => $countUser,
        'HOST' => $_SERVER['HTTP_HOST'],
        'GROUP' => $this->authTryGroup
      );

      $emailObj->setTemplate('body', $bodyTemplate, $data);
      $emailObj->setTemplate('subject', $subjectTemplate, $data);
      $emailObj->send(PAPAYA_LOGIN_NOTIFYEMAIL);
      $this->logMsg(
        MSG_WARNING,
        PAPAYA_LOGTYPE_USER,
        'Brute force notification',
        $emailObj->body
      );
    }
  }

  /**
  * get status of ip
  *
  * @param string $ip
  * @access public
  * @return integer
  */
  function getIPStatus($ip) {
    $sql = "SELECT auth_ip_status
              FROM %s
             WHERE auth_ip = '%s'";
    $params = array($this->tableAuthIp, $ip);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return (int)$row[0];
      }
    }
    return 0;
  }
}


