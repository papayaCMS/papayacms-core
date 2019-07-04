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

namespace Papaya\Database\Condition {

  require_once __DIR__.'/../../../../bootstrap.php';

  class GroupTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testConstructorWithDatabaseAccess() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $group = new Group_TestProxy($databaseAccess);
      $this->assertNull($group->getParent());
      $this->assertSame($databaseAccess, $group->getDatabaseAccess());
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testConstructorWithMapping() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $mapping = $this
        ->getMockBuilder(\Papaya\Database\Interfaces\Mapping::class)
        ->getMock();
      $group = new Group_TestProxy($databaseAccess, $mapping);
      $this->assertSame($mapping, $group->getMapping());
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testConstructorWithInterfaceDatabaseAccess() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Accessible $parent */
      $parent = $this->createMock(\Papaya\Database\Accessible::class);
      $parent
        ->expects($this->once())
        ->method('getDatabaseAccess')
        ->will($this->returnValue($databaseAccess));
      $group = new Group_TestProxy($parent);
      $this->assertNull($group->getParent());
      $this->assertSame($databaseAccess, $group->getDatabaseAccess());
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testConstructorWithGroup() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $parent */
      $parent = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $parent
        ->expects($this->once())
        ->method('getDatabaseAccess')
        ->will($this->returnValue($databaseAccess));
      $group = new Group_TestProxy($parent);
      $this->assertSame($parent, $group->getParent());
      $this->assertSame($databaseAccess, $group->getDatabaseAccess());
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testConstructorWithInvalidParent() {
      $this->expectException(\InvalidArgumentException::class);
      /** @noinspection PhpParamsInspection */
      new Group_TestProxy(new \stdClass());
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testEnd() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $parent */
      $parent = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group = new Group_TestProxy($parent);
      $this->assertSame($parent, $group->end());
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testCountWhileEmpty() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $group = new Group_TestProxy($databaseAccess);
      $this->assertCount(0, $group);
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testCountTwoElements() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $group = new Group_TestProxy($databaseAccess);
      $group
        ->isEqual('foo', 'bar')
        ->isEqual('bar', 'foo');
      $this->assertCount(2, $group);
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testGetIteratorWhileEmpty() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $group = new Group_TestProxy($databaseAccess);
      $this->assertEquals(array(), iterator_to_array($group));
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testGetIteratorWithOneSubGroup() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $group = new Group_TestProxy($databaseAccess);
      $subGroup = $group->logicalAnd();
      $this->assertCount(1, iterator_to_array($group));
      $this->assertSame($group, $subGroup->end());
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testGetSqlWithIsEqual() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('getSqlCondition')
        ->with(array('field' => 'value'))
        ->will($this->returnValue("field = 'value'"));

      $group = new Group_TestProxy($databaseAccess);
      $group->isEqual('field', 'value');
      $this->assertEquals("(field = 'value')", $group->getSql());
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testGetSqlWithTwoSubgroupsOneOfThemEmpty() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->any())
        ->method('getSqlCondition')
        ->with(array('field' => 'value'))
        ->will($this->returnValue("field = 'value'"));

      $group = new Group_TestProxy($databaseAccess);
      $group
        ->logicalOr()
        ->end()
        ->logicalOr()
        ->isEqual('field', 'value')
        ->isEqual('field', 'value');

      $this->assertEquals("((field = 'value' OR field = 'value'))", $group->getSql());
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testGetSqlWithNotAndTwoConditions() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->any())
        ->method('getSqlCondition')
        ->with(array('field' => 'value'))
        ->will($this->returnValue("field = 'value'"));

      $group = new Group_TestProxy($databaseAccess);
      $group
        ->logicalNot()
        ->isEqual('field', 'value')
        ->isEqual('field', 'value');

      $this->assertEquals("(NOT(field = 'value' AND field = 'value'))", $group->getSql());
    }

    /**
     * @covers \Papaya\Database\Condition\Group
     */
    public function testUnknownConditionCallExpectingException() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $group = new Group_TestProxy($databaseAccess);
      $this->expectException(\BadMethodCallException::class);
      /** @noinspection PhpUndefinedMethodInspection */
      $group->isUnknownCondition();
    }
  }

  class Group_TestProxy extends Group {

  }
}
