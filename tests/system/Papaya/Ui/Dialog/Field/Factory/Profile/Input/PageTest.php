<?php
require_once(dirname(__FILE__).'/../../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryProfileInputPageTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputPage::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputPage();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputPage', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputPage::getField
   */
  public function testGetFieldWithHint() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'hint' => 'Some hint text'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputPage();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputPage
   * @dataProvider provideValidPageInputs
   */
  public function testValidateDifferentIntputs($value) {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => $value
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputPage();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertTrue($field->validate());
  }

  public static function provideValidPageInputs() {
    return array(
      array('42'),
      array('42,21'),
      array('foo'),
      array('http://foobar.tld/')
    );
  }
}