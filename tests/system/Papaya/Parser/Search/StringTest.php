<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaParserSearchStringTest extends PapayaTestCase {

  /**
   * @covers PapayaParserSearchStringTest
   * @param array $expected
   * @param string $searchFor
   * @dataProvider provideSearchStrings
   */
  public function testParse($expected, $searchFor) {
    $tokens = new PapayaParserSearchString($searchFor);
    $this->assertEquals($expected, iterator_to_array($tokens));
  }

  public static function provideSearchStrings() {
    return [
      [
        [
          ['mode' => '(', 'value' => 1],
          ['mode' => '+', 'value' => 'foo', 'quotes' => FALSE],
          ['mode' => ')', 'value' => 1]
        ],
        'foo'
      ],
      [
        [
          ['mode' => '(', 'value' => 1],
          ['mode' => '+', 'value' => 'foo', 'quotes' => FALSE],
          ['mode' => '+', 'value' => 'bar', 'quotes' => FALSE],
          ['mode' => ')', 'value' => 1]
        ],
        'foo bar'
      ],
      [
        [
          ['mode' => '(', 'value' => 1],
          ['mode' => '+', 'value' => 'foo', 'quotes' => FALSE],
          ['mode' => '-', 'value' => 'bar', 'quotes' => FALSE],
          ['mode' => ')', 'value' => 1]
        ],
        'foo -bar'
      ],
      [
        [
          ['mode' => '(', 'value' => 1],
          ['mode' => '+', 'value' => 'foo bar', 'quotes' => TRUE],
          ['mode' => ')', 'value' => 1]
        ],
        '"foo bar"'
      ],
    ];
  }

}
