<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaControllerGroupTest extends PapayaTestCase {

  /**
   * @covers PapayaControllerGroup
   */
  public function testConstructorWithTwoControllers() {
    $controller = new PapayaControllerGroup(
      $one = $this->createMock(PapayaController::class),
      $two = $this->createMock(PapayaController::class)
    );
    $this->assertEquals(
      array($one, $two),
      iterator_to_array($controller)
    );
  }

  /**
   * @covers PapayaControllerGroup
   */
  public function testExecute() {
    $application = $this->mockPapaya()->application();
    $application
      ->expects($this->exactly(2))
      ->method('setObject')
      ->with($this->logicalOr('request', 'response'));
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();

    $one = $this->createMock(PapayaController::class);
    $one
      ->expects($this->once())
      ->method('execute')
      ->with($application, $request, $response)
      ->will($this->returnValue(FALSE));
    $two = $this->createMock(PapayaController::class);
    $two
      ->expects($this->once())
      ->method('execute')
      ->with($application, $request, $response)
      ->will($this->returnValue(TRUE));

    $controller = new PapayaControllerGroup($one, $two);
    $this->assertTrue(
      $controller->execute($application, $request, $response)
    );
  }

  /**
   * @covers PapayaControllerGroup
   */
  public function testExecuteWithoutControllers() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();
    $controller = new PapayaControllerGroup();
    $this->assertFalse(
      $controller->execute($application, $request, $response)
    );
  }

  public function testExecuteWithReturnedController() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();

    $one = $this->createMock(PapayaController::class);
    $one
      ->expects($this->once())
      ->method('execute')
      ->with($application, $request, $response)
      ->will($this->returnValue(TRUE));
    $two = $this->createMock(PapayaController::class);
    $two
      ->expects($this->once())
      ->method('execute')
      ->with($application, $request, $response)
      ->will($this->returnValue($one));

    $controller = new PapayaControllerGroup($two);
    $this->assertTrue(
      $controller->execute($application, $request, $response)
    );
  }

  public function testExecuteBreakRecursion() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();

    $one = $this->createMock(PapayaController::class);
    $one
      ->expects($this->exactly(20))
      ->method('execute')
      ->with($application, $request, $response)
      ->will($this->returnSelf());

    $controller = new PapayaControllerGroup($one);
    $this->assertFalse(
      $controller->execute($application, $request, $response)
    );
  }
}
