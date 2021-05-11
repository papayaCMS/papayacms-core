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

namespace Papaya\Content\Page\Version;

require_once __DIR__.'/../../../../../bootstrap.php';

class TranslationTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Content\Page\Version\Translation::load
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
      ->with($this->isType('string'), array('table_topic_versions_trans', 'table_views'))
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
}
