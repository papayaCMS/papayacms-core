<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiStringTranslatedTest extends PapayaTestCase {

  /**
  * @covers PapayaUiStringTranslated::__toString
  * @covers PapayaUiStringTranslated::translate
  */
  public function testMagicMethodToString() {
    /* PapayaPhraseManager will be the new implementation of the phrase translations,
       just mock it for now, so we dont have to handle the constant declarations in the
       current class */
    $phrases = $this->getMock('PapayaPhraseManager', array('getText'));
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