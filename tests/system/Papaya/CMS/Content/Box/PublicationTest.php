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

namespace Papaya\CMS\Content\Box;

require_once __DIR__.'/../../../../../bootstrap.php';

class PublicationTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Box\Publication::save
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
      ->with(
        $this->isType('string'),
        $this->equalTo(array('table_box_public', 42))
      )
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->will($this->returnCallback(array($this, 'checkInsertData')));
    $box = new Publication();
    $box->papaya($this->mockPapaya()->application());
    $box->setDatabaseAccess($databaseAccess);
    $box->assign(
      array(
        'id' => 42,
        'name' => 'Box Name',
        'group_id' => 21,
        'created' => 0,
        'modified' => 0,
        'cache_mode' => \Papaya\CMS\Content\Options::CACHE_SYSTEM,
        'cache_time' => 0
      )
    );
    $this->assertTrue($box->save());
  }

  public function checkInsertData($table, $idField, $data) {
    $this->assertEquals('table_box_public', $table);
    $this->assertNull($idField);
    $this->assertEquals(42, $data['box_id']);
    $this->assertEquals(21, $data['boxgroup_id']);
    $this->assertGreaterThan(0, $data['box_created']);
    $this->assertGreaterThan(0, $data['box_modified']);
    $this->assertEquals(\Papaya\CMS\Content\Options::CACHE_SYSTEM, $data['box_cachemode']);
    $this->assertEquals(0, $data['box_cachetime']);
    return TRUE;
  }

  /**
   * @covers \Papaya\CMS\Content\Box\Publication::save
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
      ->with(
        $this->isType('string'),
        $this->equalTo(array('table_box_public', 42))
      )
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->will($this->returnCallback(array($this, 'checkUpdateData')));
    $box = new Publication();
    $box->papaya($this->mockPapaya()->application());
    $box->setDatabaseAccess($databaseAccess);
    $box->assign(
      array(
        'id' => 42,
        'name' => 'Box Name',
        'group_id' => 21,
        'created' => 123,
        'modified' => 0,
        'cache_mode' => \Papaya\CMS\Content\Options::CACHE_SYSTEM,
        'cache_time' => 0
      )
    );
    $this->assertTrue($box->save());
  }

  public function checkUpdateData($table, $data, $filter) {
    $this->assertEquals('table_box_public', $table);
    $this->assertEquals('Box Name', $data['box_name']);
    $this->assertEquals(21, $data['boxgroup_id']);
    $this->assertEquals(123, $data['box_created']);
    $this->assertGreaterThan(1, $data['box_modified']);
    $this->assertEquals(\Papaya\CMS\Content\Options::CACHE_SYSTEM, $data['box_cachemode']);
    $this->assertEquals(0, $data['box_cachetime']);
    $this->assertEquals(array('box_id' => 42), $filter);
    return 42;
  }

  /**
   * @covers \Papaya\CMS\Content\Box\Publication::save
   */
  public function testSaveWithoutIdExpectingFalse() {
    $box = new Publication();
    $this->assertFalse($box->save());
  }

  /**
   * @covers \Papaya\CMS\Content\Box\Publication::save
   */
  public function testSaveWithSqlErrorOnCheckExistingExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        $this->equalTo(array('table_box_public', 42))
      )
      ->will($this->returnValue(FALSE));
    $page = new Publication();
    $page->papaya($this->mockPapaya()->application());
    $page->setDatabaseAccess($databaseAccess);
    $page->assign(
      array(
        'id' => 42
      )
    );
    $this->assertFalse($page->save());
  }
}
