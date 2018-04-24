<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaRequestParametersQueryTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParametersQuery::__construct
  */
  public function testConstructor() {
    $query = new PapayaRequestParametersQuery(':');
    $this->assertAttributeEquals(
      ':', '_separator', $query
    );
  }

  /**
  * @covers PapayaRequestParametersQuery::setSeparator
  * @dataProvider provideValidSeparators
  */
  public function testSetSeparator($separator, $expected) {
    $query = new PapayaRequestParametersQuery();
    $query->setSeparator($separator);
    $this->assertAttributeEquals(
      $expected, '_separator', $query
    );
  }

  /**
  * @covers PapayaRequestParametersQuery::setSeparator
  */
  public function testSetSeparatorWithInvalidValueExpectingException() {
    $query = new PapayaRequestParametersQuery();
    $this->expectException(InvalidArgumentException::class);
    $query->setSeparator('I');
  }

  /**
  * @covers PapayaRequestParametersQuery::values
  */
  public function testValuesReadImplicitCreate() {
    $query = new PapayaRequestParametersQuery();
    $this->assertInstanceOf(
      PapayaRequestParameters::class, $query->values()
    );
  }

  /**
  * @covers PapayaRequestParametersQuery::values
  */
  public function testValuesWrite() {
    $query = new PapayaRequestParametersQuery();
    $parameters = new PapayaRequestParameters();
    $query->values($parameters);
    $this->assertAttributeSame(
      $parameters, '_values', $query
    );
  }

  /**
  * @covers PapayaRequestParametersQuery::values
  */
  public function testValuesRead() {
    $query = new PapayaRequestParametersQuery();
    $parameters = new PapayaRequestParameters();
    $query->values($parameters);
    $this->assertSame(
      $parameters, $query->values()
    );
  }

  /**
  * @covers PapayaRequestParametersQuery::setString
  * @covers PapayaRequestParametersQuery::_decode
  * @covers PapayaRequestParametersQuery::_prepare
  * @dataProvider provideQueryStringsForDecode
  */
  public function testSetString($queryString, $stripSlashes, $expected) {
    $query = new PapayaRequestParametersQuery(':');
    $this->assertSame(
      $query, $query->setString($queryString, $stripSlashes)
    );
    $this->assertEquals(
      $expected, $query->values()->toArray()
    );
  }

  /**
  * @covers PapayaRequestParametersQuery::GetString
  * @covers PapayaRequestParametersQuery::_encode
  * @dataProvider provideValuesForEncode
  */
  public function testGetString($values, $groupSeparator, $expected) {
    $query = new PapayaRequestParametersQuery($groupSeparator);
    $parameters = new PapayaRequestParameters();
    $parameters->merge($values);
    $query->values($parameters);
    $this->assertEquals(
      $expected, $query->getString()
    );
  }

  /**
  * @covers PapayaRequestParametersQuery::GetString
  * @covers PapayaRequestParametersQuery::_encode
  */
  public function testGetStringWithObjectArgument() {
    $mock = $this->getMock(stdClass::class, array('__toString'));
    $mock
      ->expects($this->any())
      ->method('__toString')
      ->will($this->returnValue('bar'));
    $query = new PapayaRequestParametersQuery('[]');
    $parameters = new PapayaRequestParameters();
    $parameters->merge(array('foo' => $mock));
    $query->values($parameters);
    $this->assertEquals(
      'foo=bar', $query->getString()
    );
  }

  /*************************
  * Data Provider
  *************************/

  public static function provideValidSeparators() {
    return array(
      array('/', '/'),
      array(':', ':'),
      array('', ''),
      array('[]', ''),
    );
  }

  public static function provideQueryStringsForDecode() {
    return array(
      array(
        'arg=value',
        FALSE,
        array(
          'arg' => 'value'
        )
      ),
      array(
        '',
        FALSE,
        array(
        )
      ),
      array(
        'boolarg',
        FALSE,
        array(
          'boolarg' => TRUE
        )
      ),
      array(
        'arg=value&boolarg',
        FALSE,
        array(
          'arg' => 'value',
          'boolarg' => TRUE
        )
      ),
      array(
        'group[arg1]=value1&group[arg2]=value2',
        FALSE,
        array(
          'group' => array(
            'arg1' => 'value1',
            'arg2' => 'value2'
          )
        )
      ),
      array(
        'group:arg1=value1&group[arg2]=value2',
        FALSE,
        array(
          'group' => array(
            'arg1' => 'value1',
            'arg2' => 'value2'
          )
        )
      ),
      array(
        'group:arg1=value1&group:subgroup:arg2=value2',
        FALSE,
        array(
          'group' => array(
            'arg1' => 'value1',
            'subgroup' => array(
              'arg2' => 'value2'
            )
          )
        )
      ),
      array(
        'array[]=1&array[]=2&array[][]=3_1',
        FALSE,
        array(
          'array' => array(
            0 => '1',
            1 => '2',
            2 => array(
              0 => '3_1'
            )
          )
        )
      ),
      array(
        'array:23=1&array::=2',
        FALSE,
        array(
          'array' => array(
            23 => '1',
            24 => array('2')
          )
        )
      ),
      array(
        'array[23]=FOO&array:23:=2',
        FALSE,
        array(
          'array' => array(
            23 => array('2')
          )
        )
      ),
      array(
        'arg=\\"value\\"',
        FALSE,
        array(
          'arg' => '\\"value\\"'
        )
      ),
      array(
        'arg=\\"value\\"',
        TRUE,
        array(
          'arg' => '"value"'
        )
      ),
    );
  }

  public static function provideValuesForEncode() {
    return array(
      array(
        array('cmd' => 'show', 'id' => 1),
        '[]',
        'cmd=show&id=1'
      ),
      array(
        array('tt' => array('cmd' => 'show', 'id' => 1)),
        '[]',
        'tt[cmd]=show&tt[id]=1'
      ),
      array(
        array('tt' => array('cmd' => 'show', 'id' => 1)),
        ':',
        'tt:cmd=show&tt:id=1'
      ),
      array(
        array('b' => 2, 'a' => 1),
        ':',
        'a=1&b=2'
      ),
      array(
        array('foo' => array(), 'bar' => 1),
        '[]',
        'bar=1'
      ),
      array(
        array('foo' => new stdClass, 'bar' => 1),
        '[]',
        'bar=1'
      )
    );
  }
}
