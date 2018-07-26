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

use Papaya\Content\Page\Reference;
use Papaya\Content\Tables;
use Papaya\Database\Record\Key\Fields;
use Papaya\Database\Record\Mapping;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageReferenceTest extends PapayaTestCase {

  /**
  * @covers Reference::_createKey
  */
  public function testCreateKey() {
    $reference = new Reference();
    $key = $reference->key();
    $this->assertInstanceOf(Fields::class, $key);
    $this->assertEquals(array('source_id', 'target_id'), $key->getProperties());
  }

  /**
  * @covers Reference::_createMapping
  */
  public function testCreateMapping() {
    $reference = new Reference();
    /** @var PHPUnit_Framework_MockObject_MockObject|Mapping $mapping */
    $mapping = $reference->mapping();
    $this->assertInstanceOf(Mapping::class, $mapping);
    $this->assertTrue(isset($mapping->callbacks()->onAfterMapping));
  }

  /**
   * @covers Reference::callbackSortPageIds
   * @dataProvider provideMappingData
   * @param array $expected
   * @param int $mode
   * @param array $values
   * @param array $record
   */
  public function testCallbackSortPageIds(array $expected, $mode, array $values, array $record) {
    $reference = new Reference();
    $this->assertEquals(
      $expected,
      $reference->callbackSortPageIds(new stdClass, $mode, $values, $record)
    );
  }

  /**
  * @covers Reference::exists
  */
  public function testExistsExpectingTrue() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_'.Tables::PAGE_REFERENCES, 21, 48))
      ->will($this->returnValue($databaseResult));
    $reference = new Reference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertTrue($reference->exists(48, 21));
  }

  /**
  * @covers Reference::exists
  */
  public function testExistsExpectingFalse() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_'.Tables::PAGE_REFERENCES, 21, 48))
      ->will($this->returnValue($databaseResult));
    $reference = new Reference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertFalse($reference->exists(21, 48));
  }

  /**
  * @covers Reference::exists
  */
  public function testExistsWithDatabaseErrorExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_'.Tables::PAGE_REFERENCES, 21, 48))
      ->will($this->returnValue(FALSE));
    $reference = new Reference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertFalse($reference->exists(21, 48));
  }

  /*************************
  * Data Provider
  *************************/

  public static function provideMappingData() {
    return array(
      'record keep' => array(
        array(
          'topic_source_id' => 21,
          'topic_target_id' => 42
        ),
        Mapping::PROPERTY_TO_FIELD,
        array(),
        array(
          'topic_source_id' => 21,
          'topic_target_id' => 42
        )
      ),
      'record change' => array(
        array(
          'topic_source_id' => 42,
          'topic_target_id' => 84
        ),
        Mapping::PROPERTY_TO_FIELD,
        array(),
        array(
          'topic_source_id' => 84,
          'topic_target_id' => 42
        )
      ),
      'values keep' => array(
        array(
          'source_id' => 21,
          'target_id' => 42
        ),
        Mapping::FIELD_TO_PROPERTY,
        array(
          'source_id' => 21,
          'target_id' => 42
        ),
        array()
      ),
      'values change' => array(
        array(
          'source_id' => 42,
          'target_id' => 84
        ),
        Mapping::FIELD_TO_PROPERTY,
        array(
          'source_id' => 84,
          'target_id' => 42
        ),
        array()
      )
    );
  }
}
