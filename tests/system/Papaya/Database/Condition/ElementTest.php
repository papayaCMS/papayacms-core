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

  class ElementTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testConstructor() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Element_TestProxy($group);
      $this->assertSame($group, $element->getParent());
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testConstructorWithField() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Element_TestProxy($group, 'sample');
      $this->assertEquals('sample', $element->getField());
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testConstructorWithOperator() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Element_TestProxy($group, NULL, '=');
      $this->assertAttributeEquals('=', '_operator', $element);
    }

    /**
     * @covers \Papaya\Database\Condition\Element
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
        ->willReturn($databaseAccess);
      $element = new Element_TestProxy($group);
      $this->assertSame($databaseAccess, $element->getDatabaseAccess());
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testGetMapping() {
      $mapping = $this
        ->getMockBuilder(\Papaya\Database\Interfaces\Mapping::class)
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
        ->willReturn($mapping);
      $element = new Element_TestProxy($group);
      $this->assertSame($mapping, $element->getMapping());
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testGetMappingExpectingNull() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Element_TestProxy($group);
      $this->assertNull($element->getMapping());
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testMagicMethodToString() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('getSqlCondition')
        ->willReturn('sql string');
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->once())
        ->method('getDatabaseAccess')
        ->willReturn($databaseAccess);
      $element = new Element_TestProxy($group, 'field');
      $this->assertEquals(
        'sql string', (string)$element
      );
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testMapFieldName() {
      $mapping = $this
        ->getMockBuilder(\Papaya\Database\Interfaces\Mapping::class)
        ->disableOriginalConstructor()
        ->getMock();
      $mapping
        ->expects($this->once())
        ->method('getField')
        ->with('field')
        ->willReturn('mapped_field');
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->once())
        ->method('getMapping')
        ->willReturn($mapping);
      $element = new Element_TestProxy($group);
      $this->assertEquals('mapped_field', $element->mapFieldName('field'));
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testMapFieldNameWithoutMapping() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Element_TestProxy($group);
      $this->assertEquals('field', $element->mapFieldName('field'));
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testMapFieldNameWithInvalidMappingExpectingException() {
      $mapping = $this
        ->getMockBuilder(\Papaya\Database\Interfaces\Mapping::class)
        ->disableOriginalConstructor()
        ->getMock();
      $mapping
        ->expects($this->once())
        ->method('getField')
        ->with('field')
        ->willReturn('');
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->once())
        ->method('getMapping')
        ->willReturn($mapping);
      $element = new Element_TestProxy($group);
      $this->expectException(\LogicException::class);
      $element->mapFieldName('field');
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testMapFieldNameWithEmptyFieldNameException() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Element_TestProxy($group);
      $this->expectException(\LogicException::class);
      $element->mapFieldName('');
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testGetSqlWithScalar() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('getSqlCondition')
        ->willReturnMap(
          [
            [['parent_field' => 21], NULL, '=', 'parent_field = 21']
          ]
        );

      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->any())
        ->method('getDatabaseAccess')
        ->willReturn($databaseAccess);

      $condition = new Element_TestProxy($group, 'parent_field', 21, '=');

      $this->assertEquals(
        'parent_field = 21',
        $condition->getSql()
      );
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testGetSqlWithMultipleFields() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->exactly(2))
        ->method('getSqlCondition')
        ->willReturnMap(
          [
            [['field_one' => 21], NULL, '=', 'field_one = 21'],
            [['field_two' => 21], NULL, '=', 'field_two = 21']
          ]
        );

      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->any())
        ->method('getDatabaseAccess')
        ->willReturn($databaseAccess);

      $condition = new Element_TestProxy($group, ['field_one', 'field_two'], 21, '=');

      $this->assertEquals(
        ' (field_one = 21 AND field_two = 21) ',
        $condition->getSql()
      );
    }

    /**
     * @covers \Papaya\Database\Condition\Element
     */
    public function testGetSqlWithList() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('getSqlCondition')
        ->willReturnMap(
          [
            [
              ['parent_field' => [21, 42]], NULL, '=', 'parent_field IN (21, 42)'
            ]
          ]
        );

      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->any())
        ->method('getDatabaseAccess')
        ->willReturn($databaseAccess);

      $condition = new Element_TestProxy(
        $group, 'parent_field', [21, 42], '='
      );

      $this->assertEquals(
        'parent_field IN (21, 42)',
        $condition->getSql()
      );
    }

    /**
     * @covers \Papaya\Database\Condition\Element
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
        ->willReturn($databaseAccess);

      $condition = new Element_TestProxy($group, '', NULL, '=');
      $this->expectException(\LogicException::class);
      $condition->getSql();
    }

    /**
     * @covers \Papaya\Database\Condition\Element
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
        ->willReturn($databaseAccess);

      $condition = new Element_TestProxy($group, '', NULL);

      $this->assertEquals('', $condition->getSql(TRUE));
    }
  }

  class Element_TestProxy extends Element {

    public function mapFieldName($value) {
      return parent::mapFieldName($value);
    }
  }
}
