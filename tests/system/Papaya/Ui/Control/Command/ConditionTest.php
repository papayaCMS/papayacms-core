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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiControlCommandConditionTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiControlCommandCondition::command
  */
  public function testCommandGetAfterSet() {
    $application = $this->mockPapaya()->application();
    $command = $this->createMock(\PapayaUiControlCommand::class);
    $command
      ->expects($this->once())
      ->method('papaya')
      ->will($this->returnValue($application));
    $condition = new \PapayaUiControlCommandCondition_TestProxy();
    $condition->papaya();
    $this->assertSame($command, $condition->command($command));
    $this->assertEquals($application, $condition->papaya());
  }

  /**
  * @covers \PapayaUiControlCommandCondition::command
  */
  public function testCommandGetExpectingException() {
    $condition = new \PapayaUiControlCommandCondition_TestProxy();
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage(
      'LogicException: Instance of "PapayaUiControlCommandCondition_TestProxy" has no command assigned.'
    );
    $condition->command();
  }

  /**
  * @covers \PapayaUiControlCommandCondition::hasCommand
  */
  public function testHascommandExpectingTrue() {
    $command = $this->createMock(\PapayaUiControlCommand::class);
    $condition = new \PapayaUiControlCommandCondition_TestProxy();
    $condition->command($command);
    $this->assertTrue($condition->hasCommand());
  }

  /**
  * @covers \PapayaUiControlCommandCondition::hasCommand
  */
  public function testHasCommandExpectingFalse() {
    $condition = new \PapayaUiControlCommandCondition_TestProxy();
    $this->assertFalse($condition->hasCommand());
  }
}

class PapayaUiControlCommandCondition_TestProxy extends \PapayaUiControlCommandCondition {

  public function validate() {

  }
}
