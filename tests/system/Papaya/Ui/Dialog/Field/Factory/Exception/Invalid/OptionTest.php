<?php
require_once(dirname(__FILE__).'/../../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryExceptionInvalidOptionTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryExceptionInvalidOption::__construct
   */
  public function testConstructor() {
    $exception = new PapayaUiDialogFieldFactoryExceptionInvalidOption('OptionName');
    $this->assertEquals(
      'Invalid field factory option name "OptionName".',
      $exception->getMessage()
    );
  }
}