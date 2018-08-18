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

class ReferencesTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Content\Page\References::load
   * @covers \Papaya\Content\Page\References::_fetchRecords
   *
   *
   */
  public function testLoad() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_source_id' => 21,
            'topic_target_id' => 42,
            'topic_note' => 'note 21 -> 42',
            'topic_source_title' => 'topic 21',
            'topic_target_title' => 'topic 42',
            'topic_source_modified' => '123',
            'topic_target_modified' => '456'
          ),
          array(
            'topic_source_id' => 42,
            'topic_target_id' => 84,
            'topic_note' => 'note 42 -> 84',
            'topic_source_title' => 'topic 42',
            'topic_target_title' => 'topic 84',
            'topic_source_modified' => '123',
            'topic_target_modified' => '456'
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
        array(
          'table_'.\Papaya\Content\Tables::PAGE_REFERENCES,
          'table_'.\Papaya\Content\Tables::PAGES,
          'table_'.\Papaya\Content\Tables::PAGE_TRANSLATIONS,
          42,
          1
        )
      )
      ->will(
        $this->returnValue($databaseResult)
      );
    $references = new References();
    $references->setDatabaseAccess($databaseAccess);
    $this->assertTrue($references->load(42, 1));
    $this->assertEquals(
      array(
        21 => array(
          'source_id' => 42,
          'target_id' => 21,
          'title' => 'topic 21',
          'modified' => '123',
          'note' => 'note 21 -> 42'
        ),
        84 => array(
          'source_id' => 42,
          'target_id' => 84,
          'title' => 'topic 84',
          'modified' => '456',
          'note' => 'note 42 -> 84'
        )
      ),
      $references->getIterator()->getArrayCopy()
    );
  }
}
