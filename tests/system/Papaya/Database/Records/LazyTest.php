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

namespace Papaya\Database\Records {

  require_once __DIR__.'/../../../../bootstrap.php';

  class LazyTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Database\Records\Lazy::activateLazyLoad
     * @covers \Papaya\Database\Records\Lazy::getLazyLoadParameters
     */
    public function testActivateLazyLoadDoesNotTriggerLoading() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->never())
        ->method('queryFmt');
      $records = new Lazy_TestProxy();
      $records->setDatabaseAccess($databaseAccess);
      $records->activateLazyLoad();
      $this->assertEquals(
        array([], NULL, NULL),
        $records->getLazyLoadParameters()
      );
    }

    /**
     * @covers \Papaya\Database\Records\Lazy::activateLazyLoad
     * @covers \Papaya\Database\Records\Lazy::lazyLoad
     * @covers \Papaya\Database\Records\Lazy::_loadRecords
     */
    public function testActiveLazyLoadParametersAreUsedDuringLazyLoad() {
      $databaseAccess = $this->getDatabaseAccessFixture();
      $databaseAccess
        ->expects($this->once())
        ->method('getSqlCondition')
        ->with(array('field_id' => 21))
        ->will($this->returnValue('>>CONDITION>>'));
      $records = new Lazy_TestProxy();
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
     * @covers \Papaya\Database\Records\Lazy::lazyLoad
     */
    public function testLoadIsOnlyCalledOnce() {
      $databaseAccess = $this->getDatabaseAccessFixture();
      $records = new Lazy_TestProxy();
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
     * @covers \Papaya\Database\Records\Lazy::absCount
     */
    public function testAbsCount() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
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

      $records = new Lazy_TestProxy();
      $records->setDatabaseAccess($databaseAccess);
      $records->activateLazyLoad();
      $this->assertEquals(7, $records->absCount());
    }

    /**
     * @covers \Papaya\Database\Records\Lazy::toArray
     */
    public function testToArray() {
      $records = new Lazy_TestProxy();
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
     * @covers \Papaya\Database\Records\Lazy::getIterator
     */
    public function testGetIterator() {
      $records = new Lazy_TestProxy();
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
     * @covers \Papaya\Database\Records\Lazy::count
     */
    public function testCount() {
      $records = new Lazy_TestProxy();
      $records->setDatabaseAccess($this->getDatabaseAccessFixture());
      $records->activateLazyLoad();
      $this->assertCount(
        1, $records
      );
    }

    /**
     * @covers \Papaya\Database\Records\Lazy::offsetExists
     */
    public function testOffsetExists() {
      $records = new Lazy_TestProxy();
      $records->setDatabaseAccess($this->getDatabaseAccessFixture());
      $records->activateLazyLoad();
      $this->assertTrue(isset($records[21]));
    }

    /**
     * @covers \Papaya\Database\Records\Lazy::offsetGet
     */
    public function testOffsetGet() {
      $records = new Lazy_TestProxy();
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
     * @covers \Papaya\Database\Records\Lazy::offsetSet
     */
    public function testOffsetSet() {
      $records = new Lazy_TestProxy();
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
     * @covers \Papaya\Database\Records\Lazy::offsetUnset
     */
    public function testOffsetUnset() {
      $records = new Lazy_TestProxy();
      $records->setDatabaseAccess($this->getDatabaseAccessFixture());
      $records->activateLazyLoad();
      unset($records[21]);
      $this->assertCount(0, $records);
    }

    /*************************
     * Fixtures
     *************************/

    private function getDatabaseAccessFixture() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
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

  class Lazy_TestProxy extends Lazy {

    protected $_fields = array(
      'id' => 'field_id',
      'content' => 'field_content'
    );

    protected $_identifierProperties = array('id');

    protected $_tableName = 'sampletable';
  }
}
