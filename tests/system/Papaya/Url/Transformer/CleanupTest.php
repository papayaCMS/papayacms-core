<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUrlTransformerCleanupTest extends PapayaTestCase {

  /**
  * @covers PapayaUrlTransformerCleanup::transform
  * @covers PapayaUrlTransformerCleanup::_calculateRealPath
  * @dataProvider transformDataProvider
  */
  public function testTransform($expected, $targetUrl) {
    $transformer = new PapayaUrlTransformerCleanup();
    $this->assertSame(
      $expected,
      $transformer->transform(
        $targetUrl
      )
    );
  }

  /*************************************
  * Data Providers
  *************************************/

  public static function transformDataProvider() {
    return array(
      array(
        '/',
        '/'
      ),
      array(
        '/some/location.html',
        '/some/location.html'
      ),
      array(
        '/some/location.html?foo',
        '/some/location.html?foo'
      ),
      array(
        '/some/location.html#bar',
        '/some/location.html#bar'
      ),
      array(
        '/some/location/',
        '/some/location/'
      ),
      array(
        'http://www.example.com/some/location.html',
        'http://www.example.com/some/location.html'
      ),
      array(
        'http://www.example.com:80/some/location.html',
        'http://www.example.com:80/some/location.html'
      ),
      array(
        'http://user@www.example.com:80/some/location.html',
        'http://user@www.example.com:80/some/location.html'
      ),
      array(
        'http://user:pass@www.example.com/some/location.html',
        'http://user:pass@www.example.com/some/location.html'
      ),
      array(
        '/some/location.html',
        '/some//////////////location.html'
      ),
      array(
        '/location.html',
        '/some/../location.html'
      ),
      array(
        '/some/location.html',
        '/some/path/../path/../location.html'
      ),
      array(
        '/some/location.html',
        '/some/path/path/..//../location.html'
      ),
      array(
        'http://www.example.com/some/location.html',
        'http://www.example.com/some//path/path/..//../location.html'
      ),
    );
  }
}
