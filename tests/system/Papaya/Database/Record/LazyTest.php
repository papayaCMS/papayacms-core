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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseRecordLazyTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordLazy::activateLazyLoad
  * @covers PapayaDatabaseRecordLazy::getLazyLoadParameters
  */
  public function testActivateLazyLoadDoesNotTriggerLoading() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('queryFmt');
    $record = new PapayaDatabaseRecordLazy_TestProxy();
    $record->setDatabaseAccess($databaseAccess);
    $record->activateLazyLoad(array('id' => 42));
    $this->assertEquals(
      array(array('id' => 42)),
      $record->getLazyLoadParameters()
    );
  }

  /**
  * @covers PapayaDatabaseRecordLazy::activateLazyLoad
  * @covers PapayaDatabaseRecordLazy::lazyLoad
  * @covers PapayaDatabaseRecordLazy::_loadRecord
  */
  public function testActiveLazyLoadParametersAreUsedDuringLazyLoad() {
    $databaseAccess = $this->getDatabaseAccessFixture();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue('>>CONDITION>>'));
    $record = new PapayaDatabaseRecordLazy_TestProxy();
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
  * @covers PapayaDatabaseRecordLazy::assign
  */
  public function testAssignDisablesLazyLoad() {
    $record = new PapayaDatabaseRecordLazy_TestProxy();
    $record->activateLazyLoad(array('id' => 42));
    $record->assign(array('id' => 42));
    $this->assertNull($record->getLazyLoadParameters());
  }

  /**
  * @covers PapayaDatabaseRecordLazy::lazyLoad
  */
  public function testLoadIsOnlyCalledOnce() {
    $databaseAccess = $this->getDatabaseAccessFixture();
    $record = new PapayaDatabaseRecordLazy_TestProxy();
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
  * @covers PapayaDatabaseRecordLazy::toArray
  */
  public function testToArray() {
    $record = new PapayaDatabaseRecordLazy_TestProxy();
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
  * @covers PapayaDatabaseRecordLazy::__isset
  */
  public function testMagicMethodIsset() {
    $record = new PapayaDatabaseRecordLazy_TestProxy();
    $record->setDatabaseAccess($this->getDatabaseAccessFixture());
    $record->activateLazyLoad(array('id' => 42));
    $this->assertTrue(
      isset($record->content)
    );
  }

  /**
  * @covers PapayaDatabaseRecordLazy::__get
  */
  public function testMagicMethodGet() {
    $record = new PapayaDatabaseRecordLazy_TestProxy();
    $record->setDatabaseAccess($this->getDatabaseAccessFixture());
    $record->activateLazyLoad(array('id' => 42));
    $this->assertEquals(
      'content one',
      $record->content
    );
  }

  /**
  * @covers PapayaDatabaseRecordLazy::__set
  */
  public function testMagicMethodSet() {
    $record = new PapayaDatabaseRecordLazy_TestProxy();
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
  * @covers PapayaDatabaseRecordLazy::__unset
  */
  public function testMagicMethodUnset() {
    $record = new PapayaDatabaseRecordLazy_TestProxy();
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
  * @covers PapayaDatabaseRecordLazy::offsetExists
  */
  public function testOffsetExists() {
    $record = new PapayaDatabaseRecordLazy_TestProxy();
    $record->setDatabaseAccess($this->getDatabaseAccessFixture());
    $record->activateLazyLoad(array('id' => 42));
    $this->assertTrue(isset($record['content']));
  }

  /**
  * @covers PapayaDatabaseRecordLazy::isLoaded
  */
  public function testIsLoaded() {
    $record = new PapayaDatabaseRecordLazy_TestProxy();
    $record->setDatabaseAccess($this->getDatabaseAccessFixture());
    $record->activateLazyLoad(array('id' => 42));
    $this->assertTrue($record->isLoaded());
  }

  /*************************
  * Fixtures
  *************************/

  private function getDatabaseAccessFixture() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
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
class PapayaDatabaseRecordLazy_TestProxy extends PapayaDatabaseRecordLazy {

  protected $_fields = array(
    'id' => 'field_id',
    'content' => 'field_content'
  );

  protected $_tableName = 'sampletable';
}
