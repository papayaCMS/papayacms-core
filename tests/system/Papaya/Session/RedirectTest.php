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

namespace Papaya\Session;

require_once __DIR__.'/../../../bootstrap.php';

class RedirectTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Session\Redirect::__construct
   */
  public function testConstructor() {
    $redirect = new Redirect('foo');
    $this->assertAttributeEquals(
      'foo', '_sessionName', $redirect
    );
  }

  /**
   * @covers \Papaya\Session\Redirect::__construct
   */
  public function testConstructorWithAllParameters() {
    $redirect = new Redirect('sid', '42', Id::SOURCE_PATH, 'test');
    $this->assertAttributeEquals(
      '42', '_sessionId', $redirect
    );
    $this->assertAttributeEquals(
      Id::SOURCE_PATH, '_transport', $redirect
    );
    $this->assertAttributeEquals(
      'test', '_reason', $redirect
    );
  }

  /**
   * @covers \Papaya\Session\Redirect::url
   */
  public function testUrlSet() {
    $redirect = new Redirect('sid', '42', Id::SOURCE_PATH, 'test');
    $url = $this->createMock(\Papaya\URL::class);
    $redirect->url($url);
    $this->assertAttributeSame(
      $url, '_url', $redirect
    );
  }

  /**
   * @covers \Papaya\Session\Redirect::url
   */
  public function testUrlGetAfterSet() {
    $redirect = new Redirect('sid', '42', Id::SOURCE_PATH, 'test');
    $url = $this->createMock(\Papaya\URL::class);
    $redirect->url($url);
    $this->assertSame(
      $url, $redirect->url()
    );
  }

  /**
   * @covers \Papaya\Session\Redirect::url
   */
  public function testUrlGetCloningRequestUrl() {
    $redirect = new Redirect('sid', '42', Id::SOURCE_PATH, 'test');
    $redirect->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertInstanceOf(
      \Papaya\URL::class, $redirect->url()
    );
  }

  /**
   * @covers       \Papaya\Session\Redirect::prepare
   * @covers       \Papaya\Session\Redirect::_setQueryParameter
   * @covers       \Papaya\Session\Redirect::_setPathParameter
   * @dataProvider provideRedirectData
   * @param string $expectedUrl
   * @param string $url
   * @param int $transport
   * @param string $sessionName
   */
  public function testPrepareAddPathAndQueryString(
    $expectedUrl, $url, $transport, $sessionName = ''
  ) {
    $redirect = new Redirect(
      'sid'.$sessionName, '42', $transport, 'test'
    );
    $redirect->papaya(
      $this->mockPapaya()->application(
        array('Request' => $this->mockPapaya()->request(array(), $url))
      )
    );
    $redirect->prepare();
    $this->assertEquals(
      array(
        'Cache-Control' =>
          'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform',
        'Pragma' => 'no-cache',
        'Expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
        'X-Papaya-Redirect' => 'test',
        'Location' => $expectedUrl
      ),
      $redirect->headers()->getIterator()->getArrayCopy()
    );
  }

  /**
   * @covers \Papaya\Session\Redirect::send
   */
  public function testSend() {
    $application = $this->mockPapaya()->application();
    $helper = $this->createMock(\Papaya\Response\Helper::class);
    $helper
      ->expects($this->exactly(8))
      ->method('header')
      ->with(
        $this->logicalOr(
          $this->equalTo('HTTP/1.1 302 Found'),
          $this->stringStartsWith('Cache-Control:'),
          $this->stringStartsWith('Pragma:'),
          $this->stringStartsWith('Expires:'),
          $this->equalTo('X-Papaya-Redirect: test'),
          $this->equalTo('Location: http://www.test.tld/test.html'),
          $this->equalTo('Content-Length: 6'),
          $this->equalTo('Content-Type: text/plain; charset=UTF-8')
        )
      );
    $redirect = new Redirect('sid', '42', 0, 'test');
    $redirect->papaya($application);
    $redirect->helper($helper);
    $redirect->setContentType('text/plain');
    $redirect->content(new \Papaya\Response\Content\Text('SAMPLE'));
    ob_start();
    $redirect->send();
    $this->assertEquals(
      'SAMPLE',
      ob_get_clean()
    );
  }

  /*****************************
   * Data Provider
   *****************************/

  public static function provideRedirectData() {
    return array(
      'add query and path' => array(
        'http://www.test.tld/sid42/test.html?sid=42',
        'http://www.test.tld/test.html',
        Id::SOURCE_PATH | Id::SOURCE_QUERY
      ),
      'remove query and path' => array(
        'http://www.test.tld/test.html',
        'http://www.test.tld/sid42/test.html?sid=42',
        0
      ),
      'add query, remove path' => array(
        'http://www.test.tld/test.html?sid=42',
        'http://www.test.tld/sid42/test.html',
        Id::SOURCE_QUERY
      ),
      'add path, remove query' => array(
        'http://www.test.tld/sid42/test.html',
        'http://www.test.tld/test.html?sid=42',
        Id::SOURCE_PATH
      ),
      'add path, with session name' => array(
        'http://www.test.tld/sidfoo42/test.html',
        'http://www.test.tld/sid42/test.html',
        Id::SOURCE_PATH,
        'foo'
      )
    );
  }
}
