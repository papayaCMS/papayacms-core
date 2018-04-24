<?php
require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileInputSuggestTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputSuggest::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'parameters' => 'suggest.url'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputSuggest();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputSuggest', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputSuggest::getField
   */
  public function testGetFieldWithHint() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'parameters' => 'suggest.url',
        'hint' => 'Some hint text'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputSuggest();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputSuggest::getField
   */
  public function testGetFieldDisabled() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'parameters' => 'suggest.url',
        'hint' => 'Some hint text',
        'disabled' => true
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputSuggest();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertTrue($field->getDisabled());
  }
}
