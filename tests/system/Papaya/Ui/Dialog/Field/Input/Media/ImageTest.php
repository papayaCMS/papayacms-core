<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaUiDialogFieldInputMediaImageTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldInputMediaImage
   */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldInputMediaImage('caption', 'name', TRUE);
    $this->assertEquals(new PapayaFilterGuid(), $field->getFilter());
  }
}
