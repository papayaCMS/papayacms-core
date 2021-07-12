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

namespace Papaya\UI;
require_once __DIR__.'/../../../bootstrap.php';

class MessagesTest extends \Papaya\TestFramework\TestCase {


  /**
   * @covers \Papaya\UI\Messages::__construct
   * @covers \Papaya\UI\Messages::appendTo
   * @covers \Papaya\UI\Messages::getXML
   */
  public function testAppendTo() {
    $message = $this
      ->getMockBuilder(Message::class)
      ->disableOriginalConstructor()
      ->getMock();
    $message
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $messages = new Messages;
    $messages[] = $message;
    $this->assertEquals(
    /** @lang XML */
      '<messages/>',
      $messages->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Messages::appendTo
   */
  public function testAppendToWithoutElements() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\XML\Element $parent */
    $parent = $this
      ->getMockBuilder(\Papaya\XML\Element::class)
      ->setConstructorArgs(array('messages'))
      ->getMock();
    $parent
      ->expects($this->never())
      ->method('appendTo');
    $messages = new Messages;
    $this->assertNull(
      $messages->appendTo($parent)
    );
  }

  /**
   * @covers \Papaya\UI\Messages::getXML
   */
  public function testgetXmlWithoutElements() {
    $messages = new Messages;
    $this->assertEquals(
      '', $messages->getXML()
    );
  }
}
