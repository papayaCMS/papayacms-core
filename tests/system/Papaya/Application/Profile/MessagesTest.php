<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaApplicationProfileMessagesTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileMessages::createObject
  */
  public function testCreateObject() {
    $application = $this->getMock('PapayaApplication');
    $profile = new PapayaApplicationProfileMessages();
    $messages = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaMessageManager', $messages
    );
    $dispatchers = $this->readAttribute($messages, '_dispatchers');
    $this->assertInstanceOf(
      'PapayaMessageDispatcherTemplate', $dispatchers[0]
    );
    $this->assertInstanceOf(
      'PapayaMessageDispatcherDatabase', $dispatchers[1]
    );
    $this->assertInstanceOf(
      'PapayaMessageDispatcherWildfire', $dispatchers[2]
    );
    $this->assertInstanceOf(
      'PapayaMessageDispatcherXhtml', $dispatchers[3]
    );
  }
}