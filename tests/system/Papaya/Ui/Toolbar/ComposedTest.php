<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiToolbarComposedTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarComposed::__construct
  * @covers PapayaUiToolbarComposed::setNames
  */
  public function testConstructor() {
    $composed = new PapayaUiToolbarComposed(
      array('first', 'second')
    );
    $this->assertTrue(isset($composed->first));
    $this->assertTrue(isset($composed->second));
  }

  /**
  * @covers PapayaUiToolbarComposed::__construct
  * @covers PapayaUiToolbarComposed::setNames
  */
  public function testConstructorWithEmptySetList() {
    $this->setExpectedException(
      'InvalidArgumentException', 'No sets defined'
    );
    $composed = new PapayaUiToolbarComposed(array());
  }

  /**
  * @covers PapayaUiToolbarComposed::__construct
  * @covers PapayaUiToolbarComposed::setNames
  */
  public function testConstructorWithInvalidSetName() {
    $this->setExpectedException(
      'InvalidArgumentException', 'Invalid set name "" in index "0".'
    );
    $composed = new PapayaUiToolbarComposed(array(''));
  }

  /**
  * @covers PapayaUiToolbarComposed::appendTo
  */
  public function testAppendTo() {
    $set = $this->getMock('PapayaUiToolbarSet');
    $elements = $this->getMock('PapayaUiToolbarElements');
    $elements
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiToolbarSet'));
    $toolbar = $this->getMock('PapayaUiToolbar');
    $toolbar
      ->expects($this->any())
      ->method('__get')
      ->with('elements')
      ->will($this->returnValue($elements));
    $toolbar
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $composed = new PapayaUiToolbarComposed(array('first', 'second'));
    $composed->toolbar($toolbar);
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->first = $set;
    $composed->getXml();
  }

  /**
  * @covers PapayaUiToolbarComposed::toolbar
  */
  public function testToolbarGetAfterSet() {
    $toolbar = $this->getMock('PapayaUiToolbar');
    $composed = new PapayaUiToolbarComposed(array('first', 'second'));
    $composed->toolbar($toolbar);
    $this->assertSame($toolbar, $composed->toolbar());
  }

  /**
  * @covers PapayaUiToolbarComposed::toolbar
  */
  public function testToolbarGetImplicitCreate() {
    $composed = new PapayaUiToolbarComposed(array('first', 'second'));
    $this->assertInstanceOf('PapayaUiToolbar', $toolbar = $composed->toolbar());
  }

  /**
  * @covers PapayaUiToolbarComposed::__isset
  */
  public function testIssetExpectingTrue() {
    $composed = new PapayaUiToolbarComposed(array('someSet'));
    $this->assertTrue(isset($composed->someSet));
  }

  /**
  * @covers PapayaUiToolbarComposed::__isset
  */
  public function testIssetExpectingFalse() {
    $composed = new PapayaUiToolbarComposed(array('someSet'));
    $this->assertFalse(isset($composed->unknownSet));
  }

  /**
  * @covers PapayaUiToolbarComposed::__set
  * @covers PapayaUiToolbarComposed::__get
  */
  public function testGetAfterSet() {
    $set = $this->getMock('PapayaUiToolbarSet');
    $composed = new PapayaUiToolbarComposed(array('someSet'));
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->someSet = $set;
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertSame($set, $composed->someSet);
  }

  /**
  * @covers PapayaUiToolbarComposed::__get
  */
  public function testGetImplicitCreate() {
    $composed = new PapayaUiToolbarComposed(array('someSet'));
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertInstanceOf('PapayaUiToolbarSet', $set = $composed->someSet);
  }

  /**
  * @covers PapayaUiToolbarComposed::__get
  */
  public function testGetWithUndefinedNameExpectingException() {
    $composed = new PapayaUiToolbarComposed(array('someSet'));
    $this->setExpectedException(
      'UnexpectedValueException', 'Invalid toolbar set requested.'
    );
    /** @noinspection PhpUndefinedFieldInspection */
    $dummy = $composed->unknownSet;
  }

  /**
  * @covers PapayaUiToolbarComposed::__set
  */
  public function testSetWithUndefinedNameExpectingException() {
    $composed = new PapayaUiToolbarComposed(array('someSet'));
    $this->setExpectedException(
      'UnexpectedValueException', 'Invalid toolbar set requested.'
    );
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->unknownSet = $this->getMock('PapayaUiToolbarSet');
  }
}
