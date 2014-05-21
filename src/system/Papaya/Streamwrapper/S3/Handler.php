<?php
/**
* Papaya Streamwrapper Amazon S3 Handler
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Streamwrapper
* @version $Id: Handler.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Papaya Streamwrapper Amazon S3 Handler
*
* @package Papaya-Library
* @subpackage Streamwrapper
*/
class PapayaStreamwrapperS3Handler {

  /**
  * HTTP client object
  * @var PapayaHttpClient
  */
  private $_client = NULL;

  /**
  * Temporary file that will on close be written to S3
  * @var resource
  */
  private $_temporaryFile = NULL;

  /**
  * Set HTTP client object
  * @param PapayaHttpClient $client
  * @return void
  */
  public function setHTTPClient(PapayaHttpClient $client) {
    $this->_client = $client;
  }

  /**
  * Get the HTTP client object, reset it if it already exists
  * @return PapayaHttpClient
  */
  public function getHTTPClient() {
    if (!($this->_client instanceof PapayaHttpClient)) {
      $this->_client = new PapayaHttpClient();
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
  * @param integer $options
  * @param array $arguments for the http request
  * @return NULL|PapayaHttpClient
  */
  private function _sendRequest($method, $url, $headers, $options, $arguments = array()) {
    $client = $this->getHTTPClient();
    $client->setMethod($method);
    $client->setUrl($url);
    foreach ($headers as $key => $value) {
      $client->setHeader($key, $value);
    }
    foreach ($arguments as $key => $value) {
      $client->addRequestData($key, $value);
    }
    $client->send();
    $status = $client->getResponseStatus();
    if (in_array($status, array(200, 204, 206))) {
      return $client;
    } else {
      $client->close();
      if ($options & STREAM_REPORT_ERRORS) {
        switch ($status) {
        case 404 :
          break;
        case 403 :
          trigger_error(
            'Invalid Amazon S3 permissions',
            E_USER_WARNING
          );
          break;
        default :
          trigger_error(
            'Unexpected response status: '.$status,
            E_USER_WARNING
          );
          break;
        }
      }
      return NULL;
    }
  }

  /**
  * Get informations about a file resource
  *
  * @param array $location
  * @param integer $options
  * @return array|NULL
  */
  public function getFileInformations($location, $options) {
    $headers = array(
      'Date' => gmdate(DATE_RFC1123),
      'Content-Type' => 'text/plain',
      'Connection' => 'keep-alive'
    );
    $signature = new PapayaStreamwrapperS3Signature($location, 'HEAD', $headers);
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
        return array(
          'size' => $client->getResponseHeader('Content-Length'),
          'modified' => strtotime($client->getResponseHeader('Last-Modified')),
          'mode' => 0100006
        );
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
   * @param integer $options
   * @return array|NULL
   */
  public function readFileContent($location, $position, $count, $options) {
    $headers = array(
      'Date' => gmdate(DATE_RFC1123),
      'Content-Type' => 'text/plain',
      'Connection' => 'keep-alive',
      'Range' => 'bytes='.$position.'-'.($position + $count - 1)
    );
    $signature = new PapayaStreamwrapperS3Signature($location, 'GET', $headers);
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
      $return = preg_match($pattern, $rangeHeader, $range);
      if (1 !== $return) {
        $client->close();
        if ($options & STREAM_REPORT_ERRORS) {
          trigger_error(
            'Missing Content-Range header in response from amazon S3.',
            E_USER_WARNING
          );
        }
        return NULL;
      }
      $size = $range[3];
      $stat = array(
        'size' => (int)$size,
        'modified' => strtotime($client->getResponseHeader('Last-Modified')),
        'mode' => 0100006
      );
      return array($client->getResponseData(), $stat);
    } elseif ($options & STREAM_REPORT_ERRORS) {
      trigger_error(
        'Can not find amazon resource.',
        E_USER_WARNING
      );
    }
    return NULL;
  }

  /**
   * Open file for writing
   *
   * @param array $location
   * @param integer $options
   * @param string $mimeType
   * @internal param string $data
   * @return boolean success
   */
  public function openWriteFile($location, $options, $mimeType = 'application/octet-stream') {
    $headers = array(
      'Date' => gmdate(DATE_RFC1123),
      'Content-Type' => $mimeType,
      'Connection' => 'close',
    );
    $method = 'PUT';
    $signature = new PapayaStreamwrapperS3Signature(
      $location,
      $method,
      $headers
    );
    $headers['Authorization'] = 'AWS '.$location['id'].':'.$signature;
    $client = $this->getHTTPClient();
    $client->setMethod($method);
    $url = 'http://'.$location['bucket'].'.s3.amazonaws.com/'.
      $location['object'];
    $client->setUrl($url);
    foreach ($headers as $key => $value) {
      $client->setHeader($key, $value);
    }
    $this->_temporaryFile = tmpfile();
    $result = is_resource($this->_temporaryFile);
    if (TRUE !== $result && $options & STREAM_REPORT_ERRORS) {
      // @codeCoverageIgnoreStart
      trigger_error(
        'Failed to create temporary file.',
        E_USER_WARNING
      );
    }
    // @codeCoverageIgnoreEnd
    return $result;
  }

  /**
  * Write $data to file
  *
  * @param integer $options
  * @param string $data
  * @return integer amount of bytes written
  */
  public function writeFileContent($options, $data) {
    return fwrite($this->_temporaryFile, $data);
  }

  /**
  * Close file for writing
  *
  * @param integer $options
  * @return void
  */
  public function closeWriteFile($options) {
    $client = $this->_client;
    fseek($this->_temporaryFile, 0);
    $client->addRequestFile(
      new PapayaHttpClientFileResource("file", "file", $this->_temporaryFile)
    );
    $client->send();
    $status = $client->getResponseStatus();
    $client->close();
    if (!in_array($status, array(200))
        && ($options & STREAM_REPORT_ERRORS)) {
      switch ($status) {
      case 403 :
        trigger_error(
          'Invalid Amazon S3 permissions',
          E_USER_WARNING
        );
        break;
      default :
        trigger_error(
          'Unexpected response status: '.$status,
          E_USER_WARNING
        );
        break;
      }
    }
    fclose($this->_temporaryFile);
  }

  /**
   * Remove a file.
   *
   * @param array $location
   * @param int $options
   * @return boolean success
   */
  public function removeFile($location, $options) {
    $headers = array(
      'Date' => gmdate(DATE_RFC1123),
      'Content-Type' => 'text/plain',
      'Connection' => 'keep-alive',
    );
    $signature = new PapayaStreamwrapperS3Signature($location, 'DELETE', $headers);
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
  * @param integer $options
  * @param integer $maxKeys Limit the number of results, default 1
  * @param string $startMarker Start output lexicographically after this, default ''
  * @return array|NULL associative array $result
  */
  public function getDirectoryInformations($location, $options, $maxKeys = 1, $startMarker = '') {
    $headers = array(
      'Date' => gmdate(DATE_RFC1123),
      'Content-Type' => 'text/plain',
      'Connection' => 'keep-alive'
    );
    if (substr($location['object'], -1) == '/') {
      $path = $location['object'];
    } else {
      $path = $location['object'].'/';
    }
    $arguments = array(
      'prefix' => $path,
      'marker' => $path.$startMarker,
      'max-keys' => (int)$maxKeys,
      'delimiter' => '/'
    );
    $location['object'] = '';
    $signature = new PapayaStreamwrapperS3Signature($location, 'GET', $headers);
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
        new DOMDocument('1.0', 'UTF-8'),
        $response,
        '//s3:IsTruncated/text() = "true"'
      );
      $items = $this->evaluateResult(
        new DOMDocument('1.0', 'UTF-8'),
        $response,
        '//s3:Contents/s3:Key | //s3:CommonPrefixes/s3:Prefix'
      );
      if ($items->length > 0) {
        $contents = array();
        $prefixLength = strlen($path);
        foreach ($items as $item) {
          if (substr($item->nodeValue, -1) == '/') {
            $value = substr($item->nodeValue, $prefixLength, -1);
          } else {
            $value = substr($item->nodeValue, $prefixLength);
          }
          if ($value !== '$') {
            $contents[] = $value;
          }
        }
        $contents = array_unique($contents);
        return array(
          'size' => 0,
          'modified' => 0,
          'mode' => 040006,
          'contents' => $contents,
          'moreContent' => $moreContent,
          'startMarker' => $startMarker,
        );
      }
    }
    return NULL;
  }

  /**
  * Evaluate xml result using a xpath expression
  *
  * @param DOMDocument $dom
  * @param string $xml
  * @param string $xpath
  * @return mixed
  */
  public function evaluateResult($dom, $xml, $xpath) {
    $dom->loadXML($xml);
    $query = new DOMXPath($dom);
    $query->registerNamespace('s3', 'http://s3.amazonaws.com/doc/2006-03-01/');
    return $query->evaluate($xpath);
  }

}
