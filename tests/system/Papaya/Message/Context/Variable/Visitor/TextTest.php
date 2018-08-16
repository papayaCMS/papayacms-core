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

namespace Papaya\Message\Context\Variable\Visitor {

  class TextTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::get
     */
    public function testGet() {
      $visitor = new Text(21, 42);
      $this->assertSame(
        '',
        $visitor->get()
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitArray
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::_addLine
     */
    public function testVisitArrayWithEmptyArray() {
      $visitor = new Text(21, 42);
      $visitor->visitArray(array());
      $this->assertAttributeEquals(
        "array(0) {\n}",
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitArray
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::_addLine
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::_increaseIndent
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::_decreaseIndent
     */
    public function testVisitArrayWithNestedArray() {
      $visitor = new Text(21, 42);
      $visitor->visitArray(array(array()));
      $this->assertAttributeEquals(
        "array(1) {\n  [0]=>\n  array(0) {\n  }\n}",
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitArray
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::_addLine
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::_increaseIndent
     */
    public function testVisitArrayWithNestedArrayReachingLimit() {
      $visitor = new Text(1, 42);
      $visitor->visitArray(array(array()));
      $this->assertAttributeEquals(
        "array(1) {\n  ...recursion limit...\n}",
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitBoolean
     */
    public function testVisitBooleanWithTrue() {
      $visitor = new Text(21, 42);
      $visitor->visitBoolean(TRUE);
      $this->assertAttributeEquals(
        'bool(true)',
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitBoolean
     */
    public function testVisitBooleanWithFalse() {
      $visitor = new Text(21, 42);
      $visitor->visitBoolean(FALSE);
      $this->assertAttributeEquals(
        'bool(false)',
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitInteger
     */
    public function testVisitInteger() {
      $visitor = new Text(21, 42);
      $visitor->visitInteger(3117);
      $this->assertAttributeEquals(
        'int(3117)',
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitFloat
     */
    public function testVisitFloat() {
      $visitor = new Text(21, 42);
      $visitor->visitFloat(31.17);
      $this->assertAttributeEquals(
        'float(31.17)',
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitNull
     */
    public function testVisitNull() {
      $visitor = new Text(21, 42);
      $visitor->visitNull(NULL);
      $this->assertAttributeEquals(
        'NULL',
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitObject
     */
    public function testVisitObject() {
      $visitor = new Text(21, 42);
      $sample = new TextVisitor_SampleClass();
      $visitor->visitObject($sample);
      $this->assertAttributeEquals(
        'object(Papaya\Message\Context\Variable\Visitor\TextVisitor_SampleClass) #1 {'."\n".
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
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitObject
     */
    public function testVisitObjectWithInheritance() {
      $visitor = new Text(21, 42);
      $sample = new TextVisitor_SampleChildClass();
      $visitor->visitObject($sample);
      $this->assertAttributeEquals(
        'object(Papaya\Message\Context\Variable\Visitor\TextVisitor_SampleChildClass) #1 {'."\n".
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
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitObject
     */
    public function testVisitObjectWithRecursion() {
      $visitor = new Text(21, 42);
      $sample = new TextVisitor_SampleClass();
      $sample->recursion = $sample;
      $visitor->visitObject($sample);
      $this->assertAttributeEquals(
        'object(Papaya\Message\Context\Variable\Visitor\TextVisitor_SampleClass) #1 {'."\n".
        '  [private:privateProperty]=>'."\n".
        '  int(1)'."\n".
        '  [protected:protectedProperty]=>'."\n".
        '  int(2)'."\n".
        '  [public:publicProperty]=>'."\n".
        '  int(3)'."\n".
        '  [public:recursion]=>'."\n".
        '  object(Papaya\Message\Context\Variable\Visitor\TextVisitor_SampleClass) #1 {'."\n".
        '    ...object recursion...'."\n".
        '  }'."\n".
        '}',
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitObject
     */
    public function testVisitObjectWithRecursionLimit() {
      $visitor = new Text(1, 42);
      $sample = new TextVisitor_SampleClass();
      $sample->recursion = $sample;
      $visitor->visitObject($sample);
      $this->assertAttributeEquals(
        'object(Papaya\Message\Context\Variable\Visitor\TextVisitor_SampleClass) #1 {'."\n".
        '  ...recursion limit...'."\n".
        '}',
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitArray
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitObject
     */
    public function testVisitObjectWithDuplication() {
      $visitor = new Text(21, 42);
      $sample = new TextVisitor_SampleClass();
      $objects = array($sample, $sample);
      $visitor->visitArray($objects);
      $this->assertAttributeEquals(
        'array(2) {'."\n".
        '  [0]=>'."\n".
        '  object(Papaya\Message\Context\Variable\Visitor\TextVisitor_SampleClass) #1 {'."\n".
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
        '  object(Papaya\Message\Context\Variable\Visitor\TextVisitor_SampleClass) #1 {'."\n".
        '    ...object duplication...'."\n".
        '  }'."\n".
        '}',
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitResource
     */
    public function testVisitResource() {
      $resource = fopen('php://memory', 'rwb');
      $visitor = new Text(21, 42);
      $visitor->visitResource($resource);
      $this->assertRegExp(
        "(^resource\(#\d+\)$)D",
        $this->readAttribute($visitor, '_variableString')
      );
      fclose($resource);
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitString
     */
    public function testVisitString() {
      $visitor = new Text(21, 42);
      $visitor->visitString('Sample');
      $this->assertAttributeEquals(
        'string(6) "Sample"',
        '_variableString',
        $visitor
      );
    }

    /**
     * @covers \Papaya\Message\Context\Variable\Visitor\Text::visitString
     */
    public function testVisitLongString() {
      $visitor = new Text(21, 3);
      $visitor->visitString('Sample');
      $this->assertAttributeEquals(
        'string(6) "Sam..."',
        '_variableString',
        $visitor
      );
    }
  }

  class TextVisitor_SampleClass {
    private /** @noinspection PhpUnusedPrivateFieldInspection */
      $privateProperty = 1;
    protected $protectedProperty = 2;
    public $publicProperty = 3;
    public $recursion;
  }

  require_once __DIR__.'/../../../../../../bootstrap.php';

  class TextVisitor_SampleChildClass
    extends TextVisitor_SampleClass {
    private static /** @noinspection PhpUnusedPrivateFieldInspection */
      $privateStaticProperty = 5;
    protected static $publicStaticProperty = 6;
    public $publicProperty = 4;
  }
}
