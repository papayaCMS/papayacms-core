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

namespace Papaya\Content\Box\Version;

require_once __DIR__.'/../../../../../bootstrap.php';

class TranslationTest extends \PapayaTestCase {

  /**
   * @covers Translation::load
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
      ->with($this->isType('string'), array('table_box_versions_trans', 'table_views', 'table_modules', 42, 1))
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
}
