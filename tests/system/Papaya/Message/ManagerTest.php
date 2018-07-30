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
  * @covers \Papaya\Message\Manager::addDispatcher
  */
  public function testAddDispatcher() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Dispatcher $dispatcher */
    $dispatcher = $this->createMock(\Papaya\Message\Dispatcher::class);
    $manager = new \Papaya\Message\Manager();
    $manager->addDispatcher($dispatcher);
    $this->assertAttributeEquals(
      array($dispatcher),
      '_dispatchers',
      $manager
    );
  }

  /**
  * @covers \Papaya\Message\Manager::dispatch
  */
  public function testDispatch() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message $message */
    $message = $this->createMock(\Papaya\Message::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Dispatcher $dispatcher */
    $dispatcher = $this->createMock(\Papaya\Message\Dispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->equalTo($message));
    $manager = new \Papaya\Message\Manager();
    $manager->addDispatcher($dispatcher);
    $manager->dispatch($message);
  }

  /**
  * @covers \Papaya\Message\Manager::display
  */
  public function testDisplay() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Dispatcher $dispatcher */
    $dispatcher = $this->createMock(\Papaya\Message\Dispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Display::class));
    $manager = new \Papaya\Message\Manager();
    $manager->addDispatcher($dispatcher);
    $manager->display(\Papaya\Message::SEVERITY_INFO, 'TEST');
  }

  /**
  * @covers \Papaya\Message\Manager::log
  */
  public function testLog() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Dispatcher $dispatcher */
    $dispatcher = $this->createMock(\Papaya\Message\Dispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with(
        new \Papaya\Message\Log(
          \Papaya\Message::SEVERITY_INFO,
          \Papaya\Message\Logable::GROUP_COMMUNITY,
          'TEST'
        )
      );
    $manager = new \Papaya\Message\Manager();
    $manager->addDispatcher($dispatcher);
    $manager->log(\Papaya\Message::SEVERITY_INFO, \Papaya\Message\Logable::GROUP_COMMUNITY, 'TEST');
  }

  /**
  * @covers \Papaya\Message\Manager::log
  */
  public function testLogWithContext() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message::SEVERITY_INFO, \Papaya\Message\Logable::GROUP_COMMUNITY, 'TEST'
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Context\Data $context */
    $context = $this->createMock(\Papaya\Message\Context\Data::class);
    $message->context()->append($context);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Dispatcher $dispatcher */
    $dispatcher = $this->createMock(\Papaya\Message\Dispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($message);
    $manager = new \Papaya\Message\Manager();
    $manager->addDispatcher($dispatcher);
    $manager->log(
      \Papaya\Message::SEVERITY_INFO,
      \Papaya\Message\Logable::GROUP_COMMUNITY,
      'TEST',
      $context
    );
  }

  /**
  * @covers \Papaya\Message\Manager::log
  */
  public function testLogWithContextGroup() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message::SEVERITY_INFO, \Papaya\Message\Logable::GROUP_COMMUNITY, 'TEST'
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Context\Data $context */
    $context = $this->createMock(\Papaya\Message\Context\Data::class);
    $message->context()->append($context);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Dispatcher $dispatcher */
    $dispatcher = $this->createMock(\Papaya\Message\Dispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($message);
    $manager = new \Papaya\Message\Manager();
    $manager->addDispatcher($dispatcher);
    $manager->log(
      \Papaya\Message::SEVERITY_INFO,
      \Papaya\Message\Logable::GROUP_COMMUNITY,
      'TEST',
      $context
    );
  }

  /**
  * @covers \Papaya\Message\Manager::log
  */
  public function testLogWithData() {
    $message = new \Papaya\Message\Log(
      \Papaya\Message::SEVERITY_INFO, \Papaya\Message\Logable::GROUP_COMMUNITY, 'TEST'
    );
    $message->context()->append(new \Papaya\Message\Context\Variable('data'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Dispatcher $dispatcher */
    $dispatcher = $this->createMock(\Papaya\Message\Dispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($message);
    $manager = new \Papaya\Message\Manager();
    $manager->addDispatcher($dispatcher);
    $manager->log(
      \Papaya\Message::SEVERITY_INFO,
      \Papaya\Message\Logable::GROUP_COMMUNITY,
      'TEST',
      'data'
    );
  }

  /**
  * @covers \Papaya\Message\Manager::encapsulate
  */
  public function testEncapsulate() {
    $manager = new \Papaya\Message\Manager();
    $manager->papaya($papaya = $this->mockPapaya()->application());
    $sandbox = $manager->encapsulate('substr');
    $this->assertInternalType('callable', $sandbox);
    /** @noinspection PhpUndefinedMethodInspection */
    $this->assertSame($papaya, $sandbox[0]->papaya());
  }

  /**
  * @covers \Papaya\Message\Manager::hooks
  */
  public function testHooksSettingHooks() {
    $hookOne = $this->createMock(\Papaya\Message\Hook::class);
    $hookTwo = $this->createMock(\Papaya\Message\Hook::class);
    $manager = new \Papaya\Message\Manager();
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
  * @covers \Papaya\Message\Manager::hooks
  */
  public function testHooksReadHooks() {
    $hookOne = $this->createMock(\Papaya\Message\Hook::class);
    $manager = new \Papaya\Message\Manager();
    $manager->hooks(array($hookOne));
    $this->assertSame(
      array($hookOne),
      $manager->hooks()
    );
  }

  /**
  * @covers \Papaya\Message\Manager::hooks
  */
  public function testHooksReadHooksImplizitCreate() {
    $manager = new \Papaya\Message\Manager();
    $this->assertCount(2, $manager->hooks());
  }

  /**
  * @covers \Papaya\Message\Manager::setUp
  */
  public function testSetUp() {
    $errorReporting = error_reporting();
    $options = $this->mockPapaya()->options();
    $hookOne = $this->createMock(\Papaya\Message\Hook::class);
    $hookOne
      ->expects($this->once())
      ->method('activate');
    $hookTwo = $this->createMock(\Papaya\Message\Hook::class);
    $hookTwo
      ->expects($this->once())
      ->method('activate');

    $manager = new \Papaya\Message\Manager();
    $manager->hooks(array($hookOne, $hookTwo));
    $manager->setUp($options);

    $this->assertAttributeGreaterThan(
      0, '_startTime', \Papaya\Message\Context\Runtime::class
    );
    $this->assertEquals(E_ALL & ~E_STRICT, error_reporting());

    error_reporting($errorReporting);
  }


  /**
  * @covers \Papaya\Message\Manager::debug
  */
  public function testDebug() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Dispatcher $dispatcher */
    $dispatcher = $this->createMock(\Papaya\Message\Dispatcher::class);
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Log::class));
    $manager = new \Papaya\Message\Manager();
    $manager->addDispatcher($dispatcher);
    $manager->debug('test');
  }
}
