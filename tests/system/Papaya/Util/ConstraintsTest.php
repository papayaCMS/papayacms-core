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

use Papaya\Url;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaUtilConstraintsTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Utility\Constraints::assertArray
  */
  public function testAssertArray() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertArray(array())
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertArray
   * @dataProvider provideInvalidValuesForAssertArray
   * @param mixed $value
   */
  public function testAssertArrayFailureExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertArray($value);
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertArrayOrTraversable
  */
  public function testAssertArrayOrTraversableWithArray() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertArrayOrTraversable(array())
    );
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertArrayOrTraversable
  */
  public function testAssertArrayOrTraversableWithTraversable() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertArrayOrTraversable(new ArrayIterator(array()))
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertArrayOrTraversable
   * @dataProvider provideInvalidValuesForAssertArrayOrTraversable
   * @param mixed $value
   */
  public function testAssertArrayOrTraversableFailureExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertArrayOrTraversable($value);
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertBoolean
  */
  public function testAssertBooleanWithTrue() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertBoolean(TRUE)
    );
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertBoolean
  */
  public function testAssertBooleanWithFalse() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertBoolean(FALSE)
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertBoolean
   * @dataProvider provideInvalidValuesForAssertBoolean
   * @param mixed $value
   */
  public function testAssertBooleanFailureExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertBoolean($value);
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertCallable
   * @dataProvider provideValidValuesForAssertCallable
   * @param callable $value
   */
  public function testAssertCallable($value) {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertCallable($value)
    );
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertCallable
  */
  public function testAssertCallableWithMethod() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertCallable(array($this, 'testAssertCallableWithMethod'))
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertCallable
   * @dataProvider provideInvalidValuesForAssertCallable
   * @param mixed $value
   */
  public function testAssertCallableFailureExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertCallable($value);
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertContains
  */
  public function testAssertContainsExpectingTrue() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertContains(array('yes', 'no'), 'yes')
    );
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertContains
  */
  public function testAssertContainsExpectingException() {
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Array does not contains the given value.');
    \Papaya\Utility\Constraints::assertContains(array('yes', 'no'), 'maybe');
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertContains
  */
  public function testAssertContainsExpectingExceptionWithIndividualMessage() {
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('NOT IN LIST');
    \Papaya\Utility\Constraints::assertContains(array('yes', 'no'), 'maybe', 'NOT IN LIST');
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertFloat
  */
  public function testAssertFloat() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertFloat(42.21)
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertFloat
   * @dataProvider provideInvalidValuesForAssertFloat
   * @param mixed $value
   */
  public function testAssertFloatFailureExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertFloat($value);
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertInstanceOf
  */
  public function testAssertInstanceOf() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertInstanceOf(stdClass::class, new stdClass)
    );
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertInstanceOf
  */
  public function testAssertInstanceOfWithSuperclass() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertInstanceOf(\PapayaTestCase::class, $this)
    );
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertInstanceOf
  */
  public function testAssertInstanceOfWithTwoClasses() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertInstanceOf(array(stdClass::class, \PapayaTestCase::class), $this)
    );
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertInstanceOf
  */
  public function testAssertInstanceOfFailureExpectingException() {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertInstanceOf(stdClass::class, $this);
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertInstanceOf
  */
  public function testAssertInstanceOfWithTwoClassesFailureExpectingException() {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertInstanceOf(array(Url::class, stdClass::class), $this);
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertInteger
  */
  public function testAssertInteger() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertInteger(42)
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertInteger
   * @dataProvider provideInvalidValuesForAssertInteger
   * @param mixed $value
   */
  public function testAssertIntegerFailureExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertInteger($value);
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertNotEmpty
   * @dataProvider provideValidValuesForAssertNotEmpty
   * @param mixed $value
   */
  public function testAssertNotEmptyWithValidValues($value) {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertNotEmpty($value)
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertNotEmpty
   * @dataProvider provideInvalidValuesForAssertNotEmpty
   * @param mixed $value
   */
  public function testAssertNotEmptyWithInValidValuesExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertNotEmpty($value);
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertNotEmpty
   * @dataProvider provideInvalidValuesForAssertNotEmpty
   * @param mixed $value
   */
  public function testAssertNotEmptyWithInValidValuesExpectingExceptionIndividualMessage($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertNotEmpty($value, 'SAMPLE MESSAGE');
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertNumber
  */
  public function testAssertNumberWithInteger() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertNumber(42)
    );
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertNumber
  */
  public function testAssertNumberWithFloat() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertNumber(42.21)
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertNumber
   * @dataProvider provideInvalidValuesForAssertNumber
   * @param mixed $value
   */
  public function testAssertNumberFailureExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertNumber($value);
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertObject
  */
  public function testAssertObject() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertObject(new stdClass)
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertObject
   * @dataProvider provideInvalidValuesForAssertObject
   * @param mixed $value
   */
  public function testAssertObjectFailureExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertObject($value);
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertObjectOrNull
  */
  public function testAssertObjectOrNullWithObject() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertObjectOrNull(new stdClass)
    );
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertObjectOrNull
  */
  public function testAssertObjectOrNullWithNull() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertObjectOrNull(NULL)
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertObjectOrNull
   * @dataProvider provideInvalidValuesForAssertObjectOrNull
   * @param mixed $value
   */
  public function testAssertObjectOrNullFailureExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertObjectOrNull($value);
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertResource
  */
  public function testAssertResource() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertResource($fh = fopen('php://memory', 'rwb'))
    );
    fclose($fh);
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertResource
  */
  public function testAssertResourceFailureExpectingException() {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertResource('');
  }

  /**
  * @covers \Papaya\Utility\Constraints::assertString
  */
  public function testAssertString() {
    $this->assertTrue(
      \Papaya\Utility\Constraints::assertString('')
    );
  }

  /**
   * @covers \Papaya\Utility\Constraints::assertString
   * @dataProvider provideInvalidValuesForAssertString
   * @param mixed $value
   */
  public function testAssertStringFailureExpectingException($value) {
    $this->expectException(UnexpectedValueException::class);
    \Papaya\Utility\Constraints::assertString($value);
  }

  /**
  * @covers \Papaya\Utility\Constraints::createException
  */
  public function testCreateExceptionWithScalar() {
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Unexpected value type: Expected "string" but "integer" given.');
    throw \PapayaUtilConstraints_TestProxy::createException('string', 42, '');
  }

  /**
  * @covers \Papaya\Utility\Constraints::createException
  */
  public function testCreateExceptionWithObject() {
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Unexpected value type: Expected "integer, float" but "stdClass" given.');
    throw \PapayaUtilConstraints_TestProxy::createException('integer, float', new stdClass, '');
  }

  /**
  * @covers \Papaya\Utility\Constraints::createException
  */
  public function testCreateExceptionWithIndividualMessage() {
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('SAMPLE MESSAGE');
    throw \PapayaUtilConstraints_TestProxy::createException('', new stdClass, 'SAMPLE MESSAGE');
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function provideInvalidValuesForAssertArray() {
    return array(
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertArrayOrTraversable() {
    return array(
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertBoolean() {
    return array(
      'array' => array(array()),
      'float' => array(1.1),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideValidValuesForAssertCallable() {
    return array(
      'function' => array('is_callable'),
      'class function' => array(array(\Papaya\Utility\Constraints::class, 'assertCallable'))
    );
  }

  public static function provideInvalidValuesForAssertCallable() {
    return array(
      'array' => array(array()),
      'float' => array(1.1),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertFloat() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertInteger() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideValidValuesForAssertNotEmpty() {
    return array(
      'array' => array(array(1)),
      'boolean' => array(TRUE),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array('foo'),
    );
  }

  public static function provideInvalidValuesForAssertNotEmpty() {
    return array(
      'array' => array(array()),
      'boolean' => array(FALSE),
      'integer' => array(0),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertNumber() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertObject() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'integer' => array(1),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertObjectOrNull() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'integer' => array(1),
      'string' => array('')
    );
  }

  public static function provideInvalidValuesForAssertString() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'integer' => array(1),
      'object' => array(new stdClass),
      'NULL' => array(NULL)
    );
  }
}

class PapayaUtilConstraints_TestProxy extends \Papaya\Utility\Constraints {

  public static function createException($expected, $value, $message) {
    return parent::createException($expected, $value, $message);
  }
}
