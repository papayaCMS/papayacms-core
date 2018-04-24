<?php
require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @covers PapayaRequestParametersValidator
 */
class PapayaRequestParametersValidatorTest extends PapayaTestCase {

  public function testConstructor() {
    $parameters = new PapayaRequestParameters();
    $definitions = array(
      array('example', 'default', new PapayaFilterNotEmpty())
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
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
      array('example', 'default', new PapayaFilterNotEmpty())
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertEquals(
      array(
        'foo' => 'bar'
      ),
      iterator_to_array($this->readAttribute($validator, '_parameters'))
    );
  }

  public function testConstructorWithNamedDefinition() {
    $parameters = new PapayaRequestParameters();
    $definitions = array(
      array(
        'name' => 'example',
        'default' => 'default',
        'filter' => new PapayaFilterNotEmpty()
      )
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertEquals(
      array(
        'example' => 'default'
      ),
      iterator_to_array($validator)
    );
  }

  public function testConstructorWithoutFilter() {
    $parameters = new PapayaRequestParameters();
    $definitions = array(
      array(
        'name' => 'example',
        'default' => 'default'
      )
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertEquals(
      array(
        'example' => 'default'
      ),
      iterator_to_array($validator)
    );
  }

  public function testConstructorWithoutDefault() {
    $parameters = new PapayaRequestParameters();
    $definitions = array(
      array(
        'name' => 'example'
      )
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertEquals(
      array(
        'example' => NULL
      ),
      iterator_to_array($validator)
    );
  }

  public function testConstructorWithValidatorAsSecondParameter() {
    $parameters = new PapayaRequestParameters(
      array('example' => 21)
    );
    $definitions = array(
      array('example', new PapayaFilterInteger(42))
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertEquals(
      array(
        'example' => NULL
      ),
      iterator_to_array($validator)
    );
  }

  public function testValidateWithTwoValues() {
    $parameters = new PapayaRequestParameters(
      array(
        'foo' => '21',
        'bar' => '42'
      )
    );
    $definitions = array(
      array('name' => 'foo'),
      array('name' => 'bar', 23, new PapayaFilterInteger(0, 42))
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
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
    $parameters = new PapayaRequestParameters(
      array(
        'foo' => '21'
      )
    );
    $definitions = array(
      array('foo', 23, new PapayaFilterInteger(42))
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
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
    $parameters = new PapayaRequestParameters(array());
    $definitions = array(
      array('foo', 42)
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertTrue(isset($validator['foo']));
  }

  public function testIssetValueExpectingFalse() {
    $parameters = new PapayaRequestParameters(array());
    $definitions = array(
      array('foo', 42)
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertFalse(isset($validator['bar']));
  }

  public function testGetFetchingValue() {
    $parameters = new PapayaRequestParameters(array('foo' => 21));
    $definitions = array(
      array('foo', 42)
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertEquals(21, $validator->get('foo'));
  }

  public function testOffsetGetFetchingValue() {
    $parameters = new PapayaRequestParameters(array('foo' => 21));
    $definitions = array(
      array('foo', 42)
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertEquals(21, $validator['foo']);
  }

  public function testOffsetGetFetchingDefaultValue() {
    $parameters = new PapayaRequestParameters(array());
    $definitions = array(
      array('foo', 42)
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertEquals(42, $validator['foo']);
  }

  public function testOffsetGetWithInvalidNameExpectingNull() {
    $parameters = new PapayaRequestParameters(array());
    $definitions = array();
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->assertNull($validator['bar']);
  }

  /**
   * @dataProvider provideOffsetSetData
   */
  public function testSet($expected, $name, $value) {
    $parameters = new PapayaRequestParameters(array());
    $definitions = array(
      array('integer', 0),
      array('float', 0.0),
      array('string', ''),
      array('array', array()),
      array('nodefault'),
      array('withfilter', 0, new PapayaFilterInteger(0, 21)),
      array('stringobject', new PapayaUiString('ok'))
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
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
    $parameters = new PapayaRequestParameters(array());
    $definitions = array();
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->expectException(InvalidArgumentException::class);
    $validator['foo'] = 'bar';
  }

  public function testUnsetSetToDefaultValue() {
    $parameters = new PapayaRequestParameters(array('foo' => 21));
    $definitions = array(
      array('foo', 42)
    );
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    unset($validator['foo']);
    $this->assertEquals(42, $validator['foo']);
  }

  public function testUnsetSetToInvalidParameterExpectingException() {
    $parameters = new PapayaRequestParameters(array('foo' => 21));
    $definitions = array();
    $validator = new PapayaRequestParametersValidator($definitions, $parameters);
    $this->expectException(InvalidArgumentException::class);
    unset($validator['foo']);
  }

}
