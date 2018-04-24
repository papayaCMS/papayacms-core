<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageContextBacktraceTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageContextBacktrace::__construct
  * @covers PapayaMessageContextBacktrace::setOffset
  */
  public function testContructorWithOffset() {
    $backtrace = new PapayaMessageContextBacktrace(41);
    $this->assertAttributeEquals(
      42,
      '_offset',
      $backtrace
    );
  }

  /**
  * @covers PapayaMessageContextBacktrace::__construct
  * @covers PapayaMessageContextBacktrace::setOffset
  */
  public function testContructorWithOffsetAndTraceData() {
    $backtrace = new PapayaMessageContextBacktrace(42, array());
    $this->assertAttributeEquals(
      42,
      '_offset',
      $backtrace
    );
  }

  /**
  * @covers PapayaMessageContextBacktrace::__construct
  * @covers PapayaMessageContextBacktrace::setOffset
  */
  public function testContructorWithoutOffset() {
    $backtrace = new PapayaMessageContextBacktrace();
    $this->assertAttributeEquals(
      1,
      '_offset',
      $backtrace
    );
  }

  /**
  * @covers PapayaMessageContextBacktrace::setOffset
  */
  public function testSetOffsetWithInvalidOffsetExpectingException() {
    $backtrace = new PapayaMessageContextBacktrace();
    $this->setExpectedException(InvalidArgumentException::class);
    $backtrace->setOffset(-1);
  }

  /**
  * @covers PapayaMessageContextBacktrace::setBacktrace
  */
  public function testSetBacktrace() {
    $backtrace = new PapayaMessageContextBacktrace();
    $backtrace->setBacktrace(array(1), 42);
    $this->assertAttributeEquals(
      array(1),
      '_backtrace',
      $backtrace
    );
    $this->assertAttributeEquals(
      42,
      '_offset',
      $backtrace
    );
  }

  /**
  * @covers PapayaMessageContextBacktrace::getBacktrace
  */
  public function testGetBacktrace() {
    $backtrace = new PapayaMessageContextBacktrace();
    $backtrace->setBacktrace(array(1));
    $this->assertEquals(
      array(1),
      $backtrace->getBacktrace()
    );
  }

  /**
  * @covers PapayaMessageContextBacktrace::getBacktrace
  */
  public function testGetBacktraceImplicitCreate() {
    $backtrace = new PapayaMessageContextBacktrace();
    $this->assertInternalType(
      'array',
      $backtrace->getBacktrace()
    );
  }

  /**
  * @covers PapayaMessageContextBacktrace::asArray
  */
  public function testAsArray() {
    $backtrace = new PapayaMessageContextBacktrace();
    $backtrace->setBacktrace(
      $this->getBacktraceFixture()
    );
    $this->assertEquals(
      array(
        'function() test.php:23',
        'testClass::staticFunction() testClass.php:21',
        'testClass->method() testClass.php:42'
      ),
      $backtrace->asArray()
    );
  }

  /**
  * @covers PapayaMessageContextBacktrace::asArray
  */
  public function testAsArrayWithOffset() {
    $backtrace = new PapayaMessageContextBacktrace();
    $backtrace->setBacktrace(
      $this->getBacktraceFixture(),
      2
    );
    $this->assertEquals(
      array(
        'testClass->method() testClass.php:42'
      ),
      $backtrace->asArray()
    );
  }

  /**
  * @covers PapayaMessageContextBacktrace::asString
  */
  public function testAsString() {
    $backtrace = new PapayaMessageContextBacktrace();
    $backtrace->setBacktrace(
      $this->getBacktraceFixture()
    );
    $this->assertEquals(
      'function() test.php:23'."\n".
        'testClass::staticFunction() testClass.php:21'."\n".
        'testClass->method() testClass.php:42',
      $backtrace->asString()
    );
  }

  /**
  * @covers PapayaMessageContextBacktrace::asXhtml
  */
  public function testAsXhtml() {
    $backtrace = new PapayaMessageContextBacktrace();
    $backtrace->setBacktrace(
      $this->getBacktraceFixture()
    );
    $this->assertEquals(
      'function() test.php:23'."<br />\n".
        'testClass::staticFunction() testClass.php:21'."<br />\n".
        'testClass-&gt;method() testClass.php:42',
      $backtrace->asXhtml()
    );
  }

  /**
  * @covers PapayaMessageContextBacktrace::getLabel
  */
  public function testGetLabel() {
    $backtrace = new PapayaMessageContextBacktrace();
    $this->assertEquals(
      'Backtrace',
      $backtrace->getLabel()
    );
  }

  public function getBacktraceFixture() {
    return array(
      array(
       'function' => 'function',
       'file' => 'test.php',
       'line' => 23
      ),
      array(
       'function' => 'staticFunction',
       'file' => 'testClass.php',
       'line' => 21,
       'class' => 'testClass',
       'type' => '::'
      ),
      array(
       'function' => 'method',
       'file' => 'testClass.php',
       'line' => 42,
       'class' => 'testClass',
       'type' => '->'
      )
    );
  }

}
