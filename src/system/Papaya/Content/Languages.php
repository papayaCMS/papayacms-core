<?php
/**
* Provide data encapsulation for the languages list.
*
* The list does not contain all detail data, it is for list outputs etc. To get the full data
* use {@see PapayaContentPageTranslation}.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Content
* @version $Id: Languages.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Provide data encapsulation for the languages list.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentLanguages extends PapayaDatabaseRecords {

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
  protected $_fields = array(
    'id' => 'lng_id',
    'identifier' => 'lng_ident',
    'code' => 'lng_short',
    'title' => 'lng_title',
    'image' => 'lng_glyph',
    'is_interface' => 'is_interface_lng',
    'is_content' => 'is_content_lng'
  );

  /**
  * Languages table
  *
  * @var string
  */
  protected $_tableName = PapayaContentTables::LANGUAGES;

  /**
  * A mapping of the unique language codes (de-DE) to the internal id
  *
  * @var array(string=>string,...)
  */
  protected $_mapCodes = array();

  /**
  * A mapping of the unique language identifiers (de) to the internal id
  *
  * @var array(string=>string,...)
  */
  protected $_mapIdentifiers = array();

  /**
  * load languages from database, this can be filtered by usage
  *
  * @param integer $usageFilter
  * @return boolean
  */
  public function load($usageFilter = self::FILTER_NONE) {
    $databaseAccess = $this->getDatabaseAccess();
    $filters = array(
       self::FILTER_NONE => '',
       self::FILTER_IS_CONTENT => " WHERE is_content_lng = 1 ",
       self::FILTER_IS_INTERFACE => " WHERE is_interface_lng = 1 "
    );
    $filter = $filters[$usageFilter];
    $sql = "SELECT lng_id, lng_ident, lng_short,
                   lng_title, lng_glyph,
                   is_interface_lng, is_content_lng
              FROM %s
              $filter
             ORDER BY lng_title";
    $parameters = array(
      $databaseAccess->getTableName($this->_tableName)
    );
    if ($result = $this->_loadRecords($sql, $parameters, NULL, NULL, 'id')) {
      $this->_mapCodes = array();
      $this->_mapIdentifiers = array();
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
   * @return PapayaContentLanguage
   */
  public function getLanguage($language) {
    if (is_int($language) || preg_match('(^\\d+$)D', $language)) {
      $id = (int)$language;
    } else {
      $id = 0;
    }
    if ($id > 0) {
      $result = new PapayaContentLanguage();
      $result->papaya($this->papaya());
      $result->setDatabaseAccess($this->getDatabaseAccess());
      if (isset($this[$id])) {
        $result->assign($this[$id]);
        return $result;
      } elseif ($result->load($id)) {
        return $result;
      }
    } elseif (preg_match('(^[a-zA-Z\\d]+-[a-zA-Z\\d]+$)', $language)) {
      return $this->getLanguageByCode($language);
    } elseif (preg_match('(^[a-zA-Z\\d]+$)', $language)) {
      return $this->getLanguageByIdentifier($language);
    }
    return NULL;
  }

  /**
  * Create a new language record object and assign the data from the list if available.
  *
  * @param string $code
  * @return PapayaContentLanguage
  */
  public function getLanguageByCode($code) {
    $result = new PapayaContentLanguage();
    if (isset($this->_mapCodes[$code]) &&
        isset($this[$this->_mapCodes[$code]])) {
      $result->assign($this[$this->_mapCodes[$code]]);
      return $result;
    }
    return NULL;
  }

  /**
  * Create a new language record object and assign the data from the list if available.
  *
  * @param string $identifier
  * @return PapayaContentLanguage
  */
  public function getLanguageByIdentifier($identifier) {
    $result = new PapayaContentLanguage();
    if (isset($this->_mapIdentifiers[$identifier]) &&
        isset($this[$this->_mapIdentifiers[$identifier]])) {
      $result->assign($this[$this->_mapIdentifiers[$identifier]]);
      return $result;
    }
    return NULL;
  }

  /**
   * Get the language code by the id.
   *
   * @param int $languageId
   * @return string|NULL
   */
  public function getIdentiferById($languageId) {
    if ($language = $this->getLanguage($languageId)) {
      return $language->identifier;
    }
    return NULL;
  }
}