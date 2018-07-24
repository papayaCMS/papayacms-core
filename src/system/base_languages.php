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
* Language management UI and Content
*
* @package Papaya
* @subpackage Core
*/
class base_languages extends base_db {

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'phr';

  /**
  * Papaya database table language
  * @var string $tableLanguage
  */
  var $tableLanguage = PAPAYA_DB_TBL_LNG;
  /**
  * Papaya database table translation
  * @var string $tableTranslation
  */
  var $tableTranslation = PAPAYA_DB_TBL_PHRASE_TRANS;

  /**
  * Languages
  * @var array $languages
  */
  var $languages = array();

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
   * @var base_dialog
   */
  private $dialog = NULL;

  /**
   * @var base_phraseeditor
   */
  private $phraseEditor;

  /**
  * Initialization
  *
  * @access public
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_languages_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('pagemode');
  }

  /**
  * Basic execution
  *
  * @access public
  */
  function execute() {
    if (!isset($this->params['pagemode'])) {
      $this->params['pagemode'] = '';
    }
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    switch ($this->params['pagemode']) {
    case 1:
      $this->loadLanguages();
      switch($this->params['cmd']) {
      case 'lng_add':
        $this->initializeDialog();
        if ($this->dialog->checkDialogInput()) {
          if ($newId = $this->addLanguage()) {
            $this->params['lng_id'] = $newId;
            $this->addMsg(
              MSG_INFO,
              sprintf($this->_gt('%s added.'), $this->_gt('Language'))
            );
            unset($this->dialog);
            $this->loadLanguages();
          }
        }
        break;
      case 'lng_chg':
        if (isset($this->params['lng_id']) && $this->params['lng_id'] > 0 &&
            isset($this->languages[$this->params['lng_id']])) {
          $this->initializeDialog();
          if ($this->dialog->checkDialogInput()) {
            if ($this->saveLanguage()) {
              $this->addMsg(
                MSG_INFO,
                sprintf($this->_gt('%s modified.'), $this->_gt('Language'))
              );
              unset($this->dialog);
              $this->loadLanguages();
            }
          }
        }
        break;
      case 'lng_del':
        if (isset($this->params['lng_id']) && $this->params['lng_id'] > 0 &&
            isset($this->languages[$this->params['lng_id']])) {
          if (!$this->checkLanguageHasPhrases($this->params['lng_id'])) {
            if (isset($this->params['confirm_delete']) &&
                $this->params['confirm_delete']) {
              if ($this->deleteLanguage()) {
                unset($this->languages[$this->params['lng_id']]);
                unset($this->params['lng_id']);
                unset($this->params['cmd']);
                $this->addMsg(
                  MSG_INFO,
                  sprintf($this->_gt('%s deleted.'), $this->_gt('Language'))
                );
              }
            }
          } else {
            unset($this->params['cmd']);
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Language has phrase translations for admininterface.')
            );
          }
        }
        break;
      }
      break;
    default:
      $this->phraseEditor = new base_phraseeditor();
      $this->phraseEditor->layout = $this->layout;
      $this->phraseEditor->initialize();
      $this->phraseEditor->execute();
      break;
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Get XML
  *
  * @access public
  * @return mixed
  * @see base_phraseeditor::getXML()
  */
  function getXML() {
    if (!isset($this->params['pagemode'])) {
      $this->params['pagemode'] = '';
    }
    switch ($this->params['pagemode']) {
    case 1:
      $this->getLanguagesListXML();
      $this->getXMLButtons();
      $this->getDelForm();
      $this->getDialogXML();
      break;
    default:
      $this->phraseEditor->getXML();
      break;
    }
    return '';
  }

  /**
  * Get xml buttons
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
      isset($this->params['pagemode']) && $this->params['pagemode'] != 1
    );
    $toolbar->addSeperator();
    $toolbar->addButton(
      'New Language',
      $this->getLink(array('pagemode' => 1, 'lng_id' => 0)),
      'actions-phrase-add',
      '',
      FALSE
    );
    if (isset($this->params['lng_id']) && $this->params['lng_id'] > 0 &&
        isset($this->languages[$this->params['lng_id']])) {
      $toolbar->addButton(
        'Delete',
        $this->getLink(array('cmd' => 'lng_del', 'lng_id' => (int)$this->params['lng_id'])),
        'actions-phrase-delete',
        '',
        FALSE
      );
    }
    $toolbar->addSeperator();
    if ($result = $toolbar->getXML()) {
      $this->layout->add('<menu>'.$result.'</menu>', 'menus');
    }
  }

  /**
  * Load languages from database
  *
  * @see base_db::databaseQueryFmt()
  * @access public
  * @return boolean
  */
  function loadLanguages() {
    unset($this->languages);
    $sql = "SELECT lng_id, lng_ident, lng_short, lng_title, lng_glyph,
                   is_interface_lng, is_content_lng
              FROM %s
             ORDER BY lng_title, lng_short, lng_ident, lng_id";
    if ($res = $this->databaseQueryFmt($sql, $this->tableLanguage)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (preg_match('~^\w+-(\w+\.gif)~', $row['lng_glyph'], $match)) {
          $row['lng_glyph'] = $match[1];
        }
        $this->languages[$row['lng_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Save / update language to database
  *
  * @see base_db::databaseUpdateRecord()
  * @access public
  * @return boolean
  */
  function saveLanguage() {
    $data = array(
      'lng_short' => empty($this->params['lng_short']) ? '' : $this->params['lng_short'],
      'lng_ident' => empty($this->params['lng_ident']) ? '' : $this->params['lng_ident'],
      'lng_title' => empty($this->params['lng_title']) ? '' : $this->params['lng_title'],
      'lng_glyph' => empty($this->params['lng_glyph']) ? '' : $this->params['lng_glyph'],
      'is_interface_lng' => empty($this->params['is_interface_lng'])
        ? 0 : (int)$this->params['is_interface_lng'],
      'is_content_lng' => empty($this->params['is_content_lng'])
        ? 0 : (int)$this->params['is_content_lng']
    );
    if ((!$data['is_interface_lng']) &&
        $this->checkLanguageHasPhrases($this->params['lng_id'])) {
      $this->addMsg(
        MSG_WARNING,
        $this->_gt('Language has phrase translations for admininterface.')
      );
      $data['is_interface_lng'] = 1;
    }
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableLanguage, $data, 'lng_id', (int)$this->params['lng_id']
    );
  }

  /**
  * Add / insert language to database
  *
  * @see base_db::databaseInsertRecord()
  * @access public
  * @return mixed boolean FALSE error or integer insered row id
  */
  function addLanguage() {
    $data = array(
      'lng_short' => empty($this->params['lng_short']) ? '' : $this->params['lng_short'],
      'lng_ident' => empty($this->params['lng_ident']) ? '' : $this->params['lng_ident'],
      'lng_title' => empty($this->params['lng_title']) ? '' : $this->params['lng_title'],
      'lng_glyph' => empty($this->params['lng_glyph']) ? '' : $this->params['lng_glyph'],
      'is_interface_lng' => empty($this->params['is_interface_lng'])
        ? 0 : (int)$this->params['is_interface_lng'],
      'is_content_lng' => empty($this->params['is_content_lng'])
        ? 0 : (int)$this->params['is_content_lng']
    );
    return $this->databaseInsertRecord($this->tableLanguage, 'lng_id', $data);
  }

  /**
  * Delete language from database
  *
  * @see base_db::databaseDeleteRecord()
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function deleteLanguage() {
    return $this->databaseDeleteRecord(
      $this->tableLanguage, 'lng_id', (int)$this->params['lng_id']
    );
  }

  /**
  * Check if language has phrases
  *
  * @param integer $lngId language id
  * @access public
  * @return boolean
  */
  function checkLanguageHasPhrases($lngId) {
    $sql = "SELECT COUNT(*) FROM %s WHERE lng_id = %d";
    $params = array($this->tableTranslation, (int)$lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      list($count) = $res->fetchRow();
      return ($count > 0);
    }
    return FALSE;
  }

  /**
  * Get language list in xml
  *
  * @access public
  */
  function getLanguagesListXML() {
    if (isset($this->languages) && is_array($this->languages) &&
        count($this->languages) > 0) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Languages'))
      );
      $result .= '<items>';
      foreach ($this->languages as $lng) {
        if (isset($this->params['lng_id']) && $this->params['lng_id'] == $lng['lng_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $href = $this->getLink(array('lng_id' => (int)$lng['lng_id']));
        $result .= sprintf(
          '<listitem href="%s" title="%s"%s>',
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($lng['lng_title']),
          $selected
        );
        if ($lng['lng_glyph'] != '' &&
            file_exists($this->getBasePath(TRUE).'pics/language/'.$lng['lng_glyph'])) {
          $result .= '<subitem>';
          $result .= sprintf(
            '<glyph src="%s"/>',
            papaya_strings::escapeHTMLChars('./pics/language/'.$lng['lng_glyph'])
          );
          $result .= '</subitem>';
        } else {
          $result .= '<subitem/>';
        }
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addLeft($result);
    }
  }

  /**
  * Initialize dialog
  *
  * @access public
  */
  function initializeDialog() {
    if (!(isset($this->dialog) && is_object($this->dialog))) {
      if (isset($this->params['lng_id']) && $this->params['lng_id'] > 0 &&
          isset($this->languages[$this->params['lng_id']])) {
        $hidden = array(
          'save' => 1,
          'cmd' => 'lng_chg',
          'lng_id' => empty($this->params['lng_id']) ? 0 : $this->params['lng_id']
        );
        $data = $this->languages[$this->params['lng_id']];
        $caption = 'Edit';
      } else {
        $hidden = array(
          'save' => 1,
          'cmd' => 'lng_add'
        );
        $data = array();
        $caption = 'Add';
      }
      $path = $this->getBasePath(TRUE).'pics/language/';
      $fields = array(
        'lng_short' => array('Language', '/^[a-z]{2,3}-[a-z]{2,3}$/i', TRUE,
          'input', 7, '', 'en-US'),
        'lng_ident' => array('Ident', '/^[a-z]{2,3}$/', TRUE, 'input', 3, '', 'en'),
        'lng_title' => array('Title', 'isNoHTML', TRUE, 'input', 30, '', 'New Language'),
        'lng_glyph' => array ('Image file', 'isFile', FALSE, 'filecombo',
          array($path, '/^[a-zA-Z0-9\-]+\.gif$/i', TRUE), ''),
        'is_interface_lng' => array('Backend Language', 'isNum', TRUE, 'yesno', '', '', 1),
        'is_content_lng' => array('Content', 'isNum', TRUE, 'yesno', '', '', 1),
      );
      $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->dialog->dialogTitle = $this->_gt($caption);
      $this->dialog->baseLink = $this->baseLink;
      $this->dialog->loadParams();
    }
  }

  /**
  * Get dialog xml
  *
  * @access public
  */
  function getDialogXML() {
    $this->initializeDialog();
    $this->layout->add($this->dialog->getDialogXML());
  }


  /**
  * Get delete language form
  *
  * @access public
  */
  function getDelForm() {
    if (isset($this->params['lng_id']) && $this->params['lng_id'] > 0 &&
        isset($this->languages[$this->params['lng_id']]) &&
        isset($this->params['cmd']) && $this->params['cmd'] == 'lng_del') {
      $hidden = array(
        'cmd' => 'lng_del',
        'lng_id' => (int)$this->params['lng_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete language "%s"?'),
        $this->languages[$this->params['lng_id']]['lng_title']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }
}
