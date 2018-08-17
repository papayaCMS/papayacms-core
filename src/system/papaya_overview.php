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

use Papaya\Administration;
use Papaya\Content;

/**
* Create topic list
*
* @package Papaya
* @subpackage Administration
*/
class papaya_overview extends base_db {

  /**
  * Papaya database table
  * @var string $tableTopicsPublic
  */
  var $tableTopicsPublic = PAPAYA_DB_TBL_TOPICS_PUBLIC;
  /**
  * Papaya database table
  * @var string $tableTopicsPublicTrans
  */
  var $tableTopicsPublicTrans = PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS;
  /**
  * Papaya database table
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;
  /**
  * Papaya database table
  * @var string $tableTopicsVersion
  */
  var $tableTopicsVersion = PAPAYA_DB_TBL_TOPICS_VERSIONS;
  /**
  * Papaya database table
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;
  /**
  * Papaya database table
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;
  /**
  * Papaya database table
  * @var string $tableMessages
  */
  var $tableMessages = PAPAYA_DB_TBL_MESSAGES;
  /**
  * Papaya database table
  * @var string $tableTodos
  */
  var $tableTodos = PAPAYA_DB_TBL_TODOS;
  /**
  * Papaya database table
  * @var string $tableViews
  */
  var $tableViews = PAPAYA_DB_TBL_VIEWS;
  /**
  * Papaya database table
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;

  /**
  * Topics
  * @var array $topics
  */
  var $topics = NULL;

  /**
   * @var \Papaya\Template
   */
  public $layout = NULL;

  private $_topicsAbsCount = 0;

  /**
  * Message list
  * @var array $messageList
  */
  var $messageList = NULL;
  /**
  * Users
  * @var array $users
  */
  var $users = NULL;
  /**
  * Todos
  * @var array $todos
  */
  var $todos = NULL;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'overview';

  private $_pages = NULL;
  private $_views = NULL;
  private $_modules = NULL;

  /**
   * @var base_dialog
   */
  private $dialogSearch = NULL;

  /**
  * Initialization - pipeline to initializeParams()
  *
  * @see papaya_overview::initializeParams
  * @access public
  */
  function initialize($mode = 'overview') {
    $this->initializeParams();
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = $mode;
    }
  }

  /**
  * Execution - handle parameters
  *
  * @access public
  */
  function execute() {
    switch ($this->params['cmd']) {
    case 'search':
      if (isset($this->params['search_clear'])) {
        $this->params = array('cmd' => 'search');
        $this->sessionParams = array();
      } else {
        $this->sessionParams = $this->getSessionValue($this);
        $this->initializeSessionParam('filter_title');
        $this->initializeSessionParam('filter_user');
        $this->initializeSessionParam('filter_tag');
        $this->initializeSessionParam('filter_status');
        $this->initializeSessionParam('filter_view_id');
        $this->initializeSessionParam('filter_module_guid');
        $this->initializeSessionParam('filter_date_option');
        $this->initializeSessionParam('filter_date_from');
        $this->initializeSessionParam('filter_date_to');
        $this->initializeSessionParam('filter_offset');
        $this->initializeSessionParam('filter_defines');
      }
      $this->searchFor();
      $this->setSessionValue($this, $this->sessionParams);
      if (!isset($this->topics) && !is_array($this->topics)) {
        $this->addMsg(MSG_WARNING, $this->_gt('No Page Found'));
      }
      break;
    case 'refreshpages':
      $administrationUser = $this->papaya()->administrationUser;
      if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_CACHE_CLEAR)) {
        $this->refreshPages();
      }
      break;
    }
  }

  /**
  * Add Content search dialog / search results / main page (topic/messages/todos)
  *
  * @access public
  */
  function getXML() {
    $administrationUser = $this->papaya()->administrationUser;
    $this->layout->parameters()->assign(
      array(
        'COLUMNWIDTH_LEFT' => '300px',
        'COLUMNWIDTH_RIGHT' => '100%'
      )
    );

    switch($this->params['cmd']) {
    case 'search':
      $this->layout->addLeft($this->getSearchDialog());
      $this->layout->add($this->getTopicsList('Search results', '100%', TRUE));
      break;
    default:
      $this->loadNoPublishedTopicList(
        $this->papaya()->administrationLanguage->id,
        TRUE,
        $administrationUser->options['PAPAYA_OVERVIEW_ITEMS_UNPUBLISHED']
      );
      $this->layout->add($this->getTopicsList("Not published", '100%'));
      $this->loadTopicList(
        $this->papaya()->administrationLanguage->id,
        TRUE,
        $administrationUser->options['PAPAYA_OVERVIEW_ITEMS_PUBLISHED']
      );
      $this->layout->addRight($this->getTopicsList("Latest publications", '100%'));
      $this->layout->parameters()->assign(
        array(
          'COLUMNWIDTH_LEFT' => '300px',
          'COLUMNWIDTH_RIGHT' => '50%',
          'COLUMNWIDTH_CENTER' => '50%'
        )
      );
      break;
    }
    $this->loadUsers();
    $this->loadTodoList($administrationUser->options['PAPAYA_OVERVIEW_ITEMS_TASKS']);
    $this->layout->addLeft($this->getTodoList('100%'));
    if ($administrationUser->hasPerm(Administration\Permissions::MESSAGES)) {
      $this->loadMessageList(
        TRUE,
        $administrationUser->options['PAPAYA_OVERVIEW_ITEMS_MESSAGES']
      );
      $this->layout->addLeft($this->getMessageList('100%'));
    }
    $this->getButtonsXML();
  }

  /**
  * Get buttons
  *
  * @access public
  * @return array $result
  */
  function getButtonsXML() {
    $menubar = new base_btnbuilder;
    $menubar->images = $this->papaya()->images;
    $administrationUser = $this->papaya()->administrationUser;
    if ($administrationUser->hasPerm(Administration\Permissions::SYSTEM_CACHE_CLEAR)) {
      $menubar->addButton(
        'Empty cache',
        $this->getLink(array('cmd' => 'refreshpages')),
        'actions-edit-clear',
        'Empty output cache',
        FALSE
      );
    }

    if ($result = $menubar->getXML()) {
      $this->layout->add('<menu>'.$result.'</menu>', 'menus');
    }
  }

  /**
  * Load all published Topics
  *
  * @access public
  * @param integer $lngId
  * @param boolean $desc
  * @param integer $max
  */
  function loadTopicList($lngId, $desc = TRUE, $max = 8) {
    unset($this->topics);
    $ancestorId = $this->papaya()->administrationUser->startNode;
    $ancestorCondition = '';
    if ($ancestorId > 0) {
      $ancestorCondition = \Papaya\Utility\Text::escapeForPrintf(
        sprintf(
        " AND (t.prev = %1\$d OR t.prev_path LIKE '%%;%1\$d;%%')", $ancestorId
        )
      );
    }
    $order = $desc ? 'DESC' : 'ASC';
    $sql = /** @lang TEXT */
      "SELECT tp.topic_modified AS topic_published, tt.topic_title,
              t.topic_modified, t.topic_id, t.author_id, t.topic_created,
              t.box_useparent, t.meta_useparent,
              u.givenname, u.surname, u.user_id
         FROM %s t
         LEFT JOIN %s tt ON (t.topic_id = tt.topic_id AND tt.lng_id = %d)
         LEFT JOIN %s tp ON (t.topic_id = tp.topic_id)
         LEFT JOIN %s u ON (t.author_id = u.user_id)
        WHERE (tp.topic_modified >= t.topic_modified) $ancestorCondition
        ORDER BY topic_published %s";
    $params = array(
      $this->tableTopics,
      $this->tableTopicsTrans,
      $lngId,
      $this->tableTopicsPublic,
      $this->tableAuthUser,
      $order
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $max)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topics[$row['topic_id']] = $row;
      }
    }
  }

  /**
  * Load all created but NOT published Topics
  *
  * @access public
  * @param integer $langId
  * @param boolean $desc
  * @param integer $max
  */
  function loadNoPublishedTopicList($langId, $desc = TRUE, $max = 8) {
    unset($this->topics);
    $ancestorId = $this->papaya()->administrationUser->startNode;
    $ancestorCondition = '';
    if ($ancestorId > 0) {
      $ancestorCondition = \Papaya\Utility\Text::escapeForPrintf(
        sprintf(
        " AND (t.prev = %1\$d OR t.prev_path LIKE '%%;%1\$d;%%')", $ancestorId
        )
      );
    }
    $order = $desc ? 'DESC' : 'ASC';
    $sql = /** @lang TEXT */
      "SELECT tp.topic_modified AS topic_published, tt.topic_title,
              t.topic_modified, t.topic_id, t.author_id, t.topic_created,
              t.box_useparent, t.meta_useparent,
              u.givenname, u.surname, u.user_id
         FROM %s t
         LEFT JOIN %s tt ON (t.topic_id = tt.topic_id AND tt.lng_id = %d)
         LEFT JOIN %s tp ON (t.topic_id = tp.topic_id)
         LEFT JOIN %s u ON (t.author_id = u.user_id)
        WHERE (tp.topic_id IS NULL OR (tp.topic_modified < t.topic_modified)) $ancestorCondition
        ORDER BY t.topic_modified %s";
    $params = array(
      $this->tableTopics,
      $this->tableTopicsTrans,
      $langId,
      $this->tableTopicsPublic,
      $this->tableAuthUser,
      $order
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $max)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topics[$row['topic_id']] = $row;
      }
    }
  }

  /**
   * Build a list of all Topics
   *
   * @access public
   * @param string $title
   * @param int $width
   * @param bool $showDetails
   * @return string
   */
  function getTopicsList($title, $width = 490, $showDetails = FALSE) {
    if (isset($this->topics) && is_array($this->topics) && count($this->topics) > 0) {
      $listview = new \Papaya\UI\Listview();
      $listview->caption = new \Papaya\UI\Text\Translated($title);
      $listview->toolbars()->topLeft->elements[] = $paging = new \Papaya\UI\Toolbar\Paging(
        array($this->paramName, 'filter_offset'),
        (int)$this->_topicsAbsCount,
        \Papaya\UI\Toolbar\Paging::MODE_OFFSET
      );
      $paging->reference()->setParameters(
        array(
          'cmd' => 'search'
        ),
        $this->paramName
      );
      $paging->currentOffset = isset($this->params['filter_offset'])
        ? (int)$this->params['filter_offset'] : 0;

      foreach ($this->topics as $topic) {
        $title = '';
        if (empty($topic['topic_title'])) {
          $pageTitle = new \Papaya\UI\Text\Translated('No Title');
        } else {
          $pageTitle = $topic['topic_title'];
        }
        $pageTitle .= ' #'.$topic['topic_id'];
        if (isset($topic['ancestors'])) {
          $title .= $this->getAncestorsTitle(
            $topic['ancestors'],
            $this->papaya()->options->get('PAPAYA_UI_SEARCH_CHARACTER_LIMIT', 100) - strlen($pageTitle),
            $this->papaya()->options->get('PAPAYA_UI_SEARCH_ANCESTOR_LIMIT',  0)
          );
        }
        $title .= $pageTitle;
        if (isset($topic['topic_published']) &&
            $topic['topic_published'] < $topic['topic_modified']) {
          $image = 'status-page-modified';
        } elseif (isset($topic['topic_published']) &&
                  $topic['topic_published'] >= $topic['topic_modified']) {
          $image = 'status-page-published';
        } else {
          $image = 'status-page-created';
        }
        $text = $topic['givenname']." ".$topic['surname'];
        if (isset($topic['topic_modified']) && $topic['topic_modified'] > 0) {
          $text .= sprintf(
            ', %s: %s',
            new \Papaya\UI\Text\Translated('Modified'),
            new \Papaya\UI\Text\Date($topic['topic_modified'])
          );
        } else {
          $text .= sprintf(
            ', %s: %s',
            new \Papaya\UI\Text\Translated('Created'),
            new \Papaya\UI\Text\Date($topic['topic_created'])
          );
        }
        if (isset($topic['topic_published']) && $topic['topic_published'] > 0) {
          $text .= sprintf(
            ', %s: %s',
            new \Papaya\UI\Text\Translated('Published'),
            new \Papaya\UI\Text\Date($topic['topic_published'])
          );
        }
        $listview->items[] = $item = new \Papaya\UI\Listview\Item($image, $title);
        $item->text = $text;
        $item->emphased = ($topic['user_id'] == $this->papaya()->administrationUser->userId);
        $item->reference()->setRelative('topic.php');
        $item->reference()->setParameters(array('page_id' => $topic['topic_id']), 'tt');

        if ($showDetails) {
          $text = $topic['view_title'];
          if (!empty($topic['module_title']) && $topic['module_title'] != $topic['view_title']) {
            $text .= ' ('.$topic['module_title'].')';
          }
          $item->subitems[] = $subitem = new \Papaya\UI\Listview\Subitem\Text($text);
        }
      }
      return $listview->getXML();
    }
    return '';
  }

  public function getAncestorsTitle($ids, $characterLimit = 20, $itemLimit = 1) {
    $result = '';
    $pages = $this->pages();
    if ($itemLimit == 0) {
      return '';
    }
    $sorted = [];
    $max = ceil(count($ids) / 2);
    for ($i = 0; $i < $max; $i++) {
      $sorted[] = $ids[$i];
      $x = count($ids) - $i;
      if ($i < $x && isset($ids[$x])) {
        $sorted[] = $ids[$x];
      }
    }
    $buffers = [ 0 => '', 1 => ''];
    foreach ($sorted as $index => $id) {
      $itemLimit--;
      if (
        (strlen($buffers[0]) + strlen($buffers[1]) + strlen($pages[$id]['title']) + 4) >= $characterLimit ||
        $itemLimit < 0
      ) {
        return $buffers[0] . ' … > ' . $buffers[1];
      }
      if (isset($pages[$id])) {
        $buffers[$index % 2] .= sprintf('%s #%d > ', $pages[$id]['title'], $id);
      }
    }
    return $buffers[0] . $buffers[1];
  }

  /**
  * Load all NEW messages
  *
  * @param boolean $desc
  * @param integer $max
  * @access public
  */
  function loadMessageList($desc = TRUE, $max = 8) {
    if ($desc) {
      $order = "DESC";
    } else {
      $order = "ASC";
    }
    unset($this->messageList);
    $sql = "SELECT msg_id, msg_owner_id, msg_folder_id, msg_from, msg_datetime,
                   msg_subject, msg_priority, msg_type, msg_new
              FROM %s
             WHERE msg_owner_id = '%s' AND msg_folder_id = '0'
             ORDER BY msg_new DESC, msg_datetime %s";
    $params = array(
      $this->tableMessages,
      $this->papaya()->administrationUser->user['user_id'],
      $order
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $max)) {
      while ($messages = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->messageList[$messages['msg_id']] = $messages;
      }
    }
  }

  /**
  * Build a list of all NEW Messages
  *
  * @access public
  */
  function getMessageList($width = '490') {
    $result = '';
    if (isset($this->messageList) &&
        is_array($this->messageList) && count($this->messageList) > 0) {
      $result .= sprintf(
        '<listview width="%s" title="%s">'.LF,
        papaya_strings::escapeHTMLChars($width),
        papaya_strings::escapeHTMLChars($this->_gt('Messages'))
      );
      $result .= '<items>'.LF;
      $result .= $this->getMessageElements();
      $result .= '</items></listview>'.LF;
    }
    return $result;
  }

  /**
  * Build listitems
  *
  * @access public
  * @return string $result
  */
  function getMessageElements() {
    $result = '';
    if (isset($this->messageList) && is_array($this->messageList)) {
      $images = $this->papaya()->images;
      foreach ($this->messageList as $values) {
        if (isset($values) && is_array($values)) {
          if ($values['msg_new']) {
            $imageIdx = 'status-mail-new';
          } else {
            $imageIdx = 'items-mail';
          }
          $result .= sprintf(
            '<listitem title="%s" image="%s" href="%s" subtitle="%s">',
            papaya_strings::escapeHTMLChars($values['msg_subject']),
            papaya_strings::escapeHTMLChars($images[$imageIdx]),
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array('msg_id' => $values['msg_id']),
                'msg',
                'msgbox.php'
              )
            ),
            papaya_strings::escapeHTMLChars(
              isset($this->users[$values['msg_from']])
                ? $this->getUserEmailName($this->users[$values['msg_from']])
                : ''
            )
          );
          $imageIdx = $this->getPriorityGlyphIndex($values['msg_priority']);
          if (!empty($imageIdx)) {
            $result .= sprintf(
              '<subitem align="right"><glyph src="%s"/></subitem>'.LF,
              papaya_strings::escapeHTMLChars(
                $images[$imageIdx]
              )
            );
          } else {
            $result .= '<subitem/>';
          }
          $result .= '</listitem>'.LF;
        }
      }
    }
    return $result;
  }

  /**
  * Get priority glyph index
  *
  * @param integer $priority
  * @access public
  * @return integer
  */
  function getPriorityGlyphIndex($priority) {
    switch($priority) {
    case 2:
      return 'status-priority-highest';
    case 1:
      return 'status-priority-high';
    default:
      return FALSE;
    }
  }

  /**
  * Get the full name of a user
  *
  * @param array $userData
  * @access public
  * @return integer
  */
  function getUserEmailName($userData) {
    if (isset($userData)) {
      return strtr($userData['givenname'].' '.$userData['surname'], ',;', '');
    }
    return '';
  }

  /**
  * Load all User
  *
  * @access public
  */
  function loadUsers() {
    unset($this->users);
    $sql = "SELECT user_id, surname, givenname
              FROM %s
             ORDER BY surname, givenname";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableAuthUser))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->users[$row['user_id']] = $row;
      }
    }
  }

  /**
   * Load all todos
   *
   * @param integer $max
   * @access public
   */
  function loadTodoList($max = 8) {
    $sql = "SELECT td.todo_id, td.title, td.priority, td.status, td.date_to,
                   td.date_to, td.user_id_from, td.topic_id
              FROM %s td
             WHERE td.user_id_to = '%s'
             ORDER BY td.date_to ASC";
    $params = array(
      $this->tableTodos,
      $this->papaya()->administrationUser->user["user_id"]
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $max)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->todos[$row["todo_id"]] = $row;
      }
    }
  }

  /**
   * Build a list of all NEW Messages
   *
   * @access public
   * @param string $width
   * @return string $result
   */
  function getTodoList($width = '490') {
    $result = '';
    if (isset($this->todos) && is_array($this->todos) && count($this->todos) > 0) {
      $result .= sprintf(
        '<listview width="%s" title="%s">'.LF,
        papaya_strings::escapeHTMLChars($width),
        papaya_strings::escapeHTMLChars($this->_gt('Open todos'))
      );
      $result .= '<items>'.LF;
      $result .= $this->getTodoElements();
      $result .= '</items></listview>'.LF;
    }
    return $result;
  }

  /**
  * Build listitems
  *
  * @access public
  * @return string $result
  */
  function getTodoElements() {
    $result = '';
    $images = $this->papaya()->images;
    if (isset($this->todos) && is_array($this->todos)) {
      foreach ($this->todos as $values) {
        if (isset($values) && is_array($values)) {
          if ($values['date_to'] > 0 ) {
            $dateTo = ' subtitle="'.date("Y-m-d H:i", $values['date_to']).'"';
          } else {
            $dateTo = '';
          }
          if (!($iconIndex = $this->getPriorityGlyphIndex($values['priority']))) {
            $iconIndex = 'items-task';
          }
          $result .= sprintf(
            '<listitem title="%s" %s href="%s" image="%s">',
            papaya_strings::escapeHTMLChars($values['title']),
            $dateTo,
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array('todo_id' => $values['todo_id']),
                'todo',
                'todo.php'
              )
            ),
            papaya_strings::escapeHTMLChars($images[$iconIndex])
          );
          if ($iconIndex = $this->getStatusGlyphIndex($values['status'])) {
            $result .= sprintf(
              '<subitem align="right"><glyph src="%s"/></subitem>'.LF,
              papaya_strings::escapeHTMLChars($images[$iconIndex])
            );
          } else {
            $result .= '<subitem/>'.LF;
          }
          $result .= '</listitem>'.LF;
        }
      }
    }
    return $result;
  }

  /**
  * Get status glyph index
  *
  * @param integer $status
  * @access public
  * @return integer
  */
  function getStatusGlyphIndex($status) {
    switch($status) {
    case 2:
      return 'status-sign-problem';
    case 1:
      return 'status-sign-warning';
    default:
      return 'status-sign-info';
    }
  }

  /**
  * Get search dialog
  *
  * @see base_dialog::getDialogXML
  * @access public
  * @return string xml
  */
  function getSearchDialog() {
    $this->initializeSearchDialog();
    return $this->dialogSearch->getDialogXML();
  }

  /**
  * Build Page Search dialog
  *
  * @access public
  */
  function initializeSearchDialog() {
    if (!(isset($this->dialogSearch) && is_object($this->dialogSearch))) {
      $hidden = array (
        'cmd' => 'search',
        'filter_offset' => 0
      );
      $data = array();
      if (empty($data['filter_date_from'])) {
        $data['filter_date_from'] = new \Papaya\UI\Text\Date(time() - (86400 * 7));
      }
      if (empty($data['filter_date_to'])) {
        $data['filter_date_to'] = new \Papaya\UI\Text\Date(time() + 86400);
      }
      $statusComboValue = array(
        'all' => $this->_gt('All'),
        'created' => $this->_gt('Created'),
        'modified' => $this->_gt('modified'),
        'published' => $this->_gt('published')
      );
      $statusComboField = array(
        'all' => $this->_gt('Ignore'),
        'created' => $this->_gt('Created'),
        'modified' => $this->_gt('modified'),
        'published' => $this->_gt('published')
      );
      $this->modules()->load(array('type' => 'page', 'is_active' => TRUE));
      $modules = iterator_to_array(
        new \Papaya\Iterator\Union(
          \Papaya\Iterator\Union::MIT_KEYS_ASSOC,
          new ArrayIterator(array('' => 'All')),
          new \Papaya\Iterator\ArrayMapper($this->modules(), 'title')
        )
      );
      $this->views()->load(array('module_type' => 'page'));
      $views = iterator_to_array(
        new \Papaya\Iterator\Union(
          \Papaya\Iterator\Union::MIT_KEYS_ASSOC,
          new ArrayIterator(array('' => 'All')),
          new \Papaya\Iterator\ArrayMapper($this->views(), 'title')
        )
      );

      $fields = array(
        'filter_title' => array('Title', 'isSometext', FALSE, 'input', 255, '', ''),
        'filter_user' => array('Username', 'isSometext', FALSE, 'input', 255, '', ''),
        'filter_tag' => array('Tag', 'isSometext', FALSE, 'input', 255, '', ''),
        'filter_status' => array('Status', 'isNum', TRUE, 'combo',
          $statusComboValue, '', 1),
        'Type',
        'filter_module_guid' => array(
           'Module', 'isGuid', TRUE, 'combo', $modules, ''
        ),
        'filter_view_id' => array(
           'View', 'isNum', TRUE, 'combo', $views, ''
        ),
        'Date',
        'filter_date_option' => array('Search for', 'isNum', TRUE, 'combo',
          $statusComboField, '', 1),
        'filter_date_from' => array('From', 'isISODate', TRUE, 'datetime', 255),
        'filter_date_to' => array('To', 'isISODate', TRUE, 'datetime', 255),
        'Additional',
        'filter_defines' => array(
          'Defines',
          'isAlpha',
          FALSE,
          'checkgroup',
          array(
            'meta' => new \Papaya\UI\Text\Translated('Meta Tags'),
            'boxes' => new \Papaya\UI\Text\Translated('Boxes')
          )
        )
      );
      $this->dialogSearch = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogSearch->params = $this->params;
      $this->dialogSearch->dialogTitle = $this->_gt('Search Page');
      $this->dialogSearch->buttonTitle = 'Search';
      $this->dialogSearch->inputFieldSize = 'x-small';
      $this->dialogSearch->dialogDoubleButtons = FALSE;
      $this->dialogSearch->addButton('search_clear', new \Papaya\UI\Text\Translated('Reset'));
    }
  }

  /** Fill array with all searchresults
  *
  * @access public
  */
  function searchFor($limit = 20) {

    $sql = "SELECT t.topic_id, t.prev, t.prev_path, t.topic_created, t.topic_modified,
                   t.box_useparent, t.meta_useparent,
                   tp.topic_modified AS topic_published,
                   au.givenname, au.surname, au.user_id,
                   tt.topic_title,
                   v.view_id, v.view_title, m.module_title
              FROM %s t
              LEFT OUTER JOIN %s au ON (t.author_id = au.user_id)
              LEFT OUTER JOIN %s tt ON (tt.topic_id = t.topic_id AND lng_id = %d)
              LEFT OUTER JOIN %s tp ON (tp.topic_id = t.topic_id)
              LEFT OUTER JOIN %s v ON (v.view_id= tt.view_id)
              LEFT OUTER JOIN %s m ON (m.module_guid = v.module_guid)";
    $dateFrom = empty($this->params['filter_date_from'])
      ? 0 : \Papaya\Utility\Date::stringToTimestamp($this->params['filter_date_from']);
    $dateTo = empty($this->params['filter_date_to'])
      ? time() + 86400
      : \Papaya\Utility\Date::stringToTimestamp($this->params['filter_date_to']) + 86400;
    $sqlParams = array(
      $this->tableTopics,
      $this->tableAuthUser,
      $this->tableTopicsTrans,
      $this->papaya()->administrationLanguage->getCurrent()->id,
      $this->tableTopicsPublic,
      $this->tableViews,
      $this->tableModules,
      $dateFrom,
      $dateTo
    );
    $sqlString = new searchstringparser;

    $conditions = array();
    $subSelectConditions = array();
    if (isset($this->params['filter_title']) && $this->params['filter_title'] != "") {
      if (strlen($this->params['filter_title']) > 2) {
        $subSelectConditions['title'] = $sqlString->getSQL(
          $this->params['filter_title'], array('sub_tt.topic_title')
        );
      } else {
        $this->addMsg(
          MSG_INFO,
          sprintf(
            $this->_gt('%s too short. (%d characters minimum)'),
            $this->_gt('Title'),
            3
          )
        );
      }
    }
    if (isset($this->params['filter_view_id']) && $this->params['filter_view_id'] > 0) {
      $subSelectConditions['view'] = $this->databaseGetSqlCondition(
        'sub_tt.view_id', (int)$this->params['filter_view_id']
      );
    } elseif (isset($this->params['filter_module_guid']) &&
              $this->params['filter_module_guid'] != '') {
      $subSelectConditions['module'] = $this->databaseGetSqlCondition(
        'sub_v.module_guid', $this->params['filter_module_guid']
      );
    }
    if (!empty($subSelectConditions)) {
      if (isset($subSelectConditions['module'])) {
        $conditions[] = " t.topic_id IN (
          SELECT sub_tt.topic_id
            FROM ".$this->tableTopicsTrans." AS sub_tt
            JOIN ".$this->tableViews." AS sub_v ON (sub_v.view_id = sub_tt.view_id)
           WHERE ".implode(' AND ', $subSelectConditions).") ";
      } else {
        $conditions[] = " t.topic_id IN (
          SELECT sub_tt.topic_id
            FROM ".$this->tableTopicsTrans." AS sub_tt
           WHERE ".implode(' AND ', $subSelectConditions).") ";
      }
    }
    if (isset($this->params['filter_tag']) && $this->params['filter_tag'] != "") {
      if (strlen($this->params['filter_tag']) > 2) {
        $conditions[] = sprintf(
          " t.topic_id IN (
             SELECT taglinks.link_id
               FROM %s AS taglinks, %s AS tagdata
              WHERE taglinks.tag_id = tagdata.tag_id
                AND taglinks.link_type = 'topic'
                AND %s
           )",
          $this->getDatabaseAccess()->getTableName(\Papaya\Content\Tables::TAG_LINKS),
          $this->getDatabaseAccess()->getTableName(\Papaya\Content\Tables::TAG_TRANSLATIONS),
          $sqlString->getSQL($this->params['filter_tag'], array('tagdata.tag_title'))
        );
      } else {
        $this->addMsg(
          MSG_INFO,
          sprintf(
            $this->_gt('%s too short. (%d characters minimum)'),
            $this->_gt('Tag'),
            3
          )
        );
      }
    }

    if (isset($this->params['filter_user']) && $this->params['filter_user'] != "") {
      if (strlen($this->params['filter_user']) > 2) {
        $conditions[] = $sqlString->getSQL(
          $this->params['filter_user'],
          array('au.givenname', 'au.surname')
        );
      } else {
        $this->addMsg(MSG_INFO, $this->_gt('Searchstring "Username" to short'));
      }
    }
    if (isset($this->params['filter_status'])) {
      switch ($this->params['filter_status']) {
      case 'all' :
        break;
      case 'modified' :
        $conditions[] = 't.topic_modified > tp.topic_modified';
        break;
      case 'published' :
        $conditions[] = 't.topic_modified <= tp.topic_modified';
        break;
      case 'created' :
        $conditions[] = 'tp.topic_modified IS NULL';
        break;
      }
    }
    if (isset($this->params['filter_date_option'])) {
      switch ($this->params['filter_date_option']) {
      case 'created':
        $conditions[] = sprintf(
          "t.topic_created >= '%d' AND t.topic_created <= '%d'",
          \Papaya\Utility\Date::stringToTimestamp($this->params['filter_date_from']),
          \Papaya\Utility\Date::stringToTimestamp($this->params['filter_date_to'])
        );
        break;
      case 'modified':
        $conditions[] = sprintf(
          "t.topic_modified >= '%d' AND t.topic_modified <= '%d'",
          \Papaya\Utility\Date::stringToTimestamp($this->params['filter_date_from']),
          \Papaya\Utility\Date::stringToTimestamp($this->params['filter_date_to'])
        );
        break;
      case 'published':
        $conditions[] = sprintf(
          "tp.topic_modified >= '%d' AND tp.topic_modified <= '%d'",
          \Papaya\Utility\Date::stringToTimestamp($this->params['filter_date_from']),
          \Papaya\Utility\Date::stringToTimestamp($this->params['filter_date_to'])
        );
        break;
      }
    }
    if (isset($this->params['filter_defines']) && is_array($this->params['filter_defines'])) {
      foreach ($this->params['filter_defines'] as $define) {
        switch ($define) {
        case 'meta' :
          $conditions[] = "t.meta_useparent = 0";
          break;
        case 'boxes' :
          $conditions[] = "t.box_useparent = 0";
          break;
        }
      }
    }
    $ancestorId = $this->papaya()->administrationUser->startNode;
    if ($ancestorId > 0) {
      $conditions[] = sprintf(
        "(t.prev = %1\$d OR t.prev_path LIKE '%%;%1\$d;%%')", $ancestorId
      );
    }

    if (!empty($conditions)) {
      $sql .= ' WHERE '.str_replace('%', '%%', implode(' AND ', $conditions));
    }
    $sql .= " ORDER BY t.topic_modified DESC";

    $allAncestors = array();
    $offset = empty($this->params['filter_offset']) ? 0 : (int)$this->params['filter_offset'];
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $key = $row["topic_id"]."-".(empty($row["lng_id"]) ? 0 : (int)$row["lng_id"]);
        $ancestors = \Papaya\Utility\Arrays::decodeIdList($row['prev_path']);
        $ancestors[] = (int)$row['prev'];
        array_shift($ancestors);
        $allAncestors = array_merge($allAncestors, $ancestors);
        $row['ancestors'] = $ancestors;
        $this->topics[$key] = $row;
      }
      $this->_topicsAbsCount = $res->absCount();
      $this->pages()->load(
        array(
          'id' => array_unique($allAncestors),
          'language_id' => $this->papaya()->administrationLanguage->getCurrent()->id,
          'ancestor_id' => $ancestorId
        )
      );
    }
  }

  /**
   * Getter/Setter for a pages list subobject
   *
   * @param \Papaya\Content\Pages $pages
   * @return \Papaya\Content\Pages
   */
  public function pages(Content\Pages $pages = NULL) {
    if (isset($pages)) {
      $this->_pages = $pages;
    } elseif (NULL == $this->_pages) {
      $this->_pages = new Content\Pages();
      $this->_pages->papaya($this->papaya());
    }
    return $this->_pages;
  }

  /**
   * Getter/Setter for a view list subobject
   *
   * @param \Papaya\Content\Views $views
   * @return \Papaya\Content\Views
   */
  public function views(Content\Views $views = NULL) {
    if (isset($views)) {
      $this->_views = $views;
    } elseif (NULL == $this->_views) {
      $this->_views = new Content\Views();
      $this->_views->papaya($this->papaya());
    }
    return $this->_views;
  }

  /**
   * Getter/Setter for a modules list subobject
   *
   * @param \Papaya\Content\Modules $modules
   * @return \Papaya\Content\Modules
   */
  public function modules(Content\Modules $modules = NULL) {
    if (isset($modules)) {
      $this->_modules = $modules;
    } elseif (NULL == $this->_modules) {
      $this->_modules = new Content\Modules();
      $this->_modules->papaya($this->papaya());
    }
    return $this->_modules;
  }

  /**
  * Refresh pages
  *
  * @access public
  */
  function refreshPages() {
    $cache = Cache::getService($this->papaya()->options);
    $counter = $cache->delete();
    if ($counter > 0) {
      $this->addMsg(MSG_INFO, sprintf($this->_gt('%s files deleted.'), $counter));
    } else {
      $this->addMsg(MSG_INFO, $this->_gt('Cache was empty - no files deleted.'));
    }
    $mediaDB = new papaya_mediadb();
    $fileCounter = $mediaDB->clearCacheDirectory();
    if ($fileCounter > 0) {
      $this->addMsg(
        MSG_INFO,
        sprintf($this->_gt('%d media file softlinks deleted.'), $fileCounter)
      );
    }
  }
}

