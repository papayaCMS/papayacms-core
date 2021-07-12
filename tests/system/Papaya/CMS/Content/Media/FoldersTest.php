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

namespace Papaya\CMS\Content\Media;

use Papaya\Database\Statement;

require_once __DIR__.'/../../../../../bootstrap.php';

class FoldersTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Media\Folders::_createMapping
   */
  public function testCreateMapping() {
    $records = new Folders();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $records->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromFieldToProperty));
  }

  /**
   * @covers \Papaya\CMS\Content\Media\Folders
   */
  public function testCallbackMapValueFromFieldToProperty() {
    $records = new Folders();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $records->mapping();
    $this->assertEquals(
      23,
      $mapping->callbacks()->onMapValueFromFieldToProperty(
        'id', 'folder', '23'
      )
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Media\Folders
   */
  public function testCallbackMapValueFromFieldToPropertyDecodesAncestors() {
    $records = new Folders();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $records->mapping();
    $this->assertEquals(
      array(21, 42),
      $mapping->callbacks()->onMapValueFromFieldToProperty(
        'ancestors', 'parent_path', ';21;42;'
      )
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Media\Folders
   */
  public function testCallbackGetFieldForPropertyUnknownPropertyExpectingNull() {
    $records = new Folders();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $records->mapping();
    $this->assertNull(
      $mapping->getField(
        'unknown_property_name'
      )
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Media\Folders::load
   */
  public function testLoad() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'folder_id' => 1,
            'parent_id' => 0,
            'parent_path' => ';0;',
            'lng_id' => 1,
            'folder_name' => 'One'
          ),
          array(
            'folder_id' => 2,
            'parent_id' => 0,
            'parent_path' => ';0;',
            'lng_id' => 1,
            'folder_name' => 'Two'
          ),
          array(
            'folder_id' => 3,
            'parent_id' => 1,
            'parent_path' => ';1;',
            'lng_id' => 1,
            'folder_name' => 'Tree'
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isInstanceOf(Statement::class), $this->isType('array')
      )
      ->willReturn($databaseResult);

    $records = new Folders();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load(array('language_id' => 1)));

    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'parent_id' => 0,
          'ancestors' => array(0),
          'language_id' => 1,
          'title' => 'One'
        ),
        3 => array(
          'id' => 3,
          'parent_id' => 1,
          'ancestors' => array(1),
          'language_id' => 1,
          'title' => 'Tree'
        ),
        2 => array(
          'id' => 2,
          'parent_id' => 0,
          'ancestors' => array(0),
          'language_id' => 1,
          'title' => 'Two'
        )
      ),
      iterator_to_array(
        new \RecursiveIteratorIterator($records, \RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }


  /**
   * @covers \Papaya\CMS\Content\Media\Folders::load
   */
  public function testLoadwithoutLanguageIdExpectingNoTranslations() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'folder_id' => 1,
            'parent_id' => 0,
            'parent_path' => ';0;',
            'lng_id' => NULL,
            'folder_name' => NULL
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isInstanceOf(Statement::class), $this->isType('array')
      )
      ->willReturn($databaseResult);

    $records = new Folders();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load());

    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'parent_id' => 0,
          'ancestors' => array(0),
          'language_id' => NULL,
          'title' => NULL
        )
      ),
      iterator_to_array(
        new \RecursiveIteratorIterator($records, \RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }
}
