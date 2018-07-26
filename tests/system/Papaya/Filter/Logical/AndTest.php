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

class PapayaFilterLogicalAndTest extends PapayaTestCase {

  /**
  * @covers \PapayaFilterLogicalAnd::validate
  */
  public function testValidateExpectingTrue() {
    $subFilterOne = $this->createMock(PapayaFilter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $subFilterTwo = $this->createMock(PapayaFilter::class);
    $subFilterTwo
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $filter = new \PapayaFilterLogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers \PapayaFilterLogicalAnd::validate
  */
  public function testValidateWithScalarValuesExpectingTrue() {
    $subFilterOne = $this->createMock(PapayaFilter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $filter = new \PapayaFilterLogicalAnd($subFilterOne, 'foo');
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers \PapayaFilterLogicalAnd::validate
  */
  public function testValidateWithScalarValuesExpectingException() {
    $subFilterOne = $this->createMock(PapayaFilter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $filter = new \PapayaFilterLogicalAnd($subFilterOne, 'bar');
    $this->expectException(PapayaFilterException::class);
    $filter->validate('foo');
  }

  /**
  * @covers \PapayaFilterLogicalAnd::filter
  */
  public function testFilter() {
    $subFilterOne = $this->createMock(PapayaFilter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('foo'));
    $subFilterTwo = $this->createMock(PapayaFilter::class);
    $subFilterTwo
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('foo'));
    $filter = new \PapayaFilterLogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertEquals(
      'foo',
      $filter->filter('foo')
    );
  }

  /**
  * @covers \PapayaFilterLogicalAnd::filter
  */
  public function testFilterExpectingNullFromFirstSubFilter() {
    $subFilterOne = $this->createMock(PapayaFilter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(NULL));
    $subFilterTwo = $this->createMock(PapayaFilter::class);
    $subFilterTwo
      ->expects($this->never())
      ->method('filter');
    $filter = new \PapayaFilterLogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertNull(
      $filter->filter('foo')
    );
  }

  /**
  * @covers \PapayaFilterLogicalAnd::filter
  */
  public function testFilterExpectingNullFromSecondSubFilter() {
    $subFilterOne = $this->createMock(PapayaFilter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('foo'));
    $subFilterTwo = $this->createMock(PapayaFilter::class);
    $subFilterTwo
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(NULL));
    $filter = new \PapayaFilterLogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertNull(
      $filter->filter('foo')
    );
  }
}
