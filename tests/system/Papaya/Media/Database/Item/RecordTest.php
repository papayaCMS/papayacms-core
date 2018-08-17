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

namespace Papaya\Media\Database\Item;

require_once __DIR__.'/../../../../../bootstrap.php';

\Papaya\TestCase::defineConstantDefaults('DB_FETCHMODE_ASSOC');

class RecordTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Media\Database\Item\Record::load
   */
  public function testLoad() {
    $record = new Record();
    $databaseResult = $this
      ->getMockBuilder(\dbresult_base::class)
      ->disableOriginalConstructor()
      ->allowMockingUnknownTypes()
      ->getMock();
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->will(
        $this->returnValue(
          array(
            'file_id' => '',
            'folder_id' => '',
            'surfer_id' => '',
            'file_name' => '',
            'file_date' => '',
            'file_size' => '',
            'width' => '',
            'height' => ''
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->exactly(2))
      ->method('getTableName')
      ->will($this->returnValue('TEST'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue($databaseResult));
    $record->setDatabaseAccess($databaseAccess);
    $this->assertTrue($record->load('sample'));
  }

  /**
   * @covers \Papaya\Media\Database\Item\Record::load
   */
  public function testLoadExpectingFalse() {
    $record = new Record();
    $databaseResult = $this
      ->getMockBuilder(\dbresult_base::class)
      ->disableOriginalConstructor()
      ->allowMockingUnknownTypes()
      ->getMock();
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->will($this->returnValue(NULL));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->exactly(2))
      ->method('getTableName')
      ->will($this->returnValue('TEST'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue($databaseResult));
    $record->setDatabaseAccess($databaseAccess);
    $this->assertFalse($record->load('sample'));
  }

  /**
   * @covers \Papaya\Media\Database\Item\Record::save
   */
  public function testSaveExpectingFalse() {
    $record = new Record();
    $this->assertFalse($record->save());
  }
}
