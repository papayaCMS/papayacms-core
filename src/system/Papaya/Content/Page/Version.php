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
 * Provide data encapsulation for a single content page version and access to its translations.
 *
 * Allows to load/create the page version.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property integer $id
 * @property integer $created
 * @property string $owner
 * @property string $message
 * @property integer $level
 * @property integer $pageId
 * @property integer $modified
 * @property integer $position page position relative to its siblings
 * @property boolean $inheritBoxes box inheritance
 * @property integer $defaultLanguage default/fallback language,
 * @property integer $linkType page link type for navigation,
 * @property boolean $inheritMetaInfo inherit meta information like page title and keywords,
 * @property integer $changeFrequency change frequency (for search engines)
 * @property integer $priority content priority (for search engines)
 * @property integer $scheme page scheme (http, https or both)
 */
class Version extends \Papaya\Database\BaseObject\Record {

  /**
   * Map properties to database fields
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    // auto increment version id
    'id' => 'version_id',
    // version timestamp
    'created' => 'version_time',
    // version owner
    'owner' => 'version_author_id',
    // version log message
    'message' => 'version_message',
    // version change level (minor, major, ...)
    'level' => 'topic_change_level',
    // page id
    'page_id' => 'topic_id',
    // page modification timestamp
    'modified' => 'topic_modified',
    // page validation timestamp
    'audited' => 'topic_audited',
    // page position (siblings)
    'position' => 'topic_weight',
    // change frequency (for search engines)
    'change_frequency' => 'topic_changefreq',
    // page priority (for search engines)
    'priority' => 'topic_priority',
    // inherit meta informations from achestors
    'inherit_meta_information' => 'meta_useparent',
    // inherit box links from anchestors
    'inherit_boxes' => 'box_useparent',
    // default page language
    'default_language' => 'topic_mainlanguage',
    // page link type
    'link_type' => 'linktype_id',
    // page scheme/protocol
    'scheme' => 'topic_protocol'
  );

  /**
   * version table name for default load() implementations
   *
   * @var string
   */
  protected $_tableName = \Papaya\Content\Tables::PAGE_VERSIONS;

  /**
   * version translations list subobject
   *
   * @var Version\Translations
   */
  private $_translations;

  /**
   * Saving an existing version is not allowed. The creation of a new version will be directly from
   * the stored data using sql commands.
   *
   * @throws \LogicException
   * @throws \UnexpectedValueException
   * @return boolean
   */
  public function save() {
    if (isset($this->id)) {
      throw new \LogicException('LogicException: Page versions can not be changed.');
    }
    if (empty($this->pageId) || empty($this->owner) || empty($this->message)) {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: page id, owner or message are missing.'
      );
    }
    return $this->create();
  }

  /**
   * Create and store a backup of the current page working copy and its translations
   *
   * @return integer|FALSE
   */
  private function create() {
    $sql = /** @lang Text */
      "INSERT INTO %s (
             version_time, version_author_id, version_message, topic_change_level,
             topic_id, topic_modified, topic_weight, topic_changefreq, topic_priority,
             meta_useparent, box_useparent, topic_mainlanguage, linktype_id, topic_protocol
      )
      SELECT
             '%d', '%s', '%s', '%d',
             topic_id, topic_modified, topic_weight, topic_changefreq, topic_priority,
             meta_useparent, box_useparent, topic_mainlanguage, linktype_id, topic_protocol
        FROM %s
       WHERE topic_id = '%d'";
    $parameters = array(
      $this->databaseGetTableName($this->_tableName),
      isset($this->created) ? $this->created : time(),
      $this->owner,
      $this->message,
      isset($this->level) ? $this->level : -1,
      $this->databaseGetTableName(\Papaya\Content\Tables::PAGES),
      $this->pageId
    );
    if ($this->databaseQueryFmtWrite($sql, $parameters)) {
      $newId = $this->databaseLastInsertId(
        $this->databaseGetTableName($this->_tableName), 'version_id'
      );
      $sql = /** @lang Text */
        "INSERT INTO %s (
               version_id, lng_id, version_published,
               topic_id, topic_title, topic_content, author_id,
               view_id, meta_title, meta_keywords, meta_descr
        )
        SELECT '%d', tt.lng_id, 0, tt.topic_id, tt.topic_title,
               tt.topic_content, tt.author_id,
               tt.view_id, tt.meta_title, tt.meta_keywords, tt.meta_descr
          FROM %s tt
         WHERE tt.topic_id = %d";
      $parameters = array(
        $this->databaseGetTableName(\Papaya\Content\Tables::PAGE_VERSION_TRANSLATIONS),
        $newId,
        $this->databaseGetTableName(\Papaya\Content\Tables::PAGE_TRANSLATIONS),
        $this->pageId
      );
      $this->databaseQueryFmtWrite($sql, $parameters);
      return $newId;
    }
    return FALSE;
  }

  /**
   * Access to the version translations
   *
   * @param Version\Translations $translations
   * @return Version\Translations
   */
  public function translations(Version\Translations $translations = NULL) {
    if (NULL !== $translations) {
      $this->_translations = $translations;
    }
    if (NULL === $this->_translations) {
      $this->_translations = new Version\Translations();
      $this->_translations->setDatabaseAccess($this->getDatabaseAccess());
    }
    return $this->_translations;
  }
}
