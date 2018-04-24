<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputCountedTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldInputCounted
  */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldInputCounted('Caption', 'fieldname', 42, TRUE);
    $this->assertEquals('counted', $field->getType());
  }

  /**
  * @covers PapayaUiDialogFieldInputCounted
  */
  public function testAppendTo() {
    $field = new PapayaUiDialogFieldInputCounted('Caption', 'fieldname');
    $field->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldInputCounted" error="no">'.
        '<input type="counted" name="fieldname" maxlength="1024"/>'.
      '</field>',
      $field->getXml()
    );
  }
}
