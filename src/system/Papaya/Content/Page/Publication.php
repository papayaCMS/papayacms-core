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

namespace Papaya\Content\Page;
/**
 * Provide data encapsulation for the working copy of content page.
 *
 * Allows to edit the pages. It contains no validation, only the database access
 * encapsulation.
 *
 * @property integer $id page id
 * @property integer $parentId direct page parent/ancestor id,
 * @property array $parentPath all page ancestor ids,
 * @property string $owner administration user that own this page
 * @property integer $group administration user group that own this page
 * @property string $permissions administration permissions,
 * @property integer $inheritVisitorPermissions inherit visitor permisssion from anchestors (mode)
 * @property array $visitorPermissions visitor permission for this node
 * @property integer $created page creation timestamp
 * @property integer $modified last modification timestamp
 * @property integer $position page position relative to its siblings
 * @property boolean $inheritBoxes box inheritance
 * @property integer $defaultLanguage default/fallback language,
 * @property integer $linkType page link type for navigations,
 * @property boolean $inheritMetaInfo inherit meta informations like page title and keywords,
 * @property integer $changeFrequency change frequency (for search engines)
 * @property integer $priority content priority (for search engines)
 * @property integer $scheme page scheme (http, https or both)
 * @property integer $cacheMode page content cache mode (system, none, own)
 * @property integer $cacheTime page content cache time, if mode == own
 * @property integer $expiresMode page browser cache mode (system, none, own)
 * @property integer $expiresTime page browser cache time, if mode == own
 * @property integer $publishedFrom publication period - start time
 * @property integer $publishedTo publication period - end time
 */
class Publication extends \Papaya\Content\Page {

  /**
   * Map properties to database fields
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    // page id
    'id' => 'topic_id',
    // parent id
    'parent_id' => 'prev',
    // all anchestor ids (index)
    'parent_path' => 'prev_path',
    // ownership and permissions
    'owner' => 'author_id',
    'group' => 'author_group',
    'permissions' => 'author_perm',
    // visitor permission inheritance
    'inherit_visitor_permissions' => 'surfer_useparent',
    'visitor_permissions' => 'surfer_permids',
    // creation / modification timestamps
    'created' => 'topic_created',
    'modified' => 'topic_modified',
    // page position (relative to is siblings)
    'position' => 'topic_weight',
    // box link inheritance
    'inherit_boxes' => 'box_useparent',
    // default language of this page
    'default_language' => 'topic_mainlanguage',
    // link type for navigations (default, invisible, popup, ...)
    'link_type' => 'linktype_id',
    // meta data inheritance
    'inherit_meta_information' => 'meta_useparent',
    // page change frequency (for search engines)
    'change_frequency' => 'topic_changefreq',
    // page content priority (for search engines)
    'priority' => 'topic_priority',
    // http scheme
    'scheme' => 'topic_protocol',
    // server side content caching
    'cache_mode' => 'topic_cachemode',
    'cache_time' => 'topic_cachetime',
    // browser side caching
    'expires_mode' => 'topic_expiresmode',
    'expires_time' => 'topic_expirestime',
    //publication period
    'published_from' => 'published_from',
    'published_to' => 'published_to'
  );

  protected $_tableName = \Papaya\Content\Tables::PAGE_PUBLICATIONS;
  protected $_translationsTableName = \Papaya\Content\Tables::PAGE_PUBLICATION_TRANSLATIONS;

  public function _createKey() {
    return new \Papaya\Database\Record\Key\Fields(
      $this, $this->getDatabaseAccess()->getTableName($this->_tableName), array('id')
    );
  }

  public function _insertRecord() {
    if (empty($this['id'])) {
      return FALSE;
    }
    return parent::_insertRecord();
  }
}
