<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterArgumentsTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterArguments::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterArguments(array(new PapayaFilterNotEmpty()));
    $this->assertAttributeEquals(
      array(new PapayaFilterNotEmpty()), '_filters', $filter
    );
  }

  /**
  * @covers PapayaFilterArguments::__construct
  */
  public function testConstructorWithAllParameters() {
    $filter = new PapayaFilterArguments(array(new PapayaFilterNotEmpty()), ';');
    $this->assertAttributeEquals(
      ';', '_separator', $filter
    );
  }

  /**
  * @covers PapayaFilterArguments::validate
  * @dataProvider provideValidValidationData
  */
  public function testValidateExpectingTrue($value, $filters, $separator) {
    $filter = new PapayaFilterArguments($filters, $separator);
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterArguments::validate
  * @dataProvider provideInvalidValidationData
  */
  public function testValidateExpectingException($value, $filters, $separator) {
    $filter = new PapayaFilterArguments($filters, $separator);
    $this->expectException(PapayaFilterException::class);
    $filter->validate($value);
  }

  /**
  * @covers PapayaFilterArguments::filter
  * @dataProvider provideFilterData
  */
  public function testFilter($expected, $value, $filters, $separator) {
    $filter = new PapayaFilterArguments($filters, $separator);
    $this->assertSame($expected, $filter->filter($value));
  }

  /**
  * @covers PapayaFilterArguments::filter
  * @dataProvider provideInvalidValidationData
  */
  public function testFilterExpectingNull($value, $filters, $separator) {
    $filter = new PapayaFilterArguments($filters, $separator);
    $this->assertNull($filter->filter($value));
  }

  public static function provideValidValidationData() {
    return array(
      'one integer' => array(
        '42', array(new PapayaFilterInteger()), ','
      ),
      'two integers' => array(
        '21,42', array(new PapayaFilterInteger(), new PapayaFilterInteger()), ','
      ),
      'different filters' => array(
        'foo,42', array(new PapayaFilterText(), new PapayaFilterInteger()), ','
      ),
      'different separator' => array(
        'foo;42', array(new PapayaFilterText(), new PapayaFilterInteger()), ';'
      ),
      'second element optional' => array(
        '21',
        array(
         new PapayaFilterInteger(),
         new PapayaFilterLogicalOr(new PapayaFilterEmpty(), new PapayaFilterInteger()),
        ),
        ','
      )
    );
  }

  public static function provideInvalidValidationData() {
    return array(
      'empty' => array('', array(), ','),
      'missing element' => array(
        '42', array(new PapayaFilterInteger(), new PapayaFilterInteger()), ','
      ),
      'to many elements' => array(
        '21,42', array(new PapayaFilterInteger()), ','
      ),
      'invalid element' => array(
        '21,foo', array(new PapayaFilterInteger(), new PapayaFilterInteger()), ','
      ),
      'invalid separator' => array(
        '21,foo', array(new PapayaFilterInteger(), new PapayaFilterInteger()), '#'
      )
    );
  }

  public static function provideFilterData() {
    return array(
      'one integer' => array(
        '42', '42', array(new PapayaFilterInteger()), ','
      ),
      'two integers' => array(
        '21,42', '21,42', array(new PapayaFilterInteger(), new PapayaFilterInteger()), ','
      ),
      'different filters' => array(
        'foo,42', 'foo,42', array(new PapayaFilterText(), new PapayaFilterInteger()), ','
      ),
      'different separator' => array(
        'foo;42', 'foo;42', array(new PapayaFilterText(), new PapayaFilterInteger()), ';'
      ),
      'second element optional' => array(
        '21,0',
        '21',
        array(
         new PapayaFilterInteger(),
         new PapayaFilterLogicalOr(new PapayaFilterEmpty(), new PapayaFilterInteger()),
        ),
        ','
      )
    );
  }
}
