<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterFactoryExceptionTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryException
   */
  public function testThrowException() {
    $this->setExpectedException(PapayaFilterFactoryException::class);
    throw new PapayaFilterFactoryException_TestProxy();
  }

}

class PapayaFilterFactoryException_TestProxy extends PapayaFilterFactoryException {

  public function getFilter() {
  }
}
