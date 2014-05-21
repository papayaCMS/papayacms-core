<?php
require_once(dirname(__FILE__).'/../../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryProfileSelectCheckboxesTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectCheckboxes::createField
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
    $profile = new PapayaUiDialogFieldFactoryProfileSelectCheckboxes();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelectCheckboxes', $field = $profile->getField());
  }
}