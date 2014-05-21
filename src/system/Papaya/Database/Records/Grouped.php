<?php
/**
* Papaya Database Records Grouped - reads records from the database and stores them grouped.
*
* @copyright 2013 by papaya Software GmbH - All rights reserved.
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
* @subpackage Database
* @version $Id: Grouped.php 38854 2013-09-30 10:05:46Z weinert $
*/

/**
* Papaya Database Records Grouped - reads records from the database and stores them grouped.
*
* @package Papaya-Library
* @subpackage Database
*/
abstract class PapayaDatabaseRecordsGrouped extends PapayaDatabaseRecordsLazy {

  /**
  * identifing a child - the detail record identifier
  *
  * @var array
  */
  protected $_identifierProperties = array();

  /**
  * identifing a group - the group record identifier
  *
  * @var array
  */
  protected $_groupIdentifierProperties = array('group_id');

  /**
   * Load the records, read them from database and create the children buffer.
   *
   * @param string $sql
   * @param array $parameters
   * @param integer|NULL $limit
   * @param integer|NULL $offset
   * @param array $idProperties
   * @throws LogicException
   * @return bool
   */
  protected function _loadRecords($sql, $parameters, $limit, $offset, $idProperties = array()) {
    $this->_records = array();
    if ($this->_loadSql($sql, $parameters, $limit, $offset)) {
      foreach ($this->getResultIterator() as $values) {
        $identifier = $this->getIdentifier($values, $idProperties);
        $groupIdentifier = $this->getIdentifier($values, $this->_groupIdentifierProperties);
        if (empty($groupIdentifier) &&
            $groupIdentifier !== '0' &&
            $groupIdentifier !== 0) {
          throw new LogicException(
            'Properties needed to group records.'
          );
        }
        if (!isset($this->_records[$groupIdentifier])) {
          $this->_records[$groupIdentifier] = new ArrayObject();
        }
        if (isset($identifier)) {
          $this->_records[$groupIdentifier][$identifier] = $values;
        } else {
          $this->_records[$groupIdentifier][] = $values;
        }
      }
      return TRUE;
    }
    return FALSE;
  }
}