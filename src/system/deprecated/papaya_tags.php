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

use Papaya\CMS\Administration;

/**
* Tags Administration
*
* @package Papaya
* @subpackage Administration
*/
class papaya_tags extends base_tags {

  /**
  * Permission modes
  * @var array
  */
  var $permissionModes = array();

  /**
  * Permissions
  * @var array
  */
  var $permissions = array(
    'user_edit_category' => 1,
    'user_edit_tag' => 1,
    'user_use_tags' => 1,
    'surfer_edit_category' => 1,
    'surfer_edit_tag' => 1,
    'surfer_use_tags' => 1,
  );

  /**
  * Paging step limits
  * @var array
  */
  var $pagingSteps = array(10 => 10, 20 => 20, 50 => 50, 100 => 100);

  /**
  * Default paging step limit
  * @var integer
  */
  var $defaultLimit = 20;

  /**
  * Absolute count
  * @var integer
  */
  var $absCount = 0;

  /**
   * Language selector
   * @var base_language_select
   */
  var $lngSelect = NULL;

  /**
   * @var \Papaya\Template
   */
  public $layout = NULL;
  /**
   * @var array
   */
  private $currentTag = NULL;

  /**
   * @var array
   */
  private $userPermissions = NULL;

  /**
   * @var base_dialog
   */
  private $categoryDialog = NULL;

  /**
   * @var array
   */
  private $category = NULL;

  /**
   * @var base_dialog
   */
  private $tagDialog = NULL;

  /**
   * @var array
   */
  private $categoryTree = NULL;

  /**
   * @var array
   */
  private $alternativeCategoryNames = array();

  /**
   * @var array
   */
  private $categoryPermissions = array();

  /**
   * @var base_dialog
   */
  private $dialog = NULL;

  /**
   * @var base_mediadb_edit
   */
  private $mediaDB = NULL;

  /**
  * php 5 constructor, sets param name
  */
  function __construct($paramName = 'tg') {
    parent::__construct();
    $this->paramName = $paramName;
    $this->permissionModes = array(
      'inherited' => $this->_gt('Inherited'),
      'own' => $this->_gt('Own'),
      'additional' => $this->_gt('Additional'),
    );
  }

  /**
  * initializes language selector and session params
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();

    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('cat_id', array('tag_id'));
    $this->initializeSessionParam('tag_id');
    $this->initializeSessionParam('open_categories');
    $this->initializeSessionParam('offset_tags');
    $this->initializeSessionParam('limit');
    if (!isset($this->params['cmd']) || $this->params['cmd'] != 'search') {
      unset($this->sessionParams['search_string']);
      unset($this->params['search_string']);
    } else {
      $this->initializeSessionParam('search_string', array('limit'));
    }
    $this->initializeSessionParam('order');
    $this->initializeSessionParam('sort');
    $this->initializeSessionParam('move_category');
    $this->initializeSessionParam('move_tag');

    if (isset($this->params['cat_id']) && $this->params['cat_id'] > 0
        && !isset($this->sessionParams['open_categories'][$this->params['cat_id']])) {
      $this->sessionParams['open_categories'][$this->params['cat_id']] =
        $this->params['cat_id'];
    }
    if (!isset($this->params['limit']) || $this->params['limit'] == 0) {
      $this->params['limit'] = $this->defaultLimit;
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);

    $this->layout->parameters()->set('COLUMNWIDTH_RIGHT', '100%');
    $this->layout->parameters()->set('COLUMNWIDTH_CENTER', '250px');
  }

  /**
  * executes tag editing commands
  */
  function execute() {
    if (isset($this->params['cat_id'])) {
      $this->loadCategory();
      if (!isset($this->params['cmd']) && $this->params['cat_id'] > 0) {
        $this->params['cmd'] = 'add_tag';
      }
    } else {
      $this->loadPermissions();
    }

    if (isset($this->params['tag_id']) && $this->params['tag_id'] > 0) {
      $this->currentTag = $this->getTag(
        $this->params['tag_id'],
        $this->papaya()->administrationLanguage->id
      );
    }

    if (isset($this->params['cmd'])) {
      switch($this->params['cmd']) {
      case 'add_category' :
        if (isset($this->userPermissions['user_edit_category'])) {
          $this->initializeCategoryDialog(FALSE);
          if ($this->categoryDialog->checkDialogInput()) {
            if ($newId = $this->addNewCategory($this->categoryDialog->data)) {
              $this->params['cat_id'] = $newId;
              unset($this->categoryDialog);
              unset($this->categories);
              $this->loadCategory();
            }
          }
          $this->getEditCategoryDialog(FALSE);
        }
        break;
      case 'edit_category' :
      case 'add_perm' :
      case 'del_perm' :
        if (isset($this->category) &&
            is_array($this->category) &&
            isset($this->category['category_id']) &&
            isset($this->userPermissions['user_edit_category'])) {
          if (in_array($this->params['cmd'], array('add_perm', 'del_perm')) &&
              isset($this->params['perm_type']) &&
              isset($this->permissions[$this->params['perm_type']]) &&
              isset($this->params['perm_id'])) {
            switch ($this->params['cmd']) {
            case 'add_perm' :
              $this->addPermission(
                $this->category['category_id'],
                $this->params['perm_type'],
                $this->params['perm_id']
              );
              break;
            case 'del_perm' :
              $this->delPermission(
                $this->category['category_id'],
                $this->params['perm_type'],
                $this->params['perm_id']
              );
              break;
            }
            $this->loadCategory();
          } else {
            $this->initializeCategoryDialog();
            if ($this->categoryDialog->checkDialogInput()) {
              if ($this->setCategory($this->categoryDialog->data, $this->category['category_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Category modified.'));
                $this->loadCategory();
              }
            }
          }
          $this->getEditCategoryDialog();
        }
        break;
      case 'del_category' :
        if (isset($this->userPermissions['user_edit_category'])) {
          if (isset($this->params['confirm']) && $this->params['confirm'] &&
              isset($this->params['cat_id'])) {
            if ($this->delCategory($this->params['cat_id'])) {
              $this->addMsg(MSG_INFO, $this->_gt('Category deleted.'));
              unset($this->params['cat_id']);
            }
            unset($this->categories);
          } else {
            $this->getDelCategoryDialog();
          }
        }
        break;
      case 'cut_category':
        if (count($this->category) > 0 &&
            isset($this->userPermissions['user_edit_category'])) {
          if (isset($this->params['cat_id'])) {
            $this->sessionParams['move_category'] = $this->params['cat_id'];
            $this->setSessionValue($this->sessionParamName, $this->sessionParams);
            $this->addMsg(
              MSG_INFO,
              sprintf(
                $this->_gt('Selected category "%s" for pasting.'),
                $this->getCategoryTitle($this->category, $this->params['cat_id'])
              )
            );
          }
        }
        break;
      case 'paste_category':
        if (isset($this->userPermissions['user_edit_category'])) {
          if (isset($this->params['cat_id'])) {
            if (isset($this->params['confirm']) &&
                $this->params['confirm'] &&
                $this->moveCategory(
                  $this->sessionParams['move_category'], $this->params['cat_id']
                )
               ) {
              $this->loadCategories();
              unset($this->sessionParams['move_category']);
              $this->setSessionValue($this->sessionParamName, $this->sessionParams);
            } else {
              $this->getMoveCategoryDialog();
            }
          }
        }
        break;
      case 'add_tag':
        if (isset($this->category) &&
            is_array($this->category) &&
            !empty($this->category['category_id']) &&
            isset($this->userPermissions['user_edit_tag'])) {
          $this->initializeTagDialog();
          if ($this->tagDialog->checkDialogInput()) {

            // Prepare tag data and titles for addNewTag(...)
            $tagData = array();
            if (is_array($this->tagDialog->data) && count($this->tagDialog->data) > 0) {
              $tagData['tag_title'] = $this->tagDialog->data['tag_title'];
              $tagData['tag_image'] = $this->tagDialog->data['tag_image'];
              $tagData['tag_description'] = $this->tagDialog->data['tag_description'];
              $tagData['cat_id'] = $this->category['category_id'];
              $tagData['tag_uri'] = $this->tagDialog->data['tag_uri'];
            }
            if ($newId = $this->addNewTag($tagData)) {
              $this->addMsg(MSG_INFO, $this->_gt('Tag added.'));
              $this->params['tag_id'] = $newId;
              $this->currentTag = $this->getTag(
                $this->params['tag_id'],
                $this->papaya()->administrationLanguage->id
              );
              unset($this->tagDialog);
            }
          }
          $this->getEditTagDialog();
          $this->layout->addRight($this->getTagStatisticXML());
        }
        break;
      case 'edit_tag':
        if (isset($this->currentTag) &&
            is_array($this->currentTag) &&
            !empty($this->currentTag['tag_id']) &&
            isset($this->userPermissions['user_edit_tag'])) {
          $this->initializeTagDialog();
          if ($this->tagDialog->checkDialogInput()) {
            if ($this->setTag($this->tagDialog->data, $this->currentTag['tag_id'])) {
              $this->addMsg(MSG_INFO, $this->_gt('Tag modified.'));
            }
          }
          $this->getEditTagDialog();
          $this->layout->addRight($this->getTagStatisticXML());
        }
        break;
      case 'del_tag':
        if (count($this->currentTag) > 0
            && isset($this->userPermissions['user_edit_tag'])) {
          if (isset($this->params['confirm']) && $this->params['confirm']
              && isset($this->params['tag_id'])) {
            $this->delTag($this->params['tag_id']);
          } else {
            $this->getDelTagDialog();
          }
        }
        break;
      case 'open_category':
        if (isset($this->params['cat_open_id']) && $this->params['cat_open_id'] > 0) {
          $this->sessionParams['open_categories'][(int)$this->params['cat_open_id']] =
            (int)$this->params['cat_open_id'];
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        break;
      case 'close_category':
        if (isset($this->params['cat_close_id']) && $this->params['cat_close_id'] > 0) {
          unset($this->sessionParams['open_categories'][$this->params['cat_close_id']]);
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        break;
      case 'search':
        if (isset($this->params['search_string'])) {
          $this->displaySearchResult();
        }
        break;
      case 'import_tags':
        $this->mediaDB = new base_mediadb_edit;
        $this->processCSVUpload();
        break;
      }
    }
  }

  /**
  * generates xml for administration of tags
  */
  function getXML() {
    if (!isset($this->categories)) {
      $this->loadCategories();
    }
    $this->checkSelectedCategory();

    $this->loadMenuXML();
    $this->layout->addLeft($this->getXMLCategoryTree());
    $this->layout->addLeft($this->getXMLSearchForm());
    if (isset($this->params) && isset($this->params['cmd']) &&
        $this->params['cmd'] != 'import_tags') {
      // don't show list if importing
      $this->layout->add($this->getTagsListXML());
    }

    if (isset($this->dialog) && get_class($this->dialog) == 'base_dialog') {
      $this->layout->add($this->dialog->getDialogXML());
    }
  }

  /**
  * makes sure, that the selected category is visible, i.e. it's parent nodes are open
  */
  function checkSelectedCategory() {
    if (isset($this->params['cat_id']) && $this->params['cat_id'] > 0) {
      // make sure a deep link to a category opens all parent categories
      if (!isset($this->categories[$this->params['cat_id']])) {
        $categoryData = $this->getCategory($this->params['cat_id']);
        $category = current($categoryData);
      } else {
        $category = $this->categories[$this->params['cat_id']];
      }
      $parents = explode(';', $category['parent_path']);
      foreach ($parents as $catId) {
        if ($catId > 0) {
          $this->sessionParams['open_categories'][$catId] = $catId;
        }
      }
      $this->sessionParams['open_categories'][$this->params['cat_id']] =
        $this->params['cat_id'];
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
      $this->loadCategories();
    }
  }

  /**
  * generate menu xml
  */
  function loadMenuXML() {
    $menubar = new base_btnbuilder;
    $menubar->images = $this->papaya()->images;
    $administrationUser = $this->papaya()->administrationUser;
    if ($administrationUser->hasPerm(Administration\Permissions::TAG_CATEGORY_MANAGE) &&
        (isset($this->userPermissions['user_edit_category']) || $this->params['cat_id'] == 0)) {
      if (isset($this->params['cat_id']) && $this->params['cat_id'] > 0) {
        if (isset($this->params['cat_id']) &&
            isset($this->categories[$this->params['cat_id']]) &&
            !empty($this->categories[$this->params['cat_id']]['parent_id'])) {
          $parentId = (int)$this->categories[$this->params['cat_id']]['parent_id'];
        } else {
          $parentId = 0;
        }
        $menubar->addButton(
          'Add category',
          $this->getLink(
            array(
              'cmd' => 'add_category',
              'cat_id' => $this->params['cat_id'],
              'parent_id' => $parentId,
            )
          ),
          'actions-folder-add',
          '',
          FALSE
        );
      }
      $menubar->addButton(
        'Add subcategory',
        $this->getLink(
          array(
            'cmd' => 'add_category',
            'cat_id' => empty($this->params['cat_id']) ? 0 : (int)$this->params['cat_id'],
            'parent_id' => empty($this->params['cat_id']) ? 0 : (int)$this->params['cat_id'],
          )
        ),
        'actions-folder-child-add',
        '',
        FALSE
      );
      if (isset($this->params['cat_id'])) {
        if ($this->params['cat_id'] > 0) {
          $menubar->addButton(
            'Delete category',
            $this->getLink(
              array(
                'cmd' => 'del_category',
                'cat_id' => $this->params['cat_id']
              )
            ),
            'actions-folder-delete',
            '',
            FALSE
          );
          $menubar->addButton(
            'Cut category',
            $this->getLink(
              array(
                'cmd' => 'cut_category',
                'cat_id' => $this->params['cat_id']
              )
            ),
            'actions-edit-cut',
            '',
            FALSE
          );
        }
        if (isset($this->sessionParams['move_category']) &&
            !empty($this->params['cat_id']) &&
            $this->params['cat_id'] != $this->sessionParams['move_category'] &&
            !substr_count(
              $this->categories[$this->params['cat_id']]['parent_path'],
              ';'.$this->sessionParams['move_category'].';'
            ) &&
            $this->categories[$this->sessionParams['move_category']]['parent_id'] !=
              $this->params['cat_id']
        ) {
          $menubar->addButton(
            'Paste category',
            $this->getLink(
              array(
                'cmd' => 'paste_category',
                'cat_id' => $this->params['cat_id']
              )
            ),
            'actions-edit-paste',
            '',
            FALSE
          );
        }
        $menubar->addButton(
          'Import tags',
          $this->getLink(
            array(
              'cmd' => 'import_tags',
              'cat_id' => empty($this->params['cat_id']) ? 0 : (int)$this->params['cat_id'],
            )
          ),
          'actions-upload',
          '',
          FALSE
        );
      }
    }
    if (isset($this->categories) && is_array($this->categories)
        && count($this->categories) > 0
        && isset($this->params['cat_id']) && $this->params['cat_id'] > 0
        && isset($this->userPermissions['user_edit_tag'])
        && $administrationUser->hasPerm(Administration\Permissions::TAG_EDIT)) {
      $menubar->addSeperator();
      $menubar->addButton(
        'Add tag',
        $this->getLink(
          array(
            'cmd' => 'add_tag',
            'cat_id' => $this->params['cat_id'],
            'tag_id' => 0
          )
        ),
        'actions-tag-add',
        '',
        FALSE
      );
      if (isset($this->params['tag_id'])) {
        $menubar->addButton(
          'Delete tag',
          $this->getLink(
            array(
              'cmd' => 'del_tag',
              'tag_id' => $this->params['tag_id']
            )
          ),
          'actions-tag-delete',
          '',
          FALSE
        );
      }
    }

    if ($str = $menubar->getXML()) {
      $this->layout->addMenu(sprintf('<menu>%s</menu>'.LF, $str));
    }
  }

  /**
  * generate listview for tag statistic
  *
  * @return string $result tag statistic XML
  */
  function getTagStatisticXML() {
    $result = '';
    $data = $this->getTagStatistic(empty($this->params['tag_id']) ? '' : $this->params['tag_id']);
    if (is_array($data) && count($data) > 0) {
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Tag statistic'))
      );
      $result .= sprintf(
        '<cols><col>%s</col><col>%s</col></cols>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Type')),
        papaya_strings::escapeHTMLChars($this->_gt('Count'))
      );
      $result .= '<items>'.LF;
      foreach ($data as $type => $count) {
        $result .= sprintf(
          '<listitem title="%s"><subitem>%d</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($type),
          (int)$count
        );
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * generate tag search form
  *
  * @return string $result dialog xml
  */
  function getXMLSearchForm() {
    $data = array(
      'search_string' => empty ($this->params['search_string'])
        ? '' : (string)$this->params['search_string']
    );
    $hidden = array('cmd' => 'search');
    $fields = array('search_string' => array('', 'isNoHTML', TRUE, 'input', 100));
    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $dialog->dialogTitle = $this->_gt('Search for tags');
    $dialog->loadParams();
    $dialog->buttonTitle = 'Search';
    $dialog->inputFieldSize = 'small';
    return $dialog->getDialogXML();
  }

  /**
  * generate categories tree xml
  *
  * @return string $result listview xml
  */
  function getXMLCategoryTree() {
    $result = sprintf(
      '<listview title="%s" >'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Categories'))
    );
    $result .= '<items>'.LF;
    if (isset($this->params) && isset($this->params['cat_id'])) {
      $selected = ($this->params['cat_id'] == 0) ? ' selected="selected"' : '';
    } else {
      $selected = '';
    }
    $result .= sprintf(
      '<listitem href="%s" title="%s" image="%s" %s><subitem /></listitem>'.LF,
      papaya_strings::escapeHTMLChars($this->getLink(array('cat_id' => 0))),
      papaya_strings::escapeHTMLChars($this->_gt('Base')),
      papaya_strings::escapeHTMLChars($this->papaya()->images['items-folder']),
      $selected
    );
    if (isset($this->categories) && is_array($this->categories)) {
      $result .= $this->getXMLCategorySubTree(0, 0);
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * generate subtree of categories
  */
  function getXMLCategorySubTree($parent, $indent) {
    $result = '';
    if (isset($this->categoryTree[$parent]) &&
        is_array($this->categoryTree[$parent]) &&
        (isset($this->sessionParams['open_categories'][$parent]) || ($parent == 0))) {
      foreach ($this->categoryTree[$parent] as $id) {
        $result .= $this->getXMLCategoryEntry($id, $indent);
      }
    }
    return $result;
  }

  /**
  * generate category tree listview line
  */
  function getXMLCategoryEntry($id, $indent) {
    $result = '';
    if (isset($this->categories[$id]) && is_array($this->categories[$id])) {
      if (isset($this->sessionParams['open_categories'][$id]) &&
          isset($this->categories[$id]['CATEG_COUNT']) &&
          $this->categories[$id]['CATEG_COUNT'] > 0) {
        $opened = TRUE;
      } else {
        $opened = FALSE;
      }
      if (empty($this->categories[$id]['CATEG_COUNT']) ||
          $this->categories[$id]['CATEG_COUNT'] < 1) {
        $node = ' node="empty"';
        if (isset($this->sessionParams['move_category']) &&
            $id == (int)$this->sessionParams['move_category']) {
          $imageIndex = 'items-folder'; // 178;
        } else {
          $imageIndex = 'items-folder'; // 56;
        }
      } elseif ($opened) {
        $nodeHref = $this->getLink(
          array('cmd' => 'close_category', 'cat_close_id' => (int)$id)
        );
        $node = sprintf(
          ' node="open" nhref="%s"',
          papaya_strings::escapeHTMLChars($nodeHref)
        );
        if (isset($this->sessionParams['move_category']) &&
            $id == (int)$this->sessionParams['move_category']) {
          $imageIndex = 'items-folder'; // 179;
        } else {
          $imageIndex = 'items-folder'; // 57;
        }
      } else {
        $nodeHref = $this->getLink(
          array('cmd' => 'open_category', 'cat_open_id' => (int)$id)
        );
        $node = sprintf(
          ' node="close" nhref="%s"',
          papaya_strings::escapeHTMLChars($nodeHref)
        );
        if (isset($this->sessionParams['move_category']) &&
            $id == (int)$this->sessionParams['move_category']) {
          $imageIndex = 'items-folder'; // 178;
        } else {
          $imageIndex = 'items-folder'; // 56;
        }
      }
      if (!isset($this->categories[$id]) ||
          !isset($this->categories[$id]['category_title']) ||
          $this->categories[$id]['category_title'] == "") {
        if (isset($this->alternativeCategoryNames) &&
            is_array($this->alternativeCategoryNames) &&
            isset($this->alternativeCategoryNames[$id])) {
          $title = '['.$this->alternativeCategoryNames[$id].']';
        } else {
          $title = $this->_gt('No Title');
        }
      } else {
        $title = $this->categories[$id]['category_title'];
      }
      if (isset($this->params) && $this->params['cat_id'] > 0) {
        $selected = ((int)$this->params['cat_id'] == $id) ? ' selected="selected"' : '';
      } else {
        $selected = '';
      }
      if (isset($this->params['cat_id']) && $this->params['cat_id'] == $id) {
        if (isset($this->sessionParams['move_category']) &&
            $id == (int)$this->sessionParams['move_category']) {
          $imageIndex = 'actions-page-move';
        } else {
          $imageIndex = 'status-folder-open';
        }
      }
      $images = $this->papaya()->images;
      $result .= sprintf(
        '<listitem href="%s" title="%s" indent="%d" image="%s" %s %s>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'edit_category',
              'cat_id' => (int)$id,
              'offset_tags' => 0
            )
          )
        ),
        papaya_strings::escapeHTMLChars($title),
        (int)$indent,
        papaya_strings::escapeHTMLChars($images[$imageIndex]),
        $node,
        $selected
      );
      if ($this->categories[$id]['permission_mode'] != 'inherited') {
        $result .= sprintf(
          '<subitem align="right"><glyph src="%s" /></subitem>'.LF,
          papaya_strings::escapeHTMLChars($images['items-permission'])
        );
      } else {
        $result .= '<subitem />'.LF;
      }
      $result .= '</listitem>'.LF;
      $result .= $this->getXMLCategorySubTree($id, $indent + 1);
    }
    return $result;
  }

  /**
  * load categories and category tree
  */
  function loadCategories() {
    unset($this->categories);
    unset($this->categoryTree);

    if (isset($this->sessionParams['open_categories'])) {
      $parentIds = $this->sessionParams['open_categories'];
    }
    $parentIds[] = 0;

    $lngCondition = $this->databaseGetSQLCondition(
      'ct.lng_id', $this->papaya()->administrationLanguage->id
    );
    $parentCondition = $this->databaseGetSQLCondition('c.parent_id', $parentIds);
    $availableCategories = $this->getAvailableCategories(
      $this->papaya()->administrationUser,
      array('user_edit_category', 'user_edit_tag')
    );
    if (is_array($availableCategories) && count($availableCategories) > 0) {
      $categoryCondition =
        ' AND '.$this->databaseGetSQLCondition('c.category_id', $availableCategories);
    } else {
      $categoryCondition = '';
    }
    $sql = "SELECT DISTINCT c.category_id, c.parent_id, c.parent_path,
                   c.category_name, c.permission_mode,
                   ct.category_title, ct.category_description, ct.lng_id
              FROM %s c
              LEFT OUTER JOIN %s ct
                   ON (c.category_id = ct.category_id AND $lngCondition)
             WHERE $parentCondition
                   $categoryCondition
             ORDER BY c.parent_path ASC, ct.category_title ASC
           ";
    $params = array($this->tableTagCategory, $this->tableTagCategoryTrans);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $categoriesWithoutName = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->categories[(int)$row['category_id']] = $row;
        $this->categoryTree[(int)$row['parent_id']][$row['category_id']] = $row['category_id'];
        if ($row['category_title'] == '') {
          $categoriesWithoutName[] = $row['category_id'];
        }
      }
      $this->loadCategoryCounts($this->categories);
      $this->loadAlternativeCategoryNames($categoriesWithoutName);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load category titles in content language
  * @param array $categoryIds
  * @return void
  */
  function loadAlternativeCategoryNames($categoryIds) {
    $defaultLanguage = $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::CONTENT_LANGUAGE);
    if ($defaultLanguage != $this->papaya()->administrationLanguage->id &&
        isset($categoryIds) && is_array($categoryIds) &&
        count($categoryIds) > 0) {
      $categoryCondition = $this->databaseGetSQLCondition('category_id', $categoryIds);
      $sql = "SELECT category_id, category_title
                FROM %s
              WHERE lng_id = %d
                AND $categoryCondition";
      $params = array($this->tableTagCategoryTrans, $defaultLanguage);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->alternativeCategoryNames[$row['category_id']] = $row['category_title'];
        }
      }
    }
  }

  /**
  * load a single category and its permissions
  */
  function loadCategory() {
    $this->category = $this->getCategory(
      $this->params['cat_id'], $this->papaya()->administrationLanguage->id
    );
    $this->categoryPermissions = $this->calculatePermissions($this->category);
    $this->loadPermissions();
  }

  /**
  * Gets category title, alternative title or no title
  *
  * @param array $category current or selected category
  * @param integer $categoryId id of current or selected category
  * @access public
  * @return string $categoryTitle
  */
  function getCategoryTitle($category, $categoryId) {
    if (!isset($category['category_title']) || $category['category_title'] == '') {
      if (!isset($this->alternativeCategoryNames[$categoryId])) {
        $this->loadAlternativeCategoryNames(array($categoryId));
      }
      if (isset($this->alternativeCategoryNames[$categoryId])) {
        $categoryTitle = '['.$this->alternativeCategoryNames[$categoryId].']';
      } else {
        $categoryTitle = $this->_gt('No title');
      }
    } else {
      $categoryTitle = $category['category_title'];
    }
    return $categoryTitle;
  }

  /**
  * load current permission state of user/category, mainly for menu buttons
  */
  function loadPermissions() {
    unset($this->userPermissions);
    $administrationUser = $this->papaya()->administrationUser;
    foreach ($administrationUser->user['groups'] as $groupId) {
      if ($groupId == '-1'
          || isset($this->categoryPermissions['user_edit_category'][$groupId])) {
        $this->userPermissions['user_edit_category'] = 1;
      }
      if ($groupId == '-1'
          || isset($this->categoryPermissions['user_edit_tag'][$groupId])) {
        $this->userPermissions['user_edit_tag'] = 1;
      }
      if ($groupId == '-1'
          || isset($this->categoryPermissions['user_use_tags'][$groupId])) {
        $this->userPermissions['user_use_tags'] = 1;
      }
    }
  }

  /**
  * generate list of tags
  *
  * @return string $result tag listview XML
  */
  function getTagsListXML() {
    $result = '';
    if (isset($this->params['cat_id']) && $this->params['cat_id'] > 0) {
      $tags = $this->getTagsByCategory(
        $this->params['cat_id'],
        $this->papaya()->administrationLanguage->id,
        $this->params['limit'],
        $this->params['offset_tags']
      );
      if (is_array($tags) && count($tags) > 0) {
        if ($this->absCount > 0 && count($tags) == 0
            && $this->params['offset_tags'] > $this->absCount) {
          $this->params['offset_tags'] =
            (floor(($this->absCount - 1) / $this->params['limit'])) * $this->params['limit'];
          $tags = $this->getTagsByCategory(
            $this->params['cat_id'],
            $this->papaya()->administrationLanguage->id,
            $this->params['limit'],
            $this->params['offset_tags']
          );
          $tags = $tags[$this->params['cat_id']];
        } else {
          $tags = $tags[$this->params['cat_id']];
        }
      }
    }
    if (isset($tags) && is_array($tags) && count($tags) > 0) {
      $images = $this->papaya()->images;
      $result .= sprintf(
        '<listview width="300" title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Tags'))
      );
      $result .= $this->getTagsPagingBar($this->absCount);
      $result .= '<items>'.LF;
      foreach ($tags as $tag) {
        $linkParams = array('cmd' => 'edit_tag', 'tag_id' => $tag['tag_id']);
        if (isset($this->params['tag_id'])
            && $tag['tag_id'] == $this->params['tag_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $defaultLanguage = $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::CONTENT_LANGUAGE);
        if ($tag['tag_title'] != '') {
          $title = $tag['tag_title'];
        } elseif ($defaultLanguage != $this->papaya()->administrationLanguage->id) {
          if (!(
                isset($this->alternativeTags) &&
                is_array($this->alternativeTags) &&
                count($this->alternativeTags) > 0
              )) {
            $this->alternativeTags = $this->getTags(
              array_keys($tags), $defaultLanguage
            );
          }
          if (isset($this->alternativeTags[$tag['tag_id']]) &&
              isset($this->alternativeTags[$tag['tag_id']]['tag_title']) &&
              $this->alternativeTags[$tag['tag_id']]['tag_title'] != '') {
            $title = '['.$this->alternativeTags[$tag['tag_id']]['tag_title'].']';
          } else {
            $title = $this->_gt('No title');
          }
        } else {
          $title = $this->_gt('No title');
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s" %s>'.LF,
          papaya_strings::escapeHTMLChars($title),
          papaya_strings::escapeHTMLChars($this->getLink($linkParams)),
          $selected
        );
        $result .= '<subitem align="right">'.LF;
        $result .= sprintf(
          '<a href="%s"><glyph src="%s" /></a>'.LF,
          papaya_strings::escapeHTMLChars($this->getLink($linkParams)),
          papaya_strings::escapeHTMLChars($images['actions-edit'])
        );
        $result .= '</subitem>'.LF;
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * generate list of search result tags
  */
  function displaySearchResult() {
    $result = '';
    // get matching tags
    $tags = $this->searchTags(
      $this->params['search_string'],
      $this->params['limit'],
      $this->params['offset_tags'],
      $this->params['order'],
      $this->params['sort']
    );
    if (count($tags) == 0 && $this->absCount > 0
        && $this->params['offset_tags'] > $this->absCount) {
      $this->params['offset_tags'] =
        (floor(($this->absCount - 1) / $this->params['limit'])) * $this->params['limit'];
      $tags = $this->searchTags(
        $this->params['search_string'],
        $this->params['limit'],
        $this->params['offset_tags'],
        $this->params['order'],
        $this->params['sort']
      );
    }
    // search results don't belong to any category, prevents wrong tags from
    // being displayed
    unset($this->params['cat_id']);

    $catIds = array();
    foreach ($tags as $tag) {
      $parents = explode(';', $tag['parent_path']);
      foreach ($parents as $catId) {
        $catIds[$catId] = $catId;
      }
      $catIds[$tag['category_id']] = $tag['category_id'];
    }
    $categories = $this->getCategories(
      $catIds, $this->papaya()->administrationLanguage->id
    );

    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars(
        sprintf($this->_gt('Search result for \'%s\''), $this->params['search_string'])
      )
    );
    if (isset($tags) && is_array($tags) && count($tags) > 0) {
      if (!isset($this->params['order'])) {
        $this->params['order'] = '';
      }
      // calculate sort links / images
      switch ($this->params['order']) {
      default:
      case 'tag':
        $tagSortLink = $this->getLink(
          array(
            'order' => 'tag',
            'sort' => ($this->params['sort'] == 'asc') ? 'desc' : 'asc',
            'cmd' => $this->params['cmd'],
          )
        );
        $tagSort = ($this->params['sort'] == 'asc') ? 'desc' : 'asc';
        $pathSortLink = $this->getLink(
          array(
            'order' => 'path',
            'sort' => 'asc',
            'cmd' => $this->params['cmd'],
          )
        );
        $pathSort = 'none';
        break;
      case 'path':
        $tagSortLink = $this->getLink(
          array(
            'order' => 'tag',
            'sort' => 'asc',
            'cmd' => $this->params['cmd'],
          )
        );
        $tagSort = 'none';
        $pathSortLink = $this->getLink(
          array(
            'order' => 'path',
            'sort' => ($this->params['sort'] == 'asc') ? 'desc' : 'asc',
            'cmd' => $this->params['cmd'],
          )
        );
        $pathSort = ($this->params['sort'] == 'asc') ? 'desc' : 'asc';
        break;
      }

      $result .= $this->getTagsPagingBar($this->absCount);
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col href="%s" sort="%s">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($tagSortLink),
        papaya_strings::escapeHTMLChars($tagSort),
        papaya_strings::escapeHTMLChars($this->_gt('Tag'))
      );
      $result .= sprintf(
        '<col href="%s" sort="%s">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($pathSortLink),
        papaya_strings::escapeHTMLChars($pathSort),
        papaya_strings::escapeHTMLChars($this->_gt('Category path'))
      );
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      foreach ($tags as $tag) {
        $linkParams = array(
          'cmd' => 'edit_tag',
          'tag_id' => $tag['tag_id'],
        );
        if (isset($this->params['tag_id'])
            && $tag['tag_id'] == $this->params['tag_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        // calculate category path
        $parents = explode(';', $tag['parent_path']);
        $categoryPath = '/ ';
        foreach ($parents as $catId) {
          if ($catId > 0) {
            $categoryPath .= sprintf(
              '<a href="%s">%s</a> / ',
              papaya_strings::escapeHTMLChars($this->getLink(array('cat_id' => $catId))),
              papaya_strings::escapeHTMLChars($categories[$catId]['category_title'])
            );
          }
        }
        if (isset($categories[$tag['category_id']]) &&
            !empty($categories[$tag['category_id']]['category_title'])) {
          $categoryTitle = $categories[$tag['category_id']]['category_title'];
        } else {
          $categoryTitle = $this->_gt('No Title');
        }
        $categoryPath .= sprintf(
          '<a href="%s">%s</a>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cat_id' => $tag['category_id']))
          ),
          papaya_strings::escapeHTMLChars($categoryTitle)
        );

        $result .= sprintf(
          '<listitem image="%s/%s" href="%s" title="%s (%d)" %s>'.LF,
          './pics/language',
          papaya_strings::escapeHTMLChars(
            $this->papaya()->languages->getLanguage($tag['lng_id'])->image
          ),
          papaya_strings::escapeHTMLChars($this->getLink($linkParams)),
          papaya_strings::escapeHTMLChars($tag['tag_title']),
          (int)$tag['tag_id'],
          $selected
        );
        $result .= sprintf(
          '<subitem align="left">%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars($categoryPath)
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
    } else {
      $result .= '<items>'.LF;
      $result .= sprintf(
        '<listitem title="%s" />',
        papaya_strings::escapeHTMLChars($this->_gt('No result for this search.'))
      );
      $result .= '</items>'.LF;
    }
    $result .= '</listview>'.LF;
    $this->layout->addRight($result);
  }

  /**
   * generate paging bar for tags
   *
   * @param integer $absCount total number of tags
   * @param string $cmd command for tag link
   * @return string
   */
  function getTagsPagingBar($absCount, $cmd = NULL) {
    if ($cmd == NULL && isset($this->params['cmd'])) {
      $cmd = $this->params['cmd'];
    }
    $result = '';
    $offset = empty($this->params['offset_tags']) ? 0 : (int)$this->params['offset_tags'];
    $steps = (isset($this->params['limit']) && $this->params['limit'] > 10)
      ? (int)$this->params['limit'] : 10;
    $groupCount = 9;
    $offsetName = 'offset_tags';

    if ($absCount > 10) {
      $result = '<buttons>'.LF;
      $result .= papaya_paging_buttons::getPagingButtons(
        $this,
        array('cmd' => $cmd),
        (int)$offset,
        $steps,
        $absCount,
        $groupCount,
        $offsetName,
        'left'
      );
      $result .= papaya_paging_buttons::getButtons(
        $this,
        array('cmd' => $cmd),
        $this->pagingSteps,
        $steps,
        'limit',
        'right'
      );
      $result .= '</buttons>'.LF;
    }
    return $result;
  }

  /**
  * initalize dialog object for categroy edit/delete if it does not exist
  *
  * @access public
  * @param boolean $edit Initialize dialog to edit an existing category (if available)
  * @return void
  */
  function initializeCategoryDialog($edit = TRUE) {
    if (!(isset($this->categoryDialog) && is_object($this->categoryDialog))) {
      $fields['category_title'] = array(
        'Title', 'isSomeText', TRUE, 'input', 100
      );
      $fields['category_description'] = array(
        'Description', 'isSomeText', TRUE, 'textarea', 5
      );
      $fields[] = 'Language independent';
      $fields['category_name'] = array(
        'Name', 'isSomeText', FALSE, 'input', 50
      );
      if ($edit &&
          isset($this->category) &&
          is_array($this->category) &&
          isset($this->category['category_id']) &&
          $categoryDetails = $this->getCategoryDetails($this->category['category_id'])) {
        switch ($categoryDetails['creator_type']) {
        case 'surfer':
          $authorType = $this->_gt('Surfer');
          $author = $categoryDetails['surfer_givenname'].' '.
            $categoryDetails['surfer_surname'];
          break;
        case 'admin':
          $authorType = $this->_gt('Backend user');
          $author = $categoryDetails['surfer_givenname'].' '.
            $categoryDetails['surfer_surname'];
          break;
        default:
          $authorType = $this->_gt('Unknown');
          $author = '';
        }
        if ((int)$categoryDetails['creation_time'] != 0) {
          $date = \Papaya\Utility\Date::timestampToString(
            (int)$categoryDetails['creation_time'], FALSE, FALSE, FALSE
          );
        } else {
          $date = $this->_gt('Unknown');
        }
        $hidden = array(
          'cmd' => 'edit_category',
          'cat_id' => $this->category['category_id']
        );
        $data = array(
          'category_title' => $this->category['category_title'],
          'category_description' => $this->category['category_description'],
          'category_name' => $this->category['category_name'],
          'permission_mode' => $this->category['permission_mode']
        );
        $fields[] = 'Created By';
        $fields['created_by'] = array(
          $authorType, 'isSomeText', FALSE, 'info', '', '', $author, 'left'
        );
        $fields['created_on'] = array(
          'Date', 'isSomeText', FALSE, 'info', '', '', $date, 'left'
        );
        $dialogTitle = 'Edit category';
        $buttonTitle = 'Save';
      } else {
        $hidden = array(
          'cmd' => 'add_category',
          'parent_id' => empty($this->params['parent_id']) ? 0 : (int)$this->params['parent_id'],
          'cat_id' => empty($this->params['cat_id']) ? 0 : (int)$this->params['cat_id']
        );
        $data = array();
        $dialogTitle = 'Add category';
        $buttonTitle = 'Add';
      }
      $fields[] = 'Permissions';
      $fields['permission_mode'] = array(
        'Permission mode', 'isNoHTML', TRUE, 'combo', $this->permissionModes
      );
      $this->categoryDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->categoryDialog->dialogTitle = $this->_gt($dialogTitle);
      $this->categoryDialog->buttonTitle = $buttonTitle;
      $this->categoryDialog->inputFieldSize = 'large';
      $this->categoryDialog->loadParams();
    }
  }

  /**
  * generate dialog for category editing
  * @param boolean $edit Initialize dialog to edit an existing category (if available)
  */
  function getEditCategoryDialog($edit = TRUE) {
    $this->initializeCategoryDialog($edit);
    $this->layout->addRight($this->categoryDialog->getDialogXML());
    if (isset($this->category) &&
        is_array($this->category) &&
        isset($this->category['category_id'])) {
      $this->layout->addRight($this->getPermissionListXML());
    }
  }

  /**
  * generate permission listview xml for the current category
  */
  function getPermissionListXML() {
    $result = '';
    if (isset($this->category) && is_array($this->category)) {
      $surferPerms = $this->getSurferPermissions();
      $groups = $this->getGroups();
      $categoryPerms = $this->categoryPermissions;

      /***************************
      * Backend user permissions *
      ***************************/
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('User permissions'))
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Group'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Create/Edit category'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Create tag'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Use tags'))
      );
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      $images = $this->papaya()->images;
      foreach ($groups as $groupId => $groupTitle) {
        if (isset($categoryPerms['user_edit_category'][$groupId])) {
          if ($categoryPerms['user_edit_category'][$groupId] == 'own') {
            $createCategoryIcon = $images['status-node-checked'];
            $createCategoryLink = $this->getLink(
              array(
                'cmd' => 'del_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'user_edit_category',
                'perm_id' => $groupId,
              )
            );
          } else {
            $createCategoryIcon = $images['status-node-checked-disabled'];
            $createCategoryLink = '';
          }
        } else {
          if ($this->category['permission_mode'] != 'inherited') {
            $createCategoryIcon = $images['status-node-empty'];
            $createCategoryLink = $this->getLink(
              array(
                'cmd' => 'add_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'user_edit_category',
                'perm_id' => $groupId,
              )
            );
          } else {
            $createCategoryIcon = $images['status-node-empty-disabled'];
            $createCategoryLink = '';
          }
        }
        if (isset($categoryPerms['user_edit_tag'][$groupId])) {
          if ($categoryPerms['user_edit_tag'][$groupId] == 'own') {
            $createTagIcon = $images['status-node-checked'];
            $createTagLink = $this->getLink(
              array(
                'cmd' => 'del_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'user_edit_tag',
                'perm_id' => $groupId,
              )
            );
          } else {
            $createTagIcon = $images['status-node-checked-disabled'];
            $createTagLink = '';
          }
        } else {
          if ($this->category['permission_mode'] != 'inherited') {
            $createTagIcon = $images['status-node-empty'];
            $createTagLink = $this->getLink(
              array(
                'cmd' => 'add_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'user_edit_tag',
                'perm_id' => $groupId,
              )
            );
          } else {
            $createTagIcon = $images['status-node-empty-disabled'];
            $createTagLink = '';
          }
        }
        if (isset($categoryPerms['user_use_tags'][$groupId])) {
          if ($categoryPerms['user_use_tags'][$groupId] == 'own') {
            $useTagIcon = $images['status-node-checked'];
            $useTagLink = $this->getLink(
              array(
                'cmd' => 'del_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'user_use_tags',
                'perm_id' => $groupId,
              )
            );
          } else {
            $useTagIcon = $images['status-node-checked-disabled'];
            $useTagLink = '';
          }
        } else {
          if ($this->category['permission_mode'] != 'inherited') {
            $useTagIcon = $images['status-node-empty'];
            $useTagLink = $this->getLink(
              array(
                'cmd' => 'add_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'user_use_tags',
                'perm_id' => $groupId,
              )
            );
          } else {
            $useTagIcon = $images['status-node-empty-disabled'];
            $useTagLink = '';
          }
        }
        $result .= sprintf(
          '<listitem image="%s" title="%s">'.LF,
          papaya_strings::escapeHTMLChars($images['items-permission']),
          papaya_strings::escapeHTMLChars($groupTitle)
        );
        if (isset($createCategoryLink) && $createCategoryLink != '') {
          $result .= '<subitem align="center">'.LF;
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" /></a>'.LF,
            papaya_strings::escapeHTMLChars($createCategoryLink),
            papaya_strings::escapeHTMLChars($createCategoryIcon)
          );
          $result .= '</subitem>'.LF;
        } else {
          $result .= '<subitem align="center">'.LF;
          $result .= sprintf(
            '<glyph src="%s" />'.LF,
            papaya_strings::escapeHTMLChars($createCategoryIcon)
          );
          $result .= '</subitem>'.LF;
        }
        if (isset($createTagLink) && $createTagLink != '') {
          $result .= '<subitem align="center">'.LF;
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" /></a>'.LF,
            papaya_strings::escapeHTMLChars($createTagLink),
            papaya_strings::escapeHTMLChars($createTagIcon)
          );
          $result .= '</subitem>'.LF;
        } else {
          $result .= '<subitem align="center">'.LF;
          $result .= sprintf(
            '<glyph src="%s" />'.LF,
            papaya_strings::escapeHTMLChars($createTagIcon)
          );
          $result .= '</subitem>'.LF;
        }
        if (isset($useTagLink) && $useTagLink != '') {
          $result .= '<subitem align="center">'.LF;
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" /></a>'.LF,
            papaya_strings::escapeHTMLChars($useTagLink),
            papaya_strings::escapeHTMLChars($useTagIcon)
          );
          $result .= '</subitem>'.LF;
        } else {
          $result .= '<subitem align="center">'.LF;
          $result .= sprintf(
            '<glyph src="%s" />'.LF,
            papaya_strings::escapeHTMLChars($useTagIcon)
          );
          $result .= '</subitem>'.LF;
        }
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Surfer permissions'))
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Permission'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Active'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Create/Edit category'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Create tag'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Use tags'))
      );
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;

      /*********************
      * Surfer permissions *
      *********************/

      foreach ($surferPerms as $permId => $permission) {
        $result .= sprintf(
          '<listitem image="%s" title="%s">'.LF,
          papaya_strings::escapeHTMLChars($images['items-permission']),
          papaya_strings::escapeHTMLChars($permission['surferperm_title'])
        );
        if (isset($categoryPerms['surfer_edit_category'][$permId])) {
          if ($categoryPerms['surfer_edit_category'][$permId] == 'own') {
            $createCategoryIcon = $images['status-node-checked'];
            $createCategoryLink = $this->getLink(
              array(
                'cmd' => 'del_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'surfer_edit_category',
                'perm_id' => $permId,
              )
            );
          } else {
            $createCategoryIcon = $images['status-node-checked-disabled'];
            $createCategoryLink = '';
          }
        } else {
          if ($this->category['permission_mode'] != 'inherited') {
            $createCategoryIcon = $images['status-node-empty'];
            $createCategoryLink = $this->getLink(
              array(
                'cmd' => 'add_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'surfer_edit_category',
                'perm_id' => $permId,
              )
            );
          } else {
            $createCategoryIcon = $images['status-node-empty-disabled'];
            $createCategoryLink = '';
          }
        }
        if (isset($categoryPerms['surfer_edit_tag'][$permId])) {
          if ($categoryPerms['surfer_edit_tag'][$permId] == 'own') {
            $createTagIcon = $images['status-node-checked'];
            $createTagLink = $this->getLink(
              array(
                'cmd' => 'del_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'surfer_edit_tag',
                'perm_id' => $permId,
              )
            );
          } else {
            $createTagIcon = $images['status-node-checked-disabled'];
            $createTagLink = '';
          }
        } else {
          if ($this->category['permission_mode'] != 'inherited') {
            $createTagIcon = $images['status-node-empty'];
            $createTagLink = $this->getLink(
              array(
                'cmd' => 'add_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'surfer_edit_tag',
                'perm_id' => $permId,
              )
            );
          } else {
            $createTagIcon = $images['status-node-empty-disabled'];
            $createTagLink = '';
          }
        }
        if (isset($categoryPerms['surfer_use_tags'][$permId])) {
          if ($categoryPerms['surfer_use_tags'][$permId] == 'own') {
            $useTagIcon = $images['status-node-checked'];
            $useTagLink = $this->getLink(
              array(
                'cmd' => 'del_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'surfer_use_tags',
                'perm_id' => $permId,
              )
            );
          } else {
            $useTagIcon = $images['status-node-checked-disabled'];
            $useTagLink = '';
          }
        } else {
          if ($this->category['permission_mode'] != 'inherited') {
            $useTagIcon = $images['status-node-empty'];
            $useTagLink = $this->getLink(
              array(
                'cmd' => 'add_perm',
                'cat_id' => $this->category['category_id'],
                'perm_type' => 'surfer_use_tags',
                'perm_id' => $permId,
              )
            );
          } else {
            $useTagIcon = $images['status-node-empty-disabled'];
            $useTagLink = '';
          }
        }
        if ($permission['surferperm_active'] == 1) {
          $activeIcon = $images['status-node-checked-disabled'];
        } else {
          $activeIcon = $images['status-node-empty-disabled'];
        }
        $result .= sprintf(
          '<subitem align="center"><glyph src="%s" /></subitem>'.LF,
          papaya_strings::escapeHTMLChars($activeIcon)
        );
        if (isset($createCategoryLink) && $createCategoryLink != '') {
          $result .= '<subitem  align="center">'.LF;
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" /></a>'.LF,
            papaya_strings::escapeHTMLChars($createCategoryLink),
            papaya_strings::escapeHTMLChars($createCategoryIcon)
          );
          $result .= '</subitem>'.LF;
        } else {
          $result .= '<subitem  align="center">'.LF;
          $result .= sprintf(
            '<glyph src="%s" />'.LF,
            papaya_strings::escapeHTMLChars($createCategoryIcon)
          );
          $result .= '</subitem>'.LF;
        }
        if (isset($createTagLink) && $createTagLink != '') {
          $result .= '<subitem  align="center">'.LF;
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" /></a>'.LF,
            papaya_strings::escapeHTMLChars($createTagLink),
            papaya_strings::escapeHTMLChars($createTagIcon)
          );
          $result .= '</subitem>'.LF;
        } else {
          $result .= '<subitem  align="center">'.LF;
          $result .= sprintf(
            '<glyph src="%s" />'.LF,
            papaya_strings::escapeHTMLChars($createTagIcon)
          );
          $result .= '</subitem>'.LF;
        }
        if (isset($useTagLink) && $useTagLink != '') {
          $result .= '<subitem  align="center">'.LF;
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" /></a>'.LF,
            papaya_strings::escapeHTMLChars($useTagLink),
            papaya_strings::escapeHTMLChars($useTagIcon)
          );
          $result .= '</subitem>'.LF;
        } else {
          $result .= '<subitem  align="center">'.LF;
          $result .= sprintf(
            '<glyph src="%s" />'.LF,
            papaya_strings::escapeHTMLChars($useTagIcon)
          );
          $result .= '</subitem>'.LF;
        }
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * get available surfer permissions
  */
  function getSurferPermissions() {
    $result = array();
    $sql = "SELECT surferperm_id, surferperm_title, surferperm_active
              FROM %s";
    if ($res = $this->databaseQueryFmt($sql, array(PAPAYA_DB_TBL_SURFERPERM))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['surferperm_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * get list of available backend user groups
  */
  function getGroups() {
    $result = array(-1 => 'Administrator');
    $sql = "SELECT group_id, grouptitle
              FROM %s";
    if ($res = $this->databaseQueryFmt($sql, array(PAPAYA_DB_TBL_AUTHGROUPS))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['group_id']] = $row['grouptitle'];
      }
    }
    return $result;
  }

  /**
   * add a permission to a category
   *
   * @param integer $categoryId id of a tag category
   * @param string $permissionType type of permission, see {papaya_tags::permissions}
   * @param integer $permissionId permission value, i.e. surfer_id or group_id
   * @return bool|int|NULL
   */
  function addPermission($categoryId, $permissionType, $permissionId) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE category_id = %d
               AND permission_type = '%s'
               AND permission_value = %d
           ";
    $params = array($this->tableTagCategoryPermissions, $categoryId,
      $permissionType, $permissionId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if (!$res->fetchField()) {
        $data = array(
          'category_id' => $categoryId,
          'permission_type' => $permissionType,
          'permission_value' => $permissionId,
        );
        return $this->databaseInsertRecord(
          $this->tableTagCategoryPermissions, NULL, $data
        );
      }
    }
    return FALSE;
  }

  /**
   * remove a permission from a category
   *
   * @param integer $categoryId id of a tag category
   * @param string $permissionType type of permission, see {papaya_tags::permissions}
   * @param integer $permissionId permission value, i.e. surfer_id or group_id
   * @return int
   */
  function delPermission($categoryId, $permissionType, $permissionId) {
    $condition = array(
      'category_id' => $categoryId,
      'permission_type' => $permissionType,
      'permission_value' => $permissionId,
    );
    return $this->databaseDeleteRecord($this->tableTagCategoryPermissions, $condition);
  }

  /**
  * generate dialog for category deletion
  */
  function getDelCategoryDialog() {
    if (!$this->categoryIsEmpty($this->params['cat_id'])) {
      $this->addMsg(
        MSG_WARNING,
        sprintf(
          $this->_gt(
            'Category "%s" is not empty. Deleting it will delete all subcategories and tags.'
          ),
          $this->category['category_title']
        )
      );
    }

    $hidden = array(
      'cmd' => $this->params['cmd'],
      'cat_id' => $this->params['cat_id'],
      'confirm' => 1,
    );

    $title = $this->getCategoryTitle($this->category, $this->params['cat_id']);

    $this->dialog = new base_msgdialog(
      $this,
      $this->paramName,
      $hidden,
      sprintf($this->_gt('Do you really want to delete category "%s"?'), $title),
      'question'
    );
    $this->dialog->buttonTitle = 'Delete';
    $this->layout->addRight($this->dialog->getMsgDialog());
  }

  /**
  * generate dialog for moving/pasting a category
  */
  function getMoveCategoryDialog() {
    if (!isset($this->categories) || !is_array($this->categories)
        || !isset($this->categories[$this->sessionParams['move_category']])
        || !isset($this->categories[$this->params['cat_id']])) {
      $this->loadCategories(
        array(
          $this->sessionParams['move_category'],
          $this->params['cat_id']
        )
      );
    }

    if (isset($this->params['cat_id']) && !$this->params['cat_id'] == 0) {
      $destCategory = $this->getCategoryTitle(
        $destCategory = $this->categories[$this->params['cat_id']],
        $this->params['cat_id']
      );
    } else {
      $destCategory = $this->_gt('Base');
    }

    $hidden = array(
      'cmd' => $this->params['cmd'],
      'cat_id' => $this->params['cat_id'],
      'confirm' => 1
    );

    $moveCategory = $this->getCategoryTitle(
      $this->categories[$this->sessionParams['move_category']],
      $this->sessionParams['move_category']
    );
    $dialogMsg = sprintf(
      $this->_gt('Do you really want to move category "%s" below category "%s"?'),
      $moveCategory,
      $destCategory
    );
    $this->dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $dialogMsg, 'question'
    );
    $this->dialog->buttonTitle = 'Move';
    $this->layout->addRight($this->dialog->getMsgDialog());
  }

  /**
  * adds category to database
  * @return boolean
  */
  function addNewCategory() {
    $lngData = array(
      $this->papaya()->administrationLanguage->id => array(
        'category_title' => $this->params['category_title'],
        'category_description' => $this->params['category_description'],
        'category_name' => $this->params['category_name']
      ),
    );
    $catId = $this->addCategory(
      $this->params['parent_id'],
      $this->params['permission_mode'],
      'admin',
      $this->papaya()->administrationUser->userId,
      $lngData
    );
    if ($catId) {
      $this->params['cat_id'] = $catId;
      $this->loadCategories();
      return $catId;
    }
    return FALSE;
  }

  /**
   * updates a category
   *
   * @return boolean
   */
  function setCategory() {
    $update = FALSE;
    if (
      !isset($this->category['permission_mode'], $this->category['category_name']) ||
      (string)$this->category['category_name'] !== (string)$this->params['category_name'] ||
      (string)$this->category['permission_mode'] !== (string)$this->params['permission_mode']
    ) {
      $dataPerm = array(
        'category_name' => $this->params['category_name'],
        'permission_mode' => $this->params['permission_mode'],
      );
      $condition = array(
        'category_id' => $this->params['cat_id'],
      );
      if ($this->databaseUpdateRecord($this->tableTagCategory, $dataPerm, $condition)) {
        $update = TRUE;
      }
    }
    if (isset($this->category['category_title']) ||
        isset($this->category['category_description'])) {
      if ($this->category['category_title'] != $this->params['category_title'] ||
          $this->category['category_description'] != $this->params['category_description']) {
        $dataTrans = array(
          'category_title' => $this->params['category_title'],
          'category_description' => $this->params['category_description'],
        );
        $condition = array(
          'category_id' => $this->params['cat_id'],
          'lng_id' => $this->papaya()->administrationLanguage->id,
        );
        $update = $this->databaseUpdateRecord(
          $this->tableTagCategoryTrans, $dataTrans, $condition
        );
      }
    } else {
      $dataTrans = array(
        'lng_id' => $this->papaya()->administrationLanguage->id,
        'category_id' => $this->params['cat_id'],
        'category_title' => $this->params['category_title'],
        'category_description' => $this->params['category_description'],
      );
      $update = $this->databaseInsertRecord(
        $this->tableTagCategoryTrans, NULL, $dataTrans
      );
    }
    if ($update) {
      $this->loadCategories();
      $this->addMsg(
        MSG_INFO,
        sprintf(
          $this->_gt('Category "%s" (%d) has been updated.'),
          $this->params['category_title'],
          $this->params['cat_id']
        )
      );
    }
    return $update;
  }

  /**
  * deletes a category
  */
  function delCategory($catId) {
    $result = $this->deleteCategory($catId);
    $this->loadCategories();
    return $result;
  }

  /**
   * moves a category and its subtree to a different parent node
   *
   * @param integer $categoryId the tag category id to be moved
   * @param integer $newParentId new parent node id to move category to
   * @return bool
   */
  function moveCategory($categoryId, $newParentId) {
    $result = FALSE;
    if ($categoryId) {
      $categories = $this->loadCategorySubTree($categoryId);
      if (isset($this->categories[$newParentId])) {
        $parentCategory = $this->categories[$newParentId];
        $parentPath = $parentCategory['parent_path'].(int)$newParentId;
      } else {
        $parentCategory = $this->getCategory($newParentId);
        $parentCategory = current($parentCategory);
        $parentPath = $parentCategory['parent_path'].(int)$newParentId;
      }
      foreach ($categories as $category) {
        if ($category['category_id'] == $categoryId) {
          $updateData[] = array(
            'condition' => array(
              'category_id' => $category['category_id'],
            ),
            'data' => array(
              'parent_id' => $newParentId,
              'parent_path' => $parentPath.';',
            ),
          );
        } else {
          $updateData[] = array(
            'condition' => array(
              'category_id' => $category['category_id'],
            ),
            'data' => array(
              'parent_path' => $parentPath.substr(
                $category['parent_path'],
                strpos($category['parent_path'], ';'.$categoryId.';')
              ),
            ),
          );
        }
      }
      if (isset($updateData) && is_array($updateData)) {
        foreach ($updateData as $update) {
          $result = (
            $result ||
            $this->databaseUpdateRecord(
              $this->tableTagCategory, $update['data'], $update['condition']
            )
          );
        }
      }
    }
    return $result;
  }

  /**
  * load the subtree of a category
  *
  * @param integer $categoryId a tag category id
  * @return array $result array ( category_id => array(category_id, parent_path))
  */
  function loadCategorySubTree($categoryId) {
    $result = array();
    $sql = "SELECT category_id, parent_path
              FROM %s
             WHERE parent_path LIKE '%%;%d;%%'
                OR category_id = %d
           ";
    $params = array($this->tableTagCategory, $categoryId, $categoryId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['category_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * Initialize object for tag dialog
  * @return void
  */
  function initializeTagDialog() {
    if (!(isset($this->tagDialog) && is_object($this->tagDialog))) {
      if (isset($this->currentTag) &&
          isset($this->currentTag['tag_id']) &&
          $this->currentTag['tag_id'] > 0 &&
          (empty($this->params['cmd']) || $this->params['cmd'] != 'add_tag')) {
        $dialogTitle = 'Edit tag';
        $buttonTitle = 'Save';
        $hidden = array(
          'cmd' => 'edit_tag',
          'tag_id' => $this->currentTag['tag_id'],
          'cat_id' => $this->currentTag['category_id'],
          'save' => 1
        );
        $tagDetails = $this->getTagDetails($this->currentTag['tag_id']);
        switch ($tagDetails['creator_type']) {
        case 'surfer':
          $authorType = $this->_gt('Surfer');
          $author = $tagDetails['surfer_givenname'].' '.$tagDetails['surfer_surname'];
          break;
        case 'admin':
          $authorType = $this->_gt('Backend user');
          $author = $tagDetails['surfer_givenname'].' '.$tagDetails['surfer_surname'];
          break;
        default:
          $authorType = $this->_gt('Unknown');
          $author = '';
        }
        if ((int)$tagDetails['creation_time'] != 0) {
          $date = date('j.n.Y G:i', (int)$tagDetails['creation_time']);
        } else {
          $date = $this->_gt('Unknown');
        }
        $fields = array(
          'tag_author' => array(
            $authorType, '', FALSE, 'disabled_input', 400, '', $author
          ),
          'tag_created' => array(
            'Created', '', FALSE, 'disabled_input', 400, '', $date
          )
        );
        $data = array(
          'tag_uri' => $this->currentTag['tag_uri']
        );
      } else {
        $dialogTitle = 'Add tag';
        $buttonTitle = 'Add';
        $data = array();
        $hidden = array(
          'cmd' => 'add_tag',
          'tag_id' => 0,
          'cat_id' => $this->params['cat_id'],
          'save' => 1
        );
        $fields = array();
      }

      $fields['tag_title'] = array(
        'Title', 'isNoHtml', FALSE, 'input', 100, '',
         isset($this->currentTag['tag_title']) ? $this->currentTag['tag_title'] : ''
      );
      $fields['tag_image'] = array(
        'Image', 'isSomeText', FALSE, 'image', 100, '',
         isset($this->currentTag['tag_image']) ? $this->currentTag['tag_image'] : ''
      );
      $fields['tag_description'] = array(
        'Description', 'isSomeText', FALSE, 'simplerichtext', 2, '',
         isset($this->currentTag['tag_description']) ? $this->currentTag['tag_description'] : ''
      );

      $fields[] = 'Language Independent';
      $fields['tag_uri'] = array(
        'Tag URI', 'isAlphaNumChar', FALSE, 'input', 200
      );
      $this->tagDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->tagDialog->dialogTitle = $this->_gt($dialogTitle);
      $this->tagDialog->buttonTitle = $buttonTitle;
      $this->tagDialog->baseLink = $this->baseLink;
      $this->tagDialog->loadParams();
    }
  }

  /**
  * get tag add/edit dialog
  */
  function getEditTagDialog() {
    $this->initializeTagDialog();
    $this->layout->addRight($this->tagDialog->getDialogXML());
  }

  /**
  * Creates dialog for tag deletion
  */
  function getDelTagDialog() {
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'tag_id' => $this->params['tag_id'],
      'confirm' => 1,
    );
    $this->dialog = new base_msgdialog(
      $this,
      $this->paramName,
      $hidden,
      $this->_gt('Do you really want to delete this tag?'),
      'question'
    );
    $this->dialog->buttonTitle = 'Delete';
    $this->layout->addRight($this->dialog->getMsgDialog());
  }

  /**
  * Adds tag to database
  */
  function addNewTag($values) {
    return $this->addTag(
      $values['cat_id'],
      $this->papaya()->administrationLanguage->id,
      'admin',
      $this->papaya()->administrationUser->userId,
      $values,
      $values['tag_uri']
    );
  }

  /**
  * Updates existing tag
  */
  function setTag($values, $tagId) {
    if ($this->checkTagURI($values['tag_uri'], $this->currentTag['tag_uri'])) {
      $data = array(
        'tag_uri' => $values['tag_uri']
      );
      $filter = array(
        'tag_id' => $tagId
      );
      if (FALSE !== $this->databaseUpdateRecord($this->tableTag, $data, $filter)) {
        $inserts = array();
        $updates = array();
        $lngId = $this->papaya()->administrationLanguage->id;

        $oldTag = $this->getTag($tagId, $lngId);

        if (isset($oldTag['tag_title'])) {
          $updates[] = array(
            'filter' => array('lng_id' => $lngId, 'tag_id' => $tagId),
            'data' => array(
              'tag_title' => $values['tag_title'],
              'tag_image' => $values['tag_image'],
              'tag_description' => $values['tag_description'],
              'tag_char' => $this->compileTagChar($values['tag_title']),
            )
          );
        } else {
          $inserts[] = array(
            'tag_id' => $tagId,
            'lng_id' => $lngId,
            'tag_title' => $values['tag_title'],
            'tag_image' => $values['tag_image'],
            'tag_description' => $values['tag_description'],
            'tag_char' => $this->compileTagChar($values['tag_title']),
          );
        }
        if (count($inserts) > 0) {
          if ($this->databaseInsertRecords($this->tableTagTrans, $inserts)) {
            return FALSE;
          }
        }
        foreach ($updates as $update) {
          if (
            FALSE === $this->databaseUpdateRecord(
              $this->tableTagTrans, $update['data'], $update['filter']
            )
          ) {
            return FALSE;
          }
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * deletes tag
  */
  function delTag($tagId) {
    if (NULL !== $tagId) {
      $condition = array('tag_id' => $tagId);
      if ($this->databaseDeleteRecord($this->tableTagLinks, $condition)) {
        $this->addMsg(
          MSG_INFO,
          sprintf($this->_gt('Deleted links to tag #%d.'), $tagId)
        );
      }
      if ($this->databaseDeleteRecord($this->tableTagTrans, $condition)) {
        $this->addMsg(
          MSG_INFO,
          sprintf($this->_gt('Deleted translations of tag #%d.'), $tagId)#
        );
      }
      if ($this->databaseDeleteRecord($this->tableTag, $condition)) {
        $this->addMsg(
          MSG_INFO,
          sprintf($this->_gt('Deleted tag #%d.'), $tagId)
        );
      }
    }
  }

  /** IMPORT OF TAGS FROM CSV FILE */

  /**
  * This method prints information on how the CSV file should be formatted
  */
  function getImportInfo() {
    $result = '';
    $result .= '<sheet>'.LF;
    $result .= '<header>'.LF;
    $result .= '<lines>'.LF;
    $result .= '<line>'.LF;
    $result .= papaya_strings::escapeHTMLChars(
      $this->_gt('The CSV file should look like this:')
    ).LF;
    $result .= '</line>'.LF;
    $result .= '</lines>'.LF;
    $result .= '</header>'.LF;
    $result .= '<text><pre>'.LF;
    $result .= 'DE; 1.  Kategorie; 1.  Unterkategorie; Tag  1'.LF;
    $result .= 'EN; 1st catgory;   1st subcategory;    tag #1'.LF;
    $result .= 'DE; 2.  Kategorie; 2.  Unterkategorie; Tag  2'.LF;
    $result .= 'EN; 2nd catgory;   2nd subcategory;    tag #2'.LF;
    $result .= '</pre></text>'.LF;
    $result .= '</sheet>'.LF;
    $this->layout->addRight($result);
  }

  /**
  * This method creates the upload form for the CSV import
  */
  function getImportTagsDialog() {
    $result = '';
    // if ($this->module->hasPerm(??)) { // may be reactivated if neccessary
    if (isset($this->params['cat_id'])) {
      if ($this->categoryIsEmpty($this->params['cat_id'])) {
        $result .= sprintf(
          '<dialog action="%s" title="%s" type="file" enctype="multipart/form-data">'.LF,
          papaya_strings::escapeHTMLChars($this->baseLink),
          papaya_strings::escapeHTMLChars($this->_gt('CSV import'))
        );
        $result .= sprintf(
          '<input type="hidden" name="MAX_FILE_SIZE" value="%s" />'.LF,
          papaya_strings::escapeHTMLChars($this->mediaDB->getMaxUploadSize())
        );
        $result .= sprintf(
          '<input type="hidden" name="%s[step]" value="1" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName)
        );
        $result .= sprintf(
          '<input type="hidden" name="%s[cat_id]" value="%s" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($this->params['cat_id'])
        );
        $result .= sprintf(
          '<input type="hidden" name="%s[cmd]" value="%s" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($this->params['cmd'])
        );
        $result .= '<lines>';

        $result .= sprintf(
          '<line caption="%s">',
          papaya_strings::escapeHTMLChars($this->_gt('First row contains titles'))
        );
        $result .= sprintf(
          '<input type="checkbox" name="%s[first_row_titles]" value="1" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName)
        );
        $result .= '</line>';
        $result .= sprintf(
          '<line caption="%s">',
          papaya_strings::escapeHTMLChars($this->_gt('Upload'))
        );
        $result .= sprintf(
          '<input type="file" size="40" class="file" name="%s[import_csv]" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName)
        );
        $result .= '</line>';
        $result .= '</lines>';
        $result .= sprintf(
          '<dlgbutton value="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Upload'))
        );
        $result .= '</dialog>'.LF;

      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('The category must be empty for importing tags.')
        );
      }
    }
    // }
    $this->layout->addRight($result);
  }

  /**
  * This method reads out the import CSV file and prepares the data
  */
  function importTags() {
    $csvObj = new base_csv();
    $associative = FALSE;
    // this prepares for using XMLRpc for this to keep load low
    $limit = NULL; // NULL means no limit
    $offset = 0;
    $csvData = $csvObj->readCSVFile(
      $this->getCacheFileName(),
      empty($this->params['first_row_titles']) ? FALSE : (bool)$this->params['first_row_titles'],
      $associative,
      $limit,
      $offset
    );
    if ($csvData) {
      // atm we want an empty category, because the handling of already existing
      // tags is not yet implemented
      if ($this->categoryIsEmpty($this->params['cat_id'])) {
        $tagRecords = array();
        $tagId = 0;
        foreach ($csvData as $i => $record) {
          $lngId = array_shift($csvData[$i]);
          if (isset($tagRecords[$tagId][$lngId])) {
            $tagId++;
          }
          $tagRecords[$tagId][$lngId] = $csvData[$i];
        }
        foreach ($tagRecords as $lngData) {
          $this->addImportTag($lngData);
        }
      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('Category must be empty for importing tags.')
        );
      }
    }
  }

  /**
  * This method adds an imported tag with its translations
  *
  * @param array $tagLngData array with tag categories and titles for each language
  *              array('DE' => array(0 => '1kat', 1 => '2kat', 3 => 'mein tag'),
  *                    'EN' => array(0 => 'cat 1', 1 => 'cat 2', 3 => 'my tag'))
  */
  function addImportTag($tagLngData) {
    $tagTitles = array();
    foreach ($tagLngData as $lngIdent => $tagData) {
      foreach ($tagData as $level => $title) {
        // we don't want any empty titles
        if ($title != '') {
          // we'll need the actual id for the lng ident we have got
          $tagLngId = $this->papaya()->languages->getLanguage(strtolower($lngIdent))->id;
          // this will be the actual tag lng => title array after the last run
          // of this foreach loop
          $tagTitles[$tagLngId] = papaya_strings::ensureUTF8($title);
          // this will be the list of categories
          $categories[$level][$tagLngId]['category_title'] = papaya_strings::ensureUTF8($title);
        }
      }
    }
    // remove the last entry from categories since it's the actual tag
    array_pop($categories);
    $categoryId = $this->addImportTagCategories($this->params['cat_id'], $categories);
    if ($categoryId) {
      $this->addTag(
        $categoryId,
        $this->papaya()->administrationLanguage->id,
        'admin',
        $this->papaya()->administrationUser->userId,
        $tagTitles
      );
    }
  }

  /**
  * This method adds the necessary categories for a tag; called recursively
  *
  * @param integer $parentId the parent id to add the category to
  * @param array $categoryData flat list of category tree; key equals level
  * @param integer $level the current level that is processed
  * @return integer the last category id, i.e. the one the tag goes
  */
  function addImportTagCategories($parentId, $categoryData, $level = 0) {
    if (isset($categoryData[$level])) {
      // this should use already existing categories, though ATM it's blocked
      // in the first place
      foreach ($categoryData[$level] as $lngId => $category) {
        if (
          $categoryIds = $this->getCategoryIdsByTitle(
            $category['category_title'], $parentId, $lngId
          )
        ) {
          $categoryId = current($categoryIds);
          break;
        }
      }
      if (!isset($categoryId) || !$categoryId) {
        // this adds the not yet existing category
        $categoryId = $this->addCategory(
          $parentId,
          'inherited',
          'admin',
          $this->papaya()->administrationUser->userId,
          $categoryData[$level]
        );
      }
      // recursive call this method to create subcategories
      return $this->addImportTagCategories($categoryId, $categoryData, $level + 1);
    }
    // we don't have any levels left, so return the last category id
    return $parentId;
  }

  /**
  * This method handles the csv import
  */
  function processCSVUpload() {
    if (!isset($this->params['step'])) {
      $this->params['step'] = 0;
    }
    switch ($this->params['step']) {
    default:
    case 0:
      $this->getImportInfo();
      $this->getImportTagsDialog();
      break;
    case 1:
      if ($this->checkFile()) {
        $tempFileName = $_FILES[$this->paramName]['tmp_name']['import_csv'];
        $fileData = file_get_contents($tempFileName);
        $cacheId = md5($tempFileName.time());
        $this->sessionParams['cache_id'] = $cacheId;
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        $cacheFileName = $this->getCacheFileName($cacheId);
        if ($fp = fopen($cacheFileName, 'w+')) {
          fwrite($fp, $fileData);
          fclose($fp);
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Couldn\'t copy temporary file.'));
        }
        @unlink($tempFileName);

        $this->importTags();
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Upload failed.'));
      }
      break;
    }
  }

  /**
   * This method constructs the cache file name for the uploaded file
   *
   * @param string $cacheId if this is not set, it will be fetched from session
   * @return string
   */
  function getCacheFileName($cacheId = NULL) {
    if ($cacheId == NULL) {
      $cacheId = $this->sessionParams['cache_id'];
    }
    return $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::PATH_CACHE, '').'.tags_'.$cacheId.'.csv';
  }

  /**
  * This method checks whether the uploaded file is a CSV file
  *
  * @return boolean TRUE if it is a CSV file, else FALSE
  */
  function checkFile() {
    if (isset($_FILES[$this->paramName]['tmp_name'])
        && isset($_FILES[$this->paramName]['name'])) {
      $tempFileName = $_FILES[$this->paramName]['tmp_name']['import_csv'];
      $tempFileTitle = $_FILES[$this->paramName]['name']['import_csv'];
      if (@file_exists($tempFileName) && @is_uploaded_file($tempFileName)) {
        $tempFileSize = @filesize($tempFileName);
        if ($tempFileSize > 0 && $tempFileSize < $this->mediaDB->getMaxUploadSize()) {
          $properties = $this->mediaDB->getFileProperties($tempFileName, $tempFileTitle);
          if ($properties['mimetype'] == 'text/csv') {
            return TRUE;
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Invalid file type!'));
            @unlink($tempFileName);
          }
        } elseif ($tempFileSize == 0) {
          $this->addMsg(
            MSG_ERROR,
            sprintf($this->_gt('File "%s" is empty.'), $tempFileTitle)
          );
          @unlink($tempFileName);
        } else {
          $this->addMsg(
            MSG_ERROR,
            sprintf($this->_gt('File "%s" is to large.'), $tempFileTitle)
          );
          @unlink($tempFileName);
        }
      }
    }
    return FALSE;
  }
}
