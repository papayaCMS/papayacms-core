<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageDispatcherTemplateTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDispatcherTemplate::dispatch
  * @backupGlobals enabled
  */
  public function testDispatch() {
    $message = $this->createMock(PapayaMessageDisplayable::class);
    $message
      ->expects($this->once())
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_WARNING));
    $message
      ->expects($this->once())
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $values = $this->createMock(PapayaTemplateValues::class);
    $values
      ->expects($this->once())
      ->method('append')
      ->with(
        '/page/messages',
        'message',
        array(
          'severity' => 'warning'
        ),
        $this->equalTo('Sample message')
      );
    $GLOBALS['PAPAYA_LAYOUT'] = $this->createMock(PapayaTemplate::class);
    $GLOBALS['PAPAYA_LAYOUT']
      ->expects($this->once())
      ->method('values')
      ->will($this->returnValue($values));
    $dispatcher = new PapayaMessageDispatcherTemplate();
    $this->assertTrue($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherTemplate::dispatch
  */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    $message = $this->createMock(PapayaMessage::class);
    $dispatcher = new PapayaMessageDispatcherTemplate();
    $this->assertFalse($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherTemplate::dispatch
  */
  public function testDispatchWithoutGlobalObjectExpectingFalse() {
    $message = $this->createMock(PapayaMessageDisplayable::class);
    $dispatcher = new PapayaMessageDispatcherTemplate();
    $this->assertFalse($dispatcher->dispatch($message));
  }
}
