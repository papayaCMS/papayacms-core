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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaMessageContextVariableVisitorStringTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageContextVariableVisitorString::get
  */
  public function testGet() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $this->assertSame(
      '',
      $visitor->get()
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitArray
  * @covers PapayaMessageContextVariableVisitorString::_addLine
  */
  public function testVisitArrayWithEmptyArray() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $visitor->visitArray(array());
    $this->assertAttributeEquals(
      "array(0) {\n}",
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitArray
  * @covers PapayaMessageContextVariableVisitorString::_addLine
  * @covers PapayaMessageContextVariableVisitorString::_increaseIndent
  * @covers PapayaMessageContextVariableVisitorString::_decreaseIndent
  */
  public function testVisitArrayWithNestedArray() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $visitor->visitArray(array(array()));
    $this->assertAttributeEquals(
      "array(1) {\n  [0]=>\n  array(0) {\n  }\n}",
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitArray
  * @covers PapayaMessageContextVariableVisitorString::_addLine
  * @covers PapayaMessageContextVariableVisitorString::_increaseIndent
  */
  public function testVisitArrayWithNestedArrayReachingLimit() {
    $visitor = new PapayaMessageContextVariableVisitorString(1, 42);
    $visitor->visitArray(array(array()));
    $this->assertAttributeEquals(
      "array(1) {\n  ...recursion limit...\n}",
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitBoolean
  */
  public function testVisitBooleanWithTrue() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $visitor->visitBoolean(TRUE);
    $this->assertAttributeEquals(
      'bool(true)',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitBoolean
  */
  public function testVisitBooleanWithFalse() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $visitor->visitBoolean(FALSE);
    $this->assertAttributeEquals(
      'bool(false)',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitInteger
  */
  public function testVisitInteger() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $visitor->visitInteger(3117);
    $this->assertAttributeEquals(
      'int(3117)',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitFloat
  */
  public function testVisitFloat() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $visitor->visitFloat(31.17);
    $this->assertAttributeEquals(
      'float(31.17)',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitNull
  */
  public function testVisitNull() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $visitor->visitNull(NULL);
    $this->assertAttributeEquals(
      'NULL',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitObject
  */
  public function testVisitObject() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $sample = new PapayaMessageContextVariableVisitorString_SampleClass();
    $visitor->visitObject($sample);
    $this->assertAttributeEquals(
      'object(PapayaMessageContextVariableVisitorString_SampleClass) #1 {'."\n".
      '  [private:privateProperty]=>'."\n".
      '  int(1)'."\n".
      '  [protected:protectedProperty]=>'."\n".
      '  int(2)'."\n".
      '  [public:publicProperty]=>'."\n".
      '  int(3)'."\n".
      '  [public:recursion]=>'."\n".
      '  NULL'."\n".
      '}',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitObject
  */
  public function testVisitObjectWithInheritance() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $sample = new PapayaMessageContextVariableVisitorString_SampleChildClass();
    $visitor->visitObject($sample);
    $this->assertAttributeEquals(
      'object(PapayaMessageContextVariableVisitorString_SampleChildClass) #1 {'."\n".
      '  [static:private:privateStaticProperty]=>'."\n".
      '  int(5)'."\n".
      '  [static:protected:publicStaticProperty]=>'."\n".
      '  int(6)'."\n".
      '  [public:publicProperty]=>'."\n".
      '  int(4)'."\n".
      '  [protected:protectedProperty]=>'."\n".
      '  int(2)'."\n".
      '  [public:recursion]=>'."\n".
      '  NULL'."\n".
      '}',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitObject
  */
  public function testVisitObjectWithRecursion() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $sample = new PapayaMessageContextVariableVisitorString_SampleClass();
    $sample->recursion = $sample;
    $visitor->visitObject($sample);
    $this->assertAttributeEquals(
      'object(PapayaMessageContextVariableVisitorString_SampleClass) #1 {'."\n".
      '  [private:privateProperty]=>'."\n".
      '  int(1)'."\n".
      '  [protected:protectedProperty]=>'."\n".
      '  int(2)'."\n".
      '  [public:publicProperty]=>'."\n".
      '  int(3)'."\n".
      '  [public:recursion]=>'."\n".
      '  object(PapayaMessageContextVariableVisitorString_SampleClass) #1 {'."\n".
      '    ...object recursion...'."\n".
      '  }'."\n".
      '}',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitObject
  */
  public function testVisitObjectWithRecursionLimit() {
    $visitor = new PapayaMessageContextVariableVisitorString(1, 42);
    $sample = new PapayaMessageContextVariableVisitorString_SampleClass();
    $sample->recursion = $sample;
    $visitor->visitObject($sample);
    $this->assertAttributeEquals(
      'object(PapayaMessageContextVariableVisitorString_SampleClass) #1 {'."\n".
      '  ...recursion limit...'."\n".
      '}',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitArray
  * @covers PapayaMessageContextVariableVisitorString::visitObject
  */
  public function testVisitObjectWithDuplication() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $sample = new PapayaMessageContextVariableVisitorString_SampleClass();
    $objects = array($sample, $sample);
    $visitor->visitArray($objects);
    $this->assertAttributeEquals(
      'array(2) {'."\n".
      '  [0]=>'."\n".
      '  object(PapayaMessageContextVariableVisitorString_SampleClass) #1 {'."\n".
      '    [private:privateProperty]=>'."\n".
      '    int(1)'."\n".
      '    [protected:protectedProperty]=>'."\n".
      '    int(2)'."\n".
      '    [public:publicProperty]=>'."\n".
      '    int(3)'."\n".
      '    [public:recursion]=>'."\n".
      '    NULL'."\n".
      '  }'."\n".
      '  [1]=>'."\n".
      '  object(PapayaMessageContextVariableVisitorString_SampleClass) #1 {'."\n".
      '    ...object duplication...'."\n".
      '  }'."\n".
      '}',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitResource
  */
  public function testVisitResource() {
    $resource = fopen('php://memory', 'rwb');
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $visitor->visitResource($resource);
    $this->assertRegExp(
      "(^resource\(#\d+\)$)D",
      $this->readAttribute($visitor, '_variableString')
    );
    fclose($resource);
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitString
  */
  public function testVisitString() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 42);
    $visitor->visitString('Sample');
    $this->assertAttributeEquals(
      'string(6) "Sample"',
      '_variableString',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageContextVariableVisitorString::visitString
  */
  public function testVisitLongString() {
    $visitor = new PapayaMessageContextVariableVisitorString(21, 3);
    $visitor->visitString('Sample');
    $this->assertAttributeEquals(
      'string(6) "Sam..."',
      '_variableString',
      $visitor
    );
  }
}

class PapayaMessageContextVariableVisitorString_SampleClass{
  private /** @noinspection PhpUnusedPrivateFieldInspection */
    $privateProperty = 1;
  protected $protectedProperty = 2;
  public $publicProperty = 3;
  public $recursion;
}

class PapayaMessageContextVariableVisitorString_SampleChildClass
  extends PapayaMessageContextVariableVisitorString_SampleClass {
  private static /** @noinspection PhpUnusedPrivateFieldInspection */
    $privateStaticProperty = 5;
  protected static $publicStaticProperty = 6;
  public $publicProperty = 4;
}
