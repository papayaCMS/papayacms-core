<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaParserTagImageTest extends PHPUnit_Framework_TestCase {
  /**
   * @covers PapayaParserTagImage::appendTo
   */
  public function testAppendTo() {
    $image = new PapayaParserTagImage();
    $image->parseString('d74f6d0324f5d90b23bb3771200ddf7d,60,96,max');
    $document = new PapayaXmlDocument();
    $container = $document->appendElement('container');
    $image->appendTo($container);
    $this->assertEquals(
      '<container><papaya:media xmlns:papaya="http://www.papaya-cms.com/namespace/papaya"'
      .' src="d74f6d0324f5d90b23bb3771200ddf7d"'
      .' width="60" height="96" resize="max"></papaya:media></container>
',
      $document->saveHTML()
    );
  }

  /**
   * @covers PapayaParserTagImage::parseString
   */
  public function testParseString() {
    $image = new PapayaParserTagImage();
    $image->parseString('d74f6d0324f5d90b23bb3771200ddf7d,60,96,max', 0, 0, 'alt text', '', 'Subtitle');
    $this->assertAttributeEquals('d74f6d0324f5d90b23bb3771200ddf7d', '_source', $image);
    $this->assertAttributeEquals(60, '_width', $image);
    $this->assertAttributeEquals(96, '_height', $image);
    $this->assertAttributeEquals('max', '_resize', $image);
    $this->assertAttributeEquals('alt text', '_alt', $image);
    $this->assertAttributeEquals('Subtitle', '_subTitle', $image);
  }

  /**
   * @covers PapayaParserTagImage::parseString
   */
  public function testParseStringWithAdditionalData() {
    $image = new PapayaParserTagImage();
    $image->parseString('d74f6d0324f5d90b23bb3771200ddf7d', 60, 96, 'alt text', 'max', 'Subtitle');
    $this->assertAttributeEquals('d74f6d0324f5d90b23bb3771200ddf7d', '_source', $image);
    $this->assertAttributeEquals(60, '_width', $image);
    $this->assertAttributeEquals(96, '_height', $image);
    $this->assertAttributeEquals('max', '_resize', $image);
    $this->assertAttributeEquals('alt text', '_alt', $image);
    $this->assertAttributeEquals('Subtitle', '_subTitle', $image);
  }
}
