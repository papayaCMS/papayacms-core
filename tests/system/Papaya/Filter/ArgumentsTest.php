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

namespace Papaya\Filter;

require_once __DIR__.'/../../../bootstrap.php';

class ArgumentsTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Arguments::__construct
   */
  public function testConstructor() {
    $filter = new Arguments(array(new NotEmpty()));
    $this->assertAttributeEquals(
      array(new NotEmpty()), '_filters', $filter
    );
  }

  /**
   * @covers \Papaya\Filter\Arguments::__construct
   */
  public function testConstructorWithAllParameters() {
    $filter = new Arguments(array(new NotEmpty()), ';');
    $this->assertAttributeEquals(
      ';', '_separator', $filter
    );
  }

  /**
   * @covers       \Papaya\Filter\Arguments::validate
   * @dataProvider provideValidValidationData
   * @param mixed $value
   * @param array $filters
   * @param string $separator
   * @throws Exception
   */
  public function testValidateExpectingTrue($value, $filters, $separator) {
    $filter = new Arguments($filters, $separator);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers       \Papaya\Filter\Arguments::validate
   * @dataProvider provideInvalidValidationData
   * @param mixed $value
   * @param array $filters
   * @param string $separator
   * @throws Exception
   */
  public function testValidateExpectingException($value, $filters, $separator) {
    $filter = new Arguments($filters, $separator);
    $this->expectException(Exception::class);
    $filter->validate($value);
  }

  /**
   * @covers       \Papaya\Filter\Arguments::filter
   * @dataProvider provideFilterData
   * @param mixed $expected
   * @param mixed $value
   * @param array $filters
   * @param string $separator
   */
  public function testFilter($expected, $value, $filters, $separator) {
    $filter = new Arguments($filters, $separator);
    $this->assertSame($expected, $filter->filter($value));
  }

  /**
   * @covers       \Papaya\Filter\Arguments::filter
   * @dataProvider provideInvalidValidationData
   * @param mixed $value
   * @param array $filters
   * @param string $separator
   */
  public function testFilterExpectingNull($value, $filters, $separator) {
    $filter = new Arguments($filters, $separator);
    $this->assertNull($filter->filter($value));
  }

  public static function provideValidValidationData() {
    return array(
      'one integer' => array(
        '42', array(new IntegerValue()), ','
      ),
      'two integers' => array(
        '21,42', array(new IntegerValue(), new IntegerValue()), ','
      ),
      'different filters' => array(
        'foo,42', array(new Text(), new IntegerValue()), ','
      ),
      'different separator' => array(
        'foo;42', array(new Text(), new IntegerValue()), ';'
      ),
      'second element optional' => array(
        '21',
        array(
          new IntegerValue(),
          new LogicalOr(new EmptyValue(), new IntegerValue()),
        ),
        ','
      )
    );
  }

  public static function provideInvalidValidationData() {
    return array(
      'empty' => array('', array(), ','),
      'missing element' => array(
        '42', array(new IntegerValue(), new IntegerValue()), ','
      ),
      'to many elements' => array(
        '21,42', array(new IntegerValue()), ','
      ),
      'invalid element' => array(
        '21,foo', array(new IntegerValue(), new IntegerValue()), ','
      ),
      'invalid separator' => array(
        '21,foo', array(new IntegerValue(), new IntegerValue()), '#'
      )
    );
  }

  public static function provideFilterData() {
    return array(
      'one integer' => array(
        '42', '42', array(new IntegerValue()), ','
      ),
      'two integers' => array(
        '21,42', '21,42', array(new IntegerValue(), new IntegerValue()), ','
      ),
      'different filters' => array(
        'foo,42', 'foo,42', array(new Text(), new IntegerValue()), ','
      ),
      'different separator' => array(
        'foo;42', 'foo;42', array(new Text(), new IntegerValue()), ';'
      ),
      'second element optional' => array(
        '21,0',
        '21',
        array(
          new IntegerValue(),
          new LogicalOr(new EmptyValue(), new IntegerValue()),
        ),
        ','
      )
    );
  }
}
