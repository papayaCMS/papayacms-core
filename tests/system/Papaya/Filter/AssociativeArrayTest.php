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

namespace Papaya\Filter {

  use InvalidArgumentException;
  use Papaya\Filter;
  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Filter\AssociativeArray
   */
  class AssociativeArrayTest extends TestCase {

    public function testConstructorWithEmptyDefinitionArrayExceptionException() {
      $this->expectException(InvalidArgumentException::class);
      new AssociativeArray([]);
    }

    public function testConstructorWithInvalidDefinitionArrayExceptionException() {
      $this->expectException(InvalidArgumentException::class);
      new AssociativeArray(['foo' => 'not a filter']);
    }

    /**
     * @throws Exception
     */
    public function testValidateExpectingTrue() {
      $subFilter = $this->getMockBuilder(Filter::class)->getMock();
      $subFilter
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
     * @throws Exception
     */
    public function testValidateInvalidElementValueExpectingException() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Exception $e */
      $e = $this->createMock(Exception::class);
      $subFilter = $this->getMockBuilder(Filter::class)->getMock();
      $subFilter
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
     * @throws Exception
     */
    public function testValidateInvalidValueTypeExpectingException() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Exception $e */
      $e = $this->createMock(Exception::class);
      $subFilter = $this->getMockBuilder(Filter::class)->getMock();
      $subFilter
        ->expects($this->never())
        ->method('validate');
      $filter = new AssociativeArray(
        [
          'foo' => $subFilter,
          'bar' => $subFilter
        ]
      );
      $this->expectException(Exception::class);
      $this->assertTrue($filter->validate('invalid-type'));
    }

    /**
     * @throws Exception
     */
    public function testValidateInvalidKeyExpectingException() {
      $subFilter = $this->getMockBuilder(Filter::class)->getMock();
      $subFilter
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

    public function testFilterExpectingValue() {
      $subFilter = $this->getMockBuilder(Filter::class)->getMock();
      $subFilter
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

    public function testFilterExpectingNull() {
      $subFilter = $this->getMockBuilder(Filter::class)->getMock();
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

    public function testFilterWithoutArrayExpectingNull() {
      $subFilter = $this->getMockBuilder(Filter::class)->getMock();
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
}
