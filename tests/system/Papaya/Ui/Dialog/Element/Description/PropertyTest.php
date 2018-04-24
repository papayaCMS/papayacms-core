<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogElementDescriptionPropertyTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogElementDescriptionProperty::__construct
  * @covers PapayaUiDialogElementDescriptionProperty::setName
  */
  public function testConstructor() {
    $property = new PapayaUiDialogElementDescriptionProperty('foo', 'bar');
    $this->assertEquals(
      'foo', $property->name
    );
    $this->assertEquals(
      'bar', $property->value
    );
  }

  /**
  * @covers PapayaUiDialogElementDescriptionProperty::appendTo
  */
  public function testAppendTo() {
    $property = new PapayaUiDialogElementDescriptionProperty('foo', 'bar');
    $this->assertEquals(
      '<property name="foo" value="bar"/>', $property->getXml()
    );
  }
}
