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

namespace Papaya\Streamwrapper\S3;
require_once __DIR__.'/../../../../bootstrap.php';

class SignatureTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Streamwrapper\S3\Signature::__construct
   */
  public function testConstructor() {
    $signature = new Signature(array(), 'GET', array('Date' => '42'));
    $this->assertAttributeEquals(array(), '_resource', $signature);
    $this->assertAttributeEquals('GET', '_method', $signature);
    $this->assertAttributeEquals(array('Date' => '42'), '_headers', $signature);
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Signature
   * @dataProvider magicToStringDataProvider
   * @param array $resource
   * @param string $method
   * @param array $headers
   * @param string $expected
   */
  public function testMagicToString(array $resource, $method, array $headers, $expected) {
    $signature = new Signature($resource, $method, $headers);
    $this->assertEquals($expected, (string)$signature);
  }

  /*********************************
   * Data Provider
   *********************************/

  public static function magicToStringDataProvider() {
    return array(
      array(
        array(
          'bucket' => 'sample',
          'id' => 'KEYID123456789012345',
          'secret' => '1234567890123456789012345678901234567890',
          'object' => 'path/to/object'
        ),
        'HEAD',
        array(
          'Content-Type' => 'text/plain',
          'Date' => 'Mon, 02 Nov 2009 13:06:00 +0000'
        ),
        'j+PlR4RcJZxqExNXsttehWHyBT8='
      ),
      array(
        array(
          'bucket' => 'sample',
          'id' => 'KEYID123456789012345',
          'secret' => '1234567890123456789012345678901234567890',
          'object' => 'path/to/object'
        ),
        'PUT',
        array(
          'Content-Type' => 'image/png',
          'Date' => 'Mon, 02 Nov 2009 13:06:00 +0000',
          'x-amz-acl' => 'private'
        ),
        'r1tz7jeG57VwqEKWRpNwCeXFZUQ='
      )
    );
  }
}

