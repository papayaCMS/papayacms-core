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
use Papaya\Database\Records\Lazy;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseRecordsLazyTest extends PapayaTestCase {

  /**
  * @covers Lazy::activateLazyLoad
  * @covers Lazy::getLazyLoadParameters
  */
  public function testActivateLazyLoadDoesNotTriggerLoading() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('queryFmt');
    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->activateLazyLoad();
    $this->assertEquals(
      array(),
      $records->getLazyLoadParameters()
    );
  }

  /**
  * @covers Lazy::activateLazyLoad
  * @covers Lazy::lazyLoad
  * @covers Lazy::_loadRecords
  */
  public function testActiveLazyLoadParametersAreUsedDuringLazyLoad() {
    $databaseAccess = $this->getDatabaseAccessFixture();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 21))
      ->will($this->returnValue('>>CONDITION>>'));
    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->activateLazyLoad(array('id' => 21));
    $this->assertEquals(
      array(
        21 => array(
         'id' => 21,
         'content' => 'content one'
        )
      ),
      $records->toArray()
    );
  }

  /**
  * @covers Lazy::lazyLoad
  */
  public function testLoadIsOnlyCalledOnce() {
    $databaseAccess = $this->getDatabaseAccessFixture();
    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->activateLazyLoad();
    $records->toArray();
    $this->assertEquals(
      array(
        21 => array(
         'id' => 21,
         'content' => 'content one'
        )
      ),
      $records->toArray()
    );
  }

  /**
  * @covers Lazy::absCount
  */
  public function testAbsCount() {
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 21,
            'field_content' => 'content one'
          ),
          FALSE
        )
      );
    $databaseResult
      ->expects($this->once())
      ->method('absCount')
      ->will($this->returnValue(7));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue($databaseResult));

    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->activateLazyLoad();
    $this->assertEquals(7, $records->absCount());
  }

  /**
  * @covers Lazy::toArray
  */
  public function testToArray() {
    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $this->assertEquals(
      array(
        21 => array(
         'id' => 21,
         'content' => 'content one'
        )
      ),
      $records->toArray()
    );
  }

  /**
  * @covers Lazy::getIterator
  */
  public function testGetIterator() {
    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $this->assertEquals(
      array(
        21 => array(
         'id' => 21,
         'content' => 'content one'
        )
      ),
      iterator_to_array($records)
    );
  }

  /**
  * @covers Lazy::count
  */
  public function testCount() {
    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $this->assertCount(
      1, $records
    );
  }

  /**
  * @covers Lazy::offsetExists
  */
  public function testOffsetExists() {
    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $this->assertTrue(isset($records[21]));
  }

  /**
  * @covers Lazy::offsetGet
  */
  public function testOffsetGet() {
    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $this->assertEquals(
      array(
       'id' => 21,
       'content' => 'content one'
      ),
      $records[21]
    );
  }

  /**
  * @covers Lazy::offsetSet
  */
  public function testOffsetSet() {
    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $records[42] = array(
      'id' => 42,
      'content' => 'content two'
    );
    $this->assertEquals(
      array(
        21 => array(
         'id' => 21,
         'content' => 'content one'
        ),
        42 => array(
          'id' => 42,
          'content' => 'content two'
        )
      ),
      iterator_to_array($records)
    );
  }

  /**
  * @covers Lazy::offsetUnset
  */
  public function testOffsetUnset() {
    $records = new \PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    unset($records[21]);
    $this->assertCount(0, $records);
  }

  /*************************
  * Fixtures
  *************************/

  private function getDatabaseAccessFixture() {
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 21,
            'field_content' => 'content one'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue($databaseResult));
    return $databaseAccess;
  }
}

class PapayaDatabaseRecordsLazy_TestProxy extends Lazy {

  protected $_fields = array(
    'id' => 'field_id',
    'content' => 'field_content'
  );

  protected $_identifierProperties = array('id');

  protected $_tableName = 'sampletable';
}
