<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiControlInteractiveTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlInteractive::parameterMethod
  */
  public function testParameterMethodSet() {
    $dialog = new PapayaUiControlInteractive_TestProxy();
    $dialog->parameterMethod(PapayaUiControlInteractive::METHOD_GET);
    $this->assertAttributeEquals(
      PapayaUiControlInteractive::METHOD_GET, '_parameterMethod', $dialog
    );
  }

  /**
  * @covers PapayaUiControlInteractive::parameterMethod
  */
  public function testParameterMethodGet() {
    $dialog = new PapayaUiControlInteractive_TestProxy();
    $this->assertEquals(
      PapayaUiControlInteractive::METHOD_GET,
      $dialog->parameterMethod(PapayaUiControlInteractive::METHOD_GET)
    );
  }

  /**
  * @covers PapayaUiControlInteractive::parameterGroup
  */
  public function testParameterGroupSet() {
    $dialog = new PapayaUiControlInteractive_TestProxy();
    $dialog->parameterGroup('sample');
    $this->assertAttributeEquals(
      'sample', '_parameterGroup', $dialog
    );
  }

  /**
  * @covers PapayaUiControlInteractive::parameterGroup
  */
  public function testParameterGroupGet() {
    $dialog = new PapayaUiControlInteractive_TestProxy();
    $dialog->parameterGroup('sample');
    $this->assertEquals(
      'sample', $dialog->parameterGroup()
    );
  }

  /**
  * @covers PapayaUiControlInteractive::parameters
  */
  public function testParametersGetAfterSet() {
    $parameters = $this->getMock('PapayaRequestParameters');
    $dialog = new PapayaUiControlInteractive_TestProxy();
    $this->assertSame(
      $parameters, $dialog->parameters($parameters)
    );
  }

  /**
  * @covers PapayaUiControlInteractive::parameters
  */
  public function testParamtersGetImplicit() {
    $request = $this->getMock('PapayaRequest', array('getParameters'));
    $request
      ->expects($this->once())
      ->method('getParameters')
      ->with(PapayaRequest::SOURCE_QUERY | PapayaRequest::SOURCE_BODY)
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => 'bar'))));
    $dialog = new PapayaUiControlInteractive_TestProxy();
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertEquals(
      array('foo' => 'bar'), $dialog->parameters()->toArray()
    );
  }

  /**
  * @covers PapayaUiControlInteractive::parameters
  */
  public function testParamtersGetImplicitWithGroup() {
    $request = $this->getMock('PapayaRequest', array('getParameterGroup'));
    $request
      ->expects($this->once())
      ->method('getParameterGroup')
      ->with('group', PapayaRequest::SOURCE_QUERY | PapayaRequest::SOURCE_BODY)
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => 'bar'))));
    $dialog = new PapayaUiControlInteractive_TestProxy();
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameterGroup('group');
    $this->assertEquals(
      array('foo' => 'bar'), $dialog->parameters()->toArray()
    );
  }

  /**
  * @covers PapayaUiControlInteractive::isPostRequest
  */
  public function testIsPostRequestExpectingTrue() {
    $request = $this->getMock('PapayaRequest');
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $dialog = new PapayaUiControlInteractive_TestProxy();
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertTrue($dialog->isPostRequest());
  }

  /**
  * @covers PapayaUiControlInteractive::isPostRequest
  */
  public function testIsPostRequestExpectingFalse() {
    $request = $this->getMock('PapayaRequest');
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('get'));
    $dialog = new PapayaUiControlInteractive_TestProxy();
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertFalse($dialog->isPostRequest());
  }
}

class PapayaUiControlInteractive_TestProxy extends PapayaUiControlInteractive {

  public function appendTo(PapayaXmlElement $node) {

  }
}