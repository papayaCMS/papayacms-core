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

class AssociativeArrayTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Filter\AssociativeArray
   * @throws Exception
   */
  public function testValidateExpectingTrue() {
    $subFilter = $this->getMockBuilder(\Papaya\Filter::class)->getMock();
    $subFilter
      ->expects($this->any())
      ->method('validate')
      ->willReturn(TRUE);
    $filter = new AssociativeArray(
      [
        'foo' => $subFilter,
        'bar' => $subFilter
      ]
    );
    $this->assertTrue($filter->validate(['foo' => 21, 'bar' => 42]));
  }

  /**
   * @covers \Papaya\Filter\AssociativeArray
   * @throws Exception
   */
  public function testValidateInvalidElementValueExpectingException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Exception $e */
    $e = $this->createMock(Exception::class);
    $subFilter = $this->getMockBuilder(\Papaya\Filter::class)->getMock();
    $subFilter
      ->expects($this->any())
      ->method('validate')
      ->willThrowException($e);
    $filter = new AssociativeArray(
      [
        'foo' => $subFilter,
        'bar' => $subFilter
      ]
    );
    $this->expectException(Exception::class);
    $this->assertTrue($filter->validate(['foo' => 21, 'bar' => 42]));
  }

  /**
   * @covers \Papaya\Filter\AssociativeArray
   * @throws Exception
   */
  public function testValidateInvalidKeyExpectingException() {
    $subFilter = $this->getMockBuilder(\Papaya\Filter::class)->getMock();
    $subFilter
      ->expects($this->any())
      ->method('validate')
      ->willReturn(TRUE);
    $filter = new AssociativeArray(
      [
        'foo' => $subFilter
      ]
    );
    $this->expectException(Exception\InvalidKey::class);
    $this->assertTrue($filter->validate(['foo' => 21, 'bar' => 42]));
  }

  /**
   * @covers \Papaya\Filter\AssociativeArray
   */
  public function testFilterExpectingValue() {
    $subFilter = $this->getMockBuilder(\Papaya\Filter::class)->getMock();
    $subFilter
      ->expects($this->any())
      ->method('filter')
      ->willReturnArgument(0);
    $filter = new AssociativeArray(
      [
        'foo' => $subFilter,
        'bar' => $subFilter
      ]
    );
    $this->assertEquals(
      ['foo' => 21, 'bar' => 42],
      $filter->filter(['foo' => 21, 'bar' => 42])
    );
  }

  /**
   * @covers \Papaya\Filter\AssociativeArray
   */
  public function testFilterExpectingNull() {
    $subFilter = $this->getMockBuilder(\Papaya\Filter::class)->getMock();
    $subFilter
      ->expects($this->any())
      ->method('filter')
      ->willReturn(NULL);
    $filter = new AssociativeArray(
      [
        'foo' => $subFilter,
        'bar' => $subFilter
      ]
    );
    $this->assertEquals(
      [],
      $filter->filter(['foo' => 21, 'bar' => 42])
    );
  }

  /**
   * @covers \Papaya\Filter\AssociativeArray
   */
  public function testFilterWithoutArrayExpectingNull() {
    $subFilter = $this->getMockBuilder(\Papaya\Filter::class)->getMock();
    $subFilter
      ->expects($this->any())
      ->method('filter')
      ->willReturn(TRUE);
    $filter = new AssociativeArray(
      [
        'foo' => $subFilter,
        'bar' => $subFilter
      ]
    );
    $this->assertNull(
      $filter->filter(42)
    );
  }

}
