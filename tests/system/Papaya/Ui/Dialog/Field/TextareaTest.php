<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldTextareaTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldTextarea::__construct
  */
  public function testConstructor() {
    $textarea = new PapayaUiDialogFieldTextarea('Caption', 'name');
    $this->assertAttributeEquals(
      'Caption', '_caption', $textarea
    );
    $this->assertAttributeEquals(
      'name', '_name', $textarea
    );
  }

  /**
  * @covers PapayaUiDialogFieldTextarea::__construct
  */
  public function testConstructorWithAllParameters() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $textarea = new PapayaUiDialogFieldTextarea('Caption', 'name', 42, '50670', $filter);
    $this->assertAttributeEquals(
      42, '_lineCount', $textarea
    );
    $this->assertAttributeEquals(
      '50670', '_defaultValue', $textarea
    );
    $this->assertAttributeSame(
      $filter, '_filter', $textarea
    );
  }

  /**
  * @covers PapayaUiDialogFieldTextarea::setLineCount
  */
  public function testSetLineCount() {
    $textarea = new PapayaUiDialogFieldTextarea('Caption', 'name');
    $textarea->setLineCount(42);
    $this->assertAttributeEquals(
      42, '_lineCount', $textarea
    );
  }

  /**
  * @covers PapayaUiDialogFieldTextarea::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $textarea = new PapayaUiDialogFieldTextarea('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $textarea->papaya($application);
    $textarea->collection($this->createMock(PapayaUiDialogFields::class));
    $textarea->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field caption="Caption" class="DialogFieldTextarea" error="no">'.
        '<textarea type="text" name="name" lines="10"></textarea>'.
        '</field>'.
        '</sample>',
      $dom->saveXml($node)
    );
  }

  /**
  * @covers PapayaUiDialogFieldTextarea::appendTo
  */
  public function testAppendToWithDefaultValue() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $textarea = new PapayaUiDialogFieldTextarea('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $textarea->papaya($application);
    $textarea->collection($this->createMock(PapayaUiDialogFields::class));
    $textarea->setDefaultValue(50670);
    $textarea->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field caption="Caption" class="DialogFieldTextarea" error="no">'.
        '<textarea type="text" name="name" lines="10">50670</textarea>'.
        '</field>'.
        '</sample>',
      $dom->saveXml($node)
    );
  }
}
