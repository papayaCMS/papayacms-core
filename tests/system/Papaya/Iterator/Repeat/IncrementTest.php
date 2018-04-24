<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaIteratorRepeatIncrementTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorRepeatIncrement::__construct
  * @covers PapayaIteratorRepeatIncrement::increment
  * @dataProvider provideLimits
  */
  public function testIteration($expected, $minimum, $maximum, $step) {
    $iterator = new PapayaIteratorRepeatIncrement($minimum, $maximum, $step);
    $this->assertEquals(
      $expected,
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorRepeatIncrement::__construct
  * @covers PapayaIteratorRepeatIncrement::increment
  */
  public function testIterationWithAssocMode() {
    $iterator = new PapayaIteratorRepeatIncrement(
      0, 100, 10, PapayaIteratorRepeatIncrement::MODE_ASSOC
    );
    $this->assertEquals(
      array(
        0 => 0,
        10 => 10,
        20 => 20,
        30 => 30,
        40 => 40,
        50 => 50,
        60 => 60,
        70 => 70,
        80 => 80,
        90 => 90,
        100 => 100
      ),
      iterator_to_array($iterator)
    );
  }

  public static function provideLimits() {
    return array(
      'single entry' => array(
        array(42), 42, 42, 1
      ),
      'two entries, 1 to 2' => array(
        array(1, 2), 1, 2, 1
      ),
      'two entries, 1 to 3, step 2' => array(
        array(1, 3), 1, 3, 2
      ),
      'three entries, 0 to 5' => array(
        array(0, 1, 2, 3, 4, 5), 0, 5, 1
      )
    );
  }
}
