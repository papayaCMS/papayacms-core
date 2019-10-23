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
  use Papaya\Test\TestCase;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Condition\Fulltext
   * @covers \Papaya\Database\Condition\Fulltext\Boolean
   */
  class BooleanTest extends TestCase {

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
        (string)new Boolean(new Group($databaseAccess), $field, $searchFor)
      ) ;
    }

    public static function getConditionData() {
      return [
        ['(MATCH (col) AGAINST (\'\' IN BOOLEAN MODE))', 'col', ''],
        ["(MATCH (col) AGAINST (' ( +search) ' IN BOOLEAN MODE))", 'col', 'search'],
        ["(MATCH (col) AGAINST (' ( -search) ' IN BOOLEAN MODE))", 'col', '-search'],
        ["(MATCH (col) AGAINST (' ( +\"search for\") ' IN BOOLEAN MODE))", 'col', '"search for"'],
        ["(MATCH (col) AGAINST (' ( -\"search for\") ' IN BOOLEAN MODE))", 'col', '-"search for"'],
        ["(MATCH (col) AGAINST (' ( +search +for) ' IN BOOLEAN MODE))", 'col', 'search for'],
        ["(MATCH (col) AGAINST (' ( +search +for) ' IN BOOLEAN MODE))", 'col', 'search and for'],
        ["(MATCH (col1,col2) AGAINST (' ( +search) ' IN BOOLEAN MODE))", ['col1', 'col2'], 'search'],
        ["(MATCH (table1.col1) AGAINST (' ( +search) ' IN BOOLEAN MODE))(MATCH (table2.col2) AGAINST (' ( +search) ' IN BOOLEAN MODE))", ['table1.col1', 'table2.col2'], 'search'],
        ["(MATCH (col) AGAINST (' ( ( +search)  )' IN BOOLEAN MODE))", 'col', '(search'],
      ];
    }
  }
}
