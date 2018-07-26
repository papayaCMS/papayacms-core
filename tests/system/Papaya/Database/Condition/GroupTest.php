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

use Papaya\Database\Condition\Group;
use Papaya\Database\Interfaces\Access;
use Papaya\Database\Interfaces\Mapping;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseConditionGroupTest extends PapayaTestCase {

  /**
   * @covers Group
   */
  public function testConstructorWithDatabaseAccess() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $this->assertNull($group->getParent());
    $this->assertSame($databaseAccess, $group->getDatabaseAccess());
  }

  /**
   * @covers Group
   */
  public function testConstructorWithMapping() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $mapping = $this
      ->getMockBuilder(Mapping::class)
      ->getMock();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess, $mapping);
    $this->assertSame($mapping, $group->getMapping());
  }

  /**
   * @covers Group
   */
  public function testConstructorWithInterfaceDatabaseAccess() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    /** @var PHPUnit_Framework_MockObject_MockObject|Access $parent */
    $parent = $this->createMock(Access::class);
    $parent
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $group = new PapayaDatabaseConditionGroup_TestProxy($parent);
    $this->assertNull($group->getParent());
    $this->assertSame($databaseAccess, $group->getDatabaseAccess());
  }

  /**
   * @covers Group
   */
  public function testConstructorWithGroup() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    /** @var PHPUnit_Framework_MockObject_MockObject|Group $parent */
    $parent = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $parent
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $group = new PapayaDatabaseConditionGroup_TestProxy($parent);
    $this->assertSame($parent, $group->getParent());
    $this->assertSame($databaseAccess, $group->getDatabaseAccess());
  }

  /**
   * @covers Group
   */
  public function testConstructorWithInvalidParent() {
    $this->expectException(InvalidArgumentException::class);
    /** @noinspection PhpParamsInspection */
    new PapayaDatabaseConditionGroup_TestProxy(new stdClass());
  }

  /**
   * @covers Group
   */
  public function testEnd() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Group $parent */
    $parent = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group = new PapayaDatabaseConditionGroup_TestProxy($parent);
    $this->assertSame($parent, $group->end());
  }

  /**
   * @covers Group
   */
  public function testCountWhileEmpty() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $this->assertCount(0, $group);
  }

  /**
   * @covers Group
   */
  public function testCountTwoElements() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $group
      ->isEqual('foo', 'bar')
      ->isEqual('bar', 'foo');
    $this->assertCount(2, $group);
  }

  /**
   * @covers Group
   */
  public function testGetIteratorWhileEmpty() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $this->assertEquals(array(), iterator_to_array($group));
  }

  /**
   * @covers Group
   */
  public function testGetIteratorWithOneSubGroup() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $subGroup = $group->logicalAnd();
    $this->assertCount(1, iterator_to_array($group));
    $this->assertSame($group, $subGroup->end());
  }

  /**
   * @covers Group
   */
  public function testGetSqlWithIsEqual() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field' => 'value'))
      ->will($this->returnValue("field = 'value'"));

    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $group->isEqual('field', 'value');
    $this->assertEquals("(field = 'value')", $group->getSql());
  }

  /**
   * @covers Group
   */
  public function testGetSqlWithTwoSubgroupsOneOfThemEmpty() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->with(array('field' => 'value'))
      ->will($this->returnValue("field = 'value'"));

    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $group
      ->logicalOr()
        ->end()
      ->logicalOr()
        ->isEqual('field', 'value')
        ->isEqual('field', 'value');

    $this->assertEquals("((field = 'value' OR field = 'value'))", $group->getSql());
  }

  /**
   * @covers Group
   */
  public function testGetSqlWithNotAndTwoConditions() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->with(array('field' => 'value'))
      ->will($this->returnValue("field = 'value'"));

    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $group
      ->logicalNot()
        ->isEqual('field', 'value')
        ->isEqual('field', 'value');

    $this->assertEquals("(NOT(field = 'value' AND field = 'value'))", $group->getSql());
  }

  /**
   * @covers Group
   */
  public function testUnknownConditionCallExpectingException() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $this->expectException(BadMethodCallException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    $group->isUnknownCondition();
  }
}

class PapayaDatabaseConditionGroup_TestProxy extends Group {

}
