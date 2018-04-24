<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaMessageManagerTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageManager::addDispatcher
  */
  public function testAddDispatcher() {
    $dispatcher = $this->getMock('PapayaMessageDispatcher');
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $this->assertAttributeEquals(
      array($dispatcher),
      '_dispatchers',
      $manager
    );
  }

  /**
  * @covers PapayaMessageManager::dispatch
  */
  public function testDispatch() {
    $message = $this->getMock('PapayaMessage');
    $dispatcher = $this->getMock('PapayaMessageDispatcher', array('dispatch'));
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->equalTo($message));
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->dispatch($message);
  }

  /**
  * @covers PapayaMessageManager::display
  */
  public function testDisplay() {
    $dispatcher = $this->getMock('PapayaMessageDispatcher', array('dispatch'));
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->display(PapayaMessage::SEVERITY_INFO, 'TEST');
  }

  /**
  * @covers PapayaMessageManager::log
  */
  public function testLog() {
    $dispatcher = $this->getMock('PapayaMessageDispatcher', array('dispatch'));
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with(
        new PapayaMessageLog(PapayaMessage::SEVERITY_INFO, PapayaMessageLogable::GROUP_COMMUNITY, 'TEST')
      );
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->log(PapayaMessage::SEVERITY_INFO, PapayaMessageLogable::GROUP_COMMUNITY, 'TEST');
  }

  /**
  * @covers PapayaMessageManager::log
  */
  public function testLogWithContext() {
    $message = new PapayaMessageLog(
      PapayaMessage::SEVERITY_INFO, PapayaMessageLogable::GROUP_COMMUNITY, 'TEST'
    );
    $message->context()->append(
      $context = $this->getMock('PapayaMessageContextInterface')
    );
    $dispatcher = $this->getMock('PapayaMessageDispatcher', array('dispatch'));
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($message);
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->log(
      PapayaMessage::SEVERITY_INFO,
      PapayaMessageLogable::GROUP_COMMUNITY,
      'TEST',
      $context
    );
  }

  /**
  * @covers PapayaMessageManager::log
  */
  public function testLogWithContextGroup() {
    $message = new PapayaMessageLog(
      PapayaMessage::SEVERITY_INFO, PapayaMessageLogable::GROUP_COMMUNITY, 'TEST'
    );
    $message->setContext(
      $context = $this->getMock('PapayaMessageContextGroup')
    );
    $dispatcher = $this->getMock('PapayaMessageDispatcher', array('dispatch'));
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($message);
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->log(
      PapayaMessage::SEVERITY_INFO,
      PapayaMessageLogable::GROUP_COMMUNITY,
      'TEST',
      $context
    );
  }

  /**
  * @covers PapayaMessageManager::log
  */
  public function testLogWithData() {
    $message = new PapayaMessageLog(
      PapayaMessage::SEVERITY_INFO, PapayaMessageLogable::GROUP_COMMUNITY, 'TEST'
    );
    $message->context()->append(
      $context = new PapayaMessageContextVariable('data')
    );
    $dispatcher = $this->getMock('PapayaMessageDispatcher', array('dispatch'));
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($message);
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->log(
      PapayaMessage::SEVERITY_INFO,
      PapayaMessageLogable::GROUP_COMMUNITY,
      'TEST',
      'data'
    );
  }

  /**
  * @covers PapayaMessageManager::encapsulate
  */
  public function testEncapsulate() {
    $manager = new PapayaMessageManager();
    $manager->papaya($papaya = $this->mockPapaya()->application());
    $sandbox = $manager->encapsulate('substr');
    $this->assertTrue(is_callable($sandbox));
    $this->assertSame($papaya, $sandbox[0]->papaya());
  }

  /**
  * @covers PapayaMessageManager::hooks
  */
  public function testHooksSettingHooks() {
    $hookOne = $this->getMock('PapayaMessageHook');
    $hookTwo = $this->getMock('PapayaMessageHook');
    $manager = new PapayaMessageManager();
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
  * @covers PapayaMessageManager::hooks
  */
  public function testHooksReadHooks() {
    $hookOne = $this->getMock('PapayaMessageHook');
    $manager = new PapayaMessageManager();
    $manager->hooks(array($hookOne));
    $this->assertSame(
      array($hookOne),
      $manager->hooks()
    );
  }

  /**
  * @covers PapayaMessageManager::hooks
  */
  public function testHooksReadHooksImplizitCreate() {
    $manager = new PapayaMessageManager();
    $this->assertEquals(
      2,
      count($manager->hooks())
    );
  }

  /**
  * @covers PapayaMessageManager::setUp
  */
  public function testSetUp() {
    $errorReporting = error_reporting();
    $options = $this->mockPapaya()->options();
    $hookOne = $this->getMock('PapayaMessageHook');
    $hookOne
      ->expects($this->once())
      ->method('activate');
    $hookTwo = $this->getMock('PapayaMessageHook');
    $hookTwo
      ->expects($this->once())
      ->method('activate');

    $manager = new PapayaMessageManager();
    $manager->hooks(array($hookOne, $hookTwo));
    $manager->setUp($options);

    $this->assertAttributeGreaterThan(
      0, '_startTime', 'PapayaMessageContextRuntime'
    );
    $this->assertEquals(E_ALL & ~E_STRICT, error_reporting());

    error_reporting($errorReporting);
  }


  /**
  * @covers PapayaMessageManager::debug
  */
  public function testDebug() {
    $dispatcher = $this->getMock('PapayaMessageDispatcher', array('dispatch'));
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageLog'));
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->debug('test');
  }
}
