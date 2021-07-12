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

namespace Papaya\Database\Record {

  require_once __DIR__.'/../../../../bootstrap.php';

  class LazyTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Database\Record\Lazy::activateLazyLoad
     * @covers \Papaya\Database\Record\Lazy::getLazyLoadParameters
     */
    public function testActivateLazyLoadDoesNotTriggerLoading() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->never())
        ->method('queryFmt');
      $record = new Lazy_TestProxy();
      $record->setDatabaseAccess($databaseAccess);
      $record->activateLazyLoad(array('id' => 42));
      $this->assertEquals(
        array(array('id' => 42)),
        $record->getLazyLoadParameters()
      );
    }

    /**
     * @covers \Papaya\Database\Record\Lazy::activateLazyLoad
     * @covers \Papaya\Database\Record\Lazy::lazyLoad
     * @covers \Papaya\Database\Record\Lazy::_loadRecord
     */
    public function testActiveLazyLoadParametersAreUsedDuringLazyLoad() {
      $databaseAccess = $this->getDatabaseAccessFixture();
      $databaseAccess
        ->expects($this->once())
        ->method('getSqlCondition')
        ->with(array('field_id' => 42))
        ->will($this->returnValue('>>CONDITION>>'));
      $record = new Lazy_TestProxy();
      $record->setDatabaseAccess($databaseAccess);
      $record->activateLazyLoad(array('id' => 42));
      $this->assertEquals(
        array(
          'id' => 42,
          'content' => 'content one'
        ),
        $record->toArray()
      );
    }

    /**
     * @covers \Papaya\Database\Record\Lazy::assign
     */
    public function testAssignDisablesLazyLoad() {
      $record = new Lazy_TestProxy();
      $record->activateLazyLoad(array('id' => 42));
      $record->assign(array('id' => 42));
      $this->assertNull($record->getLazyLoadParameters());
    }

    /**
     * @covers \Papaya\Database\Record\Lazy::lazyLoad
     */
    public function testLoadIsOnlyCalledOnce() {
      $databaseAccess = $this->getDatabaseAccessFixture();
      $record = new Lazy_TestProxy();
      $record->setDatabaseAccess($databaseAccess);
      $record->activateLazyLoad(array('id' => 42));
      $record->toArray();
      $this->assertEquals(
        array(
          'id' => 42,
          'content' => 'content one'
        ),
        $record->toArray()
      );
    }

    /**
     * @covers \Papaya\Database\Record\Lazy::toArray
     */
    public function testToArray() {
      $record = new Lazy_TestProxy();
      $record->setDatabaseAccess($this->getDatabaseAccessFixture());
      $record->activateLazyLoad(array('id' => 42));
      $this->assertEquals(
        array(
          'id' => 42,
          'content' => 'content one'
        ),
        $record->toArray()
      );
    }

    /**
     * @covers \Papaya\Database\Record\Lazy::__isset
     */
    public function testMagicMethodIsset() {
      $record = new Lazy_TestProxy();
      $record->setDatabaseAccess($this->getDatabaseAccessFixture());
      $record->activateLazyLoad(array('id' => 42));
      $this->assertTrue(
        isset($record->content)
      );
    }

    /**
     * @covers \Papaya\Database\Record\Lazy::__get
     */
    public function testMagicMethodGet() {
      $record = new Lazy_TestProxy();
      $record->setDatabaseAccess($this->getDatabaseAccessFixture());
      $record->activateLazyLoad(array('id' => 42));
      $this->assertEquals(
        'content one',
        $record->content
      );
    }

    /**
     * @covers \Papaya\Database\Record\Lazy::__set
     */
    public function testMagicMethodSet() {
      $record = new Lazy_TestProxy();
      $record->setDatabaseAccess($this->getDatabaseAccessFixture());
      $record->activateLazyLoad(array('id' => 42));
      $record->content = 'changed';
      $this->assertEquals(
        array(
          'id' => 42,
          'content' => 'changed'
        ),
        $record->toArray()
      );
    }

    /**
     * @covers \Papaya\Database\Record\Lazy::__unset
     */
    public function testMagicMethodUnset() {
      $record = new Lazy_TestProxy();
      $record->setDatabaseAccess($this->getDatabaseAccessFixture());
      $record->activateLazyLoad(array('id' => 42));
      unset($record->content);
      $this->assertEquals(
        array(
          'id' => 42,
          'content' => NULL
        ),
        $record->toArray()
      );
    }

    /**
     * @covers \Papaya\Database\Record\Lazy::offsetExists
     */
    public function testOffsetExists() {
      $record = new Lazy_TestProxy();
      $record->setDatabaseAccess($this->getDatabaseAccessFixture());
      $record->activateLazyLoad(array('id' => 42));
      $this->assertTrue(isset($record['content']));
    }

    /**
     * @covers \Papaya\Database\Record\Lazy::isLoaded
     */
    public function testIsLoaded() {
      $record = new Lazy_TestProxy();
      $record->setDatabaseAccess($this->getDatabaseAccessFixture());
      $record->activateLazyLoad(array('id' => 42));
      $this->assertTrue($record->isLoaded());
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
              'field_id' => 42,
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

  /**
   * @property mixed content
   */
  class Lazy_TestProxy extends Lazy {

    protected $_fields = array(
      'id' => 'field_id',
      'content' => 'field_content'
    );

    protected $_tableName = 'sampletable';
  }
}
