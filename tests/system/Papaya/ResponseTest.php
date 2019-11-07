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

namespace Papaya {
  require_once __DIR__.'/../../bootstrap.php';

  /**
   * @covers \Papaya\Response
   */
  class ResponseTest extends TestCase {

    public function testHelperGetHelperAfterSet() {
      $response = new Response();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Helper $helper */
      $helper = $this->createMock(Response\Helper::class);
      $this->assertSame(
        $helper, $response->helper($helper)
      );
    }

    public function testHelperGetHelperImplicitCreate() {
      $response = new Response();
      $this->assertInstanceOf(
        Response\Helper::class, $response->helper()
      );
    }

    public function testHeadersGetHeadersAfterSet() {
      $response = new Response();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Headers $headers */
      $headers = $this->createMock(Response\Headers::class);
      $this->assertSame(
        $headers, $response->headers($headers)
      );
    }

    public function testHeadersGetHeadersImplicitCreate() {
      $response = new Response();
      $this->assertInstanceOf(
        Response\Headers::class, $response->headers()
      );
    }

    public function testContentGetContentAfterSet() {
      $response = new Response();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Content $content */
      $content = $this->createMock(Response\Content::class);
      $this->assertSame(
        $content, $response->content($content)
      );
    }

    public function testContentGetContentImplicitCreate() {
      $response = new Response();
      $this->assertInstanceOf(
        Response\Content::class, $response->content()
      );
    }

    public function testSetStatusInvalidExpectingError() {
      $response = new Response();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Unknown response status code: 999');
      $response->setStatus(999);
    }

    public function testGetStatusAfterSet() {
      $response = new Response();
      $response->setStatus(404);
      $this->assertEquals(
        404, $response->getStatus()
      );
    }

    public function testSetContentType() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Headers $headers */
      $headers = $this->createMock(Response\Headers::class);
      $headers
        ->expects($this->once())
        ->method('set')
        ->with('Content-Type', 'text/plain; charset=UTF-8');
      $response = new Response();
      $response->headers($headers);
      $response->setContentType('text/plain');
    }

    public function testSetContentTypeAndEncoding() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Headers $headers */
      $headers = $this->createMock(Response\Headers::class);
      $headers
        ->expects($this->once())
        ->method('set')
        ->with('Content-Type', 'text/plain; charset=ISO-8859-15');
      $response = new Response();
      $response->headers($headers);
      $response->setContentType('text/plain', 'ISO-8859-15');
      $this->assertSame('text/plain', $response->getContentType());
      $this->assertSame('ISO-8859-15', $response->getContentEncoding());
    }

    /**
     * @dataProvider provideCacheHeaders
     * @param array $expected
     * @param int $cacheMode
     * @param int $cachePeriod
     * @param int $cacheStartTime
     * @param int $now
     */
    public function testSetCache(array $expected, $cacheMode, $cachePeriod, $cacheStartTime, $now) {
      $response = new Response();
      $response->setCache($cacheMode, $cachePeriod, $cacheStartTime, $now);
      $this->assertAttributeEquals(
        $expected,
        '_headers',
        $response->headers()
      );
    }

    public function testSetCacheOneHourFromNow() {
      $response = new Response();
      $response->setCache('private', 3600);
      $headers = $response->headers();
      $this->assertEquals(
        'private, max-age=3600, pre-check=3600, no-transform', $headers['Cache-Control']
      );
      $this->assertStringStartsWith(
        gmdate('D, d M Y H:i', time() + 3600), $headers['Expires']
      );
    }

    public function testSendStatus() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Helper $helper */
      $helper = $this->createMock(Response\Helper::class);
      $helper
        ->expects($this->once())
        ->method('header')
        ->with(
          $this->equalTo('HTTP/1.1 204 No Content'),
          $this->equalTo(TRUE),
          $this->equalTo(204)
        );
      $response = new Response();
      $response->helper($helper);
      $response->sendStatus(204);
    }

    public function testSendStatusAfterSetting() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Helper $helper */
      $helper = $this->createMock(Response\Helper::class);
      $helper
        ->expects($this->once())
        ->method('header')
        ->with(
          $this->equalTo('HTTP/1.1 204 No Content'),
          $this->equalTo(TRUE),
          $this->equalTo(204)
        );
      $response = new Response();
      $response->helper($helper);
      $response->setStatus(204);
      $response->sendStatus();
    }

    public function testSendStatusInvalid() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Helper $helper */
      $helper = $this->createMock(Response\Helper::class);
      $helper
        ->expects($this->once())
        ->method('header')
        ->with(
          $this->equalTo('HTTP/1.1 200 OK'),
          $this->equalTo(TRUE),
          $this->equalTo(200)
        );
      $response = new Response();
      $response->helper($helper);
      $response->sendStatus(999);
    }

    public function testSendHeader() {
      $application = $this->mockPapaya()->application();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Helper $helper */
      $helper = $this->createMock(Response\Helper::class);
      $helper
        ->expects($this->once())
        ->method('headersSent')
        ->willReturn(FALSE);
      $helper
        ->expects($this->once())
        ->method('header')
        ->with(
          $this->equalTo('X-Unit-Test: true')
        );
      $response = new Response();
      $response->papaya($application);
      $response->helper($helper);
      $response->sendHeader('X-Unit-Test: true');
    }

    public function testSendHeaderBlockXHeader() {
      $application = $this->mockPapaya()->application(
        [
          'Options' => $this->mockPapaya()->options(
            ['PAPAYA_DISABLE_XHEADERS' => TRUE]
          )
        ]
      );
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Helper $helper */
      $helper = $this->createMock(Response\Helper::class);
      $helper
        ->expects($this->once())
        ->method('headersSent')
        ->willReturn(FALSE);
      $helper
        ->expects($this->never())
        ->method('header');
      $response = new Response();
      $response->papaya($application);
      $response->helper($helper);
      $response->sendHeader('X-Unit-Test: true');
    }

    public function testSend() {
      $application = $this->mockPapaya()->application();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Helper $helper */
      $helper = $this->createMock(Response\Helper::class);
      $helper
        ->expects($this->exactly(3))
        ->method('header')
        ->with(
          $this->logicalOr(
            $this->equalTo('HTTP/1.1 200 OK'),
            $this->equalTo('Content-Type: text/plain; charset=UTF-8'),
            $this->equalTo('Content-Length: 6')
          )
        );
      $response = new Response();
      $response->papaya($application);
      $response->helper($helper);
      $response->setContentType('text/plain');
      $response->content(new Response\Content\Text('SAMPLE'));
      $this->assertFalse($response->isSent());
      ob_start();
      $response->send();
      $this->assertEquals(
        'SAMPLE',
        ob_get_clean()
      );
      $this->assertTrue($response->isSent());
    }

    public function testSendWithCustomHeaders() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Headers $headers */
      $headers = $this->createMock(Response\Headers::class);
      $headers
        ->expects($this->once())
        ->method('getIterator')
        ->willReturn(
          new \ArrayIterator(
            [
              'X-Simple' => '1',
              'X-Complex' => ['2_1', '2_2']
            ]
          )
        );
      /** @var \PHPUnit_Framework_MockObject_MockObject|Response\Helper $helper */
      $helper = $this->createMock(Response\Helper::class);
      $helper
        ->expects($this->exactly(4))
        ->method('header')
        ->with(
          $this->logicalOr(
            $this->equalTo('HTTP/1.1 200 OK'),
            $this->equalTo('X-Simple: 1'),
            $this->equalTo('X-Complex: 2_1'),
            $this->equalTo('X-Complex: 2_2')
          )
        );
      $response = new Response();
      $response->papaya($this->mockPapaya()->application());
      $response->helper($helper);
      $response->headers($headers);
      $response->setContentType('text/plain');
      $response->content(new Response\Content\Text('SAMPLE'));
      ob_start();
      $response->send();
      $this->assertEquals(
        'SAMPLE',
        ob_get_clean()
      );
    }

    /*************************
     * Data Provider
     *************************/

    public static function provideCacheHeaders() {
      return [
        'nocache' => [
          [
            'Cache-Control' =>
              'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform',
            'Pragma' => 'no-cache',
            'Expires' => 'Thu, 19 Nov 1981 08:52:00 GMT'
          ],
          'nocache',
          0,
          NULL,
          NULL
        ],
        'public, 1800 seconds' => [
          [
            'Cache-Control' => 'public, max-age=1800, pre-check=1800, no-transform',
            'Expires' => 'Thu, 15 Jun 2000 12:30:00 GMT',
            'Last-Modified' => 'Thu, 15 Jun 2000 12:00:00 GMT',
            'Pragma' => ''
          ],
          'public',
          1800,
          gmmktime(12, 0, 0, 6, 15, 2000),
          gmmktime(12, 0, 0, 6, 15, 2000)
        ],
        'private, 1800 seconds, 900 seconds gone' => [
          [
            'Cache-Control' => 'private, max-age=900, pre-check=900, no-transform',
            'Expires' => 'Thu, 15 Jun 2000 12:30:00 GMT',
            'Last-Modified' => 'Thu, 15 Jun 2000 12:00:00 GMT',
            'Pragma' => ''
          ],
          'private',
          1800,
          gmmktime(12, 0, 0, 6, 15, 2000),
          gmmktime(12, 15, 0, 6, 15, 2000)
        ]
      ];
    }
  }
}
