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

class PagesTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Pages
   */
  public function testLoadWithTranslationNeeded() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 42,
            'prev' => 21,
            'prev_path' => ';0;21;',
            'topic_protocol' => \Papaya\Utility\Server\Protocol::HTTP,
            'linktype_id' => 1,
            'topic_title' => 'sample'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->logicalAnd($this->isType('string'), $this->stringContains('INNER JOIN')),
        array(
          'table_'.\Papaya\CMS\Content\Tables::PAGES,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_TRANSLATIONS,
          1,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_PUBLICATIONS,
          'table_'.\Papaya\CMS\Content\Tables::VIEWS,
          'table_'.\Papaya\CMS\Content\Tables::VIEW_CONFIGURATIONS,
          23,
          'table_'.\Papaya\CMS\Content\Tables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new Pages(TRUE);
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('language_id' => 1, 'viewmode_id' => 23)));
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 21),
          'title' => 'sample',
          'link_type_id' => 1,
          'scheme' => \Papaya\Utility\Server\Protocol::HTTP
        )
      ),
      $pages->toArray()
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Pages
   */
  public function testLoadWithEmptyFilter() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 42,
            'prev' => 21,
            'prev_path' => ';0;21;',
            'topic_protocol' => \Papaya\Utility\Server\Protocol::HTTP,
            'linktype_id' => 1,
            'topic_title' => NULL
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->logicalAnd($this->isType('string'), $this->stringContains('LEFT JOIN')),
        array(
          'table_'.\Papaya\CMS\Content\Tables::PAGES,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_TRANSLATIONS,
          0,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_PUBLICATIONS,
          'table_'.\Papaya\CMS\Content\Tables::VIEWS,
          'table_'.\Papaya\CMS\Content\Tables::VIEW_CONFIGURATIONS,
          0,
          'table_'.\Papaya\CMS\Content\Tables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new Pages(FALSE);
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array()));
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 21),
          'title' => NULL,
          'link_type_id' => 1,
          'scheme' => \Papaya\Utility\Server\Protocol::HTTP
        )
      ),
      $pages->toArray()
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Pages
   */
  public function testLoadWithId() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 42,
            'prev' => 21,
            'prev_path' => ';0;21;',
            'topic_protocol' => \Papaya\Utility\Server\Protocol::HTTP,
            'linktype_id' => 1,
            'topic_title' => 'sample'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('t.topic_id' => 42))
      ->will($this->returnValue(" t.topic_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->logicalAnd($this->isType('string'), $this->stringContains('LEFT JOIN')),
        array(
          'table_'.\Papaya\CMS\Content\Tables::PAGES,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_TRANSLATIONS,
          1,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_PUBLICATIONS,
          'table_'.\Papaya\CMS\Content\Tables::VIEWS,
          'table_'.\Papaya\CMS\Content\Tables::VIEW_CONFIGURATIONS,
          0,
          'table_'.\Papaya\CMS\Content\Tables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new Pages();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('id' => 42, 'language_id' => 1)));
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 21),
          'title' => 'sample',
          'link_type_id' => 1,
          'scheme' => \Papaya\Utility\Server\Protocol::HTTP
        )
      ),
      $pages->toArray()
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Pages
   */
  public function testLoadWithStatus() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 42,
            'prev' => 21,
            'prev_path' => ';0;21;',
            'topic_protocol' => \Papaya\Utility\Server\Protocol::HTTP,
            'linktype_id' => 1,
            'topic_title' => 'sample'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('t.prev' => 42))
      ->will($this->returnValue(" t.topic_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          'table_'.\Papaya\CMS\Content\Tables::PAGES,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_TRANSLATIONS,
          1,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_PUBLICATIONS,
          'table_'.\Papaya\CMS\Content\Tables::VIEWS,
          'table_'.\Papaya\CMS\Content\Tables::VIEW_CONFIGURATIONS,
          0,
          'table_'.\Papaya\CMS\Content\Tables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new Pages();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $pages->load(
        array('parent' => 42, 'language_id' => 1, 'status' => 'modified')
      )
    );
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 21),
          'title' => 'sample',
          'link_type_id' => 1,
          'scheme' => \Papaya\Utility\Server\Protocol::HTTP
        )
      ),
      $pages->toArray()
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Pages
   */
  public function testLoadWithParentId() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 42,
            'prev' => 21,
            'prev_path' => ';0;21;',
            'topic_protocol' => \Papaya\Utility\Server\Protocol::HTTP,
            'linktype_id' => 1,
            'topic_title' => 'sample'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('t.prev' => 42))
      ->will($this->returnValue(" t.topic_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          'table_'.\Papaya\CMS\Content\Tables::PAGES,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_TRANSLATIONS,
          1,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_PUBLICATIONS,
          'table_'.\Papaya\CMS\Content\Tables::VIEWS,
          'table_'.\Papaya\CMS\Content\Tables::VIEW_CONFIGURATIONS,
          0,
          'table_'.\Papaya\CMS\Content\Tables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new Pages();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('parent' => 42, 'language_id' => 1)));
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 21),
          'title' => 'sample',
          'link_type_id' => 1,
          'scheme' => \Papaya\Utility\Server\Protocol::HTTP
        )
      ),
      $pages->toArray()
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Pages
   */
  public function testMappingImplicitCreateAttachesCallback() {
    $pages = new Pages();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $pages->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onMapValue));
  }

  /**
   * @covers \Papaya\CMS\Content\Pages
   */
  public function testMapValueReturnsValueByDefault() {
    $pages = new Pages();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $pages->mapping();
    $this->assertEquals(
      'success',
      $mapping->callbacks()->onMapValue(
        \Papaya\Database\Interfaces\Mapping::FIELD_TO_PROPERTY,
        'id',
        'topic_id',
        'success'
      )
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Pages
   */
  public function testMapValueDecodesPath() {
    $pages = new Pages();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $pages->mapping();
    $this->assertEquals(
      array(21, 42),
      $mapping->callbacks()->onMapValue(
        \Papaya\Database\Interfaces\Mapping::FIELD_TO_PROPERTY,
        'path',
        'prev_path',
        ';21;42;'
      )
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Pages
   */
  public function testMapValueEncodesPath() {
    $pages = new Pages();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $pages->mapping();
    $this->assertEquals(
      ';21;42;',
      $mapping->callbacks()->onMapValue(
        \Papaya\Database\Interfaces\Mapping::PROPERTY_TO_FIELD,
        'path',
        'prev_path',
        array(21, 42)
      )
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Pages
   */
  public function testIsPublicExpectingFalse() {
    $pages = new Pages();
    $this->assertFalse($pages->isPublic());
  }
}
