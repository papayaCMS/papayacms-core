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
 * Provide basic data encapsulation for the content page.
 *
 * Allows to load pages and provides basic function for the working copy and publication.
 *
 * This is an abstract superclass, please use {@see PapayaContentPageWork} to modify the
 * working copy of a page or {@see PapayaContentPagePublication} to use the published page.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property int $id
 * @property int $parentId
 * @property array $parentPath
 * @property bool $isDeleted
 * @property string $owner
 * @property int $group
 * @property array $permissions
 * @property bool $inheritVisitorPermissions
 * @property array $visitorPermissions
 * @property int $created
 * @property int $modified
 * @property int $position
 * @property bool $inheritBoxes
 * @property int $defaultLanguage
 * @property int $linkType
 * @property bool $inheritMetaInformation
 * @property int $changeFrequency
 * @property int $priority
 * @property int $scheme
 * @property int $cacheMode
 * @property int $cacheTime
 * @property int $expiresMode
 * @property int $expiresTime
 * @property-read int $unpublishedTranslations
 */
class PapayaContentPage extends PapayaDatabaseRecordLazy {

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
    // deleted marker (trash)
    'is_deleted' => 'is_deleted',
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
    // unpublished translations counter
    'unpublished_translations' => 'topic_unpublished_languages'
  );

  /**
  * Pages table name for sql queries
  *
  * @var string
  */
  protected $_tableName = PapayaContentTables::PAGES;

  /**
  * Page translations list object
  * @var PapayaContentPageTranslations
  */
  protected $_translations = NULL;

  /**
  * Translations table name, used to define the table for the translations
  * subobject
  *
  * @var string
  */
  protected $_translationsTableName = 'topic_trans';

  /**
   * Load page record from database
   *
   * @param mixed $filter
   * @return bool
   */
  public function load($filter) {
    $loaded = parent::load($filter);
    if ($loaded) {
      $this->translations()->load($this->id);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Attach callbacks for serialized field values
   *
   * @see PapayaDatabaseRecord::_createMapping()
   */
  public function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValueFromFieldToProperty = array(
      $this, 'callbackMapValueFromFieldToProperty'
    );
    $mapping->callbacks()->onMapValueFromPropertyToField = array(
      $this, 'callbackMapValueFromPropertyToField'
    );
    return $mapping;
  }

  /**
   * Unserialize path and permissions field values
   *
   * @param object $context
   * @param string $property
   * @param string $field
   * @param string $value
   * @return mixed
   */
  public function callbackMapValueFromFieldToProperty($context, $property, $field, $value) {
    switch ($property) {
    case 'parent_path' :
    case 'visitor_permissions' :
      return PapayaUtilArray::decodeIdList($value);
    }
    return $value;
  }


  /**
   * Serialize path and permissions field values
   *
   * @param object $context
   * @param string $property
   * @param string $field
   * @param string $value
   * @return mixed
   */
  public function callbackMapValueFromPropertyToField($context, $property, $field, $value) {
    switch ($property) {
    case 'parent_path' :
      return PapayaUtilArray::encodeAndQuoteIdList(empty($value) ? array() : $value);
    case 'visitor_permissions' :
      return PapayaUtilArray::encodeIdList(empty($value) ? array() : $value);
    }
    return $value;
  }

  /**
  * Access to the translation list informations
  *
  * Allows to get/set the list object. Can create a list object if needed.
  *
  * @param PapayaContentPageTranslations $translations
  * @return PapayaContentPageTranslations
  */
  public function translations(PapayaContentPageTranslations $translations = NULL) {
    if (isset($translations)) {
      $this->_translations = $translations;
    }
    if (is_null($this->_translations)) {
      $this->_translations = new \PapayaContentPageTranslations();
      $this->_translations->setDatabaseAccess($this->getDatabaseAccess());
      $this->_translations->setTranslationsTableName($this->_translationsTableName);
    }
    return $this->_translations;
  }

  protected function _createCallbacks() {
    $callbacks = parent::_createCallbacks();
    $callbacks->onBeforeInsert = array($this, 'callbackOnBeforeInsert');
    $callbacks->onBeforeUpdate = array($this, 'callbackOnBeforeUpdate');
    return $callbacks;
  }

  /**
   * @return bool
   */
  public function callbackOnBeforeUpdate() {
    $this->modified = time();
    return TRUE;
  }

  /**
   * @return bool
   */
  public function callbackOnBeforeInsert() {
    $this->modified = $this->created = time();
    return TRUE;
  }
}
