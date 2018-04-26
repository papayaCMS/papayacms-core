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

    $controller = new PapayaControllerCallback(
      function(
        /** @noinspection PhpUnusedParameterInspection */
        PapayaApplication $application,
        PapayaRequest &$request,
        PapayaResponse &$response
      ) {
        return TRUE;
      }
    );
    $this->assertTrue(
      $controller->execute($application, $request, $response)
    );
  }
}
