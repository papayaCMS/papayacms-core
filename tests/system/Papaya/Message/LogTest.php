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

class PapayaMessageLogTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Message\Log::__construct
  */
  public function testConstructor() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message\Logable::GROUP_SYSTEM,
      \Papaya\Message::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertAttributeEquals(
      \Papaya\Message\Logable::GROUP_SYSTEM,
      '_group',
      $message
    );
    $this->assertAttributeEquals(
      \Papaya\Message::SEVERITY_WARNING,
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
  * @covers \Papaya\Message\Log::getGroup
  */
  public function testGetGroup() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message\Logable::GROUP_SYSTEM,
      \Papaya\Message::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      \Papaya\Message\Logable::GROUP_SYSTEM,
      $message->getGroup()
    );
  }


  /**
  * @covers \Papaya\Message\Log::getType
  */
  public function testGetType() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message\Logable::GROUP_SYSTEM,
      \Papaya\Message::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      \Papaya\Message::SEVERITY_WARNING,
      $message->getType()
    );
  }

  /**
  * @covers \Papaya\Message\Log::SetContext
  */
  public function testSetContext() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message\Logable::GROUP_SYSTEM,
      \Papaya\Message::SEVERITY_WARNING,
      'Sample Message'
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Context\Group $context */
    $context = $this->createMock(\Papaya\Message\Context\Group::class);
    $message->setContext($context);
    $this->assertAttributeSame(
      $context,
      '_context',
      $message
    );
  }

  /**
  * @covers \Papaya\Message\Log::context
  */
  public function testContext() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message\Logable::GROUP_SYSTEM,
      \Papaya\Message::SEVERITY_WARNING,
      'Sample Message'
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Context\Group $context */
    $context = $this->createMock(\Papaya\Message\Context\Group::class);
    $message->setContext($context);
    $this->assertSame(
      $context,
      $message->context()
    );
  }

  /**
  * @covers \Papaya\Message\Log::getMessage
  */
  public function testGetMessage() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message\Logable::GROUP_SYSTEM,
      \Papaya\Message::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }

  /**
  * @covers \Papaya\Message\Log::Context
  */
  public function testContextImplizitCreate() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message\Logable::GROUP_SYSTEM,
      \Papaya\Message::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertInstanceOf(
      \Papaya\Message\Context\Group::class,
      $message->context()
    );
  }
}
