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

use Papaya\Administration\Page\Part;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaAdministrationPagePartTest extends \PapayaTestCase {

  /**
   * @covers Part::appendTo
   */
  public function testAppendTo() {
    $commands = $this->createMock(\Papaya\Ui\Control\Command::class);
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $part = new \PapayaAdministrationPagePart_TestProxy();
    $part->commands($commands);

    $this->assertEquals(
      '',
      $part->getXml()
    );
  }

  /**
   * @covers Part::commands
   */
  public function testCommandsGetAfterSet() {
    $part = new \PapayaAdministrationPagePart_TestProxy();
    $part->commands($commands = $this->createMock(\Papaya\Ui\Control\Command::class));
    $this->assertSame($commands, $part->commands());
  }

  /**
   * @covers Part::commands
   * @covers Part::_createCommands
   */
  public function testCommandsGetImplicitCreate() {
    $part = new \PapayaAdministrationPagePart_TestProxy();
    $this->assertInstanceOf(\Papaya\Ui\Control\Command\Controller::class, $part->commands());
  }

  /**
   * @covers Part::toolbar
   */
  public function testToolbarGetAfterSet() {
    $part = new \PapayaAdministrationPagePart_TestProxy();
    $part->toolbar($toolbar = $this->createMock(\Papaya\Ui\Toolbar\Collection::class));
    $this->assertSame($toolbar, $part->toolbar());
  }

  /**
   * @covers Part::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $part = new \PapayaAdministrationPagePart_TestProxy();
    $this->assertInstanceOf(\Papaya\Ui\Toolbar\Collection::class, $part->toolbar());
  }
}

class PapayaAdministrationPagePart_TestProxy extends Part {

}
