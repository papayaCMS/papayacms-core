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
* User autentification secure functions - show data
* @package Papaya
* @subpackage Authentication
*/
class papaya_auth_secure extends base_auth_secure {

  /**
  * List limit
  * @var integer
  */
  var $listLimit = 20;
  /**
  * Login tries
  * @var array $loginTries
  */
  var $loginTries = array();
  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'asec';

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
   * @var array
   */
  public $ipDetail = array();

  /**
   * @var int
   */
  private $ipCount = 0;

  /**
   * @var array
   */
  private $ips = array();

  /**
   * @var int
   */
  private $loginTryCount = 0;

  /**
  * Initialization
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
  }

  /**
  * Base execution for handling attributes
  *
  * @access public
  */
  function execute() {
    if (isset($this->params['ip']) && !empty($this->params['ip'])) {
      if (isset($this->params['cmd'])) {
        switch($this->params['cmd']) {
        case 'allow_ip':
          if (isset($this->params['confirm_allow']) && $this->params['confirm_allow']) {
            if ($this->addIpToWhitelist($this->params['ip'])) {
              $this->addMsg(MSG_INFO, $this->_gt('IP added to whitelist.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error.'));
            }
          }
          break;
        case 'deny_ip':
          if (isset($this->params['confirm_deny']) && $this->params['confirm_deny']) {
            if ($this->params['ip'] != $_SERVER['REMOTE_ADDR']) {
              if ($this->addIpToBlacklist($this->params['ip'])) {
                $this->addMsg(MSG_INFO, $this->_gt('IP added to blacklist.'));
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Database error.'));
              }
            } else {
              $this->addMsg(
                MSG_ERROR,
                $this->_gt('You can not add your own IP to the blacklist.')
              );
            }
          }
          break;
        case 'delete_ip':
          if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
            if (defined('PAPAYA_LOGIN_RESTRICTION') && PAPAYA_LOGIN_RESTRICTION == 3 &&
                $this->params['ip'] == $_SERVER['REMOTE_ADDR']) {
              $this->addMsg(
                MSG_ERROR,
                $this->_gt(
                  'You can not delete your own IP from the list, because access is'.
                    ' restricted to whitelist currently.'
                )
              );
            } else {
              if ($this->deleteIpFromlist($this->params['ip'])) {
                $this->addMsg(MSG_INFO, $this->_gt('IP deleted.'));
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Database error.'));
              }
            }
          }
          break;
        }
      }
    }
    if (isset($this->params['cmd']) && $this->params['cmd'] == 'empty_try') {
      if (isset($this->params['confirm_empty']) && $this->params['confirm_empty']) {
        if ($this->emptyLoginTryList() !== FALSE) {
          $this->addMsg(MSG_INFO, $this->_gt('List deleted.'));
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error.'));
        }
      }
    }

    $this->loadLastTries(empty($this->params['try_offset']) ? 0 : (int)$this->params['try_offset']);
    $this->loadIpList(empty($this->params['ip_offset']) ? 0 : (int)$this->params['ip_offset']);
    if (isset($this->params['ip']) && !empty($this->params['ip'])) {
      $this->loadIpDetails(trim($this->params['ip']));
    }
  }

  /**
  * Get xml
  *
  * @access public
  */
  function getXML() {
    if (isset($this->params['ip']) && !empty($this->params['ip'])) {
      if (isset($this->params['cmd'])) {
        switch($this->params['cmd']) {
        case 'allow_ip':
          if (!(isset($this->params['confirm_allow']) && $this->params['confirm_allow'])) {
            $this->layout->add($this->getWhitelistDialog($this->params['ip']));
          }
          break;
        case 'deny_ip':
          if (!(isset($this->params['confirm_deny']) && $this->params['confirm_deny'])) {
            if ($this->params['ip'] != $_SERVER['REMOTE_ADDR']) {
              $this->layout->add($this->getBlacklistDialog($this->params['ip']));
            } else {
              $this->addMsg(
                MSG_ERROR,
                $this->_gt('You can not add your own IP to the blacklist.')
              );
            }
          }
          break;
        case 'delete_ip':
          if (!(isset($this->params['confirm_delete']) && $this->params['confirm_delete'])) {
            if (defined('PAPAYA_LOGIN_RESTRICTION') &&
                PAPAYA_LOGIN_RESTRICTION == 3 &&
                $this->params['ip'] == $_SERVER['REMOTE_ADDR']) {
              $this->addMsg(
                MSG_ERROR,
                $this->_gt(
                  'You can not delete your own IP from the list, because access is'.
                  ' restricted to whitelist currently.'
                )
              );
            } else {
              $this->layout->add($this->getDeleteIpDialog($this->params['ip']));
            }
          }
          break;
        }
      }
    }
    if (isset($this->params['cmd']) && $this->params['cmd'] == 'empty_try') {
      if (!(isset($this->params['confirm_empty']) && $this->params['confirm_empty'])) {
        $this->layout->add($this->getEmptyLoginTryDialog());
      }
    }

    $this->layout->addLeft($this->getIPListXML());
    $this->layout->addRight($this->getIpDetailXML());
    $this->layout->add($this->getLoginTryListXML());

    $this->getButtonsXML();
  }

  /**
  * Get buttons xml
  *
  * @access public
  */
  function getButtonsXML() {
    $menubar = new base_btnbuilder;
    $menubar->images = $this->papaya()->images;

    if (isset($this->ipDetail) && is_array($this->ipDetail) &&
        isset($this->ipDetail['ip'])) {
      if ($this->ipDetail['ip_status'] != 1) {
        $menubar->addButton(
          'Whitelist',
          $this->getLink(
            array('cmd' => 'allow_ip', 'ip' => $this->ipDetail['ip'])
          ),
          'status-user-angel',
          'Add to whitelist'
        );
      }
      if ($this->ipDetail['ip_status'] != 2) {
        $menubar->addButton(
          'Blacklist',
          $this->getLink(
            array('cmd' => 'deny_ip', 'ip' => $this->ipDetail['ip'])
          ),
          'status-user-evil',
          'Add to blacklist'
        );
      }
      if ($this->ipDetail['ip_status'] != 0) {
        $menubar->addButton(
          'Delete',
          $this->getLink(
            array('cmd' => 'delete_ip', 'ip' => $this->ipDetail['ip'])
          ),
          'actions-generic-delete',
          'Delete from black-/whitelist'
        );
      }
    }
    $menubar->addSeperator();
    $menubar->addButton(
      'Delete login tries',
      $this->getLink(array('cmd' => 'empty_try')),
      'actions-edit-clear',
      'Delete login try list'
    );
    if ($str = $menubar->getXML()) {
      $this->layout->add('<menu>'.$str.'</menu>', 'menus');
    }
  }

  /**
  * Add ip to whitelist
  *
  * @param string $ip
  * @access public
  * @return boolean
  */
  function addIPToWhitelist($ip) {
    $data = array(
      'auth_ip_status' => 1
    );
    if ($this->isIpInList($ip)) {
      $cond = array(
        'auth_ip' => $ip
      );
      return (FALSE !== $this->databaseUpdateRecord($this->tableAuthIp, $data, $cond));
    } else {
      $data['auth_ip'] = $ip;
      return (FALSE !== $this->databaseInsertRecord($this->tableAuthIp, NULL, $data));
    }
  }

  /**
  * Add ip to blacklist
  *
  * @param string $ip
  * @access public
  * @return boolean
  */
  function addIPToBlacklist($ip) {
    $data = array(
      'auth_ip_status' => 2
    );
    if ($this->isIpInList($ip)) {
      $cond = array(
        'auth_ip' => $ip
      );
      return (FALSE !== $this->databaseUpdateRecord($this->tableAuthIp, $data, $cond));
    } else {
      $data['auth_ip'] = $ip;
      return (FALSE !== $this->databaseInsertRecord($this->tableAuthIp, NULL, $data));
    }
  }

  /**
  * Delete ip from list
  *
  * @param string $ip
  * @access public
  * @return boolean
  */
  function deleteIpFromList($ip) {
    if ($this->isIpInList($ip)) {
      $cond = array(
        'auth_ip' => $ip
      );
      return (FALSE !== $this->databaseDeleteRecord($this->tableAuthIp, $cond));
    }
    return FALSE;
  }

  /**
  * Is ip in list
  *
  * @param string $ip
  * @access public
  * @return boolean
  */
  function isIpInList($ip) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE auth_ip = '%s'";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableAuthIp, $ip))) {
      return (boolean)$res->fetchField();
    }
    return FALSE;
  }

  /**
  * Load last tries
  *
  * @param integer $offset optional, default value 0
  * @access public
  */
  function loadLastTries($offset = 0) {
    $this->loginTries = array();
    $sql = "SELECT at.authtry_id, at.authtry_username, at.authtry_ip,
                   at.authtry_time, at.authtry_group, ip.auth_ip_status
              FROM %s at
              LEFT OUTER JOIN %s ip ON (ip.auth_ip = at.authtry_ip)
             ORDER BY at.authtry_time DESC";
    $params = array($this->tableAuthTry, $this->tableAuthIp);
    if ($res = $this->databaseQueryFmt($sql, $params, $this->listLimit, (int)$offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->loginTries[$row['authtry_id']] = $row;
      }
      $this->loginTryCount = $res->absCount();
    }
  }

  /**
  * Empty login try list
  *
  * @access public
  * @return mixed
  */
  function emptyLoginTryList() {
    return $this->databaseEmptyTable($this->tableAuthTry);
  }

  /**
  * Get login try list xml
  *
  * @access public
  * @return string $result xml
  */
  function getLoginTryListXML() {
    $result = '';
    if (isset($this->loginTries) && is_array($this->loginTries) &&
        count($this->loginTries) > 0) {
      $images = $this->papaya()->images;
      $result .= sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Login requests'))#
      );

      $offset = (isset($this->params['try_offset'])) ? (int)$this->params['try_offset'] : 0;
      $result .= papaya_paging_buttons::getPagingButtons(
        $this,
        array('cmd' => 'show'),
        (int)$offset,
        $this->listLimit,
        $this->loginTryCount,
        9,
        'try_offset'
      );
      $result .= '<cols>';
      $result .= '<col>'.papaya_strings::escapeHTMLChars($this->_gt('IP')).'</col>';
      $result .= '<col>'.papaya_strings::escapeHTMLChars($this->_gt('Time')).'</col>';
      $result .= '<col>'.papaya_strings::escapeHTMLChars($this->_gt('Username')).'</col>';
      $result .= '<col>'.papaya_strings::escapeHTMLChars($this->_gt('Login Type')).'</col>';
      $result .= '<col/>';
      $result .= '<col/>';
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->loginTries as $try) {
        $result .= sprintf(
          '<listitem href="%s" title="%s">',
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cmd' => 'show_ip', 'ip' => $try['authtry_ip']))
          ),
          papaya_strings::escapeHTMLChars($try['authtry_ip'])
        );
        $result .= sprintf(
          '<subitem>%s</subitem>',
          date('Y-m-d H:i:s', $try['authtry_time'])
        );
        $result .= sprintf(
          '<subitem>%s</subitem>',
          papaya_strings::escapeHTMLChars($try['authtry_username'])
        );
        $result .= sprintf(
          '<subitem>%s</subitem>',
          papaya_strings::escapeHTMLChars($try['authtry_group'])
        );
        if ($try['auth_ip_status'] != 1) {
          $result .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('cmd' => 'allow_ip', 'ip' => $try['authtry_ip']))
            ),
            papaya_strings::escapeHTMLChars($images['status-user-angel']),
            papaya_strings::escapeHTMLChars($this->_gt('Add to whitelist'))
          );
        } else {
          $result .= '<subitem/>';
        }
        if ($try['auth_ip_status'] != 2) {
          $result .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('cmd' => 'deny_ip', 'ip' => $try['authtry_ip']))
            ),
            papaya_strings::escapeHTMLChars($images['status-user-evil']),
            papaya_strings::escapeHTMLChars($this->_gt('Add to blacklist'))
          );
        } else {
          $result .= '<subitem/>';
        }
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
    }
    return $result;
  }

  /**
  * Load ip list
  *
  * @param integer $offset optional, default value 0
  * @access public
  */
  function loadIpList($offset = 0) {
    $this->ips = array();
    $sql = "SELECT auth_ip, auth_ip_status
             FROM %s
            ORDER BY auth_ip";
    if ($res = $this->databaseQueryFmt($sql, $this->tableAuthIp, $this->listLimit, (int)$offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->ips[$row['auth_ip']] = $row;
      }
      $this->ipCount = $res->absCount();
    }
  }

  /**
  * Get ip list xml
  *
  * @access public
  * @return string $result xml
  */
  function getIPListXML() {
    $result = '';
    if (isset($this->ips) && is_array($this->ips) && count($this->ips) > 0) {
      $result .= sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('IP list'))
      );
      $images = $this->papaya()->images;
      $offset = (isset($this->params['ip_offset'])) ? (int)$this->params['ip_offset'] : 0;
      $result .= papaya_paging_buttons::getPagingButtons(
        $this,
        array('cmd' => 'show'),
        (int)$offset,
        $this->listLimit,
        $this->ipCount,
        9,
        'ip_offset'
      );

      $result .= '<items>';
      foreach ($this->ips as $ip) {
        switch($ip['auth_ip_status']) {
        case '1':
          $imageIdx = 'status-user-angel';
          break;
        case '2':
        default:
          $imageIdx = 'status-user-evil';
          break;
        }
        if (isset($this->params['ip']) && $ip['auth_ip'] == $this->params['ip']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s"%s>',
          papaya_strings::escapeHTMLChars($ip['auth_ip']),
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cmd' => 'show_ip', 'ip' => $ip['auth_ip']))
          ),
          papaya_strings::escapeHTMLChars($images[$imageIdx]),
          $selected
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
    }
    return $result;
  }

  /**
  * Load ip details
  *
  * @param string $ip
  * @access public
  */
  function loadIpDetails($ip) {
    $time = time();
    $this->ipDetail = array(
      'ip' => $ip,
      'ip_status' => $this->getIPStatus($ip),
      'min10' => $this->loadIpCount($ip, $time - 600),
      'min30' => $this->loadIpCount($ip, $time - 1800),
      'hour' => $this->loadIpCount($ip, $time - 3600),
      'day' => $this->loadIpCount($ip, $time - 86400),
      'month' => $this->loadIpCount($ip, $time - 2635200),
    );
  }

  /**
  * Load ip count
  *
  * @param string $ip
  * @param integer $time
  * @access public
  * @return integer
  */
  function loadIpCount($ip, $time) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE authtry_ip = '%s'
               AND authtry_time >= '%d'";
    $params = array($this->tableAuthTry, $ip, $time);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return 0;
  }

  /**
  * get ip detail in xml
  *
  * @access public
  * @return string $result xml
  */
  function getIpDetailXML() {
    $result = '';
    if (isset($this->ipDetail) && is_array($this->ipDetail) &&
        !empty($this->ipDetail['ip'])) {
      $images = $this->papaya()->images;
      $result .= sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('IP details'))
      );
      $result .= '<items>';
      switch($this->ipDetail['ip_status']) {
      case '1':
        $imageIdx = 'status-user-angel';
        break;
      case '2':
        $imageIdx = 'status-user-evil';
        break;
      default:
        $imageIdx = '0';
        break;
      }
      if ($imageIdx != '0') {
        $result .= sprintf(
          '<listitem title="%s" image="%s">',
          papaya_strings::escapeHTMLChars($this->ipDetail['ip']),
          papaya_strings::escapeHTMLChars($images[$imageIdx])
        );
      } else {
        $result .= sprintf(
          '<listitem title="%s">',
          papaya_strings::escapeHTMLChars($this->ipDetail['ip'])
        );
      }
      $hostName = getHostByAddr($this->ipDetail['ip']);
      if ($hostName != $this->ipDetail['ip']) {
        $result .= sprintf(
          '<subitem>%s</subitem>',
          papaya_strings::escapeHTMLChars($hostName)
        );
      } else {
        $result .= '<subitem/>';
      }
      $result .= '</listitem>';

      $result .= sprintf(
        '<listitem title="10 %s" indent="1"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Minutes')),
        papaya_strings::escapeHTMLChars($this->ipDetail['min10'])
      );
      $result .= sprintf(
        '<listitem title="30 %s" indent="1"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Minutes')),
        papaya_strings::escapeHTMLChars($this->ipDetail['min30'])
      );
      $result .= sprintf(
        '<listitem title="1 %s" indent="1"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Hour')),
        papaya_strings::escapeHTMLChars($this->ipDetail['hour'])
      );
      $result .= sprintf(
        '<listitem title="1 %s" indent="1"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Day')),
        papaya_strings::escapeHTMLChars($this->ipDetail['day'])
      );
      $result .= sprintf(
        '<listitem title="1 %s" indent="1"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Month')),
        papaya_strings::escapeHTMLChars($this->ipDetail['month'])
      );

      $result .= '</items>';
      $result .= '</listview>';
    }
    return $result;
  }

  /**
  * Get whitelist dialog
  *
  * @see base_msgdialog::getMsgDialog
  * @param string $ip
  * @access public
  * @return string xml
  */
  function getWhitelistDialog($ip) {
    $hidden = array(
      'cmd' => 'allow_ip',
      'confirm_allow' => 1,
      'ip' => $ip
    );
    $msg = sprintf(
      $this->_gt('Add IP "%s" to whitelist?'),
      $ip
    );
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->buttonTitle = 'Add';
    return $dialog->getMsgDialog();
  }

  /**
  * Get blacklist dialog
  *
  * @see base_msgdialog::getMsgDialog
  * @param string $ip
  * @access public
  * @return string xml
  */
  function getBlacklistDialog($ip) {
    $hidden = array(
      'cmd' => 'deny_ip',
      'confirm_deny' => 1,
      'ip' => $ip
    );
    $msg = sprintf(
      $this->_gt('Add IP "%s" to blacklist?'),
      $ip
    );
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->buttonTitle = 'Add';
    return $dialog->getMsgDialog();
  }

  /**
  * Get delete ip dialog
  *
  * @see base_msgdialog::getMsgDialog
  * @param string $ip
  * @access public
  * @return string xml
  */
  function getDeleteIpDialog($ip) {
    $hidden = array(
      'cmd' => 'delete_ip',
      'confirm_delete' => 1,
      'ip' => $ip
    );
    $msg = sprintf(
      $this->_gt('Delete IP "%s" from list?'),
      $ip
    );
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Get empty login try dialog
  *
  * @see base_msgdialog::getMsgDialog
  * @access public
  * @return string xml
  */
  function getEmptyLoginTryDialog() {
    $hidden = array(
      'cmd' => 'empty_try',
      'confirm_empty' => 1,
    );
    $msg = $this->_gt('Delete login try list?');
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }
}


