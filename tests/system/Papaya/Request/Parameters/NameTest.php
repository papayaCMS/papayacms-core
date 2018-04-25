<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaRequestParametersNameTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParametersName::__construct
  */
  public function testContructorWithoutParameters() {
    $this->assertInstanceOf(
      PapayaRequestParametersName::class, new PapayaRequestParametersName()
    );
  }

  /**
  * @covers PapayaRequestParametersName::separator
  */
  public function testSetSeparator() {
    $name = new PapayaRequestParametersName();
    $this->assertEquals(
      '*', $name->separator('*')
    );
  }

  /**
  * @covers PapayaRequestParametersName::separator
  */
  public function testSetSeparatorExpectingException() {
    $name = new PapayaRequestParametersName();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid parameter group separator: "fail".');
    $name->separator('fail');
  }

  /**
  * @covers PapayaRequestParametersName::__construct
  * @covers PapayaRequestParametersName::set
  * @covers PapayaRequestParametersName::parse
  * @covers PapayaRequestParametersName::parseArray
  */
  public function testContructorWithArray() {
    $name = new PapayaRequestParametersName(array('foo', 'bar'), '/');
    $this->assertAttributeEquals(
      array('foo', 'bar'), '_parts', $name
    );
    $this->assertEquals(
      '/', $name->separator()
    );
  }

  /**
  * @covers PapayaRequestParametersName::__construct
  * @covers PapayaRequestParametersName::set
  */
  public function testContructorWithSeparator() {
    $name = new PapayaRequestParametersName(array('foo', 'bar'), '/');
    $this->assertEquals(
      '/', $name->separator()
    );
  }

  /**
  * @covers PapayaRequestParametersName::set
  */
  public function testSetFromObject() {
    $original = new PapayaRequestParametersName('foo/bar[]', '/');
    $name = new PapayaRequestParametersName($original);
    $name->set($original);
    $this->assertEquals(
      array('foo', 'bar', ''), iterator_to_array($name)
    );
    $this->assertEquals(
      '/', $name->separator()
    );
  }

  /**
  * @covers PapayaRequestParametersName::setArray
  */
  public function testSetArray() {
    $name = new PapayaRequestParametersName();
    $name->setArray(array('foo', 'bar'));
    $this->assertAttributeEquals(
      array('foo', 'bar'), '_parts', $name
    );
  }

  /**
  * @covers PapayaRequestParametersName::__construct
  * @covers PapayaRequestParametersName::set
  * @covers PapayaRequestParametersName::parse
  * @covers PapayaRequestParametersName::parseString
  */
  public function testContructorWithString() {
    $name = new PapayaRequestParametersName('foo/bar');
    $this->assertAttributeEquals(
      array('foo', 'bar'), '_parts', $name
    );
  }

  /**
  * @covers PapayaRequestParametersName::__construct
  * @covers PapayaRequestParametersName::set
  * @covers PapayaRequestParametersName::parse
  * @covers PapayaRequestParametersName::parseString
  */
  public function testContructorWithInteger() {
    $name = new PapayaRequestParametersName(23);
    $this->assertAttributeEquals(
      array('23'), '_parts', $name
    );
  }

  /**
  * @covers PapayaRequestParametersName::parse
  */
  public function testParseWithInvalidArgument() {
    $name = new PapayaRequestParametersName();
    $this->expectException(InvalidArgumentException::class);
    $name->parse(NULL);
  }

  /**
  * @covers PapayaRequestParametersName::setString
  * @covers PapayaRequestParametersName::parseString
  * @dataProvider provideNameStrings
  */
  public function testSetString($nameArray, $nameString, $separator) {
    $name = new PapayaRequestParametersName();
    $name->setString($nameString, $separator);
    $this->assertAttributeEquals(
      $nameArray, '_parts', $name
    );
  }

  /**
  * @covers PapayaRequestParametersName::setString
  * @covers PapayaRequestParametersName::parseString
  */
  public function testSetStringWithListSyntax() {
    $name = new PapayaRequestParametersName();
    $name->setString('foo/bar[]', '/');
    $this->assertAttributeEquals(
      array('foo', 'bar', ''), '_parts', $name
    );
  }

  /**
  * @covers PapayaRequestParametersName::append
  */
  public function testAppend() {
    $name = new PapayaRequestParametersName(array('foo', 'bar'));
    $name->append('moo');
    $this->assertAttributeEquals(
      array('foo', 'bar', 'moo'), '_parts', $name
    );
  }

  /**
  * @covers PapayaRequestParametersName::prepend
  */
  public function testPrepend() {
    $name = new PapayaRequestParametersName(array('foo', 'bar'));
    $name->prepend('moo');
    $this->assertAttributeEquals(
      array('moo', 'foo', 'bar'), '_parts', $name
    );
  }

  /**
  * @covers PapayaRequestParametersName::insertBefore
  * @dataProvider provideInsertBeforeData
   */
  public function testInsertBefore($expected, $index, $additional) {
    $name = new PapayaRequestParametersName(array('foo', 'bar'));
    $name->insertBefore($index, $additional);
    $this->assertEquals(
      $expected,
      iterator_to_array($name)
    );
  }

  /**
  * @covers PapayaRequestParametersName::getString
  * @dataProvider provideNameStrings
  */
  public function testGetString($nameArray, $nameString, $separator) {
    $name = new PapayaRequestParametersName($nameArray);
    $this->assertEquals(
      $nameString, $name->getString($separator)
    );
  }

  /**
  * @covers PapayaRequestParametersName::getString
  */
  public function testGetStringExpectingEmpty() {
    $name = new PapayaRequestParametersName();
    $this->assertEquals(
      '', $name->getString()
    );
  }

  /**
  * @covers PapayaRequestParametersName::__toString
  */
  public function testMagicMethodToString() {
    $name = new PapayaRequestParametersName('foo/bar');
    $this->assertEquals(
      'foo[bar]', (string)$name
    );
  }

  /**
  * @covers PapayaRequestParametersName::__toString
  */
  public function testMagicMethodToStringWihtSeparator() {
    $name = new PapayaRequestParametersName('foo/bar', '*');
    $this->assertEquals(
      'foo*bar', (string)$name
    );
  }

  /**
  * @covers PapayaRequestParametersName::getArray
  */
  public function testGetArray() {
    $name = new PapayaRequestParametersName('foo/bar');
    $this->assertEquals(
      array('foo', 'bar'), $name->getArray()
    );
  }

  /**
  * @covers PapayaRequestParametersName::__construct
  * @covers PapayaRequestParametersName::set
  * @covers PapayaRequestParametersName::parse
  */
  public function testContructorWithItself() {
    $name = new PapayaRequestParametersName(
      new PapayaRequestParametersName(array('foo', 'bar'), '/')
    );
    $this->assertAttributeEquals(
      array('foo', 'bar'), '_parts', $name
    );
    $this->assertEquals(
      '/', $name->separator()
    );
  }

  /**
  * @covers PapayaRequestParametersName::offsetExists
  */
  public function testArrayAccessExistsExpectingTrue() {
    $name = new PapayaRequestParametersName('foo/bar');
    $this->assertTrue(isset($name[1]));
  }

  /**
  * @covers PapayaRequestParametersName::offsetExists
  */
  public function testArrayAccessExistsExpectingFalse() {
    $name = new PapayaRequestParametersName('foo');
    $this->assertFalse(isset($name[1]));
  }

  /**
  * @covers PapayaRequestParametersName::offsetGet
  * @covers PapayaRequestParametersName::offsetSet
  */
  public function testArrayAccessGetAfterSet() {
    $name = new PapayaRequestParametersName('foo');
    $name[] = 'bar';
    $this->assertEquals('bar', $name[1]);
  }

  /**
  * @covers PapayaRequestParametersName::offsetUnset
  */
  public function testArrayAccessUnset() {
    $name = new PapayaRequestParametersName('foo/bar');
    unset($name[0]);
    $this->assertFalse(isset($name[1]));
  }

  /**
  * @covers PapayaRequestParametersName::count
  */
  public function testCountable() {
    $name = new PapayaRequestParametersName('foo/bar');
    $this->assertEquals(2, count($name));
  }

  /**
  * @covers PapayaRequestParametersName::getIterator
  */
  public function testIteratorAggregate() {
    $name = new PapayaRequestParametersName('foo/bar');
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
