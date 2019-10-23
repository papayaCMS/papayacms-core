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

namespace Papaya\Database\Condition;

require_once __DIR__.'/../../../../bootstrap.php';

class GeneratorTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Database\Condition\Generator
   */
  public function testConstructor() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $generator = new Generator($databaseAccess);
    $condition = $generator->fromArray([]);
    $this->assertInstanceOf(Group::class, $condition);
    $this->assertSame($databaseAccess, $condition->getDatabaseAccess());
  }

  /**
   * @covers \Papaya\Database\Condition\Generator
   */
  public function testConstructorWithInterfaceDatabaseAccess() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Accessible $parent */
    $parent = $this->createMock(\Papaya\Database\Accessible::class);
    $parent
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $generator = new Generator($parent);
    $condition = $generator->fromArray([]);
    $this->assertNull($condition->getParent());
    $this->assertSame($databaseAccess, $condition->getDatabaseAccess());
  }

  /**
   * @covers \Papaya\Database\Condition\Generator
   */
  public function testConstructorWithInvalidParent() {
    $this->expectException(\InvalidArgumentException::class);
    /** @noinspection PhpParamsInspection */
    new Generator(new \stdClass());
  }

  /**
   * @covers \Papaya\Database\Condition\Generator
   */
  public function testFromArrayWithSimpleEqualsFilter() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(['field' => 'value'])
      ->will($this->returnValue("field = 'value'"));
    $generator = new Generator($databaseAccess);

    $condition = $generator->fromArray(['field' => 'value']);
    $this->assertEquals(
      "(field = 'value')", (string)$condition
    );
  }

  /**
   * @covers \Papaya\Database\Condition\Generator
   */
  public function testFromArrayWithFieldMapping() {
    $mapping = $this->createMock(\Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('field')
      ->will($this->returnValue('mapped_field'));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(['mapped_field' => 'value'])
      ->will($this->returnValue("mapped_field = 'value'"));

    $generator = new Generator($databaseAccess, $mapping);

    $condition = $generator->fromArray(['field' => 'value']);
    $this->assertEquals(
      "(mapped_field = 'value')", $condition->getSql()
    );
  }

  /**
   * @covers \Papaya\Database\Condition\Generator
   */
  public function testFromArrayWithFieldMappingReturnsNoFieldname() {
    $mapping = $this->createMock(\Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('field')
      ->will($this->returnValue(NULL));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('getSqlCondition');

    $generator = new Generator($databaseAccess, $mapping);

    $condition = $generator->fromArray(['field' => 'value']);
    $this->assertEquals(
      '', (string)$condition
    );
  }

  /**
   * @covers \Papaya\Database\Condition\Generator
   */
  public function testFromArrayWithConditionInAnd() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          [
            [['field_one' => 'value'], NULL, '=', "field_one = 'value'"],
            [['field_two' => 'value'], NULL, '=', "field_two = 'value'"]
          ]
        )
      );
    $generator = new Generator($databaseAccess);

    $condition = $generator->fromArray(
      [
        ',and' => [
          'field_one' => 'value',
          'field_two' => 'value'
        ]
      ]
    );
    $this->assertEquals(
      "((field_one = 'value' AND field_two = 'value'))", (string)$condition
    );
  }

  /**
   * @covers \Papaya\Database\Condition\Generator
   */
  public function testFromArrayWithConditionInOr() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          [
            [['field_one' => 'value'], NULL, '=', "field_one = 'value'"],
            [['field_two' => 'value'], NULL, '=', "field_two = 'value'"]
          ]
        )
      );
    $generator = new Generator($databaseAccess);

    $condition = $generator->fromArray(
      [
        ',or' => [
          'field_one,equal' => 'value',
          'field_two' => 'value'
        ]
      ]
    );
    $this->assertEquals(
      "((field_one = 'value' OR field_two = 'value'))", (string)$condition
    );
  }

  /**
   * @covers \Papaya\Database\Condition\Generator
   */
  public function testFromArrayWithConditionConnectedByOr() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->willReturnMap(
        [
          [['field_one' => 'value'], NULL, '=', "field_one = 'value'"],
          [['field_two' => 'value'], NULL, '=', "field_two = 'value'"]
        ]
      );
    $generator = new Generator($databaseAccess);

    $condition = $generator->fromArray(
      [
        'or' => [
          'field_one,equal' => 'value',
          'field_two' => 'value'
        ]
      ]
    );
    $this->assertEquals(
      "((field_one = 'value' OR field_two = 'value'))", (string)$condition
    );
  }

  /**
   * @covers \Papaya\Database\Condition\Generator
   */
  public function testFromArrayWithConditionInNot() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          [
            [['field_one' => 'value'], NULL, '=', "field_one = 'value'"],
            [['field_two' => 'value'], NULL, '=', "field_two = 'value'"]
          ]
        )
      );
    $generator = new Generator($databaseAccess);

    $condition = $generator->fromArray(
      [
        ',not' => [
          'field_one,equal' => 'value',
          'field_two' => 'value'
        ]
      ]
    );
    $this->assertEquals(
      "(NOT(field_one = 'value' AND field_two = 'value'))", (string)$condition
    );
  }

  /**
   * @covers       \Papaya\Database\Condition\Generator
   * @dataProvider provideFilterSamples
   * @param string $expected
   * @param array $filter
   */
  public function testSimpleFiltersWithScalars($expected, array $filter) {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->will($this->returnCallback([$this, 'callbackGetSqlCondition']));
    $generator = new Generator($databaseAccess);
    $this->assertEquals($expected, (string)$generator->fromArray($filter));
  }

  public function callbackGetSqlCondition(
    /** @noinspection PhpUnusedParameterInspection */
    $filter, $value, $operator
  ) {
    return key($filter).' '.$operator.' '.current($filter);
  }

  public static function provideFilterSamples() {
    return [
      ['(foo = bar)', ['foo' => 'bar']],
      ['(foo > bar)', ['foo,greater' => 'bar']],
      ['(foo < bar)', ['foo,less' => 'bar']],
      ['(foo >= bar)', ['foo,greaterOrEqual' => 'bar']],
      ['(foo <= bar)', ['foo,lessOrEqual' => 'bar']],
      ['(foo != bar)', ['foo,notEqual' => 'bar']],
      ['(foo != bar)', ['notEqual:foo' => 'bar']],
      ['(((MATCH (foo) AGAINST (\'bar\'))))', ['match:foo' => 'bar']],
      ['(((MATCH (f1,f2) AGAINST (\'bar\'))))', ['match:f1,f2' => 'bar']],
      [
        '(((MATCH (field) AGAINST (\'foo\')) AND (MATCH (field) AGAINST (\'bar\'))))',
        ['match:field' => 'foo bar']
      ],
      [
        '((MATCH (foo) AGAINST (\' ( +bar) \' IN BOOLEAN MODE)))', ['match-boolean:foo' => 'bar']
      ],
      ['((((foo LIKE \'%foo%\') OR (bar LIKE \'%foo%\'))))', ['match-contains:foo,bar' => 'foo']]
    ];
  }

}
