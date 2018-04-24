<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiStringTranslatedTest extends PapayaTestCase {

  /**
  * @covers PapayaUiStringTranslated::__toString
  * @covers PapayaUiStringTranslated::translate
  */
  public function testMagicMethodToString() {
    $phrases = $this
      ->getMockBuilder('PapayaPhrases')
      ->disableOriginalConstructor()
      ->getMock();
    $phrases
      ->expects($this->once())
      ->method('getText')
      ->with($this->equalTo('Hello %s!'))
      ->will($this->returnValue('Hi %s!'));
    $string = new PapayaUiStringTranslated('Hello %s!', array('World'));
    $string->papaya(
      $this->mockPapaya()->application(array('Phrases' => $phrases))
    );
    $this->assertEquals(
      'Hi World!', (string)$string
    );
  }

  /**
  * @covers PapayaUiStringTranslated::__toString
  * @covers PapayaUiStringTranslated::translate
  */
  public function testMagicMethodToStringWithoutTranslationEngine() {
    $string = new PapayaUiStringTranslated('Hello %s!', array('World'));
    $string->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertEquals(
      'Hello World!', (string)$string
    );
  }

}
