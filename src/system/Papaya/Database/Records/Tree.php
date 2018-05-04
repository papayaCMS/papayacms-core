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
* Papaya Database Records Tree - reads an parent child tree from database.
*
* @package Papaya-Library
* @subpackage Database
*/
abstract class PapayaDatabaseRecordsTree extends PapayaDatabaseRecordsLazy {

  /**
  * identifing a record - the child identifier
  *
  * @var array
  */
  protected $_identifierProperties = array('id');

  /**
  * identifing a parent record - the parent identifier
  *
  * @var array
  */
  protected $_parentIdentifierProperties = array('parent_id');

  /**
  * An buffer for the children of each parent
  *
  * @var array
  */
  protected $_children = array();

  /**
   * Load the records, read them from database and create the children buffer.
   *
   * @param string $sql
   * @param array $parameters
   * @param integer|NULL $limit
   * @param integer|NULL $offset
   * @param array $idProperties
   * @throws \LogicException
   * @return bool
   */
  protected function _loadRecords($sql, $parameters, $limit, $offset, $idProperties = array()) {
    $this->_children = array();
    $this->_records = array();
    if ($this->_loadSql($sql, $parameters, $limit, $offset)) {
      foreach ($this->getResultIterator() as $values) {
        $identifier = $this->getIdentifier($values, $idProperties);
        $parentIdentifier = $this->getIdentifier($values, $this->_parentIdentifierProperties);
        if (empty($parentIdentifier)) {
          $parentIdentifier = 0;
        }
        if (isset($identifier)) {
          $this->_records[$identifier] = $values;
          $this->_children[$parentIdentifier][] = $identifier;
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
  * @return \PapayaIteratorTreeChildren
  */
  public function getIterator() {
    return new \PapayaIteratorTreeChildren($this->_records, $this->_children);
  }
}
