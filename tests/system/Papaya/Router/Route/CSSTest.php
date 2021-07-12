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

  use Papaya\Router;
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Router\Route\CSS
   */
  class CSSTest extends TestCase {

    public function testInvoke() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Router $router */
      $router = $this->createMock(Router::class);
      $route = new CSS([__DIR__.'/TestData/example.css'], '');
      $response = $route($router);
      $this->assertSame(
        'text/css; charset=UTF-8',
        (string)$response->headers()['Content-Type']
      );
      $this->assertSame(
        "/* File: example.css */\n* {\n  color: /*\$example.color*/ white;\n}\n\n",
        (string)$response->content()
      );
    }

    public function testInvokeWithNonExistingFile() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Router $router */
      $router = $this->createMock(Router::class);
      $route = new CSS([__DIR__.'/TestData/non-existing.css'], '');
      $response = $route($router);
      $this->assertSame(
        'text/css; charset=UTF-8',
        (string)$response->headers()['Content-Type']
      );
      $this->assertSame(
        "/* Failed: non-existing.css */\n",
        (string)$response->content()
      );
    }

    public function testInvokeWithTheme() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Router $router */
      $router = $this->createMock(Router::class);
      $route = new CSS([__DIR__.'/TestData/example.css'], 'example', __DIR__.'/TestData');
      $response = $route($router);
      $this->assertSame(
        'text/css; charset=UTF-8',
        (string)$response->headers()['Content-Type']
      );
      $this->assertSame(
        "/* File: example.css */\n* {\n  color: black;\n}\n\n",
        (string)$response->content()
      );
    }
  }

}
