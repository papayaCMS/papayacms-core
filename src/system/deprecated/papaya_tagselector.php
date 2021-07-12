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

use Papaya\CMS\Administration\Permissions;

/**
* Tags Administration
*
* @package Papaya
* @subpackage Administration
*/
class papaya_tagselector extends base_tags {

  /**
  * Selected tags list
  * @var array
  */
  var $selectedTags = array();

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
  * Category tree size
  * @var integer
  */
  var $categoryTreeSize = 0;

  /**
  * selection mode mutiple|single
  * @var string
  */
  var $tagSelectionMode = 'multiple';

  /**
   * @var array|NULL
   * @deprecated
   */
  public $category = NULL;

  /**
   * @var array
   */
  private $categoryTree = array();

  /**
  * papaya 5 constructor
  */
  function __construct($parentObj, $paramName = 'tg') {
    parent::__construct();
    $this->paramName = $paramName;
    $this->parentObj = $parentObj;
  }

  /**
  * get an instance of base_taglinks, NOT A SINGLETON, only for convenience
  *
  * @param \Papaya\Application\Access $parentObj parent object, must hold msgs, authUser, images
  * @return object $tagLinks instance of base_taglinks
  */
  public static function getInstance($parentObj = NULL) {
    $administrationUser = $parentObj->papaya()->administrationUser;
    if ($administrationUser->hasPerm(Permissions::TAG_MANAGE)) {
      $instance = new papaya_tagselector($parentObj, $parentObj->paramName);
      return $instance;
    }
    return NULL;
  }

  /**
  * Get selector xml
  * @param array $selectedTags
  * @param string $mode
  * @return string
  */
  function getTagSelector($selectedTags, $mode = 'multiple') {
    $this->initialize();
    $this->tagSelectionMode = $mode;
    $this->setSelectedTags($selectedTags);
    $this->execute();
    $this->loadCategories();
    $this->checkSelectedCategory();
    return $this->getTagSelectorXML();
  }

  /**
  * initialize language selector and session
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.get_class($this).'_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('cat_id');
    $this->initializeSessionParam('limit');
    $this->initializeSessionParam('offset_tags');

    if (!isset($this->sessionParams['open_categories'])) {
      $this->sessionParams['open_categories'] = array();
    }
    if (isset($this->params['cat_id']) && $this->params['cat_id'] > 0
        && !isset($this->sessionParams['open_categories'][$this->params['cat_id']])) {
      $this->sessionParams['open_categories'][$this->params['cat_id']] = $this->params['cat_id'];
    }
    if (!isset($this->params['cmd']) || $this->params['cmd'] != 'search') {
      unset($this->sessionParams['search_string']);
      unset($this->params['search_string']);
    } else {
      $this->initializeSessionParam('search_string', array('limit'));
    }

    $this->setSessionValue($this->sessionParamName, $this->sessionParams);

    if (!isset($this->params['limit']) || $this->params['limit'] == 0) {
      $this->params['limit'] = $this->defaultLimit;
    }
    $this->initializeNodes();
  }

  /**
  * check if selected category is open and open parent nodes if necessary
  */
  function checkSelectedCategory() {
    if (isset($this->params['cat_id']) && $this->params['cat_id'] > 0) {
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
      $this->sessionParams['open_categories'][$this->params['cat_id']] = $this->params['cat_id'];
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
      $this->loadCategories();
    }
  }

  /**
  * initializes open/closed nodes of tag tree
  */
  function initializeNodes() {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'open_category':
        if ($this->params['cat_id'] > 0) {
          $this->sessionParams['open_categories'][$this->params['cat_id']] = TRUE;
        }
        break;
      case 'close_category':
        if ($this->params['cat_id'] > 0
            && isset($this->sessionParams['open_categories'][$this->params['cat_id']])) {
          unset($this->sessionParams['open_categories'][$this->params['cat_id']]);
        }
        break;
      }
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    }
  }

  /**
  * links/unlinks/selects tag
  */
  function execute() {
    if (isset($this->params['cmd'])) {
      switch($this->params['cmd']) {
      case 'open':
        if (isset($this->params['cat_open_id']) && $this->params['cat_open_id'] > 0) {
          $this->sessionParams['open_categories'][(int)$this->params['cat_open_id']] =
            (int)$this->params['cat_open_id'];
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        break;
      case 'close':
        if (isset($this->params['cat_close_id']) && $this->params['cat_close_id'] > 0) {
          unset($this->sessionParams['open_categories'][(int)$this->params['cat_close_id']]);
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        }
        break;
      case 'select_tag':
        if (isset($this->params['tag_id']) && $this->params['tag_id'] > 0) {
          if ($this->tagSelectionMode == 'single') {
            unset($this->selectedTags);
          }
          $this->selectedTags[$this->params['tag_id']] = 1;
          return TRUE;
        }
        return FALSE;
        break;
      case 'deselect_tag':
        if (isset($this->params['tag_id']) && $this->params['tag_id'] > 0) {
          unset($this->selectedTags[$this->params['tag_id']]);
          return TRUE;
        }
        return TRUE;
        break;
      }
    }
    return FALSE;
  }

  /**
  * fetch selected tag id
  */
  function getSelectedTags() {
    return array_keys($this->selectedTags);
  }

  /**
  * Set selected tags property
  * @param array $selectedTags
  * @return void
  */
  function setSelectedTags($selectedTags) {
    $this->selectedTags = array();
    if (isset($selectedTags) && is_array($selectedTags)) {
      foreach ($selectedTags as $selectedTag) {
        if ($selectedTag != '' && $selectedTag > 0) {
          $this->selectedTags[$selectedTag] = 1;
        }
      }
    }
  }

  /**
   * generate listview of linked tags including their category path
   *
   * @param array $selectedTags array of tags that are linked, including tag titles, etc.
   * @return string $result tags listview xml
   */
  function getLinkedTagsListXML($selectedTags) {
    $result = '';

    $selectedCatIds = array();
    foreach ($selectedTags as $selectedTag) {
      if (!isset($this->categories[$selectedTag['category_id']])) {
        $selectedCatIds[$selectedTag['category_id']] = $selectedTag['category_id'];
      }
    }
    $selectedCategories = $this->getCategories(
      $selectedCatIds, (int)$this->papaya()->administrationLanguage->id
    );
    $allParents = array();
    foreach ($selectedCategories as $selectedCategory) {
      $parents = explode(';', $selectedCategory['parent_path']);
      foreach ($parents as $parentId) {
        $allParents[$parentId] = $parentId;
      }
      $allParents[$selectedCategory['category_id']] = $selectedCategory['category_id'];
    }
    foreach ($allParents as $key => $catId) {
      if ($catId <= 0 || isset($this->categories[$catId])) {
        unset($allParents[$key]);
      }
    }
    $moreCategories = $this->getCategories(
      $allParents, (int)$this->papaya()->administrationLanguage->id
    );
    foreach ($moreCategories as $category) {
      $this->categories[$category['category_id']] = $category;
    }

    $result .= sprintf(
      '<listview title="%s" id="tags_linked">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Selected tags'))
    );
    $result .= '<items>'.LF;
    $images = $this->papaya()->images;
    foreach ($selectedTags as $tagId => $tag) {
      $link = $this->getLink(array('cmd' => 'deselect_tag', 'tag_id' => $tagId)).'#tags_linked';
      $result .= sprintf(
        '<listitem image="%s" title="%s">'.LF,
        papaya_strings::escapeHTMLChars($images['items-tag']),
        papaya_strings::escapeHTMLChars($tag['tag_title'])
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        $this->getCategoryPath($tag['category_id'])
      );
      $result .= sprintf(
        '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s" /></a></subitem>'.LF,
        papaya_strings::escapeHTMLChars($link),
        papaya_strings::escapeHTMLChars($images['actions-tag-delete']),
        papaya_strings::escapeHTMLChars($this->_gt('Unlink tag'))
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * generate categories tree xml
  *
  * @return string $result listview xml
  */
  function getXMLCategoryTree() {
    $images = $this->papaya()->images;
    $result = sprintf(
      '<listview title="%s" id="tags_categories">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Categories'))
    );
    $result .= '<items>'.LF;
    if (isset($this->params) && isset($this->params['cat_id'])) {
      $selected = ($this->params['cat_id'] == 0) ? ' selected="selected"' : '';
    } else {
      $selected = '';
    }
    $result .= sprintf(
      '<listitem href="%s" title="%s" image="%s" node="empty" %s/>'.LF,
      papaya_strings::escapeHTMLChars($this->getLink(array('cat_id' => 0)).'#tag_categories'),
      papaya_strings::escapeHTMLChars($this->_gt('Base')),
      papaya_strings::escapeHTMLChars($images['places-desktop']),
      $selected
    );

    if (isset($this->categories) && is_array($this->categories)) {
      $result .= $this->getXMLCategorySubTree(0, 1);
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
        $this->categoryTreeSize++;
      }
    }
    return $result;
  }

  /**
  * generate category tree listview line
  */
  function getXMLCategoryEntry($id, $indent, $mode = TRUE) {
    $result = '';
    if (isset($this->categories[$id]) && is_array($this->categories[$id])) {
      $categoryCount = empty($this->categories[$id]['CATEG_COUNT'])
        ? 0 : $this->categories[$id]['CATEG_COUNT'];
      $opened = (bool)(isset($this->sessionParams['open_categories'][$id]) && ($categoryCount > 0));
      if ($categoryCount < 1) {
        $node = ' node="empty"';
      } elseif ($opened) {
        $nodeHref = $this->getLink(array('cmd' => 'close', 'cat_close_id' => (int)$id));
        $node = sprintf(' node="open" nhref="%s"', $nodeHref);
      } else {
        $nodeHref = $this->getLink(array('cmd' => 'open', 'cat_open_id' => (int)$id));
        $node = sprintf(' node="close" nhref="%s"', $nodeHref);
      }
      if (!isset($this->categories[$id]) || !isset($this->categories[$id]['category_title']) ||
          $this->categories[$id]['category_title'] == "") {
        $title = $this->_gt('No Title');
      } else {
        $title = $this->categories[$id]['category_title'];
      }
      if (isset($this->params) && isset($this->params['cat_id'])) {
        $selected = ($this->params['cat_id'] == $id) ? ' selected="selected"' : '';
      } else {
        $selected = '';
      }
      $result .= sprintf(
        '<listitem href="%s" title="%s" indent="%d" %s %s/>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cat_id' => (int)$id)).'#tag_categories'
        ),
        papaya_strings::escapeHTMLChars($title),
        (int)$indent,
        $node,
        $selected
      );
      $result .= $this->getXMLCategorySubTree($id, $indent + 1);
    }
    return $result;
  }

  /**
  * generate category path for a given category
  *
  * @param integer $categoryId tag category id
  * @return string $result category path with linked categories
  */
  function getCategoryPath($categoryId) {
    $result = '';
    if (isset($this->categories) && is_array($this->categories)
        && isset($this->categories[$categoryId])) {
      $parents = explode(';', $this->categories[$categoryId]['parent_path']);
      $result = '/ ';
      foreach ($parents as $catId) {
        if ($catId > 0) {
          $result .= sprintf(
            '<a href="%s">%s</a> / ',
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('cat_id' => $catId))
            ),
            papaya_strings::escapeHTMLChars($this->categories[$catId]['category_title'])
          );
        }
      }
      $result .= sprintf(
        '<a href="%s">%s</a>',
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cat_id' => $categoryId))
        ),
        papaya_strings::escapeHTMLChars($this->categories[$categoryId]['category_title'])
      );
    }
    return $result;
  }

  /**
  * generate list of tags of which one can be selected (for single selection)
  *
  * @param string $caption caption of listview
  * @param integer $selectedTagId tag that is selected
  * @param integer $lngId language Id to display tag names in current language
  * @return string $result tag linker listview
  */
  function getTagLinkerList($caption, $selectedTagId, $lngId) {
    $result = '';
    $result .= $this->getXMLCategoryTree();
    return $result;
  }

  /**
  * load categories and category tree
  */
  function loadCategories() {
    $this->categories = NULL;
    unset($this->categoryTree);

    if (isset($this->sessionParams['open_categories'])) {
      $parentIds = $this->sessionParams['open_categories'];
    }
    $parentIds[] = 0;

    $lngCondition = $this->databaseGetSQLCondition(
      'ct.lng_id',
      (int)$this->papaya()->administrationLanguage->id
    );
    $parentCondition = $this->databaseGetSQLCondition('c.parent_id', $parentIds);
    $availableCategories = $this->getAvailableCategories(
      $this->papaya()->administrationUser,
      array('user_use_tags')
    );
    if (is_array($availableCategories) && count($availableCategories) > 0) {
      $categoryCondition = ' AND '.$this->databaseGetSQLCondition(
        'c.category_id', $availableCategories
      );
    } else {
      $categoryCondition = '';
    }
    $sql = "SELECT DISTINCT c.category_id, c.parent_id, c.parent_path,
                   c.permission_mode,
                   ct.category_title, ct.category_description, ct.lng_id
              FROM %s c
              LEFT OUTER JOIN %s ct
                   ON (c.category_id = ct.category_id AND $lngCondition)
             WHERE $parentCondition
                   $categoryCondition
             ORDER BY c.parent_path ASC, ct.category_title ASC";
    $params = array($this->tableTagCategory, $this->tableTagCategoryTrans);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->categories[(int)$row['category_id']] = $row;
        $this->categoryTree[(int)$row['parent_id']][$row['category_id']] = $row['category_id'];
      }
      $this->loadCategoryCounts($this->categories);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * load number of subnodes of each category
  */
  function loadCategoryCounts(&$categories) {
    if (is_array($categories) && count($categories) > 0) {
      $categoryCondition = $this->databaseGetSQLCondition(
        'parent_id', array_keys($categories)
      );
      $sql = "SELECT COUNT(*) AS count, parent_id
                FROM %s
              WHERE $categoryCondition
              GROUP BY parent_id";
      if ($res = $this->databaseQueryFmt($sql, $this->tableTagCategory)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->categories[(int)$row['parent_id']]['CATEG_COUNT'] = $row['count'];
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * load current category in current language (fills $this->category)
   *
   * @return array
   */
  function loadCategory() {
    return $this->category = $this->getCategory(
      $this->params['cat_id'], $this->papaya()->administrationLanguage->id
    );
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
    if (isset($this->params['limit']) && $this->params['limit'] >= 10) {
      $steps = (int)$this->params['limit'];
    } else {
      $steps = 10;
    }
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
  * generate search dialog xml
  *
  * @return string $result dialog xml
  */
  function getXMLSearchForm() {
    $data = array(
      'search_string' => empty($this->params['search_string'])
        ? '' : (string)$this->params['search_string']
    );
    $hidden = array('cmd' => 'search');
    $fields = array('search_string' => array('', 'isNoHTML', TRUE, 'input', 100));

    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $dialog->dialogTitle = $this->_gt('Search for tags');
    $dialog->loadParams();
    $dialog->buttonTitle = 'Search';
    $dialog->inputFieldSize = 'normal';
    return $dialog->getDialogXML();
  }

  /**
  * generate tag search result listview xml
  *
  * @param array $linkedTags list of linked tag ids
  * @return string $result tag search result listview XML
  */
  function getSearchResult($linkedTags) {
    $result = '';
    // get matching tags
    $tags = $this->searchTags(
      $this->params['search_string'],
      $this->params['limit'],
      $this->params['offset_tags'],
      empty($this->params['order']) ? '' : $this->params['order'],
      empty($this->params['sort']) ? '' : $this->params['sort']
    );
    if (count($tags) == 0 && $this->absCount > 0
        && $this->params['offset_tags'] > $this->absCount) {
      $this->params['offset_tags'] = (
        floor(($this->absCount - 1) / $this->params['limit'])
      ) * $this->params['limit'];
      $tags = $this->searchTags(
        $this->params['search_string'],
        $this->params['limit'],
        $this->params['offset_tags'],
        $this->params['order'],
        $this->params['sort']
      );
    }
    // search results don't belong to any category, prevents tags to be displayed
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
    $images = $this->papaya()->images;
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars(
        sprintf($this->_gt('Search result for \'%s\''), $this->params['search_string'])
      )
    );
    if (isset($tags) && is_array($tags) && count($tags) > 0) {
      // calculate sort links / images
      if (!isset($this->params['order'])) {
        $this->params['order'] = '';
      }
      $sortDirection = (isset($this->params['sort']) && 'asc' === (string)$this->params['sort'])
        ? 'desc' : 'asc';
      switch ($this->params['order']) {
      default:
      case 'tag':
        $tagSortLink = $this->getLink(
          array(
            'order' => 'tag',
            'sort' => $sortDirection
              ? 'desc' : 'asc',
            'cmd' => $this->params['cmd'],
          )
        );
        $tagSort = $sortDirection;
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
            'sort' => $sortDirection,
            'cmd' => $this->params['cmd'],
          )
        );
        $pathSort = $sortDirection;
        break;
      }

      $result .= $this->getTagsPagingBar($this->absCount);
      $result .= '<cols>'.LF;
      $result .= '<col></col>'.LF;
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
      $result .= '<col></col>'.LF;
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      foreach ($tags as $tag) {
        if (isset($linkedTags[$tag['tag_id']])) {
          $linkParams = array(
            'cmd' => 'deselect_tag',
            'tag_id' => $tag['tag_id'],
          );
          $tagIcon = $images['actions-tag-delete'];
          $tagHint = $this->_gt('Unlink tag');
        } else {
          $linkParams = array(
            'cmd' => 'link_tag',
            'tag_id' => $tag['tag_id'],
          );
          $tagIcon = $images['actions-tag-add'];
          $tagHint = $this->_gt('Link tag');
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
        $categoryPath .= sprintf(
          '<a href="%s">%s</a>',
          papaya_strings::escapeHTMLChars($this->getLink(array('cat_id' => $tag['category_id']))),
          papaya_strings::escapeHTMLChars($categories[$tag['category_id']]['category_title'])
        );
        $result .= sprintf(
          '<listitem image="%s">'.LF,
          papaya_strings::escapeHTMLChars(
            $this->papaya()->administrationLanguage->image
          )
        );
        $result .= sprintf(
          '<subitem align="left">%s (%d)</subitem>'.LF,
          papaya_strings::escapeHTMLChars($tag['tag_title']),
          (int)$tag['tag_id']
        );
        $result .= sprintf(
          '<subitem align="left">%s</subitem>'.LF,
          $categoryPath
        );
        $result .= sprintf(
          '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s" /></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($this->getLink($linkParams)),
          papaya_strings::escapeHTMLChars($tagIcon),
          papaya_strings::escapeHTMLChars($tagHint)
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
    return $result;
  }

  /* ---------------------------- TAG LINKING ------------------------------------ */

  /**
  * Get tag selector xml
  * @return string
  */
  function getTagSelectorXML() {
    $categoryTree = $this->getXMLCategoryTree();
    if (isset($this->selectedTags)) {
      $selectedTagsData = $this->getTags(
        array_keys($this->selectedTags),
        (int)$this->papaya()->administrationLanguage->id
      );
    } else {
      $this->selectedTags = array();
      $selectedTagsData = array();
    }

    // expand tags list to category list size, use a reasonable minimum size: 10
    if ($this->categoryTreeSize > 10 && $this->categoryTreeSize > $this->params['limit']) {
      $this->params['limit'] = $this->categoryTreeSize;
    }

    if (isset($this->params['cat_id'])) {
      $this->loadCategory();
      $tags = $this->getTagsByCategory(
        $this->params['cat_id'],
        $this->papaya()->administrationLanguage->id,
        $this->params['limit'],
        $this->params['offset_tags']
      );
      if ($this->absCount > 0 && (!is_array($tags) || count($tags) == 0 )) {
        $this->params['offset_tags'] =
          (floor(($this->absCount - 1) / $this->params['limit'])) * $this->params['limit'];
        $tags = $this->getTagsByCategory(
          $this->params['cat_id'],
          $this->papaya()->administrationLanguage->id,
          $this->params['limit'],
          $this->params['offset_tags']
        );
      }
    }
    $result = '';
    $result .= '<layout border="1">'.LF;
    $result .= '<row>'.LF;
    $result .= '<cell cols="3">'.LF;
    $result .= $this->getLinkedTagsListXML($selectedTagsData);
    $result .= '</cell>'.LF;
    $result .= '</row>'.LF;
    $result .= '<row>'.LF;
    $result .= '<cell width="50%">'.LF;
    $result .= $categoryTree;
    $result .= '</cell>'.LF;
    $result .= '<cell>&#160;'.LF;
    $result .= '</cell>'.LF;
    $result .= '<cell width="50%">'.LF;
    $result .= $this->getXMLSearchForm();
    if (isset($this->params['search_string'])) {
      $result .= $this->getSearchResult(empty($linkedTags) ? NULL : $linkedTags);
    } elseif (isset($tags) && is_array($tags) && count($tags) > 0) {
      $result .= $this->getAvailableTagsListXMLForSelector($tags, $this->selectedTags);
    }
    $result .= '</cell>'.LF;
    $result .= '</row>'.LF;
    $result .= '</layout>'.LF;
    return $result;
  }

  /**
  * Get available tag listview xml
  * @param array $tags
  * @param array $selectedTags
  * @return string
  */
  function getAvailableTagsListXMLForSelector($tags, $selectedTags) {
    $images = $this->papaya()->images;
    $result = sprintf(
      '<listview width="100%%" title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Tags'))
    );
    $result .= $this->getTagsPagingBar($this->absCount);
    $result .= '<items>'.LF;
    foreach ($tags[$this->params['cat_id']] as $tagId => $tag) {
      if (isset($selectedTags[$tagId])) {
        $link = $this->getLink(array('cmd' => 'deselect_tag', 'tag_id' => $tagId));
        $image = $images['actions-tag-delete'];
        $tagHint = $this->_gt('Unlink tag');
      } else {
        $link = $this->getLink(array('cmd' => 'select_tag', 'tag_id' => $tagId));
        $image = $images['actions-tag-add'];
        $tagHint = $this->_gt('Link tag');
      }
      $result .= sprintf(
        '<listitem image="%s" title="%s">'.LF,
        papaya_strings::escapeHTMLChars($images['items-tag']),
        papaya_strings::escapeHTMLChars($tag['tag_title'])
      );
      $result .= sprintf(
        '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s" /></a></subitem>'.LF,
        papaya_strings::escapeHTMLChars($link),
        papaya_strings::escapeHTMLChars($image),
        papaya_strings::escapeHTMLChars($tagHint)
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }
}

