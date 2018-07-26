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

class PapayaMessageManagerTest extends \PapayaTestCase {

  /**
  * @covers \PapayaMessageManager::addDispatcher
  */
  public function testAddDispatcher() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageDispatcher $dispatcher */
    $dispatcher = $this->createMock(\PapayaMessageDispatcher::class);
    $manager = new \PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $this->assertAttributeEquals(
      array($dispatcher),
      '_dispatchers',
      $manager
    );
  }

  /**
  * @covers \PapayaMessageManager::dispatch
  */
  public function testDispatch() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message $message */
    $message = $this->createMock(\Papaya\Message::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageDispatcher $dispatcher */
    $dispatcher = $this->createMock(\PapayaMessageDispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->equalTo($message));
    $manager = new \PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->dispatch($message);
  }

  /**
  * @covers \PapayaMessageManager::display
  */
  public function testDisplay() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageDispatcher $dispatcher */
    $dispatcher = $this->createMock(\PapayaMessageDispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\PapayaMessageDisplay::class));
    $manager = new \PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->display(\Papaya\Message::SEVERITY_INFO, 'TEST');
  }

  /**
  * @covers \PapayaMessageManager::log
  */
  public function testLog() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageDispatcher $dispatcher */
    $dispatcher = $this->createMock(\PapayaMessageDispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with(
        new \PapayaMessageLog(
          \Papaya\Message::SEVERITY_INFO,
          \PapayaMessageLogable::GROUP_COMMUNITY,
          'TEST'
        )
      );
    $manager = new \PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->log(\Papaya\Message::SEVERITY_INFO, \PapayaMessageLogable::GROUP_COMMUNITY, 'TEST');
  }

  /**
  * @covers \PapayaMessageManager::log
  */
  public function testLogWithContext() {
    $message = new \PapayaMessageLog(
      \Papaya\Message::SEVERITY_INFO, \PapayaMessageLogable::GROUP_COMMUNITY, 'TEST'
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageContextInterface $context */
    $context = $this->createMock(\PapayaMessageContextInterface::class);
    $message->context()->append($context);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageDispatcher $dispatcher */
    $dispatcher = $this->createMock(\PapayaMessageDispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($message);
    $manager = new \PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->log(
      \Papaya\Message::SEVERITY_INFO,
      \PapayaMessageLogable::GROUP_COMMUNITY,
      'TEST',
      $context
    );
  }

  /**
  * @covers \PapayaMessageManager::log
  */
  public function testLogWithContextGroup() {
    $message = new \PapayaMessageLog(
      \Papaya\Message::SEVERITY_INFO, \PapayaMessageLogable::GROUP_COMMUNITY, 'TEST'
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageContextInterface $context */
    $context = $this->createMock(\PapayaMessageContextInterface::class);
    $message->context()->append($context);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageDispatcher $dispatcher */
    $dispatcher = $this->createMock(\PapayaMessageDispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($message);
    $manager = new \PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->log(
      \Papaya\Message::SEVERITY_INFO,
      \PapayaMessageLogable::GROUP_COMMUNITY,
      'TEST',
      $context
    );
  }

  /**
  * @covers \PapayaMessageManager::log
  */
  public function testLogWithData() {
    $message = new \PapayaMessageLog(
      \Papaya\Message::SEVERITY_INFO, \PapayaMessageLogable::GROUP_COMMUNITY, 'TEST'
    );
    $message->context()->append(new \PapayaMessageContextVariable('data'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageDispatcher $dispatcher */
    $dispatcher = $this->createMock(\PapayaMessageDispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($message);
    $manager = new \PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->log(
      \Papaya\Message::SEVERITY_INFO,
      \PapayaMessageLogable::GROUP_COMMUNITY,
      'TEST',
      'data'
    );
  }

  /**
  * @covers \PapayaMessageManager::encapsulate
  */
  public function testEncapsulate() {
    $manager = new \PapayaMessageManager();
    $manager->papaya($papaya = $this->mockPapaya()->application());
    $sandbox = $manager->encapsulate('substr');
    $this->assertInternalType('callable', $sandbox);
    /** @noinspection PhpUndefinedMethodInspection */
    $this->assertSame($papaya, $sandbox[0]->papaya());
  }

  /**
  * @covers \PapayaMessageManager::hooks
  */
  public function testHooksSettingHooks() {
    $hookOne = $this->createMock(\PapayaMessageHook::class);
    $hookTwo = $this->createMock(\PapayaMessageHook::class);
    $manager = new \PapayaMessageManager();
    $manager->hooks(
      array($hookOne, $hookTwo)
    );
    $this->assertAttributeSame(
      array($hookOne, $hookTwo),
      '_hooks',
      $manager
    );
  }

  /**
  * @covers \PapayaMessageManager::hooks
  */
  public function testHooksReadHooks() {
    $hookOne = $this->createMock(\PapayaMessageHook::class);
    $manager = new \PapayaMessageManager();
    $manager->hooks(array($hookOne));
    $this->assertSame(
      array($hookOne),
      $manager->hooks()
    );
  }

  /**
  * @covers \PapayaMessageManager::hooks
  */
  public function testHooksReadHooksImplizitCreate() {
    $manager = new \PapayaMessageManager();
    $this->assertCount(2, $manager->hooks());
  }

  /**
  * @covers \PapayaMessageManager::setUp
  */
  public function testSetUp() {
    $errorReporting = error_reporting();
    $options = $this->mockPapaya()->options();
    $hookOne = $this->createMock(\PapayaMessageHook::class);
    $hookOne
      ->expects($this->once())
      ->method('activate');
    $hookTwo = $this->createMock(\PapayaMessageHook::class);
    $hookTwo
      ->expects($this->once())
      ->method('activate');

    $manager = new \PapayaMessageManager();
    $manager->hooks(array($hookOne, $hookTwo));
    $manager->setUp($options);

    $this->assertAttributeGreaterThan(
      0, '_startTime', \PapayaMessageContextRuntime::class
    );
    $this->assertEquals(E_ALL & ~E_STRICT, error_reporting());

    error_reporting($errorReporting);
  }


  /**
  * @covers \PapayaMessageManager::debug
  */
  public function testDebug() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageDispatcher $dispatcher */
    $dispatcher = $this->createMock(\PapayaMessageDispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\PapayaMessageLog::class));
    $manager = new \PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->debug('test');
  }
}
