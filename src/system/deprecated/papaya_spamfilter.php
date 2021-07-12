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
* Spam filter admi class, collect text and check for spam
*
* @package Papaya
* @subpackage Spamfilter
*/
class papaya_spamfilter extends base_spamfilter {

  /**
  * dialog/link parameter base name
  * @var string
  */
  var $paramName = 'sf';
  /**
  * Limit for word lists
  * @var integer
  */
  var $wordsPerPage = 20;

  /**
  * Limited ignore words list
  * @var array
  */
  var $ignoreWordList = array();
  /**
  * Limited stop words list
  * @var array
  */
  var $stopWordList = array();

  /**
  * Ingore words list absolute count
  * @var integer
  */
  var $ignoreWordsAbsCount = 0;
  /**
  * Stop words list absolute count
  * @var integer
  */
  var $stopWordsAbsCount = 0;
  /**
  * Spam log entries absolute count
  * @var integer
  */
  var $spamLogEntriesAbsCount = 0;

  /**
  * Allowed categoreis
  * @var array
  */
  var $allowedCategories = array('SPAM', 'HAM');

  /**
  * Limited log texts list
  * @var array
  */
  var $logEntries = array();
  /**
  * Logged text for training
  * @var array
  */
  var $logEntry = NULL;

  /**
   * @var int
   */
  private $currentLanguageId;

  /**
   * @var base_dialog
   */
  private $wordDialog;

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
  * Initialize administration interface
  * @access public
  * @return void
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.get_class($this).'_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('mode', array('offset'));
    $this->initializeSessionParam('offset');
  }

  /**
  * Execute administration interface
  * @access public
  * @return void
  */
  function execute() {
    $this->currentLanguageId = $this->papaya()->administrationLanguage->id;
    if (empty($this->sessionParams['language']) ||
        $this->sessionParams['language'] != $this->currentLanguageId) {
      $this->params['offset'] = 0;
      $this->sessionParams['offset'] = 0;
      $this->sessionParams['language'] = $this->currentLanguageId;
    }
    if (!isset($this->params['offset'])) {
      $this->params['offset'] = 0;
    }

    if ($this->currentLanguageId) {
      if (!isset($this->params['mode'])) {
        $this->params['mode'] = 0;
      }
      switch ($this->params['mode']) {
      case 2 :
        // load ignored words
        $this->loadIgnoreWordList((int)$this->params['offset']);
        if (isset($this->params['cmd'])) {
          switch ($this->params['cmd']) {
          case 'spamignore_delete' :
            if (isset($this->params['spamignore_id']) &&
                $this->params['spamignore_id'] > 0 &&
                isset($this->params['confirm_delete']) &&
                $this->params['confirm_delete']) {
              if ($this->deleteIgnoreWord($this->params['spamignore_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Entry deleted.'));
                unset($this->params['cmd']);
                unset($this->params['spamignore_id']);
                $this->loadIgnoreWordList((int)$this->params['offset']);
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Database Error.'));
              }
            }
            break;
          case 'spamignore_add' :
            if (isset($this->params['confirm_save']) && $this->params['confirm_save']) {
              if (isset($this->params['spamignore_word']) &&
                  !$this->ignoreWordExists($this->params['spamignore_word'])) {
                $this->initializeIgnoreWordDialog();
                if ($this->wordDialog->checkDialogInput()) {
                  if ($newId = $this->addIgnoreWord()) {
                    $this->addMsg(
                      MSG_INFO,
                      $this->_gt('Word added to ignoreword list.')
                    );
                    $this->loadIgnoreWordList((int)$this->params['offset']);
                  } else {
                    $this->addMsg(MSG_ERROR, $this->_gt('Database Error!'));
                  }
                }
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Word exists already!'));
              }
            }
            break;
          case 'spamignore_edit' :
            if (isset($this->params['confirm_save']) && $this->params['confirm_save']) {
              if (isset($this->params['spamignore_word']) &&
                  !$this->ignoreWordExists($this->params['spamignore_word'])) {
                $this->initializeIgnoreWordDialog();
                if ($this->wordDialog->checkDialogInput()) {
                  if ($this->updateIgnoreWord()) {
                    $this->addMsg(MSG_INFO, $this->_gt('Word modified.'));
                    unset($this->wordDialog);
                    $this->loadIgnoreWordList((int)$this->params['offset']);
                  } else {
                    $this->addMsg(MSG_ERROR, $this->_gt('Database Error!'));
                  }
                }
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Word exists already!'));
              }
            }
            break;
          }
        }
        break;
      case 1 :
        // load stopwords
        $this->loadStopWordList((int)$this->params['offset']);
        if (isset($this->params['cmd'])) {
          switch ($this->params['cmd']) {
          case 'spamstop_delete':
            if (isset($this->params['spamstop_id']) &&
                $this->params['spamstop_id'] > 0 &&
                isset($this->params['confirm_delete']) &&
                $this->params['confirm_delete']) {
              if ($this->deleteStopWord($this->params['spamstop_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Entry deleted.'));
                unset($this->params['cmd']);
                unset($this->params['spamstop_id']);
                $this->loadStopWordList((int)$this->params['offset']);
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Database Error.'));
              }
            }
            break;
          case 'spamstop_add':
            if (isset($this->params['confirm_save']) && $this->params['confirm_save']) {
              if (isset($this->params['spamstop_word']) &&
                  !$this->stopWordExists($this->params['spamstop_word'])) {
                $this->initializeStopWordDialog();
                if ($this->wordDialog->checkDialogInput()) {
                  if ($newId = $this->addStopWord()) {
                    $this->addMsg(MSG_INFO, $this->_gt('Word added to stopword list.'));
                    unset($this->wordDialog);
                    $this->loadStopWordList((int)$this->params['offset']);
                  } else {
                    $this->addMsg(MSG_ERROR, $this->_gt('Database Error!'));
                  }
                }
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Word exists already!'));
              }
            }
            break;
          case 'spamstop_edit':
            $this->initializeStopWordDialog();
            if (isset($this->params['confirm_save']) && $this->params['confirm_save']) {
              if (isset($this->params['spamstop_word']) &&
                  !$this->stopWordExists($this->params['spamstop_word'])) {
                $this->initializeStopWordDialog();
                if ($this->wordDialog->checkDialogInput()) {
                  if ($this->updateStopWord()) {
                    $this->addMsg(MSG_INFO, $this->_gt('Word modified.'));
                    $this->loadStopWordList((int)$this->params['offset']);
                  } else {
                    $this->addMsg(MSG_ERROR, $this->_gt('Database Error!'));
                  }
                }
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Word exists already!'));
              }
            }
            break;
          }
        }
        break;
      case 0:
      default :
        if (isset($this->params['cmd'])) {
          switch ($this->params['cmd']) {
          case 'process_training_rpc' :
            $this->getProcessTrainingRPCXML();
            break;
          case 'spamlog_spam':
            if (isset($this->params['confirm_rate']) && $this->params['confirm_rate'] &&
                isset($this->params['spamlog_id']) && $this->params['spamlog_id'] &&
                $this->loadSpamLogDetails($this->params['spamlog_id'])) {
              if ($this->rateSpamLog($this->logEntry['spamlog_text'], 'SPAM')) {
                if ($this->deleteSpamLog($this->params['spamlog_id'])) {
                  unset($this->params['cmd']);
                  unset($this->params['spamlog_id']);
                  unset($this->logEntry);
                }
              }
            }
            break;
          case 'spamlog_ham':
            if (isset($this->params['confirm_rate']) && $this->params['confirm_rate'] &&
                isset($this->params['spamlog_id']) && $this->params['spamlog_id'] &&
                $this->loadSpamLogDetails($this->params['spamlog_id'])) {
              if ($this->rateSpamLog($this->logEntry['spamlog_text'], 'HAM')) {
                if ($this->deleteSpamLog($this->params['spamlog_id'])) {
                  unset($this->params['cmd']);
                  unset($this->params['spamlog_id']);
                  unset($this->logEntry);
                }
              }
            }
            break;
          case 'spamlog_delete':
            if (isset($this->params['spamlog_id']) && $this->params['spamlog_id'] > 0 &&
                isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
              if ($this->deleteSpamLog($this->params['spamlog_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Entry deleted.'));
                unset($this->params['cmd']);
                unset($this->params['spamlog_id']);
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Database Error.'));
              }
            }
            break;
          }
        }
        $this->loadSpamLogList((int)$this->params['offset']);
        if (isset($this->params['spamlog_id']) && $this->params['spamlog_id']) {
           $this->loadSpamLogDetails($this->params['spamlog_id']);
        }
        break;
      }
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    }
  }

  /**
  * Add administration interface page XML to layout object
  * @access public
  * @return void
  */
  function getXML() {
    $this->getButtonXML();
    if (!isset($this->params['mode'])) {
      $this->params['mode'] = 0;
    }
    switch ($this->params['mode']) {
    case 2 :
      $this->getXMLIgnoreWordList();
      if (isset($this->params['cmd'])) {
        switch ($this->params['cmd']) {
        case 'spamignore_delete' :
          if (isset($this->params['spamignore_id']) && $this->params['spamignore_id'] > 0) {
            $this->layout->add($this->getIgnoreWordDeleteDialog());
          }
          break;
        case 'spamignore_add' :
        case 'spamignore_edit' :
          $this->getIgnoreWordDialog();
          break;
        }
      }
      break;
    case 1 :
      $this->getXMLStopWordList();
      if (isset($this->params['cmd'])) {
        switch ($this->params['cmd']) {
        case 'spamstop_delete' :
          if (isset($this->params['spamstop_id']) && $this->params['spamstop_id'] > 0) {
            $this->layout->add($this->getStopWordDeleteDialog());
          }
          break;
        case 'spamstop_add' :
        case 'spamstop_edit' :
          $this->getStopWordDialog();
          break;
        }
      }
      break;
    case 0:
    default :
      $this->getXMLSpamLogList();
      if (!isset($this->params['cmd'])) {
        $this->params['cmd'] = 'spamlog_show';
      }
      switch ($this->params['cmd']) {
      case 'spamlog_spam' :
        $this->layout->add($this->getSpamLogRateDialog('SPAM'));
        if (isset($this->logEntry) && is_array($this->logEntry)) {
          $this->getSpamLogDialog();
        }
        break;
      case 'spamlog_ham' :
        $this->layout->add($this->getSpamLogRateDialog('HAM'));
        if (isset($this->logEntry) && is_array($this->logEntry)) {
          $this->getSpamLogDialog();
        }
        break;
      case 'spamlog_delete' :
        if (isset($this->params['spamlog_id']) && $this->params['spamlog_id'] > 0) {
          $this->layout->add($this->getSpamLogDeleteDialog());
        }
        break;
      case 'spamlog_show' :
        if (isset($this->logEntry) && is_array($this->logEntry)) {
            $this->getSpamLogDialog();
        }
        break;
      }
      break;
    }
  }

  /**
  * Add administration interface  buttons XML to layout object
  * @access private
  * @return void
   */
  function getButtonXML() {
    $menubar = new base_btnbuilder;
    $menubar->images = $this->papaya()->images;
    $menubar->addButton(
      'Protocol',
      $this->getLink(array('mode' => 0)),
      'categories-protocol',
      '',
      empty($this->params['mode']) || $this->params['mode'] == 0
    );
    $menubar->addButton(
      'Stopwords',
      $this->getLink(array('mode' => 1)),
      'items-page-stopword',
      '',
      isset($this->params['mode']) && $this->params['mode'] == 1
    );
    $menubar->addButton(
      'Ignore words',
      $this->getLink(array('mode' => 2)),
      'items-page-ignoreword',
      '',
      isset($this->params['mode']) && $this->params['mode'] == 2
    );
    $menubar->addSeperator();
    if (!isset($this->params['mode'])) {
      $this->params['mode'] = 0;
    }
    switch ($this->params['mode']) {
    case 2 :
      $menubar->addButton(
        'Add word',
        $this->getLink(array('cmd' => 'spamignore_add', 'spamignore_id' => 0)),
        'actions-page-ignoreword-add'
      );
      if (isset($this->params['spamignore_id']) &&
          isset($this->ignoreWordList[$this->params['spamignore_id']])) {
        $menubar->addButton(
          'Delete',
          $this->getLink(
            array(
              'cmd' => 'spamignore_delete',
              'spamignore_id' => (int)$this->params['spamignore_id']
            )
          ),
          'actions-page-ignoreword-delete'
        );
      }
      break;
    case 1 :
      $menubar->addButton(
        'Add word',
        $this->getLink(array('cmd' => 'spamstop_add', 'spamstop_id' => 0)),
        'actions-page-stopword-add'
      );
      if (isset($this->params['spamstop_id']) &&
          isset($this->stopWordList[$this->params['spamstop_id']])) {
        $menubar->addButton(
          'Delete',
          $this->getLink(
            array(
              'cmd' => 'spamstop_delete',
              'spamstop_id' => (int)$this->params['spamstop_id']
            )
          ),
          'actions-page-stopword-delete'
        );
      }
      break;
    case 0:
    default :
      if (isset($this->params['spamlog_id']) &&
          isset($this->logEntries[$this->params['spamlog_id']])) {
        $menubar->addButton(
          'Spam',
          $this->getLink(
            array(
              'cmd' => 'spamlog_spam',
              'spamlog_id' => (int)$this->params['spamlog_id']
            )
          ),
          'items-junk',
          '',
          isset($this->params['cmd']) && $this->params['cmd'] == 'spamlog_spam'
        );
        $menubar->addButton(
          'Ham',
          $this->getLink(
            array(
              'cmd' => 'spamlog_ham',
              'spamlog_id' => (int)$this->params['spamlog_id']
            )
          ),
          'items-page',
          '',
          isset($this->params['cmd']) && $this->params['cmd'] == 'spamlog_ham'
        );
        $menubar->addSeperator();
        $menubar->addButton(
          'Delete',
          $this->getLink(
            array(
              'cmd' => 'spamlog_delete',
              'spamlog_id' => (int)$this->params['spamlog_id']
            )
          ),
          'places-trash'
        );
      }
      $menubar->addSeperator();
      $menubar->addButton(
        'Train',
        'javascript:processTraining();',
        'actions-database-refresh'
      );
      $this->getTrainingJavascript();
      break;
    }
    if ($str = $menubar->getXML()) {
      $this->layout->addMenu(sprintf('<menu>%s</menu>'.LF, $str));
    }
  }

  /**
  * Get XML for stop words listview and add to layout object
  * @return void
  */
  function getXMLStopWordList() {
    if (isset($this->stopWordList) && is_array($this->stopWordList) &&
        count($this->stopWordList)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Stopwords'))
      );
      $result .= $this->getXMLListNav(
        $this->params['offset'], $this->stopWordsAbsCount, $this->wordsPerPage
      );
      $result .= '<items>'.LF;
      foreach ($this->stopWordList as $word) {
        if (isset($this->params['spamstop_id']) &&
            $this->params['spamstop_id'] == $word['spamstop_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s"%s/>'.LF,
          papaya_strings::escapeHTMLChars($word['spamstop_word']),
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array('cmd' => 'spamstop_edit', 'spamstop_id' => $word['spamstop_id'])
            )
          ),
          $selected
        );
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
   * Get list navigation
   *
   * @param integer $offset current offset
   * @param integer $max max offset
   * @param integer $steps
   * @param integer $maxPages
   * @param string $paramName offset param name
   * @return string
   * @access private
   */
  function getXMLListNav($offset, $max, $steps, $maxPages = 9, $paramName = 'offset') {
    return papaya_paging_buttons::getPagingButtons(
      $this, array(), (int)$offset, $steps, $max, $maxPages, $paramName
    );
  }

  /**
  * Get XML for ignore words listview and add to layout object
  * @return void
  */
  function getXMLIgnoreWordList() {
    if (isset($this->ignoreWordList) && is_array($this->ignoreWordList) &&
        count($this->ignoreWordList)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Ignored Words'))
      );
      $result .= $this->getXMLListNav(
        $this->params['offset'], $this->ignoreWordsAbsCount, $this->wordsPerPage
      );
      $result .= '<items>'.LF;
      foreach ($this->ignoreWordList as $word) {
        if (isset($this->params['spamignore_id']) &&
            $this->params['spamignore_id'] == $word['spamignore_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s"%s/>'.LF,
          papaya_strings::escapeHTMLChars($word['spamignore_word']),
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array('cmd' => 'spamignore_edit', 'spamignore_id' => $word['spamignore_id'])
            )
          ),
          $selected
        );
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get XML for spam log listview and add to layout object
  * @return void
  */
  function getXMLSpamLogList() {
    if (isset($this->logEntries) && is_array($this->logEntries) && count($this->logEntries)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Log'))
      );
      $result .= $this->getXMLListNav(
        $this->params['offset'], $this->spamLogEntriesAbsCount, $this->wordsPerPage
      );
      $result .= '<items>'.LF;
      foreach ($this->logEntries as $entry) {
        if (isset($this->params['spamlog_id']) &&
            $this->params['spamlog_id'] == $entry['spamlog_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s"%s/>'.LF,
          papaya_strings::escapeHTMLChars(
            papaya_strings::substr($entry['spamlog_text'], 0, 30)
          ),
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array('cmd' => 'spamlog_show', 'spamlog_id' => $entry['spamlog_id'])
            )
          ),
          $selected
        );
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Initialize object for stop words edit dialog
  * @access private
  * @return void
  */
  function initializeStopWordDialog() {
    if (!(isset($this->wordDialog) && is_object($this->wordDialog))) {
      if (isset($this->params['spamstop_id']) &&
          isset($this->stopWordList[$this->params['spamstop_id']])) {
        $data = $this->stopWordList[$this->params['spamstop_id']];
        $hidden = array(
          'cmd' => 'spamstop_edit',
          'spamstop_id' => $data['spamstop_id'],
          'confirm_save' => 1
        );
        $title = 'Edit';
        $btnTitle = 'Save';
      } else {
        $data = array();
        $hidden = array(
          'cmd' => 'spamstop_add',
          'spamstop_id' => 0,
          'confirm_save' => 1
        );
        $title = 'Add';
        $btnTitle = 'Add';
      }

      $fields = array(
        'spamstop_word' => array('Word', '~^[^'.$this->nonWordChars.']+$~', TRUE, 'input', '30', '')
      );

      $this->wordDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->wordDialog->dialogTitle = $this->_gt($title);
      $this->wordDialog->buttonTitle = $btnTitle;
      $this->wordDialog->baseLink = $this->baseLink;
      $this->wordDialog->loadParams();
    }
  }

  /**
  * Get stop words edit dialog
  * @access private
  * @return void
  */
  function getStopWordDialog() {
    $this->initializeStopWordDialog();
    $this->layout->add($this->wordDialog->getDialogXML());
  }

  /**
  * Initialize object for ignore words edit dialog
  * @access private
  * @return void
  */
  function initializeIgnoreWordDialog() {
    if (!(isset($this->wordDialog) && is_object($this->wordDialog))) {
      if (isset($this->params['spamignore_id']) &&
          isset($this->ignoreWordList[$this->params['spamignore_id']])) {
        $data = $this->ignoreWordList[$this->params['spamignore_id']];
        $hidden = array(
          'cmd' => 'spamignore_edit',
          'spamstop_id' => $data['spamignore_id'],
          'confirm_save' => 1
        );
        $title = 'Edit';
        $btnTitle = 'Save';
      } else {
        $data = array();
        $hidden = array(
          'cmd' => 'spamignore_add',
          'spamignore_id' => 0,
          'confirm_save' => 1
        );
        $title = 'Add';
        $btnTitle = 'Add';
      }

      $fields = array(
        'spamignore_word' => array(
          'Word', '~^[^'.$this->nonWordChars.']+$~', TRUE, 'input', '30', ''
        )
      );

      $this->wordDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->wordDialog->dialogTitle = $this->_gt($title);
      $this->wordDialog->buttonTitle = $btnTitle;
      $this->wordDialog->baseLink = $this->baseLink;
      $this->wordDialog->loadParams();
    }
  }

  /**
  * Get ignore words edit dialog
  * @access private
  * @return void
  */
  function getIgnoreWordDialog() {
    $this->initializeIgnoreWordDialog();
    $this->layout->add($this->wordDialog->getDialogXML());
  }

  /**
  * Load limited stop words list from database
  * @access private
  * @param integer $offset
  * @return boolean
  */
  function loadStopWordList($offset) {
    $this->stopWordList = array();
    $sql = "SELECT spamstop_id, spamstop_word
              FROM %s
             WHERE spamstop_lngid = '%s'
             ORDER BY spamstop_word";
    $params = array($this->tableStopWords, $this->currentLanguageId);
    $res = $this->databaseQueryFmt($sql, $params, (int)$this->wordsPerPage, (int)$offset);
    if ($res) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->stopWordList[$row['spamstop_id']] = $row;
      }
      $this->stopWordsAbsCount = $res->absCount();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Stop word exists already?
  *
  * @param string $stopWord word to stop
  * @access private
  * @return boolean used or not
  */
  function stopWordExists($stopWord) {
    $stopWord = papaya_strings::strtolower($stopWord);
    $sql = "SELECT COUNT(spamstop_id)
              FROM %s
             WHERE spamstop_word = '%s'
               AND spamstop_lngid = %d";
    $params = array($this->tableStopWords, $stopWord,
      $this->currentLanguageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() > 0);
    }
    return FALSE;
  }

  /**
  * Add stop word record to database
  * @access private
  * @return integer|FALSE
  */
  function addStopWord() {
    $data = array(
      'spamstop_word' => $this->params['spamstop_word'],
      'spamstop_lngid' => $this->currentLanguageId,
    );
    return $this->databaseInsertRecord($this->tableStopWords, 'spamstop_id', $data);
  }

  /**
  * Update stop word record in database
  * @access private
  * @return boolean
  */
  function updateStopWord() {
    $data = array(
      'spamstop_word' => $this->params['spamstop_word'],
    );
    $filter = array(
      'spamstop_id' => $this->params['spamstop_id'],
      'spamstop_lngid' => $this->currentLanguageId,
    );
    return (FALSE !== $this->databaseUpdateRecord($this->tableStopWords, $data, $filter));
  }

  /**
  * Load limited ignore words list from database
  * @access private
  * @param integer $offset
  * @return boolean
  */
  function loadIgnoreWordList($offset) {
    $this->ignoreWordList = array();
    $sql = "SELECT spamignore_id, spamignore_word
              FROM %s
             WHERE spamignore_lngid = '%s'
             ORDER BY spamignore_word";
    $params = array($this->tableIgnoreWords, $this->currentLanguageId);
    $res = $this->databaseQueryFmt($sql, $params, (int)$this->wordsPerPage, (int)$offset);
    if ($res) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->ignoreWordList[$row['spamignore_id']] = $row;
      }
      $this->ignoreWordsAbsCount = $res->absCount();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Ignore word exists already?
  *
  * @param string $ignoreWord word to ignore
  * @access private
  * @return boolean used or not
  */
  function ignoreWordExists($ignoreWord) {
    $ignoreWord = papaya_strings::strtolower($ignoreWord);
    $sql = "SELECT COUNT(spamignore_id)
              FROM %s
             WHERE spamignore_word = '%s'
               AND spamignore_lngid = %d";
    $params = array($this->tableIgnoreWords, $ignoreWord,
      $this->currentLanguageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() > 0);
    }
    return FALSE;
  }

  /**
  * Add ignore word record to database
  * @access private
  * @return integer|FALSE
  */
  function addIgnoreWord() {
    $data = array(
      'spamignore_word' => $this->params['spamignore_word'],
      'spamignore_lngid' => $this->currentLanguageId,
    );
    return $this->databaseInsertRecord(
      $this->tableIgnoreWords,
      'spamignore_id',
      $data
    );
  }

  /**
  * Update ignore word record in database
  * @access private
  * @return boolean
  */
  function updateIgnoreWord() {
    $data = array(
      'spamignore_word' => $this->params['spamignore_word'],
    );
    $filter = array(
      'spamignore_id' => $this->params['spamignore_id'],
      'spamignore_lngid' => $this->currentLanguageId,
    );
    return (
      FALSE !== $this->databaseUpdateRecord($this->tableIgnoreWords, $data, $filter)
    );
  }

  /**
  * Load limited spam log texts list from database
  * @access private
  * @param integer $offset
  * @return bool
  */
  function loadSpamLogList($offset) {
    $this->logEntries = array();
    $sql = "SELECT spamlog_id, spamlog_time, spamlog_text
              FROM %s
             WHERE spamlog_lngid = '%d'
             ORDER BY spamlog_time DESC";
    $params = array($this->tableSpamLog, $this->currentLanguageId);
    $res = $this->databaseQueryFmt($sql, $params, (int)$this->wordsPerPage, (int)$offset);
    if ($res) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->logEntries[$row['spamlog_id']] = $row;
      }
      $this->spamLogEntriesAbsCount = $res->absCount();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load spam log text details from database
  * @access private
  * @param integer $logId
  * @return boolean
  */
  function loadSpamLogDetails($logId) {
    unset($this->logEntry);
    $sql = "SELECT spamlog_id, spamlog_time, spamlog_text, spamlog_info, spamlog_lngid
              FROM %s
             WHERE spamlog_id = '%d'";
    $params = array($this->tableSpamLog, (int)$logId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->logEntry = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get log text details dialog
  * @access private
  * @return void
  */
  function getSpamLogDialog() {
    if (isset($this->logEntry) && is_array($this->logEntry)) {
      $spamCheck = $this->check(
        $this->logEntry['spamlog_text'], $this->logEntry['spamlog_lngid']
      );
      $result = '<sheet>';
      $result .= '<header>';
      $result .= '<lines>';
      $result .= sprintf(
        '<line class="headertitle">%s</line>',
        papaya_strings::escapeHTMLChars($this->logEntry['spamlog_info'])
      );
      $result .= sprintf(
        '<line class="headersubtitle">%s: %s</line>',
        papaya_strings::escapeHTMLChars($this->_gt('Spam')),
        papaya_strings::escapeHTMLChars($this->_gt($spamCheck['spam'] ? 'Yes' : 'No'))
      );
      $result .= sprintf(
        '<line class="%s">%s (%s/%s): %s%% / %s%%</line>',
        'headersubtitle',
        papaya_strings::escapeHTMLChars($this->_gt('Probability')),
        papaya_strings::escapeHTMLChars($this->_gt('Spam')),
        papaya_strings::escapeHTMLChars($this->_gt('Ham')),
        empty($spamCheck['scores']['SPAM']) ? 0 : (int)($spamCheck['scores']['SPAM'] * 100),
        empty($spamCheck['scores']['HAM']) ? 0 : (int)($spamCheck['scores']['HAM'] * 100)
      );
      $result .= sprintf(
        '<line class="headersubtitle">%s: %d</line>',
        papaya_strings::escapeHTMLChars($this->_gt('Rated words')),
        (int)$spamCheck['scoretokencount']
      );
      $result .= sprintf(
        '<line class="headersubtitle">%s: %d</line>',
        papaya_strings::escapeHTMLChars($this->_gt('Stopwords')),
        (int)$spamCheck['stopwordcount']
      );
      $result .= sprintf(
        '<line class="headersubtitle">%s: %d</line>',
        papaya_strings::escapeHTMLChars($this->_gt('Small words')),
        (int)$spamCheck['smalltokencount']
      );
      $result .= sprintf(
        '<line class="headersubtitle">%s: %d</line>',
        papaya_strings::escapeHTMLChars($this->_gt('Large words')),
        (int)$spamCheck['largetokencount']
      );
      $result .= sprintf(
        '<line class="headersubtitle">%s: %d</line>',
        papaya_strings::escapeHTMLChars($this->_gt('Very large words')),
        (int)$spamCheck['blocktokencount']
      );
      $result .= '</lines>';
      $result .= '</header>';
      $result .= '<text>';
      $result .= '<noescape><![CDATA[';
      $result .= papaya_strings::escapeHTMLTags($this->logEntry['spamlog_text'], TRUE);
      $result .= ']]></noescape>';
      $result .= '</text>';
      $result .= '</sheet>';
      $this->layout->add($result);
      $this->getSpamLogTokenList();
    }
  }

  /**
  * Compare two tokens by occurence
  * @access private
  * @param string $token1
  * @param string $token2
  * @return integer
  */
  function compareLogTokens($token1, $token2) {
    if ($token1[1] == $token2[1]) {
      return strcmp($token1[0], $token2[0]);
    } else {
      return ($token1[1] < $token2[1]);
    }
  }

  /**
  * Get spam log text token list
  * @access private
  * @return void
  */
  function getSpamLogTokenList() {
    if (isset($this->logEntry) && is_array($this->logEntry)) {
      $this->_initializeWordLists($this->logEntry['spamlog_lngid']);
      $tokenData = $this->_getTokens(
        $this->logEntry['spamlog_text'], $this->logEntry['spamlog_lngid']
      );
      if (is_array($tokenData) && isset($tokenData['tokens']) &&
          is_array($tokenData['tokens']) && count($tokenData['tokens']) > 0) {
        foreach ($tokenData['tokens'] as $token => $tokenCount) {
          $tokens[] = array($token, $tokenCount);
        }
        uasort($tokens, array($this, 'compareLogTokens'));
        $result = sprintf(
          '<listview title="%s">',
          papaya_strings::escapeHTMLChars($this->_gt('Words - Top 20'))
        );
        $result .= '<items>';
        $counter = 0;
        foreach ($tokens as $tokenArray) {
          if (++$counter >= 20) {
            break;
          }
          list($token, $tokenCount) = $tokenArray;
          if (isset($this->stopWords[$this->logEntry['spamlog_lngid']][$token])) {
            $imgIdx = 'items-page-stopword';
          } elseif (isset($this->ignoreWords[$this->logEntry['spamlog_lngid']][$token])) {
            $imgIdx = 'items-page-ignoreword';
          } else {
            $imgIdx = 'items-page';
          }
          $result .= sprintf(
            '<listitem title="%s" image="%s">',
            papaya_strings::escapeHTMLChars($token),
            papaya_strings::escapeHTMLChars($this->papaya()->images[$imgIdx])
          );
          $result .= '<subitem align="right">'.(int)$tokenCount.'</subitem>';
          if (isset($this->stopWords[$this->logEntry['spamlog_lngid']][$token])) {
            $result .= '<subitem/>';
          } else {
            $result .= sprintf(
              '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'mode' => 1,
                    'cmd' => 'spamstop_add',
                    'spamstop_id' => 0,
                    'spamstop_word' => $token
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($this->papaya()->images['actions-page-stopword-add']),
              papaya_strings::escapeHTMLChars($this->_gt('Stopword'))
            );
          }
          if (isset($this->ignoreWords[$this->logEntry['spamlog_lngid']][$token])) {
            $result .= '<subitem/>';
          } else {
            $result .= sprintf(
              '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'mode' => 2,
                    'cmd' => 'spamignore_add',
                    'spamignore_id' => 0,
                    'spamignore_word' => $token
                  )
                )
              ),
              papaya_strings::escapeHTMLChars(
                $this->papaya()->images['actions-page-ignoreword-add']
              ),
              papaya_strings::escapeHTMLChars($this->_gt('Ignore word'))
            );
          }
          $result .= '</listitem>';
        }
        $result .= '</items>';
        $result .= '</listview>';
        $this->layout->addRight($result);
      }
    }
  }

  /**
  * Get log text delete confirmation dialog
  * @access private
  * @return string
  */
  function getSpamLogDeleteDialog() {
    $hidden = array(
      'cmd' => 'spamlog_delete',
      'spamlog_id' => $this->params['spamlog_id'],
      'confirm_delete' => 1,
    );
    $msg = sprintf($this->_gt('Delete entry?'));
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Get stop word delete confirmation dialog
  * @return string
  */
  function getStopWordDeleteDialog() {
    $hidden = array(
      'cmd' => 'spamstop_delete',
      'spamstop_id' => $this->params['spamstop_id'],
      'confirm_delete' => 1,
    );
    $msg = $this->_gt('Delete entry?');
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Get ignore word delete confirmation dialog
  * @access private
  * @return string
  */
  function getIgnoreWordDeleteDialog() {
    $hidden = array(
      'cmd' => 'spamignore_delete',
      'spamignore_id' => $this->params['spamignore_id'],
      'confirm_delete' => 1,
    );
    $msg = $this->_gt('Delete entry?');
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Delete log text record from database
  * @access private
  * @param integer $spamLogId
  * @return boolean
  */
  function deleteSpamLog($spamLogId) {
    return FALSE !== $this->databaseDeleteRecord(
      $this->tableSpamLog, array('spamlog_id' => (int)$spamLogId)
    );
  }

  /**
  * Delete stop word record from database
  * @access private
  * @param integer $stopWordId
  * @return boolean
  */
  function deleteStopWord($stopWordId) {
    return FALSE !== $this->databaseDeleteRecord(
      $this->tableStopWords, array('spamstop_id' => (int)$stopWordId)
    );
  }

  /**
  * Delete ignore word record from database
  * @access private
  * @param integer $ignoreWordId
  * @return boolean
  */
  function deleteIgnoreWord($ignoreWordId) {
    return FALSE !== $this->databaseDeleteRecord(
      $this->tableIgnoreWords, array('spamignore_id' => (int)$ignoreWordId)
    );
  }

  /**
  * Serialize tokens array
  * @access private
  * @param array $tokens
  * @return string
  */
  function serializeTokens($tokens) {
    if (isset($tokens) && is_array($tokens) && count($tokens) > 0) {
      $result = '';
      foreach ($tokens as $token => $tokenCount) {
        $result .= $token.':'.$tokenCount."\n";
      }
      return substr($result, 0, -1);
    }
    return '';
  }

  /**
  * Unserialize tokens array from string
  * @access private
  * @param string $tokenStr
  * @return string
  */
  function unserializeTokens($tokenStr) {
    $result = array();
    if (preg_match_all('~^(.+):(\d+)$~m', $tokenStr, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        $result[$match[1]] = (int)$match[2];
      }
    }
    return $result;
  }

  /**
  * Get log text rating dialog
  * @access private
  * @param string $category
  * @return string
  */
  function getSpamLogRateDialog($category) {
    if (isset($this->logEntry) && is_array($this->logEntry)) {
      $hidden = array(
        'spamlog_id' => $this->logEntry['spamlog_id'],
        'confirm_rate' => 1,
      );
      if ($category == 'HAM') {
        $msg = sprintf($this->_gt('This is not spam?'));
        $hidden['cmd'] = 'spamlog_ham';
      } else {
        $msg = sprintf($this->_gt('This is spam?'));
        $hidden['cmd'] = 'spamlog_spam';
      }
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->buttonTitle = 'Yes';
      return $dialog->getMsgDialog();
    }
    return '';
  }

  /**
  * Store log text rating to database
  * @access private
  * @param string $logText
  * @param string $category
  * @return boolean
  */
  function rateSpamLog($logText, $category) {
    $this->_initializeWordLists($this->currentLanguageId);
    $tokenData = $this->_getTokens($logText, $this->currentLanguageId);
    $tokenStr = trim($this->serializeTokens($tokenData['tokens']));
    if ($tokenStr != '') {
      $data = array(
        'spamreference_data' => $tokenStr,
        'spamreference_lngid' => $this->currentLanguageId
      );
      if (in_array($category, $this->allowedCategories)) {
        $data['spamcategory_ident'] = $category;
      } else {
        $data['spamcategory_ident'] = 'SPAM';
      }
      if (FALSE !== $this->databaseInsertRecord($this->tableSpamReferences, NULL, $data)) {
        $this->addMsg(MSG_INFO, $this->_gt('Message rated.'));
        return TRUE;
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Database Error.'));
        return FALSE;
      }
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('Message contains no tokens.'));
      return FALSE;
    }
  }

  /**
  * Get training javascript
  * @access private
  * @return void
  */
  function getTrainingJavascript() {
    $rpcLink = $this->baseLink.'?'.$this->paramName.
      '[cmd]=process_training_rpc&'.$this->paramName;

    $this->layout->addScript('<script type="text/javascript" src="script/xmlrpc.js"></script>');
    $result = '<script type="text/javascript">
var queuePosition = 0;
function requestProcessTraining(offset) {
  var url = \''.$rpcLink.'[queue_offset]=\'+offset;
  loadXMLDoc(url, true);
}

function rpcSetProcessTraining(data, params) {
  var responseArray = new Array();
  if (params) {
    responseArray = xmlParamNodesToArray(params);
    if (responseArray.countDone && responseArray.countQueue) {
      responseArray.countDone = parseInt(responseArray.countDone);
      responseArray.countQueue = parseInt(responseArray.countQueue);
      if (isNaN(responseArray.countSent)) {
        responseArray.countSent = 0;
      }
      if (isNaN(responseArray.countQueue)) {
        responseArray.countQueue = 0;
      }
      queuePosition += responseArray.countDone;
      var queueCount = responseArray.countQueue;
      updateTrainingStatus(responseArray.countDone.toString() + \' - \' +
        queuePosition.toString() + \'/\' + queueCount.toString(),
        Math.floor(queuePosition * 100 / queueCount));
      if (queuePosition &lt; queueCount && queuePosition &gt; 0) {
        window.setTimeout(\'requestProcessTraining(\'+queuePosition+\')\', 100);
      } else {
        updateTrainingStatus(\''.$this->_gt('Done').'\', 100);
      }
    } else {
      updateTrainingStatus(\'Params error\', 0);
    }
  } else {
    updateTrainingStatus(\'Error\', 0);
  }
}

function updateTrainingStatus(labelText, barPosition) {
  PapayaLightBox.update(labelText, barPosition);
}

function processTraining() {
  PapayaLightBox.init(\''.$this->_gt('Spamfilter').'\', \''.$this->_gt('Close').'\');
  PapayaLightBox.update(\''.$this->_gt('Requesting...').'\', 0);
  requestProcessTraining(0);
}
</script>';
    $this->layout->addScript($result);
  }

  /**
  * Get training progress rpc call xml
  * @access private
  * @return void
  */
  function getProcessTrainingRPCXML() {
    /* Rueckgabe = abgearbeitet / verbleibend als XML*/
    $countDone = 0;
    $countSteps = 25;
    $countQueue = 0;

    if (isset($this->params['queue_offset']) && $this->params['queue_offset'] == 0) {
       $this->databaseEmptyTable($this->tableSpamWords);
    }

    $sql = "SELECT spamreference_id, spamcategory_ident, spamreference_lngid,
                   spamreference_data
              FROM %s
             ORDER BY spamreference_id";
    $res = $this->databaseQueryFmt(
      $sql, $this->tableSpamReferences, $countSteps, (int)$this->params['queue_offset']
    );
    if ($res) {
      $references = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $references[] = $row;
      }
      $countQueue = $res->absCount();
      $countDone = $res->count();
      foreach ($references as $reference) {
        $this->train(
          $reference['spamreference_lngid'],
          $reference['spamcategory_ident'],
          $reference['spamreference_data']
        );
      }
      if ($countQueue <= ($countDone + $this->params['queue_offset'])) {
        $this->updateProbabilities();
      }
    }

    // rueckgabe xml
    $result = '<?xml version="1.0" encoding="UTF-8"?>';
    $result .= '<response>';
    $result .= '<method>rpcSetProcessTraining</method>';
    $result .= sprintf('<param name="countDone" value="%d" />', (int)$countDone);
    $result .= sprintf('<param name="countQueue" value="%d" />', (int)$countQueue);
    $result .= '<data></data>';
    $result .= '</response>';
    header('Content-type: text/xml; charset=utf-8');
    echo $result;
    exit;
  }

  /**
  * Train spamfilter
  * @access private
  * @param integer $lngId
  * @param string $category
  * @param string $tokenStr
  * @return void
  */
  function train($lngId, $category, $tokenStr) {
    $tokens = $this->unserializeTokens($tokenStr);
    if (isset($tokens) && is_array($tokens) && count($tokens) > 0) {
      foreach ($tokens as $token => $tokenCount) {
        $this->updateToken($lngId, $category, $token, $tokenCount);
      }
    }
  }

  /**
  * Update token rating in database
  * @access private
  * @param integer $lngId
  * @param string $category
  * @param string $token
  * @param integer $tokenCount
  * @return void
  */
  function updateToken($lngId, $category, $token, $tokenCount) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE spamword = '%s'
               AND spamword_lngid = '%s'
               AND spamcategory_ident = '%s'";
    $params = array($this->tableSpamWords, $token, $lngId, $category);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($res->fetchField() > 0) {
        $sql = "UPDATE %s
                   SET spamword_count = spamword_count + %d
                 WHERE spamword = '%s'
                   AND spamword_lngid = '%s'
                   AND spamcategory_ident = '%s'";
        $params = array($this->tableSpamWords, $tokenCount, $token, $lngId, $category);
        $this->databaseQueryFmtWrite($sql, $params);
      } else {
        $data = array(
          'spamword' => $token,
          'spamword_count' => $tokenCount,
          'spamword_lngid' => $lngId,
          'spamcategory_ident' => $category
        );
        $this->databaseInsertRecord($this->tableSpamWords, NULL, $data);
      }
    }
  }

  /**
  * Update category probabilities in database
  * @access private
  * @return void
  */
  function updateProbabilities() {
    $this->databaseEmptyTable($this->tableSpamCategories);
    $sql = "SELECT spamcategory_ident, spamword_lngid, SUM(spamword_count) AS wordcount
              FROM %s
             GROUP BY spamcategory_ident, spamword_lngid";
    if ($res = $this->databaseQueryFmt($sql, $this->tableSpamWords)) {
      $probabilities = array();
      $total = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $probabilities[] = $row;
        if (isset($total[$row['spamword_lngid']])) {
          $total[$row['spamword_lngid']] += $row['wordcount'];
        } else {
          $total[$row['spamword_lngid']] = $row['wordcount'];
        }
      }
      $data = array();
      foreach ($probabilities as $probability) {
        if ($total[$probability['spamword_lngid']] > 0) {
          $data[] = array(
            'spamcategory_ident' => $probability['spamcategory_ident'],
            'spamcategory_probability' => empty($probability['wordcount'])
              ? 0 : (float)$probability['wordcount'] / $total[$probability['spamword_lngid']],
            'spamcategory_words' => $probability['wordcount'],
            'spamcategory_lngid' => $probability['spamword_lngid'],
          );
        }
      }
      if (count($data) > 0) {
        $this->databaseInsertRecords($this->tableSpamCategories, $data);
      }
    }
  }
}

