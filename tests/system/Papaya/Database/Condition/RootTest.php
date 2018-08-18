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

namespace Papaya\Database\Condition;

require_once __DIR__.'/../../../../bootstrap.php';

class RootTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Database\Condition\Root
   */
  public function testCallAddingFirstElement() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          array(
            array(array('foo' => 'bar'), NULL, '=', "foo = 'bar'")
          )
        )
      );
    $element = new Root($databaseAccess);
    $element->isEqual('foo', 'bar');
    $this->assertEquals("foo = 'bar'", $element->getSql());
  }

  /**
   * @covers \Papaya\Database\Condition\Root
   */
  public function testCallAddingSecondElementExpectingException() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $element = new Root($databaseAccess);
    $element->isEqual('foo', 'bar');
    $this->expectException(\LogicException::class);
    $element->isEqual('foo', 'bar');
  }

  /**
   * @covers \Papaya\Database\Condition\Root
   */
  public function testGetSqlWithoutElement() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $element = new Root($databaseAccess);
    $this->assertEquals('', $element->getSql());
  }
}
