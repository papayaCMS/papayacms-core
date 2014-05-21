<?php
require_once(dirname(__FILE__).'/../../../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryProfileInputGeoPositionTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputGeoPosition::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputGeoPosition();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputGeoPosition', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputGeoPosition::getField
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
    $profile = new PapayaUiDialogFieldFactoryProfileInputGeoPosition();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}