<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldHoneypotTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldHoneypot::__construct
  */
  public function testConstructor() {
    $input = new PapayaUiDialogFieldHoneypot('Caption', 'name');
    $this->assertAttributeEquals(
      'Caption', '_caption', $input
    );
    $this->assertAttributeEquals(
      'name', '_name', $input
    );
  }

  /**
  * @covers PapayaUiDialogFieldHoneypot::setFilter
  */
  public function testSetFilterExpectingException() {
    $input = new PapayaUiDialogFieldHoneypot('Caption', 'name');
    $this->setExpectedException('LogicException');
    $input->setFilter($this->createMock(PapayaFilter::class));
  }

  /**
  * @covers PapayaUiDialogFieldHoneypot::setMandatory
  */
  public function testSetMandatoryExpectingException() {
    $input = new PapayaUiDialogFieldHoneypot('Caption', 'name');
    $this->setExpectedException('LogicException');
    $input->setMandatory(FALSE);
  }

  /**
  * @covers PapayaUiDialogFieldHoneypot
  */
  public function testAppendTo() {
    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->any())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('name' => ''))));
    $dialog
      ->expects($this->any())
      ->method('getParameterName')
      ->with('name')
      ->will($this->returnValue(new PapayaRequestParametersName('name')));
    $dialog
      ->expects($this->any())
      ->method('parameterGroup')
      ->withAnyParameters()
      ->will($this->returnValue('group'));
    $collection = $this
      ->getMockBuilder('PapayaUiDialogFields')
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->any())
      ->method('hasOwner')
      ->will($this->returnValue(TRUE));
    $collection
      ->expects($this->any())
      ->method('owner')
      ->will($this->returnValue($dialog));

    $input = new PapayaUiDialogFieldHoneypot('Caption', 'name');
    $input->papaya($this->mockPapaya()->application());
    $input->collection($collection);

    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldHoneypot" error="no" mandatory="yes">'.
        '<input type="text" name="group[name]"></input>'.
      '</field>',
      $input->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldHoneypot
  */
  public function testAppendToExpectingError() {
    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->any())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array())));
    $dialog
      ->expects($this->any())
      ->method('getParameterName')
      ->with('name')
      ->will($this->returnValue(new PapayaRequestParametersName('name')));
    $dialog
      ->expects($this->any())
      ->method('parameterGroup')
      ->withAnyParameters()
      ->will($this->returnValue(NULL));
    $collection = $this
      ->getMockBuilder('PapayaUiDialogFields')
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->any())
      ->method('hasOwner')
      ->will($this->returnValue(TRUE));
    $collection
      ->expects($this->any())
      ->method('owner')
      ->will($this->returnValue($dialog));

    $input = new PapayaUiDialogFieldHoneypot('Caption', 'name');
    $input->papaya($this->mockPapaya()->application());
    $input->collection($collection);

    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldHoneypot" error="yes" mandatory="yes">'.
        '<input type="text" name="name"></input>'.
      '</field>',
      $input->getXml()
    );
  }


  /**
  * @covers PapayaUiDialogFieldHoneypot
  */
  public function testAppendToWithoutCollection() {
    $input = new PapayaUiDialogFieldHoneypot('Caption', 'name');
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldHoneypot" error="no" mandatory="yes">'.
        '<input type="text" name="name"></input>'.
      '</field>',
      $input->getXml()
    );
  }
}
