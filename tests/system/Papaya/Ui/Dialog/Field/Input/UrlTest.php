<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiDialogFieldInputUrlTest extends PapayaTestCase {
  /**
  * @covers PapayaUiDialogFieldInputurl::__construct
  */
  public function testConstrutor() {
    $field = new PapayaUiDialogFieldInputUrl('Url', 'url', 'http://www.default.com', TRUE);
    $this->assertEquals(
      'Url',
      $field->caption
    );
    $this->assertEquals(
      'url',
      $field->name
    );
    $this->assertEquals(
      'http://www.default.com',
      $field->defaultValue
    );
    $this->assertTrue(
      $field->mandatory
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputUrl
  * @dataProvider provideValidUrlInputs
  */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputUrl('Url', 'url');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputUrl
  * @dataProvider provideInvalidUrlInputs
  */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputUrl('Url', 'url');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputUrl::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $field = new PapayaUiDialogFieldInputUrl('Url', 'url');
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($dom->appendElement('test'));
    $this->assertEquals(
      '<test>'.
        '<field caption="Url" class="DialogFieldInputUrl" error="no">'.
          '<input type="url" name="url" maxlength="1024"/>'.
        '</field>'.
      '</test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  public static function provideValidUrlInputs() {
    return array(
      array('http://www.example.com', TRUE),
      array('http://www.example.com', FALSE),
      array('', FALSE),
    );
  }

  public static function provideInvalidUrlInputs() {
    return array(
      array(':http://www.example.com', TRUE),
      array(':http://www.example.com', FALSE),
      array('http://www.example.', FALSE),
      array('', TRUE),
    );
  }
}
