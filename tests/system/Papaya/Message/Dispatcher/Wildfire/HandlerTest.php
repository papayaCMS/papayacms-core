<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaMessageDispatcherWildfireHandlerTest extends PapayaTestCase {

  private $_collectedHeaders = array();

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::__construct
  */
  public function testConstructor() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $this->assertAttributeSame(
      $callback,
      '_callback',
      $handler
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::__construct
  */
  public function testConstructorWithInvalidCallbackExpectingException() {
    $this->setExpectedException('InvalidArgumentException');
    new PapayaMessageDispatcherWildfireHandler(NULL);
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::sendInitialization
  * @covers PapayaMessageDispatcherWildfireHandler::resetCounters
  * @covers PapayaMessageDispatcherWildfireHandler::_send
  */
  public function testSendInitialization() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->sendInitialization(PapayaMessageDispatcherWildfireHandler::HEADER_MAIN);
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3'
      ),
      $this->_collectedHeaders
    );
    $this->assertAttributeEquals(
      array(
        PapayaMessageDispatcherWildfireHandler::HEADER_MAIN => 1,
        PapayaMessageDispatcherWildfireHandler::HEADER_CONSOLE => 0,
        PapayaMessageDispatcherWildfireHandler::HEADER_DUMP => 0,
        PapayaMessageDispatcherWildfireHandler::HEADER_DATA => 0
      ),
      '_counter',
      'PapayaMessageDispatcherWildfireHandler'
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::sendMessage
  */
  public function testSendMessage() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->sendMessage('Hallo Welt');
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 29|[{"Type":"LOG"},"Hallo Welt"]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::sendMessage
  */
  public function testSendMessageWithTypeParameter() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->sendMessage('Hallo Welt', 'INFO');
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 30|[{"Type":"INFO"},"Hallo Welt"]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::sendMessage
  */
  public function testSendMessageWithLabelParameter() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->sendMessage('Hallo Welt', 'INFO', 'Sample');
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 47|[{"Type":"INFO","Label":"Sample"},"Hallo Welt"]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::sendDump
  */
  public function testSendDump() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->sendDump(array());
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 19|[{"Type":"LOG"},[]]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::startGroup
  */
  public function testStartGroup() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->startGroup();
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 41|[{"Type":"GROUP_START","Label":" "},null]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::startGroup
  */
  public function testStartGroupWithParameters() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->startGroup('Sample', TRUE);
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 46|[{"Type":"GROUP_START","Label":"Sample"},null]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::endGroup
  */
  public function testEndGroup() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->endGroup();
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 27|[{"Type":"GROUP_END"},null]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::sendData
  */
  public function testSendData() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->sendData(
      PapayaMessageDispatcherWildfireHandler::HEADER_CONSOLE,
      array(
        'Type' => 'INFO'
      ),
      NULL
    );
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 22|[{"Type":"INFO"},null]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::sendData
  */
  public function testSendDataTwoTimes() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->sendData(
      PapayaMessageDispatcherWildfireHandler::HEADER_CONSOLE,
      array(
        'Type' => 'INFO'
      ),
      'Info'
    );
    $handler->sendData(
      PapayaMessageDispatcherWildfireHandler::HEADER_CONSOLE,
      array(
        'Type' => 'WARN'
      ),
      'Warning'
    );
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 24|[{"Type":"INFO"},"Info"]|',
        'X-Wf-1-1-1-2: 27|[{"Type":"WARN"},"Warning"]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::sendData
  */
  public function testSendDataWithLargeContent() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    PapayaMessageDispatcherWildfireHandler::$lengthLimit = 30;
    $handler->sendData(
      PapayaMessageDispatcherWildfireHandler::HEADER_CONSOLE,
      array(
        'Type' => 'INFO'
      ),
      'Not really large but large enough'
    );
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 53|[{"Type":"INFO"},"Not really l|\\',
        'X-Wf-1-1-1-2: |arge but large enough"]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireHandler::sendData
  */
  public function testSendDumpData() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new PapayaMessageDispatcherWildfireHandler($callback);
    $handler->resetCounters();
    $handler->sendData(
      PapayaMessageDispatcherWildfireHandler::HEADER_DUMP,
      NULL,
      array('Hello' => 'World')
    );
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-2: http://meta.firephp.org/Wildfire/Structure/FirePHP/Dump/0.1',
        'X-Wf-1-2-1-1: 17|{"Hello":"World"}|'
      ),
      $this->_collectedHeaders
    );
  }

  public function callbackCollectHeaders($header) {
    $this->_collectedHeaders[] = $header;
  }
}
