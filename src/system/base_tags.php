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
* Tags basic object
* @package Papaya
* @subpackage Core
*/
class base_tags extends base_db {

  /**
  * Categories table name
  * @var string
  */
  var $tableTagCategory = PAPAYA_DB_TBL_TAG_CATEGORY;
  /**
  * Category translation table name
  * @var string
  */
  var $tableTagCategoryTrans = PAPAYA_DB_TBL_TAG_CATEGORY_TRANS;
  /**
  * Category permissions table name
  * @var string
  */
  var $tableTagCategoryPermissions = PAPAYA_DB_TBL_TAG_CATEGORY_PERMISSIONS;
  /**
  * Tags table name
  * @var string
  */
  var $tableTag = PAPAYA_DB_TBL_TAG;
  /**
  * Tag translations table name
  * @var string
  */
  var $tableTagTrans = PAPAYA_DB_TBL_TAG_TRANS;
  /**
  * Tag links table name
  * @var string
  */
  var $tableTagLinks = PAPAYA_DB_TBL_TAG_LINKS;

  /**
   * @var integer $absCount
   */
  public $absCount;

  protected $categories;

  /**
   * Create/return single object instance (Singleton)
   * @param null $parentObj
   * @return base_tags
   */
  public static function getInstance($parentObj = NULL) {
    static $tagObj;
    if (!(isset($tagObj) && is_object($tagObj) && is_a($tagObj, 'base_tags'))) {
      $tagObj = new base_tags();
    }
    return $tagObj;
  }

  /**
  * This method adds a category to the category tree
  *
  * @param integer $parentCategoryId the parent category of the to be created category
  * @param string $permissionMode the permission mode for this category 'inherited',
  *               'own', 'additional'
  * @param string $userType the user type 'admin' or 'surfer'
  * @param string $userId the user id of the previously defined type
  * @param array $translations list of translations for this category, e.g.
  * <code>
  *   array(
  *     1 => array(
  *       'category_title' => 'Meine Kategorie',
  *       'category_description' => 'Das ist eine Kategorie fuer dieses und jenes'
  *     ),
  *     2 => array('category_title' => 'my category')
  *   );
  * </code>
  * @return mixed the id of the newly created category on success, FALSE if it failed
  */
  function addCategory(
    $parentCategoryId, $permissionMode, $userType, $userId, $translations
  ) {
    if ($parentCategoryId != 0) {
      $parentCategory = $this->getCategory($parentCategoryId);
      $parentCategory = current($parentCategory);
      $parentPath = $parentCategory['parent_path'].$parentCategoryId.';';
    } else {
      $parentPath = ';0;';
    }
    $data = array(
      'parent_id' => $parentCategoryId,
      'parent_path' => $parentPath,
      'permission_mode' => $permissionMode,
      'creator_type' => $userType,
      'creator_id' => $userId,
      'creation_time' => time(),
    );
    $catId = $this->databaseInsertRecord(
      $this->tableTagCategory, 'category_id', $data
    );
    if ($catId) {
      $dataTrans = array();
      foreach ($translations as $lngId => $lngData) {
        $dataTrans[] = array(
          'category_id' => $catId,
          'category_title' => $lngData['category_title'],
          'category_description' =>
            empty($lngData['category_description']) ? '' : (string)$lngData['category_description'],
          'lng_id' => $lngId,
        );
      }
      if ($this->databaseInsertRecords($this->tableTagCategoryTrans, $dataTrans)) {
        $this->addMsg(
          MSG_INFO,
          sprintf(
            $this->_gt('New category "%s" (%d) has been added.'),
            empty($lngData['category_title']) ? '' : (string)$lngData['category_title'],
            $catId
          )
        );
        return $catId;
      }
    }
    return FALSE;
  }

  /**
  * This method deletes a category, all subcategories and tags, so be careful!
  *
  * @param integer $categoryId
   * @return bool
   */
  function deleteCategory($categoryId) {
    // first lets get all subcategories for the given category
    $subCategories = $this->getAllSubCategoryIds($categoryId);
    // add the actual id as well
    $subCategories[] = $categoryId;
    // put this together to form an SQL condition
    $categoryCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('category_id', $subCategories)
    );
    // get all tag ids that are in any of the just determined categories
    $tagSQL = "SELECT tag_id
                 FROM %s
                WHERE $categoryCondition";
    if ($res = $this->databaseQueryFmt($tagSQL, array($this->tableTag))) {
      while ($tagId = $res->fetchField()) {
        $tags[] = $tagId;
      }
    }
    // if any tags were found, delete any references to them, any translations
    // and the tags themselves
    if (isset($tags) && is_array($tags) && count($tags) > 0) {
      $tagCondition = array('tag_id' => $subCategories);
      if (!(
            FALSE !== $this->databaseDeleteRecord($this->tableTagLinks, $tagCondition) &&
            FALSE !== $this->databaseDeleteRecord($this->tableTagTrans, $tagCondition) &&
            FALSE !== $this->databaseDeleteRecord($this->tableTag, $tagCondition)
          )) {
        return FALSE;
      }
    }
    $condition = array('category_id' => $subCategories);
    if (FALSE !== $this->databaseDeleteRecord($this->tableTagCategoryPermissions, $condition) &&
        FALSE !== $this->databaseDeleteRecord($this->tableTagCategoryTrans, $condition) &&
        FALSE !== $this->databaseDeleteRecord($this->tableTagCategory, $condition)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * load tag categories for given languageId
  *
  * @param array $categoryIds list of category ids
  * @param integer $lngId language id
  * @return array $result list of categories
  */
  function getCategories($categoryIds, $lngId) {
    $result = array();
    $lngCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('ct.lng_id', $lngId)
    );
    $categoryCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('c.category_id', $categoryIds)
    );
    $sql = "SELECT c.category_id, c.parent_id, c.parent_path, 
                   c.category_name, c.permission_mode,
                   ct.category_title, ct.category_description, ct.lng_id
              FROM %s c
              LEFT OUTER JOIN %s ct
                   ON (c.category_id = ct.category_id AND $lngCondition)
             WHERE $categoryCondition
             ORDER BY c.parent_path ASC";
    $params = array($this->tableTagCategory, $this->tableTagCategoryTrans);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['category_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * This method loads all direct subcategories for a given category id
  *
  * @param integer|array $categoryId a category id or an list of ids
  * @param integer $lngId the language id to load the subcategory data for
  * @return array $result list of subcategories with category data
  */
  function getSubCategories($categoryId, $lngId) {
    $result = array();
    $lngCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('ct.lng_id', $lngId)
    );
    $categoryCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('c.parent_id', $categoryId)
    );
    $sql = "SELECT c.category_id, c.parent_id, c.parent_path, 
                   c.category_name, c.permission_mode,
                   ct.category_title, ct.category_description, ct.lng_id
              FROM %s c
              LEFT OUTER JOIN %s ct
                   ON (c.category_id = ct.category_id AND $lngCondition)
             WHERE $categoryCondition
             ORDER BY c.parent_path ASC, category_title ASC";
    $params = array($this->tableTagCategory, $this->tableTagCategoryTrans);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['category_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * This method fetches the ids for all subcategories of a given category
  *
  * This is used when deleting a complete category with all subcategories and tags
  *
  * @param integer $categoryId a category id
  * @return array $result list of category ids that are below the given id
  */
  function getAllSubCategoryIds($categoryId) {
    $result = FALSE;
    $category = $this->getCategory($categoryId);
    $category = current($category);
    $sql = "SELECT category_id
              FROM %s
             WHERE parent_path LIKE '%s%%'";
    $params = array($this->tableTagCategory, $category['parent_path'].$categoryId.';');
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $result = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['category_id']] = $row['category_id'];
      }
    }
    return $result;
  }

  /**
  * Extend a given category list with child category counts
  * @param array &$categories
  * @return boolean
  */
  function loadCategoryCounts(&$categories) {
    if (is_array($categories) && (count($categories) > 0)) {
      $categoryCondition = PapayaUtilString::escapeForPrintf(
        $this->databaseGetSqlCondition('parent_id', array_keys($categories))
      );
      $sql = "SELECT COUNT(*) AS count, parent_id
                FROM %s
               WHERE $categoryCondition
               GROUP BY parent_id";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableTagCategory))) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $categories[(int)$row['parent_id']]['CATEG_COUNT'] = $row['count'];
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * load data for a given category in all available languages
   *
   * @param integer $categoryId category id
   * @param null|integer $lngId
   * @return array $result category data (lng_id => array(category_data)) or only
   *               category_data if lngId is set
   */
  function getCategory($categoryId, $lngId = NULL) {
    $result = array();
    $categoryCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('c.category_id', $categoryId)
    );
    if (!is_array($lngId)) {
      $lngCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('ct.lng_id', $lngId)
      );
    } else {
      $lngCondition = '';
    }
    $sql = "SELECT c.category_id, c.parent_id, c.parent_path, 
                   c.category_name, c.permission_mode,
                   ct.category_title, ct. category_description, ct.lng_id
              FROM %s c
              LEFT OUTER JOIN %s ct ON (c.category_id = ct.category_id $lngCondition)
             WHERE $categoryCondition
             ORDER BY ct.lng_id ASC
           ";
    $params = array($this->tableTagCategory, $this->tableTagCategoryTrans);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (isset($lngId) && $lngId > 0) {
          return $row;
        }
        $result[$row['lng_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * load category details (creator, creation time)
  * @param integer $categoryId
   * @return array
   */
  function getCategoryDetails($categoryId) {
    $result = array();
    $categoryCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('c.category_id', $categoryId)
    );
    $sql = "SELECT c.category_id, c.parent_id, c.parent_path, c.category_name, 
                   c.creator_type, c.creator_id, c.creation_time,
                   s.surfer_givenname, s.surfer_surname, s.surfer_email
              FROM %s c
              LEFT OUTER JOIN %s s
                ON ((c.creator_type = 'admin' AND c.creator_id = s.auth_user_id)
                    OR (c.creator_type = 'surfer' AND c.creator_id = s.surfer_id))
             WHERE $categoryCondition";
    $params = array($this->tableTagCategory, PAPAYA_DB_TBL_SURFER);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row;
      }
    }
    return $result;
  }

  /**
  * load tags
  *
  * @param mixed $tagIds array of tag ids or single tag id
  * @param integer $lngId language id
  * @return array $result tags data (tag_id => array(tag_data))
  */
  function getTags($tagIds, $lngId) {
    $result = array();
    if (isset($lngId)) {
      $lngCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('tt.lng_id', $lngId)
      );
    } else {
      $lngCondition = '';
    }
    if (isset($tagIds) && (is_array($tagIds) || (int)$tagIds > 0)) {
      $tagCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('t.tag_id', $tagIds)
      );
    } else {
      $tagCondition = '';
    }

    $sql = "SELECT t.category_id, t.tag_id, tt.tag_title
              FROM %s t, %s tt
             WHERE t.tag_id = tt.tag_id
              $lngCondition
              $tagCondition
             ORDER BY tag_title ASC";
    $params = array($this->tableTag, $this->tableTagTrans);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['tag_id']] = $row;
      }
    }
    return $result;
  }

  /**
   * loads tags by category
   *
   * @param array|integer $categoryId array of category ids or single category id
   * @param integer $lngId language id
   * @param int $limit
   * @param int $offset
   * @param null|string $char
   * @return array $result category data (cat_id => array(tag_id => array(tag_data)))
   */
  function getTagsByCategory($categoryId, $lngId, $limit = 20, $offset = 0, $char = NULL) {
    $result = array();
    $categoryCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('t.category_id', $categoryId)
    );
    $lngCondition = ' AND '.str_replace(
      '%', '%%', $this->databaseGetSQLCondition('tt.lng_id', $lngId)
    );
    $charCondition = '';
    if (!is_null($char)) {
      $charCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('tt.tag_char', $char)
      );
    }
    $sql = "SELECT t.category_id, t.tag_id, tt.tag_title, tt.tag_image, tt.tag_description
              FROM %s t
              LEFT OUTER JOIN %s tt ON (t.tag_id = tt.tag_id $lngCondition)
             WHERE $categoryCondition
                   $charCondition
             ORDER BY tag_title ASC";
    $params = array($this->tableTag, $this->tableTagTrans);
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['category_id']][$row['tag_id']] = $row;
      }
      $this->absCount = $res->absCount();
    }
    return $result;
  }

  /**
   * Load tags by type and link IDs
   *
   * You can either provide a single link ID, resulting in an array
   * of tags for that link, or an array of link IDs, resulting in an
   * array of tag arrays for each link in which the link IDs are the keys.
   * If you provide a language ID, localized information for that language
   * will be provided, otherwise just the language-independent data.
   *
   * @todo Refactor into a "PageTags" class
   * @param string $type
   * @param integer|array $linkIds
   * @param integer $lngId optional, default NULL
   * @retun array
   */
  function getTagsByTypeAndLinkIds($type, $linkIds, $lngId = NULL) {
    $multiple = TRUE;
    if (!is_array($linkIds)) {
      $multiple = FALSE;
    }
    $result = array();
    $condition = $this->databaseGetSQLCondition('tl.link_id', $linkIds);
    if ($lngId !== NULL) {
      $sql = "SELECT t.tag_id, t.tag_uri, tt.tag_title, tt.tag_description,
                     tt.tag_image, tl.link_id
                FROM %s tl
               INNER JOIN %s t
                  ON tl.tag_id = t.tag_id
                LEFT JOIN %s tt
                  ON t.tag_id = tt.tag_id
               WHERE tl.link_type = '%s'
                 AND ".str_replace('%', '%%', $condition);
      $parameters = array(
        $this->tableTagLinks,
        $this->tableTag,
        $this->tableTagTrans,
        $type
      );
    } else {
      $sql = "SELECT t.tag_id, t.tag_uri, tl.link_id
                FROM %s tl
               INNER JOIN %s t
                  ON tl.tag_id = t.tag_id
               WHERE tl.link_type = '%s'
                 AND ".str_replace('%', '%%', $condition);
      $parameters = array(
        $this->tableTagLinks,
        $this->tableTag,
        $type
      );
    }
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!isset($result[$row['link_id']])) {
          $result[$row['link_id']] = array();
        }
        $result[$row['link_id']][$row['tag_id']] = $row;
      }
    }
    if (!$multiple && isset($result[$linkIds])) {
      $result = $result[$linkIds];
    }
    return $result;
  }

  /**
   * loads tags by category
   *
   * @todo Refactor into a "PageTags" class
   * @param integer|array $categoryId array of category ids or single category id
   * @param integer $lngId language id
   * @param int $limit
   * @param int $offset
   * @param null|string $char
   * @param null $viewId
   * @return array $result category data (cat_id => array(tag_id => array(tag_data)))
   */
  function getTagTitlesByCategory(
    $categoryId, $lngId, $limit = 20, $offset = 0, $char = NULL, $viewId = NULL
  ) {
    $result = array();
    $categoryCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('t.category_id', $categoryId)
    );
    $charCondition = '';
    if (!is_null($char)) {
      $charCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('tt.tag_char', $char)
      );
    }

    $viewCondition = '';
    if ($viewId) {
      $viewCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('pages.view_id', $viewId)
      );
    }

    $sql = "SELECT tt.tag_title title, COUNT(pages.topic_id) taglinks
              FROM %s t
              JOIN %s AS tt ON (t.tag_id = tt.tag_id AND tt.lng_id = '%d')
              JOIN %s AS links ON (links.tag_id = tt.tag_id AND links.link_type = 'topic')
              JOIN %s AS pages ON (pages.topic_id = links.link_id AND pages.lng_id = '%d')
             WHERE $categoryCondition
                   $charCondition
                   $viewCondition
             GROUP BY tt.tag_title
            HAVING taglinks > 0
          ORDER BY tag_title ASC";
    $params = array(
      $this->tableTag,
      $this->tableTagTrans,
      $lngId,
      $this->tableTagLinks,
      $this->getDatabaseAccess()->getTableName(
        $this->papaya()->request->isPreview
          ? Papaya\Content\Tables::PAGE_TRANSLATIONS
          : Papaya\Content\Tables::PAGE_PUBLICATION_TRANSLATIONS
      ),
      $lngId
    );

    $this->databaseEnableAbsoluteCount();
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row['title'];
      }
      $this->absCount = $res->absCount();
    }
    return $result;
  }

  /**
   * @todo Refactor into a "PageTags" class
   * @param integer|array $categoryId
   * @param integer $lngId
   * @param int $limit
   * @param int $offset
   * @param null|string $char
   * @return array
   */
  function getTagTitlesByParentCategory(
    $categoryId, $lngId, $limit = 20, $offset = 0, $char = NULL
  ) {
    $result = array();
    $categoryCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('c.parent_id', $categoryId)
    );
    $charCondition = '';
    if (!is_null($char)) {
      $charCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('tt.tag_char', $char)
      );
    }
    $sql = "SELECT tt.tag_title title, COUNT(pages.topic_id) taglinks
              FROM (%s AS t, %s AS c)
              JOIN %s AS tt ON (t.tag_id = tt.tag_id AND tt.lng_id = '%d')
              JOIN %s AS links ON (links.tag_id = tt.tag_id AND links.link_type = 'topic')
              JOIN %s AS pages ON (pages.topic_id = links.link_id AND pages.lng_id = '%d')
             WHERE $categoryCondition
                   $charCondition
               AND t.category_id = c.category_id
             GROUP BY tt.tag_title
            HAVING taglinks > 0
          ORDER BY title ASC";
    $params = array(
      $this->tableTag,
      $this->tableTagCategory,
      $this->tableTagTrans,
      $lngId,
      $this->tableTagLinks,
      $this->getDatabaseAccess()->getTableName(
        $this->papaya()->request->isPreview
          ? Papaya\Content\Tables::PAGE_TRANSLATIONS
          : Papaya\Content\Tables::PAGE_PUBLICATION_TRANSLATIONS
      ),
      $lngId
    );
    $this->databaseEnableAbsoluteCount();
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row['title'];
      }
      $this->absCount = $res->absCount();
    }
    return $result;
  }

  /**
   * Returns an array with first chars from tag titles by category id
   *
   * @todo Refactor into a "PageTags" class
   * @param integer|array $categoryId
   * @param integer $lngId
   * @param integer|array $viewId
   * @return array
   */
  public function getCharsByCategory($categoryId, $lngId, $viewId = NULL) {
    $result = array();
    $categoryCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('t.category_id', $categoryId)
    );
    $viewCondition = '';
    if ($viewId) {
      $viewCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('pages.view_id', $viewId)
      );
    }
    $sql = "SELECT tt.tag_char, COUNT(pages.topic_id) taglinks
              FROM %s AS tt
              JOIN %s AS t ON (tt.tag_id = t.tag_id AND tt.lng_id = %d)
              JOIN %s AS links ON (links.tag_id = tt.tag_id AND links.link_type = 'topic')
              JOIN %s AS pages ON (pages.topic_id = links.link_id AND pages.lng_id = '%d')
             WHERE $categoryCondition
               $viewCondition
             GROUP BY tt.tag_char
            HAVING taglinks > 0
          ORDER BY tt.tag_char ASC";
    $params = array(
      $this->tableTagTrans,
      $this->tableTag,
      $lngId,
      $this->tableTagLinks,
      $this->getDatabaseAccess()->getTableName(
        $this->papaya()->request->isPreview
          ? Papaya\Content\Tables::PAGE_TRANSLATIONS
          : Papaya\Content\Tables::PAGE_PUBLICATION_TRANSLATIONS
      ),
      $lngId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row['tag_char'];
      }
      $this->absCount = $res->count();
    }
    return $result;
  }

  /**
   * Returns an array with first chars from tag titles by parent category id
   *
   * @todo Refactor into a "PageTags" class
   * @param integer $categoryId
   * @param integer $lngId
   * @return array
   */
  public function getCharsByParentCategory($categoryId, $lngId) {
    $result = array();
    $categoryCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('tc.parent_id', $categoryId)
    );
    $sql = "SELECT tt.tag_char, COUNT(pages.topic_id) taglinks
              FROM %s AS tt
              JOIN %s AS t ON (tt.tag_id = t.tag_id AND tt.lng_id = %d)
              JOIN %s AS tc ON (t.category_id = tc.category_id)
              JOIN %s AS links ON (links.tag_id = tt.tag_id AND links.link_type = 'topic')
              JOIN %s AS pages ON (pages.topic_id = links.link_id AND pages.lng_id = '%d')
             WHERE $categoryCondition
             GROUP BY tt.tag_char
            HAVING taglinks > 0
          ORDER BY tt.tag_char ASC";

    $params = array(
      $this->tableTagTrans,
      $this->tableTag,
      $lngId,
      $this->tableTagCategory,
      $this->tableTagLinks,
      $this->getDatabaseAccess()->getTableName(
        $this->papaya()->request->isPreview
          ? Papaya\Content\Tables::PAGE_TRANSLATIONS
          : Papaya\Content\Tables::PAGE_PUBLICATION_TRANSLATIONS
      ),
      $lngId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row['tag_char'];
      }
      $this->absCount = $res->count();
    }
    return $result;
  }

  /**
  * Check tag uri is valid and unique
  * @param string $newTagURI
  * @param string $currentTagURI
  * @return boolean
  */
  function checkTagURI($newTagURI, $currentTagURI = '') {
    if (empty($newTagURI)) {
      return TRUE;
    } elseif (empty($currentTagURI)) {
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE tag_uri = '%s'";
      $params = array($this->tableTag, $newTagURI);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($res->fetchField() == 0) {
          return TRUE;
        }
      }
    } else {
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE tag_uri IN ('%s', '%s')";
      $params = array($this->tableTag, $newTagURI, $currentTagURI);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($res->fetchField() == 1) {
          return TRUE;
        }
      }
    }
    $this->addMsg(MSG_ERROR, $this->_gt('Tag uri must be unique'));
    return FALSE;
  }

  /**
  * This method adds a tag to the tagpool
  *
  * @param integer $categoryId the category that will hold the tag
  * @param integer $lngId the default language id of that tag
  * @param string $userType either 'admin' or 'surfer', whoever created the tag
  * @param string $userId the id of the previous given user type
  * @param array $tagData array with tag data
  * @param string $tagURI intended for referencing mpeg 7 tags
  * @return mixed $result either the tag id if adding it worked, otherwise FALSE
  */
  function addTag($categoryId, $lngId, $userType, $userId, $tagData, $tagURI = '') {
    if ($this->checkTagURI($tagURI)) {
      $data = array(
        'category_id' => (string)$categoryId,
        'default_lng_id' => (string)$lngId,
        'creator_type' => (string)$userType,
        'creator_id' => (string)$userId,
        'creation_time' => time(),
        'tag_uri' => (string)$tagURI,
      );
      if ($tagId = $this->databaseInsertRecord($this->tableTag, 'tag_id', $data)) {
        $dataTrans[] = array(
          'tag_id' => (string)$tagId,
          'tag_title' => $tagData['tag_title'],
          'tag_image' => $tagData['tag_image'],
          'tag_description' => $tagData['tag_description'],
          'tag_char' => $this->compileTagChar($tagData['tag_title']),
          'lng_id' => (string)$lngId,
        );
        if ($this->databaseInsertRecords($this->tableTagTrans, $dataTrans)) {
          return $tagId;
        }
      }
    }
    return FALSE;
  }

  /**
  * load tag
  *
  * @param integer $tagId tag id
  * @param integer $lngId language id
  * @return array $result tag data (id, title, lng_id, category_id) in given lng
  */
  function getTag($tagId, $lngId = NULL) {
    $result = array();
    $filter = str_replace('%', '%%', $this->databaseGetSQLCondition('t.tag_id', $tagId));
    $lngFilter = '';
    if (!empty($lngId)) {
      $lngFilter = " AND tt.lng_id = '".(int)$lngId."'";
    }
    $sql = "SELECT t.tag_id, tt.tag_title, tt.lng_id, tt.tag_image, tt.tag_description,
                   tt.tag_char, t.category_id, t.tag_uri
              FROM %s t
              LEFT OUTER JOIN %s tt ON (t.tag_id = tt.tag_id $lngFilter)
             WHERE $filter
             ORDER BY tt.tag_title ASC
           ";
    $params = array($this->tableTag, $this->tableTagTrans);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $result = NULL;
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!isset($result)) {
          $result = $row;
        }
      }
    }
    return $result;
  }

  /**
  * get details for a tag, i.e. information on creation time/user
  *
  * @param integer $tagId tag id
  * @return array $result tag_id, category_id, creator (type, id, time, name, email)
  */
  function getTagDetails($tagId) {
    $result = array();
    $tagCondition = str_replace('%', '%%', $this->databaseGetSQLCondition('t.tag_id', $tagId));
    $sql = "SELECT t.tag_id, t.category_id, t.creator_type, t.creator_id, t.tag_uri,
                   t.creation_time,
                   s.surfer_givenname, s.surfer_surname, s.surfer_email
              FROM %s t
              LEFT OUTER JOIN %s s
                ON ((t.creator_type = 'admin' AND t.creator_id = s.auth_user_id)
                    OR (t.creator_type = 'surfer' AND t.creator_id = s.surfer_id))
              WHERE $tagCondition
           ";
    $params = array($this->tableTag, PAPAYA_DB_TBL_SURFER);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $result = $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
    return $result;
  }

  /**
  * load all linked items of a specific type that are tagged with given tag(s)
  *
  * @param string $type tag type
  * @param mixed $tagId tag id or array of tag ids
  * @return array $result array of linked item ids (item_id => item_id)
  */
  function getTaggedLinks($type, $tagId) {
    $result = array();
    if ($type != '') {
      $tagCondition = str_replace('%', '%%', $this->databaseGetSQLCondition('tag_id', $tagId));
      $sql = "SELECT link_id
                FROM %s
              WHERE link_type = '%s'
                AND $tagCondition
              ORDER BY link_priority";
      $params = array($this->tableTagLinks, $type);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($linkId = $res->fetchField()) {
          $result[$linkId] = $linkId;
        }
      }
    }
    return $result;
  }

  /**
  * load all tags that a given link id of given type is tagged with
  *
  * @param string $type tag type
  * @param array|int $linkId link id or array of link ids
  * @param integer $lngId language id, loads translated title if set
  * @return array $result array of tags (tag_id => tag_id)
  */
  function getLinkedTags($type, $linkId, $lngId = NULL) {
    $result = array();
    if ($type != '') {
      $linkCondition = str_replace(
        '%', '%%', $this->databaseGetSQLCondition('tl.link_id', $linkId)
      );
      if ($lngId > 0) {
        $sql = "SELECT tl.tag_id, tl.link_priority, tt.tag_title
                  FROM %s tl
                  LEFT OUTER JOIN %s tt
                       ON (tt.tag_id = tl.tag_id AND tt.lng_id = '%d')
                 WHERE tl.link_type = '%s'
                   AND $linkCondition
                 ORDER BY tl.link_priority, tt.tag_title";
        $params = array($this->tableTagLinks, $this->tableTagTrans, $lngId, $type);
      } else {
        $sql = "SELECT tl.tag_id, tl.link_priority
                  FROM %s tl
                WHERE tl.link_type = '%s'
                  AND $linkCondition
                ORDER BY tl.link_priority";
        $params = array($this->tableTagLinks, $type);
      }
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['tag_id']] = new PapayaObjectStringValues(
            $row, isset($row['tag_title']) ? 'tag_title' : 'tag_id'
          );
        }
      }
    }
    return $result;
  }

  /**
  * This method loads a list of tags that are linked to given ids
  *
  * @param string $type tag type
  * @param array|integer $linkIds list of link ids
  * @param integer $lngId optional, a language id
  * @return array $result list of tags linked to the given link ids
  *                       array(linkid => array(tagid => tagtitle|id))
  */
  function getLinkedTagsForIds($type, $linkIds, $lngId = NULL) {
    $result = array();
    if ($type != '') {
      $linkCondition = str_replace(
        '%', '%%', $this->databaseGetSQLCondition('tl.link_id', $linkIds)
      );
      if ($lngId > 0) {
        $sql = "SELECT tl.tag_id, tl.link_id, tl.link_priority, tl.link_priority, tt.tag_title
                  FROM %s tl
                  LEFT OUTER JOIN %s tt
                       ON (tt.tag_id = tl.tag_id AND tt.lng_id = '%d')
                 WHERE tl.link_type = '%s'
                   AND $linkCondition
                 ORDER BY tl.link_priority, tt.tag_title";
        $params = array($this->tableTagLinks, $this->tableTagTrans, $lngId, $type);
      } else {
        $sql = "SELECT tl.tag_id, tl.link_id, tl.link_priority
                  FROM %s tl
                 WHERE tl.link_type = '%s'
                   AND $linkCondition
                 ORDER BY tl.link_priority";
        $params = array($this->tableTagLinks, $type);
      }

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['tag_id']] = new PapayaObjectStringValues(
            $row, isset($row['tag_title']) ? 'tag_title' : $row['tag_id']
          );
        }
      }
    }
    return $result;
  }

  /**
   * add a tag for an item
   *
   * @param string $type tag type
   * @param mixed $linkId item id (integer or string)
   * @param integer $tagId tag id
   * @param int $priority
   * @return boolean whether the linking worked (TRUE) or failed (FALSE)
   */
  function linkTag($type, $linkId, $tagId, $priority = 50) {
    if (is_array($linkId)) {
      $tags = $this->getLinkedTagsForIds($type, $linkId);
      foreach ($linkId as $singleLinkId) {
        if (!isset($tags[$tagId][$singleLinkId])) {
          $data[] = array(
            'link_type' => $type,
            'link_id' => $singleLinkId,
            'link_priority' => (int)$priority,
            'tag_id' => $tagId
          );
        }
      }
      if (isset($data) && is_array($data) && count($data) > 0) {
        return $this->databaseInsertRecords($this->tableTagLinks, $data);
      }
    } else {
      $tags = $this->getLinkedTags($type, $linkId);
      if (!(is_array($tags) && isset($tags[$tagId]))) {
        $data = array(
          'link_type' => $type,
          'link_id' => $linkId,
          'link_priority' => (int)$priority,
          'tag_id' => $tagId,
        );
        return FALSE !== $this->databaseInsertRecord(
          $this->tableTagLinks, NULL, $data
        );
      }
    }
    return TRUE;
  }

  /**
   * change the priority of an tag
   *
   * @param string $type tag type
   * @param mixed $linkId item id (integer or string)
   * @param integer $tagId tag id
   * @param $priority
   * @return boolean whether the linking worked (TRUE) or failed (FALSE)
   */
  function saveTagPriority($type, $linkId, $tagId, $priority) {
    $values = array(
      'link_priority' => (int)$priority
    );
    $filter = array(
      'link_type' => (string)$type,
      'link_id' => (string)$linkId,
      'tag_id' => (int)$tagId
    );
    return FALSE !== $this->databaseUpdateRecord($this->tableTagLinks, $values, $filter);
  }

  /**
   * Link tags to resource
   * @param string $type
   * @param array|integer $linkId
   * @param array $tagIds
   * @param int $priority
   * @return boolean|integer
   */
  function linkTags($type, $linkId, $tagIds, $priority = 50) {
    if (is_array($tagIds)) {
      $tags = $this->getLinkedTagsForIds($type, $linkId);
      foreach ($tagIds as $singleTagId) {
        if (!isset($tags[$linkId][$singleTagId])) {
          $data[] = array(
            'link_type' => $type,
            'link_id' => $linkId,
            'link_priority' => (int)$priority,
            'tag_id' => $singleTagId,
          );
        }
      }
      if (isset($data) && is_array($data) && count($data) > 0) {
        return $this->databaseInsertRecords($this->tableTagLinks, $data);
      }
    }
    return TRUE;
  }

  /**
  * remove a tag from an item
  *
  * @param string $type tag type
  * @param mixed $linkId item id (integer or string)
  * @param integer $tagId tag id
   * @return bool|int
   */
  function unlinkTag($type, $linkId, $tagId = NULL) {
    if (is_array($linkId)) {
      if (count($linkId) > 0) {
        $linkCondition = str_replace('%', '%%', $this->databaseGetSQLCondition('link_id', $linkId));
        $sql = "DELETE FROM %s
                WHERE link_type = '%s'
                  AND tag_id = %d
                  AND $linkCondition";
        $params = array($this->tableTagLinks, $type, $tagId);
        return $this->databaseQueryFmtWrite($sql, $params);
      }
    } else {
      $condition = array(
        'link_type' => $type,
        'link_id' => $linkId,
      );
      if ($tagId > 0) {
        $condition['tag_id'] = $tagId;
      }
      return $this->databaseDeleteRecord($this->tableTagLinks, $condition);
    }
    return FALSE;
  }

  /**
  * load statistic for a given tag (how many links of each type exist)
  *
  * @param integer $tagId tag id
  * @return array $result (link_type => count)
  */
  function getTagStatistic($tagId) {
    $result = array();
    $sql = "SELECT COUNT(*) AS count, link_type
              FROM %s
             WHERE tag_id = %d
             GROUP BY link_type
             ORDER BY count";
    $params = array($this->tableTagLinks, $tagId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['link_type']] = $row['count'];
      }
    }
    return $result;
  }

  /**
  * search for tags
  *
  * @todo check/implement for searchstringparser usage
  *
  * @param string $search search string
  * @param integer $limit maximum number of results, default is 20
  * @param integer $offset offset of results, default is 0
  * @param string $order order by field, possible values are 'path'|'tag'|'length',
  *               default is 'tag'
  * @param string $sort order by mode, possible values are 'DESC'|'ASC' default is ASC
  * @param integer $lngId optional language id
  * @return array $result list of tags
  */
  function searchTags(
    $search, $limit = 20, $offset = 0, $order = 'tag', $sort = 'ASC', $lngId = NULL
  ) {
    $result = array();
    if ($search != '') {
      if (FALSE == strpos($search, '*')) {
        $search .= '*';
      }
      $replaceChars = array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_');
      $patt = strtr($search, $replaceChars);

      $patt = str_replace('%', '%%', $patt);
    } else {
      $patt = "%";
    }
    if (strtoupper($sort) == 'ASC' || strtoupper($sort) == 'DESC') {
      $sort = strtoupper($sort);
    } else {
      $sort = 'ASC';
    }
    switch ($order) {
    default :
    case 'tag':
      $orderSQL = ' ORDER BY tt.tag_title '.$sort.', c.parent_path ASC, c.category_id ASC ';
      break;
    case 'path':
      $orderSQL = ' ORDER BY c.parent_path '.$sort.', c.category_id '.$sort.
        ', tt.tag_title ASC ';
      break;
    case 'length':
      $orderSQL = ' ORDER BY LENGTH(tt.tag_title) '.$sort.', tt.tag_title ASC ';
      break;
    }

    $lngCondition = '';
    if (!is_null($lngId)) {
      $lngCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('tt.lng_id', $lngId)
      );
    }
    $sql = "SELECT tt.tag_id, tt.tag_title, t.category_id, tt.lng_id,
                   c.parent_path
              FROM %s tt
              LEFT OUTER JOIN %s t ON (tt.tag_id = t.tag_id)
              LEFT OUTER JOIN %s c ON (t.category_id = c.category_id)
             WHERE tag_title LIKE '%s'
             $lngCondition
             $orderSQL";
    $params = array(
      $this->tableTagTrans, $this->tableTag, $this->tableTagCategory, $patt
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
      $this->absCount = $res->absCount();
    }
    return $result;
  }

  /**
   * search for tags
   *
   * @todo check/implement for searchstringparser usage
   *
   * @param string $search search string
   * @param integer $limit maximum number of results, default is 20
   * @param integer $offset offset of results, default is 0
   * @param string $order order by field, possible values are 'path'|'tag'|'length',
   *               default is 'tag'
   * @param string $sort order by mode, possible values are 'DESC'|'ASC' default is ASC
   * @param integer $lngId optional language id
   * @return array $result list of tags
   */
  function searchTagTitles(
    $search, $limit = 20, $offset = 0, $order = 'tag', $sort = 'ASC', $lngId = NULL
  ) {
    $result = array();
    if ($search != '') {
      if (FALSE == strpos($search, '*')) {
        $search .= '*';
      }
      $replaceChars = array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_');
      $patt = strtr($search, $replaceChars);

      $patt = str_replace('%', '%%', $patt);
    } else {
      $patt = "%";
    }
    if (strtoupper($sort) == 'ASC' || strtoupper($sort) == 'DESC') {
      $sort = strtoupper($sort);
    } else {
      $sort = 'ASC';
    }
    switch ($order) {
    default :
    case 'tag':
      $orderSQL = ' ORDER BY tag_title '.$sort.', parent_path ASC, category_id ASC ';
      break;
    case 'path':
      $orderSQL = ' ORDER BY parent_path '.$sort.', category_id '.$sort.
        ', tag_title ASC ';
      break;
    case 'length':
      $orderSQL = ' ORDER BY LENGTH(tt.tag_title) '.$sort.', tt.tag_title ASC ';
      break;
    }

    $lngCondition = '';
    if (!is_null($lngId)) {
      $lngCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('tt.lng_id', $lngId)
      );
    }
    $sql = "SELECT DISTINCT tt.tag_title
              FROM %s tt
              LEFT OUTER JOIN %s t ON (tt.tag_id = t.tag_id)
              LEFT OUTER JOIN %s c ON (t.category_id = c.category_id)
             WHERE tag_title LIKE '%s'
             $lngCondition
             $orderSQL";

    $params = array(
      $this->tableTagTrans, $this->tableTag, $this->tableTagCategory, $patt
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
      $this->absCount = $res->absCount();
    }
    return $result;
  }

  /**
  * get list of available categories for a permission
  *
  * @todo implement this for surfers
  *
  * @param base_auth $authUser instance of base_auth
  * @param array $permissions which permissions should the category satisfy for
  *              the given user?
  * @return array $result array (category_id => category_id)
  */
  function getAvailableCategories($authUser, $permissions = array('user_use_tags')) {
    $result = array();
    if (isset($authUser) && isset($authUser->user)) {
      $isAdmin = (in_array('-1', $authUser->user['groups']));
      if (!$isAdmin) {
        $permissionCondition = str_replace(
          '%', '%%', $this->databaseGetSQLCondition('cp.permission_type', $permissions)
        );
        $sql = "SELECT DISTINCT category_id
                  FROM %s cp, %s al
                WHERE $permissionCondition
                  AND al.user_id = '%s'
                  AND (al.group_id = cp.permission_value OR cp.permission_value = %d)";
        $params = array($this->tableTagCategoryPermissions, PAPAYA_DB_TBL_AUTHLINK,
          $authUser->userId, $authUser->user['group_id']);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          while ($catId = $res->fetchField()) {
            $result[$catId] = $catId;
            $parentPathConditions[$catId] = " parent_path LIKE '%%;$catId;%%' ";
          }
        }
        if (isset($parentPathConditions) && is_array($parentPathConditions) &&
            count($parentPathConditions) > 0) {
          $parentPathCondition = implode(' OR ', $parentPathConditions);
        } else {
          $parentPathCondition = '1=1';
        }
        $sql = "SELECT DISTINCT category_id
                  FROM %s
                 WHERE (permission_mode = 'inherited'
                        OR permission_mode = 'additional')
                   AND ($parentPathCondition)";
        $params = array($this->tableTagCategory);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          while ($catId = $res->fetchField()) {
            $result[$catId] = $catId;
          }
        }
      }
    }
    return $result;
  }


  /**
  * calculate permission state for surfer/backend users for the current category
  */
  function calculatePermissions(&$category) {
    $result = array();
    if (isset($category) && is_array($category) && count($category) > 0) {
      if ($category['permission_mode'] == 'own' ||
          $category['permission_mode'] == 'additional') {
        $permissions['own'][] = $this->getCategoryPermissions($category['category_id']);
      }
      if ($category['permission_mode'] == 'inherited' ||
          $category['permission_mode'] == 'additional') {
        $this->loadCategories();
        $parents = explode(';', $category['parent_path']);
        array_pop($parents);
        while ($parentId = array_pop($parents)) {
          if ($parentId > 0 && isset($this->categories[$parentId])) {
            switch ($this->categories[$parentId]['permission_mode']) {
            case 'own':
              $permissions['inherited'][] = $this->getCategoryPermissions($parentId);
              break 2;
            case 'inherited':
              break;
            case 'additional':
              $permissions['inherited'][] = $this->getCategoryPermissions($parentId);
              break;
            }
          }
        }
      }
    }
    if (isset($permissions) && is_array($permissions) && count($permissions) > 0) {
      foreach ($permissions as $mode => $modePerms) {
        foreach ($modePerms as $partPerms) {
          foreach ($partPerms as $permType => $singlePermission) {
            foreach ($singlePermission as $permissionId => $value) {
              if (!isset($result[$permType][$permissionId]) ||
                  $result[$permType][$permissionId] != 'own') {
                $result[$permType][$permissionId] = $mode;
              }
            }
          }
        }
      }
    }
    // administrators may do everything, and this may not be changed
    $result['user_edit_category'][-1] = 'inherited';
    $result['user_edit_tag'][-1] = 'inherited';
    $result['user_use_tags'][-1] = 'inherited';
    return $result;
  }

  /**
  * load category permissions for given category
  *
  * @param integer $categoryId id of a tag category
  * @return array $result array (permission_type => array('permission_value' => 1))
  */
  function getCategoryPermissions($categoryId) {
    $result = array();
    $sql = "SELECT permission_type, permission_value
              FROM %s
             WHERE category_id = %d";
    $params = array($this->tableTagCategoryPermissions, $categoryId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['permission_type']][$row['permission_value']] = 1;
      }
    }
    return $result;
  }

  /**
  * This method fetches all category ids for a given title and optional a
  * language id and/or a category id
  *
  * @param string $categoryTitle a category title to look for
  * @param integer $parentId optional, restrict to categories directly below this id
  * @param integer $lngId optional, restrict search on this language id
   * @return array|bool
   */
  function getCategoryIdsByTitle($categoryTitle, $parentId = NULL, $lngId = NULL) {
    $result = FALSE;
    if ($lngId != NULL) {
      $lngCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('ct.lng_id', $lngId)
      );
    } else {
      $lngCondition = '';
    }
    if ($parentId != NULL) {
      $categoryCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('c.parent_id', $parentId)
      );
    } else {
      $categoryCondition = '';
    }
    $sql = "SELECT c.category_id
              FROM %s ct
              LEFT OUTER JOIN %s c ON (ct.category_id = c.category_id)
             WHERE ct.category_title = '%s'
                   $lngCondition
                   $categoryCondition";
    $params = array($this->tableTagCategoryTrans, $this->tableTagCategory, $categoryTitle);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $result = array();
      while ($categoryId = $res->fetchField()) {
        $result[$categoryId] = $categoryId;
      }
    }
    return $result;
  }

  /**
  * This method fetches all tag ids for a given title and optional a language
  * id and/or a category id
  *
  * @param string $tagTitle a tag title to look for
  * @param integer $categoryId optional, a category id
  * @param integer $lngId optional, a language id
  * @return array $result list of tag ids that match the given criteria
  */
  function getTagIdsByTitle($tagTitle, $categoryId = NULL, $lngId = NULL) {
    $result = array();
    if ($lngId != NULL) {
      $lngCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('tt.lng_id', $lngId)
      );
    } else {
      $lngCondition = '';
    }
    if ($categoryId != NULL) {
      $categoryCondition = ' AND '.str_replace(
        '%', '%%', $this->databaseGetSQLCondition('t.category_id', $categoryId)
      );
    } else {
      $categoryCondition = '';
    }
    $sql = "SELECT tt.tag_id
              FROM %s tt
              LEFT OUTER JOIN %s t ON (tt.tag_id = t.tag_id)
             WHERE tt.tag_title = '%s'
                   $lngCondition
                   $categoryCondition";
    $params = array($this->tableTagTrans, $this->tableTag, $tagTitle);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($tagId = $res->fetchField()) {
        $result[$tagId] = $tagId;
      }
    }
    return $result;
  }

  /**
  * get tag ids for a list of tag URIs.
  *
  * @param array | string $tagURIs
  * @access public
  * @return array URI => Id
  */
  function getTagIdsByURI($tagURIs) {
    $result = array();
    $filter = $this->databaseGetSQLCondition('tag_uri', $tagURIs);
    $sql = "SELECT tag_id, tag_uri
              FROM %s
             WHERE $filter
               AND tag_uri != ''";
    if ($res = $this->databaseQueryFmt($sql, $this->tableTag)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['tag_uri']] = $row['tag_id'];
      }
    }
    return $result;
  }

  /**
  * This method fetches the number of subcategory for a given category
  *
  * @param integer $categoryId a category id
  * @return integer the number of subcategories
  */
  function getNumberOfSubcategories($categoryId) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE parent_id = '%d'";
    $params = array($this->tableTagCategory, $categoryId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return FALSE;
  }

  /**
  * This method fetches the number of tags a given category contains
  *
  * @param integer $categoryId a category id
  * @return integer number of tags the category contains
  */
  function getNumberOfTags($categoryId) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE category_id = '%d'";
    $params = array($this->tableTag, $categoryId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return FALSE;
  }

  /**
  * This method checks whether the given category is empty (i.e. has no subcategories
  * and no tags
  *
  * @param integer $categoryId a tag category id
  * @return boolean TRUE if the category is empty, otherwise FALSE
  */
  function categoryIsEmpty($categoryId) {
    $subCount = $this->getNumberOfSubcategories($categoryId);
    $tagCount = $this->getNumberOfTags($categoryId);
    if ($subCount == 0 && $tagCount == 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns the normalized first char of a tag title
   *
   * @param string $tagTitle
   * @return string
   */
  function compileTagChar($tagTitle) {
    $search = array('ä','ö','ü','ß','Ä','Ö','Ü');
    $replace = array('a','o','u','ss','a','o','u');
    $tagTitle = strtolower($tagTitle);
    $tagTitle = str_replace($search, $replace, $tagTitle);
    $char = substr($tagTitle, 0, 1);
    if (preg_match('/^[a-zA-Z]$/', $char)) {
      return $char;
    }
    return '';
  }

  /**
   * Returns a list of page ids where the given tag is linked.
   *
   * @param string $tagTitle
   * @param integer $lngId
   * @return array $pages
   */
  public function getLinkedPagesByTagTitle($tagTitle, $lngId) {
    $pages = array();
    $titleCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('tt.tag_title', $tagTitle)
    );
    $languageCondition = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('tt.lng_id', $lngId)
    );
    $sql = "SELECT tl.link_id
              FROM %s as tt
         LEFT JOIN %s as tl
                ON tt.tag_id = tl.tag_id
             WHERE $titleCondition
               AND $languageCondition
          ORDER BY tl.link_priority DESC";
    $params = array(
      $this->tableTagTrans,
      $this->tableTagLinks
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $pages[] = $row['link_id'];
      }
    }
    return $pages;
  }

  /**
   * @return bool
   */
  protected function loadCategories() {
    return FALSE;
  }
}
