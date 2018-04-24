<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseExceptionConnectTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseExceptionConnect::__construct
  */
  public function testConstructorWithMessage() {
    $exception = new PapayaDatabaseExceptionConnect('Sample');
    $this->assertEquals(
      'Sample', $exception->getMessage()
    );
    $this->assertEquals(
      PapayaDatabaseException::SEVERITY_ERROR, $exception->getSeverity()
    );
  }

  /**
  * @covers PapayaDatabaseExceptionConnect::__construct
  */
  public function testConstructorWithCode() {
    $exception = new PapayaDatabaseExceptionConnect('Sample', 42);
    $this->assertEquals(
      42, $exception->getCode()
    );
  }
}
