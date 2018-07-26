<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

use Papaya\Controller\Group;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaControllerGroupTest extends \PapayaTestCase {

  /**
   * @covers Group
   */
  public function testConstructorWithTwoControllers() {
    $controller = new Group(
      $one = $this->createMock(\PapayaController::class),
      $two = $this->createMock(\PapayaController::class)
    );
    $this->assertEquals(
      array($one, $two),
      iterator_to_array($controller)
    );
  }

  /**
   * @covers Group
   */
  public function testExecute() {
    $application = $this->mockPapaya()->application();
    $application
      ->expects($this->exactly(2))
      ->method('setObject')
      ->with($this->logicalOr('request', 'response'));
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();

    $one = $this->createMock(\PapayaController::class);
    $one
      ->expects($this->once())
      ->method('execute')
      ->with($application, $request, $response)
      ->will($this->returnValue(FALSE));
    $two = $this->createMock(\PapayaController::class);
    $two
      ->expects($this->once())
      ->method('execute')
      ->with($application, $request, $response)
      ->will($this->returnValue(TRUE));

    $controller = new Group($one, $two);
    $this->assertTrue(
      $controller->execute($application, $request, $response)
    );
  }

  /**
   * @covers Group
   */
  public function testExecuteWithoutControllers() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();
    $controller = new Group();
    $this->assertFalse(
      $controller->execute($application, $request, $response)
    );
  }

  public function testExecuteWithReturnedController() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();

    $one = $this->createMock(\PapayaController::class);
    $one
      ->expects($this->once())
      ->method('execute')
      ->with($application, $request, $response)
      ->will($this->returnValue(TRUE));
    $two = $this->createMock(\PapayaController::class);
    $two
      ->expects($this->once())
      ->method('execute')
      ->with($application, $request, $response)
      ->will($this->returnValue($one));

    $controller = new Group($two);
    $this->assertTrue(
      $controller->execute($application, $request, $response)
    );
  }

  public function testExecuteBreakRecursion() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();

    $one = $this->createMock(\PapayaController::class);
    $one
      ->expects($this->exactly(20))
      ->method('execute')
      ->with($application, $request, $response)
      ->will($this->returnSelf());

    $controller = new Group($one);
    $this->assertFalse(
      $controller->execute($application, $request, $response)
    );
  }
}
