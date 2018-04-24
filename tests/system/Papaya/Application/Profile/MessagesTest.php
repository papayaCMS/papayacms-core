<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileMessagesTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileMessages::createObject
  */
  public function testCreateObject() {
    $application = $this->createMock(PapayaApplication::class);
    $profile = new PapayaApplicationProfileMessages();
    $messages = $profile->createObject($application);
    $this->assertInstanceOf(
      PapayaMessageManager::class, $messages
    );
    $dispatchers = $this->readAttribute($messages, '_dispatchers');
    $this->assertInstanceOf(
      PapayaMessageDispatcherTemplate::class, $dispatchers[0]
    );
    $this->assertInstanceOf(
      PapayaMessageDispatcherDatabase::class, $dispatchers[1]
    );
    $this->assertInstanceOf(
      PapayaMessageDispatcherWildfire::class, $dispatchers[2]
    );
    $this->assertInstanceOf(
      PapayaMessageDispatcherXhtml::class, $dispatchers[3]
    );
  }
}
