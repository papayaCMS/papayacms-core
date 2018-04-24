<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterLogicalTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterLogical::__construct
  * @covers PapayaFilterLogical::_setFilters
  */
  public function testConstructorWithTwoFilters() {
    $subFilterOne = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterTwo = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $filter = new PapayaFilterLogical_TestProxy($subFilterOne, $subFilterTwo);
    $this->assertAttributeEquals(
      array($subFilterOne, $subFilterTwo),
      '_filters',
      $filter
    );
  }
  /**
  * @covers PapayaFilterLogical::__construct
  * @covers PapayaFilterLogical::_setFilters
  */
  public function testConstructorWithTwoScalars() {
    $subFilterOne = new PapayaFilterEquals('one');
    $subFilterTwo = new PapayaFilterEquals('two');
    $filter = new PapayaFilterLogical_TestProxy('one', 'two');
    $this->assertAttributeEquals(
      array($subFilterOne, $subFilterTwo),
      '_filters',
      $filter
    );
  }

  /**
  * @covers PapayaFilterLogical::__construct
  * @covers PapayaFilterLogical::_setFilters
  */
  public function testConstructorWithThreeFilters() {
    $subFilterOne = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterTwo = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterThree = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $filter = new PapayaFilterLogical_TestProxy($subFilterOne, $subFilterTwo, $subFilterThree);
    $this->assertAttributeEquals(
      array($subFilterOne, $subFilterTwo, $subFilterThree),
      '_filters',
      $filter
    );
  }

  /**
  * @covers PapayaFilterLogical::__construct
  * @covers PapayaFilterLogical::_setFilters
  */
  public function testContructorWithOneFilterExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    $filter = new PapayaFilterLogical_TestProxy(
      $this->getMock(PapayaFilter::class, array('validate', 'filter'))
    );
  }

  /**
  * @covers PapayaFilterLogical::__construct
  * @covers PapayaFilterLogical::_setFilters
  */
  public function testContructorWithInvalidObjectsExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    $filter = new PapayaFilterLogical_TestProxy(
      new stdClass(), new stdClass()
    );
  }
}

class PapayaFilterLogical_TestProxy extends PapayaFilterLogical {

  public function validate($value) {
  }

  public function filter($value) {
  }
}
