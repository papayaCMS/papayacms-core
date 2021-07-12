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

  class ToolbarTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\UI\Control\Command\Toolbar
     */
    public function testConstructor() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Toolbar\Elements $elements */
      $elements = $this->createMock(\Papaya\UI\Toolbar\Elements::class);
      $command = new Toolbar_TestProxy($elements);
      $this->assertSame($elements, $command->elements());
    }

    /**
     * @covers \Papaya\UI\Control\Command\Toolbar
     */
    public function testGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Toolbar\Elements $elements */
      $elements = $this->createMock(\Papaya\UI\Toolbar\Elements::class);
      $command = new Toolbar_TestProxy($elements);
      $command->elements($newElements = $this->createMock(\Papaya\UI\Toolbar\Elements::class));
      $this->assertSame($newElements, $command->elements());
    }

    /**
     * @covers \Papaya\UI\Control\Command\Toolbar
     */
    public function testAppendTo() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Toolbar\Elements $elements */
      $elements = $this->createMock(\Papaya\UI\Toolbar\Elements::class);
      $elements
        ->expects($this->once())
        ->method('add')
        ->with($this->isInstanceOf(\Papaya\UI\Toolbar\Element::class));

      $document = new \Papaya\XML\Document();
      $document->appendElement('test');
      $command = new Toolbar_TestProxy($elements);
      $command->testElement = $this->createMock(\Papaya\UI\Toolbar\Element::class);
      $command->appendTo($document->documentElement);
      $this->assertEquals(/** @lang XML */
        '<test/>', $document->documentElement->saveXML());
    }

  }

  class Toolbar_TestProxy extends Toolbar {

    public $testElement;

    public function appendToolbarElements() {
      $this->elements()->add($this->testElement);
    }
  }
}
