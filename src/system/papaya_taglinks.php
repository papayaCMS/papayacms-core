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
* Tags Administration
*
* @package Papaya
* @subpackage Administration
*/
class papaya_taglinks extends base_tags {

  var $selectedTags = array();
  /**
  * @var string $tagType type of tag, identifier for taglinks, defaults to topic
  */
  var $tagType = 'topic';
  /**
  * @var integer|array $linkId id of $tagType item to be tagged
  */
  var $linkId = NULL;

  /**
  * linked tags
  * @var array
  */
  var $linkedTags = NULL;
  /**
  * linked tags record data
  * @var array
  */
  var $linkedTagsData = NULL;
  /**
  * tag records list
  * @var array
  */
  var $tags = NULL;

  /**
  * Switch to show/hide add tag action
  * @var boolean
  */
  var $showAddTag = FALSE;

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
  * Linking parameters
  * @var array
  */
  var $linkParams = array();

  /**
  * Escape link separator ('&amp;' instead of '&')?
  * @var boolean
  */
  var $escapeLinkSeparator = TRUE;

  /**
   * @var array
   */
  public $alternativeCategoryNames;

  /**
   * @var array
   */
  public $categoryTree;

  /**
   * @var array
   */
  public $linkedTagsMulti;

  /**
   * @var PapayaUiDialog
   */
  private $_dialogLinkPriority = NULL;

  /**
  * papaya 5 constructor
  */
  function __construct($parentObj, $paramName = 'tg') {
    parent::__construct();
    $this->paramName = $paramName;
    $this->parentObj = $parentObj;
  }

  /**
   * get an instance of base_taglinks
   *
   * @param object $parentObj parent object
   * @param string $paramName
   * @return object $tagLinks instance of base_taglinks
   */
  public static function getInstance($parentObj = NULL, $paramName = NULL) {
    /** @var PapayaApplicationCms $application */
    $application = PapayaApplication::getInstance();
    $validUser = $application->administrationUser->hasPerm(
      PapayaAdministrationPermissions::TAG_MANAGE
    );
    if ($validUser && isset($parentObj) && is_object($parentObj)) {
      if ($paramName == NULL) {
        $paramName = $parentObj->paramName;
      }
      $instance = new papaya_taglinks($parentObj, $paramName);
      return $instance;
    }
    return NULL;
  }

  /**
  * Prepare Tag Linker setting target data
  *
  * @param string $linkType
  * @param integer $linkId
  * @param boolean $showAddTag
  * @return boolean
  */
  public function prepare($linkType, $linkId, $showAddTag = FALSE) {
    if ($linkType != '' && $linkId != '') {
      $this->tagType = $linkType;
      $this->linkId = $linkId;
      $this->showAddTag = $showAddTag;
      $this->initialize();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Get xml to add tag linker to layout.
  *
  * @return string
  */
  public function getXml() {
    $this->loadCategories();
    $this->checkSelectedCategory();
    return $this->getTagLinkingXML();
  }

  /**
   * generate tag linking listview
   *
   * @param string $linkType type of link, used as papaya_tag_links.link_type, e.g. 'topic'
   * @param string $linkId item to be linked, e.g. topic id, media item id, ...
   * @param bool $showAddTag
   * @return string $result tag selection listview(s)
   */
  function getTagLinker($linkType, $linkId, $showAddTag = FALSE) {
    if ($this->prepare($linkType, $linkId, $showAddTag)) {
      $this->execute();
      return $this->getXml();
    }
    return '';
  }

  /**
  * initialize language selector and session
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.get_class($this).'_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('cat_id');
    $this->initializeSessionParam('open_categories');
    $this->initializeSessionParam('limit');
    $this->initializeSessionParam('offset_tags');

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
      $this->loadTagLinksData();
      switch($this->params['cmd']) {
      case 'link_tag':
        if (isset($this->params['tag_id']) && $this->params['tag_id'] > 0) {
          if ($this->linkTag($this->tagType, $this->linkId, $this->params['tag_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Tag linked.'));
            return TRUE;
          } else {
            $this->addMsg(MSG_WARNING, $this->_gt('Tag couldn`t be linked.'));
            return FALSE;
          }
        }
        break;
      case 'unlink_tag':
        if (isset($this->params['tag_id']) && $this->params['tag_id'] > 0) {
          if ($this->unlinkTag($this->tagType, $this->linkId, $this->params['tag_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Tag unlinked.'));
            return TRUE;
          } else {
            $this->addMsg(MSG_WARNING, $this->_gt('Tag couldn`t be unlinked.'));
            return FALSE;
          }
        }
        break;
      case 'prioritize_tag':
        $dialog = $this->getLinkPriorityDialog();
        if ($dialog->execute()) {
          $tagId = $dialog->data()->get('tag_id', 0);
          $priority = $dialog->data()->get('taglink_priority', 50);
          if ($this->saveTagPriority($this->tagType, $this->linkId, $tagId, $priority)) {
            $this->addMsg(MSG_INFO, $this->_gt('Priority changed.'));
            return TRUE;
          }
        }
        break;
      case 'add_tag':
        if (isset($this->params['tag_name']) && $this->params['tag_name'] != '') {
          return $this->addTag($this->params['tag_name']);
        }
        break;
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
      }
    }
    return NULL;
  }

  /**
  * generate xml for tag linking, i.e. category tree, linked tags, available tags, search
  *
  * @return string $result tag linking xml
  */
  function getTagLinkingXML() {
    $categoryTree = $this->getXMLCategoryTree();
    $this->loadTagLinksData();

    $result = '';
    $result .= '<layout border="1">'.LF;
    $result .= '<row>'.LF;
    $result .= '<cell cols="3">'.LF;
    if ($this->papaya()->administrationUser->hasPerm(PapayaAdministrationPermissions::TAG_LINK) &&
        isset($this->params['cmd']) &&
        $this->params['cmd'] == 'prioritize_tag' &&
        !empty($this->linkId)) {
      $result .= $this->getLinkPriorityDialog()->getXml();
    }
    $result .= $this->getLinkedTagsListXML();
    $result .= '</cell>'.LF;
    $result .= '</row>'.LF;
    if ($this->papaya()->administrationUser->hasPerm(PapayaAdministrationPermissions::TAG_LINK)) {
      $result .= '<row>'.LF;
      $result .= '<cell width="50%">'.LF;
      $result .= $categoryTree;
      $result .= '</cell>'.LF;
      $result .= '<cell>&#160;</cell>'.LF;
      $result .= '<cell width="50%">'.LF;
      $result .= $this->getXMLSearchForm();
      if (isset($this->params['search_string'])) {
        $result .= $this->getSearchResult();
      } elseif (isset($this->tags) && is_array($this->tags) && count($this->tags) > 0) {
        $result .= $this->getAvailableTagsListXML();
      }
      $result .= '</cell>'.LF;
      $result .= '</row>'.LF;
      if ($this->showAddTag) {
        if (isset($this->params['cat_id']) && isset($this->categories[$this->params['cat_id']])) {
          $permissions = $this->calculatePermissions($this->categories[$this->params['cat_id']]);
          foreach ($this->papaya()->administrationUser->user['groups'] as $groupId) {
            if (isset($permissions['user_edit_tag'][$groupId])) {
              $result .= '<row>'.LF;
              $result .= '<cell cols="3">'.LF;
              $result .= $this->getAddTagDialog();
              $result .= '</cell>'.LF;
              $result .= '</row>'.LF;
              break;
            }
          }
        }
      }
    }
    $result .= '</layout>'.LF;
    return $result;
  }

  /**
  * load data about linked tags
  * @return void
  */
  function loadTagLinksData() {
    if (isset($this->linkId) && !is_array($this->linkId) && $this->linkId != '' &&
        isset($this->tagType)) {
      $this->linkedTags = $this->getLinkedTags($this->tagType, $this->linkId);
      $this->linkedTagsData = $this->getTags(
        $this->linkedTags, $this->papaya()->administrationLanguage->id
      );
    } elseif (isset($this->linkId) && is_array($this->linkId) && count($this->linkId) > 0) {
      $linkedTagsMulti = $this->getLinkedTagsForIds($this->tagType, $this->linkId);
      /*
      * we need to translate the array,
      * since the output of getLinkedTagsForIds was changed, and that's okay
      */
      foreach ($linkedTagsMulti as $linkId => $tagIds) {
        foreach ($tagIds as $tagId) {
          $this->linkedTagsMulti[$tagId][$linkId] = $tagId;
        }
      }
      if (is_array($linkedTagsMulti) && count($linkedTagsMulti) > 0) {
        $this->linkedTags[] = array_keys($this->linkedTagsMulti);
        $this->linkedTagsData = $this->getTags(
          $this->linkedTags, $this->papaya()->administrationLanguage->id
        );
      }
    }

    // expand tags list to category list size, use a reasonable minimum size: 10
    if ($this->categoryTreeSize > 10 && $this->categoryTreeSize > $this->params['limit']) {
      $this->params['limit'] = $this->categoryTreeSize;
    }

    if (isset($this->params['cat_id'])) {
      $this->loadCategory();
      $this->tags = $this->getTagsByCategory(
        $this->params['cat_id'],
        $this->papaya()->administrationLanguage->id,
        $this->params['limit'],
        $this->params['offset_tags']
      );
      if ($this->absCount > 0 && (!is_array($this->tags) || count($this->tags) == 0 )) {
        $this->params['offset_tags'] =
          (floor(($this->absCount - 1) / $this->params['limit'])) * $this->params['limit'];
        $this->tags = $this->getTagsByCategory(
          $this->params['cat_id'],
          $this->papaya()->administrationLanguage->id,
          $this->params['limit'],
          $this->params['offset_tags']
        );
      }
    }
  }

  /**
  * generate listview of available tags in the currently selected category
  *
  * @return string $result tags listview xml
  */
  function getAvailableTagsListXML() {
    $result = sprintf(
      '<listview title="%s" id="tags_available">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Tags'))
    );
    $result .= $this->getTagsPagingBar($this->absCount);
    $result .= '<items>'.LF;
    $alternativeAvailableTags = array();
    $images = $this->papaya()->images;
    foreach ($this->tags[$this->params['cat_id']] as $tagId => $tag) {
      $title = $this->getTagTitle(
        $alternativeAvailableTags,
        $tag,
        $this->tags[$this->params['cat_id']]
      );
      if (isset($this->linkId) && is_array($this->linkId)) {
        if (!isset($this->linkedTagsMulti[$tagId])) {
          $link = $this->getLink(array('cmd' => 'link_tag', 'tag_id' => $tagId));
          $image = $images['actions-list-add'];
          $hint = $this->_gt('not linked, link for all');
          $stateImg = $images['items-tag'];
        } elseif (count($this->linkedTagsMulti[$tagId]) == count($this->linkId)) {
          $link = $this->getLink(array('cmd' => 'unlink_tag', 'tag_id' => $tagId));
          $image = $images['actions-list-remove'];
          $hint = $this->_gt('linked for all, unlink');
          $stateImg = $images['status-tag-linked'];
        } else {
          $link = $this->getLink(array('cmd' => 'link_tag', 'tag_id' => $tagId));
          $image = $images['actions-list-add'];
          $hint = $this->_gt('partially linked, link for all');
          $stateImg = $images['status-tag-linked'];
        }
      } elseif (isset($this->linkedTags[$tagId])) {
        $link = $this->getLink(array('cmd' => 'unlink_tag', 'tag_id' => $tagId));
        $image = $images['actions-list-remove'];
        $hint = $this->_gt('unlink tag');
          $stateImg = $images['status-tag-linked'];
      } else {
        $link = $this->getLink(array('cmd' => 'link_tag', 'tag_id' => $tagId));
        $image = $images['actions-list-add'];
        $hint = $this->_gt('link tag');
        $stateImg = $images['items-tag'];
      }

      $result .= sprintf(
        '<listitem image="%s" title="%s" id="tags_available_%d">'.LF,
        papaya_strings::escapeHTMLChars($stateImg),
        papaya_strings::escapeHTMLChars($title),
        (int)$tagId
      );
      $result .= sprintf(
        '<subitem align="right"><a href="%s"><glyph hint="%s" src="%s" /></a></subitem>'.LF,
        papaya_strings::escapeHTMLChars($link.'#tags_available_'.((int)$tagId)),
        papaya_strings::escapeHTMLChars($hint),
        papaya_strings::escapeHTMLChars($image)
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Generates listview of linked tags including their category path
  *
  * @return string $result tags listview xml
  */
  function getLinkedTagsListXML() {
    $result = '';

    $linkCatIds = array();
    if (is_array($this->linkedTagsData) && count($this->linkedTagsData) > 0) {
      foreach ($this->linkedTagsData as $linkedTag) {
        if (!isset($this->categories[$linkedTag['category_id']])) {
          $linkCatIds[$linkedTag['category_id']] = $linkedTag['category_id'];
        }
      }
    }
    $linkCategories = $this->getCategories(
      $linkCatIds, $this->papaya()->administrationLanguage->id
    );
    $allParents = array();
    foreach ($linkCategories as $linkCategory) {
      $parents = explode(';', $linkCategory['parent_path']);
      foreach ($parents as $parentId) {
        $allParents[$parentId] = $parentId;
      }
      $allParents[$linkCategory['category_id']] = $linkCategory['category_id'];
    }
    foreach ($allParents as $key => $catId) {
      if ($catId <= 0 || isset($this->categories[$catId])) {
        unset($allParents[$key]);
      }
    }
    $moreCategories = $this->getCategories(
      $allParents, $this->papaya()->administrationLanguage->id
    );
    foreach ($moreCategories as $category) {
      $this->categories[$category['category_id']] = $category;
    }

    if (is_array($this->linkedTagsData) && count($this->linkedTagsData) > 0) {
      $result .= sprintf(
        '<listview title="%s" id="tags_linked">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Linked tags'))
      );

      $result .= '<items>'.LF;
      $alternativeLinkedTags = array();
      $images = $this->papaya()->images;
      foreach ($this->linkedTagsData as $tagId => $tag) {
        $title = $this->getTagTitle($alternativeLinkedTags, $tag, $this->linkedTags);
        $priority = $this->linkedTags[$tagId]['link_priority'];
        $fragment = '#tags_linked';
        $image = '';
        $hint = '';
        if (
            $this->papaya()->administrationUser->hasPerm(
              PapayaAdministrationPermissions::TAG_LINK
            )
           ) {
          $link = $this->getLink(array('cmd' => 'unlink_tag', 'tag_id' => $tagId));
          $image = $images['actions-list-remove'];

          if (is_array($this->linkId)) {
            if (count($this->linkedTagsMulti[$tagId]) == count($this->linkId)) {
              $hint = $this->_gt('linked for all, unlink');
              $stateImg = $images['status-tag-linked'];
            } else {
              $hint = $this->_gt('partially linked, unlink all');
              $stateImg = $images['status-tag-linked'];
            }
            $result .= sprintf(
              '<subitem align="right"><glyph src="%s" /></subitem>'.LF,
              papaya_strings::escapeHTMLChars($stateImg)
            );
          } else {
            $stateImg = $images['status-tag-linked'];
            $hint = $this->_gt('unlink tag');
          }
        } else {
          $stateImg = $images['items-tag'];
          $link = NULL;
        }

        $result .= sprintf(
          '<listitem image="%s" title="%s">'.LF,
          papaya_strings::escapeHTMLChars($stateImg),
          papaya_strings::escapeHTMLChars($title),
          (int)$tagId
        );

        if ($priority !== FALSE) {
          $result .= sprintf(
            '<subitem align="center"><a href="%s">%s%%</a></subitem>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('cmd' => 'prioritize_tag', 'tag_id' => $tagId))
            ),
            papaya_strings::escapeHTMLChars($priority)
          );
        } else {
          $result .= '<subitem/>';
        }

        $categoryPath = $this->getCategoryPath($tag['category_id']);
        $result .= sprintf(
          '<subitem>%s</subitem>'.LF,
          $categoryPath
        );

        if (isset($link)) {
          $result .= sprintf(
            '<subitem align="right"><a href="%s"><glyph hint="%s" src="%s" /></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($link.$fragment),
            papaya_strings::escapeHTMLChars($hint),
            papaya_strings::escapeHTMLChars($image)
          );
        }

        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * Gets tag title for tags in available or linked tags list
  *
  * @param array $alternativeTags alternative tags (available or linked)
  * @param array $currentTag current tag in list
  * @param array $otherTags other tags in list
  * @access public
  * @return string $title
  */
  function getTagTitle($alternativeTags, $currentTag, $otherTags) {
    $languageId = $this->papaya()->administrationLanguage->id;
    if ($currentTag['tag_title'] != '') {
      $title = $currentTag['tag_title'];
    } elseif ($this->papaya()->options['PAPAYA_CONTENT_LANGUAGE'] != $languageId) {
      if (!(
            isset($alternativeTags) &&
            is_array($alternativeTags) &&
            count($alternativeTags) > 0 &&
            isset($otherTags) &&
            is_array($otherTags) &&
            count($otherTags) > 0
          )) {
        $alternativeTags = $this->getTags(
          array_keys($otherTags), $this->papaya()->options['PAPAYA_CONTENT_LANGUAGE']
        );
      }
      if (isset($alternativeTags[$currentTag['tag_id']]) &&
          isset($alternativeTags[$currentTag['tag_id']]['tag_title']) &&
          $alternativeTags[$currentTag['tag_id']]['tag_title'] != '') {
        $title = '['.$alternativeTags[$currentTag['tag_id']]['tag_title'].']';
      } else {
        $title = $this->_gt('No title');
      }
    } else {
      $title = $this->_gt('No title');
    }
    return $title;
  }

  /**
  * Generates categories tree xml
  *
  * @return string $result listview xml
  */
  function getXMLCategoryTree() {
    $result = sprintf(
      '<listview width="100%%" title="%s" id="tag_categories">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Categories'))
    );
    $result .= '<items>'.LF;
    if (isset($this->params) && isset($this->params['cat_id'])) {
      $selected = ($this->params['cat_id'] == 0) ? ' selected="selected"' : '';
    } else {
      $selected = '';
    }
    $result .= sprintf(
      '<listitem href="%s" title="%s" image="%s" %s/>'.LF,
      papaya_strings::escapeHTMLChars($this->getLink(array('cat_id' => 0)).'#tag_categories'),
      papaya_strings::escapeHTMLChars($this->_gt('Base')),
      papaya_strings::escapeHTMLChars($this->papaya()->images['places-desktop']),
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
  * Generates subtree of categories
  *
  * @param integer $parent category id
  * @param integer $indent of category tree
  * @access public
  * @return string $result
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
  * Generates category tree listview line
  *
  * @param integer $id category id
  * @param integer $indent of category
  * @access public
  * @return string $result as xml
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
      $fragment = '#tag_categories_'.((int)$id);
      if (empty($this->categories[$id]['CATEG_COUNT']) ||
          $this->categories[$id]['CATEG_COUNT'] < 1) {
        $node = ' node="empty"';
      } elseif ($opened) {
        $nodeHref = $this->getLink(array('cmd' => 'close', 'cat_close_id' => (int)$id)).$fragment;
        $node = sprintf(' node="open" nhref="%s"', $nodeHref);
      } else {
        $nodeHref = $this->getLink(array('cmd' => 'open', 'cat_open_id' => (int)$id)).$fragment;
        $node = sprintf(' node="close" nhref="%s"', $nodeHref);
      }

      if (isset($this->params) && isset($this->params['cat_id'])) {
        $selected = ($this->params['cat_id'] == $id) ? ' selected="selected"' : '';
      } else {
        $selected = '';
      }
      if (isset($this->params) && isset($this->params['cat_id']) &&
          $this->params['cat_id'] == $id) {
        $imageIndex = 'status-folder-open';
      } else {
        $imageIndex = 'items-folder';
      }
      $result .= sprintf(
        '<listitem href="%s" title="%s" indent="%d" image="%s" id="tag_categories_%d" %s %s/>'.LF,
        papaya_strings::escapeHTMLChars($this->getLink(array('cat_id' => (int)$id)).$fragment),
        papaya_strings::escapeHTMLChars($this->getCategoryTitle($id)),
        (int)$indent,
        papaya_strings::escapeHTMLChars($this->papaya()->images[$imageIndex]),
        (int)$id,
        $node,
        $selected
      );
      $result .= $this->getXMLCategorySubTree($id, $indent + 1);
    }
    return $result;
  }

  /**
  * Generates category path for a given category
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
          if (!isset($this->alternativeCategoryNames[$catId])) {
            $this->loadAlternativeCategoryNames(array($catId));
          }
          if (
              $this->papaya()->administrationUser->hasPerm(
                PapayaAdministrationPermissions::TAG_LINK
              )
             ) {
            $result .= sprintf(
              '<a href="%s">%s</a> / ',
              papaya_strings::escapeHTMLChars($this->getLink(array('cat_id' => $catId))),
              papaya_strings::escapeHTMLChars($this->getCategoryTitle($catId))
            );
          } else {
            $result .= papaya_strings::escapeHTMLChars($this->getCategoryTitle($catId)).' / ';
          }
        }
      }
      if (!isset($this->alternativeCategoryNames[$categoryId])) {
        $this->loadAlternativeCategoryNames(array($categoryId));
      }
      if ($this->papaya()->administrationUser->hasPerm(PapayaAdministrationPermissions::TAG_LINK)) {
        $result .= sprintf(
          '<a href="%s">%s</a>',
          papaya_strings::escapeHTMLChars($this->getLink(array('cat_id' => $categoryId))),
          papaya_strings::escapeHTMLChars($this->getCategoryTitle($categoryId))
        );
      } else {
        $result .= papaya_strings::escapeHTMLChars($this->getCategoryTitle($categoryId));
      }
    }
    return $result;
  }

  /**
  * Gets title, alternative title or placeholder for category by id
  *
  * @param integer $categoryId id of category
  * @access public
  * @return string $title
  */
  function getCategoryTitle($categoryId) {
    if (!isset($this->categories[$categoryId]) ||
        !isset($this->categories[$categoryId]['category_title']) ||
      $this->categories[$categoryId]['category_title'] == "") {
      if (isset($this->alternativeCategoryNames) &&
          is_array($this->alternativeCategoryNames) &&
          isset($this->alternativeCategoryNames[$categoryId])) {
        $title = '['.$this->alternativeCategoryNames[$categoryId].']';
      } else {
        $title = $this->_gt('No Title');
      }
    } else {
      $title = $this->categories[$categoryId]['category_title'];
    }
    return $title;
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
      $this->papaya()->administrationUser, array('user_use_tags')
    );
    if (is_array($availableCategories) && count($availableCategories) > 0) {
      $categoryCondition = ' AND '.
        $this->databaseGetSQLCondition('c.category_id', $availableCategories);
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
  * Load category titles for currently selected ui language if different from content language
  * @param array $categoryIds
  * @return void
  */
  function loadAlternativeCategoryNames($categoryIds) {
    $languageId = $this->papaya()->administrationLanguage->id;
    if ($this->papaya()->options['PAPAYA_CONTENT_LANGUAGE'] != $languageId &&
        isset($categoryIds) && is_array($categoryIds) &&
        count($categoryIds) > 0) {
      $categoryCondition = $this->databaseGetSQLCondition('category_id', $categoryIds);
      $sql = "SELECT category_id, category_title
                FROM %s
              WHERE lng_id = %d
                AND $categoryCondition";
      $params = array(
        $this->tableTagCategoryTrans,
        $this->papaya()->options['PAPAYA_CONTENT_LANGUAGE']
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->alternativeCategoryNames[$row['category_id']] = $row['category_title'];
        }
      }
    }
  }

  /**
   * load current category in current language (fills $this->category)
   *
   * @return array|NULL
   */
  function loadCategory() {
    return $this->getCategory(
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
    $steps = isset($this->params['limit']) && $this->params['limit'] > 10
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
  * Get add tag dialog xml
  * @return string
  */
  function getAddTagDialog() {
    $hidden['cmd'] = 'add_tag';

    $fields = array('tag_name' => array('Tagname', 'isNoHTML', TRUE, 'input', 100));

    $data = array();
    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $dialog->dialogTitle = $this->_gt('Add a tag');
    $dialog->buttonTitle = 'add';
    $dialog->inputFieldSize = 'normal';
    $dialog->baseLink = $this->getLink(array());

    return $dialog->getDialogXML();
  }

  /**
   * Add a tag record
   * @param string $tagName
   *
   * @return bool|mixed
   */
  function addTag($tagName) {
    $data = array(
      'category_id' => $this->params['cat_id'],
      'default_lng_id' => $this->papaya()->administrationLanguage->id,
      'creator_type' => 'admin',
      'creator_id' => $this->papaya()->administrationUser->userId,
      'creation_time' => time(),
    );
    if ($tagId = $this->databaseInsertRecord($this->tableTag, 'tag_id', $data)) {
      $dataTrans[] = array(
        'tag_id' => $tagId,
        'tag_title' => $tagName,
        'lng_id' => $this->papaya()->administrationLanguage->id,
      );
      if ($this->databaseInsertRecords($this->tableTagTrans, $dataTrans)) {
        $this->addMsg(
          MSG_INFO,
          sprintf(
            $this->_gt('New tag "%s" (%d) has been added.'),
            $this->params['tag_name'],
            $tagId
          )
        );
        $this->linkTag($this->tagType, $this->linkId, $tagId);
        return TRUE;
      }
    }
    return FALSE;
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
    $hidden['cmd'] = 'search';
    $fields = array('search_string' => array('', 'isNoHTML', TRUE, 'input', 100));

    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $dialog->dialogTitle = $this->_gt('Search for tags');
    $dialog->loadParams();
    $dialog->buttonTitle = 'Search';
    $dialog->inputFieldSize = 'normal';
    $dialog->baseLink = $this->getLink(array());

    return $dialog->getDialogXML();
  }

  /**
  * generate tag search result listview xml
  *
  * @return string $result tag search result listview XML
  */
  function getSearchResult() {
    $result = '';
    if (empty($this->params['order'])) {
      $this->params['order'] = '';
    }
    if (empty($this->params['sort'])) {
      $this->params['sort'] = 'asc';
    }
    // get matching tags
    $tags = $this->searchTags(
      $this->params['search_string'],
      $this->params['limit'],
      $this->params['offset_tags'],
      $this->params['order'],
      $this->params['sort']
    );
    if (count($tags) == 0 && $this->absCount > 0 &&
        $this->params['offset_tags'] > $this->absCount) {
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
    $categories = $this->getCategories($catIds, $this->papaya()->administrationLanguage->id);

    $result .= sprintf(
      '<listview title="%s" id="tags_available">'.LF,
      papaya_strings::escapeHTMLChars(
        sprintf($this->_gt('Search result for \'%s\''), $this->params['search_string'])
      )
    );
    if (isset($tags) && is_array($tags) && count($tags) > 0) {
      // calculate sort links / images
      switch ($this->params['order']) {
      default:
      case 'tag':
        $tagSortLink = $this->getLink(
          array(
            'order' => 'tag',
            'sort' => (isset($this->params['sort']) && $this->params['sort'] == 'asc')
              ? 'desc' : 'asc',
            'cmd' => $this->params['cmd'],
          )
        );
        $tagSort = (isset($this->params['sort']) && $this->params['sort'] == 'asc')
          ? 'desc' : 'asc';
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
            'sort' => (isset($this->params['sort']) && $this->params['sort'] == 'asc')
            ? 'desc' : 'asc',
            'cmd' => $this->params['cmd'],
          )
        );
        $pathSort = (isset($this->params['sort']) && $this->params['sort'] == 'asc')
          ? 'desc' : 'asc';
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
      $result .= '<col></col>'.LF;
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      $images = $this->papaya()->images;
      foreach ($tags as $tag) {
        $fragment = 'tag_available_'.((int)$tag['tag_id']);
        if (isset($this->linkedTags[$tag['tag_id']])) {
          $linkParams = array(
            'cmd' => 'unlink_tag',
            'tag_id' => $tag['tag_id'],
          );
          $tagIcon = $images['actions-list-remove'];
        } else {
          $linkParams = array(
            'cmd' => 'link_tag',
            'tag_id' => $tag['tag_id'],
          );
          $tagIcon = $images['actions-list-add'];
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
          '<listitem image="./pics/language/%s" title="%s (%d)" id="tags_available_%d">'.LF,
          papaya_strings::escapeHTMLChars(
            $this->papaya()->languages[$tag['lng_id']]['image']
          ),
          papaya_strings::escapeHTMLChars($tag['tag_title']),
          (int)$tag['tag_id'],
          (int)$tag['tag_id']
        );
        $result .= sprintf(
          '<subitem align="left">%s</subitem>'.LF, $categoryPath
        );
        $result .= sprintf(
          '<subitem align="right"><a href="%s"><glyph src="%s" /></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($this->getLink($linkParams).$fragment),
          papaya_strings::escapeHTMLChars($tagIcon)
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

  public function getLinkPriorityDialog() {
    if (!($this->_dialogLinkPriority instanceof PapayaUiDialog)) {
      $tagId = empty($this->params['tag_id']) ? 0 : $this->params['tag_id'];
      $this->_dialogLinkPriority = $dialog = new PapayaUiDialog();
      $dialog->caption = new PapayaUiStringTranslated('Edit priority');
      $dialog->parameterGroup($this->paramName);
      $dialog->hiddenFields()->merge(
        array(
          'cmd' => 'prioritize_tag',
          'tag_id' => $tagId
        )
      );
      $dialog->fields[] = $field = new PapayaUiDialogFieldSelect(
        new PapayaUiStringTranslated('Priority'),
        'taglink_priority',
        new PapayaIteratorRepeatDecrement(100, 0, 10, PapayaIteratorRepeatDecrement::MODE_ASSOC)
      );
      $field->callbacks()->getOptionCaption = array($this, 'callbackFormatPriority');
      $dialog->buttons[] = new PapayaUiDialogButtonSubmit(new PapayaUiStringTranslated('Save'));
      if (isset($this->linkedTags[$tagId])) {
        $dialog->data()->set(
          'taglink_priority', $this->linkedTags[$tagId]['link_priority']
        );
      }
    }
    return $this->_dialogLinkPriority;
  }

  public function callbackFormatPriority(
    /** @noinspection PhpUnusedParameterInspection */
    $context,
    $priority
  ) {
    return $priority.'%';
  }

  /**
  * Set link parameters
  * @param string $paramName
  * @param array $params
  * @return void
  */
  function setLinkParams($paramName, $params) {
    if (is_array($params)) {
      $this->linkParams[$paramName] = $params;
    }
  }

  /**
   * Get link
   * @param array $params
   * @param string $paramName
   *
   * @return string
   */
  function getLink($params, $paramName = NULL) {
    if (empty($paramName)) {
      $paramName = $this->paramName;
    }
    if (is_array($this->linkParams) && count($this->linkParams) > 0) {
      if (is_array($params) && count($params) > 0) {
        $queryString = $this->encodeQueryString($params, $paramName);
        if (empty($queryString)) {
          $queryString = $this->encodeQueryString($this->linkParams);
        } else {
          $linkSeparator = $this->escapeLinkSeparator ? '&amp;' : '&';
          $queryString .= $linkSeparator
            .substr($this->encodeQueryString($this->linkParams), 1);
        }
        return $this->getBaseLink().$queryString;
      } else {
        return parent::getLink($this->linkParams);
      }
    } else {
      return parent::getLink($params, $paramName);
    }
  }
}

