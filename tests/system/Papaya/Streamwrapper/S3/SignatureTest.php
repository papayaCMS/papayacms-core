<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaStreamwrapperS3SignatureTest extends PapayaTestCase {

  /**
  * @covers PapayaStreamwrapperS3Signature::__construct
  */
  public function testConstructor() {
    $signature = new PapayaStreamwrapperS3Signature(array(), 'GET', array('Date' => '42'));
    $this->assertAttributeEquals(array(), '_resource', $signature);
    $this->assertAttributeEquals('GET', '_method', $signature);
    $this->assertAttributeEquals(array('Date' => '42'), '_headers', $signature);
  }

  /**
  * @covers PapayaStreamwrapperS3Signature
  * @dataProvider magicToStringDataProvider
  */
  public function testMagicToString($resource, $method, $headers, $expected) {
    $signature = new PapayaStreamwrapperS3Signature($resource, $method, $headers);
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

