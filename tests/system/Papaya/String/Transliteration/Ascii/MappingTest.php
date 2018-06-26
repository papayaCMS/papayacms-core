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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaStringTransliterationAsciiMappingTest extends PapayaTestCase {

  /**
  * @covers PapayaStringTransliterationAsciiMapping::__construct
  */
  public function testConstructor() {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $this->assertFileExists($mapping->getFile(0, 'generic'));
  }

  /**
   * @covers PapayaStringTransliterationAsciiMapping::get
   * @dataProvider getMappedCharacterDataProvider
   * @param string $expected
   * @param string $codePoint
   * @param string $language
   */
  public function testGet($expected, $codePoint, $language) {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $actual = $mapping->get($codePoint, $language);
    $this->assertSame(
      $expected, $actual
    );
  }

  /**
  * @covers PapayaStringTransliterationAsciiMapping::clear
  */
  public function testClear() {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $mapping->lazyLoad(0, 'de');
    $mapping->clear();
    $this->assertFalse($mapping->isLoaded(0, 'de'));
  }

  /**
  * @covers PapayaStringTransliterationAsciiMapping::isLoaded
  */
  public function testIsLoadedBeforeLoading() {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $this->assertFalse($mapping->isLoaded(0, 'de'));
  }

  /**
  * @covers PapayaStringTransliterationAsciiMapping::isLoaded
  */
  public function testIsLoadedAfterLoading() {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $mapping->lazyLoad(0, 'de');
    $this->assertTrue($mapping->isLoaded(0, 'de'));
  }

  /**
  * @covers PapayaStringTransliterationAsciiMapping::lazyLoad
  * @covers PapayaStringTransliterationAsciiMapping::add
  * @covers PapayaStringTransliterationAsciiMapping::link
  */
  public function testLazyLoadLanguageSpecificMapping() {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $mapping->lazyLoad(0, 'de');
    $result = $this->readAttribute($mapping, '_mappingTables');
    $this->assertArrayHasKey('de', $result);
  }

  /**
  * @covers PapayaStringTransliterationAsciiMapping::lazyLoad
  * @covers PapayaStringTransliterationAsciiMapping::add
  * @covers PapayaStringTransliterationAsciiMapping::link
  */
  public function testLazyLoadFallbackToGeneric() {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $mapping->lazyLoad(5, 'de');
    $result = $this->readAttribute($mapping, '_mappingTables');
    $this->assertArrayHasKey('de', $result);
  }

  /**
  * @covers PapayaStringTransliterationAsciiMapping::lazyLoad
  * @covers PapayaStringTransliterationAsciiMapping::add
  * @covers PapayaStringTransliterationAsciiMapping::link
  */
  public function testLazyLoadGeneric() {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $mapping->lazyLoad(5, 'generic');
    $result = $this->readAttribute($mapping, '_mappingTables');
    $this->assertArrayHasKey('generic', $result);
  }

  /**
  * @covers PapayaStringTransliterationAsciiMapping::lazyLoad
  * @covers PapayaStringTransliterationAsciiMapping::add
  * @covers PapayaStringTransliterationAsciiMapping::link
  */
  public function testLazyLoadMultipleCall() {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $mapping->lazyLoad(5, 'de');
    $mapping->lazyLoad(5, 'generic');
    $result = $this->readAttribute($mapping, '_mappingTables');
    $this->assertEquals(
      $result['generic'], $result['de']
    );
  }

  /**
  * @covers PapayaStringTransliterationAsciiMapping::getFile
  */
  public function testGetFileGeneric() {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $this->assertStringEndsWith(
      'utf8/external/x00.php',
      $mapping->getFile(0, 'generic')
    );
  }

  /**
  * @covers PapayaStringTransliterationAsciiMapping::getFile
  */
  public function testGetFileGerman() {
    $mapping = new PapayaStringTransliterationAsciiMapping();
    $this->assertStringEndsWith(
      'utf8/external/de/x00.php',
      $mapping->getFile(0, 'de')
    );
  }

  public static function getMappedCharacterDataProvider() {
    return array(
      array('a', 228, 'generic'), // LATIN SMALL LETTER A WITH DIAERESIS
      array('ae', 228, 'de'), // LATIN SMALL LETTER A WITH DIAERESIS
      array('ae', 228, 'de-DE'), // LATIN SMALL LETTER A WITH DIAERESIS
      array('a', 97, 'de'), // LATIN SMALL LETTER A
      array('r', 1088, 'de'), // CYRILLIC SMALL LETTER ER
      array('', 0, 'generic'), // NULL
      array(NULL, 999999, 'generic'), // invalid codepoint
    );
  }
}
