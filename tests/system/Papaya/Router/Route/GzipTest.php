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
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Router\Route\Gzip
   */
  class GzipTest extends TestCase {

    public function testInvoke() {
      if (!extension_loaded('zlib')) {
        $this->markTestSkipped('Zlib extension needed.');
      }

      /** @var \PHPUnit_Framework_MockObject_MockObject|Router $router */
      $router = $this->createMock(Router::class);

      $helper = $this->createMock(Response\Helper::class);
      $helper
        ->method('allowGzip')
        ->willReturn(TRUE);
      $helper
        ->method('hasOutputBuffers')
        ->willReturn(FALSE);

      $outerHeaders = new Response\Headers();
      $outerResponse = $this->createMock(Response::class);
      $outerResponse
        ->method('content')
        ->with(
          $this->isInstanceOf(Response\Content\Text::class)
        )
        ->willReturnCallback(
          function (Response\Content\Text $content) {
            $this->assertSame('success', gzdecode($content));
          }
        );
      $outerResponse
        ->method('headers')
        ->willReturn($outerHeaders);

      $innerContent = new Response\Content\Text('success');
      $innerResponse = $this->createMock(Response::class);
      $innerResponse
        ->method('helper')
        ->willReturn($helper);
      $innerResponse
        ->method('content')
        ->willReturn($innerContent);
      $innerResponse
        ->expects($this->once())
        ->method('duplicate')
        ->willReturn($outerResponse);

      $route = new Gzip(
        static function () use ($innerResponse) {
          return $innerResponse;
        }
      );

      $response = $route($router);
      $this->assertSame(
        'gzip',
        (string)$response->headers()['Content-Encoding']
      );
    }

    public function testInvokeWithGzipNotAllowed() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Router $router */
      $router = $this->createMock(Router::class);

      $helper = $this->createMock(Response\Helper::class);
      $helper
        ->method('allowGzip')
        ->willReturn(FALSE);

      $innerContent = new Response\Content\Text('success');
      $innerResponse = $this->createMock(Response::class);
      $innerResponse
        ->method('helper')
        ->willReturn($helper);
      $innerResponse
        ->method('content')
        ->willReturn($innerContent);
      $innerResponse
        ->expects($this->never())
        ->method('duplicate');

      $route = new Gzip(
        static function () use ($innerResponse) {
          return static function () use ($innerResponse) {
            return $innerResponse;
          };
        }
      );

      $response = $route($router);
      $this->assertSame(
        'success',
        (string)$response->content()
      );
    }

    public function testInvokeWithRouteReturningNull() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Router $router */
      $router = $this->createMock(Router::class);
      $route = new Gzip(
        static function () {
          return NULL;
        }
      );
      $this->assertNull(
        $route($router)
      );
    }
  }
}
