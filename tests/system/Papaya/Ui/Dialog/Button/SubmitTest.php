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

class PapayaUiDialogButtonSubmitTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiDialogButtonSubmit::__construct
  */
  public function testConstructor() {
    $button = new \PapayaUiDialogButtonSubmit('Test Caption');
    $this->assertAttributeEquals(
      'Test Caption',
      '_caption',
      $button
    );
  }

  /**
  * @covers \PapayaUiDialogButtonSubmit::__construct
  */
  public function testConstructorWithAlignment() {
    $button = new \PapayaUiDialogButtonSubmit(
      'Test Caption', \PapayaUiDialogButton::ALIGN_LEFT
    );
    $this->assertAttributeEquals(
      \PapayaUiDialogButton::ALIGN_LEFT,
      '_align',
      $button
    );
  }

  /**
  * @covers \PapayaUiDialogButtonSubmit::appendTo
  */
  public function testAppendTo() {
    $document = new \PapayaXmlDocument();
    $document->appendElement('test');
    $button = new \PapayaUiDialogButtonSubmit('Test Caption');
    $button->appendTo($document->documentElement);
    $this->assertEquals(
      /** @lang XML */
      '<test><button type="submit" align="right">Test Caption</button></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaUiDialogButtonSubmit::appendTo
  */
  public function testAppendToWithInterfaceStringObject() {
    $caption = $this
      ->getMockBuilder(\PapayaUiString::class)
      ->setConstructorArgs(array('.'))
      ->getMock();
    $caption
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('Test Caption'));
    $document = new \PapayaXmlDocument();
    $document->appendElement('test');
    $button = new \PapayaUiDialogButtonSubmit(
      $caption, \PapayaUiDialogButton::ALIGN_LEFT
    );
    $button->appendTo($document->documentElement);
    $this->assertEquals(
      /** @lang XML */
      '<test><button type="submit" align="left">Test Caption</button></test>',
      $document->saveXML($document->documentElement)
    );
  }
}
