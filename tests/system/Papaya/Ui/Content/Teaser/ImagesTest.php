<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiContentTeaserImagesTest extends PapayaTestCase {

  /**
  * @covers PapayaUiContentTeaserImages::__construct
  */
  public function testConstructorWithAllParameters() {
    $xml =
      '<subtopics>'.
        '<subtopic no="42">'.
          '<image>'.
            '<img src="test.png"/>'.
          '</image>'.
        '</subtopic>'.
      '</subtopics>';
    $dom = new PapayaXmlDocument();
    $dom->loadXml($xml);
    $images = new PapayaUiContentTeaserImages($dom->documentElement, 21, 42, 'min');
    $this->assertAttributeSame(
      $dom->documentElement, '_teasers', $images
    );
    $this->assertAttributeEquals(
      21, '_width', $images
    );
    $this->assertAttributeEquals(
      42, '_height', $images
    );
    $this->assertAttributeEquals(
      'min', '_resizeMode', $images
    );
  }

  /**
  * @covers PapayaUiContentTeaserImages::appendTo
  */
  public function testAppendToWithTeasers() {
    $xml =
      '<teasers>'.
        '<teaser page-id="42">'.
          '<image>'.
            '<img src="test.png"/>'.
          '</image>'.
        '</teaser>'.
      '</teasers>';
    $dom = new PapayaXmlDocument();
    $dom->loadXml($xml);

    $images = new PapayaUiContentTeaserImages($dom->documentElement, 100, 100);
    $this->assertEquals(
      '<teaser-thumbnails>'.
        '<thumbnail page-id="42">'.
          '<papaya:media xmlns:papaya="http://www.papaya-cms.com/ns/papayacms"'.
          ' src="test.png" resize="max" width="100" height="100"/>'.
        '</thumbnail>'.
      '</teaser-thumbnails>',
      $images->getXml()
    );
  }

  /**
  * @covers PapayaUiContentTeaserImages::appendTo
  */
  public function testAppendToWithSubtopics() {
    $xml =
      '<subtopics>'.
        '<subtopic no="42">'.
          '<image>'.
            '<img src="test.png"/>'.
          '</image>'.
        '</subtopic>'.
      '</subtopics>';
    $dom = new PapayaXmlDocument();
    $dom->loadXml($xml);

    $images = new PapayaUiContentTeaserImages($dom->documentElement, 100, 100);
    $this->assertEquals(
      '<subtopicthumbs>'.
        '<thumb topic="42">'.
          '<papaya:media xmlns:papaya="http://www.papaya-cms.com/ns/papayacms"'.
          ' src="test.png" resize="max" width="100" height="100"/>'.
        '</thumb>'.
      '</subtopicthumbs>',
      $images->getXml()
    );
  }

  /**
  * @covers PapayaUiContentTeaserImages::appendTo
  */
  public function testAppendToWithoutImageData() {
    $xml =
      '<teasers>'.
        '<teaser page-id="42"/>'.
      '</teasers>';
    $dom = new PapayaXmlDocument();
    $dom->loadXml($xml);

    $images = new PapayaUiContentTeaserImages($dom->documentElement, 100, 100);
    $this->assertEquals(
      '', $images->getXml()
    );
  }
}
