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
  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\Router\Route\PathChoice
   */
  class PathChoiceTest extends TestCase {

    public function testInvokeCallsDefaultChoice() {
      $router = $this->createMock(Router::class);
      $routeOne = $this->createMock(Route::class);
      $routeOne
        ->expects($this->never())
        ->method('__invoke');
      $routeTwo = $this->createMock(Route::class);
      $routeTwo
        ->expects($this->once())
        ->method('__invoke')
        ->with($router)
        ->willReturn($response = $this->createMock(Response::class));

      $choices = new PathChoice(
        [
          'one' => $routeOne,
          'two' => $routeTwo
        ],
        'two'
      );

      $this->assertSame($response, $choices($router));
    }

    public function testInvokeCallsWithChoiceFromPath() {
      $router = $this->createMock(Router::class);
      $path = $this->createMock(Router\Path::class);
      $path
        ->expects($this->once())
        ->method('getRouteString')
        ->with(0, 0)
        ->willReturn('one');
      $routeOne = $this->createMock(Route::class);
      $routeOne
        ->expects($this->once())
        ->method('__invoke')
        ->with($router)
        ->willReturn($response = $this->createMock(Response::class));
      $routeTwo = $this->createMock(Route::class);
      $routeTwo
        ->expects($this->never())
        ->method('__invoke');

      $choices = new PathChoice(
        [
          'one' => $routeOne,
          'two' => $routeTwo
        ],
        'two'
      );

      $this->assertSame($response, $choices($router, $path));
    }

    public function testInvokeCallsWithoutMatchingChoice() {
      $router = $this->createMock(Router::class);
      $routeOne = $this->createMock(Route::class);
      $routeOne
        ->expects($this->never())
        ->method('__invoke');
      $routeTwo = $this->createMock(Route::class);

      $choices = new PathChoice(
        [
          'one' => $routeOne
        ],
        'two'
      );

      $this->assertNull($choices($router));
    }

    public function testGetChoicesByName() {
      $choices = new PathChoice(
        [
          'one' => $routeOne = $this->createMock(Route::class),
          'two' => $routeTwo = $this->createMock(Route::class)
        ]
      );

      $this->assertTrue(isset($choices['one']));
      $this->assertSame($routeOne, $choices['one']);
      $this->assertSame($routeTwo, $choices['two']);
    }

    public function testGetChoicesAfterSet() {
      $choices = new PathChoice(
        [
          'one' => $routeOne = $this->createMock(Route::class)
        ]
      );
      $this->assertFalse(isset($choices['two']));
      $choices['two'] = $routeTwo = $this->createMock(Route::class);
      $this->assertTrue(isset($choices['two']));
      $this->assertSame($routeTwo, $choices['two']);
    }

    public function testValidateChoicesAfterUnset() {
      $choices = new PathChoice(
        [
          'one' => $routeOne = $this->createMock(Route::class),
          'two' => $routeTwo = $this->createMock(Route::class)
        ]
      );
      $this->assertTrue(isset($choices['two']));
      unset($choices['two']);
      $this->assertFalse(isset($choices['two']));
    }
  }
}
