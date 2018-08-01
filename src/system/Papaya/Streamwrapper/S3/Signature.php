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
/**
 * Papaya Streamwrapper Signature for Amazon S3
 *
 * @package Papaya-Library
 * @subpackage Streamwrapper
 */
class Signature {

  /**
   * resource data
   *
   * @var array
   */
  private $_resource;
  /**
   * http method
   *
   * @var string
   */
  private $_method;
  /**
   * http headers
   *
   * @var array
   */
  private $_headers;

  /**
   * Constructor initialize signature object
   *
   * @param array $resource
   * @param string $method
   * @param array $headers
   * @return \PapayaStreamwrapperS3Signature
   */
  public function __construct($resource, $method, $headers) {
    $this->_resource = $resource;
    $this->_method = $method;
    $this->_headers = $headers;
  }

  /**
   * Collect and aggregate signature data
   *
   * @return string
   */
  private function _getSignatureData() {
    // method
    $signatureData = $this->_method."\n";
    // content headers
    $signatureData .= "\n";
    $signatureData .= $this->_headers['Content-Type']."\n";
    // date
    $signatureData .= $this->_headers['Date']."\n";
    // amz headers
    if (!empty($this->_headers['x-amz-acl'])) {
      $signatureData .= 'x-amz-acl:'.strtolower(trim($this->_headers['x-amz-acl']))."\n";
    }
    $signatureData .= '/'.$this->_resource['bucket'].'/'.$this->_resource['object'];
    return $signatureData;
  }

  /**
   * Get signature.
   *
   * @return string
   */
  private function _getSignature() {
    $key = str_pad($this->_resource['secret'], 64, chr(0));
    $accessKey = array(
      'inner' => (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64)),
      'outer' => (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64))
    );
    return base64_encode(
      pack(
        'H*',
        sha1(
          $accessKey['outer'].pack(
            'H40', sha1($accessKey['inner'].$this->_getSignatureData())
          )
        )
      )
    );
  }

  /**
   * Convert object to signature string
   *
   * @return string
   */
  public function __toString() {
    return $this->_getSignature();
  }
}
