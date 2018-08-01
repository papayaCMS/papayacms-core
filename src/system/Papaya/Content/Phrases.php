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
 * Encapsulation for translated phrases (get text like system)
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Phrases extends \Papaya\Database\Records {

  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    'id' => 'p.phrase_id',
    'identifier' => 'p.phrase_text_lower',
    'text' => 'p.phrase_text',
    'translation' => 'pt.translation',
    'language_id' => 'pt.lng_id'
  );

  protected $_itemClass = Phrase::class;

  public function load($filter = NULL, $limit = NULL, $offset = NULL) {
    $fields = implode(', ', $this->mapping()->getFields());
    $databaseAccess = $this->getDatabaseAccess();
    if (isset($filter['group'])) {
      $group = $filter['group'];
      unset($filter['group']);
      $sql = "SELECT $fields
                FROM (%s AS p, %s AS g, %s AS grel)
                LEFT JOIN %s AS pt ON (pt.phrase_id = p.phrase_id AND pt.lng_id = '%d')
               WHERE g.module_title_lower = '%s'
                 AND grel.module_id = g.module_id
                 AND p.phrase_id = grel.phrase_id";
      $sql .= \Papaya\Utility\Text::escapeForPrintf(
        $this->_compileCondition($filter, ' AND ').$this->_compileOrderBy()
      );
      $parameters = array(
        $databaseAccess->getTableName(\Papaya\Content\Tables::PHRASES),
        $databaseAccess->getTableName(\Papaya\Content\Tables::PHRASE_GROUPS),
        $databaseAccess->getTableName(\Papaya\Content\Tables::PHRASE_GROUP_LINKS),
        $databaseAccess->getTableName(\Papaya\Content\Tables::PHRASE_TRANSLATIONS),
        \Papaya\Utility\Arrays::get($filter, 'language_id', 0),
        $group
      );
    } else {
      $sql = "SELECT $fields
                FROM %s AS p
                LEFT JOIN %s AS pt ON (pt.phrase_id = p.phrase_id AND pt.lng_id = '%d')";
      $sql .= \Papaya\Utility\Text::escapeForPrintf(
        $this->_compileCondition($filter).$this->_compileOrderBy()
      );
      $parameters = array(
        $databaseAccess->getTableName(\Papaya\Content\Tables::PHRASES),
        $databaseAccess->getTableName(\Papaya\Content\Tables::PHRASE_TRANSLATIONS),
        \Papaya\Utility\Arrays::get($filter, 'language_id', 0)
      );
    }
    return $this->_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
  }
}
