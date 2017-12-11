<?php
require_once(__DIR__.'/../../../../../bootstrap.php');
PapayaTestCase::defineConstantDefaults('DB_FETCHMODE_ASSOC');

class PapayaMediaFileInfoSvgTest extends PapayaTestCase {

  public function testReadUsingXMLReader() {
    if (!extension_loaded('xmlreader')) {
      $this->markTestSkipped('XMLReader not available');
    }
    $info = new PapayaMediaFileInfoSvg(__DIR__.'/TestData/minimum.svg');
    $this->assertTrue($info['is_valid']);
    $this->assertEquals(139, $info['width']);
    $this->assertEquals(144, $info['height']);;
  }

  public function testReadInvalidUsingXMLReader() {
    if (!extension_loaded('xmlreader')) {
      $this->markTestSkipped('XMLReader not available');
    }
    $info = new PapayaMediaFileInfoSvg('data://text/plain,');
    $this->assertFalse($info['is_valid']);
  }

  public function testReadUsingDOM() {
    $info = new PapayaMediaFileInfoSvg(__DIR__.'/TestData/minimum.svg');
    $info->forceDOM = TRUE;
    $this->assertTrue($info['is_valid']);
    $this->assertEquals(139, $info['width']);
    $this->assertEquals(144, $info['height']);
  }

  public function testReadInvalidUsingDOM() {
    if (!extension_loaded('xmlreader')) {
      $this->markTestSkipped('XMLReader not available');
    }
    $info = new PapayaMediaFileInfoSvg('data://text/plain,');
    $this->assertFalse($info['is_valid']);
  }

}