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

class PapayaUiMessagesTest extends \PapayaTestCase {


  /**
  * @covers \Papaya\Ui\Messages::__construct
  * @covers \Papaya\Ui\Messages::appendTo
  * @covers \Papaya\Ui\Messages::getXml
  */
  public function testAppendTo() {
    $message = $this
      ->getMockBuilder(\Papaya\Ui\Message::class)
      ->disableOriginalConstructor()
      ->getMock();
    $message
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $messages = new \Papaya\Ui\Messages;
    $messages[] = $message;
    $this->assertEquals(
    /** @lang XML */'<messages/>',
      $messages->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Messages::appendTo
  */
  public function testAppendToWithoutElements() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Xml\Element $parent */
    $parent = $this
      ->getMockBuilder(\Papaya\Xml\Element::class)
      ->setConstructorArgs(array('messages'))
      ->getMock();
    $parent
      ->expects($this->never())
      ->method('appendTo');
    $messages = new \Papaya\Ui\Messages;
    $this->assertNull(
      $messages->appendTo($parent)
    );
  }

  /**
  * @covers \Papaya\Ui\Messages::getXml
  */
  public function testgetXmlWithoutElements() {
    $messages = new \Papaya\Ui\Messages;
    $this->assertEquals(
      '', $messages->getXml()
    );
  }
}
