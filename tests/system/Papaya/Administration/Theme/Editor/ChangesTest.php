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

use Papaya\Administration\Theme\Editor\Changes;
use Papaya\Content\Theme\Set;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationThemeEditorChangesTest extends PapayaTestCase {

  /**
   * @covers Changes::appendTo
   */
  public function testAppendTo() {
    $commands = $this
      ->getMockBuilder(PapayaUiControlCommandController::class)
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $changes = new Changes();
    $changes->commands($commands);
    $this->assertEmpty($changes->getXml());
  }

  /**
   * @covers Changes::commands
   */
  public function testCommandsGetAfterSet() {
    $commands = $this
      ->getMockBuilder(PapayaUiControlCommandController::class)
      ->disableOriginalConstructor()
      ->getMock();
    $changes = new Changes();
    $changes->commands($commands);
    $this->assertSame($commands, $changes->commands());
  }

  /**
   * @covers Changes::commands
   */
  public function testCommandGetImplicitCreate() {
    $changes = new Changes();
    $changes->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(PapayaUiControlCommandController::class, $changes->commands());
  }


  /**
   * @covers Changes::themeSet
   */
  public function testThemeSetGetAfterSet() {
    $command = new Changes();
    $command->themeSet($themeSet =  $this->createMock(Set::class));
    $this->assertSame($themeSet, $command->themeSet());
  }

  /**
   * @covers Changes::themeSet
   */
  public function testThemeSetGetImplicitCreate() {
    $command = new Changes();
    $this->assertInstanceOf(Set::class, $command->themeSet());
  }

  /**
   * @covers Changes::themeHandler
   */
  public function testThemeHandlerGetAfterSet() {
    $command = new Changes();
    $command->themeHandler($themeHandler = $this->createMock(PapayaThemeHandler::class));
    $this->assertSame($themeHandler, $command->themeHandler());
  }

  /**
   * @covers Changes::themeHandler
   */
  public function testThemeHandlerGetImplicitCreate() {
    $command = new Changes();
    $this->assertInstanceOf(PapayaThemeHandler::class, $command->themeHandler());
  }
}
