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

/**
* Object to display an alias tree (n-dimensional)
*
* @package Papaya
* @subpackage Administration
*/
class papaya_alias_tree extends base_db {
  /**
  * Papaya database table auth user
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;
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
  * Papaya database table public topics
  * @var string $tableTopicsPublic
  */
  var $tableTopicsPublic = PAPAYA_DB_TBL_TOPICS_PUBLIC;
  /**
  * Papaya database table public topics translations
  * @var string $tableTopicsPublicTRANS
  */
  var $tableTopicsPublicTRANS = PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * Papaya database table views
  * @var string $tableViews
  */
  var $tableViews = PAPAYA_DB_TBL_VIEWS;
  /**
  * Papaya database table urls
  * @var string $tableUrls
  */
  var $tableUrls = PAPAYA_DB_TBL_URLS;
  /**
  * Papaya database table output filter / view modes
  * @var string $tableViewModes
  */
  var $tableViewModes = PAPAYA_DB_TBL_VIEWMODES;

  /**
  * Aliases
  * @var array $aliases
  */
  var $aliases;

  /**
  * Object instance of the alias plugin
  * @var object base_plugin_alias
  */
  var $aliasPlugin = NULL;

  /**
  * Links
  * @var array $links
  */
  var $links;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'altr';

  /**
   * @var array
   */
  private $viewModes = array();

  /**
   * @var base_dialog
   */
  private $dialogAlias = NULL;

  /**
   * @var array|NULL
   */
  private $alias = NULL;

  /**
   * @var array|NULL
   */
  private $modules = NULL;

  /**
   * @var \Papaya\Template
   */
  public $layout = NULL;

  /**
  * Initilization
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
  }

  /**
  * Execute - base function for handlig parameters
  *
  * @access public
  */
  function execute() {
    $user = $this->papaya()->administrationUser;
    if ($user->hasPerm(Administration\Permissions::ALIAS_MANAGE)) {
      if (isset($this->params['cmd'])) {
        switch ($this->params['cmd']) {
        case 'alias_delete':
          if (isset($this->params['alias_id']) &&
              $this->params['alias_id'] > 0 &&
              isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete']) {
            if ($this->deleteAlias((int)$this->params['alias_id'])) {
              unset($this->params['cmd']);
              $this->addMsg(MSG_INFO, $this->_gt('Alias deleted.'));
            } else {
              $this->addMsg(MSG_WARNING, $this->_gt('Database error'));
            }
          }
          break;
        case 'alias_create':
          $this->initializeAliasDialog();
          if ($this->dialogAlias->checkDialogInput() &&
              $this->checkAliasInput()) {
            if ($newId = $this->createAlias()) {
              unset($this->dialogAlias);
              $this->params['alias_id'] = $newId;
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
            } else {
              $this->addMsg(
                MSG_WARNING,
                $this->_gt('Database error! Changes not saved.')
              );
            }
          }
          break;
        case 'alias_edit':
          if (isset($this->params['alias_id']) &&
              $this->params['alias_id'] > 0 &&
              $this->loadAlias($this->params['alias_id'])) {
            $this->initializeAliasDialog();
            if ($this->dialogAlias->checkDialogInput() &&
                $this->checkAliasInput()) {
              if ($this->saveAlias($this->dialogAlias->data)) {
                unset($this->dialogAlias);
                $this->load($this->params['alias_id']);
                $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
              } else {
                $this->addMsg(
                  MSG_WARNING,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            }
          }
          break;
        }
      }
      if (isset($this->params['alias_id']) && $this->params['alias_id'] > 0) {
        $this->loadAlias($this->params['alias_id']);
      }
    }
  }

  /**
  * Get XML
  *
  * @access public
  */
  function getXML() {
    $this->load();
    $this->layout->addLeft($this->get());
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    switch($this->params['cmd']) {
    case 'alias_delete' :
      $this->layout->add($this->getDeleteForm());
      break;
    default :
      $this->layout->add($this->getAliasDialog());
      break;
    }
    $this->getButtonsXML();
  }

  /**
  * Get
  *
  * @access public
  * @return string $result
  */
  function get() {
    $result = '';
    if (isset($this->aliases) && is_array($this->aliases) &&
        count($this->aliases) > 0) {
      $result .= sprintf(
        '<listview width="450" title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Alias'))
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Path'))
      );
      $result .= '<col/>'.LF;
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      $result .= $this->getElements();
      $result .= '</items></listview>'.LF;
    } else {
      $this->addMsg(MSG_INFO, $this->_gt('No aliases defined.'));
    }
    return $result;
  }

  /**
   * Load
   *
   * @internal param int $baseId
   * @access public
   */
  function load() {
    unset($this->aliases);
    unset($this->links);
    $sql = "SELECT a.id, a.path, a.path_pattern, a.topic_id as alias_topic_id,
                   a.url_domain, a.url_redirectmode, a.target_url,
                   t.topic_id, t.topic_modified, tt.topic_title,
                   tp.topic_modified as topic_published,
                   v.view_id, v.view_title,
                   f.viewmode_id, f.viewmode_ext,
                   m.module_guid, m.module_title
              FROM %s a
              LEFT OUTER JOIN %s t ON t.topic_id = a.topic_id
              LEFT OUTER JOIN %s tt
                ON (tt.topic_id = t.topic_id AND tt.lng_id = %d)
              LEFT OUTER JOIN %s tp ON a.topic_id = tp.topic_id
              LEFT OUTER JOIN %s v ON (v.view_id = tt.view_id)
              LEFT OUTER JOIN %s f ON (f.viewmode_id = a.viewmode_id)
              LEFT OUTER JOIN %s m ON (m.module_guid = a.module_guid)
             ORDER BY a.path\r\n";
    $params = array(
      $this->tableUrls,
      $this->tableTopics, $this->tableTopicsTrans,
      $this->papaya()->administrationLanguage->id,
      $this->tableTopicsPublic, $this->tableViews, $this->tableViewModes,
      $this->tableModules);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->aliases[$row['id']] = $row;
        $this->links[$row['alias_topic_id']][] = $row['id'];
      }
    }
  }

  /**
  * Load alias
  *
  * @param integer $aliasId
  * @access public
  * @return boolean
  */
  function loadAlias($aliasId) {
    unset($this->alias);
    $sql = "SELECT id, lng_id, viewmode_id,
                   topic_id, path, path_pattern,
                   url_domain, url_params, url_redirectmode,
                   target_url,
                   module_guid, module_params
              FROM %s
             WHERE id = %d";
    if (
      $res = $this->databaseQueryFmt(
        $sql, array($this->tableUrls, $aliasId)
      )
    ) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->alias = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load list of available view modes
  * @return boolean
  */
  function loadViewModeList() {
    unset($this->viewModes);
    $sql = "SELECT viewmode_id, viewmode_ext
              FROM %s
              ORDER BY viewmode_ext";
    if ($res = $this->databaseQueryFmt($sql, $this->tableViewModes)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->viewModes[$row['viewmode_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * load a list with the alias modules
  *
  * @access public
  */
  function loadModuleList() {
    unset($this->modules);
    $sql = "SELECT module_guid, module_title
              FROM %s
             WHERE module_type = 'alias'
               AND module_active = 1";
    $params = array($this->tableModules);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->modules[$row['module_guid']] = $row;
      }
    }
  }

  /**
  * Get elemtents
  *
  * @access public
  * @return string $result
  */
  function getElements() {
    $result = '';
    if (isset($this->aliases) && is_array($this->aliases)) {
      $counter = 0;
      foreach ($this->aliases as $id => $val) {
        if (isset($val) && is_array($val)) {
          $counter++;
          if (isset($this->params['alias_id']) && (int)$this->params['alias_id'] == (int)$id) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $href = $this->getLink(array('alias_id' => $id));
          if ($val['topic_published']) {
            if ($val['topic_published'] >= $val['topic_modified']) {
              $imageIdx = 'status-page-published';
            } else {
              $imageIdx = 'status-page-modified';
            }
          } elseif (!$val['topic_id']) {
            $imageIdx = 'status-page-warning';
          } else {
            $imageIdx = 'status-page-created';
          }
          if (!empty($val['url_domain'])) {
            $title = $val['url_domain'].$val['path'];
          } else {
            $title = '*'.$val['path'];
          }
          $result .= sprintf(
            '<listitem title="%s" href="%s" image="%s" span="1" %s>',
            papaya_strings::escapeHTMLChars($title),
            papaya_strings::escapeHTMLChars($href),
            papaya_strings::escapeHTMLChars($this->papaya()->images['items-alias']),
            $selected
          );
          if ($val['url_redirectmode'] == 3) {
            $result .= sprintf(
              '<subitem align="center">%s</subitem>'.LF,
              papaya_strings::escapeHTMLChars($this->_gt('Url'))
            );
            $result .= '</listitem>'.LF;
            if (isset($val['target_url']) && !empty($val['target_url'])) {
              $result .= sprintf(
                '<listitem image="%s" title="%s" indent="1" span="2" />'.LF,
                papaya_strings::escapeHTMLChars($this->papaya()->images['items-alias']),
                papaya_strings::escapeHTMLChars($val['target_url'])
              );
            }
          } elseif ($val['url_redirectmode'] == 2) {
            $result .= sprintf(
              '<subitem align="center">%s</subitem>'.LF,
              papaya_strings::escapeHTMLChars($this->_gt('Module'))
            );
            $result .= '</listitem>'.LF;
            if (isset($val['module_guid']) && !empty($val['module_title'])) {
              $result .= sprintf(
                '<listitem image="%s" title="%s" indent="1" span="2" />'.LF,
                papaya_strings::escapeHTMLChars($this->papaya()->images['items-plugin']),
                papaya_strings::escapeHTMLChars($val['module_title'])
              );
            } else {
              $result .= sprintf(
                '<listitem image="%s" title="%s" indent="1" span="2" />'.LF,
                papaya_strings::escapeHTMLChars($this->papaya()->images['items-plugin']),
                papaya_strings::escapeHTMLChars($this->_gt('Unknown Module'))
              );
            }
          } else {
            if ($val['url_redirectmode'] == 1) {
              $result .= sprintf(
                '<subitem align="center">%s</subitem>'.LF,
                papaya_strings::escapeHTMLChars($this->_gt('Page Frameset'))
              );
            } else {
              $result .= sprintf(
                '<subitem align="center">%s</subitem>'.LF,
                papaya_strings::escapeHTMLChars($this->_gt('Page'))
              );
            }
            $result .= '</listitem>'.LF;
            if ($val['topic_id']) {
              $result .= sprintf(
                '<listitem image="%s" title="%s #%d" indent="1" span="1">'.LF,
                papaya_strings::escapeHTMLChars($this->papaya()->images[$imageIdx]),
                papaya_strings::escapeHTMLChars($val['topic_title']),
                (int)$val['alias_topic_id']
              );
              $result .= sprintf(
                '<subitem align="right"><a href="%s"><glyph src="%s" /></a></subitem>',
                papaya_strings::escapeHTMLChars(
                  $this->getLink(array('page_id' => $val['topic_id']), 'tt', 'topic.php')
                ),
                papaya_strings::escapeHTMLChars($this->papaya()->images['actions-edit'])
              );
              $result .= '</listitem>'.LF;
            } else {
              $result .= sprintf(
                '<listitem image="%s" title="%s #%d" indent="1" span="1"/>'.LF,
                papaya_strings::escapeHTMLChars($this->papaya()->images[$imageIdx]),
                papaya_strings::escapeHTMLChars($this->_gt('404 - invalid page')),
                (int)$val['alias_topic_id']
              );
            }
          }
          if (isset($this->params['alias_id']) && $this->params['alias_id'] == $id &&
              $val['url_redirectmode'] < 2) {
            $result .= $this->getSubElements($val['topic_id'], $val['id'], $counter);
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get sub Elements
  *
  * @param integer $id
  * @param string $caller
  * @access public
  * @return string $result
  */
  function getSubElements($id, $caller) {
    $result = "";
    if (isset($this->links[$id]) && is_array($this->links[$id])) {
      foreach ($this->links[$id] as $linkId) {
        if ($linkId !== $caller) {
          $result .= sprintf(
            '<listitem title="%s" indent="2" image="%s" href="%s" span="3">',
            papaya_strings::escapeHTMLChars($this->aliases[$linkId]['path']),
            papaya_strings::escapeHTMLChars($this->papaya()->images['items-alias']),
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('cmd' => 'edit', 'alias_id' => $linkId))
            )
          );
          $result .= '</listitem>';
        }
      }
    }
    return $result;
  }

  /**
  * Get delete form
  *
  * @see base_msgdialog::getMsgDialog
  * @access public
  * @return string XML
  */
  function getDeleteForm() {
    $hidden = array(
      'cmd' => 'alias_delete',
      'alias_id' => $this->alias['id'],
      'confirm_delete' => 1,
    );
    $msg = sprintf(
      $this->_gt('Really delete Alias "%s"?'),
      $this->checkPathSlashes($this->alias['path'])
    );
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Check path slashes
  *
  * @param string $path
  * @access public
  * @return string
  */
  function checkPathSlashes($path) {
    $result = trim($path);
    if (substr($path, 0, 1) != '/') {
      $result = '/'.$path;
    }
    if (substr($result, -1) != '/' && substr($result, -2) != '/*') {
      $result .= '/';
    }
    return preg_replace('#//+#', '/', $result);
  }

  /**
  * Check input
  *
  * @access public
  * @return boolean $result
  */
  function checkAliasInput() {
    $result = TRUE;
    if (isset($this->params['topic_id']) && $this->params['topic_id'] > 0) {
      $topic = new papaya_topic;
      if (empty($this->params['topic_id']) ||
          !$topic->topicExists((int)$this->params['topic_id'])) {
        $this->addMsg(MSG_ERROR, $this->_gt("Specified page doesn't exist."));
        $result = FALSE;
      }
    }
    return $result;
  }

  /**
  * Save alias
  *
  * @access public
  * @param array $values
  * @return boolean
  */
  function saveAlias($values) {
    $data = array(
      'path' => $this->checkPathSlashes($values['path']),
      'path_pattern' => $this->checkPathSlashes($values['path_pattern']),
      'topic_id' => empty($values['topic_id']) ? 0 : (int)$values['topic_id'],
      'lng_id' => empty($values['lng_id']) ? 0 : (int)$values['lng_id'],
      'viewmode_id' => empty($values['viewmode_id']) ? 0 : (int)$values['viewmode_id'],
      'url_domain' => empty($values['url_domain']) ? '' : (string)$values['url_domain'],
      'url_params' => empty($values['url_params']) ? '' : (string)$values['url_params'],
      'url_redirectmode' => empty($values['url_redirectmode'])
        ? 0 : (int)$values['url_redirectmode'],
      'module_guid' => empty($values['module_guid']) ? '' : (string)$values['module_guid'],
      'target_url' => empty($values['target_url']) ? '' : (string)$values['target_url']
    );
    if (isset($this->aliasPlugin) && is_object($this->aliasPlugin) &&
        $this->aliasPlugin->modified() && $this->aliasPlugin->checkData()) {
      $data['module_params'] = $this->aliasPlugin->getData();
    }
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableUrls, $data, 'id', (int)$this->params['alias_id']
    );
  }

  /**
  * Create a new Alias
  *
  * @access public
  * @return boolean
  */
  function createAlias() {
    $values = array(
      'path' => $this->checkPathSlashes($this->params['path']),
      'path_pattern' => $this->checkPathSlashes($this->params['path_pattern']),
      'topic_id' => $this->params['topic_id'],
      'lng_id' => $this->params['lng_id'],
      'viewmode_id' => $this->params['viewmode_id'],
      'url_domain' => $this->params['url_domain'],
      'url_params' => $this->params['url_params'],
      'url_redirectmode' => $this->params['url_redirectmode']
    );
    return ($this->databaseInsertRecord($this->tableUrls, 'id', $values));
  }

  /**
  * Delete
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function deleteAlias($id) {
    return (FALSE !== $this->databaseDeleteRecord($this->tableUrls, 'id', $id));
  }


  /**
  * Get buttons
  *
  * @access public
  * @return array $result
  */
  function getButtonsXML() {
    $toolbar = new base_btnbuilder;
    $toolbar->images = $this->papaya()->images;
    $toolbar->addButton(
      'Add alias',
      $this->getLink(array('alias_id' => 0)),
      'actions-alias-add',
      ''
    );
    if (isset($this->alias)) {
      $toolbar->addButton(
        'Delete alias',
        $this->getLink(array('cmd' => 'alias_delete', 'alias_id' => (int)$this->alias['id'])),
        'actions-alias-delete',
        ''
      );
    }
    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu('<menu>'.$str.'</menu>');
    }
  }

  /**
  * Initialize alias dialog
  *
  * @access public
  */
  function initializeAliasDialog() {
    if (!(isset($this->dialogAlias) && is_object($this->dialogAlias))) {
      if (isset($this->alias)) {
        $data = $this->alias;
        $hidden = array(
          'cmd' => 'alias_edit',
          'save' => 1,
          'alias_id' => (int)$this->alias['id']
        );
        $btnCaption = 'Save';
      } else {
        $data = array(
          'url_domain' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''
        );
        $hidden = array(
          'save' => 1,
          'cmd' => 'alias_create'
        );
        $btnCaption = 'Add';
      }
      $topicSessionParams = $this->getSessionValue('PAPAYA_SESS_tt');
      $pageId = empty($topicSessionParams['page_id']) ? 0 : (int)$topicSessionParams['page_id'];

      $redirectModes = array(
        0 => $this->_gt('Page Redirect'),
        1 => $this->_gt('Page Frameset'),
        3 => $this->_gt('Url Redirect'),
        2 => $this->_gt('Module')
      );
      $fields = array(
        'url_domain' => array('Domain', 'isHTTPHost', FALSE, 'input',
          200, '', ''),
        'path' => array(
          'Alias', '(^((?:[^!*\'();:@&=+$,?#[\\]]+(?:/\\*?)?)|(?:/?\\*))$)u', TRUE, 'input',
          40, 'End with * to define a dynamic part', '/path/'
        ),
        'path_pattern' => array(
          'Pattern', '(^[{}a-zA-Z0-9.()[\]/ ,_-]+$)u', TRUE, 'input',
          40, 'Applied to the dynamic part of the alias', '/{name}/'
        ),
        'url_redirectmode' => array('Mode', 'isNum', TRUE, 'combo',
          $redirectModes, '', ''),
        'Properties'
      );
      if (!isset($data['url_redirectmode']) || $data['url_redirectmode'] < 2) {
        $fields['topic_id'] = array('Page Id', 'isNum', FALSE, 'pageid', 6, '', $pageId);
        $fields['lng_id'] = array ('Language', 'isNum', TRUE, 'function',
           'getContentLanguageCombo', '',
           $this->papaya()->administrationLanguage->id
        );
        $fields['viewmode_id'] = array(
          'Ouput filter', 'isNum', TRUE, 'function', 'callbackViewModeList'
        );
        $fields['url_params'] = array('Params', 'isSomeText', FALSE, 'input',
          1000, 'Use {name} to insert values from the dynamic part of the path', '');
      } elseif ($data['url_redirectmode'] == 2) {
        $fields['module_guid'] = array ('Module', 'isGuid', TRUE, 'function',
           'callbackModuleList');
        if (isset($this->params['module_guid'])) {
          $moduleGuid = $this->params['module_guid'];
        } elseif (isset($data['module_guid'])) {
          $moduleGuid = $data['module_guid'];
        } else {
          $moduleGuid = '';
        }
        $this->aliasPlugin = $this->papaya()->plugins->get(
          $moduleGuid,
          $this,
          empty($data['module_params']) ? NULL : (string)$data['module_params']
        );
        if (isset($this->aliasPlugin) &&
            isset($this->aliasPlugin->editFields) && is_array($this->aliasPlugin->editFields)) {
          $this->aliasPlugin->paramName = $this->paramName;
          if ($this->aliasPlugin instanceof \Papaya\Plugin\Editable) {
            $data = \PapayaUtilArray::merge($data, $this->aliasPlugin->content());
          } elseif (isset($this->aliasPlugin->data) && is_array($this->aliasPlugin->data)) {
            $data = \PapayaUtilArray::merge($data, $this->aliasPlugin->data);
          }
          $this->aliasPlugin->initializeParams();
          $this->aliasPlugin->initializeDialog();
          $this->aliasPlugin->dialog->useToken = FALSE;
          $fields = array_merge($fields, $this->aliasPlugin->editFields);
        }
      } elseif ($data['url_redirectmode'] == 3) {
        $fields['target_url'] = array ('Url', 'isHttpX', TRUE, 'input', 400, '', '');
      }
      $this->dialogAlias = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogAlias->loadParams();
      $this->dialogAlias->dialogTitle = $this->_gt('Alias');
      $this->dialogAlias->buttonTitle = $btnCaption;
      $this->dialogAlias->dialogDoubleButtons = FALSE;
      $this->dialogAlias->textYes = 'Yes';
      $this->dialogAlias->textNo = 'No';
    }
  }

  /**
  * callback to select an output filter for an alias
  *
  * @param $name
  * @param $field
  * @param $data
  * @access public
  * @return string
  */
  function callbackViewModeList($name, $field, $data) {
    $this->loadViewModeList();
    if (isset($this->viewModes) && is_array($this->viewModes) &&
        count($this->viewModes) > 0) {
      $result = sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      foreach ($this->viewModes as $filter) {
        $selected = ($filter['viewmode_id'] == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%d"%s>%s</option>'.LF,
          (int)$filter['viewmode_id'],
          $selected,
          papaya_strings::escapeHTMLChars($filter['viewmode_ext'])
        );
      }
      $result .= '</select>'.LF;
      return $result;
    }
    return '';
  }

  /**
  * Get alias module list select box
  * @param string $name
  * @param array $field
  * @param string $data
  * @return string
  */
  function callbackModuleList($name, $field, $data) {
    $this->loadModuleList();
    if (isset($this->modules) && is_array($this->modules) &&
        count($this->modules) > 0) {
      $result = sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      foreach ($this->modules as $module) {
        $selected = ($module['module_guid'] == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($module['module_guid']),
          $selected,
          papaya_strings::escapeHTMLChars($module['module_title'])
        );
      }
      $result .= '</select>'.LF;
      return $result;
    }
    return '';
  }

  /**
  * Get alias dialog
  *
  * @see base_dialog::getDialogXML
  * @access public
  * @return string xml
  */
  function getAliasDialog() {
    $this->initializeAliasDialog();
    return $this->dialogAlias->getDialogXML();
  }

  /**
  * Get content lanuage combo
  *
  * @see base_language_select::getContentLanguageCombo
  * @param string $name
  * @param array $element
  * @param array $data
  * @access public
  * @return string xml
  */
  function getContentLanguageCombo($name, $element, $data) {
    return base_language_select::getInstance()->getContentLanguageCombo(
      $this->paramName, $name, $element, $data
    );
  }
}

