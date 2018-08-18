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

namespace Papaya\Content\Box;

require_once __DIR__.'/../../../../bootstrap.php';

class TranslationTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Content\Box\Translation::load
   * @covers \Papaya\Content\Box\Translation::convertBoxRecordToValues
   */
  public function testLoad() {
    $record = array(
      'box_id' => '42',
      'lng_id' => '1',
      'box_title' => 'translated box title',
      'box_data' => '',
      'box_trans_created' => '123',
      'box_trans_modified' => '456',
      'view_id' => '21',
      'view_title' => 'view title',
      'module_guid' => '123456789012345678901234567890ab',
      'module_title' => 'module title'
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
      ->with($this->isType('string'), array('table_box_trans', 'table_views', 'table_modules', 42, 1))
      ->will($this->returnValue($databaseResult));
    $translation = new Translation();
    $translation->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $translation->load(array(42, 1))
    );
    $this->assertAttributeEquals(
      array(
        'box_id' => 42,
        'language_id' => 1,
        'title' => 'translated box title',
        'created' => 123,
        'modified' => 456,
        'view_id' => 21,
        'view_title' => 'view title',
        'module_guid' => '123456789012345678901234567890ab',
        'module_title' => 'module title',
        'content' => array()
      ),
      '_values',
      $translation
    );
  }

  /**
   * @covers \Papaya\Content\Box\Translation::save
   * @covers \Papaya\Content\Box\Translation::_insert
   */
  public function testSaveCreateNew() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('table_box_trans', 42, 21)))
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->will($this->returnCallback(array($this, 'checkInsertData')));
    $translation = new Translation();
    $translation->setDatabaseAccess($databaseAccess);
    $translation->assign(
      array(
        'box_id' => 42,
        'language_id' => 21,
        'title' => 'box title',
        'content' => array('foo' => 'bar'),
        'view_id' => 23
      )
    );
    $this->assertTrue($translation->save());
  }

  public function checkInsertData($table, $idField, array $data) {
    $this->assertEquals('table_box_trans', $table);
    $this->assertNull($idField);
    $this->assertEquals(42, $data['box_id']);
    $this->assertEquals(21, $data['lng_id']);
    $this->assertEquals('box title', $data['box_title']);
    $this->assertEquals(
    /** @lang XML */
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $data['box_data']
    );
    $this->assertEquals(23, $data['view_id']);
    return TRUE;
  }

  /**
   * @covers \Papaya\Content\Box\Translation::save
   * @covers \Papaya\Content\Box\Translation::_update
   */
  public function testSaveUpdateExisting() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('table_box_trans', 42, 21)))
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->will($this->returnCallback(array($this, 'checkUpdateData')));
    $translation = new Translation();
    $translation->setDatabaseAccess($databaseAccess);
    $translation->assign(
      array(
        'box_id' => 42,
        'language_id' => 21,
        'title' => 'box title',
        'content' => array('foo' => 'bar'),
        'view_id' => 23
      )
    );
    $this->assertTrue($translation->save());
  }

  public function checkUpdateData($table, $data, $filter) {
    $this->assertEquals('table_box_trans', $table);
    $this->assertEquals('box title', $data['box_title']);
    $this->assertEquals(
    /** @lang XML */
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $data['box_data']
    );
    $this->assertEquals(23, $data['view_id']);
    $this->assertEquals(array('box_id' => 42, 'lng_id' => 21), $filter);
    return TRUE;
  }

  /**
   * @covers \Papaya\Content\Box\Translation::save
   */
  public function testSaveWithoutIndexDataExpectingFalse() {
    $translation = new Translation();
    $this->assertFalse($translation->save());
  }

  /**
   * @covers \Papaya\Content\Box\Translation::save
   */
  public function testSaveCheckFailesExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('table_box_trans', 42, 21)))
      ->will($this->returnValue(FALSE));
    $translation = new Translation();
    $translation->setDatabaseAccess($databaseAccess);
    $translation->assign(
      array(
        'box_id' => 42,
        'language_id' => 21
      )
    );
    $this->assertFalse($translation->save());
  }
}
