<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiStringDateTest extends PapayaTestCase {

  /**
  * @covers PapayaUiStringDate::__construct
  */
  public function testConstructor() {
    $string = new PapayaUiStringDate(strtotime('2011-08-25 16:00:00'));
    $this->assertAttributeEquals(
      strtotime('2011-08-25 16:00:00'), '_timestamp', $string
    );
  }

  /**
  * @covers PapayaUiStringDate::__toString
  */
  public function testMagicMethodToString() {
    $string = new PapayaUiStringDate(strtotime('2011-08-25 16:00:00'));
    $this->assertEquals(
      '2011-08-25 16:00', (string)$string
    );
  }

  /**
  * @covers PapayaUiStringDate::__toString
  */
  public function testMagicMethodToStringWithTime() {
    $string = new PapayaUiStringDate(
      strtotime('2011-08-25 16:00:00'),
      PapayaUiStringDate::SHOW_TIME
    );
    $this->assertEquals(
      '2011-08-25 16:00', (string)$string
    );
  }

  /**
  * @covers PapayaUiStringDate::__toString
  */
  public function testMagicMethodToStringWithTimeAndSeconds() {
    $string = new PapayaUiStringDate(
      strtotime('2011-08-25 16:00:00'),
      PapayaUiStringDate::SHOW_TIME | PapayaUiStringDate::SHOW_SECONDS
    );
    $this->assertEquals(
      '2011-08-25 16:00:00', (string)$string
    );
  }

  /**
  * @covers PapayaUiStringDate::__toString
  */
  public function testMagicMethodToStringWithSecondsExpectingDateOnly() {
    $string = new PapayaUiStringDate(
      strtotime('2011-08-25 16:00:00'),
      PapayaUiStringDate::SHOW_SECONDS
    );
    $this->assertEquals(
      '2011-08-25', (string)$string
    );
  }

}
