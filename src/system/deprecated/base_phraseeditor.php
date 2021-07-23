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
* basic class translation engine
*
* @package Papaya
* @subpackage Administration
*/
class base_phraseeditor extends base_db {
  /**
  * Papaya database table phrase
  * @var string $tablePhrase
  */
  var $tablePhrase = PAPAYA_DB_TBL_PHRASE;
  /**
  * Papaya database table phrase translations
  * @var string $tableTranslation
  */
  var $tableTranslation = PAPAYA_DB_TBL_PHRASE_TRANS;
  /**
  * Papaya database table phrase module
  * @var string $tableModule
  */
  var $tableModule = PAPAYA_DB_TBL_PHRASE_MODULE;
  /**
  * Papaya database table phrase module relation
  * @var string $tableModuleRelation
  */
  var $tableModuleRelation = PAPAYA_DB_TBL_PHRASE_MODULE_REL;
  /**
  * Papaya database table language
  * @var string $tableLanguage
  */
  var $tableLanguage = PAPAYA_DB_TBL_LNG;
  /**
  * Papaya database table phrase log
  * @var string $tableLog
  */
  var $tableLog = PAPAYA_DB_TBL_PHRASE_LOG;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'phr';
  /**
  * Session parameter name
  * @var string $sessionParamName
  */
  var $sessionParamName = 'PAPAYA_SESS_phr';
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
  * Message steps
  * @var integer $messageSteps
  */
  var $messageSteps = 10;
  /**
  * Phrase steps
  * @var integer $phraseSteps
  */
  var $phraseSteps = 20;
  /**
  * Message absolute count
  * @var integer $messageAbsCount
  */
  var $messageAbsCount = 0;
  /**
  * Phrase absolute count
  * @var integer $phraseAbsCount
  */
  var $phraseAbsCount = 0;

  /**
   * @var NULL|array $phrase
   */
  public $phrase = NULL;

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
   * @var array
   */
  private $logMessages = array();

  /**
   * @var array
   */
  private $languages = array();

  /**
   * @var array
   */
  private $modules = array();

  /**
   * @var array
   */
  private $phrases = array();

  /**
  * Initialize parameters
  *
  * @param string $paramName optional, default value 'phr'
  * @access public
  */
  function initialize($paramName = 'phr') {
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_phrases_'.$paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('msgoffset');
    $this->initializeSessionParam('phroffset');
    if (isset($this->params['clear_filter'])) {
      $this->params['filter'] = '';
    }
    $this->initializeSessionParam(
      'filter', array('phroffset')
    );
    $this->initializeSessionParam(
      'module_id', array('msgoffset', 'phroffset')
    );
    $this->initializeSessionParam(
      'lng_id', array('msgoffset', 'phroffset')
    );
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Baic function for handling parameters
  *
  * @access public
  */
  function execute() {
    $this->loadLanguages();
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    switch ($this->params['cmd']) {
    case 'emptymodules':
      if ($this->emptyModules()) {
        $this->addMsg(MSG_INFO, $this->_gt('Optimisation flushed.'));
      }
      break;
    case 'emptylog':
      if ($this->emptyLog()) {
        $this->addMsg(
          MSG_INFO,
          $this->_gt('Protocol of translation engine flushed.')
        );
      }
      break;
    case 'delmsg':
      if ($this->deleteLogMsg($this->params['log_msg_id'])) {
        $this->addMsg(MSG_INFO, $this->_gt('Message deleted.'));
      }
      break;
    case 'add':
      if ($new = $this->addPhrase(empty($this->params['phrase']) ? '' : $this->params['phrase'])) {
        $this->params['phrase_id'] = $new;
        unset($this->params['msgoffset']);
        unset($this->params['phroffset']);
        unset($this->params['filter']);
        $this->sessionParams['msgoffset'] = 0;
        $this->sessionParams['phroffset'] = 0;
        $this->sessionParams['filter'] = '';
        $this->addMsg(MSG_INFO, $this->_gt('Phrase added.'));
      }
      break;
    case 'del':
      if (isset($this->params['phrase_id'])) {
        $this->loadPhrase($this->params['phrase_id'], FALSE);
        if ($this->deletePhrase()) {
          unset($this->params['phrase_id']);
          unset($this->params['phrase']);
          unset($this->params['cmd']);
          unset($this->phrase);
          $this->addMsg(MSG_INFO, $this->_gt('Phrase deleted.'));
        }
      }
      break;
    case 'save':
      if (isset($this->params['phrase_id'])) {
        if ($this->loadPhrase($this->params['phrase_id'], FALSE)) {
          if ($this->saveTranslations()) {
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
          }
        }
      }
      break;
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    $this->loadPhrases($this->params['module_id']);
    if (isset($this->params['phrase_id'])) {
      $this->loadPhrase($this->params['phrase_id'], FALSE);
    }
    $this->loadModules();
    $this->loadLogMessages();
  }

  /**
  * Get XML
  *
  * @access public
  */
  function getXML() {
    if (is_object($this->layout)) {
      $this->layout->parameters()->set('COLUMNWIDTH_CENTER', '50%');
      $this->layout->parameters()->set('COLUMNWIDTH_RIGHT', '50%');
      $this->getXMLButtons();
      $this->getFilterPanel();
      $this->getLogMsgList();
      $this->getPhrasesList();
      if (!isset($this->params['cmd'])) {
        $this->params['cmd'] = '';
      }
      switch ($this->params['cmd']) {
      case 'del':
        $this->getDelForm();
        break;
      case 'add':
        $this->getAddForm();
        break;
      default:
        $this->getEditForm();
      }
    }
  }

  /**
  * load phrases
  *
  * @param integer $moduleId
  * @access public
  * @return boolean
  */
  function loadPhrases($moduleId) {
    unset($this->phrases);
    if (!empty($this->params['filter'])) {
      $parser = new searchStringParser();
      if ($this->params['lng_id'] > 0) {
        $fields = array('p.phrase_text');
      } else {
        $fields = array('p.phrase_text', 'pt.translation');
      }
      $letterFilter = $parser->getSQL($this->params['filter'], $fields);
      if ($letterFilter) {
        $letterFilter = " AND ".str_replace('%', '%%', $letterFilter);
      }
    } else {
      $letterFilter = '';
    }
    $countSql = FALSE;
    if (($moduleId > 0) && ($this->params['lng_id'] > 0)) {
      $sql = "SELECT DISTINCT p.phrase_id, p.phrase_text, p.phrase_text_lower
              FROM (%s mr, %s p)
              LEFT OUTER JOIN %s pt
                ON (pt.lng_id = %d AND pt.phrase_id = p.phrase_id)
              WHERE mr.module_id = '%d'
                AND p.phrase_id = mr.phrase_id
                AND pt.lng_id IS NULL
                $letterFilter
              ORDER BY p.phrase_text_lower ASC";
      $countSql = "SELECT COUNT(DISTINCT p.phrase_id)
              FROM (%s mr, %s p)
              LEFT OUTER JOIN %s pt
                ON (pt.lng_id = %d AND pt.phrase_id = p.phrase_id)
              WHERE mr.module_id = '%d'
                AND p.phrase_id = mr.phrase_id
                AND pt.lng_id IS NULL
                $letterFilter";
      $params = array($this->tableModuleRelation,
                      $this->tablePhrase,
                      $this->tableTranslation,
                      (int)$this->params['lng_id'],
                      (int)$moduleId);
    } elseif ($this->params['lng_id'] > 0) {
      $sql = "SELECT p.phrase_id, p.phrase_text, p.phrase_text_lower
                FROM %s p
                LEFT OUTER JOIN %s pt
                  ON (pt.lng_id = %d AND pt.phrase_id = p.phrase_id)
               WHERE pt.lng_id IS NULL
                $letterFilter
               ORDER BY p.phrase_text_lower ASC";
      $params = array(
        $this->tablePhrase,
        $this->tableTranslation,
        (int)$this->params['lng_id']
      );
    } elseif ($moduleId > 0) {
      $sql = "SELECT DISTINCT p.phrase_id, p.phrase_text, p.phrase_text_lower
                FROM %s mr
                LEFT JOIN %s p ON p.phrase_id = mr.phrase_id
                LEFT OUTER JOIN %s pt ON (pt.phrase_id = p.phrase_id)
               WHERE mr.module_id = '%d'
                $letterFilter
               ORDER BY p.phrase_text_lower ASC";
      $countSql = "SELECT COUNT(DISTINCT p.phrase_id)
                FROM %s mr
                LEFT JOIN %s p ON p.phrase_id = mr.phrase_id
                LEFT OUTER JOIN %s pt ON (pt.phrase_id = p.phrase_id)
               WHERE mr.module_id = '%d'
                $letterFilter
               ORDER BY p.phrase_text_lower ASC";
      $params = array(
        $this->tableModuleRelation,
        $this->tablePhrase,
        $this->tableTranslation,
        (int)$moduleId
      );
    } else {
      $sql = "SELECT p.phrase_id, p.phrase_text, p.phrase_text_lower
                FROM %s p
                LEFT OUTER JOIN %s pt ON (pt.phrase_id = p.phrase_id)
               WHERE 1 = 1
                $letterFilter
               ORDER BY p.phrase_text_lower ASC";
      $params = array($this->tablePhrase, $this->tableTranslation);
    }
    $res = $this->databaseQueryFmt(
      $sql,
      $params,
      $this->phraseSteps,
      empty($this->params['phroffset']) ? 0 : (int)$this->params['phroffset']
    );
    if ($res) {
      $ids = NULL;
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->phrases[$row['phrase_id']] = $row;
        $ids[] = (int)$row['phrase_id'];
      }
      if ($countSql) {
        $res = $this->databaseQueryFmt($countSql, $params);
        $this->phraseAbsCount = $res->fetchField();
      } else {
        $this->phraseAbsCount = $res->absCount();
      }
      if (isset($ids) && is_array($ids) && (count($ids) > 0)) {
        $filter = (count($ids) > 1) ? ' IN ('.implode(',', $ids).')' : ' = '.$ids[0];
        $sql = "SELECT pt.phrase_id, pt.translation, pt.lng_id
                  FROM %s pt
                 WHERE pt.phrase_id $filter
                 ORDER BY pt.lng_id ASC";
        $params = array($this->tableTranslation);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            if (trim($row['translation']) != '') {
              $this->phrases[$row['phrase_id']]['phrases'][$row['lng_id']] =
                $row['translation'];
            }
          }
        }
      }
      unset($ids);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load phrase
  *
  * @param integer $id
  * @param boolean $useDatabase optional, default value FALSE
  * @access public
  * @return boolean
  */
  function loadPhrase($id, $useDatabase = FALSE) {
    unset($this->phrase);
    if ((!$useDatabase) && isset($this->phrases[(int)$id])) {
      $this->phrase = $this->phrases[(int)$id];
      return TRUE;
    } else {
      $sql = "SELECT p.phrase_id, p.phrase_text, pt.translation, pt.lng_id
                FROM %s p
                LEFT OUTER JOIN %s pt ON pt.phrase_id = p.phrase_id
               WHERE p.phrase_id = '%d'";
      $params = array($this->tablePhrase, $this->tableTranslation, (int)$id);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->phrase['phrase_id'] = $row['phrase_id'];
          $this->phrase['phrase_text'] = $row['phrase_text'];
          if (trim($row['translation']) != '') {
            $this->phrase['phrases'][$row['lng_id']] = $row['translation'];
          }
        }
        return (isset($this->phrase) && is_array($this->phrase));
      }
    }
    return FALSE;
  }

  /**
  * Add phrase
  *
  * @param string $newPhrase
  * @access public
  * @return boolean
  */
  function addPhrase($newPhrase) {
    $phrase = trim($newPhrase);
    if ($phrase != '') {
      $data['phrase_text'] = $phrase;
      $data['phrase_text_lower'] = strToLower($phrase);
      $sql = "SELECT phrase_id
                FROM %s
               WHERE phrase_text_lower = '%s'";
      $params = array($this->tablePhrase, $data['phrase_text_lower']);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          return FALSE;
        }
      }
      if ($new = $this->databaseInsertRecord($this->tablePhrase, 'phrase_id', $data)) {
        $this->cleanLog($phrase);
        return $new;
      }
    }
    return FALSE;
  }

  /**
  * Delete phrase and its translations
  *
  * @access public
  * @return boolean
  */
  function deletePhrase() {
    if (isset($this->params['confirm_delete']) && is_array($this->phrase)) {
      // delete phrases from translation table
      if (
        FALSE !== $this->databaseDeleteRecord(
          $this->tableTranslation, 'phrase_id', $this->phrase['phrase_id']
        )
      ) {
        // now delete phrase
        return FALSE !== $this->databaseDeleteRecord(
          $this->tablePhrase, 'phrase_id', $this->phrase['phrase_id']
        );

      }
    }
    return FALSE;
  }

  /**
  * Save translations
  *
  * @access public
  * @return boolean
  */
  function saveTranslations() {
    if (isset($this->languages) &&
        is_array($this->languages) && isset($this->phrase)) {
      $result = TRUE;
      if (($this->phrase['phrase_text'] != $this->params['phrase']) &&
          (trim($this->params['phrase']) != '')) {
        $values = array(
          'phrase_text' => $this->params['phrase'],
          'phrase_text_lower' => strtolower($this->params['phrase'])
        );
        return FALSE !== $this->databaseUpdateRecord(
          $this->tablePhrase, $values, 'phrase_id', (int)$this->params['phrase_id']
        );
      }
      foreach ($this->languages as $lngId => $lng) {
        $old = empty($this->phrase['phrases'][$lngId])
          ? '' : trim($this->phrase['phrases'][$lngId]);
        $new = empty($this->params['trans'][$lngId])
          ? '' : trim($this->params['trans'][$lngId]);
        $res = FALSE;
        if ($old != $new) {
          if (($old != '') && ($new != '')) {
            $data = array('translation' => $new);
            $condition = array(
              'lng_id' => (int)$lngId,
              'phrase_id' => (int)$this->phrase['phrase_id'],
            );
            $result = (
              FALSE !== $this->databaseUpdateRecord(
                $this->tableTranslation, $data, $condition
              )
            );
          } elseif ($new != '') {
            $values = array('translation' => $new,
                            'lng_id' => (int)$lngId,
                            'phrase_id' => (int)$this->phrase['phrase_id']
                      );
            $res = $this->databaseInsertRecord($this->tableTranslation, NULL, $values);
          } elseif ($old != '') {
            $condition = array(
              'lng_id' => (int)$lngId,
              'phrase_id' => (int)$this->phrase['phrase_id'],
            );
            $res = $this->databaseDeleteRecord($this->tableTranslation, $condition);
          } else {
            continue;
          }
          if ($res) {
            $result = FALSE;
          } else {
            $this->cleanLog('', $this->phrase['phrase_id']);
          }
        }
      }
      return $result;
    }
    return FALSE;
  }

  /**
  * Clear module links
  *
  * @see base_db:databaseQueryFmt
  * @param mixed $module optional, default value NULL
  * @param mixed $phrase optional, default value NULL
  * @access public
  * @return mixed
  */
  function clearModuleLinks($module = NULL, $phrase = NULL) {
    $condition = array(1 => 1);
    if (isset($module)) {
      $condition['module_id'] = (int)$module;
    }
    if (isset($phrase)) {
      $condition['phrase_id'] = (int)$phrase;
    }
    return $this->databaseDeleteRecord($this->tableModuleRelation, $condition);
  }

  /**
  * Load modules
  *
  * @access public
  * @return boolean
  */
  function loadModules() {
    unset($this->modules);
    $sql = "SELECT module_id, module_title
              FROM %s
             ORDER BY module_title_lower ASC";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableModule))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->modules[$row['module_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load languages
  *
  * @access public
  * @return boolean
  */
  function loadLanguages() {
    $this->languages = array();
    $sql = "SELECT lng_id, lng_ident, lng_short, lng_title, lng_glyph
              FROM %s
             WHERE is_interface_lng = 1
             ORDER BY lng_title ASC";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableLanguage))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (preg_match('~^\w+-(\w+\.(gif|svg))~', $row['lng_glyph'], $match)) {
          $row['lng_glyph'] = $match[1];
        }
        $this->languages[$row['lng_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load log messages
  *
  * @access public
  * @return boolean
  */
  function loadLogMessages() {
    unset($this->logMessages);
    $sql = "SELECT log_id, log_msg, log_phrase_id, log_phrase, log_module, log_datetime
              FROM %s
             ORDER BY log_id DESC";
    $offset = (isset($this->params['msgoffset'])) ?
      (int)$this->params['msgoffset'] : 0;
    if ($res = $this->databaseQueryFmt($sql, $this->tableLog, $this->messageSteps, (int)$offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->logMessages[$row['log_id']] = $row;
      }
      $this->messageAbsCount = $res->absCount();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Empty log
  *
  * @access public
  * @return boolean
  */
  function emptyLog() {
    return $this->databaseEmptyTable($this->tableLog);
  }

  /**
  * Empty modules
  *
  * @access public
  * @return boolean
  */
  function emptyModules() {
    return (
      $this->databaseEmptyTable($this->tableModuleRelation) &&
      $this->databaseEmptyTable($this->tableModule)
    );
  }

  /**
  * Clean log
  *
  * @param string $phrase optional, default value ''
  * @param mixed $phraseId optional, default value NULL
  * @access public
  * @return boolean
  */
  function cleanLog($phrase = '', $phraseId = NULL) {
    if (isset($phraseId) && $phraseId > 0) {
      return $this->databaseDeleteRecord($this->tableLog, 'log_phrase_id', (int)$phraseId);
    } elseif (trim($phrase) != '') {
      return $this->databaseDeleteRecord($this->tableLog, 'log_phrase', $phrase);
    } else {
      return FALSE;
    }
  }

  /**
  * Delete log message
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function deleteLogMsg($id) {
    $sql = "SELECT log_phrase, log_phrase_id
              FROM %s
             WHERE log_id = %d";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableLog, $id))) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (trim($row['log_phrase']) != '') {
          return $this->cleanLog($row['log_phrase']);
        } else {
          return FALSE !== $this->databaseDeleteRecord(
            $this->tableLog, 'log_id', (int)$id
          );
        }
      }
    }
    return FALSE;
  }

  /**
  * get XML buttons
  *
  * @access public
  */
  function getXMLButtons() {
    $toolbar = new base_btnbuilder;
    $toolbar->images = $this->papaya()->images;
    $toolbar->addButton(
      'Languages',
      $this->getLink(array('pagemode' => 1)),
      'items-translation',
      '',
      isset($this->params['pagemode']) && $this->params['pagemode'] == 1
    );
    $toolbar->addButton(
      'Phrases',
      $this->getLink(array('pagemode' => 0, 'lng_id' => 0)),
      'categories-phrase-database',
      '',
      empty($this->params['pagemode']) || $this->params['pagemode'] != 1
    );
    $toolbar->addSeperator();
    $toolbar->addButton(
      'Clear log',
      $this->getLink(array('cmd' => 'emptylog')),
      'actions-edit-clear',
      'Clear log',
      FALSE
    );
    $toolbar->addButton(
      'Clear modules',
      $this->getLink(array('cmd' => 'emptymodules')),
      'actions-edit-clear',
      'Reset Optimisation',
      FALSE
    );
    $toolbar->addSeperator();
    $toolbar->addCombo(
      'Module',
      $this->baseLink,
      $this->paramName.'[module_id]',
      empty($this->params['module_id']) ? '' : $this->params['module_id'],
      $this->getModuleComboItems()
    );
    $toolbar->addCombo(
      'Language',
      $this->baseLink,
      $this->paramName.'[lng_id]',
      empty($this->params['lng_id']) ? '' : $this->params['lng_id'],
      $this->getLngComboItems()
    );
    $toolbar->addSeperator();
    $toolbar->addButton(
      'Add phrase',
      $this->getLink(array('cmd' => 'add')),
      'actions-phrase-add',
      '',
      FALSE
    );
    if (isset($this->phrase) && is_array($this->phrase)) {
      $toolbar->addButton(
        'Delete phrase',
        $this->getLink(array('cmd' => 'del', 'phrase_id' => (int)$this->phrase['phrase_id'])),
        'actions-phrase-delete',
        '',
        FALSE
      );
    }
    if ($result = $toolbar->getXML()) {
      $this->layout->add('<menu>'.$result.'</menu>', 'menus');
    }
  }

  /**
  * Get language combo items
  *
  * @access public
  * @return array
  */
  function getLngComboItems() {
    $result[0] = $this->_gt('All');
    if (isset($this->languages) && is_array($this->languages)) {
      foreach ($this->languages as $lngId => $lng) {
        $result[$lngId] = $lng['lng_title'].' ('.$lng['lng_short'].')';
      }
    }
    return $result;
  }

  /**
  * Get module combo items
  *
  * @access public
  * @return array
  */
  function getModuleComboItems() {
    $result[0] = $this->_gt('All');
    if (isset($this->modules) && is_array($this->modules)) {
      foreach ($this->modules as $modId => $mod) {
        $result[$modId] = $mod['module_title'];
      }
    }
    return $result;
  }

  /**
  * Get pages navigation
  *
  * @param integer $offset current offset
  * @param integer $step offset step
  * @param integer $max max offset
  * @param integer $groupCount page link count
  * @param string $paramName offset param name
  * @return string
  */
  private function getListViewNav($offset, $step, $max, $groupCount = 9, $paramName = 'offset') {
    return papaya_paging_buttons::getPagingButtons(
      $this, array('cmd' => 'show'), $offset, $step, $max, $groupCount, $paramName
    );
  }

  /**
  * Get phrases list
  *
  * @access public
  */
  function getPhrasesList() {
    if (isset($this->phrases) && is_array($this->phrases)) {
      if (isset($this->modules[$this->params['module_id']]['module_title'])) {
        $moduleTitle = $this->modules[$this->params['module_id']]['module_title'];
      } else {
        $moduleTitle = $this->_gt('All');
      }
      $result = sprintf(
        '<listview title="%s [%s]" width="100%%">',
        papaya_strings::escapeHTMLChars($this->_gt('Phrases')),
        papaya_strings::escapeHTMLChars($moduleTitle)
      );
      $result .= $this->getListViewNav(
        empty($this->params['phroffset']) ? 0 : (int)$this->params['phroffset'],
        $this->phraseSteps,
        $this->phraseAbsCount,
        11,
        'phroffset'
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Phrase'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Languages'))
      );
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      foreach ($this->phrases as $id => $phrase) {
        if ($this->params['lng_id'] == 0 ||
            (!isset($phrase['phrases'][$this->params['lng_id']]))) {
          if (isset($this->params['phrase_id']) && $this->params['phrase_id'] == $id) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $result .= sprintf(
            '<listitem href="%s" title="%s"%s>',
            papaya_strings::escapeHTMLChars($this->getLink(array('phrase_id' => $id))),
            papaya_strings::escapeHTMLChars($phrase['phrase_text']),
            $selected
          );
          $result .= '<subitem align="center">';
          if (isset($phrase['phrases']) && is_array($phrase['phrases'])) {
            foreach ($phrase['phrases'] as $lngId => $translation) {
              if (isset($this->languages[$lngId]) &&
                  is_array($this->languages[$lngId])) {
                $lng = $this->languages[$lngId];
                if ($lng['lng_glyph'] !== '') {
                  $result .= sprintf(
                    '<glyph src="./i18n-icon.%s" hint="%s"/>  ',
                    papaya_strings::escapeHTMLChars($lng['lng_glyph']),
                    papaya_strings::escapeHTMLChars($translation)
                  );
                } elseif ($lng['lng_ident'] != '') {
                  $result .= sprintf(
                    '<span title="%s">%s</span> ',
                    papaya_strings::escapeHTMLChars($translation),
                    papaya_strings::escapeHTMLChars($lng['lng_ident'])
                  );
                } else {
                  $result .= sprintf(
                    '<span title="%s">%s</span> ',
                    papaya_strings::escapeHTMLChars($translation),
                    papaya_strings::escapeHTMLChars($lng['lng_short'])
                  );
                }
              } else {
                $result .= sprintf(
                  '<span title="%s">XX</span> ',
                  papaya_strings::escapeHTMLChars($translation)
                );
              }
            }
          }
          $result .= '</subitem>';
          $result .= '</listitem>';
        }
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->add($result);
    }
  }

  /**
  * generate filter dialog and add to layout object
  * @return void
  */
  function getFilterPanel() {
    $data = array(
      'filter' => empty($this->params['filter']) ? '' : $this->params['filter']
    );
    $fields = array(
      'filter' => array('Text', 'isSomething', FALSE, 'input', 200)
    );
    $dialog = new base_dialog($this, $this->paramName, $fields, $data, array());
    $dialog->loadParams();
    $dialog->dialogTitle = $this->_gt('Filter phrases');
    $dialog->buttonTitle = 'Filter';
    $dialog->inputFieldSize = 'x-small';
    $dialog->addButton('clear_filter', 'Clear');
    $this->layout->add($dialog->getDialogXML());
  }

  /**
  * Get log message list
  *
  * @access public
  */
  function getLogMsgList() {
    if (isset($this->logMessages) && is_array($this->logMessages)) {
      $images = $this->papaya()->images;
      $result = sprintf(
        '<listview title="%s" width="100%%">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Messages'))
      );
      $result .= $this->getListViewNav(
        empty($this->params['msgoffset']) ? 0 : (int)$this->params['msgoffset'],
        $this->messageSteps,
        $this->messageAbsCount,
        11,
        'msgoffset'
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Message'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Time'))
      );
      $result .= '<col/>'.LF;
      $result .= '<col/>'.LF;
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      foreach ($this->logMessages as $id => $msg) {
        $result .= sprintf(
          '<listitem title="%s">'.LF,
          papaya_strings::escapeHTMLChars($msg['log_msg'])
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>'.LF,
          date('Y-m-d H:i:s', $msg['log_datetime'])
        );
        $result .= '<subitem align="center">';
        if ($msg['log_phrase_id'] > 0) {
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" hint="%s"/></a>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('phrase_id' => $msg['log_phrase_id']))
            ),
            papaya_strings::escapeHTMLChars($images['actions-edit']),
            papaya_strings::escapeHTMLChars($this->_gt('Edit'))
          );
        } elseif (trim($msg['log_phrase'] != '')) {
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" hint="%s"/></a>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('phrase' => $msg['log_phrase'], 'cmd' => 'add'))
            ),
            papaya_strings::escapeHTMLChars($images['actions-phrase-add']),
            papaya_strings::escapeHTMLChars($this->_gt('Add'))
          );
        }
        $result .= '</subitem>'.LF;
        $result .= '<subitem align="center">'.LF;
        $result .= sprintf(
          '<a href="%s"><glyph src="%s" hint="%s"/></a>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('log_msg_id' => $id, 'cmd' => 'delmsg'))
          ),
          papaya_strings::escapeHTMLChars($images['places-trash']),
          papaya_strings::escapeHTMLChars($this->_gt('Delete'))
        );
        $result .= '</subitem>'.LF;
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->add($result);
    }
  }

  /**
  * Get edit form
  *
  * @access public
  */
  function getEditForm() {
    if (isset($this->phrase) && is_array($this->phrase)) {
      $result = sprintf(
        '<dialog action="%s" method="post" title="%s" width="100%%">'.LF,
        papaya_strings::escapeHTMLChars($this->baseLink),
        papaya_strings::escapeHTMLChars($this->_gt('Translations'))
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="save"/>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[phrase_id]" value="%d"/>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        (int)$this->phrase['phrase_id']
      );
      $result .= '<lines class="dialogLarge">'.LF;
      $result .= sprintf(
        '<line caption="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Phrase'))
      );
      $result .= sprintf(
        '<input type="text" name="%s[phrase]" value="%s" class="dialogInput dialogScale" />',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->phrase['phrase_text'])
      );
      $result .= '</line>'.LF;
      if (isset($this->languages) && is_array($this->languages)) {
        foreach ($this->languages as $lngId => $lng) {
          $result .= sprintf(
            '<line caption="%s">',
            papaya_strings::escapeHTMLChars($lng['lng_title'])
          );
          $result .= sprintf(
            '<input type="text" name="%s[trans][%d]" value="%s" class="dialogInput dialogScale" />',
            papaya_strings::escapeHTMLChars($this->paramName),
            $lngId,
            empty($this->phrase['phrases'][$lngId])
              ? '' : papaya_strings::escapeHTMLChars($this->phrase['phrases'][$lngId])
          );
          $result .= '</line>'.LF;
        }
      }
      $result .= '</lines>'.LF;
      $result .= sprintf(
        '<dlgbutton value="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Change'))
      );
      $result .= '</dialog>'.LF;
      $this->layout->addRight($result);
    }
  }

  /**
  * Get del form
  *
  * @access public
  */
  function getDelForm() {
    if (isset($this->phrase) && is_array($this->phrase) &&
        isset($this->params['cmd']) && $this->params['cmd'] == 'del') {
      $hidden = array(
        'cmd' => 'del',
        'phrase_id' => (int)$this->phrase['phrase_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf($this->_gt('Delete phrase "%s"?'), $this->phrase['phrase_text']);
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->buttonTitle = 'Delete';
      $this->layout->addRight($dialog->getMsgDialog());
    }
  }

  /**
  * get add form
  *
  * @access public
  */
  function getAddForm() {
    if ($this->params['cmd'] = 'add' && (!(isset($this->phrase) && is_array($this->phrase)))) {
      $result = sprintf(
        '<dialog action="%s" method="post" title="%s" width="100%%">'.LF,
        papaya_strings::escapeHTMLChars($this->baseLink),
        papaya_strings::escapeHTMLChars($this->_gt('Add phrase'))
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="add"/>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= '<lines class="dialogLarge">'.LF;
      $result .= sprintf(
        '<line caption="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Phrase'))
      );
      $result .= sprintf(
        '<input type="text" name="%s[phrase]" value="%s" class="dialogInput dialogScale" />',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->phrase['phrase_text'])
          ? '' : papaya_strings::escapeHTMLChars($this->phrase['phrase_text'])
      );
      $result .= '</line>'.LF;
      $result .= '</lines>'.LF;
      $result .= sprintf(
        '<dlgbutton value="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Add'))
      );
      $result .= '</dialog>';
      $this->layout->addRight($result);
    }
  }
}

