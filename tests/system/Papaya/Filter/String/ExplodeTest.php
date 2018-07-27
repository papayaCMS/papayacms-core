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
   * @covers \PapayaFilterStringExplode
   */
  public function testValidateWithSingleTokenExpectingTrue() {
    $filter = new \PapayaFilterStringExplode();
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers \PapayaFilterStringExplode
   */
  public function testValidateWithSeveralTokensExpectingTrue() {
    $filter = new \PapayaFilterStringExplode();
    $this->assertTrue(
      $filter->validate('foo, bar, 42')
    );
  }

  /**
   * @covers \PapayaFilterStringExplode
   */
  public function testValidateWithIntegerFilterExpectingTrue() {
    $filter = new \PapayaFilterStringExplode(',', new \PapayaFilterInteger());
    $this->assertTrue(
      $filter->validate('42')
    );
  }

  /**
   * @covers \PapayaFilterStringExplode
   */
  public function testValidateWithEmptyValueExpectingException() {
    $filter = new \PapayaFilterStringExplode(',', new \PapayaFilterInteger());
    $this->expectException(\Papaya\Filter\Exception\IsEmpty::class);
    $filter->validate('');
  }

  /**
   * @covers \PapayaFilterStringExplode
   */
  public function testFilterWithSingleToken() {
    $filter = new \PapayaFilterStringExplode();
    $this->assertEquals(
      ['foo'],
      $filter->filter('foo')
    );
  }

  /**
   * @covers \PapayaFilterStringExplode
   */
  public function testFilterWithSeveralTokens() {
    $filter = new \PapayaFilterStringExplode();
    $this->assertSame(
      ['foo', 'bar', '42'],
      $filter->filter('foo, bar, 42')
    );
  }

  /**
   * @covers \PapayaFilterStringExplode
   */
  public function testFilterWithIntegerElementFilter() {
    $filter = new \PapayaFilterStringExplode(',', new \PapayaFilterInteger());
    $this->assertSame(
      [42],
      $filter->filter('42')
    );
  }

}
