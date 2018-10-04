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

namespace Papaya\Utility {

  class ConstraintsTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Utility\Constraints::assertArray
     */
    public function testAssertArray() {
      $this->assertTrue(
        Constraints::assertArray(array())
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertArray
     * @dataProvider provideInvalidValuesForAssertArray
     * @param mixed $value
     */
    public function testAssertArrayFailureExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertArray($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertArrayOrTraversable
     */
    public function testAssertArrayOrTraversableWithArray() {
      $this->assertTrue(
        Constraints::assertArrayOrTraversable(array())
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertArrayOrTraversable
     */
    public function testAssertArrayOrTraversableWithTraversable() {
      $this->assertTrue(
        Constraints::assertArrayOrTraversable(new \ArrayIterator(array()))
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertArrayOrTraversable
     * @dataProvider provideInvalidValuesForAssertArrayOrTraversable
     * @param mixed $value
     */
    public function testAssertArrayOrTraversableFailureExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertArrayOrTraversable($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertBoolean
     */
    public function testAssertBooleanWithTrue() {
      $this->assertTrue(
        Constraints::assertBoolean(TRUE)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertBoolean
     */
    public function testAssertBooleanWithFalse() {
      $this->assertTrue(
        Constraints::assertBoolean(FALSE)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertBoolean
     * @dataProvider provideInvalidValuesForAssertBoolean
     * @param mixed $value
     */
    public function testAssertBooleanFailureExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertBoolean($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertCallable
     * @dataProvider provideValidValuesForAssertCallable
     * @param callable $value
     */
    public function testAssertCallable($value) {
      $this->assertTrue(
        Constraints::assertCallable($value)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertCallable
     */
    public function testAssertCallableWithMethod() {
      $this->assertTrue(
        Constraints::assertCallable(array($this, 'testAssertCallableWithMethod'))
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertCallable
     * @dataProvider provideInvalidValuesForAssertCallable
     * @param mixed $value
     */
    public function testAssertCallableFailureExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertCallable($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertContains
     */
    public function testAssertContainsExpectingTrue() {
      $this->assertTrue(
        Constraints::assertContains(array('yes', 'no'), 'yes')
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertContains
     */
    public function testAssertContainsExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Array does not contains the given value.');
      Constraints::assertContains(array('yes', 'no'), 'maybe');
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertContains
     */
    public function testAssertContainsExpectingExceptionWithIndividualMessage() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('NOT IN LIST');
      Constraints::assertContains(array('yes', 'no'), 'maybe', 'NOT IN LIST');
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertFloat
     */
    public function testAssertFloat() {
      $this->assertTrue(
        Constraints::assertFloat(42.21)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertFloat
     * @dataProvider provideInvalidValuesForAssertFloat
     * @param mixed $value
     */
    public function testAssertFloatFailureExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertFloat($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertInstanceOf
     */
    public function testAssertInstanceOf() {
      $this->assertTrue(
        Constraints::assertInstanceOf(\stdClass::class, new \stdClass)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertInstanceOf
     */
    public function testAssertInstanceOfWithSuperclass() {
      $this->assertTrue(
        Constraints::assertInstanceOf(\Papaya\TestCase::class, $this)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertInstanceOf
     */
    public function testAssertInstanceOfWithTwoClasses() {
      $this->assertTrue(
        Constraints::assertInstanceOf(array(\stdClass::class, \Papaya\TestCase::class), $this)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertInstanceOf
     */
    public function testAssertInstanceOfFailureExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertInstanceOf(\stdClass::class, $this);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertInstanceOf
     */
    public function testAssertInstanceOfWithTwoClassesFailureExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertInstanceOf(array(\Papaya\URL::class, \stdClass::class), $this);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertInteger
     */
    public function testAssertInteger() {
      $this->assertTrue(
        Constraints::assertInteger(42)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertInteger
     * @dataProvider provideInvalidValuesForAssertInteger
     * @param mixed $value
     */
    public function testAssertIntegerFailureExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertInteger($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertNotEmpty
     * @dataProvider provideValidValuesForAssertNotEmpty
     * @param mixed $value
     */
    public function testAssertNotEmptyWithValidValues($value) {
      $this->assertTrue(
        Constraints::assertNotEmpty($value)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertNotEmpty
     * @dataProvider provideInvalidValuesForAssertNotEmpty
     * @param mixed $value
     */
    public function testAssertNotEmptyWithInValidValuesExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertNotEmpty($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertNotEmpty
     * @dataProvider provideInvalidValuesForAssertNotEmpty
     * @param mixed $value
     */
    public function testAssertNotEmptyWithInValidValuesExpectingExceptionIndividualMessage($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertNotEmpty($value, 'SAMPLE MESSAGE');
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertNumber
     */
    public function testAssertNumberWithInteger() {
      $this->assertTrue(
        Constraints::assertNumber(42)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertNumber
     */
    public function testAssertNumberWithFloat() {
      $this->assertTrue(
        Constraints::assertNumber(42.21)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertNumber
     * @dataProvider provideInvalidValuesForAssertNumber
     * @param mixed $value
     */
    public function testAssertNumberFailureExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertNumber($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertObject
     */
    public function testAssertObject() {
      $this->assertTrue(
        Constraints::assertObject(new \stdClass)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertObject
     * @dataProvider provideInvalidValuesForAssertObject
     * @param mixed $value
     */
    public function testAssertObjectFailureExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertObject($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertObjectOrNull
     */
    public function testAssertObjectOrNullWithObject() {
      $this->assertTrue(
        Constraints::assertObjectOrNull(new \stdClass)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertObjectOrNull
     */
    public function testAssertObjectOrNullWithNull() {
      $this->assertTrue(
        Constraints::assertObjectOrNull(NULL)
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertObjectOrNull
     * @dataProvider provideInvalidValuesForAssertObjectOrNull
     * @param mixed $value
     */
    public function testAssertObjectOrNullFailureExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertObjectOrNull($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertResource
     */
    public function testAssertResource() {
      $this->assertTrue(
        Constraints::assertResource($fh = fopen('php://memory', 'rwb'))
      );
      fclose($fh);
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertResource
     */
    public function testAssertResourceFailureExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertResource('');
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertString
     */
    public function testAssertString() {
      $this->assertTrue(
        Constraints::assertString('')
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertString
     * @dataProvider provideInvalidValuesForAssertString
     * @param mixed $value
     */
    public function testAssertStringFailureExpectingException($value) {
      $this->expectException(\UnexpectedValueException::class);
      Constraints::assertString($value);
    }

    /**
     * @covers \Papaya\Utility\Constraints::createException
     */
    public function testCreateExceptionWithScalar() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Unexpected value type: Expected "string" but "integer" given.');
      throw Constraints_TestProxy::createException('string', 42, '');
    }

    /**
     * @covers \Papaya\Utility\Constraints::createException
     */
    public function testCreateExceptionWithObject() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Unexpected value type: Expected "integer, float" but "stdClass" given.');
      throw Constraints_TestProxy::createException('integer, float', new \stdClass, '');
    }

    /**
     * @covers \Papaya\Utility\Constraints::createException
     */
    public function testCreateExceptionWithIndividualMessage() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('SAMPLE MESSAGE');
      throw Constraints_TestProxy::createException('', new \stdClass, 'SAMPLE MESSAGE');
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertStringCastable
     */
    public function testAssertStringCastableWithStringExpectingTrue() {
      $this->assertTrue(
        Constraints::assertStringCastable('foo')
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertStringCastable
     */
    public function testAssertStringCastableWithObjectImplementingTheMagicMethodExpectingTrue() {
      $this->assertTrue(
        Constraints::assertStringCastable(new ConstraintsTest_ImplementingToString())
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertStringCastable
     */
    public function testAssertStringCastableWithObjectImplementingStringCastableInterfaceExpectingTrue() {
      $this->assertTrue(
        Constraints::assertStringCastable(
          $this->createMock(\Papaya\BaseObject\Interfaces\StringCastable::class)
        )
      );
    }

    /**
     * @covers \Papaya\Utility\Constraints::assertStringCastable
     */
    public function testAssertStringCastableWithStdClassExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Unexpected value type: Expected "string, string castable" but "stdClass" given.');
      $this->assertTrue(
        Constraints::assertStringCastable(new \stdClass())
      );
    }

    /*************************************
     * Data Provider
     *************************************/

    public static function provideInvalidValuesForAssertArray() {
      return array(
        'boolean' => array(TRUE),
        'float' => array(1.1),
        'integer' => array(1),
        'object' => array(new \stdClass),
        'string' => array(''),
        'NULL' => array(NULL)
      );
    }

    public static function provideInvalidValuesForAssertArrayOrTraversable() {
      return array(
        'boolean' => array(TRUE),
        'float' => array(1.1),
        'integer' => array(1),
        'object' => array(new \stdClass),
        'string' => array(''),
        'NULL' => array(NULL)
      );
    }

    public static function provideInvalidValuesForAssertBoolean() {
      return array(
        'array' => array(array()),
        'float' => array(1.1),
        'integer' => array(1),
        'object' => array(new \stdClass),
        'string' => array(''),
        'NULL' => array(NULL)
      );
    }

    public static function provideValidValuesForAssertCallable() {
      return array(
        'function' => array('is_callable'),
        'class function' => array(array(Constraints::class, 'assertCallable'))
      );
    }

    public static function provideInvalidValuesForAssertCallable() {
      return array(
        'array' => array(array()),
        'float' => array(1.1),
        'integer' => array(1),
        'object' => array(new \stdClass),
        'string' => array(''),
        'NULL' => array(NULL)
      );
    }

    public static function provideInvalidValuesForAssertFloat() {
      return array(
        'array' => array(array()),
        'boolean' => array(TRUE),
        'integer' => array(1),
        'object' => array(new \stdClass),
        'string' => array(''),
        'NULL' => array(NULL)
      );
    }

    public static function provideInvalidValuesForAssertInteger() {
      return array(
        'array' => array(array()),
        'boolean' => array(TRUE),
        'float' => array(1.1),
        'object' => array(new \stdClass),
        'string' => array(''),
        'NULL' => array(NULL)
      );
    }

    public static function provideValidValuesForAssertNotEmpty() {
      return array(
        'array' => array(array(1)),
        'boolean' => array(TRUE),
        'integer' => array(1),
        'object' => array(new \stdClass),
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
        'object' => array(new \stdClass),
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
        'object' => array(new \stdClass),
        'NULL' => array(NULL)
      );
    }
  }

  class Constraints_TestProxy extends Constraints {

    public static function createException($expected, $value, $message) {
      return parent::createException($expected, $value, $message);
    }
  }

  class ConstraintsTest_ImplementingToString {
    public function __toString() {
      return 'success';
    }
  }
}
