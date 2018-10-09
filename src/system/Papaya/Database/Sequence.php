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
namespace Papaya\Database;

use Papaya\Utility;

/**
 * Papaya Database Sequence, handles manual client side sequence
 *
 * To use this class you have to define a child that implements \the abstract createId() method.
 *
 * Usage:
 *   $sequence = new \Papaya\Database\Sequence\Sample(
 *     'table_name', 'field_name'
 *   );
 *   $newId = $sequence->next();
 *
 * The class requests new ids from the generator and checks them agains the database table.
 * It does not insert a record.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
abstract class Sequence extends BaseObject {
  /**
   * Database table name
   *
   * @var string
   */
  protected $_table = '';

  /**
   * Identifier table column name
   *
   * @var string
   */
  protected $_field = '';

  /**
   * Create a single randomized identifier string
   *
   * @return string
   */
  abstract public function create();

  /**
   * Initialize object and set table and field properties
   *
   * @param string $table
   * @param string $field
   *
   * @throws \InvalidArgumentException
   */
  public function __construct($table, $field) {
    if (empty($table)) {
      throw new \InvalidArgumentException(
        'No table name provided.'
      );
    }
    if (empty($field)) {
      throw new \InvalidArgumentException(
        'No field name provided.'
      );
    }
    $this->_table = $table;
    $this->_field = $field;
  }

  /**
   * Return the next sequence identifier
   *
   * @return string|false
   */
  public function next() {
    $ids = [];
    while (empty($ids)) {
      $ids = $this->createIdentifiers(10);
      $ids = $this->checkIdentifiers($ids);
      if (FALSE === $ids) {
        return FALSE;
      }
    }
    return \reset($ids);
  }

  /**
   * Create a several ids at once
   *
   * @param int $count
   *
   * @return array
   */
  protected function createIdentifiers($count) {
    $result = [];
    for ($i = 0; $i < $count; $i++) {
      $id = $this->create();
      if (!empty($id)) {
        $result[] = $id;
      }
    }
    return $result;
  }

  /**
   * Check identifiers agains table, return only identifiers not already used.
   *
   * @param array $identifiers
   *
   * @throws \InvalidArgumentException
   *
   * @return array|false $identifiers
   */
  protected function checkIdentifiers(array $identifiers) {
    $identifiers = \array_values($identifiers);
    $filter = Utility\Text::escapeForPrintf(
      $this->databaseGetSqlCondition($this->_field, $identifiers)
    );
    if (empty($filter)) {
      throw new \InvalidArgumentException(
        'Please provide one or more sequence ids to check.'
      );
    }
    $sql = "SELECT %s FROM %s WHERE $filter";
    $parameters = [$this->_field, $this->_table];
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      $found = [];
      while ($row = $res->fetchRow()) {
        $found[] = $row[0];
      }
      return \array_diff($identifiers, $found);
    }
    return FALSE;
  }
}
