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

use Papaya\Content;
use Papaya\Database;

/**
 * Provide data encapsulation for the working copy of content page.
 *
 * Allows to edit the pages. It contains no validation, only the database access
 * encapsulation.
 *
 * @property int $id page id
 * @property int $parentId direct page parent/ancestor id,
 * @property array $parentPath all page ancestor ids,
 * @property string $owner administration user that own this page
 * @property int $group administration user group that own this page
 * @property string $permissions administration permissions,
 * @property int $inheritVisitorPermissions inherit visitor permisssion from anchestors (mode)
 * @property array $visitorPermissions visitor permission for this node
 * @property int $created page creation timestamp
 * @property int $modified last modification timestamp
 * @property int $position page position relative to its siblings
 * @property bool $inheritBoxes box inheritance
 * @property int $defaultLanguage default/fallback language,
 * @property int $linkType page link type for navigations,
 * @property bool $inheritMetaInfo inherit meta informations like page title and keywords,
 * @property int $changeFrequency change frequency (for search engines)
 * @property int $priority content priority (for search engines)
 * @property int $scheme page scheme (http, https or both)
 * @property int $cacheMode page content cache mode (system, none, own)
 * @property int $cacheTime page content cache time, if mode == own
 * @property int $expiresMode page browser cache mode (system, none, own)
 * @property int $expiresTime page browser cache time, if mode == own
 * @property int $publishedFrom publication period - start time
 * @property int $publishedTo publication period - end time
 */
class Publication extends Content\Page {
  /**
   * Map properties to database fields
   *
   * @var array(string=>string)
   */
  protected $_fields = [
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
  ];

  protected $_tableName = Content\Tables::PAGE_PUBLICATIONS;

  protected $_translationsTableName = Content\Tables::PAGE_PUBLICATION_TRANSLATIONS;

  public function _createKey() {
    return new Database\Record\Key\Fields(
      $this, $this->getDatabaseAccess()->getTableName($this->_tableName), ['id']
    );
  }

  public function _insertRecord() {
    if (empty($this['id'])) {
      return FALSE;
    }
    return parent::_insertRecord();
  }
}
