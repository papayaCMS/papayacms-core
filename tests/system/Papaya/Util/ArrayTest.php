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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaUtilArrayTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Utility\Arrays::merge
   * @dataProvider mergeDataProvider
   * @param array|NULL $arrayOne
   * @param array|NULL $arrayTwo
   * @param array|NULL $expected
   */
  public function testMerge($arrayOne, $arrayTwo, $expected) {
    $actual = \Papaya\Utility\Arrays::merge($arrayOne, $arrayTwo);
    $this->assertSame($expected, $actual);
  }

  /**
   * @covers \Papaya\Utility\Arrays::ensure
   * @dataProvider toArrayDataProvider
   * @param array $expected
   * @param mixed $input
   * @param bool $useKeys
   */
  public function testEnsure(array $expected, $input, $useKeys = TRUE) {
    $this->assertSame($expected, \Papaya\Utility\Arrays::ensure($input, $useKeys));
  }

  /**
  * @covers \Papaya\Utility\Arrays::get
  */
  public function testGetWithExistingElement() {
    $this->assertEquals(
      'success', \Papaya\Utility\Arrays::get(array('test' => 'success'), 'test')
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::get
  */
  public function testGetWithListArgumentSecondIndexExists() {
    $this->assertEquals(
      'success', \Papaya\Utility\Arrays::get(array('test' => 'success'), array('non-existing', 'test'))
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::get
  */
  public function testGetWithNonexistingElementFetchingDefault() {
    $this->assertEquals(
      'success', \Papaya\Utility\Arrays::get(array('test' => 'fail'), 'sample', 'success')
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::get
  */
  public function testGetWithListArgumentFetchingDefault() {
    $this->assertEquals(
      'success', \Papaya\Utility\Arrays::get(array('test' => 'fail'), array('sample', 99), 'success')
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::get
  */
  public function testGetWithEmptyIndexFetchingDefault() {
    $this->assertEquals(
      'success', \Papaya\Utility\Arrays::get(array(), NULL, 'success')
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::getRecursive
  */
  public function testGetRecursiveElementFromTopLevel() {
    $this->assertEquals(
      1,
      \Papaya\Utility\Arrays::getRecursive(
        array('key' => 1), array('key')
      )
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::getRecursive
  */
  public function testGetRecursiveElementFromFirstSublevel() {
    $this->assertEquals(
      2,
      \Papaya\Utility\Arrays::getRecursive(
        array('group' => array('key' => 2)), array('group', 'key')
      )
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::getRecursive
  */
  public function testGetRecursiveElementFromSecondSublevel() {
    $this->assertEquals(
      3,
      \Papaya\Utility\Arrays::getRecursive(
        array('group' => array('subgroup' => array('key' => 3))),
        array('group', 'subgroup', 'key')
      )
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::getRecursive
  */
  public function testGetRecursiveElementFromEmptyListExpectingDefault() {
    $this->assertEquals(
      'DEFAULT',
      \Papaya\Utility\Arrays::getRecursive(
        array(), array('invalid-key'), 'DEFAULT'
      )
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::getRecursive
  */
  public function testGetRecursiveInvalidElementExpectingDefault() {
    $this->assertEquals(
      'DEFAULT',
      \Papaya\Utility\Arrays::getRecursive(
        array('group' => array('subgroup' => array('key' => 3))),
        array('group' => 'subgroup', 'invalid-key'),
        'DEFAULT'
      )
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::decodeIdList
  */
  public function testDecodeIdList() {
    $this->assertEquals(
      array(21, 42), \Papaya\Utility\Arrays::decodeIdList(';21;42;')
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::decodeIdList
  */
  public function testDecodeIdListWithSign() {
    $this->assertEquals(
      array(-21, 42), \Papaya\Utility\Arrays::decodeIdList(';-21;+42;')
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::decodeIdList
  */
  public function testDecodeIdListWithEmptyStringExpectingEmptyArray() {
    $this->assertEquals(
      array(), \Papaya\Utility\Arrays::decodeIdList('')
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::encodeIdList
  */
  public function testEncodeIdList() {
    $this->assertEquals(
      '21;42', \Papaya\Utility\Arrays::encodeIdList(array(21, 42))
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::encodeIdList
  */
  public function testEncodeIdListWithCommaSeparator() {
    $this->assertEquals(
      '21,42', \Papaya\Utility\Arrays::encodeIdList(array(21, 42), ',')
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::encodeAndQuoteIdList
  */
  public function testEncodeAndQuoteIdList() {
    $this->assertEquals(
      ';21;42;', \Papaya\Utility\Arrays::encodeAndQuoteIdList(array(21, 42))
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::encodeAndQuoteIdList
  */
  public function testEncodeAndQuoteIdListWithNonStandardCharacters() {
    $this->assertEquals(
      '!21|42!', \Papaya\Utility\Arrays::encodeAndQuoteIdList(array(21, 42), '!', '|')
    );
  }

  /**
   * @covers \Papaya\Utility\Arrays::normalize
   * @dataProvider provideDataForNormalize
   * @param mixed $expected
   * @param mixed $input
   */
  public function testNormalize($expected, $input) {
    \Papaya\Utility\Arrays::normalize($input);
    $this->assertSame(
      $expected, $input
    );
  }

  /**
  * @covers \Papaya\Utility\Arrays::normalize
  */
  public function testNormalizeWithCallback() {
    $input = 23;
    \Papaya\Utility\Arrays::normalize($input, function($value) { return 'success'.$value; });
    $this->assertSame(
      'success23', $input
    );
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
      'object' => array(stdClass::class, new stdClass()),
      'object with __toString' => array('sample', new \Papaya\Ui\Text('sample'))
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
        new \PapayaUtilArray_TestProxyArrayIterator(array('foo' => 'bar')),
      ),
      'Traversable' => array(
        array('foo' => 'bar'),
        new \PapayaUtilArray_TestProxyTraversable(array('foo' => 'bar')),
      ),
      'Traversable, remove keys' => array(
        array(0 => 'bar'),
        new \PapayaUtilArray_TestProxyTraversable(array('foo' => 'bar')),
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

  private /** @noinspection PropertyInitializationFlawsInspection */
    $_array = array();

  public function __construct($array) {
    $this->_array = $array;
  }

  public function getIterator() {
    return new ArrayIterator($this->_array);
  }
}

class PapayaUtilArray_TestProxyTraversable implements Iterator {

  private /** @noinspection PropertyInitializationFlawsInspection */
    $_array = array();

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
    return (NULL !== $key && FALSE !== $key);
  }
}
