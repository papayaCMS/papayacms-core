<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaRequestParserSessionTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserSession::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock(PapayaUrl::class, array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserSession();
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
  }

  /**
  * @covers PapayaRequestParserSession::isLast
  */
  public function testIsLast() {
    $parser = new PapayaRequestParserSession();
    $this->assertFalse($parser->isLast());
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function parseDataProvider() {
    return array(
      array(
        '/index.html',
        FALSE
      ),
      array(
        '/sid01234567890123456789012345678901/index.html',
        array(
          'session' => 'sid01234567890123456789012345678901'
        )
      ),
      array(
        '/sid01234567890123456789012345678901/',
        array(
          'session' => 'sid01234567890123456789012345678901'
        )
      ),
      array(
        '/sidadmin01234567890123456789012345678901/',
        array(
          'session' => 'sidadmin01234567890123456789012345678901'
        )
      ),
    );
  }
}

