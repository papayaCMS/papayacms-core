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

namespace Papaya {

  /**
   * @covers \Papaya\Router
   */
  class RouterTest extends TestCase {

    public function testGetRouteProvidedInConstructor() {
      $router = new Router($this->mockPapaya()->application(), $route = static function() {});
      $this->assertSame($route, $router->route());
    }

    public function testGetRouteAfterSet() {
      $router = new Router($this->mockPapaya()->application());
      $router->route($route = static function() {});
      $this->assertSame($route, $router->route());
    }

    public function testGetRouteImplicitCreate() {
      $router = new Router($this->mockPapaya()->application());
      $this->assertInstanceOf(Router\Route\Error::class, $router->route());
    }

    public function testExecuteRouteReturningResponse() {
      $response = $this->createMock(Response::class);
      $router = new Router(
        $this->mockPapaya()->application(),
        function($router, $context) use($response) {
          $this->assertInstanceOf(Router::class, $router);
          $this->assertNull($context);
          return $response;
        }
      );
      $this->assertSame($response, $router->execute());
    }

    public function testExecuteRouteReturningResponseInNestedCallable() {
      $response = $this->createMock(Response::class);
      $router = new Router(
        $this->mockPapaya()->application(),
        function() use($response) {
          return function($router, $context) use($response) {
            $this->assertInstanceOf(Router::class, $router);
            $this->assertNull($context);
            return $response;
          };
        }
      );
      $this->assertSame($response, $router->execute());
    }

    public function testExecuteRouteReturningNull() {
      $router = new Router(
        $this->mockPapaya()->application(),
        static function() {
          return NULL;
        }
      );
      $this->assertNull($router->execute());
    }
  }

}
