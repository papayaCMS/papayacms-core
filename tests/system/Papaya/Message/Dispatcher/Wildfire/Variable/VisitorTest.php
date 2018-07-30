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

class PapayaMessageDispatcherWildfireVariableVisitorTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::get
  */
  public function testGet() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $this->assertSame(
      '',
      $visitor->get()
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::getDump
  */
  public function testGetDump() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $visitor->visitBoolean(TRUE);
    $this->assertTrue(
      $visitor->getDump()
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitArray
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::_addElement
  */
  public function testVisitArrayWithEmptyArray() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $visitor->visitArray(array());
    $this->assertAttributeEquals(
      array(),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitArray
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::_addElement
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::_checkIndentLimit
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::_increaseIndent
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::_decreaseIndent
  */
  public function testVisitArrayWithNestedArray() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $visitor->visitArray(array(array()));
    $this->assertAttributeEquals(
      array(
        array(
        )
      ),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitArray
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::_addElement
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::_checkIndentLimit
  */
  public function testVisitArrayWithNestedArrayReachingLimit() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(1, 42);
    $visitor->visitArray(array(array()));
    $this->assertAttributeEquals(
      array(
        0 => '** Max Recursion Depth (1) **'
      ),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitBoolean
  */
  public function testVisitBooleanWithTrue() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $visitor->visitBoolean(TRUE);
    $this->assertAttributeEquals(
      TRUE,
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitBoolean
  */
  public function testVisitBooleanWithFalse() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $visitor->visitBoolean(FALSE);
    $this->assertAttributeEquals(
      FALSE,
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitInteger
  */
  public function testVisitInteger() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $visitor->visitInteger(3117);
    $this->assertAttributeEquals(
      3117,
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitFloat
  */
  public function testVisitFloat() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $visitor->visitFloat(31.17);
    $this->assertAttributeEquals(
      31.17,
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitNull
  */
  public function testVisitNull() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $visitor->visitNull(NULL);
    $this->assertAttributeEquals(
      NULL,
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitObject
  */
  public function testVisitObject() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $sample = new \PapayaMessageDispatcherWildfireVariableVisitor_SampleClass();
    $visitor->visitObject($sample);
    $this->assertAttributeEquals(
      array(
        '__className' => 'PapayaMessageDispatcherWildfireVariableVisitor_SampleClass #1',
        'private:privateProperty' => 1,
        'protected:protectedProperty' => 2,
        'public:publicProperty' => 3,
        'public:recursion' => NULL
      ),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitObject
  */
  public function testVisitObjectWithInheritance() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $sample = new \PapayaMessageDispatcherWildfireVariableVisitor_SampleChildClass();
    $visitor->visitObject($sample);
    $this->assertAttributeEquals(
      array(
        '__className' => 'PapayaMessageDispatcherWildfireVariableVisitor_SampleChildClass #1',
        'static:private:privateStaticProperty' => 5,
        'static:protected:publicStaticProperty' => 6,
        'public:publicProperty' => 4,
        'protected:protectedProperty' => 2,
        'public:recursion' => NULL
      ),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitObject
  */
  public function testVisitObjectWithRecursion() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $sample = new \PapayaMessageDispatcherWildfireVariableVisitor_SampleClass();
    $sample->recursion = $sample;
    $visitor->visitObject($sample);
    $this->assertAttributeEquals(
      array(
        '__className' => 'PapayaMessageDispatcherWildfireVariableVisitor_SampleClass #1',
        'private:privateProperty' => 1,
        'protected:protectedProperty' => 2,
        'public:publicProperty' => 3,
        'public:recursion' =>
          '** Object Recursion (PapayaMessageDispatcherWildfireVariableVisitor_SampleClass #1) **'
      ),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitObject
  */
  public function testVisitObjectWithRecursionLimit() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(1, 42);
    $sample = new \PapayaMessageDispatcherWildfireVariableVisitor_SampleClass();
    $sample->recursion = new \PapayaMessageDispatcherWildfireVariableVisitor_SampleClass();
    $visitor->visitObject($sample);
    $this->assertAttributeEquals(
      array(
        '__className' => 'PapayaMessageDispatcherWildfireVariableVisitor_SampleClass #1',
        'private:privateProperty' => 1,
        'protected:protectedProperty' => 2,
        'public:publicProperty' => 3,
        'public:recursion' =>
          '** Max Recursion Depth (1) **'
      ),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitArray
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitObject
  */
  public function testVisitObjectWithDuplication() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $sample = new \PapayaMessageDispatcherWildfireVariableVisitor_SampleClass();
    $objects = array($sample, $sample);
    $visitor->visitArray($objects);
    $this->assertAttributeEquals(
      array(
        array(
          '__className' => 'PapayaMessageDispatcherWildfireVariableVisitor_SampleClass #1',
          'private:privateProperty' => 1,
          'protected:protectedProperty' => 2,
          'public:publicProperty' => 3,
          'public:recursion' => NULL
        ),
        '** Object Duplication (PapayaMessageDispatcherWildfireVariableVisitor_SampleClass #1) **'
      ),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitResource
  */
  public function testVisitResource() {
    $resource = fopen('php://memory', 'rwb');
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $visitor->visitResource($resource);
    $this->assertRegExp(
      "(^\\*\\* Resource id #\d+ \*\*$)D",
      $this->readAttribute($visitor, '_dump')
    );
    fclose($resource);
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitString
  */
  public function testVisitString() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 42);
    $visitor->visitString('Sample');
    $this->assertAttributeEquals(
      'Sample',
      '_dump',
      $visitor
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor::visitString
  */
  public function testVisitLongString() {
    $visitor = new \Papaya\Message\Dispatcher\Wildfire\Variable\Visitor(21, 3);
    $visitor->visitString('Sample');
    $this->assertAttributeEquals(
      'Sam...(6)',
      '_dump',
      $visitor
    );
  }
}

class PapayaMessageDispatcherWildfireVariableVisitor_SampleClass{
  private /** @noinspection PhpUnusedPrivateFieldInspection */
    $privateProperty = 1;
  protected $protectedProperty = 2;
  public $publicProperty = 3;
  public $recursion;
}

class PapayaMessageDispatcherWildfireVariableVisitor_SampleChildClass
  extends \PapayaMessageDispatcherWildfireVariableVisitor_SampleClass {
  private static /** @noinspection PhpUnusedPrivateFieldInspection */
    $privateStaticProperty = 5;
  protected static $publicStaticProperty = 6;
  public $publicProperty = 4;
}
