<?php
require_once(__DIR__.'/../../../../../bootstrap.php');
PapayaTestCase::defineConstantDefaults('DB_FETCHMODE_ASSOC');

class PapayaMediaImageSvgInfoTest extends PapayaTestCase {

  public function testReadUsingXMLReader() {
    if (!extension_loaded('xmlreader')) {
      $this->markTestSkipped('XMLReader not available');
    }
    $info = new PapayaMediaImageSvgInfo(__DIR__.'/TestData/minimum.svg');
    $this->assertTrue($info->isSvg());
    $this->assertEquals(139, $info->getWidth());
    $this->assertEquals(144, $info->getHeight());
  }

  public function testReadInvalidUsingXMLReader() {
    if (!extension_loaded('xmlreader')) {
      $this->markTestSkipped('XMLReader not available');
    }
    $info = new PapayaMediaImageSvgInfo('data://text/plain,');
    $this->assertFalse($info->isSvg());
  }

  public function testReadUsingDOM() {
    $info = new PapayaMediaImageSvgInfo(__DIR__.'/TestData/minimum.svg');
    $info->forceDOM = TRUE;
    $this->assertTrue($info->isSvg());
    $this->assertEquals(139, $info->getWidth());
    $this->assertEquals(144, $info->getHeight());
  }

  public function testReadInvalidUsingDOM() {
    if (!extension_loaded('xmlreader')) {
      $this->markTestSkipped('XMLReader not available');
    }
    $info = new PapayaMediaImageSvgInfo('data://text/plain,');
    $this->assertFalse($info->isSvg());
  }

}