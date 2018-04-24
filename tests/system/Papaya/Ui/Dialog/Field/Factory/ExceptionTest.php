<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryExceptionTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryException
   */
  public function testThrowException() {
    $this->setExpectedException(PapayaUiDialogFieldFactoryException::class);
    throw new PapayaUiDialogFieldFactoryException_TestProxy();
  }

}

class PapayaUiDialogFieldFactoryException_TestProxy extends PapayaUiDialogFieldFactoryException {

  public function getFilter() {
  }
}
