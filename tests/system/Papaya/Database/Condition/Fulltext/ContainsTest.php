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

namespace Papaya\Database\Condition\Fulltext {

  use Papaya\Database\Condition\Group;
  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Condition\Fulltext
   * @covers \Papaya\Database\Condition\Fulltext\Contains
   */
  class ContainsTest extends TestCase {

    /**
     * @param $expected
     * @param $field
     * @param $searchFor
     * @dataProvider getConditionData
     */
    public function testConditionToString($expected, $field, $searchFor) {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $this->assertSame(
        $expected,
        (string)new Contains(new Group($databaseAccess), $field, $searchFor)
      ) ;
    }

    public static function getConditionData() {
      return [
        ['', 'col', ''],
        ["(((col LIKE '%search%')))", 'col', 'search'],
        ["((NOT((col LIKE '%search%'))))", 'col', '-search'],
        ["(((col LIKE '%search%'))\n AND \n((col LIKE '%for%')))", 'col', 'search for'],
        ["(((col LIKE '%search%'))\n AND((col LIKE '%for%')))", 'col', 'search and for'],
        ["(((col1 LIKE '%search%') OR (col2 LIKE '%search%')))", ['col1', 'col2'], 'search'],
        ["(((table1.col1 LIKE '%search%') OR (table2.col2 LIKE '%search%')))", ['table1.col1', 'table2.col2'], 'search'],
        ["((((col LIKE '%search%')))\n)", 'col', '(search'],
      ];
    }
  }
}
