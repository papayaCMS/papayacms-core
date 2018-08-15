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

use Papaya\Database\Condition\Element;
use Papaya\Database\Condition\Group;
use Papaya\Database\Interfaces\Mapping;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseConditionElementTest extends \PapayaTestCase {

  /**
   * @covers Element
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $element = new \PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertSame($group, $element->getParent());
  }

  /**
   * @covers Element
   */
  public function testConstructorWithField() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $element = new \PapayaDatabaseConditionElement_TestProxy($group, 'sample');
    $this->assertEquals('sample', $element->getField());
  }

  /**
   * @covers Element
   */
  public function testConstructorWithOperator() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $element = new \PapayaDatabaseConditionElement_TestProxy($group, NULL, '=');
    $this->assertAttributeEquals('=', '_operator', $element);
  }

  /**
   * @covers Element
   */
  public function testGetDatabaseAccess() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $element = new \PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertSame($databaseAccess, $element->getDatabaseAccess());
  }

  /**
   * @covers Element
   */
  public function testGetMapping() {
    $mapping = $this
      ->getMockBuilder(Mapping::class)
      ->disableOriginalConstructor()
      ->getMock();
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getMapping')
      ->will($this->returnValue($mapping));
    $element = new \PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertSame($mapping, $element->getMapping());
  }

  /**
   * @covers Element
   */
  public function testGetMappingExpectingNull() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $element = new \PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertNull($element->getMapping());
  }

  /**
   * @covers Element
   */
  public function testMagicMethodToString() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->will($this->returnValue('sql string'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $element = new \PapayaDatabaseConditionElement_TestProxy($group, 'field');
    $this->assertEquals(
      'sql string', (string)$element
    );
  }

  /**
   * @covers Element
   */
  public function testMapFieldName() {
    $mapping = $this
      ->getMockBuilder(Mapping::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('field')
      ->will($this->returnValue('mapped_field'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getMapping')
      ->will($this->returnValue($mapping));
    $element = new \PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertEquals('mapped_field', $element->mapFieldName('field'));
  }

  /**
   * @covers Element
   */
  public function testMapFieldNameWithoutMapping() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $element = new \PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertEquals('field', $element->mapFieldName('field'));
  }

  /**
   * @covers Element
   */
  public function testMapFieldNameWithInvalidMappingExpectingException() {
    $mapping = $this
      ->getMockBuilder(Mapping::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('field')
      ->will($this->returnValue(''));
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getMapping')
      ->will($this->returnValue($mapping));
    $element = new \PapayaDatabaseConditionElement_TestProxy($group);
    $this->expectException(\LogicException::class);
    $element->mapFieldName('field');
  }

  /**
   * @covers Element
   */
  public function testMapFieldNameWithEmptyFieldNameException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $element = new \PapayaDatabaseConditionElement_TestProxy($group);
    $this->expectException(\LogicException::class);
    $element->mapFieldName('');
  }

  /**
   * @covers Element
   */
  public function testGetSqlWithScalar() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          array(
            array(array('parent_field' => 21), NULL, '=', 'parent_field = 21')
          )
        )
      );

    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));

    $condition = new \PapayaDatabaseConditionElement_TestProxy($group, 'parent_field', 21, '=');

    $this->assertEquals(
      'parent_field = 21',
      $condition->getSql()
    );
  }

  /**
   * @covers Element
   */
  public function testGetSqlWithList() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          array(
            array(
              array('parent_field' => array(21, 42)), NULL, '=', 'parent_field IN (21, 42)'
            )
          )
        )
      );

    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));

    $condition = new \PapayaDatabaseConditionElement_TestProxy(
      $group, 'parent_field', array(21, 42), '='
    );

    $this->assertEquals(
      'parent_field IN (21, 42)',
      $condition->getSql()
    );
  }

  /**
   * @covers Element
   */
  public function testGetSqlWithInvalidFieldNameExpectingException() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('getSqlCondition');

    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));

    $condition = new \PapayaDatabaseConditionElement_TestProxy($group, '', NULL, '=');
    $this->expectException(\LogicException::class);
    $condition->getSql();
  }

  /**
   * @covers Element
   */
  public function testGetSqlWithExceptionInSilentMode() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('getSqlCondition');

    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));

    $condition = new \PapayaDatabaseConditionElement_TestProxy($group, '', NULL);

    $this->assertEquals('', $condition->getSql(TRUE));
  }
}

class PapayaDatabaseConditionElement_TestProxy extends Element {

  public function mapFieldName($value) {
    return parent::mapFieldName($value);
  }
}
