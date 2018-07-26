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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaStringTransliterationAsciiTest extends PapayaTestCase {

  /**
   * @covers \PapayaStringTransliterationAscii::transliterate
   * @covers \PapayaStringTransliterationAscii::mapCharacterMatch
   * @dataProvider provideTransliterationExamples
   * @param string $expected
   * @param string $string
   * @param string $language
   */
  public function testTransliterate($expected, $string, $language) {
    $transliterator = new \PapayaStringTransliterationAscii();
    $this->assertEquals(
      $expected, $transliterator->transliterate($string, $language)
    );
  }

  /**
  * @covers \PapayaStringTransliterationAscii::mapping
  */
  public function testMappingGetAfterSet() {
    $mapping = $this->createMock(PapayaStringTransliterationAsciiMapping::class);
    $transliteratorOne = new \PapayaStringTransliterationAscii();
    $transliteratorTwo = new \PapayaStringTransliterationAscii();
    $transliteratorOne->mapping($mapping);
    $this->assertSame($mapping, $transliteratorTwo->mapping());
  }

  /**
  * @covers \PapayaStringTransliterationAscii::mapping
  * @covers \PapayaStringTransliterationAscii::resetMapping
  */
  public function testMappingImplicitCreate() {
    $transliterator = new \PapayaStringTransliterationAscii();
    $mappingOne = $transliterator->mapping();
    $transliterator->resetMapping();
    $mappingTwo = $transliterator->mapping();
    $this->assertInstanceOf(PapayaStringTransliterationAsciiMapping::class, $mappingTwo);
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
