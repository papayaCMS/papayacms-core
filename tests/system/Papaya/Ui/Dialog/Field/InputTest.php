<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldInputTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldInput::__construct
  */
  public function testConstructor() {
    $input = new PapayaUiDialogFieldInput('Caption', 'name');
    $this->assertAttributeEquals(
      'Caption', '_caption', $input
    );
    $this->assertAttributeEquals(
      'name', '_name', $input
    );
  }

  /**
  * @covers PapayaUiDialogFieldInput::__construct
  */
  public function testConstructorWithAllParameters() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $input = new PapayaUiDialogFieldInput('Caption', 'name', 42, '50670', $filter);
    $this->assertAttributeEquals(
      42, '_maximumLength', $input
    );
    $this->assertAttributeEquals(
      '50670', '_defaultValue', $input
    );
    $this->assertAttributeSame(
      $filter, '_filter', $input
    );
  }

  /**
  * @covers PapayaUiDialogFieldInput::setMaximumLength
  */
  public function testSetMaximumLength() {
    $input = new PapayaUiDialogFieldInput('Caption', 'name');
    $input->setMaximumLength(42);
    $this->assertAttributeEquals(
      42, '_maximumLength', $input
    );
  }

  /**
  * @covers PapayaUiDialogFieldInput::setMaximumLength
  */
  public function testSetMaximumLengthToZeroExpectingMinusOne() {
    $input = new PapayaUiDialogFieldInput('Caption', 'name');
    $input->setMaximumLength(0);
    $this->assertAttributeEquals(
      -1, '_maximumLength', $input
    );
  }

  /**
  * @covers PapayaUiDialogFieldInput::setType
  * @covers PapayaUiDialogFieldInput::getType
  */
  public function testGetTypeAfterSetType() {
    $input = new PapayaUiDialogFieldInput('Caption', 'name');
    $input->setType('email');
    $this->assertEquals('email', $input->getType());
  }

  /**
  * @covers PapayaUiDialogFieldInput::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $input = new PapayaUiDialogFieldInput('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(PapayaUiDialogFields::class));
    $input->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field caption="Caption" class="DialogFieldInput" error="no">'.
        '<input type="text" name="name" maxlength="1024"/>'.
        '</field>'.
        '</sample>',
      $dom->saveXml($node)
    );
  }

  /**
  * @covers PapayaUiDialogFieldInput::appendTo
  */
  public function testAppendToWithDefaultValue() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $input = new PapayaUiDialogFieldInput('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(PapayaUiDialogFields::class));
    $input->setDefaultValue(50670);
    $input->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field caption="Caption" class="DialogFieldInput" error="no">'.
        '<input type="text" name="name" maxlength="1024">50670</input>'.
        '</field>'.
        '</sample>',
      $dom->saveXml($node)
    );
  }

  /**
  * @covers PapayaUiDialogFieldInput::appendTo
  */
  public function testAppendToAffectedBySetType() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $input = new PapayaUiDialogFieldInput('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(PapayaUiDialogFields::class));
    $input->setType('email');
    $input->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field caption="Caption" class="DialogFieldInput" error="no">'.
        '<input type="email" name="name" maxlength="1024"/>'.
        '</field>'.
        '</sample>',
      $dom->saveXml($node)
    );
  }
}
