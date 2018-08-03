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

class PapayaUiControlCommandToolbarTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Control\Command\Toolbar
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Toolbar\Elements $elements */
    $elements = $this->createMock(\Papaya\Ui\Toolbar\Elements::class);
    $command = new \PapayaUiControlCommandToolbar_TestProxy($elements);
    $this->assertSame($elements, $command->elements());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Toolbar
  */
  public function testGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Toolbar\Elements $elements */
    $elements = $this->createMock(\Papaya\Ui\Toolbar\Elements::class);
    $command = new \PapayaUiControlCommandToolbar_TestProxy($elements);
    $command->elements($newElements = $this->createMock(\Papaya\Ui\Toolbar\Elements::class));
    $this->assertSame($newElements, $command->elements());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Toolbar
  */
  public function testAppendTo() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Toolbar\Elements $elements */
    $elements = $this->createMock(\Papaya\Ui\Toolbar\Elements::class);
    $elements
      ->expects($this->once())
      ->method('add')
      ->with($this->isInstanceOf(\Papaya\Ui\Toolbar\Element::class));

    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $command = new \PapayaUiControlCommandToolbar_TestProxy($elements);
    $command->testElement = $this->createMock(\Papaya\Ui\Toolbar\Element::class);
    $command->appendTo($document->documentElement);
    $this->assertEquals(/** @lang XML */'<test/>', $document->documentElement->saveXml());
  }

}

class PapayaUiControlCommandToolbar_TestProxy extends \Papaya\Ui\Control\Command\Toolbar {

  public $testElement;

  public function appendToolbarElements() {
    $this->elements()->add($this->testElement);
  }
}
