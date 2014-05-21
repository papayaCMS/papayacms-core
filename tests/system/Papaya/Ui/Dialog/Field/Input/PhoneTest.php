<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiDialogFieldInputPhoneTest extends PapayaTestCase {
  /**
  * @covers PapayaUiDialogFieldInputPhone::__construct
  */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldInputPhone('Phone', 'phone', '1234567890', TRUE);
    $this->assertEquals(
      'Phone',
      $field->caption
    );
    $this->assertEquals(
      'phone',
      $field->name
    );
    $this->assertEquals(
      '1234567890',
      $field->defaultValue
    );
    $this->assertTrue(
      $field->mandatory
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputPhone
  * @dataProvider provideValidPhoneInputs
  */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputPhone('Phone', 'phone');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputPhone
  * @dataProvider provideInvalidPhoneInputs
  */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputPhone('Phone', 'phone');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputPhone::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $field = new PapayaUiDialogFieldInputPhone('Phone', 'phone');
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($dom->appendElement('test'));
    $this->assertEquals(
      '<test>'.
        '<field caption="Phone" class="DialogFieldInputPhone" error="no">'.
          '<input type="phone" name="phone" maxlength="1024"/>'.
        '</field>'.
      '</test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  public static function provideValidPhoneInputs() {
    return array(
      array('1234567890', TRUE),
      array('1234567890', FALSE),
      array('', FALSE),
    );
  }

  public static function provideInvalidPhoneInputs() {
    return array(
      array(':1234567890', TRUE),
      array(':1234567890', FALSE),
      array('fsdjjsdf', FALSE),
      array('', TRUE),
    );
  }
}
