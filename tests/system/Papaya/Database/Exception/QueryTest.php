<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaDatabaseExceptionQueryTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseExceptionQuery::__construct
  */
  public function testConstructorWithMessage() {
    $exception = new PapayaDatabaseExceptionQuery('Sample');
    $this->assertEquals(
      'Sample', $exception->getMessage()
    );
  }

  /**
  * @covers PapayaDatabaseExceptionQuery::__construct
  */
  public function testConstructorWithCode() {
    $exception = new PapayaDatabaseExceptionQuery('Sample', 42);
    $this->assertEquals(
      42, $exception->getCode()
    );
  }

  /**
  * @covers PapayaDatabaseExceptionQuery::__construct
  * @covers PapayaDatabaseExceptionQuery::getSeverity
  */
  public function testConstructorWithSeverity() {
    $exception = new PapayaDatabaseExceptionQuery(
      'Sample', 42, PapayaDatabaseException::SEVERITY_INFO
    );
    $this->assertEquals(
      PapayaDatabaseException::SEVERITY_INFO, $exception->getSeverity()
    );
  }

  /**
  * @covers PapayaDatabaseExceptionQuery::__construct
  * @covers PapayaDatabaseExceptionQuery::getSeverity
  */
  public function testConstructorWithNullAsSeverity() {
    $exception = new PapayaDatabaseExceptionQuery('Sample', 42, NULL);
    $this->assertEquals(
      PapayaDatabaseException::SEVERITY_ERROR, $exception->getSeverity()
    );
  }

  /**
  * @covers PapayaDatabaseExceptionQuery::__construct
  * @covers PapayaDatabaseExceptionQuery::getStatement
  */
  public function testConstructorWithSql() {
    $exception = new PapayaDatabaseExceptionQuery(
      'Sample', 42, PapayaDatabaseException::SEVERITY_INFO, 'Select SQL'
    );
    $this->assertEquals(
      'Select SQL', $exception->getStatement()
    );
  }
}
