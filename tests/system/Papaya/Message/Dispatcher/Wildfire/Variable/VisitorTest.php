<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaMessageDispatcherWildfireVariableVisitorTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::get
  */
  public function testGet() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $this->assertSame(
      '',
      $visitor->get()
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::getDump
  */
  public function testGetDump() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $visitor->visitBoolean(TRUE);
    $this->assertTrue(
      $visitor->getDump()
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitArray
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::_addElement
  */
  public function testVisitArrayWithEmptyArray() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $visitor->visitArray(array());
    $this->assertAttributeEquals(
      array(),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitArray
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::_addElement
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::_checkIndentLimit
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::_increaseIndent
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::_decreaseIndent
  */
  public function testVisitArrayWithNestedArray() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
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
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitArray
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::_addElement
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::_checkIndentLimit
  */
  public function testVisitArrayWithNestedArrayReachingLimit() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(1, 42);
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
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitBoolean
  */
  public function testVisitBooleanWithTrue() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $visitor->visitBoolean(TRUE);
    $this->assertAttributeEquals(
      TRUE,
      '_dump',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitBoolean
  */
  public function testVisitBooleanWithFalse() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $visitor->visitBoolean(FALSE);
    $this->assertAttributeEquals(
      FALSE,
      '_dump',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitInteger
  */
  public function testVisitInteger() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $visitor->visitInteger(3117);
    $this->assertAttributeEquals(
      3117,
      '_dump',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitFloat
  */
  public function testVisitFloat() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $visitor->visitFloat(31.17);
    $this->assertAttributeEquals(
      31.17,
      '_dump',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitNull
  */
  public function testVisitNull() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $visitor->visitNull(NULL);
    $this->assertAttributeEquals(
      NULL,
      '_dump',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitObject
  */
  public function testVisitObject() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $sample = new PapayaMessageDispatcherWildfireVariableVisitor_SampleClass();
    $visitor->visitObject($sample);
    $this->assertAttributeEquals(
      array(
        '__className' => 'PapayaMessageDispatcherWildfireVariableVisitor_SampleClass #1',
        'private:privateProperty' => 1,
        'protected:protectedProperty' => 2,
        'public:publicProperty' => 3
      ),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitObject
  */
  public function testVisitObjectWithInheritance() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $sample = new PapayaMessageDispatcherWildfireVariableVisitor_SampleChildClass();
    $visitor->visitObject($sample);
    $this->assertAttributeEquals(
      array(
        '__className' => 'PapayaMessageDispatcherWildfireVariableVisitor_SampleChildClass #1',
        'static:private:privateStaticProperty' => 5,
        'static:protected:publicStaticProperty' => 6,
        'public:publicProperty' => 4,
        'protected:protectedProperty' => 2
      ),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitObject
  */
  public function testVisitObjectWithRecursion() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $sample = new PapayaMessageDispatcherWildfireVariableVisitor_SampleClass();
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
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitObject
  */
  public function testVisitObjectWithRecursionLimit() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(1, 42);
    $sample = new PapayaMessageDispatcherWildfireVariableVisitor_SampleClass();
    $sample->recursion = new PapayaMessageDispatcherWildfireVariableVisitor_SampleClass();
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
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitArray
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitObject
  */
  public function testVisitObjectWithDuplication() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $sample = new PapayaMessageDispatcherWildfireVariableVisitor_SampleClass();
    $objects = array($sample, $sample);
    $visitor->visitArray($objects);
    $this->assertAttributeEquals(
      array(
        array(
          '__className' => 'PapayaMessageDispatcherWildfireVariableVisitor_SampleClass #1',
          'private:privateProperty' => 1,
          'protected:protectedProperty' => 2,
          'public:publicProperty' => 3
        ),
        '** Object Duplication (PapayaMessageDispatcherWildfireVariableVisitor_SampleClass #1) **'
      ),
      '_dump',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitResource
  */
  public function testVisitResource() {
    $resource = fopen('php://memory', 'rw');
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $visitor->visitResource($resource);
    $this->assertRegExp(
      "(^\\*\\* Resource id #\d+ \*\*$)D",
      $this->readAttribute($visitor, '_dump')
    );
    fclose($resource);
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitString
  */
  public function testVisitString() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 42);
    $visitor->visitString('Sample');
    $this->assertAttributeEquals(
      'Sample',
      '_dump',
      $visitor
    );
  }

  /**
  * @covers PapayaMessageDispatcherWildfireVariableVisitor::visitString
  */
  public function testVisitLongString() {
    $visitor = new PapayaMessageDispatcherWildfireVariableVisitor(21, 3);
    $visitor->visitString('Sample');
    $this->assertAttributeEquals(
      'Sam...(6)',
      '_dump',
      $visitor
    );
  }
}

class PapayaMessageDispatcherWildfireVariableVisitor_SampleClass{
  private $privateProperty = 1;
  protected $protectedProperty = 2;
  public $publicProperty = 3;
}

class PapayaMessageDispatcherWildfireVariableVisitor_SampleChildClass
  extends PapayaMessageDispatcherWildfireVariableVisitor_SampleClass {
  private static $privateStaticProperty = 5;
  protected static $publicStaticProperty = 6;
  public $publicProperty = 4;
}
