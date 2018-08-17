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

namespace Papaya\UI\Text;
require_once __DIR__.'/../../../../bootstrap.php';

class PlaceholdersTest extends \PapayaTestCase {

  /**
   * @covers       \Papaya\UI\Text\Placeholders
   * @dataProvider providePlaceholderExamples
   * @param string $expected
   * @param string $string
   * @param array $values
   */
  public function testPlaceholdersToString($expected, $string, array $values = array()) {
    $result = new Placeholders($string, $values);
    $this->assertEquals($expected, (string)$result);
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
