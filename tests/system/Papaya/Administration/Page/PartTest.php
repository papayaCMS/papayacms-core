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

namespace Papaya\Administration\Page {

  require_once __DIR__.'/../../../../bootstrap.php';

  class PartTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Administration\Page\Part::appendTo
     */
    public function testAppendTo() {
      $commands = $this->createMock(\Papaya\UI\Control\Command::class);
      $commands
        ->expects($this->once())
        ->method('appendTo')
        ->with($this->isInstanceOf(\Papaya\XML\Element::class));
      $part = new PagePart_TestProxy();
      $part->commands($commands);

      $this->assertEquals(
        '',
        $part->getXML()
      );
    }

    /**
     * @covers \Papaya\Administration\Page\Part::commands
     */
    public function testCommandsGetAfterSet() {
      $part = new PagePart_TestProxy();
      $part->commands($commands = $this->createMock(\Papaya\UI\Control\Command::class));
      $this->assertSame($commands, $part->commands());
    }

    /**
     * @covers \Papaya\Administration\Page\Part::commands
     * @covers \Papaya\Administration\Page\Part::_createCommands
     */
    public function testCommandsGetImplicitCreate() {
      $part = new PagePart_TestProxy();
      $this->assertInstanceOf(\Papaya\UI\Control\Command\Controller::class, $part->commands());
    }

    /**
     * @covers \Papaya\Administration\Page\Part::toolbar
     */
    public function testToolbarGetAfterSet() {
      $part = new PagePart_TestProxy();
      $part->toolbar($toolbar = $this->createMock(\Papaya\UI\Toolbar\Collection::class));
      $this->assertSame($toolbar, $part->toolbar());
    }

    /**
     * @covers \Papaya\Administration\Page\Part::toolbar
     */
    public function testToolbarGetImplicitCreate() {
      $part = new PagePart_TestProxy();
      $this->assertInstanceOf(\Papaya\UI\Toolbar\Collection::class, $part->toolbar());
    }
  }

  class PagePart_TestProxy extends Part {

  }
}
