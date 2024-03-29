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

namespace Papaya\CMS\Content;

require_once __DIR__.'/../../../../bootstrap.php';

class PageTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Page
   */
  public function testLoad() {
    $translations = $this->createMock(Page\Translations::class);
    $translations
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo(42));
    $record = array(
      'topic_id' => 42,
      'prev' => 21,
      'prev_path' => ';0;11;21;',
      'is_deleted' => FALSE,
      'author_id' => '1234567890...',
      'author_group' => -1,
      'author_perm' => '777',
      'surfer_useparent' => \Papaya\CMS\Content\Options::INHERIT_PERMISSIONS_OWN,
      'surfer_permids' => '1;2;',
      'topic_created' => 1,
      'topic_modified' => 2,
      'topic_weight' => 0,
      'box_useparent' => FALSE,
      'topic_mainlanguage' => 1,
      'linktype_id' => 1,
      'meta_useparent' => FALSE,
      'topic_changefreq' => 50,
      'topic_priority' => 1,
      'topic_protocol' => \Papaya\CMS\Content\Options::SCHEME_SYSTEM,
      'topic_cachemode' => \Papaya\CMS\Content\Options::CACHE_SYSTEM,
      'topic_cachetime' => 0,
      'topic_expiresmode' => \Papaya\CMS\Content\Options::CACHE_SYSTEM,
      'topic_expirestime' => 0,
      'topic_unpublished_languages' => 0
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
      ->with($this->isType('string'), array('table_topic'))
      ->will($this->returnValue($databaseResult));
    $page = new Page();
    $page->setDatabaseAccess($databaseAccess);
    $page->translations($translations);
    $this->assertTrue(
      $page->load(42)
    );
    $this->assertEquals(
      array(
        'id' => 42,
        'parent_id' => 21,
        'is_deleted' => FALSE,
        'owner' => '1234567890...',
        'group' => -1,
        'permissions' => 777,
        'inherit_visitor_permissions' => \Papaya\CMS\Content\Options::INHERIT_PERMISSIONS_OWN,
        'created' => 1,
        'modified' => 2,
        'position' => 0,
        'inherit_boxes' => FALSE,
        'default_language' => 1,
        'link_type' => 1,
        'inherit_meta_information' => FALSE,
        'change_frequency' => 50,
        'priority' => 1,
        'scheme' => \Papaya\CMS\Content\Options::SCHEME_SYSTEM,
        'cache_mode' => \Papaya\CMS\Content\Options::CACHE_SYSTEM,
        'cache_time' => 0,
        'expires_mode' => \Papaya\CMS\Content\Options::CACHE_SYSTEM,
        'expires_time' => 0,
        'unpublished_translations' => 0,
        'parent_path' => array(0, 11, 21),
        'visitor_permissions' => array(1, 2)
      ),
      iterator_to_array($page)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page
   */
  public function testLoadExpectingFalse() {
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
      ->with($this->isType('string'), array('table_topic'))
      ->will($this->returnValue($databaseResult));
    $page = new Page();
    $page->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $page->load(42)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page
   */
  public function testTranslationsGetAfterSet() {
    $translations = $this->createMock(Page\Translations::class);
    $page = new Page();
    $page->translations($translations);
    $this->assertSame(
      $translations, $page->translations()
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page
   */
  public function testTranslationsGetImplicitCreate() {
    $page = new Page();
    $this->assertInstanceOf(
      Page\Translations::class, $page->translations()
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page
   */
  public function testMapPropertiesToFields() {
    $page = new Page();
    $this->assertEquals(
      array(
        'topic_id' => 42,
        'prev' => 21,
        'is_deleted' => FALSE,
        'author_id' => '1234567890...',
        'author_group' => -1,
        'author_perm' => '777',
        'surfer_useparent' => \Papaya\CMS\Content\Options::INHERIT_PERMISSIONS_OWN,
        'topic_created' => 1,
        'topic_modified' => 2,
        'topic_weight' => 0,
        'box_useparent' => FALSE,
        'topic_mainlanguage' => 1,
        'linktype_id' => 1,
        'meta_useparent' => FALSE,
        'topic_changefreq' => 50,
        'topic_priority' => 1,
        'topic_protocol' => \Papaya\CMS\Content\Options::SCHEME_SYSTEM,
        'topic_cachemode' => \Papaya\CMS\Content\Options::CACHE_SYSTEM,
        'topic_cachetime' => 0,
        'topic_expiresmode' => \Papaya\CMS\Content\Options::CACHE_SYSTEM,
        'topic_expirestime' => 0,
        'topic_unpublished_languages' => 0,
        'prev_path' => ';0;11;21;',
        'surfer_permids' => '1;2'
      ),
      $page->mapping()->mapPropertiesToFields(
        array(
          'id' => 42,
          'parent_id' => 21,
          'is_deleted' => FALSE,
          'owner' => '1234567890...',
          'group' => -1,
          'permissions' => 777,
          'inherit_visitor_permissions' => \Papaya\CMS\Content\Options::INHERIT_PERMISSIONS_OWN,
          'created' => 1,
          'modified' => 2,
          'position' => 0,
          'inherit_boxes' => FALSE,
          'default_language' => 1,
          'link_type' => 1,
          'inherit_meta_information' => FALSE,
          'change_frequency' => 50,
          'priority' => 1,
          'scheme' => \Papaya\CMS\Content\Options::SCHEME_SYSTEM,
          'cache_mode' => \Papaya\CMS\Content\Options::CACHE_SYSTEM,
          'cache_time' => 0,
          'expires_mode' => \Papaya\CMS\Content\Options::CACHE_SYSTEM,
          'expires_time' => 0,
          'unpublished_translations' => 0,
          'parent_path' => array(0, 11, 21),
          'visitor_permissions' => array(1, 2)
        )
      )
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page
   */
  public function testOnBeforeInsert() {
    $page = new Page();
    $page->callbacks()->onBeforeInsert($page);
    $this->assertGreaterThan(0, $page->created);
    $this->assertGreaterThan(0, $page->modified);
  }

  /**
   * @covers \Papaya\CMS\Content\Page
   */
  public function testOnBeforeUpdate() {
    $page = new Page();
    $page->callbacks()->onBeforeUpdate($page);
    $this->assertGreaterThan(0, $page->modified);
  }
}
