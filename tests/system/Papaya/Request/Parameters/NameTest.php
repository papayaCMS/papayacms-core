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

class NameTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Request\Parameters\Name::__construct
   */
  public function testConstructorWithoutParameters() {
    $this->assertInstanceOf(
      Name::class, new Name()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::separator
   */
  public function testSetSeparator() {
    $name = new Name();
    $this->assertEquals(
      '*', $name->separator('*')
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::separator
   */
  public function testSetSeparatorExpectingException() {
    $name = new Name();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid parameter group separator: "fail".');
    $name->separator('fail');
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::__construct
   * @covers \Papaya\Request\Parameters\Name::set
   * @covers \Papaya\Request\Parameters\Name::parse
   * @covers \Papaya\Request\Parameters\Name::parseArray
   */
  public function testConstructorWithArray() {
    $name = new Name(array('foo', 'bar'), '/');
    $this->assertAttributeEquals(
      array('foo', 'bar'), '_parts', $name
    );
    $this->assertEquals(
      '/', $name->separator()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::__construct
   * @covers \Papaya\Request\Parameters\Name::set
   */
  public function testConstructorWithSeparator() {
    $name = new Name(array('foo', 'bar'), '/');
    $this->assertEquals(
      '/', $name->separator()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::set
   */
  public function testSetFromObject() {
    $original = new Name('foo/bar[]', '/');
    $name = new Name($original);
    $name->set($original);
    $this->assertEquals(
      array('foo', 'bar', ''), iterator_to_array($name)
    );
    $this->assertEquals(
      '/', $name->separator()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::setArray
   */
  public function testSetArray() {
    $name = new Name();
    $name->setArray(array('foo', 'bar'));
    $this->assertAttributeEquals(
      array('foo', 'bar'), '_parts', $name
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::__construct
   * @covers \Papaya\Request\Parameters\Name::set
   * @covers \Papaya\Request\Parameters\Name::parse
   * @covers \Papaya\Request\Parameters\Name::parseString
   */
  public function testConstructorWithString() {
    $name = new Name('foo/bar');
    $this->assertAttributeEquals(
      array('foo', 'bar'), '_parts', $name
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::__construct
   * @covers \Papaya\Request\Parameters\Name::set
   * @covers \Papaya\Request\Parameters\Name::parse
   * @covers \Papaya\Request\Parameters\Name::parseString
   */
  public function testConstructorWithInteger() {
    $name = new Name(23);
    $this->assertAttributeEquals(
      array('23'), '_parts', $name
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::parse
   */
  public function testParseWithInvalidArgument() {
    $name = new Name();
    $this->expectException(\InvalidArgumentException::class);
    $name->parse(NULL);
  }

  /**
   * @covers       \Papaya\Request\Parameters\Name::setString
   * @covers       \Papaya\Request\Parameters\Name::parseString
   * @dataProvider provideNameStrings
   * @param array $nameArray
   * @param string $nameString
   * @param string $separator
   */
  public function testSetString(array $nameArray, $nameString, $separator) {
    $name = new Name();
    $name->setString($nameString, $separator);
    $this->assertAttributeEquals(
      $nameArray, '_parts', $name
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::setString
   * @covers \Papaya\Request\Parameters\Name::parseString
   */
  public function testSetStringWithListSyntax() {
    $name = new Name();
    $name->setString('foo/bar[]', '/');
    $this->assertAttributeEquals(
      array('foo', 'bar', ''), '_parts', $name
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::append
   */
  public function testAppend() {
    $name = new Name(array('foo', 'bar'));
    $name->append('moo');
    $this->assertAttributeEquals(
      array('foo', 'bar', 'moo'), '_parts', $name
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::prepend
   */
  public function testPrepend() {
    $name = new Name(array('foo', 'bar'));
    $name->prepend('moo');
    $this->assertAttributeEquals(
      array('moo', 'foo', 'bar'), '_parts', $name
    );
  }

  /**
   * @covers       \Papaya\Request\Parameters\Name::insertBefore
   * @dataProvider provideInsertBeforeData
   * @param array $expected
   * @param int $index
   * @param string $additional
   */
  public function testInsertBefore(array $expected, $index, $additional) {
    $name = new Name(array('foo', 'bar'));
    $name->insertBefore($index, $additional);
    $this->assertEquals(
      $expected,
      iterator_to_array($name)
    );
  }

  /**
   * @covers       \Papaya\Request\Parameters\Name::getString
   * @dataProvider provideNameStrings
   * @param array $nameArray
   * @param string $nameString
   * @param string $separator
   */
  public function testGetString(array $nameArray, $nameString, $separator) {
    $name = new Name($nameArray);
    $this->assertEquals(
      $nameString, $name->getString($separator)
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::getString
   */
  public function testGetStringExpectingEmpty() {
    $name = new Name();
    $this->assertEquals(
      '', $name->getString()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::__toString
   */
  public function testMagicMethodToString() {
    $name = new Name('foo/bar');
    $this->assertEquals(
      'foo[bar]', (string)$name
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::__toString
   */
  public function testMagicMethodToStringWihtSeparator() {
    $name = new Name('foo/bar', '*');
    $this->assertEquals(
      'foo*bar', (string)$name
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::getArray
   */
  public function testGetArray() {
    $name = new Name('foo/bar');
    $this->assertEquals(
      array('foo', 'bar'), $name->getArray()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::__construct
   * @covers \Papaya\Request\Parameters\Name::set
   * @covers \Papaya\Request\Parameters\Name::parse
   */
  public function testConstructorWithItself() {
    $name = new Name(
      new Name(array('foo', 'bar'), '/')
    );
    $this->assertAttributeEquals(
      array('foo', 'bar'), '_parts', $name
    );
    $this->assertEquals(
      '/', $name->separator()
    );
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::offsetExists
   */
  public function testArrayAccessExistsExpectingTrue() {
    $name = new Name('foo/bar');
    $this->assertTrue(isset($name[1]));
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::offsetExists
   */
  public function testArrayAccessExistsExpectingFalse() {
    $name = new Name('foo');
    $this->assertFalse(isset($name[1]));
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::offsetGet
   * @covers \Papaya\Request\Parameters\Name::offsetSet
   */
  public function testArrayAccessGetAfterSet() {
    $name = new Name('foo');
    $name[] = 'bar';
    $this->assertEquals('bar', $name[1]);
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::offsetUnset
   */
  public function testArrayAccessUnset() {
    $name = new Name('foo/bar');
    unset($name[0]);
    $this->assertFalse(isset($name[1]));
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::count
   */
  public function testCountable() {
    $name = new Name('foo/bar');
    $this->assertCount(2, $name);
  }

  /**
   * @covers \Papaya\Request\Parameters\Name::getIterator
   */
  public function testIteratorAggregate() {
    $name = new Name('foo/bar');
    $this->assertEquals(array('foo', 'bar'), $name->getIterator()->getArrayCopy());
  }

  /******************************
   * Data Provider
   ******************************/

  public static function provideNameStrings() {
    return array(
      array(
        array('foo'),
        'foo',
        ''
      ),
      array(
        array('foo', 'bar'),
        'foo/bar',
        '/'
      ),
      array(
        array('foo', 'bar'),
        'foo[bar]',
        ''
      ),
      array(
        array('foo', 'bar[1]'),
        'foo/bar[1]',
        '/'
      ),
      array(
        array('foo/bar', '1'),
        'foo/bar[1]',
        '[]'
      ),
      array(
        array(
          'foo',
          '', '', '', '', '', '', '', '', '', '',
          '', '', '', '', '', '', '', '', '', '',
          '', '', '', '', '', '', '', '', '', '',
          '', '', '', '', '', '', '', '', '', '][', ''
        ),
        'foo[][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][]',
        ''
      )
    );
  }

  public static function provideInsertBeforeData() {
    return array(
      array(
        array('foo', 'insert', 'bar'),
        1,
        'insert'
      ),
      array(
        array('foo', 'one', 'two', 'bar'),
        1,
        'one/two'
      ),
      array(
        array('foo', 'bar', 'one', 'two'),
        4,
        'one/two'
      ),
      array(
        array('one', 'two', 'foo', 'bar',),
        0,
        'one[two]'
      )
    );
  }

}
