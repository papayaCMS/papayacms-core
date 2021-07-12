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

namespace Papaya\CMS\Administration\Languages;

require_once __DIR__.'/../../../../../bootstrap.php';

class ImageTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Languages\Image
   */
  public function testConstructor() {
    $image = new Image();
    $this->assertEquals(0, $image->getLanguageID());
  }

  /**
   * @covers \Papaya\CMS\Administration\Languages\Image
   */
  public function testConstructorWithLanguageId() {
    $image = new Image(21);
    $this->assertEquals(21, $image->getLanguageID());
  }

  /**
   * @covers \Papaya\CMS\Administration\Languages\Image
   */
  public function testToStringWithoutLanguageInformationExpectingEmptyString() {
    $image = new Image();
    $this->assertEquals('', (string)$image);
  }

  /**
   * @covers \Papaya\CMS\Administration\Languages\Image
   */
  public function testToStringFetchingCurrentLanguage() {
    $switch = $this->createMock(Selector::class);
    $switch
      ->expects($this->once())
      ->method('getCurrent')
      ->will($this->returnValue(array('image' => 'sample.png')));
    $image = new Image();
    $image->papaya(
      $this->mockPapaya()->application(array('administrationLanguage' => $switch))
    );
    $this->assertEquals('./pics/language/sample.png', (string)$image);
  }

  /**
   * @covers \Papaya\CMS\Administration\Languages\Image
   */
  public function testToStringFetchingDefinedLanguage() {
    $languages = $this->createMock(\Papaya\CMS\Content\Languages::class);
    $languages
      ->expects($this->once())
      ->method('getLanguage')
      ->with(42)
      ->will($this->returnValue(array('image' => 'sample.png')));

    $switch = $this->createMock(Selector::class);
    $switch
      ->expects($this->once())
      ->method('languages')
      ->will($this->returnValue($languages));
    $image = new Image(42);
    $image->papaya(
      $this->mockPapaya()->application(array('administrationLanguage' => $switch))
    );
    $this->assertEquals('./pics/language/sample.png', (string)$image);
  }

  /**
   * @covers \Papaya\CMS\Administration\Languages\Image
   */
  public function testToStringWithNonExistingLanguageExpectingEmptyString() {
    $languages = $this->createMock(\Papaya\CMS\Content\Languages::class);
    $languages
      ->expects($this->once())
      ->method('getLanguage')
      ->with(23)
      ->will($this->returnValue(NULL));

    $switch = $this->createMock(Selector::class);
    $switch
      ->expects($this->once())
      ->method('languages')
      ->will($this->returnValue($languages));
    $image = new Image(23);
    $image->papaya(
      $this->mockPapaya()->application(array('administrationLanguage' => $switch))
    );
    $this->assertEquals('', (string)$image);
  }
}
