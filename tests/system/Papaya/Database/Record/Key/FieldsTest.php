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

namespace Papaya\Database\Record\Key;

require_once __DIR__.'/../../../../../bootstrap.php';

class FieldsTest extends \PapayaTestCase {

  /**
   * @covers Fields::__construct
   */
  public function testConstructor() {
    $key = $this->getKeyFixture();
    $this->assertEquals(
      array('fk_one_id', 'fk_two_id'), $key->getProperties()
    );
  }

  /**
   * @covers Fields::assign
   * @covers Fields::getFilter
   */
  public function testAssignAndGetFilter() {
    $key = $this->getKeyFixture();
    $this->assertTrue($key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42)));
    $this->assertEquals(
      array('fk_one_id' => 21, 'fk_two_id' => 42), $key->getFilter()
    );
  }

  /**
   * @covers Fields::assign
   * @covers Fields::getFilter
   */
  public function testAssignWithInvalidData() {
    $key = $this->getKeyFixture();
    $this->assertFalse($key->assign(array('other' => 42)));
    $this->assertEquals(
      array('fk_one_id' => NULL, 'fk_two_id' => NULL), $key->getFilter()
    );
  }

  /**
   * @covers Fields::getFilter
   */
  public function testGetFilterWithoutAssign() {
    $key = $this->getKeyFixture();
    $this->assertEquals(
      array('fk_one_id' => NULL, 'fk_two_id' => NULL), $key->getFilter()
    );
  }

  /**
   * @covers Fields::getFilter
   */
  public function testGetFilterWithRecord() {
    $record = $this->createMock(\Papaya\Database\Record::class);
    $record
      ->expects($this->atLeastOnce())
      ->method('offsetExists')
      ->will(
        $this->returnValueMap(
          array(
            array('fk_one_id', TRUE),
            array('fk_two_id', FALSE)
          )
        )
      );
    $record
      ->expects($this->atLeastOnce())
      ->method('offsetGet')
      ->will(
        $this->returnValueMap(
          array(
            array('fk_one_id', 21),
            array('fk_two_id', 48)
          )
        )
      );
    $key = $this->getKeyFixture($record);
    $this->assertEquals(
      array('fk_one_id' => 21, 'fk_two_id' => NULL), $key->getFilter()
    );
  }

  /**
   * @covers Fields::getProperties
   */
  public function testGetProperties() {
    $key = $this->getKeyFixture();
    $this->assertEquals(
      array('fk_one_id', 'fk_two_id'), $key->getProperties()
    );
  }

  /**
   * @covers Fields::exists
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
      ->method('getSqlCondition')
      ->with(array('field_one_id' => 21, 'field_two_id' => 42))
      ->will($this->returnValue('{CONDITION}'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
      /** @lang Text */
        'SELECT COUNT(*) FROM %s WHERE {CONDITION}', array('table_sometable')
      )
      ->will($this->returnValue($databaseResult));

    $mapping = $this->createMock(\Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->once())
      ->method('mapPropertiesToFields')
      ->with(array('fk_one_id' => 21, 'fk_two_id' => 42))
      ->will($this->returnValue(array('field_one_id' => 21, 'field_two_id' => 42)));

    $record = $this->createMock(\Papaya\Database\Record::class);
    $record
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $record
      ->expects($this->any())
      ->method('mapping')
      ->will($this->returnValue($mapping));

    $key = $this->getKeyFixture($record);
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $this->assertTrue($key->exists());
  }

  /**
   * @covers Fields::exists
   */
  public function testExistsWithDatabaseErrorExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_one_id' => 21, 'field_two_id' => 42))
      ->will($this->returnValue('{CONDITION}'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(/** @lang Text */
        'SELECT COUNT(*) FROM %s WHERE {CONDITION}', array('table_sometable'))
      ->will($this->returnValue(FALSE));

    $mapping = $this->createMock(\Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->once())
      ->method('mapPropertiesToFields')
      ->with(array('fk_one_id' => 21, 'fk_two_id' => 42))
      ->will($this->returnValue(array('field_one_id' => 21, 'field_two_id' => 42)));

    $record = $this->createMock(\Papaya\Database\Record::class);
    $record
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $record
      ->expects($this->any())
      ->method('mapping')
      ->will($this->returnValue($mapping));

    $key = $this->getKeyFixture($record);
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $this->assertFalse($key->exists());
  }

  /**
   * @covers Fields::exists
   */
  public function testExistsWithEmptyMappingResult() {
    $mapping = $this->createMock(\Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->once())
      ->method('mapPropertiesToFields')
      ->with(array('fk_one_id' => NULL, 'fk_two_id' => NULL))
      ->will($this->returnValue(array()));
    $record = $this->createMock(\Papaya\Database\Record::class);
    $record
      ->expects($this->any())
      ->method('mapping')
      ->will($this->returnValue($mapping));
    $key = $this->getKeyFixture($record);
    $this->assertFalse($key->exists());
  }

  /**
   * @covers Fields::getQualities
   */
  public function testGetQualities() {
    $key = $this->getKeyFixture();
    $this->assertEquals(0, $key->getQualities());
  }

  /**
   * @covers Fields::__toString
   */
  public function testMagicToString() {
    $key = $this->getKeyFixture();
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $this->assertSame('21|42', (string)$key);
  }

  /**
   * @covers Fields::clear
   */
  public function testClear() {
    $key = $this->getKeyFixture();
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $key->clear();
    $this->assertSame('', (string)$key);
  }

  /**
   * @param \Papaya\Database\Record|NULL|\PHPUnit_Framework_MockObject_MockObject $record
   * @return Fields
   */
  public function getKeyFixture(\Papaya\Database\Record $record = NULL) {
    if (NULL === $record) {
      $record = $this->createMock(\Papaya\Database\Record::class);
    }
    return new Fields(
      $record, 'sometable', array('fk_one_id', 'fk_two_id')
    );
  }
}
