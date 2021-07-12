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

namespace Papaya\CMS\Administration\Theme\Editor;

require_once __DIR__.'/../../../../../../bootstrap.php';

class ChangesTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes::appendTo
   */
  public function testAppendTo() {
    $commands = $this
      ->getMockBuilder(\Papaya\UI\Control\Command\Controller::class)
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $changes = new Changes();
    $changes->commands($commands);
    $this->assertEmpty($changes->getXML());
  }

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes::commands
   */
  public function testCommandsGetAfterSet() {
    $commands = $this
      ->getMockBuilder(\Papaya\UI\Control\Command\Controller::class)
      ->disableOriginalConstructor()
      ->getMock();
    $changes = new Changes();
    $changes->commands($commands);
    $this->assertSame($commands, $changes->commands());
  }

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes::commands
   */
  public function testCommandGetImplicitCreate() {
    $changes = new Changes();
    $changes->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(\Papaya\UI\Control\Command\Controller::class, $changes->commands());
  }


  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes::themeSet
   */
  public function testThemeSetGetAfterSet() {
    $command = new Changes();
    $command->themeSet($themeSet = $this->createMock(\Papaya\CMS\Content\Theme\Skin::class));
    $this->assertSame($themeSet, $command->themeSet());
  }

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes::themeSet
   */
  public function testThemeSetGetImplicitCreate() {
    $command = new Changes();
    $this->assertInstanceOf(\Papaya\CMS\Content\Theme\Skin::class, $command->themeSet());
  }

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes::themeHandler
   */
  public function testThemeHandlerGetAfterSet() {
    $command = new Changes();
    $command->themeHandler($themeHandler = $this->createMock(\Papaya\CMS\Theme\Handler::class));
    $this->assertSame($themeHandler, $command->themeHandler());
  }

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes::themeHandler
   */
  public function testThemeHandlerGetImplicitCreate() {
    $command = new Changes();
    $this->assertInstanceOf(\Papaya\CMS\Theme\Handler::class, $command->themeHandler());
  }
}
