<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaHttpClientFileTest extends PapayaTestCase {

  public function testGetName() {
      $file = new PapayaHttpClientFile_TestProxy();
      $file->_name = 'sample.ext';
      $this->assertEquals('sample.ext', $file->getName());
  }

  public function testGetNameIfEmpty() {
    $file = new PapayaHttpClientFile_TestProxy();
    $this->setExpectedException('UnexpectedValueException');
    $file->getName();
  }

  public function testGetHeaders() {
    $expected = 'Content-Disposition: form-data; name=""; filename=""'."\r\n";
    $expected .= 'Content-Transfer-Encoding: binary'."\r\n";
    $expected .= 'Content-Type: application/octet-stream'."\r\n";
    $expected .= 'Content-Length: 0'."\r\n";
    $file = new PapayaHttpClientFile_TestProxy();
    $this->assertEquals($expected, $file->getHeaders());
  }

  public function testGetHeadersWithIndividualMimetype() {
    $expected = 'Content-Disposition: form-data; name=""; filename=""'."\r\n";
    $expected .= 'Content-Transfer-Encoding: binary'."\r\n";
    $expected .= 'Content-Type: text/plain'."\r\n";
    $expected .= 'Content-Length: 0'."\r\n";
    $file = new PapayaHttpClientFile_TestProxy();
    $file->_mimeType = 'text/plain';
    $this->assertEquals($expected, $file->getHeaders());
  }

  public function testEscapeHeaderValue() {
    $file = new PapayaHttpClientFile_TestProxy();
    $this->assertEquals('foo \'\\"\\\\', $file->_escapeHeaderValue('foo \'"\\'));
  }
}

class PapayaHttpClientFile_TestProxy extends PapayaHttpClientFile {

  public $_name = '';
  public $_mimeType = '';

  public function send(PapayaHttpClientSocket $socket, $chunked = FALSE, $bufferSize = 0) {
    parent::send($socket, $chunked, $bufferSize);
  }

  public function _escapeHeaderValue($value) {
    return parent::_escapeHeaderValue($value);
  }
}
