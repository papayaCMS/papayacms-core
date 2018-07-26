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

class PapayaMessageContextVariableVisitorXhtmlTest extends \PapayaTestCase {

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::__construct
  * @covers \PapayaMessageContextVariableVisitorXhtml::get
  */
  public function testGet() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $this->assertEquals(
      '<ul class="variableDump"></ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitArray
  */
  public function testVisitArrayWithEmptyArray() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $visitor->visitArray(array());
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>array</strong>(<em class="number">0</em>) {}</li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitArray
  * @covers \PapayaMessageContextVariableVisitorXhtml::_increaseIndent
  * @covers \PapayaMessageContextVariableVisitorXhtml::_decreaseIndent
  */
  public function testVisitArrayWithNestedArray() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $visitor->visitArray(array(array()));
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>array</strong>(<em class="number">1</em>) {'.
      '<ul>'.
      '<li>[<em class="number">0</em>]=&gt;</li>'.
      '<li><strong>array</strong>(<em class="number">0</em>) {}</li>'.
      '</ul>'.
      '}</li></ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitArray
  * @covers \PapayaMessageContextVariableVisitorXhtml::_increaseIndent
  */
  public function testVisitArrayWithNestedArrayReachingLimit() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(1, 42);
    $visitor->visitArray(array(array()));
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>array</strong>(<em class="number">1</em>) {'.
      '<em class="string">...recursion limit...</em>'.
      '}</li></ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitBoolean
  */
  public function testVisitBooleanWithTrue() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $visitor->visitBoolean(TRUE);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>bool</strong>(<em class="boolean">true</em>)</li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitBoolean
  */
  public function testVisitBooleanWithFalse() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $visitor->visitBoolean(FALSE);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>bool</strong>(<em class="boolean">false</em>)</li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitInteger
  * @covers \PapayaMessageContextVariableVisitorXhtml::_createListNode
  * @covers \PapayaMessageContextVariableVisitorXhtml::_addTypeNode
  * @covers \PapayaMessageContextVariableVisitorXhtml::_addValueNode
  * @covers \PapayaMessageContextVariableVisitorXhtml::_addText
  */
  public function testVisitInteger() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $visitor->visitInteger(42);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>int</strong>(<em class="number">42</em>)</li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitFloat
  */
  public function testVisitFloat() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $visitor->visitFloat(42.21);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>float</strong>(<em class="number">42.21</em>)</li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitNull
  */
  public function testVisitNull() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $visitor->visitNull(NULL);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>null</strong></li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitObject
  */
  public function testVisitObject() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $sample = new \PapayaMessageContextVariableVisitorXhtml_SampleClass();
    $visitor->visitObject($sample);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>object</strong>'.
      '(<em class="string">PapayaMessageContextVariableVisitorXhtml_SampleClass</em>) #1 {'.
      '<ul>'.
      '<li>[<em class="string">private:privateProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">1</em>)</li>'.
      '<li>[<em class="string">protected:protectedProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">2</em>)</li>'.
      '<li>[<em class="string">public:publicProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">3</em>)</li>'.
      '<li>[<em class="string">public:recursion</em>]=&gt;</li>'.
      '<li><strong>null</strong></li>'.
      '</ul>}'.
      '</li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitObject
  */
  public function testVisitObjectWithInheritance() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $sample = new \PapayaMessageContextVariableVisitorXhtml_SampleChildClass();
    $visitor->visitObject($sample);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>object</strong>'.
      '(<em class="string">PapayaMessageContextVariableVisitorXhtml_SampleChildClass</em>) #1 {'.
      '<ul>'.
      '<li>[<em class="string">static:private:privateStaticProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">5</em>)</li>'.
      '<li>[<em class="string">static:protected:publicStaticProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">6</em>)</li>'.
      '<li>[<em class="string">public:publicProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">4</em>)</li>'.
      '<li>[<em class="string">protected:protectedProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">2</em>)</li>'.
      '<li>[<em class="string">public:recursion</em>]=&gt;</li><li><strong>null</strong></li>'.
      '</ul>}'.
      '</li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitObject
  */
  public function testVisitObjectWithRecursion() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $sample = new \PapayaMessageContextVariableVisitorXhtml_SampleClass();
    $sample->recursion = $sample;
    $visitor->visitObject($sample);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>object</strong>'.
      '(<em class="string">PapayaMessageContextVariableVisitorXhtml_SampleClass</em>) #1 {'.
      '<ul>'.
      '<li>[<em class="string">private:privateProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">1</em>)</li>'.
      '<li>[<em class="string">protected:protectedProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">2</em>)</li>'.
      '<li>[<em class="string">public:publicProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">3</em>)</li>'.
      '<li>[<em class="string">public:recursion</em>]=&gt;</li>'.
      '<li><strong>object</strong>'.
      '(<em class="string">PapayaMessageContextVariableVisitorXhtml_SampleClass</em>) #1 {'.
      '<em class="string">...object recursion...</em>}'.
      '</li>'.
      '</ul>}'.
      '</li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitObject
  */
  public function testVisitObjectWithRecursionLimit() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(1, 42);
    $sample = new \PapayaMessageContextVariableVisitorXhtml_SampleClass();
    $sample->recursion = $sample;
    $visitor->visitObject($sample);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>object</strong>'.
      '(<em class="string">PapayaMessageContextVariableVisitorXhtml_SampleClass</em>) #1 {'.
      '<em class="string">...recursion limit...</em>}'.
      '</li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitArray
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitObject
  */
  public function testVisitObjectWithDuplication() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $sample = new \PapayaMessageContextVariableVisitorXhtml_SampleClass();
    $objects = array($sample, $sample);
    $visitor->visitArray($objects);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>array</strong>(<em class="number">2</em>) {'.
      '<ul>'.
      '<li>[<em class="number">0</em>]=&gt;</li>'.
      '<li><strong>object</strong>'.
      '(<em class="string">PapayaMessageContextVariableVisitorXhtml_SampleClass</em>) #1 {'.
      '<ul>'.
      '<li>[<em class="string">private:privateProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">1</em>)</li>'.
      '<li>[<em class="string">protected:protectedProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">2</em>)</li>'.
      '<li>[<em class="string">public:publicProperty</em>]=&gt;</li>'.
      '<li><strong>int</strong>(<em class="number">3</em>)</li>'.
      '<li>[<em class="string">public:recursion</em>]=&gt;</li>'.
      '<li><strong>null</strong></li>'.
      '</ul>}'.
      '</li>'.
      '<li>[<em class="number">1</em>]=&gt;</li>'.
      '<li><strong>object</strong>'.
      '(<em class="string">PapayaMessageContextVariableVisitorXhtml_SampleClass</em>) #1 {'.
      '<em class="string">...object duplication...</em>}</li>'.
      '</ul>}'.
      '</li>'.
      '</ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitResource
  */
  public function testVisitResource() {
    $resource = fopen('php://memory', 'rwb');
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $visitor->visitResource($resource);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>resource</strong>(#<em class="number">'.((int)$resource).'</em>)</li>'.
      '</ul>',
      $visitor->get()
    );
    fclose($resource);
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitString
  */
  public function testVisitString() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 42);
    $visitor->visitString('Sample');
    $this->assertEquals(
      '<ul class="variableDump"><li>'.
      '<strong>string</strong>(<em class="number">6</em>)'.
      ' "<em class="string">Sample</em>"'.
      '</li></ul>',
      $visitor->get()
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitorXhtml::visitString
  */
  public function testVisitLongString() {
    $visitor = new \PapayaMessageContextVariableVisitorXhtml(21, 3);
    $visitor->visitString('Sample');
    $this->assertEquals(
      '<ul class="variableDump"><li>'.
      '<strong>string</strong>(<em class="number">6</em>)'.
      ' "<em class="string">Sam...</em>"'.
      '</li></ul>',
      $visitor->get()
    );
  }
}

class PapayaMessageContextVariableVisitorXhtml_SampleClass{
  private /** @noinspection PhpUnusedPrivateFieldInspection */
    $privateProperty = 1;
  protected $protectedProperty = 2;
  public $publicProperty = 3;
  public $recursion;
}

class PapayaMessageContextVariableVisitorXhtml_SampleChildClass
  extends \PapayaMessageContextVariableVisitorXhtml_SampleClass {
  private static /** @noinspection PhpUnusedPrivateFieldInspection */
    $privateStaticProperty = 5;
  protected static $publicStaticProperty = 6;
  public $publicProperty = 4;
}
