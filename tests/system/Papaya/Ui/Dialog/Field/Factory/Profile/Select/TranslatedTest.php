<?php
require_once(dirname(__FILE__).'/../../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryProfileSelectTranslatedTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectTranslated::createField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 0,
        'parameters' => array('foo', 'bar')
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectTranslated();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelect', $field = $profile->getField());
    $this->assertAttributeInstanceOf('PapayaUiStringTranslatedList', '_values', $field);
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectTranslated::createField
   */
  public function testGetFieldEmptyElementsList() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 0,
        'parameters' => NULL
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectTranslated();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelect', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectTranslated::createField
   */
  public function testGetFieldWithHint() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 0,
        'hint' => 'Some hint text',
        'parameters' => array('foo', 'bar')
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectTranslated();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}