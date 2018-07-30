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

class PapayaMessagePhpTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Message\PHP::__construct
  */
  public function testConstructor() {
    $message = new  \PapayaMessagePHP_TestProxy();
    $this->assertAttributeInstanceOf(
      \Papaya\Message\Context\Group::class,
      '_context',
      $message
    );
  }

  /**
  * @covers \Papaya\Message\PHP::setSeverity
  */
  public function testSetSeverity() {
    $message = new  \PapayaMessagePHP_TestProxy();
    $message->setSeverity(E_USER_NOTICE);
    $this->assertAttributeEquals(
      \Papaya\Message::SEVERITY_INFO,
      '_type',
      $message
    );
  }

  /**
  * @covers \Papaya\Message\PHP::getGroup
  */
  public function testGetGroup() {
    $message = new  \PapayaMessagePHP_TestProxy();
    $this->assertEquals(
      \Papaya\Message\Logable::GROUP_PHP,
      $message->getGroup()
    );
  }

  /**
  * @covers \Papaya\Message\PHP::getType
  */
  public function testGetType() {
    $message = new  \PapayaMessagePHP_TestProxy();
    $this->assertEquals(
      \Papaya\Message::SEVERITY_ERROR,
      $message->getType()
    );
  }

  /**
  * @covers \Papaya\Message\PHP::getMessage
  */
  public function testGetMessage() {
    $message = new  \PapayaMessagePHP_TestProxy();
    $this->assertSame(
      '',
      $message->getMessage()
    );
  }

  /**
  * @covers \Papaya\Message\PHP::context
  */
  public function testContext() {
    $message = new  \PapayaMessagePHP_TestProxy();
    $this->assertInstanceOf(
      \Papaya\Message\Context\Group::class,
      $message->context()
    );
  }

  /**
  * @covers \Papaya\Message\PHP::setContext
  */
  public function testSetContext() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Context\Group $context */
    $context = $this->createMock(\Papaya\Message\Context\Group::class);
    $message = new  \PapayaMessagePHP_TestProxy();
    $message->setContext($context);
    $this->assertAttributeSame(
      $context,
      '_context',
      $message
    );
  }
}

class PapayaMessagePHP_TestProxy extends \Papaya\Message\PHP {}
