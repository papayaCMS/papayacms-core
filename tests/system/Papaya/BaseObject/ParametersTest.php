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

/** @noinspection PhpIllegalArrayKeyTypeInspection */
namespace Papaya\BaseObject;
require_once __DIR__.'/../../../bootstrap.php';

class ParametersTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\BaseObject\Parameters::__construct
   */
  public function testConstructor() {
    $parameters = new Parameters(array('foo' => 'bar'));
    $this->assertEquals(
      array('foo' => 'bar'),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::__construct
   */
  public function testConstructorWithRecursiveArray() {
    $parameters = new Parameters(array('foobar' => array('foo' => 'bar')));
    $this->assertEquals(
      array('foobar' => array('foo' => 'bar')),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::merge
   */
  public function testMergeWithArray() {
    $parameters = new Parameters();
    $parameters->merge(array('foo' => 'bar'));
    $parameters->merge(array('bar' => 'foo'));
    $this->assertEquals(
      array(
        'foo' => 'bar',
        'bar' => 'foo'
      ),
      (array)$parameters
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::merge
   */
  public function testMergeWithInvalidArgument() {
    $parameters = new Parameters();
    $this->expectException(\UnexpectedValueException::class);
    /** @noinspection PhpParamsInspection */
    $parameters->merge('foo');
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::merge
   */
  public function testMergeWithObject() {
    $parametersFirst = new Parameters();
    $parametersSecond = new Parameters();
    $parametersFirst->merge(array('foo' => 'bar'));
    $parametersSecond->merge(array('bar' => 'foo'));
    $parametersFirst->merge($parametersSecond);
    $this->assertEquals(
      array(
        'foo' => 'bar',
        'bar' => 'foo'
      ),
      (array)$parametersFirst
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::assign
   */
  public function testAssignReplacesElements() {
    $parameters = new Parameters(
      array(
        'foo' => array(21),
        'bar' => ''
      )
    );
    $parameters->assign(array('foo' => array(42)));
    $this->assertEquals(
      array(
        'foo' => array(42),
        'bar' => ''
      ),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::has
   */
  public function testHasExpectingTrue() {
    $parameters = new Parameters(array('foo' => 'bar'));
    $this->assertTrue($parameters->has('foo'));
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::has
   */
  public function testHasExpectingFalse() {
    $parameters = new Parameters();
    $this->assertFalse($parameters->has('foo'));
  }

  /**
   * @covers       \Papaya\BaseObject\Parameters::get
   * @dataProvider provideOffsetsAndDefaultValues
   * @param string $name
   * @param mixed $defaultValue
   * @param mixed $expected
   */
  public function testGet($name, $defaultValue, $expected) {
    $parameters = new Parameters($this->getSampleArray());
    $this->assertSame(
      $expected,
      $parameters->get($name, $defaultValue)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::get
   */
  public function testGetWithObjectDefaultValueExpectingParameterValue() {
    $defaultValue = $this
      ->getMockBuilder(\Papaya\UI\Text::class)
      ->disableOriginalConstructor()
      ->getMock();
    $parameters = new Parameters();
    $parameters->merge(
      array(
        'sample' => 'success'
      )
    );
    $this->assertSame(
      'success',
      $parameters->get('sample', $defaultValue)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::get
   */
  public function testGetWithObjectDefaultValueExpectingDefaultValue() {
    $defaultValue = $this
      ->getMockBuilder(\Papaya\UI\Text::class)
      ->setMethods(array('__toString'))
      ->setConstructorArgs(array(' '))
      ->getMock();
    $defaultValue
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('success'));
    $parameters = new Parameters();
    $parameters->merge(
      array(
        'sample' => array('failed')
      )
    );
    $this->assertSame(
      'success',
      $parameters->get('sample', $defaultValue)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::get
   */
  public function testGetWithFilter() {
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('42'))
      ->will($this->returnValue(42));
    $parameters = new Parameters();
    $parameters->merge(
      array(
        'integer' => '42'
      )
    );
    $this->assertSame(
      42,
      $parameters->get('integer', 0, $filter)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::get
   */
  public function testGetWithFilterExpectingDefaultValue() {
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('42'))
      ->will($this->returnValue(NULL));
    $parameters = new Parameters();
    $parameters->merge(
      array(
        'integer' => '42'
      )
    );
    $this->assertSame(
      23,
      $parameters->get('integer', 23, $filter)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::clear
   */
  public function testClear() {
    $parameters = new Parameters(array('foo' => 'bar'));
    $parameters->clear();
    $this->assertCount(0, $parameters);
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetExists
   */
  public function testIssetWithNonExistingOffsetExpectingFalse() {
    $parameters = new Parameters();
    $this->assertFalse(isset($parameters['foo']));
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetSet
   * @covers \Papaya\BaseObject\Parameters::offsetGet
   */
  public function testGetAfterSet() {
    $parameters = new Parameters();
    $parameters['foo'] = 'bar';
    $this->assertEquals('bar', $parameters['foo']);
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetGet
   */
  public function testGetNestedParameter() {
    $parameters = new Parameters(array('foo' => array('bar' => 42)));
    $this->assertEquals(42, $parameters['foo']['bar']);
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetGet
   */
  public function testOffsetGetNestedParameterUsingArrayOffset() {
    $parameters = new Parameters(array('foo' => array('bar' => 42)));
    $this->assertEquals(42, $parameters[array('foo', 'bar')]);
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetSet
   */
  public function testOffsetSetNestedParameterUsingArrayOffset() {
    $parameters = new Parameters();
    $parameters[array('foo', 'bar')] = 42;
    $this->assertEquals(
      array('foo' => array('bar' => 42)),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetSet
   */
  public function testOffsetSetNestedParameterUsingArrayOffsetWithSingleElement() {
    $parameters = new Parameters();
    $parameters[array('foo')] = 42;
    $this->assertEquals(
      array('foo' => 42),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetSet
   */
  public function testOffsetSetNestedParameterUsingEmptyKeys() {
    $parameters = new Parameters();
    $parameters[array('foo', 'bar', '', '')] = 42;
    $this->assertEquals(
      array('foo' => array('bar' => array(0 => array(0 => 42)))),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetSet
   */
  public function testOffsetSetNestedParameterOverridesExistingParameter() {
    $parameters = new Parameters(array('foobar' => array('foo' => 'bar')));
    $parameters[array('foobar', 'foo', 'bar')] = 42;
    $this->assertEquals(
      array('foobar' => array('foo' => array('bar' => 42))),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetSet
   */
  public function testOffsetSetNestedParameterOverridesExistingParameterWithNewArray() {
    $parameters = new Parameters(array('foobar' => 'bar'));
    $parameters[array('foobar', 'bar')] = 42;
    $this->assertEquals(
      array('foobar' => array('bar' => 42)),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetSet
   */
  public function testOffsetSetNestedParameterOverridesExistingParameterWithNewArrayAppend() {
    $parameters = new Parameters(array('foobar' => 'bar'));
    $parameters[array('foobar', '')] = 42;
    $this->assertEquals(
      array('foobar' => array(42)),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::offsetSet
   */
  public function testOffsetSetWithTraversableAsValue() {
    $parameters = new Parameters();
    $parameters[] = new \ArrayIterator(array(21, 42));
    $this->assertEquals(
      array(0 => array(21, 42)),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers       \Papaya\BaseObject\Parameters::offsetGet
   * @dataProvider provideOffsetsAndValues
   * @param string $name
   * @param mixed $expected
   */
  public function testOffsetGet($name, $expected) {
    $parameters = new Parameters($this->getSampleArray());
    $this->assertEquals(
      $expected,
      $parameters[$name]
    );
  }

  /**
   * @covers       \Papaya\BaseObject\Parameters::offsetExists
   * @dataProvider provideExistingOffsets
   * @param string $name
   */
  public function testOffsetExistsExpectingTrue($name) {
    $parameters = new Parameters($this->getSampleArray());
    $this->assertTrue(isset($parameters[$name]));
  }

  /**
   * @covers       \Papaya\BaseObject\Parameters::offsetExists
   * @dataProvider provideNonExistingOffsets
   * @param string $name
   */
  public function testOffsetExistsExpectingFalse($name) {
    $parameters = new Parameters($this->getSampleArray());
    $this->assertFalse(isset($parameters[$name]));
  }

  /**
   * @covers       \Papaya\BaseObject\Parameters::offsetUnset
   * @dataProvider provideExistingOffsets
   * @param string $name
   */
  public function testOffsetUnset($name) {
    $parameters = new Parameters($this->getSampleArray());
    unset($parameters[$name]);
    $this->assertFalse(isset($parameters[$name]));
  }

  /**
   * @covers       \Papaya\BaseObject\Parameters::offsetUnset
   * @dataProvider provideNonExistingOffsets
   * @param string $name
   */
  public function testOffsetUnsetWithNonExistingParameters($name) {
    $parameters = new Parameters($this->getSampleArray());
    unset($parameters[$name]);
    $this->assertFalse(isset($parameters[$name]));
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::getChecksum
   */
  public function testGetChecksum() {
    $parameters = new Parameters(array('foo' => 'bar'));
    $this->assertEquals(
      '49a3696adf0fbfacc12383a2d7400d51', $parameters->getChecksum()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Parameters::getChecksum
   */
  public function testGetChecksumNormalizesArray() {
    $parameters = new Parameters(array('foo' => 'bar', 'bar' => 42));
    $this->assertEquals(
      'e486614c5fe79b1235ead81bd5fc7292', $parameters->getChecksum()
    );
  }

  /*********************************
   * Fixtures
   ********************************/

  public function getSampleArray() {
    return array(
      'string' => 'test',
      'integer' => '42',
      'float' => '42.21',
      'array' => array('1', '2', '3'),
      'group' => array(
        'element1' => 1,
        'element2' => 2,
        'subgroup' => array(
          'subelement' => 3
        )
      )
    );
  }

  /*********************************
   * Data Provider
   ********************************/

  public static function provideOffsetsAndDefaultValues() {
    return array(
      'no-existing, return default value' =>
        array('NON_EXISTING', 'default', 'default'),
      'string, no default, return string value' =>
        array('integer', NULL, '42'),
      'array, no default, return array value' =>
        array('array', NULL, array('1', '2', '3')),
      'string default, return value' =>
        array('string', '', 'test'),
      'string, integer default, return typecasted value' =>
        array('integer', 0, 42),
      'string, float default, return typecasted value' =>
        array('float', 0.0, 42.21),
      'array, array default, return array value' =>
        array('array', array(), array('1', '2', '3')),
      'array, array default, return default' =>
        array('string', array('23'), array('23')),
      'array, integer default, return default' =>
        array('array', 1, 1),
      'sub element' =>
        array(array('group', 'element2'), 0, 2),
      'no-existing sub element' =>
        array(array('group', 'element99'), 0, 0),
      'no-existing group' =>
        array(array('integer', 'element2'), 1, 1)
    );
  }

  public static function provideOffsetsAndValues() {
    return array(
      'no-existing, return NULL' =>
        array('NON_EXISTING', NULL),
      'existing, return integer' =>
        array('integer', 42),
      'existing, return array' =>
        array('array', array('1', '2', '3')),
      'existing, return string' =>
        array('string', 'test'),
      'sub element' =>
        array(array('group', 'element2'), 2),
      'no-existing sub element' =>
        array(array('group', 'element99'), NULL),
      'no-existing group' =>
        array(array('integer', 'element2'), NULL)
    );
  }

  public static function provideExistingOffsets() {
    return array(
      array('integer'),
      array('array'),
      array('string'),
      array(array('group', 'element2')),
      array(array('group', 'subgroup', 'subelement'))
    );
  }

  public static function provideNonExistingOffsets() {
    return array(
      array('', NULL),
      array('NON_EXISTING', NULL),
      array(array('NON_EXISTING'), NULL),
      array(array('NON_EXISTING', 'NON_EXISTING'), NULL),
      array(array('group', 'element99'), NULL),
      array(array('group', 'element1', 'NON_EXISTING'), NULL),
      array(array('group', 'subgroup', 'subelement', 'NON_EXISTING'), NULL),
      array(array('integer', 'element2'), NULL)
    );
  }
}
