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

use Papaya\Content\Media\Folders;
use Papaya\Database\Result;
use Papaya\Database\Record\Mapping;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentMediaFoldersTest extends \PapayaTestCase {

  /**
   * @covers Folders::_createMapping
   */
  public function testCreateMapping() {
    $records = new Folders();
    /** @var Mapping $mapping */
    $mapping = $records->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromFieldToProperty));
    $this->assertTrue(isset($mapping->callbacks()->onGetFieldForProperty));
  }

  /**
   * @covers Folders::callbackMapValueFromFieldToProperty
   */
  public function testCallbackMapValueFromFieldToProperty() {
    $records = new Folders();
    $this->assertEquals(
      23,
      $records->callbackMapValueFromFieldToProperty(
        new stdClass(), 'id', 'folder', '23'
      )
    );
  }

  /**
   * @covers Folders::callbackMapValueFromFieldToProperty
   */
  public function testCallbackMapValueFromFieldToPropertyDecodesAncestors() {
    $records = new Folders();
    $this->assertEquals(
      array(21, 42),
      $records->callbackMapValueFromFieldToProperty(
        new stdClass(), 'ancestors', 'parent_path', ';21;42;'
      )
    );
  }

  /**
   * @covers Folders::callbackGetFieldForProperty
   */
  public function testCallbackGetFieldForPropertyUnknownPropertyExpectingNull() {
    $records = new Folders();
    $this->assertNull(
      $records->callbackGetFieldForProperty(
        new stdClass(), 'unknown_property_name'
      )
    );
  }

  /**
   * @covers Folders::callbackGetFieldForProperty
   * @dataProvider providePropertyToFieldValues
   * @param string $expected
   * @param string $property
   */
  public function testCallbackGetFieldForProperty($expected, $property) {
    $records = new Folders();
    $this->assertEquals(
      $expected, $records->callbackGetFieldForProperty(new stdClass, $property)
    );
  }

  public static function providePropertyToFieldValues() {
    return array(
      array('f.folder_id', 'id'),
      array('f.parent_id', 'parent_id'),
      array('f.parent_path', 'ancestors'),
      array('ft.lng_id', 'language_id'),
      array('ft.folder_name', 'title')
    );
  }

  /**
   * @covers Folders::load
   */
  public function testLoad() {
    $databaseResult = $this->createMock(Result::class);
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
        $this->isType('string'),
        array('table_mediadb_folders', 'table_mediadb_folders_trans', 1)
      )
      ->will($this->returnValue($databaseResult));

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
        new RecursiveIteratorIterator($records, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }


  /**
   * @covers Folders::load
   */
  public function testLoadwithoutLanguageIdExpectingNoTranslations() {
    $databaseResult = $this->createMock(Result::class);
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
        $this->isType('string'),
        array('table_mediadb_folders', 'table_mediadb_folders_trans', 0)
      )
      ->will($this->returnValue($databaseResult));

    $records = new Folders();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load());

    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'parent_id' => 0,
          'ancestors' => array(0),
          'language_id' =>  NULL,
          'title' => NULL
        )
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($records, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }
}
