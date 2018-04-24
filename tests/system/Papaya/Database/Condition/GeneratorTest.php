<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseConditionGeneratorTest extends PapayaTestCase {

  /**
   * @covers PapayaDatabaseConditionGenerator
   */
  public function testConstructor() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->disableOriginalConstructor()
      ->getMock();
    $generator = new PapayaDatabaseConditionGenerator($databaseAccess);
    $condition = $generator->fromArray(array());
    $this->assertInstanceOf(PapayaDatabaseConditionGroup::class, $condition);
    $this->assertSame($databaseAccess, $condition->getDatabaseAccess());
  }

  /**
   * @covers PapayaDatabaseConditionGenerator
   */
  public function testConstructorWithInterfaceDatabaseAccess() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->disableOriginalConstructor()
      ->getMock();
    $parent = $this
      ->createMock(PapayaDatabaseInterfaceAccess::class);
    $parent
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $generator = new PapayaDatabaseConditionGenerator($parent);
    $condition = $generator->fromArray(array());
    $this->assertNull($condition->getParent());
    $this->assertSame($databaseAccess, $condition->getDatabaseAccess());
  }

  /**
   * @covers PapayaDatabaseConditionGenerator
   */
  public function testConstructorWithInvalidParent() {
    $this->expectException(InvalidArgumentException::class);
    $group = new PapayaDatabaseConditionGenerator(new stdClass());
  }

  /**
   * @covers PapayaDatabaseConditionGenerator
   */
  public function testFromArrayWithSimpleEqualsFilter() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field' => 'value'))
      ->will($this->returnValue("field = 'value'"));
    $generator = new PapayaDatabaseConditionGenerator($databaseAccess);

    $condition = $generator->fromArray(array('field' => 'value'));
    $this->assertEquals(
      "(field = 'value')", (string)$condition
    );
  }

  /**
   * @covers PapayaDatabaseConditionGenerator
   */
  public function testFromArrayWithFieldMapping() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('field')
      ->will($this->returnValue('mapped_field'));
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('mapped_field' => 'value'))
      ->will($this->returnValue("mapped_field = 'value'"));

    $generator = new PapayaDatabaseConditionGenerator($databaseAccess, $mapping);

    $condition = $generator->fromArray(array('field' => 'value'));
    $this->assertEquals(
      "(mapped_field = 'value')", $condition->getSql()
    );
  }

  /**
   * @covers PapayaDatabaseConditionGenerator
   */
  public function testFromArrayWithFieldMappingReturnsNoFieldname() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('field')
      ->will($this->returnValue(NULL));
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->never())
      ->method('getSqlCondition');

    $generator = new PapayaDatabaseConditionGenerator($databaseAccess, $mapping);

    $condition = $generator->fromArray(array('field' => 'value'));
    $this->assertEquals(
      "", (string)$condition
    );
  }

  /**
   * @covers PapayaDatabaseConditionGenerator
   */
  public function testFromArrayWithConditionInAnd() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
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
    $generator = new PapayaDatabaseConditionGenerator($databaseAccess);

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
   * @covers PapayaDatabaseConditionGenerator
   */
  public function testFromArrayWithConditionInOr() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
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
    $generator = new PapayaDatabaseConditionGenerator($databaseAccess);

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
   * @covers PapayaDatabaseConditionGenerator
   */
  public function testFromArrayWithConditionInNot() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
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
    $generator = new PapayaDatabaseConditionGenerator($databaseAccess);

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
   * @covers PapayaDatabaseConditionGenerator
   * @dataProvider provideFilterSamples
   */
  public function testSimpleFiltersWithScalars($expected, $filter) {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->will($this->returnCallback(array($this, 'callbackGetSqlCondition')));
    $generator = new PapayaDatabaseConditionGenerator($databaseAccess);
    $this->assertEquals($expected, (string)$generator->fromArray($filter));
  }

  public function callbackGetSqlCondition($filter, $value, $operator) {
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
