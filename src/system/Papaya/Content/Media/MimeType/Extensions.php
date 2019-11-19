<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Content\Media\MimeType {

  use Papaya\Content\Tables as ContentTables;
  use Papaya\Database\Interfaces\Order as DatabaseOrder;

  class Extensions extends \Papaya\Database\Records\Lazy {

    protected $_fields = [
      'id' => 'mimetype_id',
      'extension' => 'mimetype_extension'
    ];

    protected $_tableName = ContentTables::MEDIA_MIMETYPE_EXTENSIONS;
    private $_tableNameMimeTypes = ContentTables::MEDIA_MIMETYPES;

    protected $_orderByProperties = [
      'extension' => DatabaseOrder::ASCENDING,
      'id' => DatabaseOrder::ASCENDING,
    ];

    /**
     * @param $typeId
     * @param array $extensions
     * @return bool
     * @throws ExtensionsConflict
     */
    public function update($typeId, array $extensions) {
      $databaseAccess = $this->getDatabaseAccess();
      $statement = $databaseAccess->prepare(
        /** @lang AnsiSQL */
        'SELECT extensions.mimetype_id type_id, extensions.mimetype_extension extension, types.mimetype as mimetype
          FROM :extensions_table AS extensions, :types_table AS types
         WHERE (types.mimetype_id = extensions.mimetype_id) 
           AND ((extensions.mimetype_extension IN :extensions) OR (extensions.mimetype_id = :type_id))'
      );
      $statement->addTableName('extensions_table', $this->_tableName);
      $statement->addTableName('types_table', $this->_tableNameMimeTypes);
      $statement->addInt('type_id', $typeId);
      $statement->addStringList('extensions', $extensions);
      $toRemove = [];
      $toAdd = [];
      $inUse = [];
      $inConflict = [];
      if ($databaseResult = $statement->execute()) {
        foreach ($databaseResult as $record) {
          // is current mime type
          if ((int)$record['type_id'] === (int)$typeId) {
            if (!in_array($record['extension'], $extensions, TRUE)) {
              $toRemove[] = $record['extension'];
            } else {
              $inUse[] = $record['extension'];
            }
          } else {
            if (!isset($inConflict[$record['mimetype']])) {
              $inConflict[$record['mimetype']] = [];
            }
            $inConflict[$record['mimetype']][] = $record['extension'];
          }
        }
        $toAdd = array_diff($extensions, $inUse);
      }
      if (count($inConflict) > 0) {
        throw new ExtensionsConflict($inConflict);
      }
      $executed = TRUE;
      if ($executed && count($toRemove) > 0) {
        $statement = $databaseAccess->prepare(
        /** @lang AnsiSQL */
          'DELETE FROM :extensions_table WHERE mimetype_extension IN :extensions'
        );
        $statement->addTableName('extensions_table', $this->_tableName);
        $statement->addStringList('extensions', $toRemove);
        $executed = FALSE !== $statement->execute();
      }
      if ($executed && count($toAdd) > 0) {
        $executed = FALSE !== $databaseAccess->insert(
          $databaseAccess->getTableName($this->_tableName),
          array_map(
            static function($extension) use ($typeId) {
              return [
                'mimetype_id' => $typeId,
                'mimetype_extension' => $extension
              ];
            },
            $toAdd
          )
        );
      }
      return $executed;
    }

    public function delete($typeId) {
      $databaseAccess = $this->getDatabaseAccess();
      $statement = $databaseAccess->prepare(
        'DELETE FROM :extensions_table WHERE mimetype_id = :type_id'
      );
      $statement->addTableName('extensions_table', $this->_tableName);
      $statement->addInt('type_id', $typeId);
      return FALSE !== $statement->execute();
    }
  }
}
