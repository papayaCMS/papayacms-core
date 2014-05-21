<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiDialogFieldsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFields::validate
  */
  public function testValidateExpectingTrue() {
    $fieldOne = $this->getMockField();
    $fieldOne
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fieldTwo = $this->getMockField();
    $fieldTwo
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fields = new PapayaUiDialogFields();
    $fields->add($fieldOne);
    $fields->add($fieldTwo);
    $this->assertTrue($fields->validate());
  }

  /**
  * @covers PapayaUiDialogFields::validate
  */
  public function testValidateExpectingFalse() {
    $fieldOne = $this->getMockField();
    $fieldOne
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $fieldTwo = $this->getMockField();
    $fieldTwo
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fields = new PapayaUiDialogFields();
    $fields->add($fieldOne);
    $fields->add($fieldTwo);
    $this->assertFalse($fields->validate());
  }

  private function getMockField() {
    $item = $this->getMock(
      'PapayaUiDialogField', array('collection', 'index', 'appendTo', 'validate')
    );
    return $item;
  }
}