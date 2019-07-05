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
  use Papaya\Test\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Schema\AbstractSchema
   */
  class AbstractSchemaTest extends TestCase {

    /**
     * @param string $expected
     * @param string $tableName
     * @param string $prefix
     * @testWith
     *   ["table", "table"]
     *   ["table", "TABLE"]
     *   ["prefix_table", "table", "prefix"]
     *   ["field name", "field name"]
     */
    public function testGetIdentifier($expected, $tableName, $prefix = '') {
      /** @var DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      $schema = new AbstractSchema_TestProxy($connection);
      $this->assertSame(
        $expected,
        $schema->getIdentifier($tableName, $prefix)
      );
    }

    /**
     * @param string $tableName
     * @param string $prefix
     * @testWith
     *   [""]
     *   ["foo.bar"]
     *   ["table", "foo.bar"]
     *   ["\"table\""]
     *   ["'table'"]
     */
    public function testGetIdentifierWithInvalidValuesExpectingException($tableName, $prefix = '') {
      /** @var DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      $schema = new AbstractSchema_TestProxy($connection);
      $this->expectException(\InvalidArgumentException::class);
      $schema->getIdentifier($tableName, $prefix);
    }
  }

  class AbstractSchema_TestProxy extends AbstractSchema {

    public function getIdentifier($name, $prefix = '') {
      return parent::getIdentifier($name, $prefix);
    }

    public function getTables() {
    }

    public function describeTable($tableName, $tablePrefix = '') {
    }

    public function createTable(array $tableStructure, $tablePrefix = '') {
    }

    public function addField($tableName, array $fieldStructure) {
    }

    public function changeField($tableName, array $fieldStructure) {
    }

    public function dropField($tableName, $fieldName) {
    }

    public function addIndex($tableName, array $indexStructure) {
    }

    public function changeIndex($tableName, array $indexStructure, $dropCurrent = TRUE) {
    }

    public function dropIndex($tableName, $indexName) {
    }

    public function isFieldDifferent(array $expectedStructure, array $currentStructure) {
    }

    public function isIndexDifferent(array $expectedStructure, array $currentStructure) {
    }
  }
}
