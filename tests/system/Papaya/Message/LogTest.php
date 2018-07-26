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

class PapayaMessageLogTest extends PapayaTestCase {

  /**
  * @covers \PapayaMessageLog::__construct
  */
  public function testConstructor() {
    $message = new \PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertAttributeEquals(
      PapayaMessageLogable::GROUP_SYSTEM,
      '_group',
      $message
    );
    $this->assertAttributeEquals(
      PapayaMessage::SEVERITY_WARNING,
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
  * @covers \PapayaMessageLog::getGroup
  */
  public function testGetGroup() {
    $message = new \PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      PapayaMessageLogable::GROUP_SYSTEM,
      $message->getGroup()
    );
  }


  /**
  * @covers \PapayaMessageLog::getType
  */
  public function testGetType() {
    $message = new \PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      PapayaMessage::SEVERITY_WARNING,
      $message->getType()
    );
  }

  /**
  * @covers \PapayaMessageLog::SetContext
  */
  public function testSetContext() {
    $message = new \PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageContextGroup $context */
    $context = $this->createMock(PapayaMessageContextGroup::class);
    $message->setContext($context);
    $this->assertAttributeSame(
      $context,
      '_context',
      $message
    );
  }

  /**
  * @covers \PapayaMessageLog::context
  */
  public function testContext() {
    $message = new \PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageContextGroup $context */
    $context = $this->createMock(PapayaMessageContextGroup::class);
    $message->setContext($context);
    $this->assertSame(
      $context,
      $message->context()
    );
  }

  /**
  * @covers \PapayaMessageLog::getMessage
  */
  public function testGetMessage() {
    $message = new \PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }

  /**
  * @covers \PapayaMessageLog::Context
  */
  public function testContextImplizitCreate() {
    $message = new \PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertInstanceOf(
      PapayaMessageContextGroup::class,
      $message->context()
    );
  }
}
