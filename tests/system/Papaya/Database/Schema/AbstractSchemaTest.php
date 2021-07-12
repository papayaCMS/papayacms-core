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

namespace Papaya\Database\Schema {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Schema\Structure\FieldStructure;
  use Papaya\Database\Schema\Structure\IndexStructure;
  use Papaya\Database\Schema\Structure\TableStructure;
  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Schema\AbstractSchema
   */
  class AbstractSchemaTest extends TestCase {

    /**
     * @param string $expected
     * @param string $identifier
     * @param string $prefix
     * @testWith
     *   ["table", "table"]
     *   ["table", "TABLE"]
     *   ["prefix_table", "table", "prefix"]
     *   ["field name", "field name"]
     */
    public function testGetIdentifier($expected, $identifier, $prefix = '') {
      /** @var DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      $schema = new AbstractSchema_TestProxy($connection);
      $this->assertSame(
        $expected,
        $schema->getIdentifier($identifier, $prefix)
      );
    }

    /**
     * @param string $identifier
     * @param string $prefix
     * @testWith
     *   [""]
     *   ["foo.bar"]
     *   ["table", "foo.bar"]
     *   ["\"table\""]
     *   ["'table'"]
     */
    public function testGetIdentifierWithInvalidValuesExpectingException($identifier, $prefix = '') {
      /** @var DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      $schema = new AbstractSchema_TestProxy($connection);
      $this->expectException(\InvalidArgumentException::class);
      $schema->getIdentifier($identifier, $prefix);
    }

    /**
     * @param string $expected
     * @param string $identifier
     * @param string $prefix
     * @testWith
     *   ["`table_name`", "table_name"]
     *   ["`prefix_table_name`", "table_name", "prefix"]
     */
    public function testGetQuotedIdentifier($expected, $identifier, $prefix = '') {
      /** @var DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      $connection
        ->expects($this->once())
        ->method('quoteIdentifier')
        ->willReturnCallback(
          static function($name) { return "`{$name}`"; }
        );
      $schema = new AbstractSchema_TestProxy($connection);
      $this->assertSame(
        $expected, $schema->getQuotedIdentifier($identifier, $prefix)
      );
    }
  }

  class AbstractSchema_TestProxy extends AbstractSchema {

    public function getIdentifier($name, $prefix = '') {
      return parent::getIdentifier($name, $prefix);
    }

    public function getQuotedIdentifier($name, $prefix = '') {
      return parent::getQuotedIdentifier($name, $prefix);
    }

    public function getTables() {
    }

    public function describeTable($tableName, $tablePrefix = '') {
    }

    public function createTable(TableStructure $tableStructure, $tablePrefix = '') {
    }

    public function addField($tableName, FieldStructure $fieldStructure) {
    }

    public function changeField($tableName, FieldStructure $fieldStructure) {
    }

    public function dropField($tableName, $fieldName) {
    }

    public function addIndex($tableName, IndexStructure $indexStructure) {
    }

    public function changeIndex($tableName, IndexStructure $indexStructure, $dropCurrent = TRUE) {
    }

    public function dropIndex($tableName, $indexName) {
    }

    public function isFieldDifferent(FieldStructure $expectedStructure, FieldStructure $currentStructure) {
    }

    public function isIndexDifferent(IndexStructure $expectedStructure, IndexStructure $currentStructure) {
    }
  }
}
