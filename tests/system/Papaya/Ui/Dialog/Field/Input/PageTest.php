<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiDialogFieldInputPageTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldInputPage::__construct
  */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldInputPage('Page', 'page_id', 42, TRUE);
    $this->assertEquals(
      'Page', $field->caption
    );
    $this->assertEquals(
      'page_id', $field->name
    );
    $this->assertEquals(
      42, $field->defaultValue
    );
    $this->assertTrue(
      $field->mandatory
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputPage
  * @dataProvider provideValidPageIdInputs
  */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputPage('Page', 'page_id');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputPage
  * @dataProvider provideInvalidPageIdInputs
  */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputPage('Page', 'page_id');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputPage::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $field = new PapayaUiDialogFieldInputPage('Page', 'page_id');
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($dom->appendElement('sample'));
    $this->assertEquals(
      '<sample>'.
        '<field caption="Page" class="DialogFieldInputPage" error="no">'.
          '<input type="page" name="page_id" maxlength="20"/>'.
        '</field>'.
      '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidPageIdInputs() {
    return array(
      array(1, TRUE),
      array(1, FALSE),
      array(0, FALSE),
      array(NULL, FALSE)
    );
  }

  public static function provideInvalidPageIdInputs() {
    return array(
      array(0, TRUE),
      array(-1, TRUE),
      array(-1, FALSE),
      array(NULL, TRUE)
    );
  }
}