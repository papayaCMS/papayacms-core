<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldFileTemporaryTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFileTemporary::__construct
   */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldFileTemporary('Caption', 'name');
    $this->assertEquals('Caption', $field->getCaption());
    $this->assertEquals('name', $field->getName());
  }

  /**
   * @covers PapayaUiDialogFieldFileTemporary::appendTo
   */
  public function testAppendTo() {
    $field = new PapayaUiDialogFieldFileTemporary('Caption', 'name');
    $field->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldFileTemporary" error="no">'.
        '<input type="file" name="name"/>'.
      '</field>',
      $field->getXml()
    );
  }

  /**
   * @covers PapayaUiDialogFieldFileTemporary::validate
   */
  public function testValidateWithFileExpectingTrue() {
    $file = $this
      ->getMockBuilder('PapayaRequestParameterFile')
      ->disableOriginalConstructor()
      ->getMock();
    $file
      ->expects($this->once())
      ->method('isValid')
      ->will($this->returnValue(TRUE));
    $field = new PapayaUiDialogFieldFileTemporary('Caption', 'name');
    $field->file($file);
    $this->assertTrue($field->validate());
    $this->assertTrue($field->validate());
  }

  /**
   * @covers PapayaUiDialogFieldFileTemporary::validate
   */
  public function testValidateWithoutOptionalFileExpectingTrue() {
    $file = $this
      ->getMockBuilder('PapayaRequestParameterFile')
      ->disableOriginalConstructor()
      ->getMock();
    $file
      ->expects($this->once())
      ->method('isValid')
      ->will($this->returnValue(FALSE));
    $field = new PapayaUiDialogFieldFileTemporary('Caption', 'name');
    $field->file($file);
    $this->assertTrue($field->validate());
    $this->assertTrue($field->validate());
  }

  /**
   * @covers PapayaUiDialogFieldFileTemporary::validate
   */
  public function testValidateWithoutManatoryFileExpectingFalse() {
    $file = $this
      ->getMockBuilder('PapayaRequestParameterFile')
      ->disableOriginalConstructor()
      ->getMock();
    $file
      ->expects($this->once())
      ->method('isValid')
      ->will($this->returnValue(FALSE));
    $field = new PapayaUiDialogFieldFileTemporary('Caption', 'name');
    $field->setMandatory(TRUE);
    $field->file($file);
    $this->assertFalse($field->validate());
    $this->assertFalse($field->validate());
  }

  /**
   * @covers PapayaUiDialogFieldFileTemporary::collect
   */
  public function testCollectReturnsTrue() {
    $field = new PapayaUiDialogFieldFileTemporary('Caption', 'name');
    $this->assertTrue($field->collect());
  }

  /**
   * @covers PapayaUiDialogFieldFileTemporary::file
   */
  public function testFileGetAfterSet() {
    $file = $this
      ->getMockBuilder('PapayaRequestParameterFile')
      ->disableOriginalConstructor()
      ->getMock();
    $field = new PapayaUiDialogFieldFileTemporary('Caption', 'name');
    $field->file($file);
    $this->assertSame($file, $field->file());
  }

  /**
   * @covers PapayaUiDialogFieldFileTemporary::file
   */
  public function testFileGetWithImplicitCreate() {
    $field = new PapayaUiDialogFieldFileTemporary('Caption', 'name');
    $this->assertInstanceOf('PapayaRequestParameterFile', $field->file());
  }
}
