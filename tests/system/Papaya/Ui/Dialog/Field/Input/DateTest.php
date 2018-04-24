<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputDateTest extends PapayaTestCase {
  /**
  * @covers PapayaUiDialogFieldInputDate::__construct
  */
  public function testConstructor() {
    $input = new PapayaUiDialogFieldInputDate(
      'Date', 'date', '2011-01-01 18:00', TRUE, PapayaFilterDate::DATE_OPTIONAL_TIME, 300.0
    );
    $this->assertEquals('Date', $input->caption);
    $this->assertEquals('date', $input->name);
    $this->assertEquals('2011-01-01 18:00', $input->defaultValue);
    $this->assertTrue($input->mandatory);
    $this->assertAttributeEquals(PapayaFilterDate::DATE_OPTIONAL_TIME, '_includeTime', $input);
    $this->assertAttributeEquals(300.0, '_step', $input);
  }

  /**
  * @covers PapayaUiDialogFieldInputDate::__construct
  */
  public function testConstructorWithInvalidIncludeTimeOption() {
    try {
      $input = new PapayaUiDialogFieldInputDate(
        'Date', 'date', '2011-01-01 18:00', TRUE, 23, 300.0
      );
    } catch (InvalidArgumentException $e) {
      $this->assertEquals(
        'Argument must be PapayaFilterDate::DATE_NO_TIME, PapayaFilterDate::DATE_OPTIONAL_TIME,'.
        ' or PapayaFilterDate::DATE_MANDATORY_TIME.',
        $e->getMessage()
      );
    }
  }

  /**
  * @covers PapayaUiDialogFieldInputDate::__construct
  */
  public function testConstructorWithInvalidStep() {
    try {
      $input = new PapayaUiDialogFieldInputDate(
        'Date', 'date', '2011-01-01 18:00', TRUE, PapayaFilterDate::DATE_OPTIONAL_TIME, -300.0
      );
    } catch (InvalidArgumentException $e) {
      $this->assertEquals(
        'Step must be greater than 0.',
        $e->getMessage()
      );
    }
  }

  /**
  * @covers PapayaUiDialogFieldInputDate
  * @dataProvider filterExpectingTrueProvider
  */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $input = new PapayaUiDialogFieldInputDate(
      'Date', 'date', NULL, FALSE, PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertTrue($input->validate());
  }

  /**
  * @covers PapayaUiDialogFieldInputDate
  * @dataProvider filterExpectingFalseProvider
  */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $input = new PapayaUiDialogFieldInputDate(
      'Date', 'date', NULL, FALSE, PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertFalse($input->validate());
  }

  /**
  * @covers PapayaUiDialogFieldInputDate::getXml
  */
  public function testGetXml() {
    $input = new PapayaUiDialogFieldInputDate('Date', 'date');
    $input->papaya($this->mockPapaya()->application());
    $this->assertEquals(
        '<field caption="Date" class="DialogFieldInputDate" error="no">'.
          '<input type="date" name="date" maxlength="19"/>'.
        '</field>',
      $input->getXml()
    );
  }

  public static function filterExpectingTrueProvider() {
    return array(
      array('2011-08-13 11:35', TRUE),
      array('2011-08-13', TRUE),
      array('2011-08-13', FALSE),
      array('2011-08-13 11:35', FALSE),
      array('', FALSE)
    );
  }

  public static function filterExpectingFalseProvider() {
    return array(
      array('2011-99-99', TRUE),
      array('2011*08*13', TRUE),
      array('11:35', FALSE),
      array('', TRUE)
    );
  }
}
