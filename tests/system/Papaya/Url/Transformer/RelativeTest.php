<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUrlTransformerRelativeTest extends PapayaTestCase {

  /**
  * get mock for PapayaUrl from url string
  * @param string $url
  * @return PapayaUrl
  */
  public function getPapayaUrlMockFixture($url) {
    $mapping = array(
      'getScheme' => 'scheme',
      'getUser' => 'user',
      'getPassword' => 'pass',
      'getHost' => 'host',
      'getPort' => 'port',
      'getPath' => 'path',
      'getQuery' => 'query',
      'getFragment' => 'fragment',
    );
    $urlObject = $this->getMock(PapayaUrl::class, array_keys($mapping));
    if (empty($url)) {
      $urlData = array();
    } else {
      $urlData = parse_url($url);
    }
    foreach ($mapping as $methodName => $arrayKey) {
      $urlObject
        ->expects($this->any())
        ->method($methodName)
        ->will(
          $this->returnValue(
            empty($urlData[$arrayKey]) ? NULL : $urlData[$arrayKey]
          )
        );
    }
    return $urlObject;
  }

  /**
  * @covers PapayaUrlTransformerRelative::transform
  * @covers PapayaUrlTransformerRelative::_comparePorts
  * @dataProvider transformDataProvider
  */
  public function testTransform($currentUrl, $targetUrl, $expected) {
    $transformer = new PapayaUrlTransformerRelative();
    $this->assertSame(
      $expected,
      $transformer->transform(
        $this->getPapayaUrlMockFixture($currentUrl),
        $this->getPapayaUrlMockFixture($targetUrl)
      )
    );
  }

  /**
  * @covers PapayaUrlTransformerRelative::getRelativePath
  * @dataProvider getRelativePathDataProvider
  */
  public function testGetRelativePath($currentPath, $targetPath, $expected) {
    $transformer = new PapayaUrlTransformerRelative();
    $this->assertEquals(
      $expected,
      $transformer->getRelativePath($currentPath, $targetPath)
    );
  }

  /*************************************
  * Data Providers
  *************************************/

  public static function transformDataProvider() {
    return array(
      'Valid: Full Url' => array(
        'http://www.sample.tld:80/foo',
        'http://www.sample.tld/foo?arg=1#fragment',
        'foo?arg=1#fragment'
      ),
      'Valid: Port 80 - Default Port' => array(
        'http://www.sample.tld:80/foo',
        'http://www.sample.tld/foo',
        'foo'
      ),
      'Valid: Default Port - Port 80' => array(
        'http://www.sample.tld:80/foo',
        'http://www.sample.tld/foo',
        'foo'
      ),
      'Invalid: Empty Target Host' => array(
        'http://www.sample.tld/foo',
        '',
        NULL
      ),
      'Invalid: Different scheme' => array(
        'http://www.sample.tld/foo',
        'https://www.sample.tld/foo',
        NULL
      ),
      'Invalid: Different host' => array(
        'http://www.sample.tld/foo',
        'http://www.sample2.tld/foo',
        NULL
      ),
      'Invalid: Different port' => array(
        'http://www.sample.tld/foo',
        'http://www.sample.tld:8080/foo',
        NULL
      ),
      'Invalid: Authentication needed' => array(
        'http://www.sample.tld/foo',
        'http://user:pass@www.sample.tld/foo',
        NULL
      ),
    );
  }

  public static function getRelativePathDataProvider() {
    return array(
      array(
        '',
        '/foo',
        'foo'
      ),
      array(
        '/foo',
        '/',
        './'
      ),
      array(
        '/foo',
        '/bar',
        'bar'
      ),
      array(
        '/foo/foo',
        '/foo/bar',
        'bar'
      ),
      array(
        '/foo/foo',
        '/bar/bar',
        '../bar/bar'
      ),
      array(
        '/foo/',
        '/bar/bar',
        '../bar/bar'
      ),
      array(
        '/papaya/topic.php',
        '/papaya-2.media.preview.2698dc5d16244caddcd3bb1992afa140.png',
        '../papaya-2.media.preview.2698dc5d16244caddcd3bb1992afa140.png',
      )
    );
  }
}
