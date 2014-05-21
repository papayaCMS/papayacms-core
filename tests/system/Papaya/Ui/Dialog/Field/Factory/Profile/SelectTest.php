<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryProfileSelectTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelect::getField
   * @covers PapayaUiDialogFieldFactoryProfileSelect::createField
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
    $profile = new PapayaUiDialogFieldFactoryProfileSelect();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelect', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelect::getField
   * @covers PapayaUiDialogFieldFactoryProfileSelect::createField
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
    $profile = new PapayaUiDialogFieldFactoryProfileSelect();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelect', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelect::getField
   * @covers PapayaUiDialogFieldFactoryProfileSelect::createField
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
    $profile = new PapayaUiDialogFieldFactoryProfileSelect();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}