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

use Papaya\Database\Result;
use Papaya\Database\Records\Grouped;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseRecordsGroupedTest extends \PapayaTestCase {

  /**
  * @covers Grouped::_loadRecords
  * @covers Grouped::getIterator
  */
  public function testLoadAndIterateRootWithoutIdentifier() {
    $records = new \PapayaDatabaseRecordsGrouped_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseFixture());
    $this->assertTrue($records->load());
    $this->assertEquals(
      array(
        0 => new ArrayObject(
          array(
            array(
              'id' => 1,
              'group_id' => 0,
              'title' => 'One'
            ),
            array(
              'id' => 2,
              'group_id' => 0,
              'title' => 'Two'
            )
          )
        ),
        1 => new ArrayObject(
          array(
            array(
              'id' => 3,
              'group_id' => 1,
              'title' => 'Tree'
            )
          )
        )
      ),
      iterator_to_array($records)
    );
  }

  /**
  * @covers Grouped::_loadRecords
  * @covers Grouped::getIterator
  */
  public function testLoadAndIterateRootWithIdentifier() {
    $records = new \PapayaDatabaseRecordsGrouped_TestProxy();
    $records->_identifierProperties = array('id');
    $records->setDatabaseAccess($this->getDatabaseFixture());
    $this->assertTrue($records->load());
    $this->assertEquals(
      array(
        0 => new ArrayObject(
          array(
            1 => array(
              'id' => 1,
              'group_id' => 0,
              'title' => 'One'
            ),
            2 => array(
              'id' => 2,
              'group_id' => 0,
              'title' => 'Two'
            )
          )
        ),
        1 => new ArrayObject(
          array(
            3 => array(
              'id' => 3,
              'group_id' => 1,
              'title' => 'Tree'
            )
          )
        )
      ),
      iterator_to_array($records)
    );
  }

  /**
  * @covers Grouped::_loadRecords
  * @covers Grouped::getIterator
  */
  public function testLoadWithInvalidIdentifierExpectingException() {
    $records = new \PapayaDatabaseRecordsGrouped_TestProxy();
    $records->_groupIdentifierProperties = array();
    $records->setDatabaseAccess($this->getDatabaseFixture());
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Properties needed to group records.');
    $records->load();
  }

  /**
  * @covers Grouped::load
  * @covers Grouped::_loadRecords
  */
  public function testLoadExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue(" field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue(FALSE));
    $records = new \PapayaDatabaseRecordsGrouped_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertFalse($records->load(42));
  }

  /************************
  * Fixtures
  ************************/

  public function getDatabaseFixture() {
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 1,
            'field_group_id' => 0,
            'field_title' => 'One'
          ),
          array(
            'field_id' => 2,
            'field_group_id' => 0,
            'field_title' => 'Two'
          ),
          array(
            'field_id' => 3,
            'field_group_id' => 1,
            'field_title' => 'Tree'
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    return $databaseAccess;
  }
}


class PapayaDatabaseRecordsGrouped_TestProxy extends Grouped {

  public /** @noinspection PropertyInitializationFlawsInspection */
    $_identifierProperties = array();

  public /** @noinspection PropertyInitializationFlawsInspection */
    $_groupIdentifierProperties = array('group_id');

  protected $_fields = array(
    'id' => 'field_id',
    'group_id' => 'field_group_id',
    'title' => 'field_title'
  );

  protected $_tableName = 'tablename';
}
