<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaAdministrationLanguagesImageTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationLanguagesImage
  */
  public function testConstructor() {
    $image = new PapayaAdministrationLanguagesImage();
    $this->assertAttributeEquals(0, '_languageId', $image);
  }

  /**
  * @covers PapayaAdministrationLanguagesImage
  */
  public function testConstructorWithLanguageId() {
    $image = new PapayaAdministrationLanguagesImage(21);
    $this->assertAttributeEquals(21, '_languageId', $image);
  }

  /**
  * @covers PapayaAdministrationLanguagesImage
  */
  public function testToStringWithoutLanguageInformationExpectingEmptyString() {
    $image = new PapayaAdministrationLanguagesImage();
    $this->assertEquals('', (string)$image);
  }

  /**
  * @covers PapayaAdministrationLanguagesImage
  */
  public function testToStringFetchingCurrentLanguage() {
    $switch = $this->createMock(PapayaAdministrationLanguagesSwitch::class);
    $switch
      ->expects($this->once())
      ->method('getCurrent')
      ->will($this->returnValue(array('image' => 'sample.png')));
    $image = new PapayaAdministrationLanguagesImage();
    $image->papaya(
      $this->mockPapaya()->application(array('administrationLanguage' => $switch))
    );
    $this->assertEquals('./pics/language/sample.png', (string)$image);
  }

  /**
  * @covers PapayaAdministrationLanguagesImage
  */
  public function testToStringFetchingDefinedLanguage() {
    $languages = $this->createMock(PapayaContentLanguages::class);
    $languages
      ->expects($this->once())
      ->method('getLanguage')
      ->with(42)
      ->will($this->returnValue(array('image' => 'sample.png')));

    $switch = $this->createMock(PapayaAdministrationLanguagesSwitch::class);
    $switch
      ->expects($this->once())
      ->method('languages')
      ->will($this->returnValue($languages));
    $image = new PapayaAdministrationLanguagesImage(42);
    $image->papaya(
      $this->mockPapaya()->application(array('administrationLanguage' => $switch))
    );
    $this->assertEquals('./pics/language/sample.png', (string)$image);
  }

  /**
  * @covers PapayaAdministrationLanguagesImage
  */
  public function testToStringWithNonExistingLanguageExpectingEmptyString() {
    $languages = $this->createMock(PapayaContentLanguages::class);
    $languages
      ->expects($this->once())
      ->method('getLanguage')
      ->with(23)
      ->will($this->returnValue(NULL));

    $switch = $this->createMock(PapayaAdministrationLanguagesSwitch::class);
    $switch
      ->expects($this->once())
      ->method('languages')
      ->will($this->returnValue($languages));
    $image = new PapayaAdministrationLanguagesImage(23);
    $image->papaya(
      $this->mockPapaya()->application(array('administrationLanguage' => $switch))
    );
    $this->assertEquals('', (string)$image);
  }
}
