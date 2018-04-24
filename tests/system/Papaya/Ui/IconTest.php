<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiIconTest extends PapayaTestCase {

  /**
  * @covers PapayaUiIcon::__construct
  */
  public function testContructor() {
    $icon = new PapayaUiIcon('sample');
    $this->assertAttributeEquals(
      'sample', '_image', $icon
    );
  }

  /**
  * @covers PapayaUiIcon::__construct
  */
  public function testContructorWithAllArguments() {
    $icon = new PapayaUiIcon('sample', 'caption', 'hint', array('foo' => 'bar'));
    $this->assertAttributeEquals(
      'caption', '_caption', $icon
    );
    $this->assertAttributeEquals(
      'hint', '_hint', $icon
    );
    $this->assertAttributeEquals(
      array('foo' => 'bar'), '_actionParameters', $icon
    );
  }

  /**
  * @covers PapayaUiIcon::__toString
  */
  public function testMagicMethodToString() {
    $icon = new PapayaUiIcon('sample');
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $this->assertEquals(
      'sample.png', (string)$icon
    );
  }

  /**
  * @covers PapayaUiIcon::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $icon = new PapayaUiIcon('sample');
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $icon->appendTo($dom->appendElement('sample'));
    $this->assertEquals(
      '<sample><glyph src="sample.png"/></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiIcon::appendTo
  */
  public function testAppendToWithHiddenIcon() {
    $dom = new PapayaXmlDocument();
    $icon = new PapayaUiIcon('sample');
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $icon->visible = FALSE;
    $icon->appendTo($dom->appendElement('sample'));
    $this->assertEquals(
      '<sample><glyph src="-"/></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiIcon::appendTo
  */
  public function testAppendToWithLink() {
    $dom = new PapayaXmlDocument();
    $icon = new PapayaUiIcon('sample', 'caption', 'hint', array('foo' => 'bar'));
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $icon->appendTo($dom->appendElement('sample'));
    $this->assertEquals(
      '<sample>'.
        '<glyph src="sample.png" caption="caption" hint="hint"'.
        ' href="http://www.test.tld/test.html?foo=bar"/>'.
      '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiIcon::getImageUrl
  */
  public function testGetImageUrl() {
    $icon = new PapayaUiIcon('sample');
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $this->assertEquals(
      'sample.png', $icon->getImageUrl()
    );
  }

  /**
  * @covers PapayaUiIcon::getUrl
  */
  public function testGetUrl() {
    $icon = new PapayaUiIcon('sample');
    $icon->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertNull($icon->getUrl());
  }

  /**
  * @covers PapayaUiIcon::getUrl
  */
  public function testGetUrlWithActionParameters() {
    $icon = new PapayaUiIcon('sample', 'caption', 'hint', array('foo' => 'bar'));
    $icon->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertEquals(
      'http://www.test.tld/test.html?foo=bar', $icon->getUrl()
    );
  }

  /**
  * @covers PapayaUiIcon::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->getMock('PapayaUiReference');
    $icon = new PapayaUiIcon('sample');
    $this->assertSame(
      $reference, $icon->reference($reference)
    );
  }

  /**
  * @covers PapayaUiIcon::reference
  */
  public function testReferenceGetImplicitCreate() {
    $icon = new PapayaUiIcon('sample');
    $icon->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertInstanceOf(
      'PapayaUiReference', $icon->reference()
    );
  }
}
