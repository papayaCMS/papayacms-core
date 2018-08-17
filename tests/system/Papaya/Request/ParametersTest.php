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

namespace Papaya\Request;
require_once __DIR__.'/../../../bootstrap.php';

class ParametersTest extends \Papaya\TestCase {

  public function testCreateFromString() {
    $parameters = Parameters::createFromString('foo=42&bar=21');
    $this->assertEquals(42, $parameters->get('foo'));
    $this->assertEquals(21, $parameters->get('bar'));
  }

  /**
   * @covers \Papaya\Request\Parameters::toArray
   */
  public function testToArray() {
    $parameters = new Parameters();
    $parameters->merge(
      array('group' => array('foo' => 'bar'))
    );
    $this->assertEquals(
      array('group' => array('foo' => 'bar')),
      $parameters->toArray()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::getGroup
   */
  public function testGetGroup() {
    $parameters = new Parameters();
    $parameters->merge(
      array('group' => array('foo' => 'bar'))
    );
    $this->assertInstanceOf(
      Parameters::class,
      $group = $parameters->getGroup('group')
    );
    $this->assertEquals(
      array('foo' => 'bar'),
      (array)$group
    );
  }

  /**
   * @covers       \Papaya\Request\Parameters::set
   * @covers       \Papaya\Request\Parameters::_parseParameterName
   * @dataProvider setDataProvider
   * @param array $before
   * @param string $parameter
   * @param mixed $value
   * @param array $expected
   */
  public function testSet($before, $parameter, $value, $expected) {
    $parameters = new Parameters();
    $parameters->merge($before);
    $parameters->set($parameter, $value);
    $this->assertEquals(
      $expected,
      (array)$parameters
    );
  }

  /**
   * Set and parameter object
   *
   * @covers \Papaya\Request\Parameters::set
   */
  public function testSetWithObject() {
    $parametersFirst = new Parameters();
    $parametersSecond = new Parameters();
    $parametersFirst->merge(
      array('foo' => 'bar', 'group' => array('e1' => 'fail'))
    );
    $parametersSecond->merge(array('e2' => 'success'));
    $parametersFirst->set('group', $parametersSecond);
    $this->assertEquals(
      array('foo' => 'bar', 'group' => array('e2' => 'success')),
      (array)$parametersFirst
    );
  }

  /**
   * @covers       \Papaya\Request\Parameters::has
   * @dataProvider hasDataProviderExpectingTrue
   * @param string $parameterName
   * @param mixed $parameterData
   */
  public function testHasExpectingTrue($parameterName, $parameterData) {
    $parameters = new Parameters();
    $parameters->set($parameterData);
    $this->assertTrue($parameters->has($parameterName));
  }

  /**
   * @covers       \Papaya\Request\Parameters::has
   * @dataProvider hasDataProviderExpectingFalse
   * @param string $parameterName
   * @param mixed $parameterData
   */
  public function testHasExpectingFalse($parameterName, $parameterData) {
    $parameters = new Parameters();
    $parameters->set($parameterData);
    $this->assertFalse($parameters->has($parameterName));
  }

  /**
   * @covers       \Papaya\Request\Parameters::get
   * @dataProvider getDataProvider
   * @param string $name
   * @param mixed $defaultValue
   * @param mixed $expected
   */
  public function testGet($name, $defaultValue, $expected) {
    $parameters = new Parameters();
    $parameters->merge(
      array(
        'string' => 'test',
        'integer' => '42',
        'float' => '42.21',
        'array' => array('1', '2', '3'),
        'group' => array(
          'element1' => 1,
          'element2' => 2
        )
      )
    );
    $this->assertSame(
      $expected,
      $parameters->get($name, $defaultValue)
    );
  }

  /**
   * @covers       \Papaya\Request\Parameters::remove
   * @dataProvider removeDataProvider
   * @param array $before
   * @param string $parameter
   * @param array $expected
   */
  public function testRemove($before, $parameter, $expected) {
    $parameters = new Parameters();
    $parameters->merge($before);
    $parameters->remove($parameter);
    $this->assertEquals(
      $expected,
      (array)$parameters
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::getQueryString
   */
  public function testGetQueryString() {
    $parameters = new Parameters();
    $parameters->merge(array('group' => array('foo' => 'bar')));
    $this->assertEquals(
      'group/foo=bar',
      $parameters->getQueryString('/')
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::setQueryString
   */
  public function testSetQueryString() {
    $parameters = new Parameters();
    $parameters->setQueryString('group/foo=bar');
    $this->assertEquals(
      array('group' => array('foo' => 'bar')),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers       \Papaya\Request\Parameters::getList
   * @covers       \Papaya\Request\Parameters::flattenArray
   * @dataProvider getListDataProvider
   * @param array $expected
   * @param array $parameterArray
   * @param string $separator
   */
  public function testGetList($expected, $parameterArray, $separator) {
    $parameters = new Parameters($parameterArray);
    $this->assertSame(
      $expected, $parameters->getList($separator)
    );
  }

  /**
   * @covers       \Papaya\Request\Parameters::prepareParameter
   * @dataProvider prepareParameterDataProvider
   * @param mixed $value
   * @param bool $stripSlashes
   * @param array|string $expected
   */
  public function testPrepareParameter($value, $stripSlashes, $expected) {
    $parameters = new Parameters();
    $this->assertEquals(
      $expected,
      $parameters->prepareParameter($value, $stripSlashes)
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::offsetSet
   */
  public function testArrayAccessOffsetSet() {
    $parameters = new Parameters();
    $parameters['test'] = 'sample';
    $this->assertEquals(
      array('test' => 'sample'),
      (array)$parameters
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::offsetExists
   */
  public function testArrayAccessOffsetExistsExpectingTrue() {
    $parameters = new Parameters(array('foo' => 'bar'));
    $this->assertTrue(
      isset($parameters['foo'])
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::offsetExists
   */
  public function testArrayAccessOffsetExistsExpectingFalse() {
    $parameters = new Parameters();
    $this->assertFalse(
      isset($parameters['foo'])
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::offsetUnset
   */
  public function testArrayAccessOffsetUnset() {
    $parameters = new Parameters(array('foo' => 'bar'));
    unset($parameters['foo']);
    $this->assertEquals(
      array(),
      (array)$parameters
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::offsetGet
   */
  public function testArrayAccessOffsetGet() {
    $parameters = new Parameters(array('foo' => 'bar'));
    $this->assertEquals(
      'bar', $parameters['foo']
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::offsetGet
   */
  public function testArrayAccessOffsetGetWithArray() {
    $parameters = new Parameters(array('foo' => array('bar' => 'foobar')));
    $this->assertInstanceOf(
      Parameters::class, $parameters['foo']
    );
    $this->assertEquals(
      'foobar', $parameters['foo']['bar']
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::count
   */
  public function testCountable() {
    $parameters = new Parameters(array('foo' => 'bar', 'bar' => 'foo'));
    $this->assertCount(2, $parameters);
  }

  /**
   * @covers \Papaya\Request\Parameters::isEmpty
   */
  public function testIsEmptyExpectingTrue() {
    $parameters = new Parameters();
    $this->assertTrue(
      $parameters->isEmpty()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters::isEmpty
   */
  public function testIsEmptyExpectingFalse() {
    $parameters = new Parameters(array('foo' => 'bar'));
    $this->assertFalse(
      $parameters->isEmpty()
    );
  }

  /*************************************
   * Data Provider
   *************************************/

  public static function setDataProvider() {
    return array(
      array(
        array(),
        'foo',
        'bar',
        array(
          'foo' => 'bar'
        )
      ),
      array(
        array(),
        array('foo' => 'bar'),
        NULL,
        array('foo' => 'bar')
      ),
      array(
        array(),
        array('foo' => 'bar', 'bar' => 'foo'),
        NULL,
        array('foo' => 'bar', 'bar' => 'foo')
      ),
      array(
        array('foo' => 'bar'),
        array('bar' => 'foo'),
        NULL,
        array('foo' => 'bar', 'bar' => 'foo')
      ),
      array(
        array('group' => array('e1' => 1)),
        'group[e2]',
        2,
        array('group' => array('e1' => 1, 'e2' => 2))
      ),
      array(
        array('list' => array(5 => 'foo')),
        'list[]',
        'bar',
        array('list' => array(5 => 'foo', 6 => 'bar')),
      ),
      array(
        array(),
        'foo:bar',
        2,
        array('foo' => array('bar' => 2))
      ),
      array(
        array(),
        'foo:bar',
        2,
        array('foo' => array('bar' => 2))
      ),
      array(
        array(),
        'foo!bar',
        2,
        array('foo' => array('bar' => 2))
      )
    );
  }

  public static function getDataProvider() {
    return array(
      'no-existing, return default value' =>
        array('NON_EXISTING', 'default', 'default'),
      'string, no default, return string value' =>
        array('integer', NULL, '42'),
      'array, no default, return array value' =>
        array('array', NULL, array('1', '2', '3')),
      'string default, return value' =>
        array('string', '', 'test'),
      'string, integer default, return typecast value' =>
        array('integer', 0, 42),
      'string, float default, return typecast value' =>
        array('float', 0.0, 42.21),
      'array, array default, return array value' =>
        array('array', array(), array('1', '2', '3')),
      'array, array default, return default' =>
        array('string', array('23'), array('23')),
      'array, integer default, return default' =>
        array('array', 1, 1),
      'sub element' =>
        array('group[element2]', 0, 2),
      'no-existing group' =>
        array('integer[element2]', 1, 1)
    );
  }

  public static function hasDataProviderExpectingTrue() {
    return array(
      array(
        'foo',
        array('foo' => 'bar')
      ),
      array(
        'foo/bar',
        array('foo[bar]' => 'bar')
      ),
      array(
        'foo[bar]',
        array('foo[bar]' => 'bar')
      )
    );
  }

  public static function hasDataProviderExpectingFalse() {
    return array(
      array(
        'foo',
        array()
      ),
      array(
        'foo/bar',
        array('foo[foobar]' => 'bar')
      ),
      array(
        'foo[bar]',
        array('foo[foobar]' => 'bar')
      )
    );
  }

  public static function removeDataProvider() {
    return array(
      'simple' => array(
        array('foo' => 1, 'bar' => 2),
        'foo',
        array('bar' => 2)
      ),
      'empty name' => array(
        array('foo' => 1, 'bar' => 2),
        '',
        array('foo' => 1, 'bar' => 2)
      ),
      'no-existing param' => array(
        array('bar' => 2),
        'foo',
        array('bar' => 2)
      ),
      'level param' => array(
        array('foo' => array('bar1' => 1, 'bar2' => 2)),
        'foo[bar1]',
        array('foo' => array('bar2' => 2))
      ),
      'no-existing level param' => array(
        array('foo' => 'bar'),
        'foo[bar][foobar]',
        array('foo' => 'bar'),
      ),
      'multiple parameters' => array(
        array('foo' => 1, 'bar' => 2),
        array('foo', 'bar'),
        array()
      )
    );
  }

  public static function prepareParameterDataProvider() {
    return array(
      array('foo', FALSE, 'foo'),
      array(array('foo' => 'bar'), FALSE, array('foo' => 'bar')),
      array(array('foo' => TRUE), FALSE, array('foo' => TRUE)),
      array('\\x', TRUE, 'x'),
      array('\\x', FALSE, '\\x')
    );
  }

  public static function getListDataProvider() {
    return array(
      'simple list' => array(
        array('foo' => 'bar'),
        array('foo' => 'bar'),
        '[]'
      ),
      'parameter group' => array(
        array('foo[bar]' => 'foobar'),
        array('foo' => array('bar' => 'foobar')),
        '[]'
      ),
      'parameter group with list' => array(
        array(
          'foo[bar][foobar][0]' => '21',
          'foo[bar][foobar][1]' => '42'
        ),
        array('foo' => array('bar' => array('foobar' => array(21, 42)))),
        '[]'
      ),
      'parameter group with defined separator' => array(
        array('foo/bar' => 'foobar'),
        array('foo' => array('bar' => 'foobar')),
        '/'
      ),
      'parameter group with numeric keys' => array(
        array(
          'foo/21' => 'foo 21',
          'foo/42' => 'foo 42'
        ),
        array('foo' => array(21 => 'foo 21', 42 => 'foo 42')),
        '/'
      ),
      'list to sort' => array(
        array('a/1' => '1.1', 'a/2' => '1.2', 'b' => '2', 'c' => '3', 'd' => '4'),
        array('d' => 4, 'a' => array(2 => '1.2', 1 => '1.1'), 'c' => 3, 'b' => 2),
        '/'
      )
    );
  }
}
