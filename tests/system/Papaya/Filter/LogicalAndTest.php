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

namespace Papaya\Filter;
require_once __DIR__.'/../../../bootstrap.php';

class LogicalAndTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Filter\LogicalAnd::validate
   */
  public function testValidateExpectingTrue() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $filter = new LogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\LogicalAnd::validate
   */
  public function testValidateWithScalarValuesExpectingTrue() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $filter = new LogicalAnd($subFilterOne, 'foo');
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\LogicalAnd::validate
   */
  public function testValidateWithScalarValuesExpectingException() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $filter = new LogicalAnd($subFilterOne, 'bar');
    $this->expectException(\Papaya\Filter\Exception::class);
    $filter->validate('foo');
  }

  /**
   * @covers \Papaya\Filter\LogicalAnd::filter
   */
  public function testFilter() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('foo'));
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('foo'));
    $filter = new LogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertEquals(
      'foo',
      $filter->filter('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\LogicalAnd::filter
   */
  public function testFilterExpectingNullFromFirstSubFilter() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(NULL));
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo
      ->expects($this->never())
      ->method('filter');
    $filter = new LogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertNull(
      $filter->filter('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\LogicalAnd::filter
   */
  public function testFilterExpectingNullFromSecondSubFilter() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('foo'));
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(NULL));
    $filter = new LogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertNull(
      $filter->filter('foo')
    );
  }
}
