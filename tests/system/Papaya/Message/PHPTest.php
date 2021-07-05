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

namespace Papaya\Message {

  require_once __DIR__.'/../../../bootstrap.php';

  class PHPTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Message\PHP::__construct
     */
    public function testConstructor() {
      $message = new  PHPMessage_TestProxy();
      $this->assertInstanceOf(
        Context\Group::class,
        $message->context()
      );
    }

    /**
     * @covers \Papaya\Message\PHP::setSeverity
     */
    public function testSetSeverity() {
      $message = new  PHPMessage_TestProxy();
      $message->setSeverity(E_USER_NOTICE);
      $this->assertEquals(
        \Papaya\Message::SEVERITY_INFO,
        $message->getSeverity()
      );
    }

    /**
     * @covers \Papaya\Message\PHP::getGroup
     */
    public function testGetGroup() {
      $message = new  PHPMessage_TestProxy();
      $this->assertEquals(
        Logable::GROUP_PHP,
        $message->getGroup()
      );
    }

    /**
     * @covers \Papaya\Message\PHP::getSeverity
     */
    public function testGetSeverity() {
      $message = new  PHPMessage_TestProxy();
      $this->assertEquals(
        \Papaya\Message::SEVERITY_ERROR,
        $message->getSeverity()
      );
    }

    /**
     * @covers \Papaya\Message\PHP::getMessage
     */
    public function testGetMessage() {
      $message = new  PHPMessage_TestProxy();
      $this->assertSame(
        '',
        $message->getMessage()
      );
    }

    /**
     * @covers \Papaya\Message\PHP::context
     */
    public function testContext() {
      $message = new  PHPMessage_TestProxy();
      $this->assertInstanceOf(
        Context\Group::class,
        $message->context()
      );
    }

    /**
     * @covers \Papaya\Message\PHP::setContext
     */
    public function testSetContext() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Context\Group $context */
      $context = $this->createMock(Context\Group::class);
      $message = new  PHPMessage_TestProxy();
      $message->setContext($context);
      $this->assertSame(
        $context,
        $message->context()
      );
    }
  }

  class PHPMessage_TestProxy extends PHP {
  }
}
