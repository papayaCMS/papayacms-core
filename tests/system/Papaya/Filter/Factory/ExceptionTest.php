<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterFactoryExceptionTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryException
   */
  public function testThrowException() {
    $this->setExpectedException('PapayaFilterFactoryException');
    throw new PapayaFilterFactoryException_TestProxy();
  }

}

class PapayaFilterFactoryException_TestProxy extends PapayaFilterFactoryException {

  public function getFilter() {
  }
}
