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

  class TreeTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Database\Records\Tree::_loadRecords
     * @covers \Papaya\Database\Records\Tree::getIterator
     */
    public function testLoadAndIterateRoot() {
      $records = new Tree_TestProxy();
      $records->setDatabaseAccess($this->getDatabaseFixture());
      $this->assertTrue($records->load());
      $this->assertEquals(
        array(
          1 => array(
            'id' => 1,
            'parent_id' => 0,
            'title' => 'One'
          ),
          2 => array(
            'id' => 2,
            'parent_id' => 0,
            'title' => 'Two'
          ),
        ),
        iterator_to_array($records)
      );
    }

    /**
     * @covers \Papaya\Database\Records\Tree::_loadRecords
     * @covers \Papaya\Database\Records\Tree::getIterator
     */
    public function testLoadAndIterateAll() {
      $records = new Tree_TestProxy();
      $records->setDatabaseAccess($this->getDatabaseFixture());
      $this->assertTrue($records->load());
      $this->assertEquals(
        array(
          1 => array(
            'id' => 1,
            'parent_id' => 0,
            'title' => 'One'
          ),
          3 => array(
            'id' => 3,
            'parent_id' => 1,
            'title' => 'Tree'
          ),
          2 => array(
            'id' => 2,
            'parent_id' => 0,
            'title' => 'Two'
          ),
        ),
        iterator_to_array(
          new \RecursiveIteratorIterator($records, \RecursiveIteratorIterator::SELF_FIRST)
        )
      );
    }

    /**
     * @covers \Papaya\Database\Records\Tree::_loadRecords
     * @covers \Papaya\Database\Records\Tree::getIterator
     */
    public function testLoadAndIterateLeafs() {
      $records = new Tree_TestProxy();
      $records->setDatabaseAccess($this->getDatabaseFixture());
      $this->assertTrue($records->load());
      $this->assertEquals(
        array(
          3 => array(
            'id' => 3,
            'parent_id' => 1,
            'title' => 'Tree'
          ),
          2 => array(
            'id' => 2,
            'parent_id' => 0,
            'title' => 'Two'
          ),
        ),
        iterator_to_array(
          new \RecursiveIteratorIterator($records)
        )
      );
    }

    /**
     * @covers \Papaya\Database\Records\Tree::_loadRecords
     * @covers \Papaya\Database\Records\Tree::getIterator
     */
    public function testLoadWithInvalidIdentifierExpectingException() {
      $records = new Tree_TestProxy();
      $records->_identifierProperties = array();
      $records->setDatabaseAccess($this->getDatabaseFixture());
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('Identifier properties needed to link children to parents.');
      $records->load();
    }

    /**
     * @covers \Papaya\Database\Records\Tree::load
     * @covers \Papaya\Database\Records\Tree::_loadRecords
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
      $records = new Tree_TestProxy();
      $records->setDatabaseAccess($databaseAccess);
      $this->assertFalse($records->load(42));
    }

    /************************
     * Fixtures
     ************************/

    public function getDatabaseFixture() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->any())
        ->method('fetchRow')
        ->will(
          $this->onConsecutiveCalls(
            array(
              'field_id' => 1,
              'field_parent_id' => 0,
              'field_title' => 'One'
            ),
            array(
              'field_id' => 2,
              'field_parent_id' => 0,
              'field_title' => 'Two'
            ),
            array(
              'field_id' => 3,
              'field_parent_id' => 1,
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

  class Tree_TestProxy extends Tree {

    public /** @noinspection PropertyInitializationFlawsInspection */
      $_identifierProperties = array('id');

    protected $_fields = array(
      'id' => 'field_id',
      'parent_id' => 'field_parent_id',
      'title' => 'field_title'
    );

    protected $_tableName = 'tablename';
  }
}
