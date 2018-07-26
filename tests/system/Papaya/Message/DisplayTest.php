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

class PapayaMessageDisplayTest extends PapayaTestCase {

  /**
  * @covers \PapayaMessageDisplay::__construct
  * @covers \PapayaMessageDisplay::_isValidType
  */
  public function testConstructor() {
    $message = new \PapayaMessageDisplay(PapayaMessage::SEVERITY_WARNING, 'Sample Message');
    $this->assertAttributeEquals(
      \PapayaMessage::SEVERITY_WARNING,
      '_type',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Message',
      '_message',
      $message
    );
  }

  /**
  * @covers \PapayaMessageDisplay::__construct
  * @covers \PapayaMessageDisplay::_isValidType
  */
  public function testConstructorWithInvalidTypeExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    new \PapayaMessageDisplay(PapayaMessage::SEVERITY_DEBUG, 'Sample Message');
  }

  /**
  * @covers \PapayaMessageDisplay::getType
  */
  public function testGetType() {
    $message = new \PapayaMessageDisplay(PapayaMessage::SEVERITY_WARNING, 'Sample Message');
    $this->assertEquals(
      \PapayaMessage::SEVERITY_WARNING,
      $message->getType()
    );
  }

  /**
  * @covers \PapayaMessageDisplay::getMessage
  */
  public function testGetMessage() {
    $message = new \PapayaMessageDisplay(PapayaMessage::SEVERITY_WARNING, 'Sample Message');
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }

}
