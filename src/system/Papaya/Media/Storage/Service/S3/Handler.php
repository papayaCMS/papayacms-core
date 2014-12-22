<?php
/**
* HTTP Handler for Amazon S3
*
* @copyright 2002-2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Media-Storage
* @version $Id: Handler.php 39725 2014-04-07 17:19:34Z weinert $
*/

/**
* Amazon S3 based storage service for Papaya Media Storage
*
* @package Papaya-Library
* @subpackage Media-Storage
*/
class PapayaMediaStorageServiceS3Handler {

  /**
  * http client object
  * @var object PapayaHttpClient
  */
  private $_client = NULL;

  /**
  * Amazon S3 access key id
  * @var string
  */
  private $_storageAccessKeyId = '';

  /**
  * Amazon S3 private access key parts (already padded)
  * @var array
  */
  private $_storageAccessKey = array();

  /**
  * Constructor - set configuration if provided
  * @param PapayaConfiguration $configuration
  */
  public function __construct($configuration = NULL) {
    if (isset($configuration) && is_object($configuration)) {
      $this->setConfiguration($configuration);
    }
  }

  /**
  * Set the used HTTP client object.
  *
  * @param object PapayaHttpClient $client
  * @return void
  */
  public function setHTTPClient(PapayaHttpClient $client) {
    $this->_client = $client;
  }

  /**
  * Set the storage configuration values.
  *
  * @param PapayaConfiguration $configuration
  */
  public function setConfiguration($configuration) {
    $this->_storageAccessKeyId = $configuration->get(
      'PAPAYA_MEDIA_STORAGE_S3_KEYID', $this->_storageAccessKeyId
    );
    $this->_setStorageKey(
      $configuration->get(
        'PAPAYA_MEDIA_STORAGE_S3_KEY', ''
      )
    );
  }

  /**
  * Initialize HTTP client, create instance if not already exists, reset current instance
  *
  * @return void
  */
  public function initHTTPClient() {
    if (!isset($this->_client)) {
      $this->_client = new PapayaHttpClient();
    }
    $this->_client->reset();
  }

  /**
  * Prepare request (set up HTTP client object for action)
  *
  * @param string $url
  * @param string $method
  * @param array $parameters
  * @param array $headers
  * @return PapayaHttpClient
  */
  public function setUpRequest(
    $url, $method = 'GET', $parameters = array(), $headers = array()
  ) {
    $this->initHTTPClient();
    $this->_client->setMethod($method);
    $this->_client->setUrl($url);
    if (!empty($parameters)) {
      $this->_client->addRequestData($parameters);
    }
    if (!empty($headers)) {
      foreach ($headers as $name => $value) {
        $this->_client->setHeader($name, $value);
      }
    }
    $this->_client->setHeader(
      'Date',
      gmdate(DATE_RFC1123)
    );
    $this->_client->setHeader(
      'Authorization',
      'AWS '.$this->_storageAccessKeyId.':'.
        $this->_getSignature($this->getSignatureData($url))
    );
    return $this->_client;
  }

  /**
   * Collect and aggregate signature data
   *
   * @param string $url
   * @return string
   */
  public function getSignatureData($url) {
    // method
    $signatureData = $this->_client->getMethod()."\n";
    // content headers
    $signatureData .= "\n";
    $signatureData .= $this->_client->getHeader('Content-Type')."\n";
    // date
    $signatureData .= $this->_client->getHeader('Date')."\n";
    // amz headers
    $amzHeaders = array(
      'x-amz-acl',
      'x-amz-copy-source',
      'x-amz-metadata-directive'
    );
    foreach ($amzHeaders as $amzHeader) {
      $headerValue = $this->_client->getHeader($amzHeader);
      if (!empty($headerValue)) {
        $signatureData .= strtolower(trim($amzHeader)).':'.trim($headerValue)."\n";
      }
    }
    // path is the request URI from first / up to the query string
    $urlPattern = '(^
      [^:/]+://
      (?P<bucket>[^/]+)
      \\.s3\\.amazonaws[^/?]+
      (?:/(?P<path>(?:[^?]*)?))?
      (?P<queryString>(?:\\?.*)?)$
    )x';
    $result = preg_match($urlPattern, $url, $matches);
    if (1 !== $result) {
      var_dump($url, $result, $matches);
      trigger_error(
        'Can not parse URL to Amazon S3.',
        E_USER_WARNING
      );
      return '';
    }
    $signatureData .= '/'.$matches['bucket'];
    if (!empty($matches['path'])) {
      $signatureData .= '/'.$matches['path'];
    }
    if ($matches['queryString'] === '?acl') {
      $signatureData .= $matches['queryString'];
    }
    return $signatureData;
  }


  /**
  * The storage key setter creates and sets the two parts of the storage key
  * needed to create the signature.
  *
  * @param string $key
  * @return void
  */
  private function _setStorageKey($key) {
    if (strlen($key) < 64) {
      $key = str_pad($key, 64, chr(0));
    }
    $this->_storageAccessKey = array(
      'inner' => (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64)),
      'outer' => (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64))
    );
  }

  /**
  * Create signature for data string.
  *
  * @param string $data
  * @return string
  */
  private function _getSignature($data) {
    return base64_encode(
      pack(
        'H*',
        sha1(
          $this->_storageAccessKey['outer'].pack(
            'H40', sha1($this->_storageAccessKey['inner'].$data)
          )
        )
      )
    );
  }
}
