<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaAdministrationLanguagesCaptionTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationLanguagesCaption
  */
  public function testConstructor() {
    $caption = new PapayaAdministrationLanguagesCaption();
    $this->assertEquals('', (string)$caption);
  }

  /**
  * @covers PapayaAdministrationLanguagesCaption
  */
  public function testConstructorWithString() {
    $caption = new PapayaAdministrationLanguagesCaption('Suffix string');
    $this->assertAttributeEquals('Suffix string', '_suffix', $caption);
  }

  /**
  * @covers PapayaAdministrationLanguagesCaption
  */
  public function testConstructorWithStringAndSeparator() {
    $caption = new PapayaAdministrationLanguagesCaption('Suffix string', '|');
    $this->assertAttributeEquals('|', '_separator', $caption);
  }

  /**
  * @covers PapayaAdministrationLanguagesCaption
  * @dataProvider provideSampleWithoutLanguageSwitch
  */
  public function testToStringWithoutAdministrationLanguage($expected, $suffix, $separator) {
    $caption = new PapayaAdministrationLanguagesCaption($suffix, $separator);
    $this->assertEquals($expected, (string)$caption);
  }

  /**
  * @covers PapayaAdministrationLanguagesCaption
  * @dataProvider provideSampleWithLanguageSwitch
  */
  public function testToStringWithAdministrationLanguage($expected, $language, $suffix, $separator) {
    $switch = $this->getMock('PapayaAdministrationLanguagesSwitch');
    $switch
      ->expects($this->once())
      ->method('getCurrent')
      ->will($this->returnValue($language));

    $caption = new PapayaAdministrationLanguagesCaption($suffix, $separator);
    $caption->papaya(
      $this->mockPapaya()->application(array('administrationLanguage' => $switch))
    );
    $this->assertEquals($expected, (string)$caption);
  }

  public static function provideSampleWithoutLanguageSwitch() {
    return array(
      array('', '', ' - '),
      array('Foo', 'Foo', ' - '),
      array('Foo', 'Foo', ' > '),
      array('Foo', new PapayaUiString('Foo'), ' '),
    );
  }

  public static function provideSampleWithLanguageSwitch() {
    return array(
      array('', NULL, '', ' - '),
      array('Foo', NULL, 'Foo', ' - '),
      array('Foo', NULL, 'Foo', ' > '),
      array('Foo', NULL, new PapayaUiString('Foo'), ''),
      array('English', array('title' => 'English'), '', ' - '),
      array('English - Foo', array('title' => 'English'), 'Foo', ' - '),
      array('English > Foo', array('title' => 'English'), 'Foo', ' > '),
      array('English Foo', array('title' => 'English'), new PapayaUiString('Foo'), ' '),
    );
  }
}