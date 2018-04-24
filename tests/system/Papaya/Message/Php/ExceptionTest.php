<?php
require_once __DIR__.'/../../../../bootstrap.php';


class PapayaMessagePhpExceptionTest extends PapayaTestCase {

  /**
  * @covers PapayaMessagePhpException::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessagePhpException(
      new ErrorException('Sample Error', 0, E_USER_ERROR, 'sample.php', 42)
    );
    $this->assertAttributeEquals(
      PapayaMessage::SEVERITY_ERROR,
      '_type',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Error',
      '_message',
      $message
    );
    $this->assertEquals(
      1,
      count($message->context())
    );
  }
}
