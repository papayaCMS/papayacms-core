<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageContextRuntimeTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageContextRuntime::__construct
  */
  public function testConstructorWithoutParameters() {
    PapayaMessageContextRuntime::setStartTime(0);
    $context = new PapayaMessageContextRuntime();
    $this->assertAttributeGreaterThan(
      0,
      '_startTime',
      'PapayaMessageContextRuntime'
    );
    $this->assertAttributeGreaterThan(
      0,
      '_previousTime',
      'PapayaMessageContextRuntime'
    );
    $this->assertAttributeEquals(
      PapayaMessageContextRuntime::MODE_GLOBAL,
      '_mode',
      $context
    );
  }

  /**
  * @covers PapayaMessageContextRuntime::__construct
  */
  public function testConstructorWithParameters() {
    $context = new PapayaMessageContextRuntime(23, 42);
    $this->assertAttributeEquals(
      19,
      '_neededTime',
      $context
    );
    $this->assertAttributeEquals(
      42,
      '_currentTime',
      $context
    );
    $this->assertAttributeEquals(
      PapayaMessageContextRuntime::MODE_SINGLE,
      '_mode',
      $context
    );
  }

  /**
  * @covers PapayaMessageContextRuntime::setTimeValues
  * @covers PapayaMessageContextRuntime::_prepareTimeValue
  * @dataProvider setTimeValuesDataProvider
  *
  * @param float $expected
  * @param float|integer|string $start
  * @param float|integer|string $stop
  */
  public function testSetTimeValues($expectedDiff, $expectedStop, $start, $stop) {
    $context = new PapayaMessageContextRuntime();
    $context->setTimeValues($start, $stop);
    $this->assertAttributeEquals(
      $expectedDiff,
      '_neededTime',
      $context,
      '',
      0.000001
    );
    $this->assertAttributeEquals(
      $expectedStop,
      '_currentTime',
      $context,
      '',
      0.000001
    );
  }

  /**
  * @covers PapayaMessageContextRuntime::setStartTime
  */
  public function testSetStartTime() {
    PapayaMessageContextRuntime::setStartTime(42);
    $this->assertAttributeEquals(
      42,
      '_startTime',
      'PapayaMessageContextRuntime'
    );
    $this->assertAttributeEquals(
      42,
      '_previousTime',
      'PapayaMessageContextRuntime'
    );
  }

  /**
  * @covers PapayaMessageContextRuntime::rememberTime
  */
  public function testRememberTime() {
    PapayaMessageContextRuntime::rememberTime(42);
    $this->assertAttributeEquals(
      42,
      '_previousTime',
      'PapayaMessageContextRuntime'
    );
  }

  /**
  * @covers PapayaMessageContextRuntime::asString
  */
  public function testAsStringInGlobalMode() {
    $context = new PapayaMessageContextRuntime();
    PapayaMessageContextRuntime::setStartTime(23);
    $context->setTimeValues(42, 77);
    $this->assertEquals(
      'Time: 54s 0ms (+35s 0ms)',
      $context->asString()
    );
  }

  /**
  * @covers PapayaMessageContextRuntime::asString
  */
  public function testAsStringInSingleMode() {
    $context = new PapayaMessageContextRuntime(42, 77);
    $this->assertEquals(
      'Time needed: 35s 0ms',
      $context->asString()
    );
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function setTimeValuesDataProvider() {
    return array(
      'integers' => array(19, 42, 23, 42),
      'strings' => array(19.5, 42.7, '0.2 23', '0.7 42'),
      'floats' => array(19.5, 42.7, 23.2, 42.7)
    );
  }
}
