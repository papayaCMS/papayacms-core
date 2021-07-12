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

/**
 * @covers \Papaya\Filter\Not
 */
class NotTest extends \Papaya\TestFramework\TestCase {

  public function testValidateExpectingException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filterMock */
    $filterMock = $this->createMock(\Papaya\Filter::class);
    $filterMock
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo(123))
      ->will($this->returnValue(TRUE));
    $filter = new Not($filterMock);
    $this->expectException(\Exception::class);
    $filter->validate(123);
  }

  public function testValidateExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filterMock */
    $filterMock = $this->createMock(\Papaya\Filter::class);
    $filterMock
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('abc'))
      ->will($this->returnCallback(array($this, 'callbackThrowFilterException')));
    $filter = new Not($filterMock);
    $this->assertTrue($filter->validate('abc'));
  }

  public function testFilter() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filterMock */
    $filterMock = $this->createMock(\Papaya\Filter::class);
    $filter = new Not($filterMock);
    $this->assertEquals('Test', $filter->filter('Test'));
  }

  /*************************************
   * Callbacks
   *************************************/

  public function callbackThrowFilterException() {
    throw $this->getMockForAbstractClass(Exception::class);
  }

}
