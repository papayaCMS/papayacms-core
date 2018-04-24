<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputMediaFileTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldInputMediaFile::__construct
   */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldInputMediaFile('caption', 'name', TRUE);
    $this->assertEquals(new PapayaFilterGuid(), $field->getFilter());
  }
}
