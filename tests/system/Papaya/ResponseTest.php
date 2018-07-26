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

require_once __DIR__.'/../../bootstrap.php';

class PapayaResponseTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Response::helper
  */
  public function testHelperSetHelper() {
    $response = new Papaya\Response();
    $helper = $this->createMock(\PapayaResponseHelper::class);
    $response->helper($helper);
    $this->assertAttributeSame(
      $helper, '_helper', $response
    );
  }

  /**
  * @covers \Papaya\Response::helper
  */
  public function testHelperGetHelperAfterSet() {
    $response = new Papaya\Response();
    $helper = $this->createMock(\PapayaResponseHelper::class);
    $this->assertSame(
      $helper, $response->helper($helper)
    );
  }

  /**
  * @covers \Papaya\Response::helper
  */
  public function testHelperGetHelperImplizitCreate() {
    $response = new Papaya\Response();
    $this->assertInstanceOf(
      \PapayaResponseHelper::class, $response->helper()
    );
  }

  /**
  * @covers \Papaya\Response::headers
  */
  public function testHeadersSetHeaders() {
    $response = new Papaya\Response();
    $headers = $this->createMock(\PapayaResponseHeaders::class);
    $response->headers($headers);
    $this->assertAttributeSame(
      $headers, '_headers', $response
    );
  }

  /**
  * @covers \Papaya\Response::headers
  */
  public function testHeadersGetHeadersAfterSet() {
    $response = new Papaya\Response();
    $headers = $this->createMock(\PapayaResponseHeaders::class);
    $this->assertSame(
      $headers, $response->headers($headers)
    );
  }

  /**
  * @covers \Papaya\Response::headers
  */
  public function testHeadersGetHeadersImplizitCreate() {
    $response = new Papaya\Response();
    $this->assertInstanceOf(
      \PapayaResponseHeaders::class, $response->headers()
    );
  }

  /**
  * @covers \Papaya\Response::content
  */
  public function testContentSetContent() {
    $response = new Papaya\Response();
    $content = $this->createMock(\PapayaResponseContent::class);
    $response->content($content);
    $this->assertAttributeSame(
      $content, '_content', $response
    );
  }

  /**
  * @covers \Papaya\Response::content
  */
  public function testContentGetContentAfterSet() {
    $response = new Papaya\Response();
    $content = $this->createMock(\PapayaResponseContent::class);
    $this->assertSame(
      $content, $response->content($content)
    );
  }

  /**
  * @covers \Papaya\Response::content
  */
  public function testContentGetContentImplizitCreate() {
    $response = new Papaya\Response();
    $this->assertInstanceOf(
      \PapayaResponseContent::class, $response->content()
    );
  }

  /**
  * @covers \Papaya\Response::setStatus
  */
  public function testSetStatus() {
    $response = new Papaya\Response();
    $response->setStatus(404);
    $this->assertAttributeEquals(
      404, '_status', $response
    );
  }

  /**
  * @covers \Papaya\Response::setStatus
  */
  public function testSetStatusInvalidExpectingError() {
    $response = new Papaya\Response();
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Unknown response status code: 999');
    $response->setStatus(999);
  }

  /**
  * @covers \Papaya\Response::getStatus
  */
  public function testGetStatusAfterSet() {
    $response = new Papaya\Response();
    $response->setStatus(404);
    $this->assertEquals(
      404, $response->getStatus()
    );
  }

  /**
  * @covers \Papaya\Response::setContentType
  */
  public function testSetContentType() {
    $headers = $this->createMock(\PapayaResponseHeaders::class);
    $headers
      ->expects($this->once())
      ->method('set')
      ->with('Content-Type', 'text/plain; charset=UTF-8');
    $response = new Papaya\Response();
    $response->headers($headers);
    $response->setContentType('text/plain');
  }

  /**
  * @covers \Papaya\Response::setContentType
  */
  public function testSetContentTypeAndEncoding() {
    $headers = $this->createMock(\PapayaResponseHeaders::class);
    $headers
      ->expects($this->once())
      ->method('set')
      ->with('Content-Type', 'text/plain; charset=ISO-8859-15');
    $response = new Papaya\Response();
    $response->headers($headers);
    $response->setContentType('text/plain', 'ISO-8859-15');
  }

  /**
   * @covers \Papaya\Response::setCache
   * @dataProvider provideCacheHeaders
   * @param array $expected
   * @param int $cacheMode
   * @param int $cachePeriod
   * @param int $cacheStartTime
   * @param int $now
   */
  public function testSetCache(array $expected, $cacheMode, $cachePeriod, $cacheStartTime, $now) {
    $response = new Papaya\Response();
    $response->setCache($cacheMode, $cachePeriod, $cacheStartTime, $now);
    $this->assertAttributeEquals(
      $expected,
      '_headers',
      $response->headers()
    );
  }

  /**
  * @covers \Papaya\Response::setCache
  */
  public function testSetCacheOneHourFromNow() {
    $response = new Papaya\Response();
    $response->setCache('private', 3600);
    $headers = $response->headers();
    $this->assertEquals(
      'private, max-age=3600, pre-check=3600, no-transform', $headers['Cache-Control']
    );
    $this->assertStringStartsWith(
      gmdate('D, d M Y H:i', time() + 3600), $headers['Expires']
    );
  }

  /**
  * @covers \Papaya\Response::sendStatus
  */
  public function testSendStatus() {
    $helper = $this->createMock(\PapayaResponseHelper::class);
    $helper
      ->expects($this->once())
      ->method('header')
      ->with(
        $this->equalTo('HTTP/1.1 204 No Content'),
        $this->equalTo(TRUE),
        $this->equalTo(204)
      );
    $response = new Papaya\Response();
    $response->helper($helper);
    $response->sendStatus(204);
  }

  /**
  * @covers \Papaya\Response::sendStatus
  */
  public function testSendStatusAfterSetting() {
    $helper = $this->createMock(\PapayaResponseHelper::class);
    $helper
      ->expects($this->once())
      ->method('header')
      ->with(
        $this->equalTo('HTTP/1.1 204 No Content'),
        $this->equalTo(TRUE),
        $this->equalTo(204)
      );
    $response = new Papaya\Response();
    $response->helper($helper);
    $response->setStatus(204);
    $response->sendStatus();
  }

  /**
  * @covers \Papaya\Response::sendStatus
  */
  public function testSendStatusInvalid() {
    $helper = $this->createMock(\PapayaResponseHelper::class);
    $helper
      ->expects($this->once())
      ->method('header')
      ->with(
        $this->equalTo('HTTP/1.1 200 OK'),
        $this->equalTo(TRUE),
        $this->equalTo(200)
      );
    $response = new Papaya\Response();
    $response->helper($helper);
    $response->sendStatus(999);
  }

  /**
  * @covers \Papaya\Response::sendHeader
  */
  public function testSendHeader() {
    $application = $this->mockPapaya()->application();
    $helper = $this->createMock(\PapayaResponseHelper::class);
    $helper
      ->expects($this->once())
      ->method('headersSent')
      ->will($this->returnValue(FALSE));
    $helper
      ->expects($this->once())
      ->method('header')
      ->with(
        $this->equalTo('X-Unit-Test: true')
      );
    $response = new Papaya\Response();
    $response->papaya($application);
    $response->helper($helper);
    $response->sendHeader('X-Unit-Test: true');
  }

  /**
  * @covers \Papaya\Response::sendHeader
  */
  public function testSendHeaderBlockXHeader() {
    $application = $this->mockPapaya()->application(
      array(
        'Options' => $this->mockPapaya()->options(
          array('PAPAYA_DISABLE_XHEADERS' => TRUE)
        )
      )
    );
    $helper = $this->createMock(\PapayaResponseHelper::class);
    $helper
      ->expects($this->once())
      ->method('headersSent')
      ->will($this->returnValue(FALSE));
    $helper
      ->expects($this->never())
      ->method('header');
    $response = new Papaya\Response();
    $response->papaya($application);
    $response->helper($helper);
    $response->sendHeader('X-Unit-Test: true');
  }

  /**
  * @covers \Papaya\Response::send
  */
  public function testSend() {
    $application = $this->mockPapaya()->application();
    $helper = $this->createMock(\PapayaResponseHelper::class);
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
    $response = new Papaya\Response();
    $response->papaya($application);
    $response->helper($helper);
    $response->setContentType('text/plain');
    $response->content(new \PapayaResponseContentString('SAMPLE'));
    ob_start();
    $response->send();
    $this->assertEquals(
      'SAMPLE',
      ob_get_clean()
    );
  }

  /**
  * @covers \Papaya\Response::send
  */
  public function testSendWithCustomHeaders() {
    $headers = $this->createMock(\PapayaResponseHeaders::class);
    $headers
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              'X-Simple' => '1',
              'X-Complex' => array('2_1', '2_2')
            )
          )
        )
      );
    $helper = $this->createMock(\PapayaResponseHelper::class);
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
    $response = new Papaya\Response();
    $response->papaya($this->mockPapaya()->application());
    $response->helper($helper);
    $response->headers($headers);
    $response->setContentType('text/plain');
    $response->content(new \PapayaResponseContentString('SAMPLE'));
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
    return array(
      'nocache' => array(
        array(
          'Cache-Control' =>
            'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform',
          'Pragma' => 'no-cache',
          'Expires' => 'Thu, 19 Nov 1981 08:52:00 GMT'
        ),
        'nocache',
        0,
        NULL,
        NULL
      ),
      'public, 1800 seconds' => array(
        array(
          'Cache-Control' => 'public, max-age=1800, pre-check=1800, no-transform',
          'Expires' => 'Thu, 15 Jun 2000 12:30:00 GMT',
          'Last-Modified' => 'Thu, 15 Jun 2000 12:00:00 GMT',
          'Pragma' => ''
        ),
        'public',
        1800,
        gmmktime(12, 0, 0, 6, 15, 2000),
        gmmktime(12, 0, 0, 6, 15, 2000)
      ),
      'private, 1800 seconds, 900 seconds gone' => array(
        array(
          'Cache-Control' => 'private, max-age=900, pre-check=900, no-transform',
          'Expires' => 'Thu, 15 Jun 2000 12:30:00 GMT',
          'Last-Modified' => 'Thu, 15 Jun 2000 12:00:00 GMT',
          'Pragma' => ''
        ),
        'private',
        1800,
        gmmktime(12, 0, 0, 6, 15, 2000),
        gmmktime(12, 15, 0, 6, 15, 2000)
      )
    );
  }
}
