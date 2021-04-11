<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Router\Route {

  use Papaya\Response;
  use Papaya\Router;
  use Papaya\Router\Route;
  use Papaya\TestCase;

  /**
   * @covers \Papaya\Router\Route\Group
   */
  class GroupTest extends TestCase {

    public function testIssetExpectingTrue() {
      $route = $this->createMock(Route::class);
      $routes = new Group($route);
      $this->assertTrue(isset($routes[0]));
    }

    public function testIssetExpectingFalse() {
      $route = $this->createMock(Route::class);
      $routes = new Group($route);
      $this->assertFalse(isset($routes[42]));
    }

    public function testGetAfterAdd() {
      $route = $this->createMock(Route::class);
      $routes = new Group();
      $routes[] = $route;
      $this->assertSame($route, $routes[0]);
    }

    public function testGetAfterSet() {
      $route = $this->createMock(Route::class);
      $routes = new Group(static function() {});
      $routes[0] = $route;
      $this->assertSame($route, $routes[0]);
    }

    public function testNotSetAfterRemove() {
      $route = $this->createMock(Route::class);
      $routes = new Group($route);
      $this->assertTrue(isset($routes[0]));
      unset($routes[0]);
      $this->assertFalse(isset($routes[0]));
    }

    public function testInvokeCallsRoutes() {
      $router = $this->createMock(Router::class);
      $routeOne = $this->createMock(Route::class);
      $routeOne
        ->expects($this->once())
        ->method('__invoke')
        ->with($router, 'context', 'argument')
        ->willReturn(NULL);
      $routeTwo = $this->createMock(Route::class);
      $routeTwo
        ->expects($this->once())
        ->method('__invoke')
        ->with($router, 'context', 'argument')
        ->willReturn(NULL);

      $routes = new Group($routeOne, $routeTwo);
      $this->assertNull($routes($router, 'context', 'argument'));
    }

    public function testInvokeCallsRoutesUntilTruthyResponse() {
      $router = $this->createMock(Router::class);
      $routeOne = $this->createMock(Route::class);
      $routeOne
        ->expects($this->once())
        ->method('__invoke')
        ->with($router, 'context', 'argument')
        ->willReturn($response = $this->createMock(Response::class));
      $routeTwo = $this->createMock(Route::class);
      $routeTwo
        ->expects($this->never())
        ->method('__invoke');

      $routes = new Group($routeOne, $routeTwo);
      $this->assertSame($response, $routes($router, 'context', 'argument'));
    }
  }

}
