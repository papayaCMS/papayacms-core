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

namespace Papaya\Content;

/**
 * Provide data encapsulation for the languages list.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Languages extends \Papaya\Database\Records {
  /**
   * All languages - filter disabled
   */
  const FILTER_NONE = 0;

  /**
   * Content languages filter
   */
  const FILTER_IS_CONTENT = 1;

  /**
   * Interface languages filter
   */
  const FILTER_IS_INTERFACE = 2;

  /**
   * Map field names to value identfiers
   *
   * @var array
   */
  protected $_fields = [
    'id' => 'lng_id',
    'identifier' => 'lng_ident',
    'code' => 'lng_short',
    'title' => 'lng_title',
    'image' => 'lng_glyph',
    'is_interface' => 'is_interface_lng',
    'is_content' => 'is_content_lng'
  ];

  /**
   * Languages table
   *
   * @var string
   */
  protected $_tableName = \Papaya\Content\Tables::LANGUAGES;

  /**
   * @var string
   */
  protected $_identifierProperties = ['id'];

  /**
   * A mapping of the unique language codes (de-DE) to the internal id
   *
   * @var array(string=>string,...)
   */
  protected $_mapCodes = [];

  /**
   * A mapping of the unique language identifiers (de) to the internal id
   *
   * @var array(string=>string,...)
   */
  protected $_mapIdentifiers = [];

  /**
   * load languages from database, this can be filtered by usage
   *
   * @param int $usageFilter
   * @return bool
   */
  public function loadByUsage($usageFilter = self::FILTER_NONE) {
    $filter = [];
    switch ($usageFilter) {
      case self::FILTER_IS_CONTENT :
        $filter['is_content'] = TRUE;
      break;
      case self::FILTER_IS_INTERFACE :
        $filter['is_interface'] = TRUE;
      break;
    }
    return $this->load($filter);
  }

  /**
   * load languages from database
   *
   * @param array $filter
   * @param null $limit
   * @param int $offset
   * @return bool
   */
  public function load($filter = [], $limit = NULL, $offset = 0) {
    $this->_mapCodes = [];
    $this->_mapIdentifiers = [];
    if ($result = parent::load($filter, $limit, $offset)) {
      foreach ($this as $language) {
        $this->_mapCodes[$language['code']] = $language['id'];
        $this->_mapIdentifiers[$language['identifier']] = $language['id'];
      }
    }
    return $result;
  }

  /**
   * Create a new language record object and assign the data from the list if available.
   * If the language is an id and not found in the list, the load method of the record
   * object is called.
   *
   * @param string|int $language
   * @return \Papaya\Content\Language
   */
  public function getLanguage($language, $usageFilter = self::FILTER_NONE) {
    if (\is_int($language) || \preg_match('(^\\d+$)D', $language)) {
      $id = (int)$language;
    } else {
      $id = 0;
    }
    $result = NULL;
    if ($id > 0) {
      $result = new \Papaya\Content\Language();
      $result->papaya($this->papaya());
      $result->setDatabaseAccess($this->getDatabaseAccess());
      if (isset($this[$id])) {
        $result->assign($this[$id]);
      } elseif (!$result->load($id)) {
        $result = NULL;
      }
    } elseif (\preg_match('(^[a-zA-Z\\d]+-[a-zA-Z\\d]+$)', $language)) {
      $result = $this->getLanguageByCode($language);
    } elseif (\preg_match('(^[a-zA-Z\\d]+$)', $language)) {
      $result = $this->getLanguageByIdentifier($language);
    }
    if ($result) {
      switch ($usageFilter) {
        case self::FILTER_IS_CONTENT:
          if (!$result['is_content']) {
            return;
          }
        break;
        case self::FILTER_IS_INTERFACE:
          if (!$result['is_interface']) {
            return;
          }
        break;
      }
    }
    return $result;
  }

  /**
   * Create a new language record object and assign the data from the list if available.
   *
   * @param string $code
   * @return \Papaya\Content\Language
   */
  public function getLanguageByCode($code) {
    $result = new \Papaya\Content\Language();
    if (isset($this->_mapCodes[$code]) &&
      isset($this[$this->_mapCodes[$code]])) {
      $result->assign($this[$this->_mapCodes[$code]]);
      return $result;
    }
    return;
  }

  /**
   * Create a new language record object and assign the data from the list if available.
   *
   * @param string $identifier
   * @return \Papaya\Content\Language
   */
  public function getLanguageByIdentifier($identifier) {
    $result = new \Papaya\Content\Language();
    if (isset($this->_mapIdentifiers[$identifier]) &&
      isset($this[$this->_mapIdentifiers[$identifier]])) {
      $result->assign($this[$this->_mapIdentifiers[$identifier]]);
      return $result;
    }
    return;
  }

  /**
   * Get the language code by the id.
   *
   * @param int $languageId
   * @return string|null
   */
  public function getIdentiferById($languageId) {
    if ($language = $this->getLanguage($languageId)) {
      return $language->identifier;
    }
    return;
  }

  /**
   * @return \Papaya\Content\Language
   */
  public function getDefault() {
    if (\count($this->_records) > 0 && ($id = \array_keys($this->_records)[0])) {
      return $this->getLanguage($id);
    } else {
      return;
    }
  }
}
