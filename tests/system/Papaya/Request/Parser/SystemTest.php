<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaRequestParserSystemTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserSystem::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock('PapayaUrl', array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserSystem();
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function parseDataProvider() {
    return array(
      array(
        '',
        FALSE
      ),
      array(
        '/index.urls',
        array(
          'mode' => 'urls',
        )
      ),
      array(
        '/index.status',
        array(
          'mode' => 'status',
        )
      )
    );
  }
}

