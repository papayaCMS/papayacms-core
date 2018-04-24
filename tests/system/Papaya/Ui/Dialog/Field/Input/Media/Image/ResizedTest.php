<?php
require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputMediaImageResizedTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldInputMediaImageResized::__construct
   * @dataProvider provideValuesForFilterValidation
   */
  public function testConstructorInitializesFilter($value) {
    $field = new PapayaUiDialogFieldInputMediaImageResized('caption', 'name', TRUE);
    $this->assertTrue($field->getFilter()->validate($value));
  }

  /**
   * @covers PapayaUiDialogFieldInputMediaImageResized::__construct
   * @dataProvider provideInvalidValuesForFilterValidation
   */
  public function testConstructorInitializesFilterExpectingExceptionForInvalidValues($value) {
    $field = new PapayaUiDialogFieldInputMediaImageResized('caption', 'name', TRUE);
    $this->setExpectedException(PapayaFilterException::class);
    $field->getFilter()->validate($value);
  }

  public static function provideValuesForFilterValidation() {
    return array(
      array('123456789012345678901234567890ab'),
      array('123456789012345678901234567890ab,320'),
      array('123456789012345678901234567890ab,320,240'),
      array('123456789012345678901234567890ab,320,240,max')
    );
  }

  public static function provideInvalidValuesForFilterValidation() {
    return array(
      array(''),
      array('foo'),
      array('123456789012345678901234567890ab,foo'),
      array('123456789012345678901234567890ab,320,foo'),
      array('123456789012345678901234567890ab,320,240,foo')
    );
  }
}
