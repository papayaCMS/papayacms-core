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

class PapayaFilterNotTest extends PapayaTestCase {

  /**
  * @covers \PapayaFilterNot::__construct
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaFilter $filterMock */
    $filterMock = $this->createMock(PapayaFilter::class);
    $filter = new \PapayaFilterNot($filterMock);
    $this->assertAttributeInstanceOf(
      PapayaFilter::class, '_filter', $filter
    );
  }

  /**
  * @covers \PapayaFilterNot::validate
  * @expectedException PapayaFilterException
  */
  public function testValidateExpectingException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaFilter $filterMock */
    $filterMock = $this->createMock(PapayaFilter::class);
    $filterMock
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo(123))
      ->will($this->returnValue(TRUE));
    $filter = new \PapayaFilterNot($filterMock);
    $filter->validate(123);
  }

  /**
  * @covers \PapayaFilterNot::validate
  */
  public function testValidateExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaFilter $filterMock */
    $filterMock = $this->createMock(PapayaFilter::class);
    $filterMock
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('abc'))
      ->will($this->returnCallback(array($this, 'callbackThrowFilterException')));
    $filter = new \PapayaFilterNot($filterMock);
    $this->assertTrue($filter->validate('abc'));
  }

  /**
   * @covers \PapayaFilterNot::filter
   */
  public function testFilter() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaFilter $filterMock */
    $filterMock = $this->createMock(PapayaFilter::class);
    $filter = new \PapayaFilterNot($filterMock);
    $this->assertEquals('Test', $filter->filter('Test'));
  }

  /*************************************
  * Callbacks
  *************************************/

  public function callbackThrowFilterException() {
    throw $this->getMockForAbstractClass(PapayaFilterException::class);
  }

}
