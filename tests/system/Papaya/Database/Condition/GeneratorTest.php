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

use Papaya\Database\Condition\Generator;
use Papaya\Database\Condition\Group;
use Papaya\Database\Interfaces\Access;
use Papaya\Database\Interfaces\Mapping;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseConditionGeneratorTest extends \PapayaTestCase {

  /**
   * @covers Generator
   */
  public function testConstructor() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $generator = new Generator($databaseAccess);
    $condition = $generator->fromArray(array());
    $this->assertInstanceOf(Group::class, $condition);
    $this->assertSame($databaseAccess, $condition->getDatabaseAccess());
  }

  /**
   * @covers Generator
   */
  public function testConstructorWithInterfaceDatabaseAccess() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    /** @var PHPUnit_Framework_MockObject_MockObject|Access $parent */
    $parent = $this->createMock(Access::class);
    $parent
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $generator = new Generator($parent);
    $condition = $generator->fromArray(array());
    $this->assertNull($condition->getParent());
    $this->assertSame($databaseAccess, $condition->getDatabaseAccess());
  }

  /**
   * @covers Generator
   */
  public function testConstructorWithInvalidParent() {
    $this->expectException(InvalidArgumentException::class);
    /** @noinspection PhpParamsInspection */
    new Generator(new stdClass());
  }

  /**
   * @covers Generator
   */
  public function testFromArrayWithSimpleEqualsFilter() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field' => 'value'))
      ->will($this->returnValue("field = 'value'"));
    $generator = new Generator($databaseAccess);

    $condition = $generator->fromArray(array('field' => 'value'));
    $this->assertEquals(
      "(field = 'value')", (string)$condition
    );
  }

  /**
   * @covers Generator
   */
  public function testFromArrayWithFieldMapping() {
    $mapping = $this->createMock(Mapping::class);
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('field')
      ->will($this->returnValue('mapped_field'));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('mapped_field' => 'value'))
      ->will($this->returnValue("mapped_field = 'value'"));

    $generator = new Generator($databaseAccess, $mapping);

    $condition = $generator->fromArray(array('field' => 'value'));
    $this->assertEquals(
      "(mapped_field = 'value')", $condition->getSql()
    );
  }

  /**
   * @covers Generator
   */
  public function testFromArrayWithFieldMappingReturnsNoFieldname() {
    $mapping = $this->createMock(Mapping::class);
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

    $condition = $generator->fromArray(array('field' => 'value'));
    $this->assertEquals(
      '', (string)$condition
    );
  }

  /**
   * @covers Generator
   */
  public function testFromArrayWithConditionInAnd() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          array(
            array(array('field_one' => 'value'), NULL, '=', "field_one = 'value'"),
            array(array('field_two' => 'value'), NULL, '=', "field_two = 'value'")
          )
        )
      );
    $generator = new Generator($databaseAccess);

    $condition = $generator->fromArray(
      array(
        ',and' => array(
          'field_one' => 'value',
          'field_two' => 'value'
        )
      )
    );
    $this->assertEquals(
      "((field_one = 'value' AND field_two = 'value'))", (string)$condition
    );
  }

  /**
   * @covers Generator
   */
  public function testFromArrayWithConditionInOr() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          array(
            array(array('field_one' => 'value'), NULL, '=', "field_one = 'value'"),
            array(array('field_two' => 'value'), NULL, '=', "field_two = 'value'")
          )
        )
      );
    $generator = new Generator($databaseAccess);

    $condition = $generator->fromArray(
      array(
        ',or' => array(
          'field_one,equal' => 'value',
          'field_two' => 'value'
        )
      )
    );
    $this->assertEquals(
      "((field_one = 'value' OR field_two = 'value'))", (string)$condition
    );
  }

  /**
   * @covers Generator
   */
  public function testFromArrayWithConditionInNot() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          array(
            array(array('field_one' => 'value'), NULL, '=', "field_one = 'value'"),
            array(array('field_two' => 'value'), NULL, '=', "field_two = 'value'")
          )
        )
      );
    $generator = new Generator($databaseAccess);

    $condition = $generator->fromArray(
      array(
        ',not' => array(
          'field_one,equal' => 'value',
          'field_two' => 'value'
        )
      )
    );
    $this->assertEquals(
      "(NOT(field_one = 'value' AND field_two = 'value'))", (string)$condition
    );
  }

  /**
   * @covers       Generator
   * @dataProvider provideFilterSamples
   * @param string $expected
   * @param array $filter
   */
  public function testSimpleFiltersWithScalars($expected, array $filter) {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->will($this->returnCallback(array($this, 'callbackGetSqlCondition')));
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
    return array(
      array('(foo = bar)', array('foo' => 'bar')),
      array('(foo > bar)', array('foo,greater' => 'bar')),
      array('(foo < bar)', array('foo,less' => 'bar')),
      array('(foo >= bar)', array('foo,greaterOrEqual' => 'bar')),
      array('(foo <= bar)', array('foo,lessOrEqual' => 'bar')),
      array('(foo != bar)', array('foo,notEqual' => 'bar')),
      array('(foo != bar)', array('notEqual:foo' => 'bar')),
      array('(((MATCH (foo) AGAINST (\'bar\'))))', array('match:foo' => 'bar')),
      array('(((MATCH (f1,f2) AGAINST (\'bar\'))))', array('match:f1,f2' => 'bar')),
      array(
        '(((MATCH (field) AGAINST (\'foo\')) AND (MATCH (field) AGAINST (\'bar\'))))',
        array('match:field' => 'foo bar')
      ),
      array(
        '((MATCH (foo) AGAINST (\' ( +bar) \' IN BOOLEAN MODE)))', array('match-boolean:foo' => 'bar')
      ),
      array('((((foo LIKE \'%foo%\') OR (bar LIKE \'%foo%\'))))', array('match-contains:foo,bar' => 'foo'))
    );
  }

}
