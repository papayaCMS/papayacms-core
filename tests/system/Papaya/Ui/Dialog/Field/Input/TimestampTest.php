<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiDialogFieldInputTimestampTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldInputTimestamp::getCurrentValue
  */
  public function testGetCurrentValueFromDialogParameters() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will(
        $this->returnValue(
          new PapayaRequestParameters(array('date' => '2011-01-01 18:00'))
        )
      );
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date', 'date', NULL, FALSE, PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    $field->collection($this->getCollectionMock($dialog));
    $this->assertEquals(strtotime('2011-01-01 18:00'), $field->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogFieldInputTimestamp::getCurrentValue
  */
  public function testGetCurrentValueFromDefaultValue() {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date', 'date', NULL, FALSE, PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    $field->setDefaultValue(strtotime('2011-01-01 18:00'));
    $this->assertEquals(strtotime('2011-01-01 18:00'), $field->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogFieldInputTimestamp
  * @dataProvider filterExpectingTrueProvider
  */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date', 'date', NULL, FALSE, PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue($field->validate());
  }

  /**
  * @covers PapayaUiDialogFieldInputTimestamp
  * @dataProvider filterExpectingFalseProvider
  */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date', 'date', NULL, FALSE, PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse($field->validate());
  }

  /**
  * @covers PapayaUiDialogFieldInputTimestamp::appendTo
  * @covers PapayaUiDialogFieldInputTimestamp::formatDateTime
  */
  public function testAppendTo() {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date',
      'date',
      strtotime('2011-01-01 18:00'),
      FALSE,
      PapayaFilterDate::DATE_OPTIONAL_TIME,
      300.0
    );
    $this->assertEquals(
      '<field caption="Date" class="DialogFieldInputTimestamp" error="no">'.
        '<input type="datetime" name="date" maxlength="19">2011-01-01 18:00:00</input>'.
      '</field>',
      $field->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputTimestamp::appendTo
  * @covers PapayaUiDialogFieldInputTimestamp::formatDateTime
  */
  public function testAppendToWithoutTime() {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date',
      'date',
      strtotime('2011-01-01 18:00'),
      FALSE,
      PapayaFilterDate::DATE_NO_TIME,
      300.0
    );
    $this->assertEquals(
      '<field caption="Date" class="DialogFieldInputTimestamp" error="no">'.
        '<input type="date" name="date" maxlength="19">2011-01-01</input>'.
      '</field>',
      $field->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputTimestamp::appendTo
  * @covers PapayaUiDialogFieldInputTimestamp::formatDateTime
  */
  public function testAppendToWithEmptyTimestamp() {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date',
      'date',
      0,
      FALSE,
      PapayaFilterDate::DATE_NO_TIME,
      300.0
    );
    $this->assertEquals(
      '<field caption="Date" class="DialogFieldInputTimestamp" error="no">'.
        '<input type="date" name="date" maxlength="19"></input>'.
      '</field>',
      $field->getXml()
    );
  }

  /*************************
  * Data Provider
  *************************/

  public static function filterExpectingTrueProvider() {
    return array(
      array('2011-08-13 11:35', TRUE),
      array('2011-08-13', TRUE),
      array('2011-08-13', FALSE),
      array('2011-08-13 11:35', FALSE),
      array('11:35', FALSE),
      array('', FALSE)
    );
  }

  public static function filterExpectingFalseProvider() {
    return array(
      array('', TRUE)
    );
  }

  /*************************
  * Fixtures
  *************************/

  public function getCollectionMock($owner = NULL) {
    $collection = $this->getMock('PapayaUiDialogFields');
    if ($owner) {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->any())
        ->method('owner')
        ->will($this->returnValue($owner));
    } else {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(FALSE));
    }
    return $collection;
  }
}