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

namespace Papaya\Filter\Text;
require_once __DIR__.'/../../../../bootstrap.php';

class ExplodeTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testValidateWithSingleTokenExpectingTrue() {
    $filter = new Explode();
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testValidateWithSeveralTokensExpectingTrue() {
    $filter = new Explode();
    $this->assertTrue(
      $filter->validate('foo, bar, 42')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testValidateWithIntegerFilterExpectingTrue() {
    $filter = new Explode(',', new \Papaya\Filter\IntegerValue());
    $this->assertTrue(
      $filter->validate('42')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testValidateWithEmptyValueExpectingException() {
    $filter = new Explode(',', new \Papaya\Filter\IntegerValue());
    $this->expectException(\Papaya\Filter\Exception\IsEmpty::class);
    $filter->validate('');
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testFilterWithSingleToken() {
    $filter = new Explode();
    $this->assertEquals(
      ['foo'],
      $filter->filter('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testFilterWithSeveralTokens() {
    $filter = new Explode();
    $this->assertSame(
      ['foo', 'bar', '42'],
      $filter->filter('foo, bar, 42')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testFilterWithIntegerElementFilter() {
    $filter = new Explode(',', new \Papaya\Filter\IntegerValue());
    $this->assertSame(
      [42],
      $filter->filter('42')
    );
  }

}
