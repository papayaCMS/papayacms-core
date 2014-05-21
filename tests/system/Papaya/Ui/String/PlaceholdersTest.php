<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiStringPlaceholdersTest extends PapayaTestCase {

  /**
   * @covers PapayaUiStringPlaceholders
   * @dataProvider providePlaceholderExamples
   * @param $expected
   * @param $string
   * @param $values
   */
  public function testPlaceholdersToString($expected, $string, $values = array()) {
    $string = new PapayaUiStringPlaceholders($string, $values);
    $this->assertEquals($expected, (string)$string);
  }

  public static function providePlaceholderExamples() {
    return array(
      array('Test', 'Test'),
      array('Test', 'Test', array('a' => 'b')),
      array('Hello World!', 'Hello {target}!', array('target' => 'World')),
      array('Hello !', 'Hello {target}!', array('some' => 'World')),
      array('Hello World!', 'Hello {target}!', array('some' => 'foo', 'target' => 'World')),
    );
  }
}