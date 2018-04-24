<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputGeoPositionTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldInputGeoPosition::__construct
  */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldInputGeoPosition('Position', 'geo_position', '21,42', TRUE);
    $this->assertEquals(
      'Position', $field->caption
    );
    $this->assertEquals(
      'geo_position', $field->name
    );
    $this->assertEquals(
      '21,42', $field->defaultValue
    );
    $this->assertTrue(
      $field->mandatory
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputGeoPosition
  * @dataProvider provideValidGeoPositionInputs
  */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputGeoPosition(
      'Position', 'geo_position', $value, $mandatory
    );
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputPage
  * @dataProvider provideInvalidGeoPositionInputs
  */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputGeoPosition(
      'Position', 'geo_position', $value, $mandatory
    );
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputPage::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $field = new PapayaUiDialogFieldInputGeoPosition('Position', 'geo_position', '', FALSE);
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($dom->appendElement('sample'));
    $this->assertEquals(
      '<sample>'.
        '<field caption="Position" class="DialogFieldInputGeoPosition" error="no">'.
          '<input type="geoposition" name="geo_position" maxlength="100"></input>'.
        '</field>'.
      '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidGeoPositionInputs() {
    return array(
      array('1,1', TRUE),
      array('1,1', FALSE),
      array('', FALSE),
      array(NULL, FALSE)
    );
  }

  public static function provideInvalidGeoPositionInputs() {
    return array(
      array('0', TRUE),
      array('-1', TRUE),
      array('-1', FALSE),
      array(NULL, TRUE)
    );
  }
}
