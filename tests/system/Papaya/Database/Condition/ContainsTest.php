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

  use Papaya\Test\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Condition\Contains
   */
  class ContainsTest extends TestCase {

    public function testContains() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->any())
        ->method('getSQLCondition')
        ->with(['field' => '%value%'], NULL, 'LIKE')
        ->willReturn('field LIKE \'%value%\'');

      $like = new Contains(new Group($databaseAccess), 'field', '*value*');
      $this->assertSame(
        'field LIKE \'%value%\'',
        $like->getSql()
      ) ;
    }

    public function testContainsAddsWildcards() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->any())
        ->method('getSQLCondition')
        ->with(['field' => '%value%'], NULL, 'LIKE')
        ->willReturn('field LIKE \'%value%\'');

      $like = new Contains(new Group($databaseAccess), 'field', 'value');
      $this->assertSame(
        'field LIKE \'%value%\'',
        $like->getSql()
      ) ;
    }
  }
}
