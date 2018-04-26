<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessagePhpErrorTest extends PapayaTestCase {

  /**
  * @covers PapayaMessagePhpError::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessagePhpError(E_USER_WARNING, 'Sample Warning', 'Sample Context');
    $this->assertAttributeEquals(
      PapayaMessage::SEVERITY_WARNING,
      '_type',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Warning',
      '_message',
      $message
    );
    $this->assertCount(2, $message->context());
  }
}
