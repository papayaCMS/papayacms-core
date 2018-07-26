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
* message service
*
* @package Papaya
* @subpackage Administration
*/
class base_messages extends base_db {
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
  * Papaya database table auth user group membership
  * @var string $tableAuthLinks
  */
  var $tableAuthLinks = PAPAYA_DB_TBL_AUTHLINK;
  /**
  * Papaya database table messages
  * @var string $tableMessages
  */
  var $tableMessages = PAPAYA_DB_TBL_MESSAGES;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'msg';
  /**
  * Session parameter name
  * @var string $sessionParamName
  */
  var $sessionParamName = 'msg';
  /**
  * Parameters
  * @var array $params
  */
  var $params = NULL;
  /**
  * Session parameters
  * @var array $sessionParams
  */
  var $sessionParams = NULL;
  /**
  * Base link
  * @var string $baseLink
  */
  var $baseLink = '';

  /**
  * Layout
  * @var object papaya_xsl $layout
  */
  var $layout = NULL;

  /**
  * Message list
  * @var array $messageList
  */
  var $messageList = NULL;
  /**
  * Message tree
  * @var mixed $messageTree
  */
  var $messageTree = NULL;
  /**
  * Users
  * @var array $users
  */
  var $users = NULL;
  /**
  * Users
  * @var array $users
  */
  var $userGroups = NULL;

  /**
   * @var array|NULL
   */
  public $messageThread = NULL;

  /**
   * @var array|NULL
   */
  public $message = NULL;

  /**
  * Load messages
  *
  * @access public
  */
  function loadMessages() {
    unset($this->messageList);
    $sql = "SELECT msg_id, msg_owner_id, msg_prev_id, msg_folder_id,
                   msg_to, msg_from, msg_datetime, msg_subject,
                   msg_priority, msg_type, msg_new, msg_rel_topic_id, msg_rel_box_id
              FROM %s
             WHERE (msg_owner_id = '%s') AND (msg_folder_id = '%s')
             ORDER BY msg_datetime DESC";
    $params = array($this->tableMessages, $this->papaya()->administrationUser->userId,
      (int)$this->params['folder_id']);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->messageList[$row['msg_id']] = $row;
      }
    }
  }

  /**
  * Load message thread
  *
  * @param integer $id
  * @access public
  */
  function loadMessageThread($id) {
    unset($this->messageThread);
    $sql = "SELECT msg_id, msg_owner_id, msg_prev_id, msg_folder_id,
                   msg_to, msg_from, msg_datetime, msg_subject, msg_priority,
                   msg_type, msg_new, msg_rel_topic_id, msg_rel_box_id
              FROM %s
             WHERE (msg_owner_id = '%s')
               AND ((msg_thread_id = '%s') OR (msg_id = '%s'))
               AND (msg_folder_id >= -1)
             ORDER BY msg_datetime DESC";
    $params = array($this->tableMessages, $this->papaya()->administrationUser->userId,
      (int)$id, (int)$id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->messageThread[$row['msg_id']] = $row;
      }
    }
  }

  /**
  * Load message
  *
  * @param integer $id
  * @access public
  */
  function loadMessage($id) {
    unset($this->message);
    $sql = "SELECT msg_id, msg_owner_id, msg_prev_id, msg_folder_id,
                   msg_to, msg_from, msg_cc, msg_bcc, msg_datetime,
                   msg_subject, msg_text, msg_priority,
                   msg_type, msg_new, msg_rel_topic_id, msg_rel_box_id
              FROM %s
             WHERE (msg_id = '%d') AND (msg_owner_id = '%s')";
    $params = array($this->tableMessages, (int)$id, $this->papaya()->administrationUser->userId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->message = $row;
      }
    }
  }

  /**
  * Load user
  *
  * @access public
  */
  function loadUsers() {
    $this->users = array();
    $sql = "SELECT user_id, surname, givenname, email, username
              FROM %s
             ORDER BY surname, givenname";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableAuthUser))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->users[$row['user_id']] = $row;
      }
    }
  }

  /**
  * Load user groups
  * @return void
  */
  function loadUserGroups() {
    $this->userGroups = array();
    $sql = "SELECT group_id, grouptitle
              FROM %s
             ORDER BY grouptitle";
    if ($res = $this->databaseQueryFmt($sql, $this->tableAuthGroups)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->userGroups[$row['group_id']] = $row;
      }
    }
  }

  /**
  * Load thread id
  *
  * @param integer $id
  * @access public
  * @return integer
  */
  function loadThreadId($id) {
    $sql = "SELECT msg_prev_id, msg_thread_id
              FROM %s
             WHERE msg_id = '%d'";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableMessages, (int)$id))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        return (int)(($row['msg_prev_id'] > 0) ? $row['msg_thread_id'] : $id);
      }
    }
    return 0;
  }

  /**
  * Decode addresses
  *
  * @param string $str
  * @access public
  * @return mixed array or NULL
  */
  function decodeAddresses($str) {
    $result = NULL;
    $addressPattern = '~,?\s*(("(([^"]|\\")+)"|([^<,]+))\s+<([^>]+)>|([^@\s]+@[^@\s,;]+)|([^,]+))~';
    if (preg_match_all($addressPattern, $str, $regs, PREG_SET_ORDER)) {
      foreach ($regs as $reg) {
        if (isset($reg[5])) {
          $userName = $reg[5];
          $userEmail = $reg[6];
        } else if ($reg[4]) {
          $userName = $reg[4];
          $userEmail = $reg[6];
        } else if ($reg[7]) {
          $userName = NULL;
          $userEmail = $reg[7];
        } else if ($reg[8]) {
          $userName = $reg[8];
          $userEmail = NULL;
        } else {
          $userName = $reg[1];
          $userEmail = NULL;
        }
        if (preg_match('/^[A-Fa-f\d]{32}$/', $userEmail)) {
          $result[] = array(
            'name' => $userName,
            'user_id' => $userEmail
          );
        } elseif ('@papaya' == substr($userEmail, -7)) {
          $result[] = array(
            'name' => $userName,
            'user_login' => substr($userEmail, 0, -7)
          );
        } elseif ('@group.papaya' == substr($userEmail, -13)) {
          $result[] = array(
            'name' => $userName,
            'group_name' => substr($userEmail, 0, -13)
          );
        } elseif (\PapayaFilterFactory::isEmail($userEmail, TRUE)) {
          $result[] = array(
            'name' => $userName,
            'email' => $userEmail
          );
        } else {
          $result[] = array('name' => $reg[1]);
        }
      }
    }
    return $result;
  }

  /**
  * Update read
  *
  * @param integer $id
  * @access public
  * @return bool
  */
  function updateReaded($id) {
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableMessages, array('msg_new' => 0), 'msg_id', (int)$id
    );
  }

  /**
  * Save message
  *
  * @param array $addrList
  * @param array $data
  * @access public
  */
  function saveMessage($addrList, array $data) {
    if (isset($addrList) && is_array($addrList)) {
      foreach ($addrList as $addr) {
        if (isset($addr['user_id'])) {
          $data['msg_owner_id'] = $addr['user_id'];
          if (!$this->databaseInsertRecord($this->tableMessages, 'msg_id', $data)) {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error'));
          }
        }
      }
    }
  }

  /**
  * Delete Message
  *
  * @access public
  * @return boolean
  */
  function deleteMessage() {
    if (isset($this->message) && is_array($this->message)) {
      if ($this->message['msg_folder_id'] != (-2)) {
        $updated = $this->databaseUpdateRecord(
          $this->tableMessages,
          array('msg_folder_id' => '-2'),
          'msg_id',
          $this->message['msg_id']
        );
        if (FALSE !== $updated) {
          unset($this->params['msg_id']);
          return TRUE;
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error').' - '.
            $this->_gtf(
              'Could not move message "%s (%d)" to the trash folder.',
              $this->message['msg_subject'],
              $this->message['msg_id']
            )
          );
        }
      } else {
        if ($this->databaseDeleteRecord($this->tableMessages, 'msg_id', $this->message['msg_id'])) {
          unset($this->params['msg_id']);
          return TRUE;
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error').' - '.
            $this->_gtf(
              'Could not delete message "%s (%d)".',
              $this->message['msg_subject'],
              $this->message['msg_id']
            )
          );
        }
      }
    } else {
        $this->addMsg(MSG_ERROR, $this->_gt('No message selected.'));
    }
    return FALSE;
  }

  /**
  * Get email string
  *
  * @param array $addrList
  * @access public
  * @return string
  */
  function getEMailString($addrList) {
    $result = '';
    if (isset($addrList) && is_array($addrList)) {
      foreach ($addrList as $addr) {
        $result .= sprintf('%s <%s>, ', $addr['name'], $addr['email']);
      }
      $result = substr($result, 0, -2);
    }
    return $result;
  }

  /**
  * Check and complete addresses
  *
  * @param array &$addressLists
  * @access public
  * @return boolean
  */
  function checkAndCompleteAddresses(&$addressLists) {
    $this->loadUsers();
    $this->loadUserGroups();
    $result = FALSE;
    $resultAddresses = NULL;
    foreach ($addressLists as $field => $list) {
      if (isset($list) && is_array($list)) {
        foreach ($list as $val) {
          if (isset($val['email'])) {
            $resultAddresses[$field][] = $val;
            if ($field == 'to') {
              $result = TRUE;
            }
          } elseif (isset($val['user_id']) && (isset($this->users[$val['user_id']])) &&
              \PapayaFilterFactory::isEmail($this->users[$val['user_id']]['email'], TRUE)) {
            $val['email'] = $this->users[$val['user_id']]['email'];
            $resultAddresses[$field][] = $val;
            if ($field == 'to') {
              $result = TRUE;
            }
          } else {
            $this->addMsg(
              MSG_WARNING,
              $this->_gtf('Invalid Recipient "%s".', $val['name'])
            );
          }
        }
      }
    }
    $addressLists = $resultAddresses;
    return $result;
  }

  /**
  * Parse quotes
  *
  * @param string $str
  * @param boolean $addOnly optional, default value FALSE
  * @access public
  * @return mixed array or NULL
  */
  function parseQuotes($str, $addOnly = FALSE) {
    $result = NULL;
    if (preg_match_all('#^(>*)(([ \t]*)(.*))$#m', $str, $lines, PREG_SET_ORDER)) {
      if (isset($lines) && is_array($lines)) {
        $counter = -1;
        $lastIndent = array('index' => -1, 'indent' => -1);
        foreach ($lines as $line) {
          if (trim($line[0]) == '') {
            $result[++$counter]['data'] = '';
            $result[$counter]['indent'] = -1;
          } else {
            $append = FALSE;
            $indent = strlen($line[1]);
            if (($lastIndent['indent'] >= 0) && ($indent >= 0)) {
              $spaceIndent = ($lastIndent['indent'] < $indent) ?
                $lastIndent['indent'] : $indent;
              for ($i = $counter; $i > $lastIndent['index']; $i--) {
                $result[$i]['indent'] = $spaceIndent;
              }
            }
            if ($counter >= 0) {
              if ($result[$counter]['indent'] == $indent) {
                if ((!$addOnly) && (!preg_match('#^(-|(\d{1,3}\.))#', $line[4]))) {
                  $append = TRUE;
                }
              }
            }
            if ($append) {
              $result[$counter]['data'] .= " ".trim($line[2]);
            } else {
              $result[++$counter]['data'] = trim($line[2]);
              $result[$counter]['indent'] = $indent;
              $lastIndent = array('index' => $counter, 'indent' => $indent);
            }
          }
        }
      }
    }
    return $result;
  }

  /**
  * Rewrap message
  *
  * @param string $str
  * @param integer $addIndent optional, default value 0
  * @param integer $lineLength optional, default value 75
  * @param boolean $addOnly optional, default value TRUE
  * @access public
  * @return string
  */
  function rewrapMessage($str, $addIndent = 0, $lineLength = 75, $addOnly = TRUE) {
    $result = '';
    $parsed = $this->parseQuotes($str, $addOnly);
    if (isset($parsed) && is_array($parsed)) {
      foreach ($parsed as $para) {
        $indent = $para['indent'] + $addIndent;
        $indentStr = ($indent > 0) ? (str_repeat('>', $indent).' ') : '';
        $result .= $indentStr;
        $result .= wordwrap(
          $para['data'], $lineLength - $indent, "\n".$indentStr, 1
        );
        $result .= LF;
      }
      return substr($result, 0, -1);
    }
    return '';
  }

  /**
  * Per formatted
  *
  * @param string $str
  * @access public
  * @return string
  */
  function preformatted($str) {
    $result = (trim($str) != '') ? $str : "\n\n";
    $result = preg_replace(
      '#(\r\n)|(\n\r)|[\r\n]#', '<br />', papaya_strings::escapeHTMLChars($result)
    );
    $result = preg_replace(
      '#(^|\s)\*([^\*/_<>]+)\*(\s|$)#', '\1<b>*\2*</b>\3', $result
    );
    $result = preg_replace(
      '#(^|\s)/([^\*/_<>]+)/(\s|$)#', '\1<i>/\2/</i>\3', $result
    );
    $result = preg_replace(
      '#(^|\s)_([^\*/_<>]+)_(\s|$)#', '\1<u>_\2_</u>\3', $result
    );
    return $result;
  }

  /**
  * format message
  *
  * @param string $str
  * @access public
  * @return string
  */
  function formatMessage($str) {
    $parsed = $this->parseQuotes($str, TRUE);
    if (isset($parsed) && is_array($parsed)) {
      $result = '<div style="margin: 0px 4px 2px 0px;">';
      $indent = 0;
      $startStr = '<div class="messageQuote">'.LF;
      $endStr = '</div>'.LF;
      $ignoreSpace = FALSE;
      foreach ($parsed as $para) {
        if ($para['indent'] >= 0) {
          if ($para['indent'] < $indent) {
            $result .= str_repeat($endStr, $indent - $para['indent']);
            $ignoreSpace = TRUE;
          } elseif ($para['indent'] > $indent) {
            $result .= str_repeat($startStr, $para['indent'] - $indent);
          }
          $indent = $para['indent'];
        }
        if ($ignoreSpace && (trim($str) != '')) {
          $ignoreSpace = FALSE;
          $result .= '<br />';
        } else {
          $result .= $this->preformatted(' '.$para['data']);
        }
      }
      $result .= str_repeat($endStr, $indent);
      $result .= '</div>';
      return $result;
    }
    return '';
  }

  /**
  * Get user email adress
  *
  * @param array $userData
  * @access public
  * @return string
  */
  function getUserEmailAddress($userData) {
    if (isset($userData)) {
      return $this->getUserEmailName($userData).' <'.$userData['email'].'>';
    }
    return '';
  }

  /**
  * Get user email name
  *
  * @param array $userData
  * @access public
  * @return string
  */
  function getUserEmailName($userData) {
    if (isset($userData)) {
      return $this->getEmailName($userData['givenname'].' '.$userData['surname']);
    }
    return '';
  }

  /**
  * Get email name (remove and escape)
  *
  * @param string $name
  * @access public
  * @return string
  */
  function getEmailName($name) {
    $result = strtr($name, ',;"\'', '');
    if (FALSE != strpos($result, '<') || FALSE != strpos($result, '>')) {
      $result = '"'.$result.'"';
    }
    return $result;
  }

  /**
   * Get message counts for a given folder list
   * @param array $folderIds
   * @param bool $newOnly
   * @return array
   */
  function loadMessageCounts($folderIds, $newOnly = FALSE) {
    $result = array();
    foreach ($folderIds as $id) {
      $result[$id] = 0;
    }
    $filter = $this->databaseGetSQLCondition('msg_folder_id', $folderIds);
    if ($newOnly) {
      $filter .= " AND msg_new = '1'";
    }
    $sql = "SELECT msg_folder_id, COUNT(*) AS counted
              FROM %s
             WHERE msg_owner_id = '%s'
               AND $filter
             GROUP BY msg_folder_id";
    $params = array($this->tableMessages, $this->papaya()->administrationUser->userId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['msg_folder_id']] = $row['counted'];
      }
    }
    return $result;
  }
}


