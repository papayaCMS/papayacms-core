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
* Encapsulation for translated phrase (get text like system)
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentPhrase extends \PapayaDatabaseRecord {

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

  protected $_tableName = \PapayaContentTables::PHRASES;
  protected $_tableAlias = 'p';

  public function load($filter = NULL) {
    $fields = implode(', ', $this->mapping()->getFields());
    $databaseAccess = $this->getDatabaseAccess();
    if (is_array($filter) && isset($filter['language_id'])) {
      $languageId = (int)$filter['language_id'];
      unset($filter['language_id']);
    } else {
      $languageId = 0;
    }
    $sql = "SELECT $fields
              FROM %s AS p
              LEFT JOIN %s AS pt ON (pt.phrase_id = p.phrase_id AND pt.lng_id = '%d')";
    $sql .= \PapayaUtilString::escapeForPrintf(
      $this->_compileCondition($filter)
    );
    $parameters = array(
      $databaseAccess->getTableName($this->_tableName),
      $databaseAccess->getTableName(\PapayaContentTables::PHRASE_TRANSLATIONS),
      $languageId
    );
    return $this->_loadRecord($sql, $parameters);
  }

  public function addToGroup($title) {
    $group = $this->getGroup($title);
    if ($group->isLoaded()) {
      $groupId = $group->id;
    } else {
      $group->assign(
        array(
          'identifier' => strtolower(trim($title)),
          'title' => $title
        )
      );
      $groupId = $group->save();
    }
    $databaseAccess = $this->getDatabaseAccess();
    $linkTable = $databaseAccess->getTableName(\PapayaContentTables::PHRASE_GROUP_LINKS);
    $sql = "SELECT COUNT(*) FROM %s WHERE phrase_id = '%d' AND module_id = '%d'";
    $parameters = array($linkTable, $this->id, $groupId);
    if (
      ($result = $databaseAccess->queryFmt($sql, $parameters)) &&
      0 === $result->fetchField()
    ) {
      return FALSE !== $databaseAccess->insertRecord(
        $linkTable,
        NULL,
        array(
          'phrase_id' => $this->id,
          'module_id' => $groupId
        )
      );
    }
    return FALSE;
  }

  public function getGroup($title = NULL) {
    $identifier = strtolower(trim($title));
    $group = new \Papaya\Content\Phrase\Group();
    $group->papaya($this->papaya());
    $group->setDatabaseAccess($this->getDatabaseAccess());
    $group->activateLazyLoad(array('identifier' => $identifier));
    return $group;
  }

}
