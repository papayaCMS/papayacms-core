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

namespace Papaya\Utility\Text;
require_once __DIR__.'/../../../../bootstrap.php';

class HyphenationTest extends \Papaya\TestCase {

  /**
   * @covers       \Papaya\Utility\Text\Hyphenation::german
   * @dataProvider provideGermanWords
   * @param string $expected
   * @param string $word
   */
  public function testGerman($expected, $word) {
    $this->assertEquals(
      $expected, Hyphenation::german($word)
    );
  }

  /********************************
   * Data Provider
   ********************************/

  public static function provideGermanWords() {
    return array(
      array('meis-tens', 'meistens'),
      array('Kis-ten', 'Kisten'),
      array('Es-pe', 'Espe'),
      array('Mas-ke', 'Maske'),
      array('Zu-cker', 'Zucker'),
      array('Quad-rat', 'Quadrat'),
      array('beo-bachten', 'beobachten')
    );
  }
}
