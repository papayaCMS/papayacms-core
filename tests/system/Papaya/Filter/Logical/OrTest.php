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

class PapayaFilterLogicalOrTest extends \PapayaTestCase {

  /**
  * @covers \PapayaFilterLogicalOr::validate
  */
  public function testValidateExpectingTrueFromFirstSubFilter() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo
      ->expects($this->never())
      ->method('validate');
    $filter = new \PapayaFilterLogicalOr($subFilterOne, $subFilterTwo);
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers \PapayaFilterLogicalOr::validate
  */
  public function testValidateExpectingTrueFromSecondSubFilter() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnCallback(array($this, 'callbackThrowFilterException')));
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $filter = new \PapayaFilterLogicalOr($subFilterOne, $subFilterTwo);
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers \PapayaFilterLogicalOr::validate
  */
  public function testValidateExpectingException() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnCallback(array($this, 'callbackThrowFilterException')));
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnCallback(array($this, 'callbackThrowFilterException')));
    $filter = new \PapayaFilterLogicalOr($subFilterOne, $subFilterTwo);
    $this->expectException(\PapayaFilterException::class);
    $filter->validate('foo');
  }

  /**
  * @covers \PapayaFilterLogicalOr::filter
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
      ->expects($this->never())
      ->method('filter');
    $filter = new \PapayaFilterLogicalOr($subFilterOne, $subFilterTwo);
    $this->assertEquals(
      'foo',
      $filter->filter('foo')
    );
  }

  /**
  * @covers \PapayaFilterLogicalOr::filter
  */
  public function testFilterUsingSecondFilter() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(NULL));
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('foo'));
    $filter = new \PapayaFilterLogicalOr($subFilterOne, $subFilterTwo);
    $this->assertEquals(
      'foo',
      $filter->filter('foo')
    );
  }

  /**
  * @covers \PapayaFilterLogicalOr::filter
  */
  public function testFilterExpectingNull() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(NULL));
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(NULL));
    $filter = new \PapayaFilterLogicalOr($subFilterOne, $subFilterTwo);
    $this->assertNull(
      $filter->filter('foo')
    );
  }

  /*************************************
  * Callbacks
  *************************************/

  public function callbackThrowFilterException() {
    throw $this->getMockForAbstractClass(\PapayaFilterException::class);
  }
}
