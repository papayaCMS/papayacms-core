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
   * @covers \Papaya\Router\Route\JavaScript
   */
  class JavaScriptTest extends TestCase {

    public function testInvokeWithMultipleFiles() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Router $router */
      $router = $this->createMock(Router::class);

      $route = new JavaScript(
        [
          __DIR__.'/TestData/one.txt',
          __DIR__.'/TestData/fail.txt',
          __DIR__.'/TestData/two.txt'
        ]
      );

      $response = $route($router, NULL);
      $this->assertSame(
        'application/javascript; charset=UTF-8',
        (string)$response->headers()['Content-Type']
      );
      $this->assertSame(
        "/* File: one.txt */\nONE\n\n/* Failed: fail.txt */\n/* File: two.txt */\nTWO\n\n",
        (string)$response->content()
      );
    }
  }
}
