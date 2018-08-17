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

/**
 * @covers \Papaya\Request\Parameters\Validator
 */
class ValidatorTest extends \PapayaTestCase {

  public function testConstructor() {
    $parameters = new \Papaya\Request\Parameters();
    $definitions = array(
      array('example', 'default', new \Papaya\Filter\NotEmpty())
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertEquals(
      array(
        'example' => 'default'
      ),
      iterator_to_array($validator)
    );
  }

  public function testConstructorWithArray() {
    $parameters = array('foo' => 'bar');
    $definitions = array(
      array('example', 'default', new \Papaya\Filter\NotEmpty())
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertEquals(
      array(
        'foo' => 'bar'
      ),
      iterator_to_array($this->readAttribute($validator, '_parameters'))
    );
  }

  public function testConstructorWithNamedDefinition() {
    $parameters = new \Papaya\Request\Parameters();
    $definitions = array(
      array(
        'name' => 'example',
        'default' => 'default',
        'filter' => new \Papaya\Filter\NotEmpty()
      )
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertEquals(
      array(
        'example' => 'default'
      ),
      iterator_to_array($validator)
    );
  }

  public function testConstructorWithoutFilter() {
    $parameters = new \Papaya\Request\Parameters();
    $definitions = array(
      array(
        'name' => 'example',
        'default' => 'default'
      )
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertEquals(
      array(
        'example' => 'default'
      ),
      iterator_to_array($validator)
    );
  }

  public function testConstructorWithoutDefault() {
    $parameters = new \Papaya\Request\Parameters();
    $definitions = array(
      array(
        'name' => 'example'
      )
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertEquals(
      array(
        'example' => NULL
      ),
      iterator_to_array($validator)
    );
  }

  public function testConstructorWithValidatorAsSecondParameter() {
    $parameters = new \Papaya\Request\Parameters(
      array('example' => 21)
    );
    $definitions = array(
      array('example', new \Papaya\Filter\IntegerValue(42))
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertEquals(
      array(
        'example' => NULL
      ),
      iterator_to_array($validator)
    );
  }

  public function testValidateWithTwoValues() {
    $parameters = new \Papaya\Request\Parameters(
      array(
        'foo' => '21',
        'bar' => '42'
      )
    );
    $definitions = array(
      array('name' => 'foo'),
      array('name' => 'bar', 23, new \Papaya\Filter\IntegerValue(0, 42))
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertTrue($validator->validate());
    $this->assertEquals(
      array(
        'foo' => 21,
        'bar' => 42
      ),
      iterator_to_array($validator)
    );
  }

  public function testValidateWithInvalidValue() {
    $parameters = new \Papaya\Request\Parameters(
      array(
        'foo' => '21'
      )
    );
    $definitions = array(
      array('foo', 23, new \Papaya\Filter\IntegerValue(42))
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertFalse($validator->validate());
    $this->assertEquals(
      array(
        'foo' => 23
      ),
      iterator_to_array($validator)
    );
    $this->assertArrayHasKey('foo', $validator->getErrors());
  }

  public function testIssetValueExpectingTrue() {
    $parameters = new \Papaya\Request\Parameters(array());
    $definitions = array(
      array('foo', 42)
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertTrue(isset($validator['foo']));
  }

  public function testIssetValueExpectingFalse() {
    $parameters = new \Papaya\Request\Parameters(array());
    $definitions = array(
      array('foo', 42)
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertFalse(isset($validator['bar']));
  }

  public function testGetFetchingValue() {
    $parameters = new \Papaya\Request\Parameters(array('foo' => 21));
    $definitions = array(
      array('foo', 42)
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertEquals(21, $validator->get('foo'));
  }

  public function testOffsetGetFetchingValue() {
    $parameters = new \Papaya\Request\Parameters(array('foo' => 21));
    $definitions = array(
      array('foo', 42)
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertEquals(21, $validator['foo']);
  }

  public function testOffsetGetFetchingDefaultValue() {
    $parameters = new \Papaya\Request\Parameters(array());
    $definitions = array(
      array('foo', 42)
    );
    $validator = new Validator($definitions, $parameters);
    $this->assertEquals(42, $validator['foo']);
  }

  public function testOffsetGetWithInvalidNameExpectingNull() {
    $parameters = new \Papaya\Request\Parameters(array());
    $definitions = array();
    $validator = new Validator($definitions, $parameters);
    $this->assertNull($validator['bar']);
  }

  /**
   * @dataProvider provideOffsetSetData
   * @param mixed $expected
   * @param string $name
   * @param mixed $value
   */
  public function testSet($expected, $name, $value) {
    $parameters = new \Papaya\Request\Parameters(array());
    $definitions = array(
      array('integer', 0),
      array('float', 0.0),
      array('string', ''),
      array('array', array()),
      array('nodefault'),
      array('withfilter', 0, new \Papaya\Filter\IntegerValue(0, 21)),
      array('stringobject', new \Papaya\UI\Text('ok'))
    );
    $validator = new Validator($definitions, $parameters);
    $validator[$name] = $value;
    $this->assertSame($expected, $validator[$name]);
  }

  public static function provideOffsetSetData() {
    return array(
      array(21, 'integer', 21),
      array(42.21, 'float', 42.21),
      array('ok', 'string', 'ok'),
      array(array(21, 42), 'array', array(21, 42)),
      array('ok', 'nodefault', 'ok'),
      array(0, 'withfilter', 42),
      array(42, 'integer', '42ab'),
      array('ok', 'stringobject', 42)
    );
  }

  public function testSetWithInvalidName() {
    $parameters = new \Papaya\Request\Parameters(array());
    $definitions = array();
    $validator = new Validator($definitions, $parameters);
    $this->expectException(\InvalidArgumentException::class);
    $validator['foo'] = 'bar';
  }

  public function testUnsetSetToDefaultValue() {
    $parameters = new \Papaya\Request\Parameters(array('foo' => 21));
    $definitions = array(
      array('foo', 42)
    );
    $validator = new Validator($definitions, $parameters);
    unset($validator['foo']);
    $this->assertEquals(42, $validator['foo']);
  }

  public function testUnsetSetToInvalidParameterExpectingException() {
    $parameters = new \Papaya\Request\Parameters(array('foo' => 21));
    $definitions = array();
    $validator = new Validator($definitions, $parameters);
    $this->expectException(\InvalidArgumentException::class);
    unset($validator['foo']);
  }

}
