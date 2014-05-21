<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaDatabaseExceptionTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseException::__construct
  */
  public function testConstructorWithMessage() {
    $exception = new PapayaDatabaseException('Sample');
    $this->assertEquals(
      'Sample', $exception->getMessage()
    );
  }

  /**
  * @covers PapayaDatabaseException::__construct
  */
  public function testConstructorWithCode() {
    $exception = new PapayaDatabaseException('Sample', 42);
    $this->assertEquals(
      42, $exception->getCode()
    );
  }

  /**
  * @covers PapayaDatabaseException::__construct
  * @covers PapayaDatabaseException::getSeverity
  */
  public function testConstructorWithSeverity() {
    $exception = new PapayaDatabaseException('Sample', 42, PapayaDatabaseException::SEVERITY_INFO);
    $this->assertEquals(
      PapayaDatabaseException::SEVERITY_INFO, $exception->getSeverity()
    );
  }

  /**
  * @covers PapayaDatabaseException::__construct
  * @covers PapayaDatabaseException::getSeverity
  */
  public function testConstructorWithNullAsSeverity() {
    $exception = new PapayaDatabaseException('Sample', 42, NULL);
    $this->assertEquals(
      PapayaDatabaseException::SEVERITY_ERROR, $exception->getSeverity()
    );
  }
}
