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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiMessagesTest extends PapayaTestCase {


  /**
  * @covers PapayaUiMessages::__construct
  * @covers PapayaUiMessages::appendTo
  * @covers PapayaUiMessages::getXml
  */
  public function testAppendTo() {
    $message = $this
      ->getMockBuilder(PapayaUiMessage::class)
      ->disableOriginalConstructor()
      ->getMock();
    $message
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $messages = new PapayaUiMessages;
    $messages[] = $message;
    $this->assertEquals(
      '<messages/>',
      $messages->getXml()
    );
  }

  /**
  * @covers PapayaUiMessages::appendTo
  */
  public function testAppendToWithoutElements() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaXmlElement $parent */
    $parent = $this
      ->getMockBuilder(PapayaXmlElement::class)
      ->setConstructorArgs(array('messages'))
      ->getMock();
    $parent
      ->expects($this->never())
      ->method('appendTo');
    $messages = new PapayaUiMessages;
    $this->assertNull(
      $messages->appendTo($parent)
    );
  }

  /**
  * @covers PapayaUiMessages::getXml
  */
  public function testgetXmlWithoutElements() {
    $messages = new PapayaUiMessages;
    $this->assertEquals(
      '', $messages->getXml()
    );
  }
}
