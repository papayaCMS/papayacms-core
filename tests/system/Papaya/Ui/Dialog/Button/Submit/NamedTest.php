<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogButtonSubmitNamedTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogButtonSubmitNamed::__construct
  */
  public function testConstructor() {
    $button = new PapayaUiDialogButtonSubmitNamed('Test', 'name');
    $this->assertAttributeEquals(
      'name', '_name', $button
    );
  }

  /**
  * @covers PapayaUiDialogButtonSubmitNamed::__construct
  */
  public function testConstructorWithAllParameters() {
    $button = new PapayaUiDialogButtonSubmitNamed(
      'Test', 'name', 'value', PapayaUiDialogButton::ALIGN_LEFT
    );
    $this->assertAttributeEquals(
      'value', '_value', $button
    );
    $this->assertAttributeEquals(
      PapayaUiDialogButton::ALIGN_LEFT, '_align', $button
    );
  }

  /**
  * @covers PapayaUiDialogButtonSubmitNamed::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $button = new PapayaUiDialogButtonSubmitNamed('Test Caption', 'buttonname');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $button->papaya($application);
    $button->collection($this->getCollectionMock());
    $button->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><button type="submit" align="right" name="buttonname[1]">Test Caption</button></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiDialogButtonSubmitNamed::appendTo
  */
  public function testAppendToWithDialogParameterGroup() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('isSubmitted', 'execute', 'appendTo', 'parameterGroup'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->any())
      ->method('parameterGroup')
      ->will($this->returnValue('group'));
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $button = new PapayaUiDialogButtonSubmitNamed('Test Caption', 'buttonname');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $button->papaya($application);
    $button->collection($this->getCollectionMock($dialog));
    $button->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test>'.
        '<button type="submit" align="right" name="group[buttonname][1]">Test Caption</button>'.
        '</test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiDialogButtonSubmitNamed::collect
  */
  public function testCollectExpectingTrue() {
    $parameters = $this->getMock('PapayaRequestParameters', array('has'));
    $parameters
      ->expects($this->once())
      ->method('has')
      ->with($this->equalTo('buttonname[42]'))
      ->will($this->returnValue(TRUE));
    $data = $this->getMock('PapayaRequestParameters', array('set'));
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('buttonname'), $this->equalTo(42));
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('isSubmitted', 'execute', 'appendTo', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue($parameters));
    $dialog
      ->expects($this->any())
      ->method('data')
      ->will($this->returnValue($data));
    $button = new PapayaUiDialogButtonSubmitNamed('Test Caption', 'buttonname', 42);
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $button->papaya($application);
    $button->collection($this->getCollectionMock($dialog));
    $this->assertTrue($button->collect());
  }

  /**
  * @covers PapayaUiDialogButtonSubmitNamed::collect
  */
  public function testCollectWithGroupExpectingTrue() {
    $parameters = $this->getMock('PapayaRequestParameters', array('has'));
    $parameters
      ->expects($this->once())
      ->method('has')
      ->with($this->equalTo('buttonname[42]'))
      ->will($this->returnValue(TRUE));
    $data = $this->getMock('PapayaRequestParameters', array('set'));
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('buttonname'), $this->equalTo(42));
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('isSubmitted', 'execute', 'appendTo', 'parameterGroup', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->any())
      ->method('parameterGroup')
      ->will($this->returnValue('group'));
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue($parameters));
    $dialog
      ->expects($this->any())
      ->method('data')
      ->will($this->returnValue($data));
    $button = new PapayaUiDialogButtonSubmitNamed('Test Caption', 'buttonname', 42);
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $button->papaya($application);
    $button->collection($this->getCollectionMock($dialog));
    $this->assertTrue($button->collect());
    $this->assertEquals(
      '<button type="submit" align="right" name="group[buttonname][42]">Test Caption</button>',
      $button->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogButtonSubmitNamed::collect
  */
  public function testCollectExpectingFalse() {
    $parameters = $this->getMock('PapayaRequestParameters', array('has'));
    $parameters
      ->expects($this->once())
      ->method('has')
      ->with($this->equalTo('buttonname[42]'))
      ->will($this->returnValue(FALSE));
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('isSubmitted', 'execute', 'appendTo', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue($parameters));
    $dialog
      ->expects($this->never())
      ->method('data');
    $button = new PapayaUiDialogButtonSubmitNamed('Test Caption', 'buttonname', 42);
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $button->papaya($application);
    $button->collection($this->getCollectionMock($dialog));
    $this->assertFalse($button->collect());
  }

  /*****************************
  * Mocks
  *****************************/

  public function getCollectionMock($owner = NULL) {
    $collection = $this->getMock('PapayaUiDialogElements');
    if ($owner) {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->any())
        ->method('owner')
        ->will($this->returnValue($owner));
    } else {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(FALSE));
    }
    return $collection;
  }
}
