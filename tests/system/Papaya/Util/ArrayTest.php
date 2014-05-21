<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUtilArrayTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilArray::merge
  * @dataProvider mergeDataProvider
  */
  public function testMerge($arrayOne, $arrayTwo, $expected) {
    $actual = PapayaUtilArray::merge($arrayOne, $arrayTwo);
    $this->assertSame($expected, $actual);
  }

  /**
  * @covers PapayaUtilArray::ensure
  * @dataProvider toArrayDataProvider
  */
  public function testEnsure($expected, $input, $useKeys = TRUE) {
    $this->assertSame($expected, PapayaUtilArray::ensure($input, $useKeys));
  }

  /**
  * @covers PapayaUtilArray::get
  */
  public function testGetWithExistingElement() {
    $this->assertEquals(
      'success', PapayaUtilArray::get(array('test' => 'success'), 'test')
    );
  }

  /**
  * @covers PapayaUtilArray::get
  */
  public function testGetWithListArgumentSecondIndexExists() {
    $this->assertEquals(
      'success', PapayaUtilArray::get(array('test' => 'success'), array('non-existing', 'test'))
    );
  }

  /**
  * @covers PapayaUtilArray::get
  */
  public function testGetWithNonexistingElementFetchingDefault() {
    $this->assertEquals(
      'success', PapayaUtilArray::get(array('test' => 'fail'), 'sample', 'success')
    );
  }

  /**
  * @covers PapayaUtilArray::get
  */
  public function testGetWithListArgumentFetchingDefault() {
    $this->assertEquals(
      'success', PapayaUtilArray::get(array('test' => 'fail'), array('sample', 99), 'success')
    );
  }

  /**
  * @covers PapayaUtilArray::get
  */
  public function testGetWithEmptyIndexFetchingDefault() {
    $this->assertEquals(
      'success', PapayaUtilArray::get(array(), NULL, 'success')
    );
  }

  /**
  * @covers PapayaUtilArray::getRecursive
  */
  public function testGetRecursiveElementFromTopLevel() {
    $this->assertEquals(
      1,
      PapayaUtilArray::getRecursive(
        array('key' => 1), array('key')
      )
    );
  }

  /**
  * @covers PapayaUtilArray::getRecursive
  */
  public function testGetRecursiveElementFromFirstSublevel() {
    $this->assertEquals(
      2,
      PapayaUtilArray::getRecursive(
        array('group' => array('key' => 2)), array('group', 'key')
      )
    );
  }

  /**
  * @covers PapayaUtilArray::getRecursive
  */
  public function testGetRecursiveElementFromSecondSublevel() {
    $this->assertEquals(
      3,
      PapayaUtilArray::getRecursive(
        array('group' => array('subgroup' => array('key' => 3))),
        array('group', 'subgroup', 'key')
      )
    );
  }

  /**
  * @covers PapayaUtilArray::getRecursive
  */
  public function testGetRecursiveElementFromEmptyListExpectingDefault() {
    $this->assertEquals(
      'DEFAULT',
      PapayaUtilArray::getRecursive(
        array(), array('invalid-key'), 'DEFAULT'
      )
    );
  }

  /**
  * @covers PapayaUtilArray::getRecursive
  */
  public function testGetRecursiveInvalidElementExpectingDefault() {
    $this->assertEquals(
      'DEFAULT',
      PapayaUtilArray::getRecursive(
        array('group' => array('subgroup' => array('key' => 3))),
        array('group' => 'subgroup', 'invalid-key'),
        'DEFAULT'
      )
    );
  }

  /**
  * @covers PapayaUtilArray::decodeIdList
  */
  public function testDecodeIdList() {
    $this->assertEquals(
      array(21, 42), PapayaUtilArray::decodeIdList(';21;42;')
    );
  }

  /**
  * @covers PapayaUtilArray::decodeIdList
  */
  public function testDecodeIdListWithSign() {
    $this->assertEquals(
      array(-21, 42), PapayaUtilArray::decodeIdList(';-21;+42;')
    );
  }

  /**
  * @covers PapayaUtilArray::decodeIdList
  */
  public function testDecodeIdListWithEmptyStringExpectingEmptyArray() {
    $this->assertEquals(
      array(), PapayaUtilArray::decodeIdList('')
    );
  }

  /**
  * @covers PapayaUtilArray::encodeIdList
  */
  public function testEncodeIdList() {
    $this->assertEquals(
      '21;42', PapayaUtilArray::encodeIdList(array(21, 42))
    );
  }

  /**
  * @covers PapayaUtilArray::encodeIdList
  */
  public function testEncodeIdListWithCommaSeparator() {
    $this->assertEquals(
      '21,42', PapayaUtilArray::encodeIdList(array(21, 42), ',')
    );
  }

  /**
  * @covers PapayaUtilArray::encodeAndQuoteIdList
  */
  public function testEncodeAndQuoteIdList() {
    $this->assertEquals(
      ';21;42;', PapayaUtilArray::encodeAndQuoteIdList(array(21, 42))
    );
  }

  /**
  * @covers PapayaUtilArray::encodeAndQuoteIdList
  */
  public function testEncodeAndQuoteIdListWithNonStandardCharacters() {
    $this->assertEquals(
      '!21|42!', PapayaUtilArray::encodeAndQuoteIdList(array(21, 42), '!', '|')
    );
  }

  /**
  * @covers PapayaUtilArray::normalize
  * @dataProvider provideDataForNormalize
  */
  public function testNormalize($expected, $input) {
    PapayaUtilArray::normalize($input);
    $this->assertSame(
      $expected, $input
    );
  }

  /**
  * @covers PapayaUtilArray::normalize
  */
  public function testNormalizeWithCallback() {
    $input = 23;
    PapayaUtilArray::normalize($input, array($this, 'callbackNormlize'));
    $this->assertSame(
      'success23', $input
    );
  }

  public function callbackNormlize($value) {
    return 'success'.$value;
  }

  /****************************
  * Data Provider
  ****************************/

  public static function provideDataForNormalize() {
    return array(
      'int' => array('42', 42),
      'string' => array('hello', 'hello'),
      'true' => array('1', TRUE),
      'false' => array('', FALSE),
      'array' => array(array('42', 'hello', '1', ''), array(42, 'hello', TRUE, FALSE)),
      'array of array' => array(array(array('foo' => '42')), array(array('foo' => 42))),
      'object' => array('stdClass', new stdClass()),
      'object with __toString' => array('sample', new PapayaUiString('sample'))
    );
  }

  public static function mergeDataProvider() {
    return array(
      array(
        NULL,
        NULL,
        NULL
      ),
      array(
        array('cmd' => 'show'),
        NULL,
        array('cmd' => 'show')
      ),
      array(
        NULL,
        array('cmd' => 'show'),
        array('cmd' => 'show')
      ),
      array(
        array('cmd' => 'show'),
        array('id' => 1),
        array(
          'cmd' => 'show',
          'id' => 1
        )
      ),
      array(
        array('cmd' => 'show', 'id' => 0),
        array('id' => 1),
        array(
          'cmd' => 'show',
          'id' => 1
        )
      ),
      array(
        array('test' => array('cmd' => 'show')),
        array('test' => array('id' => 1)),
        array(
          'test' => array(
            'cmd' => 'show',
            'id' => 1
          )
        )
      ),
      array(
        new ArrayObject(array('cmd' => 'show')),
        new ArrayObject(array('id' => 1)),
        array(
          'cmd' => 'show',
          'id' => 1
        )
      )
    );
  }

  public static function toArrayDataProvider() {
    return array(
      'array' => array(
        array('foo' => 'bar'),
        array('foo' => 'bar'),
      ),
      'array, remove keys' => array(
        array(0 => 'bar'),
        array('foo' => 'bar'),
        FALSE
      ),
      'ArrayObject' => array(
        array('foo' => 'bar'),
        new ArrayObject(array('foo' => 'bar')),
      ),
      'IteratorAggregate' => array(
        array('foo' => 'bar'),
        new PapayaUtilArray_TestProxyArrayIterator(array('foo' => 'bar')),
      ),
      'Traversable' => array(
        array('foo' => 'bar'),
        new PapayaUtilArray_TestProxyTraversable(array('foo' => 'bar')),
      ),
      'Traversable, remove keys' => array(
        array(0 => 'bar'),
        new PapayaUtilArray_TestProxyTraversable(array('foo' => 'bar')),
        FALSE
      ),
      'skalar' => array(
        array('foo'),
        'foo',
      ),
      'object (without Traversable)' => array(
        array($object = new stdClass),
        $object,
      )
    );
  }
}

class PapayaUtilArray_TestProxyArrayIterator implements IteratorAggregate {

  private $_array = array();

  public function __construct($array) {
    $this->_array = $array;
  }

  public function getIterator() {
    return new ArrayIterator($this->_array);
  }
}

class PapayaUtilArray_TestProxyTraversable implements Iterator {

  private $_array = array();

  public function __construct($array) {
    $this->_array = $array;
  }

  public function rewind() {
    reset($this->_array);
  }

  public function current() {
    return current($this->_array);
  }

  public function key() {
    return key($this->_array);
  }

  public function next() {
    return next($this->_array);
  }

  public function valid() {
    $key = key($this->_array);
    return ($key !== NULL && $key !== FALSE);
  }
}