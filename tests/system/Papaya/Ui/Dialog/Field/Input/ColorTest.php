<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputColorTest extends PapayaTestCase {
  /**
  * @covers PapayaUiDialogFieldInputColor::__construct
  */
  public function testConstrutor() {
    $field = new PapayaUiDialogFieldInputColor('Color', 'color', '#000000', TRUE);
    $this->assertEquals(
      'Color',
      $field->caption
    );
    $this->assertEquals(
      'color',
      $field->name
    );
    $this->assertEquals(
      '#000000',
      $field->defaultValue
    );
    $this->assertTrue(
      $field->getMandatory()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputColor
  * @dataProvider provideValidColorInputs
  */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputColor('Color', 'color');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputColor
  * @dataProvider provideInvalidColorInputs
  */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputColor('Color', 'color');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputColor::appendTo
  */
  public function testAppendTo() {
    $field = new PapayaUiDialogFieldInputColor('Color', 'color');
    $field->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      '<field caption="Color" class="DialogFieldInputColor" error="no">'.
        '<input type="color" name="color" maxlength="7"/>'.
      '</field>',
      $field->getXml()
    );
  }

  public static function provideValidColorInputs() {
    return array(
      array('#000000', TRUE),
      array('#000000', FALSE),
      array('', FALSE),
    );
  }

  public static function provideInvalidColorInputs() {
    return array(
      array(':#000000', TRUE),
      array(':#000000', FALSE),
      array('000000', FALSE),
      array('', TRUE),
    );
  }
}
