<?php
/**
* locking of elements
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: papaya_locking.php 39621 2014-03-19 11:30:58Z weinert $
*/

/**
* locking of elements
*
* @package Papaya
* @subpackage Administration
*/
class papaya_locking extends base_db {

  /**
  * locking table
  * @var string
  */
  var $tableLocking = PAPAYA_DB_TBL_LOCKING;

  /**
  * papaya database table authentification user
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;

  /**
  * locking timeout in seconds
  * @var integer
  */
  var $maxValidTime = 1800;

  /**
  * locking types
  * @var array
  */
  var $lockTypes = array(
    1 => 'Page'
  );

  /**
  * currently loaded locks
  * @var array
  */
  var $locks = array();

  /**
  * get the locking manager
  *
  * @access public
  * @return object papaya_locking
  */
  public static function getInstance() {
    static $lockingObj;
    if (!isset($lockingObj)) {
      $lockingObj = new papaya_locking();
    }
    return $lockingObj;
  }

  /**
  * add or update a lock
  *
  * @param string $userId
  * @param integer $type
  * @param string $ident
  * @access public
  * @return boolean
  */
  function setLock($userId, $type, $ident) {
    if (!isset($this->locks[$type][$ident])) {
      $this->loadLock($type, $ident);
    }
    $sessionId = $this->getSessionId();
    if (isset($this->locks[$type][$ident])) {
      if ($this->locks[$type][$ident]['locking_sid'] == $sessionId) {
        $data = array(
          'locking_time' => time()
        );
        $filter = array(
          'locking_type' => $type,
          'locking_ident' => $ident
        );
        unset($this->locks[$type][$ident]);
        return FALSE !== $this->databaseUpdateRecord(
          $this->tableLocking, $data, $filter
        );
      } else {
        $this->removeLocks($sessionId, $type);
        return FALSE;
      }
    } else {
      $this->removeLocks($sessionId, $type);
      $data = array(
        'user_id' => $userId,
        'locking_sid' => $sessionId,
        'locking_type' => $type,
        'locking_ident' => $ident,
        'locking_time' => time()
      );
      return (FALSE !== $this->databaseInsertRecord($this->tableLocking, NULL, $data));
    }
  }

  /**
   * remove locks
   *
   * @param string $sessionId
   * @param integer $type optional, default value NULL
   * @access public
   * @return boolean
   */
  function removeLocks($sessionId, $type = NULL) {
    if (isset($type)) {
      $filter = array(
        'locking_sid' => $sessionId,
        'locking_type' => $type
      );
      if (isset($this->locks[$type])) {
        unset($this->locks[$type]);
      }
    } else {
      $filter = array(
        'locking_sid' => $sessionId
      );
      $this->locks = array();
    }
    if (FALSE !== $this->databaseDeleteRecord($this->tableLocking, $filter)) {
      $this->removeOldLocks();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * remove outdated locks
  *
  * @access public
  * @return boolean
  */
  function removeOldLocks() {
    $sql = "DELETE FROM %s WHERE locking_time < %d";
    $params = array(
      $this->tableLocking,
      time() - $this->maxValidTime
    );
    return (FALSE !== $this->databaseQueryFmtWrite($sql, $params));
  }

  /**
  * load a lock
  *
  * @param integer $type
  * @param string $ident
  * @access public
  * @return boolean
  */
  function loadLock($type, $ident) {
    $sql = "SELECT l.user_id, l.locking_time, l.locking_sid,
                   u.givenname, u.surname
              FROM %s l
              LEFT OUTER JOIN %s u ON u.user_id = l.user_id
             WHERE l.locking_ident = '%s'
               AND l.locking_type = '%d'
               AND l.locking_time >= '%d'";
    $params = array($this->tableLocking, $this->tableAuthUser, $ident, $type,
      time() - $this->maxValidTime);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->locks[$type][$ident] = $row;
      }
    }
  }

  /**
  * Get lock user name
  * @param string $type
  * @param string $ident
  * @return string
  */
  function getLockUser($type, $ident) {
    if (!isset($this->locks[$type][$ident])) {
      $this->loadLock($type, $ident);
    }
    if (isset($this->locks[$type][$ident])) {
      return $this->locks[$type][$ident]['givenname'].' '.
        $this->locks[$type][$ident]['surname'];
    }
    return '';
  }
  /**
  * create lock identifier
  *
  * takes all functions params an serializes to a string
  *
  * @access public
  * @return string
  */
  function getLockIdent() {
    $args = func_get_args();
    $result = 'lock';
    if (isset($args) && is_array($args)) {
      foreach ($args as $arg) {
        $result .= ':'.rawurlencode($arg);
      }
    }
    return $result;
  }

  /**
  * Get current session id
  * @return string
  */
  function getSessionId() {
    return (string)$this->papaya()->session->id;
  }
}


