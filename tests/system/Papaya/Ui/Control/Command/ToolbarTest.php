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
  * @covers \PapayaUiControlCommandToolbar
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiToolbarElements $elements */
    $elements = $this->createMock(\PapayaUiToolbarElements::class);
    $command = new \PapayaUiControlCommandToolbar_TestProxy($elements);
    $this->assertSame($elements, $command->elements());
  }

  /**
  * @covers \PapayaUiControlCommandToolbar
  */
  public function testGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiToolbarElements $elements */
    $elements = $this->createMock(\PapayaUiToolbarElements::class);
    $command = new \PapayaUiControlCommandToolbar_TestProxy($elements);
    $command->elements($newElements = $this->createMock(\PapayaUiToolbarElements::class));
    $this->assertSame($newElements, $command->elements());
  }

  /**
  * @covers \PapayaUiControlCommandToolbar
  */
  public function testAppendTo() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiToolbarElements $elements */
    $elements = $this->createMock(\PapayaUiToolbarElements::class);
    $elements
      ->expects($this->once())
      ->method('add')
      ->with($this->isInstanceOf(\PapayaUiToolbarElement::class));

    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $command = new \PapayaUiControlCommandToolbar_TestProxy($elements);
    $command->testElement = $this->createMock(\PapayaUiToolbarElement::class);
    $command->appendTo($document->documentElement);
    $this->assertEquals(/** @lang XML */'<test/>', $document->documentElement->saveXml());
  }

}

class PapayaUiControlCommandToolbar_TestProxy extends \PapayaUiControlCommandToolbar {

  public $testElement;

  public function appendToolbarElements() {
    $this->elements()->add($this->testElement);
  }
}
