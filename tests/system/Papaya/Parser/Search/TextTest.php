<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Parser\Search {
  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Parser\Search\Text
   */
  class TextTest extends \Papaya\TestFramework\TestCase {

    /**
     * @param array $expected
     * @param string $searchFor
     * @dataProvider provideSearchStrings
     */
    public function testParse($expected, $searchFor) {
      $tokens = new Text($searchFor);
      $this->assertEquals($expected, iterator_to_array($tokens));
    }

    public static function provideSearchStrings() {
      return [
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          'foo'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '+foo'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_EXCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '-foo'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'bar', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          'foo bar'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_EXCLUDE, 'value' => 'bar', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          'foo -bar'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_OPERATOR, 'value' => 'AND'],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'bar', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          'foo and bar'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_OPERATOR, 'value' => 'OR'],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'bar', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          'foo or bar'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo bar', 'quotes' => TRUE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '"foo bar"'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => '+foo -bar', 'quotes' => TRUE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '"+foo -bar"'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => '-foo +bar', 'quotes' => TRUE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '"-foo +bar"'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo"', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          'foo"'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo " bar', 'quotes' => TRUE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '"foo \\" bar"'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo \\ bar', 'quotes' => TRUE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '"foo \\\\ bar"'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 2],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 2],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '(foo)'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => '(foo)', 'quotes' => true],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '"(foo)"'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 2],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 2],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '(foo)('
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 2],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 2],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '(foo) and'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 2],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'bar', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 2],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          'foo(bar)'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 2],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => 'foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 2],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '(foo))))'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => '(foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '\\(foo'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => '+foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '\\+foo'
        ],
        [
          [
            ['mode' => Text::TOKEN_PARENTHESIS_START, 'value' => 1],
            ['mode' => Text::TOKEN_INCLUDE, 'value' => '-foo', 'quotes' => FALSE],
            ['mode' => Text::TOKEN_PARENTHESIS_END, 'value' => 1]
          ],
          '\\-foo'
        ],
      ];
    }
  }
}
