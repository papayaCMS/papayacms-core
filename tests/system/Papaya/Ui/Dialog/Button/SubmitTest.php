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
  * @covers \Papaya\UI\Dialog\Button\Submit::__construct
  */
  public function testConstructor() {
    $button = new \Papaya\UI\Dialog\Button\Submit('Test Caption');
    $this->assertAttributeEquals(
      'Test Caption',
      '_caption',
      $button
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Button\Submit::__construct
  */
  public function testConstructorWithAlignment() {
    $button = new \Papaya\UI\Dialog\Button\Submit(
      'Test Caption', \Papaya\UI\Dialog\Button::ALIGN_LEFT
    );
    $this->assertAttributeEquals(
      \Papaya\UI\Dialog\Button::ALIGN_LEFT,
      '_align',
      $button
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Button\Submit::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $button = new \Papaya\UI\Dialog\Button\Submit('Test Caption');
    $button->appendTo($document->documentElement);
    $this->assertEquals(
      /** @lang XML */
      '<test><button type="submit" align="right">Test Caption</button></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Button\Submit::appendTo
  */
  public function testAppendToWithInterfaceStringObject() {
    $caption = $this
      ->getMockBuilder(\Papaya\UI\Text::class)
      ->setConstructorArgs(array('.'))
      ->getMock();
    $caption
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('Test Caption'));
    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $button = new \Papaya\UI\Dialog\Button\Submit(
      $caption, \Papaya\UI\Dialog\Button::ALIGN_LEFT
    );
    $button->appendTo($document->documentElement);
    $this->assertEquals(
      /** @lang XML */
      '<test><button type="submit" align="left">Test Caption</button></test>',
      $document->saveXML($document->documentElement)
    );
  }
}
