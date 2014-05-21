<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryExceptionTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryException
   */
  public function testThrowException() {
    $this->setExpectedException('PapayaUiDialogFieldFactoryException');
    throw new PapayaUiDialogFieldFactoryException_TestProxy();
  }

}

class PapayaUiDialogFieldFactoryException_TestProxy extends PapayaUiDialogFieldFactoryException {

  public function getFilter() {
  }
}
