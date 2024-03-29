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

namespace Papaya\HTTP\Client {

  require_once __DIR__.'/../../../../bootstrap.php';

  class FileTest extends \Papaya\TestFramework\TestCase {

    public function testGetName() {
      $file = new File_TestProxy();
      $file->_name = 'sample.ext';
      $this->assertEquals('sample.ext', $file->getName());
    }

    public function testGetNameIfEmpty() {
      $file = new File_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      $file->getName();
    }

    public function testGetHeaders() {
      $expected = 'Content-Disposition: form-data; name=""; filename=""'."\r\n";
      $expected .= 'Content-Transfer-Encoding: binary'."\r\n";
      $expected .= 'Content-Type: application/octet-stream'."\r\n";
      $expected .= 'Content-Length: 0'."\r\n";
      $file = new File_TestProxy();
      $this->assertEquals($expected, $file->getHeaders());
    }

    public function testGetHeadersWithIndividualMimeType() {
      $expected = 'Content-Disposition: form-data; name=""; filename=""'."\r\n";
      $expected .= 'Content-Transfer-Encoding: binary'."\r\n";
      $expected .= 'Content-Type: text/plain'."\r\n";
      $expected .= 'Content-Length: 0'."\r\n";
      $file = new File_TestProxy();
      $file->_mimeType = 'text/plain';
      $this->assertEquals($expected, $file->getHeaders());
    }

    public function testEscapeHeaderValue() {
      $file = new File_TestProxy();
      $this->assertEquals('foo \'\\"\\\\', $file->_escapeHeaderValue('foo \'"\\'));
    }
  }

  class File_TestProxy extends File {

    public
      /** @noinspection PropertyInitializationFlawsInspection */
      $_name = '';
    public
      /** @noinspection PropertyInitializationFlawsInspection */
      $_mimeType = '';

    public $size = 0;

    public function send(Socket $socket, $chunked = FALSE, $bufferSize = 0) {
      parent::send($socket, $chunked, $bufferSize);
    }

    public function getSize() {
      return $this->size;
    }

    public function _escapeHeaderValue($value) {
      return parent::_escapeHeaderValue($value);
    }
  }
}
