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

namespace Papaya\Request\Parameters;
require_once __DIR__.'/../../../../bootstrap.php';

class QueryStringTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Request\Parameters\QueryString::__construct
   */
  public function testConstructor() {
    $query = new QueryString(':');
    $this->assertAttributeEquals(
      ':', '_separator', $query
    );
  }

  /**
   * @covers       \Papaya\Request\Parameters\QueryString::setSeparator
   * @dataProvider provideValidSeparators
   * @param string $separator
   * @param string $expected
   */
  public function testSetSeparator($separator, $expected) {
    $query = new QueryString();
    $query->setSeparator($separator);
    $this->assertAttributeEquals(
      $expected, '_separator', $query
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\QueryString::setSeparator
   */
  public function testSetSeparatorWithInvalidValueExpectingException() {
    $query = new QueryString();
    $this->expectException(\InvalidArgumentException::class);
    $query->setSeparator('I');
  }

  /**
   * @covers \Papaya\Request\Parameters\QueryString::values
   */
  public function testValuesReadImplicitCreate() {
    $query = new QueryString();
    $this->assertInstanceOf(
      \Papaya\Request\Parameters::class, $query->values()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\QueryString::values
   */
  public function testValuesWrite() {
    $query = new QueryString();
    $parameters = new \Papaya\Request\Parameters();
    $query->values($parameters);
    $this->assertAttributeSame(
      $parameters, '_values', $query
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\QueryString::values
   */
  public function testValuesRead() {
    $query = new QueryString();
    $parameters = new \Papaya\Request\Parameters();
    $query->values($parameters);
    $this->assertSame(
      $parameters, $query->values()
    );
  }

  /**
   * @covers       \Papaya\Request\Parameters\QueryString::setString
   * @covers       \Papaya\Request\Parameters\QueryString::_decode
   * @covers       \Papaya\Request\Parameters\QueryString::_prepare
   * @dataProvider provideQueryStringsForDecode
   * @param string $queryString
   * @param bool $stripSlashes
   * @param array $expected
   */
  public function testSetString($queryString, $stripSlashes, $expected) {
    $query = new QueryString(':');
    $this->assertSame(
      $query, $query->setString($queryString, $stripSlashes)
    );
    $this->assertEquals(
      $expected, $query->values()->toArray()
    );
  }

  /**
   * @covers       \Papaya\Request\Parameters\QueryString::GetString
   * @covers       \Papaya\Request\Parameters\QueryString::_encode
   * @dataProvider provideValuesForEncode
   * @param array $values
   * @param string $groupSeparator
   * @param string $expected
   */
  public function testGetString($values, $groupSeparator, $expected) {
    $query = new QueryString($groupSeparator);
    $parameters = new \Papaya\Request\Parameters();
    $parameters->merge($values);
    $query->values($parameters);
    $this->assertEquals(
      $expected, $query->getString()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\QueryString::GetString
   * @covers \Papaya\Request\Parameters\QueryString::_encode
   */
  public function testGetStringWithObjectArgument() {
    $mock = $this
      ->getMockBuilder(\stdClass::class)
      ->setMethods(array('__toString'))
      ->getMock();
    $mock
      ->expects($this->any())
      ->method('__toString')
      ->will($this->returnValue('bar'));
    $query = new QueryString('[]');
    $parameters = new \Papaya\Request\Parameters();
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
        array()
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
        array('foo' => new \stdClass, 'bar' => 1),
        '[]',
        'bar=1'
      )
    );
  }
}
