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
* Provides admin functionality for log messages
*
* @package Papaya
* @subpackage Core
*/
class papaya_log extends base_db {
  /**
  * Papaya database log table
  * @var string $table
  */
  var $table = PAPAYA_DB_TBL_LOG;

  /**
  * Table types
  * @var string $tableTypes
  */
  var $tableTypes = '';
  /**
  * Message list
  * @var array $messageList
  */
  var $messageList;
  /**
  * Images
  * @var array $images
  */
  var $images;
  /**
  * Layout
  * @var object papaya_xsl $layout
  */
  var $layout;

  /**
  * Levels
  * @var array $levels
  */
  var $levels = array('All', 'Information', 'Warning', 'Error', 'Debug');

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'plog';
  /**
  * Session parameter name
  * @var string $sessionParamName
  */
  var $sessionParamName;
  /**
  * Base link
  * @var string $baseLink
  */
  var $baseLink;
  /**
  * Parameters
  * @var array $params
  */
  var $params;
  /**
  * Session parameters
  * @var array $sessionParams
  */
  var $sessionParams;

  /**
  * Toolbar
  * @var mixed $toolbar
  */
  var $toolbar;

  /**
  * Selected
  * @var integer $selected
  */
  var $selected = 0;
  /**
  * Selected level
  * @var integer $selLevel
  */
  var $selLevel = -1;
  /**
  * Selected type
  * @var integer $selType
  */
  var $selType = 0;

  /**
  * Steps
  * @var integer $steps
  */
  var $steps = 20;

  /**
   * @var array
   */
  private $messageTypeList;

  /**
   * @var int
   */
  private $messageCount = 0;

  /**
   * @var int
   */
  private $messageAbsCount = 0;

  /**
  * Initialize parameters
  *
  * @access public
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_log_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('page', array('id'));
    $this->initializeSessionParam('level', array('page'));
    $this->initializeSessionParam('type', array('page'));
    $this->initializeSessionParam('id', array('page'));
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    $this->selected = (int)$this->params['id'];
    $this->selLevel = (int)$this->params['level'];
    $this->selType = (int)$this->params['type'];

    foreach ($this->levels as $key => $val) {
      $this->levels[$key] = $this->_gt($val);
    }
  }

  /**
  * Load list
  *
  * @access public
  * @return boolean
  */
  function loadList() {
    $this->messageList = array();
    $this->messageCount = 0;
    $this->messageAbsCount = 0;
    $offset = (isset($this->params['page'])) ? ($this->params['page'] - 1) * $this->steps : 0;
    $filter = '';
    $operator = ' WHERE ';
    if ($this->params['type'] > 0) {
      $filter .= $operator." log_msgtype = '".(int)$this->params['type']."'\n";
      $operator = ' AND ';
    }
    if ($this->params['level'] > 0) {
      $filter .= $operator." log_msgno = '".(int)($this->params['level'] - 1)."'\n";
      $operator = ' AND ';
    }
    if ($this->selected > 0) {
      $sql = "SELECT COUNT(*) FROM %s $filter $operator log_id >= '%d'";
      if ($res = $this->databaseQueryFmt($sql, array($this->table, $this->selected))) {
        $page = ceil($res->fetchField() / $this->steps);
        $this->params['page'] = $page;
        $offset = ($page - 1) * $this->steps;
      }
    }
    $sql = "SELECT log_id, log_time, log_msgtype, log_msgno, log_msg_short
              FROM %s
                   $filter
             ORDER BY log_time DESC, log_id DESC";
    if ($res = $this->databaseQueryFmt($sql, $this->table, $this->steps, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->messageList[$row['log_id']] = $row;
      }
      $this->messageAbsCount = $res->absCount();
      return $this->loadMsgGroup();
    }
    return FALSE;
  }

  /**
  * Load message by id from database
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadMessage($id) {
    $sql = "SELECT log_id, log_time, log_msgtype, log_msgno,
                   log_msg_short, log_msg_long,
                   log_msg_uri, log_msg_script,
                   log_msg_from_ip, log_msg_referer, log_msg_cookies,
                   log_version_papaya, log_version_project,
                   user_id, username
              FROM %s
             WHERE log_id = %d";
    if ($res = $this->databaseQueryFmt($sql, array($this->table, $id))) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->messageList[$row['log_id']] = $row;
        return TRUE;
      }
    }
    return FALSE;

  }

  /**
  * Load message group
  *
  * @access public
  * @return boolean
  */
  function loadMsgGroup() {
    foreach (base_statictables::getTableLogGroups() as $key => $val) {
      $this->messageTypeList[$key] = $this->_gt($val);
    }
    return TRUE;
  }

  /**
   * Delete old
   *
   * @see base_db::databaseQueryFmt()
   * @param integer $timeFrame
   * @param int $module
   * @param int $level
   * @return mixed
   */
  function deleteOld($timeFrame, $module = 0, $level = 0) {
    $filter = '';
    if (trim($module) > 0) {
      $filter .= 'AND '.$this->databaseGetSQLCondition('log_msgtype', $module);
    }
    if (trim($level) > 0) {
      $filter .= 'AND '.$this->databaseGetSQLCondition('log_msgno', $level - 1);
    }
    $sql = "DELETE FROM %s WHERE log_time < %d $filter";
    return (FALSE !== $this->databaseQueryFmtWrite($sql, array($this->table, $timeFrame)));
  }

  /**
   * Delete all
   *
   * @see base_db::databaseEmptyTable()
   * @param int $module
   * @param int $level
   * @return mixed
   */
  function deleteAll($module = 0, $level = 0) {
    if ($module > 0 || $level > 0) {
      $condition = array();
      if ($module > 0) {
        $condition['log_msgtype'] = (int)$module;
      }
      if ($level > 0) {
        $condition['log_msgno'] = (int)$level - 1;
      }
      return (FALSE !== $this->databaseDeleteRecord($this->table, $condition));
    } else {
      return (FALSE !== $this->databaseEmptyTable($this->table));
    }
  }

  /**
  * Action clear
  *
  * @access public
  */
  function actionClear() {
    if (isset($this->messageList) && is_array($this->messageList)) {
      if (isset($this->params['confirm']) && $this->params['confirm']) {
        if (
          $this->deleteAll(
            empty($this->params['type']) ? 0 : (int)$this->params['type'],
            empty($this->params['level']) ? 0 : (int)$this->params['level']
          )
        ) {
          $this->addMsg(MSG_INFO, $this->_gt('Protocol deleted.'));
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
        }
        $this->loadList();
      } else {
        $this->layout->add($this->getClearDlg());
      }
    } else {
      $this->addMsg(MSG_INFO, $this->_gt('Protocol is empty.'));
    }
  }

  /**
  * Action delte old
  *
  * @access public
  */
  function actionDelOld() {
    if (isset($this->messageList) && is_array($this->messageList) &&
        isset($this->messageList[$this->selected])) {
      $selected = $this->messageList[$this->selected];
      if (isset($selected) && is_array($selected)) {
        if (isset($this->params['confirm'])) {
          if (
            $this->deleteOld(
              $selected['log_time'],
              empty($this->params['type']) ? 0 : (int)$this->params['type'],
              empty($this->params['level']) ? 0 : (int)$this->params['level']
            )
          ) {
            $this->addMsg(MSG_INFO, $this->_gt('Old messages deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
          $this->loadList();
        } else {
          $this->layout->add($this->getDeleteOldDlg());
        }
      } else {
        $this->addMsg(MSG_INFO, $this->_gt('No message selected.'));
      }
    } else {
      $this->addMsg(MSG_INFO, $this->_gt('The protocol is empty.'));
    }
  }

  /**
  * Get XML
  *
  * @access public
  */
  function getXML() {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'clear':
        $this->actionClear();
        break;
      case 'del_old':
        $this->actionDelOld();
        break;
      }
    }
    if (isset($this->layout) && is_object($this->layout)) {
      $this->getSelected();
      $this->layout->add($this->getList());
      $this->layout->add($this->getButtonsXML(), 'menus');
    }
  }

  /**
  * Get list
  *
  * @access public
  * @return string $result XML
  */
  function getList() {
    if (isset($this->messageList) && is_array($this->messageList)) {
      $listview = new \PapayaUiListview();
      $listview->caption = new \PapayaUiStringTranslated('Event protocol');
      $listview->parameterGroup($this->paramName);
      $listview->reference()->setParameters(
        array(
          'level' => $listview->parameters()->get('level', $this->selLevel),
          'type' => $listview->parameters()->get('type', $this->selType)
        ),
        $listview->parameterGroup($this->paramName)
      );

      $paging = new \PapayaUiToolbarPaging(
        array($this->paramName, 'page'), (int)$this->messageAbsCount
      );
      if (isset($this->params['page']) && $this->params['page'] > 0) {
        $paging->currentPage = (int)$this->params['page'];
      }
      $paging->itemsPerPage = $this->steps;
      $paging->buttonLimit = 25;
      $paging->reference->setParameters(
        array(
          'cmd' => 'show',
          'level' => $listview->parameters()->get('level', $this->selLevel),
          'type' => $listview->parameters()->get('type', $this->selType)
        ),
        $listview->parameterGroup()
      );
      $listview->toolbars->topLeft->elements[] = $paging;

      $listview->columns[] = new \PapayaUiListviewColumn(
        new \PapayaUiStringTranslated('Message'), \PapayaUiOptionAlign::LEFT
      );
      $listview->columns[] = new \PapayaUiListviewColumn(
        new \PapayaUiStringTranslated('Group'), \PapayaUiOptionAlign::CENTER
      );
      $listview->columns[] = new \PapayaUiListviewColumn(
        new \PapayaUiStringTranslated('Date'), \PapayaUiOptionAlign::CENTER
      );

      foreach ($this->messageList as $msgId => $msg) {
        $itemImages = array(
          0 => 'status-dialog-information',
          1 => 'status-dialog-warning',
          2 => 'status-dialog-error',
          3 => 'items-page',
        );
        $listitem = new \PapayaUiListviewItem(
          isset($itemImages[$msg['log_msgno']]) ? $itemImages[$msg['log_msgno']] : '',
          $msg['log_msg_short'],
          array(
           'id' => $msgId
          ),
          $msgId == $this->params['id']
        );
        $listview->items[] = $listitem;

        if (isset($this->messageTypeList[$msg['log_msgtype']])) {
          $logType = $this->messageTypeList[$msg['log_msgtype']];
        } else {
          $logType = new \PapayaUiStringTranslated('Invalid logtype #%d', $msg['log_msgtype']);
        }
        $listitem->subitems[] = new \PapayaUiListviewSubitemText($logType);
        $listitem->subitems[] = new \PapayaUiListviewSubitemDate((int)$msg['log_time']);
      }
      return $listview->getXml();
    }
    return '';
  }

  /**
  * Get selected
  *
  * @access public
  * @return string $result XML
  */
  function getSelected() {
    $selected = $this->selected;
    $result = '';
    if (isset($this->messageList[$selected]) && is_array($this->messageList[$selected])) {
      $this->loadMessage($selected);
      $result .= sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Information'))
      );
      $result .= '<items>';
      $result .= sprintf(
        '<listitem title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Time'))
      );
      $result .= sprintf(
        '<subitem>%s</subitem>',
        papaya_strings::escapeHTMLChars(
          date('Y-m-d H:i:s', $this->messageList[$selected]['log_time'])
        )
      );
      $result .= '</listitem>';
      $urls = array(
        'URL' => $this->messageList[$selected]['log_msg_uri'],
        'Referer' => $this->messageList[$selected]['log_msg_referer']
      );
      foreach ($urls as $title => $url) {
        $queryParams = NULL;
        if (!empty($url)) {
          $result .= sprintf(
            '<listitem title="%s" hint="%s">',
            $this->_gt($title),
            papaya_strings::escapeHTMLChars($url)
          );
          if (FALSE !== ($pos = strpos($url, '?'))) {
            $queryString = urldecode(substr($url, $pos));
            $queryStringPattern = '~(^|[?&])(([^=&?]+)=([^&]*))|([^&]+)~';
            if (preg_match_all($queryStringPattern, $queryString, $matches, PREG_SET_ORDER)) {
              foreach ($matches as $match) {
                if (isset($match[3]) && $match[3] != '') {
                  $queryParams[] = array($match[3], $match[4]);
                } elseif (isset($match[5])) {
                  $queryParams[] = $match[5];
                }
              }
            }
            $pageURL = substr($url, 0, $pos);
          } else {
            $queryParams = NULL;
            $pageURL = $url;
          }
          $result .= '<subitem>'.papaya_strings::escapeHTMLChars($pageURL).'</subitem>';
          $result .= '</listitem>';
          if (isset($queryParams)) {
            foreach ($queryParams as $queryParam) {
              if (is_array($queryParam)) {
                $result .= sprintf(
                  '<listitem title="%s" indent="1">',
                  papaya_strings::escapeHTMLChars($queryParam[0])
                );
                $result .= '<subitem>'.papaya_strings::escapeHTMLChars($queryParam[1]).'</subitem>';
                $result .= '</listitem>';
              } else {
                $result .= '<listitem title="" indent="1">';
                $result .= '<subitem>'.papaya_strings::escapeHTMLChars($queryParam).'</subitem>';
                $result .= '</listitem>';
              }
            }
          }
        }
      }
      $result .= sprintf(
        '<listitem title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Script'))
      );
      $result .= '<subitem>'.papaya_strings::escapeHTMLChars(
        $this->messageList[$selected]['log_msg_script']
      ).'</subitem>';
      $result .= '</listitem>';
      if (
        trim($this->messageList[$selected]['log_msg_cookies']) != '' &&
        $this->messageList[$selected]['log_time'] < time() - 21600
      ) {
        $result .= sprintf(
          '<listitem title="%s">',
          papaya_strings::escapeHTMLChars($this->_gt('Cookies'))
        );
        $result .= '<subitem>'.papaya_strings::escapeHTMLChars(
          $this->messageList[$selected]['log_msg_cookies']
        ).'</subitem>';
        $result .= '</listitem>';
      };
      if (trim($this->messageList[$selected]['log_msg_from_ip']) != '') {
        $host = $this->getHostNames($this->messageList[$selected]['log_msg_from_ip']);
        if ($host != $this->messageList[$selected]['log_msg_from_ip']) {
          $result .= sprintf(
            '<listitem title="%s">',
            papaya_strings::escapeHTMLChars($this->_gt('Host'))
          );
          $result .= '<subitem>'.papaya_strings::escapeHTMLChars($host).'</subitem>';
          $result .= '</listitem>';
        }
      }
      $result .= sprintf(
        '<listitem title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('IP'))
      );
      $result .= '<subitem>'.papaya_strings::escapeHTMLChars(
        $this->messageList[$selected]['log_msg_from_ip']
      ).'</subitem>';
      $result .= '</listitem>';
      if (!empty($this->messageList[$selected]['username'])) {
        $result .= sprintf(
          '<listitem title="%s">',
          papaya_strings::escapeHTMLChars($this->_gt('Username'))
        );
        $result .= '<subitem>'.papaya_strings::escapeHTMLChars(
          $this->messageList[$selected]['username']
        ).'</subitem>';
        $result .= '</listitem>';
      }
      if (trim($this->messageList[$selected]['log_version_papaya']) != '') {
        $result .= sprintf(
          '<listitem title="papaya CMS %s">',
          papaya_strings::escapeHTMLChars($this->_gt('Version'))
        );
        $result .= '<subitem>'.
          papaya_strings::escapeHTMLChars(
            $this->messageList[$selected]['log_version_papaya']
          ).'</subitem>';
        $result .= '</listitem>';
      }
      if (trim($this->messageList[$selected]['log_version_project']) != '') {
        $result .= sprintf(
          '<listitem title="%s">',
          papaya_strings::escapeHTMLChars($this->_gt('Project version'))
        );
        $result .= '<subitem>'.
          papaya_strings::escapeHTMLChars(
            $this->messageList[$selected]['log_version_project']
          ).'</subitem>';
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->add($result);
      $dom = new \PapayaXmlDocument();
      $dom
        ->appendElement('sheet', array('width' => '100%', 'align' => 'center'))
        ->appendElement('text')
        ->appendElement('div', array('style' => 'padding: 10px;'))
        ->appendXml(
          $data = @\Papaya\Utility\Text\Xml::repairEntities(
            $this->rewrapHTML(
              $this->messageList[$selected]['log_msg_long']
            )
          )
        );
      $this->layout->add($dom->documentElement->saveXml());
    }
  }

  /**
  * Rewrap HTML (do not break inside html tags)
  * @param string $str
  * @return string
  */
  function rewrapHTML($str) {
    $pattern = '~(^|>)([^<]+)~';
    return preg_replace_callback($pattern, array($this, 'rewrapHTMLCallback'), $str);
  }

  /**
  * Rewrap HTML part callback
  * @param array $match
  * @return string
  */
  function rewrapHTMLCallback($match) {
    $width = 80;
    $break = '<span class="allowWrap"> </span>';
    $words = preg_split('~(\r\n|\n\r|[\r\n\s])~', $match[2]);
    $systemIncludePath = dirname(dirname(__FILE__));
    if (is_array($words)) {
      $result = '';
      foreach ($words as $word) {
        if (0 === strpos($word, PAPAYA_INCLUDE_PATH)) {
          $partWord = substr($word, strlen(PAPAYA_INCLUDE_PATH));
          if (papaya_strings::strlen($partWord) > $width) {
            $result .= ' <em>{PAPAYA_INCLUDE_PATH}</em>/'.
              wordwrap($partWord, $width, $break, TRUE);
          } else {
            $result .= ' <em>{PAPAYA_INCLUDE_PATH}</em>/'.$partWord;
          }
        } elseif (isset($_SERVER['DOCUMENT_ROOT']) &&
                  0 === strpos($word, $_SERVER['DOCUMENT_ROOT'])) {
          $partWord = substr($word, strlen($_SERVER['DOCUMENT_ROOT']));
          if (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') {
            $addSlash = '/';
          } else {
            $addSlash = '';
          }
          if (papaya_strings::strlen($partWord) > $width) {
            $result .= ' <em>{DOCUMENT_ROOT}</em>'.$addSlash.
              wordwrap($partWord, $width, $break, TRUE);
          } else {
            $result .= ' <em>{DOCUMENT_ROOT}</em>'.$addSlash.$partWord;
          }
        } elseif (0 === strpos($word, $systemIncludePath)) {
          $partWord = substr($word, strlen($systemIncludePath));
          if (papaya_strings::strlen($partWord) > $width) {
            $result .= ' <em>{PAPAYA_INCLUDE_PATH}</em>'.
              wordwrap($partWord, $width, $break, TRUE);
          } else {
            $result .= ' <em>{PAPAYA_INCLUDE_PATH}</em>'.$partWord;
          }
        } elseif (papaya_strings::strlen($word) > $width) {
          $result .= ' '.wordwrap($word, $width, $break, TRUE);
        } else {
          $result .= ' '.$word;
        }
      }
      return $match[1].substr($result, 1);
    } else {
      return $match[0];
    }
  }

  /**
  * get host names for one or more ips
  *
  * @param string $ipStr
  * @access public
  * @return string
  */
  function getHostNames($ipStr) {
    if (FALSE !== strpos($ipStr, ',')) {
      $ips = explode(',', $ipStr);
      if (is_array($ips) && count($ips)) {
        $result = '';
        foreach ($ips as $ip) {
          $result .= ','.@gethostbyaddr(trim($ip));
        }
        return substr($result, 1);
      }
      return '';
    } else {
      return @gethostbyaddr($ipStr);
    }
  }

  /**
  * Get clear Dialog
  *
  * @access public
  * @return string $result XML
  */
  function getClearDlg() {
    $result = sprintf(
      '<msgdialog action="%s" type="question">',
      papaya_strings::escapeHTMLChars($this->getBaseLink())
    );
    $result .= sprintf(
      '<input type="hidden" name="%s[cmd]" value="clear"/>',
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $result .= sprintf(
      '<input type="hidden" name="%s[confirm]" value="1"/>',
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $result .= sprintf(
      '<input type="hidden" name="%s[level]" value="%d"/>',
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($this->params['level']) ? 0 : (int)$this->params['level']
    );
    $result .= sprintf(
      '<input type="hidden" name="%s[type]" value="%d"/>',
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($this->params['type']) ? 0 : (int)$this->params['type']
    );
    $result .= '<message>';
    if (isset($this->params['type']) && $this->params['type'] > 0) {
      $result .= papaya_strings::escapeHTMLChars(
        $this->_gt('Delete all events of selected type?')
      );
    } else {
      $result .= papaya_strings::escapeHTMLChars(
        $this->_gt('Delete all events?')
      );
    }
    $result .= '</message>';
    $result .= '<dlgbutton value="'.papaya_strings::escapeHTMLChars($this->_gt('Delete')).'"/>';
    $result .= '</msgdialog>';
    return $result;
  }

  /**
  * Get delete old dialog
  *
  * @access public
  * @return string $result XML
  */
  function getDeleteOldDlg() {
    $selected = $this->messageList[$this->selected];
    $result = '';
    if (isset($selected) && is_array($selected)) {
      $result = sprintf(
        '<msgdialog action="%s" type="question" width="100%%">',
        papaya_strings::escapeHTMLChars($this->baseLink)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="del_old"/>',
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[id]" value="%s"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->selected)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[level]" value="%d"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['level']) ? 0 : (int)$this->params['level']
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[type]" value="%d"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['type']) ? 0 : (int)$this->params['type']
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[confirm]" value="1"/>',
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= '<message>';
      $result .= sprintf(
        $this->_gt('Delete older (&lt;%s) events?'),
        date('Y-m-d H:i:s', $selected['log_time'])
      );
      $result .= '</message>';
      $result .= sprintf(
        '<dlgbutton value="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Delete'))
      );
      $result .= '</msgdialog>';
    }
    return $result;
  }

  /**
  * Get XML buttons
  *
  * @access public
  * @return string XML
  */
  function getButtonsXML() {
    $menu = new \PapayaUiMenu();
    $menu->identifier = 'edit';

    $button = new \PapayaUiToolbarButton();
    $button->caption = new \PapayaUiStringTranslated('Login try');
    $button->image = 'categories-log-access';
    $button->reference->setRelative('log_auth.php');
    $menu->elements[] = $button;

    $menu->elements[] = new \PapayaUiToolbarSeparator();

    $select = new \PapayaUiToolbarSelect(array($this->paramName, 'level'), $this->levels);
    $select->caption = new \PapayaUiStringTranslated('Priority');
    $select->defaultValue = $this->selLevel;
    $select->reference->setParameters(
      array('cmd' => 'show', 'type' => $this->params['type']), $this->paramName
    );
    $menu->elements[] = $select;

    $this->messageTypeList[0] = new \PapayaUiStringTranslated('All');
    asort($this->messageTypeList);
    $select = new \PapayaUiToolbarSelect(array($this->paramName, 'type'), $this->messageTypeList);
    $select->caption = new \PapayaUiStringTranslated('Type');
    $select->defaultValue = $this->selType;
    $select->reference->setParameters(
      array('cmd' => 'show', 'level' => $this->params['level']), $this->paramName
    );
    $menu->elements[] = $select;

    $button = new \PapayaUiToolbarButton();
    $button->caption = new \PapayaUiStringTranslated('Refresh');
    $button->image = 'actions-refresh';
    $select->reference->setParameters(
      array('cmd' => 'show', 'type' => $this->params['type'], 'level' => $this->params['level']),
      $this->paramName
    );
    $menu->elements[] = $button;

    $menu->elements[] = new \PapayaUiToolbarSeparator();

    if (isset($this->messageList[$this->selected])) {
      $button = new \PapayaUiToolbarButton();
      $button->caption = new \PapayaUiStringTranslated('Bug Report');
      $button->hint = new \PapayaUiStringTranslated('Report this error message.');
      $button->image = 'items-bug';
      $button->reference->setRelative('help.php');
      $button->reference->setParameters(
        array('ohmode' => 'bugreport', 'log_id' => $this->selected), 'help'
      );
      $menu->elements[] = $button;
      $menu->elements[] = new \PapayaUiToolbarSeparator();

      $button = new \PapayaUiToolbarButton();
      $button->caption = new \PapayaUiStringTranslated('Delete old');
      $button->hint = new \PapayaUiStringTranslated('Delete all events older like this.');
      $button->image = 'places-trash';
      $button->reference->setParameters(
        array('cmd' => 'del_old', 'id' => $this->selected), $this->paramName
      );
      $menu->elements[] = $button;
      $menu->elements[] = new \PapayaUiToolbarSeparator();
    }

    $button = new \PapayaUiToolbarButton();
    if (isset($this->params['type']) && $this->params['type'] > 0) {
      $button->caption = new \PapayaUiStringTranslated('Delete');
      $button->hint = new \PapayaUiStringTranslated('Delete all events of selected type.');
    } else {
      $button->caption = new \PapayaUiStringTranslated('Empty');
      $button->hint = new \PapayaUiStringTranslated('Delete all events.');
    }
    $button->image = 'places-trash';
    $button->reference->setParameters(
      array('cmd' => 'clear'), $this->paramName
    );
    $menu->elements[] = $button;
    $menu->elements[] = new \PapayaUiToolbarSeparator();

    return $menu->getXml();
  }
}
