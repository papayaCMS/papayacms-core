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
namespace Papaya\BaseObject {
  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\BaseObject\Parameters
   */
  class ParametersTest extends \Papaya\TestCase {

    public function testConstructor() {
      $parameters = new Parameters(['foo' => 'bar']);
      $this->assertEquals(
        ['foo' => 'bar'],
        iterator_to_array($parameters)
      );
    }

    public function testConstructorWithRecursiveArray() {
      $parameters = new Parameters(['foobar' => ['foo' => 'bar']]);
      $this->assertEquals(
        ['foobar' => ['foo' => 'bar']],
        iterator_to_array($parameters)
      );
    }

    public function testMergeWithArray() {
      $parameters = new Parameters();
      $parameters->merge(['foo' => 'bar']);
      $parameters->merge(['bar' => 'foo']);
      $this->assertEquals(
        [
          'foo' => 'bar',
          'bar' => 'foo'
        ],
        (array)$parameters
      );
    }

    public function testMergeWithInvalidArgument() {
      $parameters = new Parameters();
      $this->expectException(\TypeError::class);
      /** @noinspection PhpParamsInspection */
      $parameters->merge('foo');
    }

    public function testMergeWithObject() {
      $parametersFirst = new Parameters();
      $parametersSecond = new Parameters();
      $parametersFirst->merge(['foo' => 'bar']);
      $parametersSecond->merge(['bar' => 'foo']);
      $parametersFirst->merge($parametersSecond);
      $this->assertEquals(
        [
          'foo' => 'bar',
          'bar' => 'foo'
        ],
        (array)$parametersFirst
      );
    }

    public function testAssignReplacesElements() {
      $parameters = new Parameters(
        [
          'foo' => [21],
          'bar' => ''
        ]
      );
      $parameters->assign(['foo' => [42]]);
      $this->assertEquals(
        [
          'foo' => [42],
          'bar' => ''
        ],
        iterator_to_array($parameters)
      );
    }

    public function testHasExpectingTrue() {
      $parameters = new Parameters(['foo' => 'bar']);
      $this->assertTrue($parameters->has('foo'));
    }

    public function testHasExpectingFalse() {
      $parameters = new Parameters();
      $this->assertFalse($parameters->has('foo'));
    }

    /**
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

    public function testGetStringFromArrayReturningDefaultValue() {
      $parameters = new Parameters(
        ['foo' => ['bar'=> 42]]
      );
      $this->assertSame(
        'default',
        $parameters->get('foo', 'default')
      );
    }

    public function testGetWithObjectDefaultValueExpectingParameterValue() {
      $defaultValue = $this
        ->getMockBuilder(\Papaya\UI\Text::class)
        ->disableOriginalConstructor()
        ->getMock();
      $parameters = new Parameters();
      $parameters->merge(
        [
          'sample' => 'success'
        ]
      );
      $this->assertSame(
        'success',
        $parameters->get('sample', $defaultValue)
      );
    }

    public function testGetWithStringCastableObjectDefaultValueExpectingDefaultValue() {
      $defaultValue = $this
        ->getMockBuilder(\Papaya\UI\Text::class)
        ->setMethods(['__toString'])
        ->setConstructorArgs([' '])
        ->getMock();
      $defaultValue
        ->expects($this->once())
        ->method('__toString')
        ->will($this->returnValue('success'));
      $parameters = new Parameters();
      $parameters->merge(
        [
          'sample' => ['failed']
        ]
      );
      $this->assertSame(
        'success',
        $parameters->get('sample', $defaultValue)
      );
    }

    public function testGetWithObjectDefaultValueExpectingDefaultValue() {
      $defaultValue = new \stdClass();
      $parameters = new Parameters();
      $parameters->merge(
        [
          'sample' => ['failed']
        ]
      );
      $this->assertSame(
        $defaultValue,
        $parameters->get('sample', $defaultValue)
      );
    }

    public function testGetWithFilter() {
      $filter = $this->createMock(\Papaya\Filter::class);
      $filter
        ->expects($this->once())
        ->method('filter')
        ->with($this->equalTo('42'))
        ->will($this->returnValue(42));
      $parameters = new Parameters();
      $parameters->merge(
        [
          'integer' => '42'
        ]
      );
      $this->assertSame(
        42,
        $parameters->get('integer', 0, $filter)
      );
    }

    public function testGetWithFilterExpectingDefaultValue() {
      $filter = $this->createMock(\Papaya\Filter::class);
      $filter
        ->expects($this->once())
        ->method('filter')
        ->with($this->equalTo('42'))
        ->will($this->returnValue(NULL));
      $parameters = new Parameters();
      $parameters->merge(
        [
          'integer' => '42'
        ]
      );
      $this->assertSame(
        23,
        $parameters->get('integer', 23, $filter)
      );
    }

    public function testClear() {
      $parameters = new Parameters(['foo' => 'bar']);
      $parameters->clear();
      $this->assertCount(0, $parameters);
    }

    public function testIssetWithNonExistingOffsetExpectingFalse() {
      $parameters = new Parameters();
      $this->assertFalse(isset($parameters['foo']));
    }

    public function testGetAfterSet() {
      $parameters = new Parameters();
      $parameters['foo'] = 'bar';
      $this->assertEquals('bar', $parameters['foo']);
    }

    public function testGetNestedParameter() {
      $parameters = new Parameters(['foo' => ['bar' => 42]]);
      $this->assertEquals(42, $parameters['foo']['bar']);
    }

    public function testOffsetGetNestedParameterUsingArrayOffset() {
      $parameters = new Parameters(['foo' => ['bar' => 42]]);
      $this->assertEquals(42, $parameters[['foo', 'bar']]);
    }

    public function testOffsetSetNestedParameterUsingArrayOffset() {
      $parameters = new Parameters();
      $parameters[['foo', 'bar']] = 42;
      $this->assertEquals(
        ['foo' => ['bar' => 42]],
        iterator_to_array($parameters)
      );
    }

    public function testOffsetSetNestedParameterUsingArrayOffsetWithSingleElement() {
      $parameters = new Parameters();
      $parameters[['foo']] = 42;
      $this->assertEquals(
        ['foo' => 42],
        iterator_to_array($parameters)
      );
    }

    public function testOffsetSetNestedParameterUsingEmptyKeys() {
      $parameters = new Parameters();
      $parameters[['foo', 'bar', '', '']] = 42;
      $this->assertEquals(
        ['foo' => ['bar' => [0 => [0 => 42]]]],
        iterator_to_array($parameters)
      );
    }

    public function testOffsetSetNestedParameterOverridesExistingParameter() {
      $parameters = new Parameters(['foobar' => ['foo' => 'bar']]);
      $parameters[['foobar', 'foo', 'bar']] = 42;
      $this->assertEquals(
        ['foobar' => ['foo' => ['bar' => 42]]],
        iterator_to_array($parameters)
      );
    }

    public function testOffsetSetNestedParameterOverridesExistingParameterWithNewArray() {
      $parameters = new Parameters(['foobar' => 'bar']);
      $parameters[['foobar', 'bar']] = 42;
      $this->assertEquals(
        ['foobar' => ['bar' => 42]],
        iterator_to_array($parameters)
      );
    }

    public function testOffsetSetNestedParameterOverridesExistingParameterWithNewArrayAppend() {
      $parameters = new Parameters(['foobar' => 'bar']);
      $parameters[['foobar', '']] = 42;
      $this->assertEquals(
        ['foobar' => [42]],
        iterator_to_array($parameters)
      );
    }

    public function testOffsetSetWithTraversableAsValue() {
      $parameters = new Parameters();
      $parameters[] = new \ArrayIterator([21, 42]);
      $this->assertEquals(
        [0 => [21, 42]],
        iterator_to_array($parameters)
      );
    }

    /**
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
     * @dataProvider provideExistingOffsets
     * @param string $name
     */
    public function testOffsetExistsExpectingTrue($name) {
      $parameters = new Parameters($this->getSampleArray());
      $this->assertTrue(isset($parameters[$name]));
    }

    /**
     * @dataProvider provideNonExistingOffsets
     * @param string $name
     */
    public function testOffsetExistsExpectingFalse($name) {
      $parameters = new Parameters($this->getSampleArray());
      $this->assertFalse(isset($parameters[$name]));
    }

    /**
     * @dataProvider provideExistingOffsets
     * @param string $name
     */
    public function testOffsetUnset($name) {
      $parameters = new Parameters($this->getSampleArray());
      unset($parameters[$name]);
      $this->assertFalse(isset($parameters[$name]));
    }

    /**
     * @dataProvider provideNonExistingOffsets
     * @param string $name
     */
    public function testOffsetUnsetWithNonExistingParameters($name) {
      $parameters = new Parameters($this->getSampleArray());
      unset($parameters[$name]);
      $this->assertFalse(isset($parameters[$name]));
    }

    public function testGetChecksum() {
      $parameters = new Parameters(['foo' => 'bar']);
      $this->assertEquals(
        '49a3696adf0fbfacc12383a2d7400d51', $parameters->getChecksum()
      );
    }

    public function testGetChecksumNormalizesArray() {
      $parameters = new Parameters(['foo' => 'bar', 'bar' => 42]);
      $this->assertEquals(
        'e486614c5fe79b1235ead81bd5fc7292', $parameters->getChecksum()
      );
    }

    public function testWithDefaults() {
      $parameters = (new Parameters())->withDefaults(['foo' => 'bar']);
      $this->assertEquals(
        'bar', $parameters['foo']
      );
    }

    /*********************************
     * Fixtures
     ********************************/

    public function getSampleArray() {
      return [
        'string' => 'test',
        'integer' => '42',
        'float' => '42.21',
        'array' => ['1', '2', '3'],
        'group' => [
          'element1' => 1,
          'element2' => 2,
          'subgroup' => [
            'subelement' => 3
          ]
        ]
      ];
    }

    /*********************************
     * Data Provider
     ********************************/

    public static function provideOffsetsAndDefaultValues() {
      return [
        'no-existing, return default value' =>
          ['NON_EXISTING', 'default', 'default'],
        'string, no default, return string value' =>
          ['integer', NULL, '42'],
        'array, no default, return array value' =>
          ['array', NULL, ['1', '2', '3']],
        'string default, return value' =>
          ['string', '', 'test'],
        'string, integer default, return typecasted value' =>
          ['integer', 0, 42],
        'string, float default, return typecasted value' =>
          ['float', 0.0, 42.21],
        'array, array default, return array value' =>
          ['array', [], ['1', '2', '3']],
        'array, array default, return default' =>
          ['string', ['23'], ['23']],
        'array, integer default, return default' =>
          ['array', 1, 1],
        'sub element' =>
          [['group', 'element2'], 0, 2],
        'no-existing sub element' =>
          [['group', 'element99'], 0, 0],
        'no-existing group' =>
          [['integer', 'element2'], 1, 1]
      ];
    }

    public static function provideOffsetsAndValues() {
      return [
        'no-existing, return NULL' =>
          ['NON_EXISTING', NULL],
        'existing, return integer' =>
          ['integer', 42],
        'existing, return array' =>
          ['array', ['1', '2', '3']],
        'existing, return string' =>
          ['string', 'test'],
        'sub element' =>
          [['group', 'element2'], 2],
        'no-existing sub element' =>
          [['group', 'element99'], NULL],
        'no-existing group' =>
          [['integer', 'element2'], NULL]
      ];
    }

    public static function provideExistingOffsets() {
      return [
        ['integer'],
        ['array'],
        ['string'],
        [['group', 'element2']],
        [['group', 'subgroup', 'subelement']]
      ];
    }

    public static function provideNonExistingOffsets() {
      return [
        ['', NULL],
        ['NON_EXISTING', NULL],
        [['NON_EXISTING'], NULL],
        [['NON_EXISTING', 'NON_EXISTING'], NULL],
        [['group', 'element99'], NULL],
        [['group', 'element1', 'NON_EXISTING'], NULL],
        [['group', 'subgroup', 'subelement', 'NON_EXISTING'], NULL],
        [['integer', 'element2'], NULL]
      ];
    }
  }
}
