<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageDispatcherWildfireTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDispatcherWildfire::allow
  */
  public function testAllowExpectingFalse() {
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->papaya(
      $this->mockPapaya()->application(
        array(
          'Options' => $this->mockPapaya()->options(
            array(
              'PAPAYA_PROTOCOL_WILDFIRE' => FALSE
            )
          )
        )
      )
    );
    $this->assertFalse($dispatcher->allow());
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::allow
  * @covers PapayaMessageDispatcherWildfire::useable
  * @backupGlobals
  */
  public function testAllowWithUserAgentAllowExpectingFalse() {
    $_SERVER['HTTP_USER_AGENT'] = 'FirePHP';
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->papaya(
      $this->mockPapaya()->application(
        array(
          'Options' => $this->mockPapaya()->options(
            array(
              'PAPAYA_PROTOCOL_WILDFIRE' => TRUE
            )
          )
        )
      )
    );
    $this->assertFalse($dispatcher->allow());
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::allow
  * @backupGlobals
  */
  public function testAllowWithUserAgentAllowExpectingTrue() {
    $_SERVER['HTTP_USER_AGENT'] = 'FirePHP';
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->papaya(
      $this->mockPapaya()->application(
        array(
          'Options' => $this->mockPapaya()->options(
            array(
              'PAPAYA_PROTOCOL_WILDFIRE' => TRUE
            )
          )
        )
      )
    );
    $this->assertTrue($dispatcher->allow(array($this, 'useableSimulationCallback')));
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::setHandler
  */
  public function testSetHandler() {
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $this->assertAttributeSame(
      $handler,
      '_handler',
      $dispatcher
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::getHandler
  */
  public function testGetHandler() {
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $this->assertSame(
      $handler,
      $dispatcher->getHandler()
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::getHandler
  */
  public function testGetHandlerExpectingImplicitCreate() {
    $callback = array($this, 'collectHeader');
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $this->assertInstanceOf(
      PapayaMessageDispatcherWildfireHandler::class,
      $dispatcher->getHandler()
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::getWildfireMessageType
  * @dataProvider getWildfireMessageTypeDataProvider
  *
  * @param string $expected
  * @param integer $type
  */
  public function testGetWildfireMessageType($expected, $type) {
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $this->assertEquals($expected, $dispatcher->getWildfireMessageType($type));
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::getWildfireGroupLabelFromType
  * @dataProvider getWildfireGroupLabelFromTypeDataProvider
  *
  * @param string $expected
  * @param integer $type
  */
  public function testGetWildfireGroupLabelFromType($expected, $type) {
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $this->assertEquals($expected, $dispatcher->getWildfireGroupLabelFromType($type));
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::dispatch
  */
  public function testDispatchExpectingFalse() {
    $message = $this->createMock(PapayaMessage::class);
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $this->assertFalse($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::send
  */
  public function testSendWithSimpleMessage() {
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $handler
      ->expects($this->once())
      ->method('sendMessage')
      ->with($this->equalTo('Hello'), 'LOG');
    $message = $this->createMock(PapayaMessageLogable::class);
    $message
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_DEBUG));
    $message
      ->expects($this->any())
      ->method('getMessage')
      ->will($this->returnValue('Hello'));
    $message
      ->expects($this->any())
      ->method('context')
      ->will($this->returnValue($this->createMock(PapayaMessageContextGroup::class)));
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $dispatcher->send($message);
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::send
  */
  public function testSendWithMessageIncludingContext() {
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $handler
      ->expects($this->once())
      ->method('startGroup')
      ->with($this->equalTo('Debug'));
    $handler
      ->expects($this->once())
      ->method('sendMessage')
      ->with($this->equalTo('Hello'), 'LOG');
    $handler
      ->expects($this->once())
      ->method('endGroup');
    $message = $this->createMock(PapayaMessageLogable::class);
    $message
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_DEBUG));
    $message
      ->expects($this->any())
      ->method('getMessage')
      ->will($this->returnValue('Hello'));
    $message
      ->expects($this->any())
      ->method('context')
      ->will(
        $this->returnValue(array($this->createMock(PapayaMessageContextInterface::class)))
      );
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $dispatcher->send($message);
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::sendContext
  */
  public function testSendContextWithString() {
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $handler
      ->expects($this->once())
      ->method('sendMessage')
      ->with($this->equalTo('Hello'), 'LOG');
    $context = $this->createMock(PapayaMessageContextInterfaceString::class);
    $context
      ->expects($this->any())
      ->method('asString')
      ->will($this->returnValue('Hello'));
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $dispatcher->sendContext($context);
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::sendContext
  * @covers PapayaMessageDispatcherWildfire::_sendContextVariable
  */
  public function testSendContextWithVariable() {
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $handler
      ->expects($this->once())
      ->method('sendDump')
      ->with(NULL);
    $context = $this->getMock(PapayaMessageContextVariable::class, array(), array(42));
    $context
      ->expects($this->once())
      ->method('acceptVisitor')
      ->with($this->isInstanceOf(PapayaMessageContextVariableVisitor::class));
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $dispatcher->sendContext($context);
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::sendContext
  */
  public function testSendContextWithList() {
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $handler
      ->expects($this->at(0))
      ->method('startGroup')
      ->with($this->equalTo('List'));
    $handler
      ->expects($this->at(1))
      ->method('sendMessage')
      ->with($this->equalTo('(1) Hello'), 'LOG');
    $handler
      ->expects($this->at(2))
      ->method('sendMessage')
      ->with($this->equalTo('(2) World'), 'LOG');
    $handler
      ->expects($this->at(3))
      ->method('endGroup');
    $context = $this->getMock(
      PapayaMessageContextInterfaceList::class,
      array(),
      array(),
      PapayaMessageContextInterfaceList_UnitTest_Mock::class
    );
    $context
      ->expects($this->any())
      ->method('getLabel')
      ->will($this->returnValue('List'));
    $context
      ->expects($this->any())
      ->method('asArray')
      ->will($this->returnValue(array('Hello', 'World')));
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $dispatcher->sendContext($context);
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::sendContext
  * @covers PapayaMessageDispatcherWildfire::_sendContextTrace
  * @covers PapayaMessageDispatcherWildfire::_getArrayElement
  * @covers PapayaMessageDispatcherWildfire::_traceElementToArray
  */
  public function testSendContextWithBacktrace() {
    $backtrace = array(
      array(
        'class' => 'classnameOne',
        'type' => '->',
        'function' => 'method',
        'file' => 'test.php',
        'line' => 42,
      ),
      array(
        'class' => 'classnameTwo',
        'type' => '::',
        'function' => 'function',
        'file' => 'testTwo.php',
        'line' => 21,
        'args' => array('foo')
      ),
    );
    $expected = array(
      'Class' => 'classnameOne',
      'Type' => '->',
      'Message' => NULL,
      'Function' => 'method',
      'File' => 'test.php',
      'Line' => 42,
      'Args' => NULL,
      'Trace' => array(
        array(
          'class' => 'classnameTwo',
          'type' => '::',
          'function' => 'function',
          'file' => 'testTwo.php',
          'line' => 21,
          'args' => array('foo')
        )
      )
    );
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $handler
      ->expects($this->once())
      ->method('sendMessage')
      ->with($this->equalTo($expected), 'TRACE');
    $context = $this->createMock(PapayaMessageContextBacktrace::class);
    $context
      ->expects($this->once())
      ->method('getBacktrace')
      ->will($this->returnValue($backtrace));
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $dispatcher->sendContext($context);
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::sendContext
  * @covers PapayaMessageDispatcherWildfire::_sendContextTrace
  */
  public function testSendContextWithEmptyBacktrace() {
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $handler
      ->expects($this->never())
      ->method('sendMessage');
    $context = $this->createMock(PapayaMessageContextBacktrace::class);
    $context
      ->expects($this->once())
      ->method('getBacktrace')
      ->will($this->returnValue(array()));
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $dispatcher->sendContext($context);
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::sendContext
  * @covers PapayaMessageDispatcherWildfire::_sendContextTable
  * @covers PapayaMessageDispatcherWildfire::formatTableValue
  */
  public function testSendContextWithTableWithColumns() {
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $handler
      ->expects($this->once())
      ->method('sendMessage')
      ->with(
        $this->equalTo(
          array(
            array('Column One', 'Column Two'),
            array('Data One', 'Data Two')
          )
        ),
        'TABLE',
        'Sample Table'
      );
    $context = $this->createMock(PapayaMessageContextInterfaceTable::class);
    $context
      ->expects($this->once())
      ->method('getLabel')
      ->will($this->returnValue('Sample Table'));
    $context
      ->expects($this->once())
      ->method('getColumns')
      ->will($this->returnValue(array('c1' => 'Column One', 'c2' => 'Column Two')));
    $context
      ->expects($this->once())
      ->method('getRowCount')
      ->will($this->returnValue(1));
    $context
      ->expects($this->once())
      ->method('getRow')
      ->with($this->equalTo(0))
      ->will($this->returnValue(array('c1' => 'Data One', 'c2' => 'Data Two')));
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $dispatcher->sendContext($context);
  }

  /**
  * @covers PapayaMessageDispatcherWildfire::sendContext
  * @covers PapayaMessageDispatcherWildfire::_sendContextTable
  * @covers PapayaMessageDispatcherWildfire::formatTableValue
  */
  public function testSendContextWithTableWithoutColumns() {
    $callback = array($this, 'collectHeader');
    $handler = $this->getMock(PapayaMessageDispatcherWildfireHandler::class, array(), array($callback));
    $handler
      ->expects($this->once())
      ->method('sendMessage')
      ->with(
        $this->equalTo(
          array(array(), array('1.1', '1.2'), array('2.1', ''))
        ),
        'TABLE',
        'Sample Table'
      );
    $context = $this->createMock(PapayaMessageContextInterfaceTable::class);
    $context
      ->expects($this->once())
      ->method('getLabel')
      ->will($this->returnValue('Sample Table'));
    $context
      ->expects($this->once())
      ->method('getColumns')
      ->will($this->returnValue(NULL));
    $context
      ->expects($this->once())
      ->method('getRowCount')
      ->will($this->returnValue(2));
    $context
      ->expects($this->exactly(2))
      ->method('getRow')
      ->withConsecutive([0], [1])
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue(array('1.1', '1.2')),
          $this->returnValue(array('2.1', NULL))
        )
      );
    $dispatcher = new PapayaMessageDispatcherWildfire();
    $dispatcher->setHandler($handler);
    $dispatcher->sendContext($context);
  }

  /*************************
  * Callbacks
  *************************/

  public function useableSimulationCallback() {
    return TRUE;
  }

  public function collectHeader($header) {

  }

  /*************************
  * Data provider
  *************************/

  public static function getWildfireMessageTypeDataProvider() {
    return array(
      array('LOG', -1),
      array('LOG', PapayaMessage::SEVERITY_DEBUG),
      array('INFO', PapayaMessage::SEVERITY_INFO),
      array('WARN', PapayaMessage::SEVERITY_WARNING),
      array('ERROR', PapayaMessage::SEVERITY_ERROR)
    );
  }

  public static function getWildfireGroupLabelFromTypeDataProvider() {
    return array(
      array('Debug', -1),
      array('Debug', PapayaMessage::SEVERITY_DEBUG),
      array('Information', PapayaMessage::SEVERITY_INFO),
      array('Warning', PapayaMessage::SEVERITY_WARNING),
      array('Error', PapayaMessage::SEVERITY_ERROR)
    );
  }
}
