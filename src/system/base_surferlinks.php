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
* Connect sufer permissions and page
*
* @package Papaya
* @subpackage User-Community
*/
class base_surferlinks extends base_db {
  /**
  * Papaya database table surfer permissions
  * @var string $tableSurferPerm
  */
  var $tableSurferPerm = PAPAYA_DB_TBL_SURFERPERM;
  /**
  * Papaya database table topics
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;
  /**
  * Papaya database table topics translations
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName;
  /**
  * Parameters
  * @var array $params
  */
  var $params;
  /**
  * Base link
  * @var string $baseLink
  */
  var $baseLink;

  /**
  * Surfer perission list
  * @var array $surferPermissionList
  */
  var $surferPermissionList;
  /**
  * Surfer permissions
  * @var array $surferPermissions
  */
  var $surferPermissions;
  /**
  * Live permission
  * @var array $livePerm
  */
  var $livePerm;
  /**
  * Topic list
  * @var array $topicList
  */
  var $topicList;
  /**
  * Used
  * @var mixed $used
  */
  var $used;
  /**
  * Data
  * @var mixed $data
  */
  var $data;

  /**
   * @var array
   */
  public $modeList;

  /**
   * @var array|\Papaya\UI\Images
   */
  public $images = array();

  /**
  * base surfer links
  *
  * @param integer $id
  * @param string $paramName optional, default value 'sl'
  * @access public
  */
  function __construct($id, $paramName='sl') {
    $this->paramName = $paramName;
    $this->initializeParams();
    $this->topicId = (int)$id;
  }

  /**
  * Load mode list
  *
  * @access public
  * @return boolean
  */
  function loadModeList() {
    foreach (base_statictables::getTableAccessStates() as $key => $val) {
      $this->modeList[$key] = $this->_gt($val);
    }
    return TRUE;
  }

  /**
  * Load list
  *
  * @access public
  */
  function loadList() {
    unset($this->surferPermissionList);
    $sql = "SELECT surferperm_id, surferperm_title, surferperm_active
              FROM %s";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableSurferPerm))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->surferPermissionList[$row['surferperm_id']] = $row;
      }
    }
  }

  /**
  * Load function
  *
  * @access public
  */
  function load() {
    unset($this->surferPermissions);
    $sql = "SELECT surfer_useparent, surfer_permids
              FROM %s
             WHERE topic_id = %d";
    $params = array($this->tableTopics, (int)$this->topicId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->surferPermissions = $row;
      }
    }
  }

  /**
  * Load topics
  *
  * @param integer $lngId Sprach Id
  * @access public
  */
  function loadTopics($lngId) {
    unset($this->topicList);
    unset($this->livePerm);
    $sqlFunction = $this->databaseGetSQLSource(
      'LOCATE',
      $this->databaseGetSQLSource(
        'CONCAT', ';', TRUE, 't1.topic_id', FALSE, ';', TRUE
      ),
      FALSE,
      $this->databaseGetSQLSource(
        'CONCAT', 't2.prev_path', FALSE, 't2.prev', FALSE, ';', TRUE
      ),
      FALSE
    );
    $sql = "SELECT t1.topic_id, t1.surfer_useparent, t1.surfer_permids, t1.prev,
                   t2.prev_path, tt.topic_title
              FROM %s t2, %s t1
              LEFT OUTER JOIN %s tt ON (tt.topic_id = t1.topic_id AND tt.lng_id = '%d')
             WHERE (t2.topic_id = %d AND ($sqlFunction != 0) OR  t2.topic_id = %d)";
    $params = array($this->tableTopics, $this->tableTopics,
      $this->tableTopicsTrans, $lngId, $this->topicId, $this->topicId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topicList[$row['topic_id']] = $row;
      }
      $current = $this->topicId;
      while ($current != 0) {
        $rec = $this->topicList[$current]['surfer_useparent'];
        $permString = $this->topicList[$current]['surfer_permids'];
        if ($rec != 2 &&
            preg_match_all('/\d+/', $permString, $matches, PREG_PATTERN_ORDER)) {
          foreach ($matches[0] as $val) {
            if (!(
                  isset($this->livePerm) &&
                  is_array($this->livePerm) &&
                  in_array($val, array_keys($this->livePerm))
                )) {
              $this->livePerm[$val] = $this->topicList[$current]['topic_id'];
            }
          }
        }
        if ($rec == 1) {
          break;
        }
        $current = $this->topicList[$current]['prev'];
      }
    } else {
      $this->addMsg(MSG_WARNING, "Database error!");
    }
  }

  /**
  * Execute - basic class for parameter handling
  *
  * @access public
  */
  function execute() {
    $this->load();
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'chg_mode' :
        return $this->updateSurferPermUse((int)$this->params['mode']);
      case 'add' :
        preg_match_all(
          '/\d+/',
          $this->surferPermissions['surfer_permids'],
          $matches,
          PREG_PATTERN_ORDER
        );
        $argh = array_flip($matches[0]);
        $argh[(int)$this->params['perm_id']] = TRUE;
        return $this->updateSurferPerms(array_keys($argh));
      case 'del' :
        preg_match_all(
          '/\d+/',
          $this->surferPermissions['surfer_permids'],
          $matches,
          PREG_PATTERN_ORDER
        );
        $argh = array_flip($matches[0]);
        unset($argh[(int)$this->params['perm_id']]);
        return $this->updateSurferPerms(array_keys($argh));
      }
    }
    return NULL;
  }

  /**
   * Update surfer permissions
   *
   * @param array $perms
   * @return bool
   * @access public
   */
  function updateSurferPerms($perms) {
    $this->loadList();
    if (isset($perms) && is_array($perms)) {
      $perms = ";".implode(";", $perms).";";
    } else {
      $perms = '';
    }
    $values = array(
      'surfer_permids' => $perms,
      'topic_modified' => time()
    );
    $updated = $this->databaseUpdateRecord(
      $this->tableTopics, $values, 'topic_id', $this->topicId
    );
    if (FALSE !== $updated) {
      $this->addMsg(MSG_INFO, $this->_gtf('%s modified.', $this->_gt('Permissions')));
      return TRUE;
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
      return FALSE;
    }
  }

  /**
   * update surfer permission use
   *
   * @param integer $mode
   * @return bool
   * @access public
   */
  function updateSurferPermUse($mode) {
    $this->loadList();
    $values = array(
      'surfer_useparent' => (int)$mode,
      'topic_modified' => time()
    );
    $updated = $this->databaseUpdateRecord(
      $this->tableTopics, $values, 'topic_id', $this->topicId
    );
    if (FALSE !== $updated) {
      $this->addMsg(MSG_INFO, $this->_gtf('%s modified.', $this->_gt('Permissions')));
      return TRUE;
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
      return FALSE;
    }
  }

  /**
  * Get
  *
  * @param int $lngId
  * @access public
  * @return string
  */
  function get($lngId) {
    $this->loadList();
    $this->loadTopics($lngId);
    return $this->getPermList($this->topicList[$this->topicId]['surfer_useparent']);
  }

  /**
  * Get permission list
  *
  * @param integer $mode
  * @access public
  * @return string
  */
  function getPermList($mode) {
    unset($inhPerm);
    unset($ownPerm);
    unset($avPerm);
    $result = '';
    if (isset($this->surferPermissionList) && is_array($this->surferPermissionList)) {
      foreach ($this->surferPermissionList as $perm) {
        if (isset($this->livePerm[(int)$perm['surferperm_id']]) &&
            $this->livePerm[(int)$perm['surferperm_id']] != $this->topicId) {
          $inhPerm[$perm['surferperm_id']] = $this->livePerm[$perm['surferperm_id']];
        } elseif (isset($this->livePerm[(int)$perm['surferperm_id']])) {
          $ownPerm[$perm['surferperm_id']] = TRUE;
        } else {
          $avPerm[$perm['surferperm_id']] = TRUE;
        }
      }
    }
    if (isset($ownPerm) && is_array($ownPerm) && (($mode == 1) || ($mode == 3))) {
      $result .= sprintf(
        '<listview title="%s" width="100%%">',
        papaya_strings::escapeHTMLChars($this->_gt('Linked permissions'))
      );
      $result .= '<items>';
      foreach ($ownPerm as $id => $dummy) {
        $result .= sprintf(
          '<listitem title="%s" image="%s">',
          papaya_strings::escapeHTMLChars($this->surferPermissionList[$id]['surferperm_title']),
          papaya_strings::escapeHTMLChars($this->images['items-permission'])
        );
        $result .= '<subitem align="right">';
        $result .= sprintf(
          '<a href="%s"><glyph src="%s" hint="%s"/></a>',
          $this->getLink(
            array(
              'page_id' => $this->topicId,
              'cmd' => 'del',
              'perm_id' => $this->surferPermissionList[$id]['surferperm_id']
            )
          ),
          papaya_strings::escapeHTMLChars($this->images['actions-list-remove']),
          papaya_strings::escapeHTMLChars($this->_gt('Delete'))
        );
        $result .= '</subitem>';
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
    }
    if (isset($inhPerm) && is_array($inhPerm) && (($mode == 2) || ($mode == 3))) {
      $result .= sprintf(
        '<listview title="%s" width="100%%">',
        papaya_strings::escapeHTMLChars($this->_gt('Inherited permissions'))
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Permission'))
      );
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Page'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($inhPerm as $id => $pid) {
        $result .= sprintf(
          '<listitem title="%s" image="%s">',
          papaya_strings::escapeHTMLChars($this->surferPermissionList[$id]['surferperm_title']),
          papaya_strings::escapeHTMLChars($this->images['status-permission-inherited'])
        );
        if ($this->topicList[$pid]['topic_title'] != '') {
          $title = papaya_strings::escapeHTMLChars($this->topicList[$pid]['topic_title']);
        } else {
          $title = '<i>'.papaya_strings::escapeHTMLChars($this->_gt('No title')).'</i>';
        }
        $result .= sprintf(
          '<subitem><a href="%s">%s</a></subitem>',
          $this->getLink(array('page_id' => $pid)),
          $title
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
    }
    if (isset($avPerm) && is_array($avPerm) && (($mode == 1) || ($mode == 3))) {
      $result .= sprintf(
        '<listview title="%s" width="100%%">',
        papaya_strings::escapeHTMLChars($this->_gt('Avaliable permissions'))
      );
      $result .= '<items>';
      foreach ($avPerm as $id => $dummy) {
        $result .= sprintf(
          '<listitem title="%s" image="%s">',
          papaya_strings::escapeHTMLChars($this->surferPermissionList[$id]['surferperm_title']),
          papaya_strings::escapeHTMLChars($this->images['items-permission'])
        );
        $result .= '<subitem align="right">';
        $result .= sprintf(
          '<a href="%s"><glyph src="%s" hint="%s"/></a>',
          $this->getLink(
            array(
              'page_id' => $this->topicId,
              'cmd' => 'add',
              'perm_id' => $this->surferPermissionList[$id]['surferperm_id']
            )
          ),
          papaya_strings::escapeHTMLChars($this->images['actions-list-add']),
          papaya_strings::escapeHTMLChars($this->_gt('Link'))
        );
        $result .= '</subitem>';
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
    }
    return $result;
  }

  /**
  * Get mode dialog
  *
  * @access public
  * @return string
  */
  function getModeDlg() {
    $result = '';
    $this->loadModeList();
    $actId = $this->topicList[$this->topicId]['surfer_useparent'];
    if (isset($this->modeList) && is_array($this->modeList)) {
      $result = sprintf(
        '<dialog action="%s" title="%s" width="100%%">',
        papaya_strings::escapeHTMLChars($this->baseLink),
        papaya_strings::escapeHTMLChars($this->_gt('Permission inheritance'))
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="chg_mode" />',
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[page_id]" value="%d" />',
        papaya_strings::escapeHTMLChars($this->paramName),
        (int)$this->topicId
      );
      $result .= '<lines class="dialogSmall">';
      $result .= sprintf(
        '<line caption="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Mode'))
      );
      $result .= sprintf(
        '<select name="%s[mode]" class="dialogSelect dialogScale">',
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      foreach ($this->modeList as $id => $mode) {
        $selected = ($id == $actId) ? 'selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>',
          papaya_strings::escapeHTMLChars($id),
          $selected,
          papaya_strings::escapeHTMLChars($mode)
        );
      }
      $result .= '</select>';
      $result .= '</line>';
      $result .= '</lines>';
      $result .= sprintf(
        '<dlgbutton value="%s" />',
        papaya_strings::escapeHTMLChars($this->_gt('Edit'))
      );
      $result .= '</dialog>';
    }
    return $result;
  }
}

