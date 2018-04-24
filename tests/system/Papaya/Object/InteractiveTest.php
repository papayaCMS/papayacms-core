<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaObjectInteractiveTest extends PapayaTestCase {

  /**
   * @covers PapayaObjectInteractive::parameterMethod
   */
  public function testParameterMethod() {
    $parts = new PapayaObjectInteractive_TestProxy();
    $this->assertEquals(
      PapayaRequestParametersInterface::METHOD_MIXED_POST,
      $parts->parameterMethod()
    );
  }

  /**
   * @covers PapayaObjectInteractive::parameterMethod
   */
  public function testParameterMethodChange() {
    $parts = new PapayaObjectInteractive_TestProxy();
    $this->assertEquals(
      PapayaRequestParametersInterface::METHOD_MIXED_GET,
      $parts->parameterMethod(PapayaRequestParametersInterface::METHOD_MIXED_GET)
    );
  }

  /**
   * @covers PapayaObjectInteractive_TestProxy::parameterGroup
   */
  public function testParameterGroupWithChange() {
    $parts = new PapayaObjectInteractive_TestProxy();
    $this->assertEquals(
      'sample', $parts->parameterGroup('sample')
    );
  }

  /**
   * @covers PapayaObjectInteractive::parameterGroup
   */
  public function testParameterGroupWithoutChange() {
    $parts = new PapayaObjectInteractive_TestProxy();
    $this->assertEquals(
      '', $parts->parameterGroup()
    );
  }

  /**
   * @covers PapayaObjectInteractive::parameters
   */
  public function testParametersGetAfterSet() {
    $parts = new PapayaObjectInteractive_TestProxy();
    $parts->parameters($parameters = $this->createMock(PapayaRequestParameters::class));
    $this->assertEquals(
      $parameters, $parts->parameters()
    );
  }

  /**
   * @covers PapayaObjectInteractive::parameters
   */
  public function testParametersGetAllFromApplicationRequest() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameters')
      ->with(PapayaRequest::SOURCE_QUERY | PapayaRequest::SOURCE_BODY)
      ->will($this->returnValue($this->createMock(PapayaRequestParameters::class)));
    $parts = new PapayaObjectInteractive_TestProxy();
    $parts->papaya(
      $this->mockPapaya()->application(
        array('Request' => $request)
      )
    );
    $this->assertInstanceOf(PapayaRequestParameters::class, $parts->parameters());
  }

  /**
   * @covers PapayaObjectInteractive::parameters
   */
  public function testParametersGetGroupFromApplicationRequest() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameterGroup')
      ->with('group', PapayaRequest::SOURCE_QUERY | PapayaRequest::SOURCE_BODY)
      ->will($this->returnValue($this->createMock(PapayaRequestParameters::class)));
    $parts = new PapayaObjectInteractive_TestProxy();
    $parts->papaya(
      $this->mockPapaya()->application(
        array('Request' => $request)
      )
    );
    $parts->parameterGroup('group');
    $this->assertInstanceOf(PapayaRequestParameters::class, $parts->parameters());
  }
}

class PapayaObjectInteractive_TestProxy extends PapayaObjectInteractive {

}
