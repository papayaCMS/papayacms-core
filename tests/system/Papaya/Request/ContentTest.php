<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaRequestContentTest extends PapayaTestCase {

  /**
   * @covers PapayaRequestContent
   */
  public function testReadStream() {
    $stream = fopen('data://text/plain,'.urlencode('TEST'), 'r');
    $content = new PapayaRequestContent($stream);
    $this->assertEquals('TEST', (string)$content);
  }

  /**
   * @covers PapayaRequestContent
   */
  public function testReadLengthStream() {
    $content = new PapayaRequestContent(NULL, 42);
    $this->assertEquals(42, $content->length());
  }

  /**
   * @covers PapayaRequestContent
   * @backupGlobals enabled
   */
  public function testReadLengthFromEnvironment() {
    $_SERVER['HTTP_CONTENT_LENGTH'] = 42;
    $content = new PapayaRequestContent();
    $this->assertEquals(42, $content->length());
  }

}
