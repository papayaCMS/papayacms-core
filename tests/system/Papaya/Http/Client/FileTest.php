<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaHttpClientFileTest extends \PapayaTestCase {

  public function testGetName() {
      $file = new \PapayaHttpClientFile_TestProxy();
      $file->_name = 'sample.ext';
      $this->assertEquals('sample.ext', $file->getName());
  }

  public function testGetNameIfEmpty() {
    $file = new \PapayaHttpClientFile_TestProxy();
    $this->expectException(UnexpectedValueException::class);
    $file->getName();
  }

  public function testGetHeaders() {
    $expected = 'Content-Disposition: form-data; name=""; filename=""'."\r\n";
    $expected .= 'Content-Transfer-Encoding: binary'."\r\n";
    $expected .= 'Content-Type: application/octet-stream'."\r\n";
    $expected .= 'Content-Length: 0'."\r\n";
    $file = new \PapayaHttpClientFile_TestProxy();
    $this->assertEquals($expected, $file->getHeaders());
  }

  public function testGetHeadersWithIndividualMimeType() {
    $expected = 'Content-Disposition: form-data; name=""; filename=""'."\r\n";
    $expected .= 'Content-Transfer-Encoding: binary'."\r\n";
    $expected .= 'Content-Type: text/plain'."\r\n";
    $expected .= 'Content-Length: 0'."\r\n";
    $file = new \PapayaHttpClientFile_TestProxy();
    $file->_mimeType = 'text/plain';
    $this->assertEquals($expected, $file->getHeaders());
  }

  public function testEscapeHeaderValue() {
    $file = new \PapayaHttpClientFile_TestProxy();
    $this->assertEquals('foo \'\\"\\\\', $file->_escapeHeaderValue('foo \'"\\'));
  }
}

class PapayaHttpClientFile_TestProxy extends \Papaya\Http\Client\File {

  public
    /** @noinspection PropertyInitializationFlawsInspection */
    $_name = '',
    /** @noinspection PropertyInitializationFlawsInspection */
    $_mimeType = '';

  public function send(\Papaya\Http\Client\Socket $socket, $chunked = FALSE, $bufferSize = 0) {
    parent::send($socket, $chunked, $bufferSize);
  }

  public function _escapeHeaderValue($value) {
    return parent::_escapeHeaderValue($value);
  }
}
