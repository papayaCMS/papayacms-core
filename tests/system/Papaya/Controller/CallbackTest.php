<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaControllerCallbackTest extends PapayaTestCase {

  /**
   * @covers PapayaControllerCallback
   */
  public function testExecute() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();

    $controller = new PapayaControllerCallback(array($this, 'callbackReturnsTrue'));
    $this->assertTrue(
      $controller->execute($application, $request, $response)
    );
  }

  public function callbackReturnsTrue(
    PapayaApplication $application,
    PapayaRequest &$request,
    PapayaResponse &$response
  ) {
    return TRUE;
  }
}
