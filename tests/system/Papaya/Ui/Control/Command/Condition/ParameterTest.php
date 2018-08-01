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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiControlCommandConditionParameterTest extends \PapayaTestCase {

  public function testValidateExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with('value')
      ->will($this->returnArgument(0));
    $condition = new \Papaya\Ui\Control\Command\Condition\Parameter('name', $filter);
    $condition->command($this->getCommandFixture());
    $this->assertTrue($condition->validate());
  }

  public function testValidateExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with('value')
      ->will($this->returnValue(NULL));
    $condition = new \Papaya\Ui\Control\Command\Condition\Parameter('name', $filter);
    $condition->command($this->getCommandFixture());
    $this->assertFalse($condition->validate());
  }

  public function getCommandFixture() {
    $parameters = $this->createMock(\Papaya\Request\Parameters::class);
    $parameters
      ->expects($this->once())
      ->method('get')
      ->with('name')
      ->will($this->returnValue('value'));
    $owner = $this->createMock(\Papaya\Ui\Control\Interactive::class);
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue($parameters));
    $command = $this->createMock(\Papaya\Ui\Control\Command::class);
    $command
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($owner));
    return $command;
  }
}
