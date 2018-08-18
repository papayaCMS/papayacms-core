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

namespace Papaya\Text\Transliteration\ASCII;
require_once __DIR__.'/../../../../../bootstrap.php';

class MappingTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::__construct
   */
  public function testConstructor() {
    $mapping = new Mapping();
    $this->assertFileExists($mapping->getFile(0, 'generic'));
  }

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::get
   * @dataProvider getMappedCharacterDataProvider
   * @param string $expected
   * @param string $codePoint
   * @param string $language
   */
  public function testGet($expected, $codePoint, $language) {
    $mapping = new Mapping();
    $actual = $mapping->get($codePoint, $language);
    $this->assertSame(
      $expected, $actual
    );
  }

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::clear
   */
  public function testClear() {
    $mapping = new Mapping();
    $mapping->lazyLoad(0, 'de');
    $mapping->clear();
    $this->assertFalse($mapping->isLoaded(0, 'de'));
  }

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::isLoaded
   */
  public function testIsLoadedBeforeLoading() {
    $mapping = new Mapping();
    $this->assertFalse($mapping->isLoaded(0, 'de'));
  }

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::isLoaded
   */
  public function testIsLoadedAfterLoading() {
    $mapping = new Mapping();
    $mapping->lazyLoad(0, 'de');
    $this->assertTrue($mapping->isLoaded(0, 'de'));
  }

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::lazyLoad
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::add
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::link
   */
  public function testLazyLoadLanguageSpecificMapping() {
    $mapping = new Mapping();
    $mapping->lazyLoad(0, 'de');
    $result = $this->readAttribute($mapping, '_mappingTables');
    $this->assertArrayHasKey('de', $result);
  }

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::lazyLoad
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::add
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::link
   */
  public function testLazyLoadFallbackToGeneric() {
    $mapping = new Mapping();
    $mapping->lazyLoad(5, 'de');
    $result = $this->readAttribute($mapping, '_mappingTables');
    $this->assertArrayHasKey('de', $result);
  }

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::lazyLoad
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::add
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::link
   */
  public function testLazyLoadGeneric() {
    $mapping = new Mapping();
    $mapping->lazyLoad(5, 'generic');
    $result = $this->readAttribute($mapping, '_mappingTables');
    $this->assertArrayHasKey('generic', $result);
  }

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::lazyLoad
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::add
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::link
   */
  public function testLazyLoadMultipleCall() {
    $mapping = new Mapping();
    $mapping->lazyLoad(5, 'de');
    $mapping->lazyLoad(5, 'generic');
    $result = $this->readAttribute($mapping, '_mappingTables');
    $this->assertEquals(
      $result['generic'], $result['de']
    );
  }

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::getFile
   */
  public function testGetFileGeneric() {
    $mapping = new Mapping();
    $this->assertStringEndsWith(
      'utf8/external/x00.php',
      $mapping->getFile(0, 'generic')
    );
  }

  /**
   * @covers \Papaya\Text\Transliteration\ASCII\Mapping::getFile
   */
  public function testGetFileGerman() {
    $mapping = new Mapping();
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
