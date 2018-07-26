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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterBeforeTest extends PapayaTestCase {

  /**
   * @covers \PapayaFilterBefore
   */
  public function testValidate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaFilter $before */
    $before = $this->createMock(PapayaFilter::class);
    $before
      ->expects($this->once())
      ->method('filter')
      ->with('foo')
      ->willReturn('success');

    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaFilter $after */
    $after = $this->createMock(PapayaFilter::class);
    $after
      ->expects($this->once())
      ->method('validate')
      ->with('success')
      ->willReturn(TRUE);

    $filter = new \PapayaFilterBefore($before, $after);
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers \PapayaFilterBefore
   */
  public function testFilter() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaFilter $before */
    $before = $this->createMock(PapayaFilter::class);
    $before
      ->expects($this->once())
      ->method('filter')
      ->with('foo')
      ->willReturn('success');

    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaFilter $after */
    $after = $this->createMock(PapayaFilter::class);
    $after
      ->expects($this->once())
      ->method('filter')
      ->with('success')
      ->willReturn(42);

    $filter = new \PapayaFilterBefore($before, $after);
    $this->assertSame(
      42,
      $filter->filter('foo')
    );
  }
}
