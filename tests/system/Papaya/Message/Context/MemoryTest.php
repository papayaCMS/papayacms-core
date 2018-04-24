<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageContextMemoryTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageContextMemory::__construct
  */
  public function testContructor() {
    $context = new PapayaMessageContextMemory();
    $this->assertAttributeGreaterThan(
      0,
      '_currentUsage',
      $context
    );
    $this->assertAttributeGreaterThan(
      0,
      '_peakUsage',
      $context
    );
  }

  /**
  * @covers PapayaMessageContextMemory::rememberMemoryUsage
  */
  public function testRememberMemoryUsage() {
    $context = new PapayaMessageContextMemory();
    $context->rememberMemoryUsage(42);
    $this->assertAttributeEquals(
      42,
      '_previousUsage',
      PapayaMessageContextMemory::class
    );
  }

  /**
  * @covers PapayaMessageContextMemory::setMemoryUsage
  */
  public function testSetMemoryUsage() {
    $context = new PapayaMessageContextMemory();
    $context->rememberMemoryUsage(2);
    $context->setMemoryUsage(23, 42);
    $this->assertAttributeEquals(
      23,
      '_currentUsage',
      $context
    );
    $this->assertAttributeEquals(
      42,
      '_peakUsage',
      $context
    );
    $this->assertAttributeEquals(
      21,
      '_diffUsage',
      $context
    );
  }

  /**
  * @covers PapayaMessageContextMemory::asString
  */
  public function testAsStringWithIncreasingUsage() {
    $context = new PapayaMessageContextMemory();
    $context->rememberMemoryUsage(23);
    $context->setMemoryUsage(3117, 4221);
    $this->assertEquals(
      'Memory Usage: 3,117 bytes (+3,094 bytes) | Peak Usage: 4,221 Bytes',
      $context->asString()
    );
  }

  /**
  * @covers PapayaMessageContextMemory::asString
  */
  public function testAsStringWithDecreasingUsage() {
    $context = new PapayaMessageContextMemory();
    $context->rememberMemoryUsage(3117);
    $context->setMemoryUsage(23, 4221);
    $this->assertEquals(
      'Memory Usage: 23 bytes (-3,094 bytes) | Peak Usage: 4,221 Bytes',
      $context->asString()
    );
  }
}
