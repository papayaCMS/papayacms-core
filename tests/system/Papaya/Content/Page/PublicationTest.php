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

namespace Papaya\Content\Page;

require_once __DIR__.'/../../../../bootstrap.php';

class PublicationTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Content\Page\Publication
   */
  public function testSaveCreateNew() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->with(array('topic_id' => 42))
      ->will($this->returnValue("topic_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        $this->equalTo(array('table_topic_public'))
      )
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->will($this->returnCallback(array($this, 'checkInsertData')));
    $page = new Publication();
    $page->papaya($this->mockPapaya()->application());
    $page->setDatabaseAccess($databaseAccess);
    $page->assign(
      array(
        'id' => 42,
        'parent_id' => 21,
        'parent_path' => array(0, 11),
        'owner' => '123456789012345678901234567890ab',
        'group' => -1,
        'permissions' => '777',
        'inherit_visitor_permissions' => \Papaya\Content\Options::INHERIT_PERMISSIONS_OWN,
        'visitor_permissions' => array(1, 2),
        'created' => 0,
        'modified' => 0,
        'position' => 99999,
        'inherit_boxes' => TRUE,
        'default_language' => 1,
        'link_type' => 1,
        'inherit_meta_information' => TRUE,
        'change_frequency' => 50,
        'priority' => 3,
        'scheme' => \Papaya\Content\Options::SCHEME_SYSTEM,
        'cache_mode' => \Papaya\Content\Options::CACHE_SYSTEM,
        'cache_time' => 0,
        'expires_mode' => \Papaya\Content\Options::CACHE_SYSTEM,
        'expires_time' => 0
      )
    );
    $this->assertTrue((boolean)$page->save());
  }

  public function checkInsertData($table, $idField, $data) {
    $this->assertEquals('table_topic_public', $table);
    $this->assertNull($idField);
    $this->assertEquals(42, $data['topic_id']);
    $this->assertEquals(21, $data['prev']);
    $this->assertEquals(';0;11;', $data['prev_path']);
    $this->assertEquals('123456789012345678901234567890ab', $data['author_id']);
    $this->assertEquals(-1, $data['author_group']);
    $this->assertEquals('777', $data['author_perm']);
    $this->assertEquals(
      \Papaya\Content\Options::INHERIT_PERMISSIONS_OWN, $data['surfer_useparent']
    );
    $this->assertEquals('1;2', $data['surfer_permids']);
    $this->assertGreaterThan(0, $data['topic_created']);
    $this->assertGreaterThan(0, $data['topic_modified']);
    $this->assertEquals(99999, $data['topic_weight']);
    $this->assertEquals(1, (int)$data['box_useparent']);
    $this->assertEquals(1, $data['topic_mainlanguage']);
    $this->assertEquals(1, $data['linktype_id']);
    $this->assertEquals(1, (int)$data['meta_useparent']);
    $this->assertEquals(50, $data['topic_changefreq']);
    $this->assertEquals(3, $data['topic_priority']);
    $this->assertEquals(\Papaya\Content\Options::SCHEME_SYSTEM, $data['topic_protocol']);
    $this->assertEquals(\Papaya\Content\Options::CACHE_SYSTEM, $data['topic_cachemode']);
    $this->assertEquals(0, $data['topic_cachetime']);
    $this->assertEquals(\Papaya\Content\Options::CACHE_SYSTEM, $data['topic_expiresmode']);
    $this->assertEquals(0, $data['topic_expirestime']);
    return 42;
  }

  /**
   * @covers \Papaya\Content\Page\Publication
   */
  public function testSaveUpdateExisting() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->with(array('topic_id' => 42))
      ->will($this->returnValue("topic_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        $this->equalTo(array('table_topic_public'))
      )
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->will($this->returnCallback(array($this, 'checkUpdateData')));
    $page = new Publication();
    $page->papaya($this->mockPapaya()->application());
    $page->setDatabaseAccess($databaseAccess);
    $page->assign(
      array(
        'id' => 42,
        'parent_id' => 21,
        'parent_path' => array(0, 11),
        'owner' => '123456789012345678901234567890ab',
        'group' => -1,
        'permissions' => '777',
        'inherit_visitor_permissions' => \Papaya\Content\Options::INHERIT_PERMISSIONS_OWN,
        'visitor_permissions' => array(1, 2),
        'created' => 123,
        'modified' => 0,
        'position' => 99999,
        'inherit_boxes' => TRUE,
        'default_language' => 1,
        'link_type' => 1,
        'inherit_meta_information' => TRUE,
        'change_frequency' => 50,
        'priority' => 3,
        'scheme' => \Papaya\Content\Options::SCHEME_SYSTEM,
        'cache_mode' => \Papaya\Content\Options::CACHE_SYSTEM,
        'cache_time' => 0,
        'expires_mode' => \Papaya\Content\Options::CACHE_SYSTEM,
        'expires_time' => 0
      )
    );
    $this->assertTrue($page->save());
  }

  public function checkUpdateData($table, $data, $filter) {
    $this->assertEquals('table_topic_public', $table);
    $this->assertEquals(21, $data['prev']);
    $this->assertEquals(';0;11;', $data['prev_path']);
    $this->assertEquals('123456789012345678901234567890ab', $data['author_id']);
    $this->assertEquals(-1, $data['author_group']);
    $this->assertEquals('777', $data['author_perm']);
    $this->assertEquals(
      \Papaya\Content\Options::INHERIT_PERMISSIONS_OWN, $data['surfer_useparent']
    );
    $this->assertEquals('1;2', $data['surfer_permids']);
    $this->assertEquals(123, $data['topic_created']);
    $this->assertGreaterThan(0, $data['topic_modified']);
    $this->assertEquals(99999, $data['topic_weight']);
    $this->assertEquals(1, (int)$data['box_useparent']);
    $this->assertEquals(1, $data['topic_mainlanguage']);
    $this->assertEquals(1, $data['linktype_id']);
    $this->assertEquals(1, (int)$data['meta_useparent']);
    $this->assertEquals(50, $data['topic_changefreq']);
    $this->assertEquals(3, $data['topic_priority']);
    $this->assertEquals(\Papaya\Content\Options::SCHEME_SYSTEM, $data['topic_protocol']);
    $this->assertEquals(\Papaya\Content\Options::CACHE_SYSTEM, $data['topic_cachemode']);
    $this->assertEquals(0, $data['topic_cachetime']);
    $this->assertEquals(\Papaya\Content\Options::CACHE_SYSTEM, $data['topic_expiresmode']);
    $this->assertEquals(0, $data['topic_expirestime']);

    $this->assertEquals(array('topic_id' => 42), $filter);
    return 42;
  }

  /**
   * @covers \Papaya\Content\Page\Publication
   */
  public function testSaveWithoutIdExpectingFalse() {
    $page = new Publication();
    $this->assertFalse($page->save());
  }
}
