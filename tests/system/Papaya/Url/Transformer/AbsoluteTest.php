<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUrlTransformerAbsoluteTest extends PapayaTestCase {

  /**
  * get mock for PapayaUrl from url string
  * @param string $url
  * @return PapayaUrlTransformerAbsolute
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
    $urlObject = $this->getMock(
      'PapayaUrl', array_merge(array('getHostUrl'), array_keys($mapping))
    );
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
    $urlObject
      ->expects($this->any())
      ->method('getHostUrl')
      ->will($this->returnValue('http://www.example.com'));
    return $urlObject;
  }

  /**
  * @covers PapayaUrlTransformerAbsolute::transform
  * @covers PapayaUrlTransformerAbsolute::_calculateRealPath
  * @dataProvider transformDataProvider
  */
  public function testTransform($currentUrl, $targetPath, $expected) {
    $transformer = new PapayaUrlTransformerAbsolute();
    $this->assertSame(
      $expected,
      $transformer->transform(
        $this->getPapayaUrlMockFixture($currentUrl),
        $targetPath
      )
    );
  }

  /*************************************
  * Data Providers
  *************************************/

  public static function transformDataProvider() {
    return array(
      'Valid: Full path files' => array(
        'http://www.example.com/a/loc/ation.html',
        '/some/other/location.html',
        'http://www.example.com/some/other/location.html'
      ),
      'Valid: Full path folders' => array(
        'http://www.example.com/a/loc/ation/',
        '/some/other/',
        'http://www.example.com/some/other/'
      ),
      'Valid: Full path file to folder' => array(
        'http://www.example.com/a/loc/ation.html',
        '/some/other/',
        'http://www.example.com/some/other/'
      ),
      'Valid: Full path folder to file' => array(
        'http://www.example.com/a/loc/ation/',
        '/some/other/location.html',
        'http://www.example.com/some/other/location.html'
      ),
      'Valid: Relative path from folder' => array(
        'http://www.example.com/a/loc/ation/',
        '../../some/other/location.html',
        'http://www.example.com/a/some/other/location.html'
      ),
      'Valid: Relative path from file' => array(
        'http://www.example.com/a/loc/ation/test.html',
        '../../some/./../other/location.html',
        'http://www.example.com/a/other/location.html'
      ),
      'Valid: .. overflow' => array(
        'http://www.example.com/a/location/',
        '../../../../test.html',
        'http://www.example.com/test.html'
      ),
      'Valid: some //es' => array(
        'http://www.example.com/a/location/',
        '../../my/path//is///here//here//../test.html',
        'http://www.example.com/my/path/is/here/test.html'
      ),
      'Valid: another .. test' => array(
        'http://www.example.com/',
        '/this/is//a/../an/example/path/just/to/.././to/../test/some/stuff',
        'http://www.example.com/this/is/an/example/path/just/test/some/stuff'
      ),
      'Valid: full url' => array(
        'http://www.example.com/',
        'http://www.test.tld/',
        'http://www.test.tld/'
      ),
      'Valid: once up to host' => array(
        'http://www.example.com/path/file.html',
        '../',
        'http://www.example.com/'
      ),
      'Valid: several up to host' => array(
        'http://www.example.com/path/subpath/file.html',
        '/',
        'http://www.example.com/'
      ),
    );
  }
}
