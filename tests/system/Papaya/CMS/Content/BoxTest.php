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

namespace Papaya\CMS\Content {

  use Papaya\Database\Interfaces\Key;

  require_once __DIR__.'/../../../../bootstrap.php';

  class BoxTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\CMS\Content\Box::load
     */
    public function testLoad() {
      $translations = $this->createMock(Box\Translations::class);
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
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchRow')
        ->with(\Papaya\Database\Result::FETCH_ASSOC)
        ->will($this->returnValue($record));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('table_box', 42))
        ->will($this->returnValue($databaseResult));
      $box = new Box_TestProxy();
      $box->setDatabaseAccess($databaseAccess);
      $box->translations($translations);
      $this->assertTrue(
        $box->load(42)
      );
      $this->assertEquals(
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
        iterator_to_array($box)
      );
    }

    /**
     * @covers \Papaya\CMS\Content\Box::load
     */
    public function testLoadFailedExpectingFalse() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('table_box', 42))
        ->will($this->returnValue(FALSE));
      $box = new Box_TestProxy();
      $box->setDatabaseAccess($databaseAccess);
      $this->assertFalse(
        $box->load(42)
      );
    }

    /**
     * @covers \Papaya\CMS\Content\Box::translations
     */
    public function testTranslationsGetAfterSet() {
      $translations = $this->createMock(Box\Translations::class);
      $box = new Box_TestProxy();
      $box->translations($translations);
      $this->assertSame(
        $translations, $box->translations()
      );
    }

    /**
     * @covers \Papaya\CMS\Content\Box::translations
     */
    public function testTranslationsGetImplicitCreate() {
      $box = new Box_TestProxy();
      $this->assertInstanceOf(
        Box\Translations::class, $box->translations()
      );
    }
  }

  class Box_TestProxy extends Box {

    public function key(Key $key = NULL) {
    }
  }
}
