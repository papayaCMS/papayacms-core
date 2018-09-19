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
namespace Papaya\Media\Storage\Service\S3;

/**
 * Amazon S3 based storage service for Papaya Media Storage
 *
 * @package Papaya-Library
 * @subpackage Media-Storage
 */
class Handler {
  /**
   * http client object
   *
   * @var \Papaya\HTTP\Client
   */
  private $_client;

  /**
   * Amazon S3 access key id
   *
   * @var string
   */
  private $_storageAccessKeyId = '';

  /**
   * Amazon S3 private access key parts (already padded)
   *
   * @var array
   */
  private $_storageAccessKey = [];

  /**
   * Constructor - set configuration if provided
   *
   * @param \Papaya\Configuration $configuration
   */
  public function __construct($configuration = NULL) {
    if (isset($configuration) && \is_object($configuration)) {
      $this->setConfiguration($configuration);
    }
  }

  /**
   * Set the used HTTP client object.
   *
   * @param \Papaya\HTTP\Client $client
   */
  public function setHTTPClient(\Papaya\HTTP\Client $client) {
    $this->_client = $client;
  }

  /**
   * Set the storage configuration values.
   *
   * @param \Papaya\Configuration $configuration
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
   */
  public function initHTTPClient() {
    if (!isset($this->_client)) {
      $this->_client = new \Papaya\HTTP\Client();
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
   *
   * @return \Papaya\HTTP\Client
   */
  public function setUpRequest(
    $url, $method = 'GET', $parameters = [], $headers = []
  ) {
    $this->initHTTPClient();
    $this->_client->setMethod($method);
    $this->_client->setURL($url);
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
      \gmdate(DATE_RFC1123)
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
   *
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
    $amzHeaders = [
      'x-amz-acl',
      'x-amz-copy-source',
      'x-amz-metadata-directive'
    ];
    foreach ($amzHeaders as $amzHeader) {
      $headerValue = $this->_client->getHeader($amzHeader);
      if (!empty($headerValue)) {
        $signatureData .= \strtolower(\trim($amzHeader)).':'.\trim($headerValue)."\n";
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
    $result = \preg_match($urlPattern, $url, $matches);
    if (1 !== $result) {
      \trigger_error(
        'Can not parse URL to Amazon S3.',
        E_USER_WARNING
      );
      return '';
    }
    $signatureData .= '/'.$matches['bucket'];
    if (!empty($matches['path'])) {
      $signatureData .= '/'.$matches['path'];
    }
    if ('?acl' === $matches['queryString']) {
      $signatureData .= $matches['queryString'];
    }
    return $signatureData;
  }

  /**
   * The storage key setter creates and sets the two parts of the storage key
   * needed to create the signature.
   *
   * @param string $key
   */
  private function _setStorageKey($key) {
    if (\strlen($key) < 64) {
      $key = \str_pad($key, 64, \chr(0));
    }
    $this->_storageAccessKey = [
      'inner' => (\substr($key, 0, 64) ^ \str_repeat(\chr(0x36), 64)),
      'outer' => (\substr($key, 0, 64) ^ \str_repeat(\chr(0x5C), 64))
    ];
  }

  /**
   * Create signature for data string.
   *
   * @param string $data
   *
   * @return string
   */
  private function _getSignature($data) {
    return \base64_encode(
      \pack(
        'H*',
        \sha1(
          $this->_storageAccessKey['outer'].\pack(
            'H40', \sha1($this->_storageAccessKey['inner'].$data)
          )
        )
      )
    );
  }
}
