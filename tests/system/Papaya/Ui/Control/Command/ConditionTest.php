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

namespace Papaya\UI\Control\Command {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class ConditionTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\UI\Control\Command\Condition::command
     */
    public function testCommandGetAfterSet() {
      $application = $this->mockPapaya()->application();
      $command = $this->createMock(\Papaya\UI\Control\Command::class);
      $command
        ->expects($this->once())
        ->method('papaya')
        ->will($this->returnValue($application));
      $condition = new Condition_TestProxy();
      $condition->papaya();
      $this->assertSame($command, $condition->command($command));
      $this->assertEquals($application, $condition->papaya());
    }

    /**
     * @covers \Papaya\UI\Control\Command\Condition::command
     */
    public function testCommandGetExpectingException() {
      $condition = new Condition_TestProxy();
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage(
        'LogicException: Instance of "Papaya\UI\Control\Command\Condition_TestProxy" has no command assigned.'
      );
      $condition->command();
    }

    /**
     * @covers \Papaya\UI\Control\Command\Condition::hasCommand
     */
    public function testHasCommandExpectingTrue() {
      $command = $this->createMock(\Papaya\UI\Control\Command::class);
      $condition = new Condition_TestProxy();
      $condition->command($command);
      $this->assertTrue($condition->hasCommand());
    }

    /**
     * @covers \Papaya\UI\Control\Command\Condition::hasCommand
     */
    public function testHasCommandExpectingFalse() {
      $condition = new Condition_TestProxy();
      $this->assertFalse($condition->hasCommand());
    }
  }

  class Condition_TestProxy extends Condition {

    public function validate() {

    }
  }
}
