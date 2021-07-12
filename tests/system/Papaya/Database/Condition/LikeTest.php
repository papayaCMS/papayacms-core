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

  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Condition\Like
   */
  class LikeTest extends TestCase {

    public function testLike() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->any())
        ->method('getSQLCondition')
        ->with(['field' => ['%value%']], NULL, 'LIKE')
        ->willReturn('field LIKE \'%value%\'');

      $like = new Like(new Group($databaseAccess), 'field', '*value*');
      $this->assertSame(
        ' (field LIKE \'%value%\') ',
        $like->getSql()
      ) ;
    }

    public function testLikeWithMultipleFields() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->any())
        ->method('getSQLCondition')
        ->withConsecutive(
          [['field1' => ['%value%']], NULL, 'LIKE'],
          [['field2' => ['%value%']], NULL, 'LIKE']
        )
        ->willReturnOnConsecutiveCalls('field1 LIKE \'%value%\'', 'field2 LIKE \'%value%\'');

      $like = new Like(new Group($databaseAccess), ['field1', 'field2'], '*value*');
      $this->assertSame(
        ' (field1 LIKE \'%value%\' OR field2 LIKE \'%value%\') ',
        $like->getSql()
      ) ;
    }

    public function testLikeWithoutPlaceholders() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->any())
        ->method('getSQLCondition')
        ->with(['field' => ['value']], NULL, '=')
        ->willReturn('field = \'value\'');

      $like = new Like(new Group($databaseAccess), 'field', 'value');
      $this->assertSame(
        ' (field = \'value\') ',
        $like->getSql()
      ) ;
    }

    public function testLikeWithInvalidDataExpectingException() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $like = new Like(new Group($databaseAccess), '');

      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('Can not generate condition, provided name was empty.');

      $like->getSql();
    }

    public function testLikeWithInvalidDataSilencedException() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $like = new Like(new Group($databaseAccess), '');

      $this->assertSame('', $like->getSql(TRUE));
    }
  }
}
