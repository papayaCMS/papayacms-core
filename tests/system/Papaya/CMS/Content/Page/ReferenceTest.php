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

namespace Papaya\CMS\Content\Page;

require_once __DIR__.'/../../../../../bootstrap.php';

class ReferenceTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Page\Reference::_createKey
   */
  public function testCreateKey() {
    $reference = new Reference();
    $key = $reference->key();
    $this->assertInstanceOf(\Papaya\Database\Record\Key\Fields::class, $key);
    $this->assertEquals(array('source_id', 'target_id'), $key->getProperties());
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Reference::_createMapping
   */
  public function testCreateMapping() {
    $reference = new Reference();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $reference->mapping();
    $this->assertInstanceOf(\Papaya\Database\Interfaces\Mapping::class, $mapping);
    $this->assertTrue(isset($mapping->callbacks()->onAfterMapping));
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Reference
   * @dataProvider provideMappingData
   * @param array $expected
   * @param int $mode
   * @param array $values
   * @param array $record
   */
  public function testCallbackSortPageIds(array $expected, $mode, array $values, array $record) {
    $reference = new Reference();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $reference->mapping();
    $this->assertEquals(
      $expected,
      $mapping->callbacks()->onAfterMapping($mode, $values, $record)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Reference::exists
   */
  public function testExistsExpectingTrue() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_'.\Papaya\CMS\Content\Tables::PAGE_REFERENCES, 21, 48))
      ->will($this->returnValue($databaseResult));
    $reference = new Reference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertTrue($reference->exists(48, 21));
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Reference::exists
   */
  public function testExistsExpectingFalse() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_'.\Papaya\CMS\Content\Tables::PAGE_REFERENCES, 21, 48))
      ->will($this->returnValue($databaseResult));
    $reference = new Reference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertFalse($reference->exists(21, 48));
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Reference::exists
   */
  public function testExistsWithDatabaseErrorExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_'.\Papaya\CMS\Content\Tables::PAGE_REFERENCES, 21, 48))
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
        \Papaya\Database\Interfaces\Mapping::PROPERTY_TO_FIELD,
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
        \Papaya\Database\Interfaces\Mapping::PROPERTY_TO_FIELD,
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
        \Papaya\Database\Interfaces\Mapping::FIELD_TO_PROPERTY,
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
        \Papaya\Database\Interfaces\Mapping::FIELD_TO_PROPERTY,
        array(
          'source_id' => 84,
          'target_id' => 42
        ),
        array()
      )
    );
  }
}
