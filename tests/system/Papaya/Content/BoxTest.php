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

use Papaya\Content\Box\Translations;
use Papaya\Content\Box;
use Papaya\Content\Options;
use Papaya\Database\Result;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaContentBoxTest extends PapayaTestCase {

  /**
  * @covers Box::load
  */
  public function testLoad() {
    $translations = $this->createMock(Translations::class);
    $translations
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo(42));
    $record = array(
      'box_id' => 42,
      'boxgroup_id' => 21,
      'box_name' => 'Box Name',
      'box_created' => 1,
      'box_modified' => 2,
      'box_deliverymode' => Box::DELIVERY_MODE_STATIC,
      'box_cachemode' => Options::CACHE_SYSTEM,
      'box_cachetime' => 0,
      'box_expiresmode' => Options::CACHE_SYSTEM,
      'box_expirestime' => 0,
      'box_unpublished_languages' => 0
    );
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(Result::FETCH_ASSOC)
      ->will($this->returnValue($record));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_box', 42))
      ->will($this->returnValue($databaseResult));
    $box = new \PapayaContentBox_TestProxy();
    $box->setDatabaseAccess($databaseAccess);
    $box->translations($translations);
    $this->assertTrue(
      $box->load(42)
    );
    $this->assertAttributeEquals(
      array(
        'id' => 42,
        'group_id' => 21,
        'name' => 'Box Name',
        'created' => 1,
        'modified' => 2,
        'delivery_mode' => Box::DELIVERY_MODE_STATIC,
        'cache_mode' => Options::CACHE_SYSTEM,
        'cache_time' => 0,
        'expires_mode' => Options::CACHE_SYSTEM,
        'expires_time' => 0,
        'unpublished_translations' => 0
      ),
      '_values',
      $box
    );
  }

  /**
  * @covers Box::load
  */
  public function testLoadFailedExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_box', 42))
      ->will($this->returnValue(FALSE));
    $box = new \PapayaContentBox_TestProxy();
    $box->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $box->load(42)
    );
  }

  /**
  * @covers Box::translations
  */
  public function testTranslationsSet() {
    $translations = $this->createMock(Translations::class);
    $box = new \PapayaContentBox_TestProxy();
    $box->translations($translations);
    $this->assertAttributeSame(
      $translations, '_translations', $box
    );
  }

  /**
  * @covers Box::translations
  */
  public function testTranslationsGetAfterSet() {
    $translations = $this->createMock(Translations::class);
    $box = new \PapayaContentBox_TestProxy();
    $box->translations($translations);
    $this->assertSame(
      $translations, $box->translations()
    );
  }

  /**
  * @covers Box::translations
  */
  public function testTranslationsGetImplicitCreate() {
    $box = new \PapayaContentBox_TestProxy();
    $this->assertInstanceOf(
      Translations::class, $box->translations()
    );
  }
}

class PapayaContentBox_TestProxy extends Box {

}
