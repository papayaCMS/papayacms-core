<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaSessionRedirectTest extends PapayaTestCase {

  /**
  * @covers PapayaSessionRedirect::__construct
  */
  public function testConstructor() {
    $redirect = new PapayaSessionRedirect('foo');
    $this->assertAttributeEquals(
      'foo', '_sessionName', $redirect
    );
  }

  /**
  * @covers PapayaSessionRedirect::__construct
  */
  public function testConstructorWithAllParameters() {
    $redirect = new PapayaSessionRedirect('sid', '42', PapayaSessionId::SOURCE_PATH, 'test');
    $this->assertAttributeEquals(
      '42', '_sessionId', $redirect
    );
    $this->assertAttributeEquals(
      PapayaSessionId::SOURCE_PATH, '_transport', $redirect
    );
    $this->assertAttributeEquals(
      'test', '_reason', $redirect
    );
  }

  /**
  * @covers PapayaSessionRedirect::url
  */
  public function testUrlSet() {
    $redirect = new PapayaSessionRedirect('sid', '42', PapayaSessionId::SOURCE_PATH, 'test');
    $url = $this->createMock(PapayaUrl::class);
    $redirect->url($url);
    $this->assertAttributeSame(
      $url, '_url', $redirect
    );
  }

  /**
  * @covers PapayaSessionRedirect::url
  */
  public function testUrlGetAfterSet() {
    $redirect = new PapayaSessionRedirect('sid', '42', PapayaSessionId::SOURCE_PATH, 'test');
    $url = $this->createMock(PapayaUrl::class);
    $redirect->url($url);
    $this->assertSame(
      $url, $redirect->url()
    );
  }

  /**
  * @covers PapayaSessionRedirect::url
  */
  public function testUrlGetCloningRequestUrl() {
    $redirect = new PapayaSessionRedirect('sid', '42', PapayaSessionId::SOURCE_PATH, 'test');
    $redirect->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertInstanceOf(
      'PapayaUrl', $redirect->url()
    );
  }

  /**
  * @covers PapayaSessionRedirect::prepare
  * @covers PapayaSessionRedirect::_setQueryParameter
  * @covers PapayaSessionRedirect::_setPathParameter
  * @dataProvider provideRedirectData
  */
  public function testPrepareAddPathAndQueryString(
    $expectedUrl, $url, $transport, $sessionName = ''
  ) {
    $redirect = new PapayaSessionRedirect(
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
  * @covers PapayaSessionRedirect::send
  */
  public function testSend() {
    $application = $this->mockPapaya()->application();
    $helper = $this->createMock(PapayaResponseHelper::class);
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
    $redirect = new PapayaSessionRedirect('sid', '42', 0, 'test');
    $redirect->papaya($application);
    $redirect->helper($helper);
    $redirect->setContentType('text/plain');
    $redirect->content(new PapayaResponseContentString('SAMPLE'));
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
        PapayaSessionId::SOURCE_PATH | PapayaSessionId::SOURCE_QUERY
      ),
      'remove query and path' => array(
        'http://www.test.tld/test.html',
        'http://www.test.tld/sid42/test.html?sid=42',
        0
      ),
      'add query, remove path' => array(
        'http://www.test.tld/test.html?sid=42',
        'http://www.test.tld/sid42/test.html',
        PapayaSessionId::SOURCE_QUERY
      ),
      'add path, remove query' => array(
        'http://www.test.tld/sid42/test.html',
        'http://www.test.tld/test.html?sid=42',
        PapayaSessionId::SOURCE_PATH
      ),
      'add path, with session name' => array(
        'http://www.test.tld/sidfoo42/test.html',
        'http://www.test.tld/sid42/test.html',
        PapayaSessionId::SOURCE_PATH,
        'foo'
      )
    );
  }
}
