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
namespace Papaya\CMS\Content;

use Papaya\Database;
use Papaya\Utility;

/**
 * Encapsulation for translated phrases (get text like system)
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @method Phrase getItem($filter = NULL)
 */
class Phrases extends Database\Records {
  /**
   * Map field names to more convenient property names
   *
   * @var string[]
   */
  protected $_fields = [
    'id' => 'p.phrase_id',
    'identifier' => 'p.phrase_text_lower',
    'text' => 'p.phrase_text',
    'translation' => 'pt.translation',
    'language_id' => 'pt.lng_id'
  ];

  protected $_itemClass = Phrase::class;

  public function load($filter = NULL, $limit = NULL, $offset = NULL) {
    $fields = \implode(', ', $this->mapping()->getFields());
    $databaseAccess = $this->getDatabaseAccess();
    if (isset($filter['group'])) {
      $group = $filter['group'];
      unset($filter['group']);
      $sql = "SELECT $fields
                FROM %s AS p
                INNER JOIN %s AS grel ON (grel.phrase_id = p.phrase_id)
                INNER JOIN %s AS g ON (g.module_id = grel.module_id)
                LEFT OUTER JOIN %s AS pt ON ((pt.phrase_id = p.phrase_id) AND (pt.lng_id = '%d'))
               WHERE g.module_title_lower = '%s'";
      $sql .= Utility\Text::escapeForPrintf(
        $this->_compileCondition($filter, ' AND ').$this->_compileOrderBy()
      );
      $parameters = [
        $databaseAccess->getTableName(Tables::PHRASES),
        $databaseAccess->getTableName(Tables::PHRASE_GROUP_LINKS),
        $databaseAccess->getTableName(Tables::PHRASE_GROUPS),
        $databaseAccess->getTableName(Tables::PHRASE_TRANSLATIONS),
        Utility\Arrays::get($filter, 'language_id', 0),
        $group
      ];
    } else {
      $sql = "SELECT $fields
                FROM %s AS p
                LEFT JOIN %s AS pt ON (pt.phrase_id = p.phrase_id AND pt.lng_id = '%d')";
      $sql .= Utility\Text::escapeForPrintf(
        $this->_compileCondition($filter).$this->_compileOrderBy()
      );
      $parameters = [
        $databaseAccess->getTableName(Tables::PHRASES),
        $databaseAccess->getTableName(Tables::PHRASE_TRANSLATIONS),
        Utility\Arrays::get($filter, 'language_id', 0)
      ];
    }
    return $this->_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
  }
}
