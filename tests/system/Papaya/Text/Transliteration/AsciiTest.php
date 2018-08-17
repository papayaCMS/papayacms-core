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

namespace Papaya\Text\Transliteration;
require_once __DIR__.'/../../../../bootstrap.php';

class AsciiTest extends \PapayaTestCase {

  /**
   * @covers       \Papaya\Text\Transliteration\Ascii::transliterate
   * @covers       \Papaya\Text\Transliteration\Ascii::mapCharacterMatch
   * @dataProvider provideTransliterationExamples
   * @param string $expected
   * @param string $string
   * @param string $language
   */
  public function testTransliterate($expected, $string, $language) {
    $transliterator = new Ascii();
    $this->assertEquals(
      $expected, $transliterator->transliterate($string, $language)
    );
  }

  /**
   * @covers \Papaya\Text\Transliteration\Ascii::mapping
   */
  public function testMappingGetAfterSet() {
    $mapping = $this->createMock(Ascii\Mapping::class);
    $transliteratorOne = new Ascii();
    $transliteratorTwo = new Ascii();
    $transliteratorOne->mapping($mapping);
    $this->assertSame($mapping, $transliteratorTwo->mapping());
  }

  /**
   * @covers \Papaya\Text\Transliteration\Ascii::mapping
   * @covers \Papaya\Text\Transliteration\Ascii::resetMapping
   */
  public function testMappingImplicitCreate() {
    $transliterator = new Ascii();
    $mappingOne = $transliterator->mapping();
    $transliterator->resetMapping();
    $mappingTwo = $transliterator->mapping();
    $this->assertInstanceOf(Ascii\Mapping::class, $mappingTwo);
    $this->assertNotSame($mappingOne, $mappingTwo);
  }

  public static function provideTransliterationExamples() {
    return array(
      'ascii chars' => array('abcd', 'abcd', ''),
      'umlaut generic' => array('aAoOuU', 'äÄöÖüÜ', ''),
      'umlaut english' => array('aAoOuU', 'äÄöÖüÜ', 'en'),
      'specific german' => array('aeAeoeOeueUe', 'äÄöÖüÜ', 'de'),
      'cyrillic' => array('Russkii', 'Русский', ''),
      'symbol registered' => array('(r)', '®', ''),
      'symbol jing jang' => array(' ', '☯', ''),
      'symbol love' => array('[?]', '♥', '')
    );
  }
}
