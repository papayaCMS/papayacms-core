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

use Papaya\HTTP;

/**
 * Papaya Streamwrapper Amazon S3
 *
 * @package Papaya-Library
 * @subpackage Streamwrapper
 */
class Handler {
  /**
   * HTTP client object
   *
   * @var HTTP\Client
   */
  private $_client;

  /**
   * Temporary file that will on close be written to S3
   *
   * @var resource
   */
  private $_temporaryFile;

  /**
   * Set HTTP client object
   *
   * @param HTTP\Client $client
   */
  public function setHTTPClient(HTTP\Client $client) {
    $this->_client = $client;
  }

  /**
   * Get the HTTP client object, reset it if it already exists
   *
   * @return HTTP\Client
   */
  public function getHTTPClient() {
    if (!($this->_client instanceof HTTP\Client)) {
      $this->_client = new HTTP\Client();
    }
    $this->_client->reset();
    return $this->_client;
  }

  /**
   * Send request to Amazon S3, handle errors and return client if valid response
   *
   * @param string $method
   * @param string $url
   * @param array $headers
   * @param int $options
   * @param array $arguments for the http request
   *
   * @return null|HTTP\Client
   */
  private function _sendRequest($method, $url, $headers, $options, array $arguments = []) {
    $client = $this->getHTTPClient();
    $client->setMethod($method);
    $client->setURL($url);
    foreach ($headers as $key => $value) {
      $client->setHeader($key, $value);
    }
    foreach ($arguments as $key => $value) {
      $client->addRequestData($key, $value);
    }
    $client->send();
    $status = $client->getResponseStatus();
    if (\in_array($status, [200, 204, 206], TRUE)) {
      return $client;
    }
    $client->close();
    if ($options & STREAM_REPORT_ERRORS) {
      switch ($status) {
        case 404 :
        break;
        case 403 :
          throw new S3Exception(
            'Invalid Amazon S3 permissions'
          );
        break;
        default :
          throw new S3Exception(
            'Unexpected response status: '.$status
          );
        break;
      }
    }
    return NULL;
  }

  /**
   * Get information about a file resource
   *
   * @param array $location
   * @param int $options
   *
   * @return array|null
   */
  public function getFileInformations($location, $options) {
    $headers = [
      'Date' => \gmdate(DATE_RFC1123),
      'Content-Type' => 'text/plain',
      'Connection' => 'keep-alive'
    ];
    $signature = new Signature($location, 'HEAD', $headers);
    $headers['Authorization'] = 'AWS '.$location['id'].':'.$signature;
    $client = $this->_sendRequest(
      'HEAD',
      'http://'.$location['bucket'].'.s3.amazonaws.com/'.$location['object'],
      $headers,
      $options
    );
    if ($client) {
      $contentType = $client->getResponseHeader('Content-Type');
      $client->close();
      if ('application/x-directory' !== $contentType) {
        return [
          'size' => $client->getResponseHeader('Content-Length'),
          'modified' => \strtotime($client->getResponseHeader('Last-Modified')),
          'mode' => 0100006
        ];
      }
    }
    return NULL;
  }

  /**
   * Get content from file resource
   *
   * @param array $location
   * @param int $position
   * @param int $count
   * @param int $options
   *
   * @return array|null
   */
  public function readFileContent($location, $position, $count, $options) {
    $headers = [
      'Date' => \gmdate(DATE_RFC1123),
      'Content-Type' => 'text/plain',
      'Connection' => 'keep-alive',
      'Range' => 'bytes='.$position.'-'.($position + $count - 1)
    ];
    $signature = new Signature($location, 'GET', $headers);
    $headers['Authorization'] = 'AWS '.$location['id'].':'.$signature;
    $client = $this->_sendRequest(
      'GET',
      'http://'.$location['bucket'].'.s3.amazonaws.com/'.$location['object'],
      $headers,
      $options
    );
    if ($client) {
      $rangeHeader = $client->getResponseHeader('Content-Range');
      $pattern = '(^bytes (\d+)-(\d+)/(\d+)$)';
      $return = \preg_match($pattern, $rangeHeader, $range);
      if (1 !== $return) {
        $client->close();
        if ($options & STREAM_REPORT_ERRORS) {
          throw new S3Exception(
            'Missing Content-Range header in response from amazon S3.'
          );
        }
        return NULL;
      }
      $size = $range[3];
      $stat = [
        'size' => (int)$size,
        'modified' => \strtotime($client->getResponseHeader('Last-Modified')),
        'mode' => 0100006
      ];
      return [$client->getResponseData(), $stat];
    }
    if ($options & STREAM_REPORT_ERRORS) {
      throw new S3Exception(
        'Can not find amazon resource.'
      );
    }
    return NULL;
  }

  /**
   * Open file for writing
   *
   * @param array $location
   * @param int $options
   * @param string $mimeType
   *
   * @internal param string $data
   *
   * @return bool success
   */
  public function openWriteFile($location, $options, $mimeType = 'application/octet-stream') {
    $headers = [
      'Date' => \gmdate(DATE_RFC1123),
      'Content-Type' => $mimeType,
      'Connection' => 'close',
    ];
    $method = 'PUT';
    $signature = new Signature(
      $location,
      $method,
      $headers
    );
    $headers['Authorization'] = 'AWS '.$location['id'].':'.$signature;
    $client = $this->getHTTPClient();
    $client->setMethod($method);
    $url = 'http://'.$location['bucket'].'.s3.amazonaws.com/'.
      $location['object'];
    $client->setURL($url);
    foreach ($headers as $key => $value) {
      $client->setHeader($key, $value);
    }
    $this->_temporaryFile = \tmpfile();
    $result = \is_resource($this->_temporaryFile);
    if (TRUE !== $result && $options & STREAM_REPORT_ERRORS) {
      throw new S3Exception(
        'Failed to create temporary file.'
      );
    }
    return $result;
  }

  /**
   * Write $data to file
   *
   * @param int $options
   * @param string $data
   *
   * @return int amount of bytes written
   */
  public function writeFileContent(
    /** @noinspection PhpUnusedParameterInspection */
    $options, $data
  ) {
    return \fwrite($this->_temporaryFile, $data);
  }

  /**
   * Close file for writing
   *
   * @param int $options
   */
  public function closeWriteFile($options) {
    $client = $this->_client;
    \fseek($this->_temporaryFile, 0);
    $client->addRequestFile(
      new HTTP\Client\File\Stream('file', 'file', $this->_temporaryFile)
    );
    $client->send();
    $status = $client->getResponseStatus();
    $client->close();
    if (
      200 !== $status
      && ($options & STREAM_REPORT_ERRORS)
    ) {
      if (403 === $status) {
        throw new S3Exception(
          'Invalid Amazon S3 permissions'
        );
      } else {
        throw new S3Exception(
          'Unexpected response status: '.$status
        );
      }
    }
    \fclose($this->_temporaryFile);
  }

  /**
   * Remove a file.
   *
   * @param array $location
   * @param int $options
   *
   * @return bool success
   */
  public function removeFile($location, $options) {
    $headers = [
      'Date' => \gmdate(DATE_RFC1123),
      'Content-Type' => 'text/plain',
      'Connection' => 'keep-alive',
    ];
    $signature = new Signature($location, 'DELETE', $headers);
    $headers['Authorization'] = 'AWS '.$location['id'].':'.$signature;
    $client = $this->_sendRequest(
      'DELETE',
      'http://'.$location['bucket'].'.s3.amazonaws.com/'.$location['object'],
      $headers,
      $options
    );
    if ($client) {
      $client->close();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get informations about a directory resource
   *
   * array $result['contents'] of strings with contained file names.
   *   The array may be empty.
   * boolean $result['moreContent'] boolean
   * string $result['startMarker'] contains $startMarker .
   * integer $result['size'] , $result['modified'] and $result['mode']
   *   are hard coded.
   *
   * @param array $location
   * @param int $options
   * @param int $maxKeys Limit the number of results, default 1
   * @param string $startMarker Start output lexicographically after this, default ''
   *
   * @return array|null associative array $result
   */
  public function getDirectoryInformations($location, $options, $maxKeys = 1, $startMarker = '') {
    $headers = [
      'Date' => \gmdate(DATE_RFC1123),
      'Content-Type' => 'text/plain',
      'Connection' => 'keep-alive'
    ];
    if ('/' === \substr($location['object'], -1)) {
      $path = $location['object'];
    } else {
      $path = $location['object'].'/';
    }
    $arguments = [
      'prefix' => $path,
      'marker' => $path.$startMarker,
      'max-keys' => (int)$maxKeys,
      'delimiter' => '/'
    ];
    $location['object'] = '';
    $signature = new Signature($location, 'GET', $headers);
    $headers['Authorization'] = 'AWS '.$location['id'].':'.$signature;
    $client = $this->_sendRequest(
      'GET',
      'http://'.$location['bucket'].'.s3.amazonaws.com/',
      $headers,
      $options,
      $arguments
    );
    if ($client) {
      $response = $client->getResponseData();
      $moreContent = $this->evaluateResult(
        new \DOMDocument('1.0', 'UTF-8'),
        $response,
        '//s3:IsTruncated/text() = "true"'
      );
      $items = $this->evaluateResult(
        new \DOMDocument('1.0', 'UTF-8'),
        $response,
        '//s3:Contents/s3:Key | //s3:CommonPrefixes/s3:Prefix'
      );
      if ($items->length > 0) {
        $contents = [];
        $prefixLength = \strlen($path);
        foreach ($items as $item) {
          if ('/' === \substr($item->nodeValue, -1)) {
            $value = \substr($item->nodeValue, $prefixLength, -1);
          } else {
            $value = \substr($item->nodeValue, $prefixLength);
          }
          if ('$' !== $value) {
            $contents[] = $value;
          }
        }
        $contents = \array_unique($contents);
        return [
          'size' => 0,
          'modified' => 0,
          'mode' => 040006,
          'contents' => $contents,
          'moreContent' => $moreContent,
          'startMarker' => $startMarker,
        ];
      }
    }
    return NULL;
  }

  /**
   * Evaluate xml result using a xpath expression
   *
   * @param \DOMDocument $dom
   * @param string $xml
   * @param string $xpath
   *
   * @return mixed
   */
  public function evaluateResult($dom, $xml, $xpath) {
    $dom->loadXML($xml);
    $query = new \DOMXPath($dom);
    $query->registerNamespace('s3', 'http://s3.amazonaws.com/doc/2006-03-01/');
    return $query->evaluate($xpath);
  }
}
