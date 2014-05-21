<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiReferenceFactoryTest extends PapayaTestCase {

  /**
   * @covers PapayaUiReferenceFactory
   * @dataProvider provideStringsAndExpectedUrls
   */
  public function testByString($expected, $string) {
    $factory = new PapayaUiReferenceFactory();
    $factory->papaya($this->mockPapaya()->application());
    $reference = $factory->byString($string);
    $this->assertEquals($expected, (string)$reference);
  }

  /*******************************
   * Data Provider
   ******************************/

  public static function provideStringsAndExpectedUrls() {
    return array(
      array('http://www.test.tld/test.html', ''),
      array('http://www.papaya-cms.com', 'http://www.papaya-cms.com'),
      array('http://www.test.tld/foo/bar', '/foo/bar'),
      array('http://www.test.tld/foo/bar', 'foo/bar'),
      array('http://www.test.tld/index.42.html', '42'),
      array('http://www.test.tld/index.21.42.html', '21.42'),
      array('http://www.test.tld/index.21.42.en.html', '21.42.en'),
      array('http://www.test.tld/index.21.42.en.atom', '21.42.en.atom'),
      array('http://www.test.tld/21.42.en.atom?foo=bar', '/21.42.en.atom?foo=bar')
    );
  }
}