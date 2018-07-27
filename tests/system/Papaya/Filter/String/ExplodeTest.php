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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterStringExplodeTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testValidateWithSingleTokenExpectingTrue() {
    $filter = new \Papaya\Filter\Text\Explode();
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testValidateWithSeveralTokensExpectingTrue() {
    $filter = new \Papaya\Filter\Text\Explode();
    $this->assertTrue(
      $filter->validate('foo, bar, 42')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testValidateWithIntegerFilterExpectingTrue() {
    $filter = new \Papaya\Filter\Text\Explode(',', new \PapayaFilterInteger());
    $this->assertTrue(
      $filter->validate('42')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testValidateWithEmptyValueExpectingException() {
    $filter = new \Papaya\Filter\Text\Explode(',', new \PapayaFilterInteger());
    $this->expectException(\Papaya\Filter\Exception\IsEmpty::class);
    $filter->validate('');
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testFilterWithSingleToken() {
    $filter = new \Papaya\Filter\Text\Explode();
    $this->assertEquals(
      ['foo'],
      $filter->filter('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testFilterWithSeveralTokens() {
    $filter = new \Papaya\Filter\Text\Explode();
    $this->assertSame(
      ['foo', 'bar', '42'],
      $filter->filter('foo, bar, 42')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Explode
   */
  public function testFilterWithIntegerElementFilter() {
    $filter = new \Papaya\Filter\Text\Explode(',', new \PapayaFilterInteger());
    $this->assertSame(
      [42],
      $filter->filter('42')
    );
  }

}
