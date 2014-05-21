<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaMessageDispatcherTemplateTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDispatcherTemplate::dispatch
  * @backupGlobals enabled
  */
  public function testDispatch() {
    $message = $this->getMock('PapayaMessageDisplayable');
    $message
      ->expects($this->once())
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_WARNING));
    $message
      ->expects($this->once())
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $values = $this->getMock('PapayaTemplateValues');
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
    $GLOBALS['PAPAYA_LAYOUT'] = $this->getMock('PapayaTemplate');
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
    $message = $this->getMock('PapayaMessage');
    $dispatcher = new PapayaMessageDispatcherTemplate();
    $this->assertFalse($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherTemplate::dispatch
  */
  public function testDispatchWithoutGlobalObjectExpectingFalse() {
    $message = $this->getMock('PapayaMessageDisplayable');
    $dispatcher = new PapayaMessageDispatcherTemplate();
    $this->assertFalse($dispatcher->dispatch($message));
  }
}