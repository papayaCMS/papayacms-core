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

namespace Papaya\Database\BaseObject {

  require_once __DIR__.'/../../../../bootstrap.php';

  class RecordsTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Database\BaseObject\Records::getIterator
     */
    public function testGetIterator() {
      $list = new Records_TestProxy();
      $iterator = $list->getIterator();
      $this->assertEquals(
        array(
          array(
            'property1' => '1_1',
            'property2' => '1_2'
          ),
          array(
            'property1' => '2_1',
            'property2' => '2_2'
          )
        ),
        $iterator->getArrayCopy()
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::count
     */
    public function testCount() {
      $list = new Records_TestProxy();
      $this->assertCount(2, $list);
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::countAll
     */
    public function testCountAll() {
      $list = new Records_TestProxy();
      $this->assertEquals(2, $list->countAll());
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::countAll
     */
    public function testCountAllReturnsAbsoluteCount() {
      $list = new Records_TestProxy();
      $list->_recordCount = 42;
      $this->assertEquals(
        42, $list->countAll()
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::item
     */
    public function testItemExpectingArray() {
      $list = new Records_TestProxy();
      $this->assertEquals(
        array(
          'property1' => '1_1',
          'property2' => '1_2'
        ),
        $list->item(0)
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::item
     */
    public function testItemExpectingNull() {
      $list = new Records_TestProxy();
      $this->assertNull($list->item(-99));
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::itemAt
     */
    public function testItemAtExpectingArray() {
      $list = new Records_TestProxy();
      $this->assertEquals(
        array(
          'property1' => '1_1',
          'property2' => '1_2'
        ),
        $list->itemAt(0)
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::itemAt
     */
    public function testItemAtNegativePositionExpectingArray() {
      $list = new Records_TestProxy();
      $this->assertEquals(
        array(
          'property1' => '2_1',
          'property2' => '2_2'
        ),
        $list->itemAt(-1)
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::assign
     */
    public function testAssign() {
      $list = new Records_TestProxy();
      $list->assign(
        array(
          '21' => array(
            'property1' => '1.1',
            'property2' => '1.2',
            'property_unknown' => 'failed'
          ),
          '42' => array(
            'property1' => '2.1',
            'property2' => '2.2',
            'property_unknown' => 'failed'
          )
        )
      );
      $this->assertAttributeEquals(
        array(
          '21' => array(
            'property1' => '1.1',
            'property2' => '1.2'
          ),
          '42' => array(
            'property1' => '2.1',
            'property2' => '2.2'
          )
        ),
        '_records',
        $list
      );
      $this->assertEquals(2, $list->count());
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::_loadRecords
     */
    public function testLoadRecords() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->any())
        ->method('fetchRow')
        ->with($this->equalTo(\Papaya\Database\Result::FETCH_ASSOC))
        ->will(
          $this->onConsecutiveCalls(
            array('field1' => 'Hello', 'field2' => 'World'),
            FALSE
          )
        );
      $databaseResult
        ->expects($this->any())
        ->method('absCount')
        ->will($this->returnValue(42));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with(
          $this->equalTo('SQL'),
          $this->equalTo(array('1', '2')),
          $this->equalTo(10),
          $this->equalTo(5)
        )
        ->will($this->returnValue($databaseResult));
      $list = new Records_TestProxy();
      $list->setDatabaseAccess($databaseAccess);
      $this->assertTrue($list->_loadRecords('SQL', array('1', '2'), 'field1', 10, 5));
      $this->assertAttributeEquals(
        array('Hello' => array('property1' => 'Hello', 'property2' => 'World')),
        '_records',
        $list
      );
      $this->assertAttributeEquals(
        42, '_recordCount', $list
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::_loadRecords
     */
    public function testLoadRecordsExpectingFalse() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with(
          $this->equalTo('SQL'),
          $this->equalTo(array('1', '2')),
          $this->equalTo(NULL),
          $this->equalTo(NULL)
        )
        ->will($this->returnValue(FALSE));
      $list = new Records_TestProxy();
      $list->setDatabaseAccess($databaseAccess);
      $this->assertFalse($list->_loadRecords('SQL', array('1', '2')));
      $this->assertAttributeEquals(array(), '_records', $list);
      $this->assertAttributeEquals(0, '_recordCount', $list);
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::_fetchRecords
     */
    public function testFetchRecords() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Result $databaseResult */
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->any())
        ->method('fetchRow')
        ->with($this->equalTo(\Papaya\Database\Result::FETCH_ASSOC))
        ->will(
          $this->onConsecutiveCalls(
            array('field1' => 'Hello', 'field2' => 'World'),
            FALSE
          )
        );
      $list = new Records_TestProxy();
      $list->_fetchRecords($databaseResult);
      $this->assertAttributeEquals(
        array(array('property1' => 'Hello', 'property2' => 'World')),
        '_records',
        $list
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Records::_fetchRecords
     */
    public function testFetchRecordsWithIndex() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Result $databaseResult */
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->any())
        ->method('fetchRow')
        ->with($this->equalTo(\Papaya\Database\Result::FETCH_ASSOC))
        ->will(
          $this->onConsecutiveCalls(
            array('field1' => 'Hello', 'field2' => 'World'),
            FALSE
          )
        );
      $list = new Records_TestProxy();
      $list->_fetchRecords($databaseResult, 'field1');
      $this->assertAttributeEquals(
        array('Hello' => array('property1' => 'Hello', 'property2' => 'World')),
        '_records',
        $list
      );
    }
  }

  /**
   * Proxy class with some predefined values
   */
  class Records_TestProxy extends Records {

    protected $_records = array(
      array(
        'property1' => '1_1',
        'property2' => '1_2'
      ),
      array(
        'property1' => '2_1',
        'property2' => '2_2'
      )
    );

    protected $_fieldMapping = array(
      'field1' => 'property1',
      'field2' => 'property2'
    );

    public $_recordCount;

    public function _loadRecords(
      $sql, $parameters, $idProperty = NULL, $limit = NULL, $offset = NULL
    ) {
      return parent::_loadRecords($sql, $parameters, $idProperty, $limit, $offset);
    }

    public function _fetchRecords($databaseResult, $idField = '') {
      parent::_fetchRecords($databaseResult, $idField);
    }
  }
}

