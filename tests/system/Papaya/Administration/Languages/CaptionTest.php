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

use Papaya\Administration\Languages\Caption;
use Papaya\Administration\Languages\Selector;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaAdministrationLanguagesCaptionTest extends \PapayaTestCase {

  /**
  * @covers Caption
  */
  public function testConstructor() {
    $caption = new Caption();
    $this->assertEquals('', (string)$caption);
  }

  /**
  * @covers Caption
  */
  public function testConstructorWithString() {
    $caption = new Caption('Suffix string');
    $this->assertAttributeEquals('Suffix string', '_suffix', $caption);
  }

  /**
  * @covers Caption
  */
  public function testConstructorWithStringAndSeparator() {
    $caption = new Caption('Suffix string', '|');
    $this->assertAttributeEquals('|', '_separator', $caption);
  }

  /**
   * @covers       Caption
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
   * @covers       Caption
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
      ->will($this->returnValue($language));

    $caption = new Caption($suffix, $separator);
    $caption->papaya(
      $this->mockPapaya()->application(array('administrationLanguage' => $switch))
    );
    $this->assertEquals($expected, (string)$caption);
  }

  public static function provideSampleWithoutLanguageSwitch() {
    return array(
      array('', '', ' - '),
      array('Foo', 'Foo', ' - '),
      array('Foo', 'Foo', ' > '),
      array('Foo', new \Papaya\Ui\Text('Foo'), ' '),
    );
  }

  public static function provideSampleWithLanguageSwitch() {
    return array(
      array('', NULL, '', ' - '),
      array('Foo', NULL, 'Foo', ' - '),
      array('Foo', NULL, 'Foo', ' > '),
      array('Foo', NULL, new \Papaya\Ui\Text('Foo'), ''),
      array('English', array('title' => 'English'), '', ' - '),
      array('English - Foo', array('title' => 'English'), 'Foo', ' - '),
      array('English > Foo', array('title' => 'English'), 'Foo', ' > '),
      array('English Foo', array('title' => 'English'), new \Papaya\Ui\Text('Foo'), ' '),
    );
  }
}
