<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiToolbarButtonTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarButton::setAccessKey
  */
  public function testSetAccessKey() {
    $button = new PapayaUiToolbarButton();
    $button->accessKey = '1';
    $this->assertEquals(
      '1', $button->accessKey
    );
  }

  /**
  * @covers PapayaUiToolbarButton::setAccessKey
  */
  public function testSetAccessKeyWithInvalidKeyExpectingException() {
    $button = new PapayaUiToolbarButton();
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: Access key must be an single character.'
    );
    $button->accessKey = 'foo';
  }

  /**
  * @covers PapayaUiToolbarButton::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument;
    $dom->appendElement('sample');
    $button = new PapayaUiToolbarButton();
    $button->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $button->caption = 'Test';
    $button->image = 'image';
    $button->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<button href="http://www.test.tld/test.html" target="_self"'.
          ' glyph="sample.png" title="Test"/>'.
        '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiToolbarButton::appendTo
  */
  public function testAppendToWithAllProperties() {
    $dom = new PapayaXmlDocument;
    $dom->appendElement('sample');
    $button = new PapayaUiToolbarButton();
    $button->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $button->image = 'image';
    $button->caption = 'Test Caption';
    $button->hint = 'Test Hint';
    $button->selected = TRUE;
    $button->accessKey = 'T';
    $button->target = '_top';
    $button->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<button href="http://www.test.tld/test.html" target="_top" glyph="sample.png"'.
          ' title="Test Caption" accesskey="T" hint="Test Hint" down="down"/>'.
        '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiToolbarButton::appendTo
  */
  public function testAppendToWithoutProperties() {
    $dom = new PapayaXmlDocument;
    $dom->appendElement('sample');
    $button = new PapayaUiToolbarButton();
    $button->papaya(
      $this->mockPapaya()->application(array('Images' => array('' => '')))
    );
    $button->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample/>',
      $dom->saveXml($dom->documentElement)
    );
  }
}
