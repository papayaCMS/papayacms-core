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

namespace Papaya\Administration\Languages;

require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @covers \Papaya\Administration\Languages\Caption
 */
class CaptionTest extends \Papaya\TestCase {

  /**
   * @dataProvider provideSampleWithoutLanguageSwitch
   * @param string $expected
   * @param string $suffix
   * @param string $separator
   */
  public function testToStringWithoutAdministrationLanguage($expected, $suffix, $separator) {
    $caption = new Caption($suffix, $separator);
    $this->assertEquals($expected, (string)$caption);
  }

  /**
   * @dataProvider provideSampleWithLanguageSwitch
   * @param string $expected
   * @param array $language
   * @param string $suffix
   * @param string $separator
   */
  public function testToStringWithAdministrationLanguage($expected, $language, $suffix, $separator) {
    $switch = $this->createMock(Selector::class);
    $switch
      ->expects($this->once())
      ->method('getCurrent')
      ->willReturn($language);

    $caption = new Caption($suffix, $separator);
    $caption->papaya(
      $this->mockPapaya()->application(['administrationLanguage' => $switch])
    );
    $this->assertEquals($expected, (string)$caption);
  }

  public static function provideSampleWithoutLanguageSwitch(): array {
    return [
      ['', '', ' - '],
      ['Foo', 'Foo', ' - '],
      ['Foo', 'Foo', ' > '],
      ['Foo', new \Papaya\UI\Text('Foo'), ' '],
    ];
  }

  public static function provideSampleWithLanguageSwitch(): array {
    return [
      ['', NULL, '', ' - '],
      ['Foo', NULL, 'Foo', ' - '],
      ['Foo', NULL, 'Foo', ' > '],
      ['Foo', NULL, new \Papaya\UI\Text('Foo'), ''],
      ['English', ['title' => 'English'], '', ' - '],
      ['English - Foo', ['title' => 'English'], 'Foo', ' - '],
      ['English > Foo', ['title' => 'English'], 'Foo', ' > '],
      ['English Foo', ['title' => 'English'], new \Papaya\UI\Text('Foo'), ' '],
    ];
  }
}
