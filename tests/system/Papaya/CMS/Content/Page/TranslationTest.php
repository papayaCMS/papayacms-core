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

namespace Papaya\CMS\Content\Page;

require_once __DIR__.'/../../../../../bootstrap.php';

class TranslationTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Page\Translation
   */
  public function testLoad() {
    $record = array(
      'topic_id' => '42',
      'lng_id' => '1',
      'topic_title' => 'translated page title',
      'topic_content' => '',
      'topic_trans_created' => '123',
      'topic_trans_modified' => '456',
      'meta_title' => 'meta title',
      'meta_keywords' => 'meta, keywords',
      'meta_descr' => 'meta description',
      'view_id' => '21',
      'view_name' => 'view-example',
      'module_guid' => '123456789012345678901234567890ab'
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
      ->with(
        $this->isType('string'),
        array(
          'table_'.\Papaya\CMS\Content\Tables::PAGE_TRANSLATIONS,
          'table_'.\Papaya\CMS\Content\Tables::VIEWS
        )
      )
      ->will($this->returnValue($databaseResult));
    $translation = new Translation();
    $translation->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $translation->load(array(42, 1))
    );
    $this->assertEquals(
      array(
        'id' => 42,
        'language_id' => 1,
        'title' => 'translated page title',
        'created' => 123,
        'modified' => 456,
        'meta_title' => 'meta title',
        'meta_keywords' => 'meta, keywords',
        'meta_description' => 'meta description',
        'view_id' => 21,
        'view_name' => 'view-example',
        'module_guid' => '123456789012345678901234567890ab',
        'content' => array()
      ),
      iterator_to_array($translation)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Translation
   */
  public function testLoadWithId() {
    $record = array(
      'topic_id' => '42',
      'lng_id' => '1',
      'topic_title' => 'translated page title',
      'topic_content' => '',
      'topic_trans_created' => '123',
      'topic_trans_modified' => '456',
      'meta_title' => 'meta title',
      'meta_keywords' => 'meta, keywords',
      'meta_descr' => 'meta description',
      'view_id' => '21',
      'view_name' => 'view-example',
      'module_guid' => '123456789012345678901234567890ab'
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
      ->with(
        $this->isType('string'),
        array(
          'table_'.\Papaya\CMS\Content\Tables::PAGE_TRANSLATIONS,
          'table_'.\Papaya\CMS\Content\Tables::VIEWS
        )
      )
      ->will($this->returnValue($databaseResult));
    $translation = new Translation();
    $translation->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $translation->load(array(42, 1))
    );
    $this->assertEquals(
      array(
        'id' => 42,
        'language_id' => 1,
        'title' => 'translated page title',
        'created' => 123,
        'modified' => 456,
        'meta_title' => 'meta title',
        'meta_keywords' => 'meta, keywords',
        'meta_description' => 'meta description',
        'view_id' => 21,
        'view_name' => 'view-example',
        'module_guid' => '123456789012345678901234567890ab',
        'content' => array()
      ),
      iterator_to_array($translation)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Translation
   */
  public function testLoadFailedExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          'table_'.\Papaya\CMS\Content\Tables::PAGE_TRANSLATIONS,
          'table_'.\Papaya\CMS\Content\Tables::VIEWS
        )
      )
      ->will($this->returnValue(FALSE));
    $translation = new Translation();
    $translation->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $translation->load(array(42, 1))
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Translation
   */
  public function testLoadNoRecordExpectingFalse() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(\Papaya\Database\Result::FETCH_ASSOC)
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          'table_'.\Papaya\CMS\Content\Tables::PAGE_TRANSLATIONS,
          'table_'.\Papaya\CMS\Content\Tables::VIEWS
        )
      )
      ->will($this->returnValue($databaseResult));
    $translation = new Translation();
    $translation->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $translation->load(array(42, 1))
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Translation
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
      ->method('getSqlCondition')
      ->with(array('topic_id' => 42, 'lng_id' => 21))
      ->will($this->returnValue('>>CONDITION<<'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('table_topic_trans')))
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->will($this->returnCallback(array($this, 'checkInsertData')));
    $translation = new Translation();
    $translation->setDatabaseAccess($databaseAccess);
    $translation->assign(
      array(
        'id' => 42,
        'language_id' => 21,
        'title' => 'page title',
        'content' => array('foo' => 'bar'),
        'meta_title' => 'meta title',
        'meta_keywords' => 'meta keywords',
        'meta_description' => 'meta description',
        'view_id' => 23
      )
    );
    $this->assertTrue((boolean)$translation->save());
  }

  public function checkInsertData($table, $idField, $data) {
    $this->assertEquals('table_topic_trans', $table);
    $this->assertNull($idField);
    $this->assertEquals(42, $data['topic_id']);
    $this->assertEquals(21, $data['lng_id']);
    $this->assertEquals('page title', $data['topic_title']);
    $this->assertEquals(
    /** @lang XML */
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $data['topic_content']
    );
    $this->assertEquals('meta title', $data['meta_title']);
    $this->assertEquals('meta keywords', $data['meta_keywords']);
    $this->assertEquals('meta description', $data['meta_descr']);
    $this->assertEquals(23, $data['view_id']);
    return TRUE;
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Translation
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
      ->method('getSqlCondition')
      ->with(array('topic_id' => 42, 'lng_id' => 21))
      ->will($this->returnValue('>>CONDITION<<'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('table_topic_trans')))
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->will($this->returnCallback(array($this, 'checkUpdateData')));
    $translation = new Translation();
    $translation->setDatabaseAccess($databaseAccess);
    $translation->assign(
      array(
        'id' => 42,
        'language_id' => 21,
        'title' => 'page title',
        'content' => array('foo' => 'bar'),
        'meta_title' => 'meta title',
        'meta_keywords' => 'meta keywords',
        'meta_description' => 'meta description',
        'view_id' => 23
      )
    );
    $this->assertTrue($translation->save());
  }

  public function checkUpdateData($table, $data, $filter) {
    $this->assertEquals('table_topic_trans', $table);
    $this->assertEquals('page title', $data['topic_title']);
    $this->assertEquals(
    /** @lang XML */
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $data['topic_content']
    );
    $this->assertEquals('meta title', $data['meta_title']);
    $this->assertEquals('meta keywords', $data['meta_keywords']);
    $this->assertEquals('meta description', $data['meta_descr']);
    $this->assertEquals(23, $data['view_id']);
    $this->assertEquals(array('topic_id' => 42, 'lng_id' => 21), $filter);
    return TRUE;
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Translation
   */
  public function testSaveWithoutIndexDataExpectingFalse() {
    $translation = new Translation();
    $this->assertFalse($translation->save());
  }
}
