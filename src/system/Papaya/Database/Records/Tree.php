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
namespace Papaya\Database\Records;

use Papaya\Database;
use Papaya\Iterator;

/**
 * Papaya Database Records Tree - reads an parent child tree from database.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
abstract class Tree extends Lazy {
  /**
   * identifying a record - the child identifier
   *
   * @var array
   */
  protected $_identifierProperties = ['id'];

  /**
   * identifying a parent record - the parent identifier
   *
   * @var array
   */
  protected $_parentIdentifierProperties = ['parent_id'];

  /**
   * An buffer for the children of each parent
   *
   * @var array
   */
  protected $_children = [];

  private $_rootIdentifiers = [];

  /**
   * Load the records, read them from database and create the children buffer.
   *
   * @param string $sql
   * @param array $parameters
   * @param int|null $limit
   * @param int|null $offset
   * @param array $idProperties
   *
   * @throws \LogicException
   *
   * @return bool
   */
  protected function _loadRecords($sql, $parameters, $limit, $offset, $idProperties = []) {
    $this->_children = [];
    $this->_records = [];
    if ($this->_loadSql($sql, $parameters, $limit, $offset)) {
      $this->_rootIdentifiers = [];
      foreach ($this->getResultIterator() as $values) {
        $identifier = $this->getIdentifier($values, $idProperties);
        $parentIdentifier = $this->getIdentifier($values, $this->_parentIdentifierProperties);
        if (empty($parentIdentifier)) {
          $parentIdentifier = 0;
        }
        if (NULL !== $identifier) {
          $this->_records[$identifier] = $values;
          $this->_children[$parentIdentifier][] = $identifier;
          if (!isset($this->_records[$parentIdentifier])) {
            $this->_rootIdentifiers[$parentIdentifier] = TRUE;
          } elseif (isset($this->_rootIdentifiers[$parentIdentifier])) {
            unset($this->_rootIdentifiers[$parentIdentifier]);
          }
        } else {
          throw new \LogicException(
            'Identifier properties needed to link children to parents.'
          );
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Return a tree iterator for the loaded records starting with the children of the virtual
   * element zero.
   *
   * @return \Iterator
   */
  public function getIterator() {
    $this->lazyLoad();
    $identifiers = array_keys($this->_rootIdentifiers);
    if (count($identifiers) < 1) {
      return new Iterator\Tree\Children($this->_records, $this->_children, 0);
    }
    return new Iterator\Tree\Children($this->_records, $this->_children, $identifiers);
  }
}
