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

class TextTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Filter\Text::__construct
   */
  public function testConstructorWithOptionsParameter() {
    $filter = new Text(Text::ALLOW_DIGITS);
    $this->assertAttributeEquals(
      Text::ALLOW_DIGITS, '_options', $filter
    );
  }

  /**
   * @covers       \Papaya\Filter\Text::validate
   * @covers       \Papaya\Filter\Text::getPattern
   * @dataProvider provideValidValues
   * @param mixed $value
   * @param int $options
   * @throws Exception\InvalidCharacter
   * @throws Exception\IsEmpty
   */
  public function testValidateWithValidValuesExpectingTrue(
    $value, $options = Text::ALLOW_SPACES
  ) {
    $filter = new Text($options);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers       \Papaya\Filter\Text::validate
   * @covers       \Papaya\Filter\Text::getPattern
   * @dataProvider provideInvalidValues
   * @param mixed $value
   * @param int $options
   * @throws Exception\InvalidCharacter
   * @throws Exception\IsEmpty
   */
  public function testValidateWithInvalidValuesExpectingException(
    $value, $options = Text::ALLOW_SPACES
  ) {
    $filter = new Text($options);
    $this->expectException(Exception\InvalidCharacter::class);
    $filter->validate($value);
  }

  /**
   * @covers \Papaya\Filter\Text::validate
   * @covers \Papaya\Filter\Text::getPattern
   */
  public function testValidateWithEmptyValueExpectingException() {
    $filter = new Text();
    $this->expectException(Exception\IsEmpty::class);
    $filter->validate('');
  }

  /**
   * @covers \Papaya\Filter\Text::validate
   * @covers \Papaya\Filter\Text::getPattern
   */
  public function testValidateWithNullValueExpectingException() {
    $filter = new Text();
    $this->expectException(Exception\IsEmpty::class);
    $filter->validate(NULL);
  }

  /**
   * @covers \Papaya\Filter\Text::validate
   * @covers \Papaya\Filter\Text::getPattern
   */
  public function testValidateWithArrayValueExpectingException() {
    $filter = new Text();
    $this->expectException(Exception\UnexpectedType::class);
    $filter->validate(array());
  }

  /**
   * @covers       \Papaya\Filter\Text::filter
   * @covers       \Papaya\Filter\Text::getPattern
   * @dataProvider provideFilterValues
   * @param string|NULL $expected
   * @param mixed $value
   * @param int $options
   */
  public function testFilterValues(
    $expected, $value, $options = Text::ALLOW_SPACES
  ) {
    $filter = new Text($options);
    $this->assertEquals($expected, $filter->filter($value));
  }

  public static function provideValidValues() {
    return array(
      array('Hello', 0),
      array('Hello World'),
      array('Schöne Welt'),
      array('こんにちは世界'),
      array('مرحبا العالم'),
      array('Hello (first) World!'),
      array('Hello [first] World!'),
      array(',-_'),
      array('Hello 2. World!', Text::ALLOW_SPACES | Text::ALLOW_DIGITS),
      array("foo\nbar", Text::ALLOW_LINES)
    );
  }

  public static function provideInvalidValues() {
    return array(
      array("foo\nbar"),
      array('Hello World', 0),
      array('Hello 2. World!')
    );
  }

  public static function provideFilterValues() {
    return array(
      array(NULL, '123'),
      array(NULL, array()),
      array('Hello', 'Hello'),
      array('Hello World', 'Hello World'),
      array('HelloWorld', 'Hello World', 0),
      array(
        'Hello 2. World!', 'Hello 2. World!',
        Text::ALLOW_SPACES | Text::ALLOW_DIGITS
      ),
      array('Hello . World!', 'Hello 2. World!'),
      array("foo\nbar", "foo\nbar", Text::ALLOW_LINES),
      array('foobar', "foo\nbar")
    );
  }
}
