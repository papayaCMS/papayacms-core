<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputEmailTest extends PapayaTestCase {
  /**
  * @covers PapayaUiDialogFieldInputEmail::__construct
  */
  public function testConstrutor() {
    $field = new PapayaUiDialogFieldInputEmail('Email', 'email', 'default@example.com', TRUE);
    $this->assertEquals(
      'Email',
      $field->caption
    );
    $this->assertEquals(
      'email',
      $field->name
    );
    $this->assertEquals(
      'default@example.com',
      $field->defaultValue
    );
    $this->assertTrue(
      $field->getMandatory()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputEmail
  * @dataProvider provideValidEmailInputs
  */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputEmail('Email', 'email');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputEmail
  * @dataProvider provideInvalidEmailInputs
  */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputEmail('Email', 'email');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputEmail::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $field = new PapayaUiDialogFieldInputEmail('Email', 'email');
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($dom->appendElement('test'));
    $this->assertEquals(
      '<test>'.
        '<field caption="Email" class="DialogFieldInputEmail" error="no">'.
          '<input type="email" name="email" maxlength="1024"/>'.
        '</field>'.
      '</test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  public static function provideValidEmailInputs() {
    return array(
      array('unit@example.com', TRUE),
      array('unit@example.com', FALSE),
      array('', FALSE),
    );
  }

  public static function provideInvalidEmailInputs() {
    return array(
      array(':unit@example.com', TRUE),
      array(':unit@example.com', FALSE),
      array('unit@example.', FALSE),
      array('', TRUE),
    );
  }
}
