<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaControllerErrorTest extends PapayaTestCase {

  /**
  * @covers PapayaControllerError::setStatus
  */
  public function testSetStatus() {
    $controller = new PapayaControllerError();
    $controller->setStatus(403);
    $this->assertAttributeEquals(
      403, '_status', $controller
    );
  }

  /**
  * @covers PapayaControllerError::setError
  */
  public function testSetError() {
    $controller = new PapayaControllerError();
    $controller->setError('ERROR_IDENTIFIER', 'ERROR_MESSAGE');
    $this->assertAttributeEquals(
      'ERROR_MESSAGE', '_errorMessage', $controller
    );
    $this->assertAttributeEquals(
      'ERROR_IDENTIFIER', '_errorIdentifier', $controller
    );
  }

  /**
  * @covers PapayaControllerError::execute
  * @covers PapayaControllerError::_getOutput
  */
  public function testControllerExecute() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();
    $response
      ->expects($this->once())
      ->method('setStatus')
      ->with(
        $this->equalTo(500)
      );
    $response
      ->expects($this->once())
      ->method('setContentType')
      ->with(
        $this->equalTo('text/html')
      );
    $response
      ->expects($this->once())
      ->method('content')
      ->with(
        $this->isInstanceOf(PapayaResponseContentString::class)
      );
    $controller = new PapayaControllerError();
    $this->assertTrue(
      $controller->execute($application, $request, $response)
    );
  }
}
