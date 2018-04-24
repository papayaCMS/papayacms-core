<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaRequestParserWrapperTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserWrapper::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock('PapayaUrl', array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserWrapper();
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
        '/css.php',
        array(
          'mode' => '.theme-wrapper',
          'output_mode' => 'css'
        )
      ),
      array(
        '/css',
        array(
          'mode' => '.theme-wrapper',
          'output_mode' => 'css'
        )
      ),
      array(
        '/js.php',
        array(
          'mode' => '.theme-wrapper',
          'output_mode' => 'js'
        )
      ),
      array(
        '/js',
        array(
          'mode' => '.theme-wrapper',
          'output_mode' => 'js'
        )
      )
    );
  }
}

