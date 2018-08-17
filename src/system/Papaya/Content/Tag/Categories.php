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

namespace Papaya\Content\Tag;

/**
 *
 */


/**
 * This object loads page data by different conditions.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Categories extends \Papaya\Database\Records\Tree {

  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    'id' => 'c.category_id',
    'parent_id' => 'c.parent_id',
    'ancestors' => 'c.parent_path',
    'language_id' => 'ct.lng_id',
    'title' => 'ct.category_title',
    'description' => 'ct.category_description'
  );

  protected $_orderByProperties = array(
    'title' => \Papaya\Database\Interfaces\Order::ASCENDING
  );

  /**
   * Load records from the defined table. This method can be overloaded to define an own sql.
   *
   * @param mixed $filter If it is an scalar the value will be used for the id property.
   * @param integer|NULL $limit
   * @param integer|NULL $offset
   * @return bool
   */
  public function load($filter = NULL, $limit = NULL, $offset = NULL) {
    $languageId = 0;
    if (isset($filter['language_id'])) {
      $languageId = (int)$filter['language_id'];
      unset($filter['language_id']);
    }
    $fields = implode(', ', $this->mapping()->getFields());
    $sql =
      "SELECT $fields 
         FROM %s AS c 
         LEFT OUTER JOIN %s as ct  ON (ct.category_id = c.category_id AND ct.lng_id = '%d') ";
    $sql .= \Papaya\Utility\Text::escapeForPrintf(
      $this->_compileCondition($filter).$this->_compileOrderBy()
    );
    $parameters = array(
      $this->getDatabaseAccess()->getTableName(\Papaya\Content\Tables::TAG_CATEGORY),
      $this->getDatabaseAccess()->getTableName(\Papaya\Content\Tables::TAG_CATEGORY_TRANSLATIONS),
      $languageId
    );
    return $this->_loadRecords($sql, $parameters, $limit, $offset, array('id'));
  }
}
