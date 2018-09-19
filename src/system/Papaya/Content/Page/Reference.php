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
namespace Papaya\Content\Page;

/**
 * Provide data encapsulation for the reference between two pages.
 *
 * The two pages of the reference have the same weight. On mapping the informations the object
 * will put the lower id into source and the higher into target.
 *
 * @property int $sourceId page id, smaller one
 * @property int $targetId page id, larger one
 * @property string $note - a small text describing the reference
 */
class Reference extends \Papaya\Database\Record {
  /**
   * Mapping fields
   *
   * @var array
   */
  protected $_fields = [
    'target_id' => 'topic_target_id',
    'source_id' => 'topic_source_id',
    'note' => 'topic_note'
  ];

  /**
   * References table name
   *
   * @var string
   */
  protected $_tableName = \Papaya\Content\Tables::PAGE_REFERENCES;

  /**
   * Create a multi field key object containg both page id properties
   *
   * @return \Papaya\Database\Interfaces\Key
   */
  protected function _createKey() {
    return new \Papaya\Database\Record\Key\Fields(
      $this,
      $this->_tableName,
      ['source_id', 'target_id']
    );
  }

  /**
   * Add a callback to the mapping to be used after mapping
   *
   * @return \Papaya\Database\Interfaces\Mapping
   */
  protected function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onAfterMapping = [
      $this, 'callbackSortPageIds'
    ];
    return $mapping;
  }

  /**
   * The callbacks sorts the page ids, to lower value is made the source id.
   *
   * @param object $context
   * @param int $mode
   * @param array $values
   * @param array $record
   *
   * @return array
   */
  public function callbackSortPageIds($context, $mode, $values, $record) {
    if (\Papaya\Database\Record\Mapping::PROPERTY_TO_FIELD == $mode) {
      $result = $record;
      if ((int)$record['topic_source_id'] > (int)$record['topic_target_id']) {
        $result['topic_target_id'] = $record['topic_source_id'];
        $result['topic_source_id'] = $record['topic_target_id'];
      }
    } else {
      $result = $values;
      if ((int)$values['source_id'] > (int)$values['target_id']) {
        $result['target_id'] = $values['source_id'];
        $result['source_id'] = $values['target_id'];
      }
    }
    return $result;
  }

  /**
   * Check if a callback exists
   *
   * @param int $sourceId
   * @param int $targetId
   *
   * @return bool
   */
  public function exists($sourceId, $targetId) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE topic_source_id = '%d'
               AND topic_target_id = '%d'";
    $parameters = [
      $this->getDatabaseAccess()->getTableName($this->_tableName),
      (int)$sourceId > (int)$targetId ? $targetId : $sourceId,
      (int)$sourceId > (int)$targetId ? $sourceId : $targetId
    ];
    if ($databaseResult = $this->getDatabaseAccess()->queryFmt($sql, $parameters)) {
      return $databaseResult->fetchField() > 0;
    }
    return FALSE;
  }
}
