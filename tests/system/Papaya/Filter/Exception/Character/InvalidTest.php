<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaFilterExceptionCharacterInvalidTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionCharacterInvalid::__construct
  * @dataProvider provideExceptionDataAndMessage
  */
  public function testConstructor($expected, $value, $offset) {
    $e = new PapayaFilterExceptionCharacterInvalid($value, $offset);
    $this->assertAttributeEquals(
      $offset, '_characterPosition', $e
    );
    $this->assertEquals(
      $expected, $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionCharacterInvalid::getCharacterPosition
  */
  public function testGetCharacterPosition() {
    $e = new PapayaFilterExceptionCharacterInvalid('', 42);
    $this->assertEquals(
      42, $e->getCharacterPosition()
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideExceptionDataAndMessage() {
    return array(
      'short' => array('Invalid character in value "Invalid" at offset #5.', 'Invalid', 5),
      'large' => array(
        'Invalid character at offset #34 near "------------------------------Inva".',
        str_repeat('-', 30).'Invalid'.str_repeat('-', 30), 34
      ),
      'offset > 50' => array(
        'Invalid character at offset #54 near'.
          ' "----------------------------------------------Inva".',
        str_repeat('-', 50).'Invalid'.str_repeat('-', 30), 54
      )
    );
  }
}
