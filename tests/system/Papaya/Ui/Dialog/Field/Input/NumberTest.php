<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputNumberTest extends PapayaTestCase {
  /**
  * @covers PapayaUiDialogFieldInputNumber::__construct
  */
  public function testConstructSuccess() {
    $input = new PapayaUiDialogFieldInputNumber('Number', 'number', '123', TRUE, 2, 4);
    $this->assertEquals('Number', $input->caption);
    $this->assertEquals('number', $input->name);
    $this->assertEquals('123', $input->defaultValue);
    $this->assertTrue($input->mandatory);
    $this->assertAttributeEquals(2, '_minimumLength', $input);
    $this->assertAttributeEquals(4, '_maximumLength', $input);
  }

  /**
  * @covers PapayaUiDialogFieldInputNumber::__construct
  * @dataProvider constructFailureProvider
  */
  public function testConstructFailure($minimumLength, $maximumLength) {
    $this->expectException(UnexpectedValueException::class);
    new PapayaUiDialogFieldInputNumber(
      'Number', 'number', '123', TRUE, $minimumLength, $maximumLength
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputNumber
  * @dataProvider filterExpectingTrueProvider
  */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $input = new PapayaUiDialogFieldInputNumber('Number', 'number', NULL, FALSE, 2, 4);
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertTrue($input->validate());
  }

  /**
  * @covers PapayaUiDialogFieldInputNumber
  * @dataProvider filterExpectingFalseProvider
  */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $input = new PapayaUiDialogFieldInputNumber('Number', 'number', NULL, FALSE, 2, 4);
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertFalse($input->validate());
  }

  /**
  * @covers PapayaUiDialogFieldInputNumber::getXml
  */
  public function testGetXml() {
    $input = new PapayaUiDialogFieldInputNumber('Number', 'number', '123', FALSE, 2, 4);
    $this->assertEquals(
        '<field caption="Number" class="DialogFieldInputNumber" error="no">'.
          '<input type="number" name="number" maxlength="4">123</input>'.
        '</field>',
      $input->getXml()
    );
  }

  public static function constructFailureProvider() {
    return array(
      array(-1, NULL),
      array('String', NULL),
      array(NULL, -1),
      array(NULL, 'String'),
      array(5, 4)
    );
  }

  public static function filterExpectingTrueProvider() {
    return array(
      array('12', TRUE),
      array('123', TRUE),
      array('1234', TRUE),
      array('12', FALSE),
      array('123', FALSE),
      array('1234', FALSE),
      array('', FALSE)
    );
  }

  public static function filterExpectingFalseProvider() {
    return array(
      array('1', TRUE),
      array('12345', TRUE),
      array('NaN', TRUE),
      array('1', FALSE),
      array('12345', FALSE),
      array('NaN', FALSE),
      array('', TRUE)
    );
  }
}
