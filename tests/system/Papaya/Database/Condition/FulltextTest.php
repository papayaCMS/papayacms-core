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

namespace Papaya\Database\Condition {

  use Papaya\Parser\Search\Text as SearchTextParser;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Condition\Fulltext
   */
  class FullTextTest extends \Papaya\TestFramework\TestCase {

    public function testGetSqlWithInvalidFieldNameExpectingException() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->never())
        ->method('getSqlCondition');

      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->any())
        ->method('getDatabaseAccess')
        ->willReturn($databaseAccess);

      $condition = new Fulltext_TestProxy($group, '', '');
      $this->expectException(\LogicException::class);
      $condition->getSql();
    }

    public function testGetSqlWithExceptionInSilentMode() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->never())
        ->method('getSqlCondition');

      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->any())
        ->method('getDatabaseAccess')
        ->willReturn($databaseAccess);

      $condition = new Fulltext_TestProxy($group, '', '');

      $this->assertEquals('', $condition->getSql(TRUE));
    }
  }

  class FullText_TestProxy extends Fulltext {

    public function mapFieldName($value) {
      return parent::mapFieldName($value);
    }

    /**
     * @param SearchTextParser $tokens
     * @param array|\Traversable $fields
     *
     * @return string
     */
    protected function getFullTextCondition(SearchTextParser $tokens, array $fields) {
      return '';
    }
  }
}
