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

class TagsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Content\Page\Tags::load
   */
  public function testLoad() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(\Papaya\Database\Result::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array(
            'tag_id' => 1,
            'link_id' => 23,
            'tag_title' => NULL,
            'tag_image' => NULL,
            'tag_description' => NULL,
            'tag_char' => NULL,
          ),
          array(
            'tag_id' => 2,
            'link_id' => 23,
            'tag_title' => NULL,
            'tag_image' => NULL,
            'tag_description' => NULL,
            'tag_char' => NULL,
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tag_links', 'table_tag_trans', 0, 'table_tag', 'table_tag_category', 'topic', 23)
      )
      ->will($this->returnValue($databaseResult));
    $tags = new Tags();
    $tags->setDatabaseAccess($databaseAccess);
    $this->assertTrue($tags->load(23));
    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'page_id' => 23,
          'title' => NULL,
          'image' => NULL,
          'description' => NULL,
          'char' => NULL
        ),
        2 => array(
          'id' => 2,
          'page_id' => 23,
          'title' => NULL,
          'image' => NULL,
          'description' => NULL,
          'char' => NULL
        )
      ),
      $tags->getIterator()->getArrayCopy()
    );
  }

  /**
   * @covers \Papaya\Content\Page\Tags::load
   */
  public function testLoadWithLanguageId() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(\Papaya\Database\Result::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array(
            'tag_id' => 1,
            'link_id' => 23,
            'tag_title' => 'sample title one',
            'tag_image' => 'fdcadb8ada3a8a5067a597cd705824fb',
            'tag_description' => 'A short description',
            'tag_char' => 's'
          ),
          array(
            'tag_id' => 2,
            'link_id' => 23,
            'tag_title' => 'sample title two',
            'tag_image' => 'fdcadb8ada3a8a5067a597cd705824fb',
            'tag_description' => NULL,
            'tag_char' => 's'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tag_links', 'table_tag_trans', 2, 'table_tag', 'table_tag_category', 'topic', 23)
      )
      ->will($this->returnValue($databaseResult));
    $tags = new Tags();
    $tags->setDatabaseAccess($databaseAccess);
    $this->assertTrue($tags->load(23, 2));
    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'page_id' => 23,
          'title' => 'sample title one',
          'image' => 'fdcadb8ada3a8a5067a597cd705824fb',
          'description' => 'A short description',
          'char' => 's'
        ),
        2 => array(
          'id' => 2,
          'page_id' => 23,
          'title' => 'sample title two',
          'image' => 'fdcadb8ada3a8a5067a597cd705824fb',
          'description' => NULL,
          'char' => 's'
        )
      ),
      $tags->getIterator()->getArrayCopy()
    );
  }

  /**
   * @covers \Papaya\Content\Page\Tags::clear
   */
  public function testClear() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with(
        'table_tag_links',
        array(
          'link_type' => 'topic',
          'link_id' => 23
        )
      )
      ->will($this->returnValue(2));
    $tags = new Tags();
    $tags->setDatabaseAccess($databaseAccess);
    $this->assertTrue($tags->clear(23));
  }

  /**
   * @covers \Papaya\Content\Page\Tags::insert
   */
  public function testInsert() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecords')
      ->with(
        'table_tag_links',
        array(
          array(
            'link_type' => 'topic',
            'link_id' => 23,
            'tag_id' => 2
          ),
          array(
            'link_type' => 'topic',
            'link_id' => 23,
            'tag_id' => 3
          )
        )
      )
      ->will($this->returnValue(2));
    $tags = new Tags();
    $tags->setDatabaseAccess($databaseAccess);
    $this->assertTrue($tags->insert(23, array(2, 3)));
  }
}
