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

namespace Papaya\HTTP;
/**
 * Simple HTTP client object - makes it a little easier to make HTTP requests
 *
 * Supports, GET, POST, PUT and other request methods,
 * header manipulation and file uploads (using streams)
 *
 * @package Papaya-Library
 * @subpackage HTTP-Client
 */
class Client {

  /**
   * internal socket object
   *
   * @var \Papaya\HTTP\Client\Socket
   */
  private $_socket = NULL;

  /**
   * request method
   *
   * @var string
   */
  private $_method = 'GET';

  /**
   * remote url
   *
   * @var string
   */
  private $_url = '';

  /**
   * Transport protocol
   *
   * @var string
   */
  private $_transport = '';

  /**
   * proxy server data
   *
   * @var array
   */
  private $_proxy = NULL;

  /**
   * proxy server authorization
   *
   * @var array
   */
  private $_proxyAuthorization = NULL;

  /**
   * timeout in seconds for request while connecting
   *
   * @var integer
   */
  private $_timeout = 10;

  /**
   * timeout in seconds for request while reading data
   *
   * @var integer
   */
  private $_timeoutRead = 20;

  /**
   * linebreak chars
   *
   * @var string
   */
  private $_lineBreak = "\r\n";

  /**
   * http request headers
   *
   * @var \Papaya\HTTP\Headers
   */
  private $_requestHeaders = NULL;
  /**
   * http request headers
   *
   * @var string
   */
  private $_defaultRequestHeaders = array(
    'Accept' => '*/*',
    'Accept-Charset' => 'utf-8,*',
    'Connection' => 'keep-alive'
  );

  /**
   * request data array
   *
   * @var array
   */
  private $_requestData = array();

  /**
   * request files array
   *
   * @var \Papaya\HTTP\Client\File[]
   */
  private $_requestFiles = array();

  /**
   * http response headers
   *
   * @var \Papaya\HTTP\Headers
   */
  protected $_responseHeaders = NULL;

  /**
   * http response status code
   *
   * @var integer
   */
  private $_responseStatus = 0;

  /**
   * maximum internal redirects
   *
   * @var integer
   */
  private $_redirectLimit = 10;

  /**
   * internal redirect counter
   *
   * @var integer
   */
  private $_redirects = 0;

  /**
   * constructor
   *
   * @param string $url
   */
  public function __construct($url = '') {
    $this->reset();
    if (!empty($url)) {
      $this->setUrl($url);
    }
  }

  /**
   * set the url to request
   *
   * @param string $url
   * @throws \InvalidArgumentException
   */
  public function setUrl($url) {
    if (!empty($url)) {
      $urlObject = new \Papaya\Url();
      if (isset($this->_url['scheme'])) {
        $urlObject->scheme = $this->_url['scheme'];
      }
      if (isset($this->_url['host'])) {
        $urlObject->host = $this->_url['host'];
      }
      if (isset($this->_url['port'])) {
        $urlObject->port = $this->_url['port'];
      }
      if (isset($this->_url['path'])) {
        $urlObject->path = $this->_url['path'];
      }
      $transformer = new \Papaya\Url\Transformer\Absolute;
      $newUrl = $transformer->transform($urlObject, $url);
      $url = $newUrl;
      $this->_url = parse_url($url);
    } else {
      throw new \InvalidArgumentException('Invalid url');
    }
  }

  /**
   * reset request/response data
   *
   * @return void
   */
  public function reset() {
    $this->_requestHeaders = NULL;
    $this->_requestData = array();
    $this->_requestFiles = array();
    $this->_responseHeaders = NULL;
    $this->_responseStatus = 0;
    $this->_redirects = 0;
  }

  public function getRequestHeaders() {
    if (is_null($this->_requestHeaders)) {
      $this->_requestHeaders = new \Papaya\HTTP\Headers($this->_defaultRequestHeaders);
    }
    return $this->_requestHeaders;
  }

  public function getResponseHeaders($reset = FALSE) {
    if ($reset || is_null($this->_responseHeaders)) {
      $this->_responseHeaders = new \Papaya\HTTP\Headers();
    }
    return $this->_responseHeaders;
  }

  /**
   * Dependency injection of a socket object
   *
   * @param $socket
   * @access public
   * @return void
   */
  public function setSocket(\Papaya\HTTP\Client\Socket $socket) {
    $this->_socket = $socket;
  }

  /**
   * return socket object
   *
   * @access public
   * @return object \PapayaHTTPsocket
   */
  public function getSocket() {
    if (is_null($this->_socket)) {
      $this->_socket = new \Papaya\HTTP\Client\Socket();
    }
    return $this->_socket;
  }

  /**
   * Set the transport protocol
   *
   * @param string $transport
   * @return boolean TRUE if empty or available in stream_get_transports(), FALSE otherwise
   */
  public function setTransport($transport) {
    $result = FALSE;
    if ($transport == '' || in_array($transport, stream_get_transports())) {
      $this->_transport = $transport;
      $result = TRUE;
    }
    return $result;
  }

  /**
   * Get the transport protocol
   *
   * @return string
   */
  public function getTransport() {
    return $this->_transport;
  }

  /**
   * set proxy server data
   *
   * @param string $server
   * @param integer $port optional, default value NULL
   * @param string $user optional, default value NULL
   * @param string $password optional, default value NULL
   * @access public
   * @return void
   * @throws \InvalidArgumentException
   */
  public function setProxy($server, $port = NULL, $user = NULL, $password = NULL) {
    $this->_proxy = NULL;
    $this->_proxyAuthorization = NULL;
    if (!empty($server)) {
      $this->_proxy = array(
        'host' => $server
      );
      if (isset($port) && $port > 0) {
        $this->_proxy['port'] = (int)$port;
      } else {
        $this->_proxy['port'] = 80;
      }
      if (!empty($user)) {
        $this->_proxyAuthorization = array(
          'user' => $user
        );
        if (!empty($password)) {
          $this->_proxyAuthorization['password'] = $password;
        }
      }
    } else {
      throw new \InvalidArgumentException('Invalid proxy server');
    }
  }

  /**
   * Set limit for internal redirects
   *
   * @param $redirectLimit
   */
  public function setRedirectLimit($redirectLimit) {
    $this->_redirectLimit = (int)$redirectLimit;
  }

  /**
   * Get limit for internal redirects
   */
  public function getRedirectLimit() {
    return $this->_redirectLimit;
  }

  /**
   * send the request to the remote server
   *
   * @access public
   * @return boolean
   */
  public function send() {
    if ($socket = $this->open()) {
      switch ($this->_method) {
        case 'GET' :
        case 'HEAD' :
        case 'COPY' :
        case 'DELETE' :
          $socket->write($this->_lineBreak);
        break;
        case 'POST' :
          $requestHeaders = $this->getRequestHeaders();
          if (isset($this->_requestFiles) &&
            is_array($this->_requestFiles) &&
            count($this->_requestFiles) > 0) {
            $this->_sendMultipartFormData(
              isset($requestHeaders['Transfer-Encoding']) &&
              $requestHeaders['Transfer-Encoding'] == 'chunked'
            );
            break;
          }
          $contentType = isset($requestHeaders['Content-Type'])
            ? $requestHeaders['Content-Type'] : 'application/x-www-form-urlencoded';
          switch ($contentType) {
            case 'text/xml':
            case 'text/xml; charset=utf-8':
            case 'application/soap+xml':
              $this->_sendRawPostData();
            break;
            case 'application/x-www-form-urlencoded':
            default:
              $this->_sendUrlencodedFormData();
          }
        break;
        case 'PUT' :
          if (isset($this->_requestFiles) &&
            is_array($this->_requestFiles) &&
            count($this->_requestFiles) > 0) {
            $file = reset($this->_requestFiles);
            $socket->write(
              'Content-Length: '.$file->getSize().$this->_lineBreak.$this->_lineBreak
            );
            $file->send($socket, FALSE);
          } else {
            $socket->write('Content-Length: 0'.$this->_lineBreak.$this->_lineBreak);
          }
        break;
      }
      $this->readResponseHeaders();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Open the connection and return the socket object
   *
   * @return \Papaya\HTTP\Client\Socket
   */
  public function open() {
    $socket = $this->getSocket();
    if (isset($this->_proxy)) {
      $server = $this->_proxy['host'];
      $port = $this->_proxy['port'];
    } else {
      $server = empty($this->_url['host'])
        ? 'localhost' : $this->_url['host'];
      $defaultPort = $this->_url['scheme'] == 'https' ? 443 : 80;
      $port = empty($this->_url['port']) || $this->_url['port'] <= 0
        ? $defaultPort : (int)$this->_url['port'];
      if ($this->_url['scheme'] == 'https' && empty($this->_transport)) {
        $this->setTransport('tls');
      }
    }
    $opened = $socket->open(
      $server,
      $port,
      $this->_timeout,
      $this->_url['scheme'],
      $this->_transport
    );
    if ($opened) {
      if (strtolower($this->getHeader('Connection')) === 'close') {
        $socket->setKeepAlive(FALSE);
      }
      $socket->write($this->getRequestHeaderString());
      return $socket;
    }
    return FALSE;
  }

  /**
   * send a multipart/form-data formatted request body
   *
   * @param boolean $chunked optional, default value FALSE
   * @return void
   */
  private function _sendMultipartFormData($chunked = FALSE) {
    $boundary = '-------------'.md5(rand(0, time()));
    $this->_socket->write(
      'Content-Type: multipart/form-data; boundary="'.$boundary.'"'.$this->_lineBreak
    );
    $requestBody = '';
    foreach ($this->_requestData as $name => $value) {
      $requestBody .= '--'.$boundary.$this->_lineBreak;
      $requestBody .= 'Content-Disposition: form-data; name="'.$name.'"'.$this->_lineBreak;
      $requestBody .= $this->_lineBreak;
      $requestBody .= $value.$this->_lineBreak;
    }
    $requestBodyClose = '--'.$boundary.'--'.$this->_lineBreak;
    $requestBodySize = strlen($requestBody) + strlen($requestBodyClose);
    $requestFileHeaders = array();
    if (isset($this->_requestFiles) &&
      is_array($this->_requestFiles) &&
      count($this->_requestFiles) > 0) {
      /** @var \Papaya\HTTP\Client\File $file */
      foreach ($this->_requestFiles as $name => $file) {
        $size = $file->getSize();
        if (!empty($name) && $size > 0) {
          $requestFileHeader = '--'.$boundary.$this->_lineBreak;
          $requestFileHeader .= $file->getHeaders();
          $requestFileHeader .= $this->_lineBreak;
          $requestBodySize += $size + strlen($requestFileHeader) + strlen($this->_lineBreak);
          $requestFileHeaders[$name] = $requestFileHeader;
        }
      }
    }
    if ($chunked) {
      $this->_socket->write($this->_lineBreak);
      $this->_socket->writeChunk($requestBody);
      foreach ($requestFileHeaders as $name => $header) {
        $file = $this->_requestFiles[$name];
        $this->_socket->writeChunk($header);
        $file->send($this->_socket, TRUE);
      }
      $this->_socket->writeChunk($requestBodyClose);
      $this->_socket->writeChunkEnd();
    } else {
      $this->_socket->write(
        'Content-Length: '.$requestBodySize.$this->_lineBreak.$this->_lineBreak
      );
      $this->_socket->write($requestBody);
      foreach ($requestFileHeaders as $name => $header) {
        $file = $this->_requestFiles[$name];
        $this->_socket->write($header);
        $file->send($this->_socket, FALSE);
      }
      $this->_socket->write($requestBodyClose);
    }
  }

  /**
   * This method sends raw POST data without any conversion or additional headers.
   *
   * @return void
   */
  private function _sendRawPostData() {
    $data = $this->_lineBreak;
    $data .= implode('', $this->_requestData);
    $data .= $this->_lineBreak;
    $this->_socket->write($data);
  }

  /**
   * send urlencoded form data request body (no file uploads)
   *
   * @return void
   */
  private function _sendUrlencodedFormData() {
    $data = '';
    foreach ($this->_requestData as $name => $value) {
      $data .= '&'.rawurlencode($name).'='.rawurlencode($value);
    }
    $data = substr($data, 1);
    $requestHeaders = $this->getRequestHeaders();
    $additionalRequestHeaders = new \Papaya\HTTP\Headers();
    if (!isset($requestHeaders['Content-Type'])) {
      $additionalRequestHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
    }
    $additionalRequestHeaders['Content-Length'] = strlen($data);
    $this->_socket->write($additionalRequestHeaders.$this->_lineBreak.$data);
  }

  /**
   * Get the Request-URI for use in the HTTP Request-Line
   * at the start of the Request.
   * As this is usually a relative URI it is only useful for the actual request.
   *
   * @return string
   */
  public function getRequestUri() {
    if (empty($this->_url['path'])) {
      $path = '/';
    } else {
      $path = $this->_url['path'];
    }
    if (!empty($this->_url['query'])) {
      $path .= '?'.$this->_url['query'];
    }
    if (is_array($this->_requestData) &&
      count($this->_requestData) > 0 &&
      in_array($this->_method, array('GET', 'HEAD', 'COPY', 'DELETE'))) {
      $queryString = '';
      foreach ($this->_requestData as $name => $value) {
        $queryString .= '&'.rawurlencode($name).'='.rawurlencode($value);
        if (strlen($queryString) > 4048) {
          $queryString = substr($queryString, 0, 4048);
          break;
        }
      }
      if (FALSE !== strpos($path, '?')) {
        $path .= $queryString;
      } else {
        $path .= '?'.substr($queryString, 1);
      }
    }
    if (isset($this->_proxy)) {
      $host = 'http://'.$this->_url['host'];
      if (isset($this->_url['port']) && $this->_url['port'] > 0) {
        $host .= ':'.((int)$this->_url['port']);
      }
      $path = $host.$path;
    }
    return $path;
  }

  /**
   * get the request headers in one string
   *
   * @access public
   * @return string
   */
  public function getRequestHeaderString() {
    $result = '';
    $path = $this->getRequestUri();
    $result .= $this->_method.' '.$path.' HTTP/1.1'.$this->_lineBreak;
    $requestHeaders = $this->getRequestHeaders();
    if (!isset($requestHeaders['Host'])) {
      $result .= 'Host: '.$this->_url['host'].$this->_lineBreak;
    }
    if (isset($this->_proxyAuthorization)) {
      $proxyAuthorization = $this->_proxyAuthorization['user'];
      if (!empty($this->_proxyAuthorization['password'])) {
        $proxyAuthorization .= ':';
        $proxyAuthorization .= $this->_proxyAuthorization['password'];
      }
      $result .= 'Proxy-Authorization: basic '.base64_encode($proxyAuthorization).$this->_lineBreak;
    }
    $result .= (string)$requestHeaders;
    return $result;
  }

  /**
   * close the current socket
   *
   * @access public
   * @return boolean
   */
  public function close() {
    if (isset($this->_socket)) {
      return $this->_socket->close();
    }
    return FALSE;
  }

  /**
   * set the http method, note that not all methods support additional request data
   *
   * @param string $method
   * @access public
   * @return void
   */
  public function setMethod($method) {
    $method = strtoupper($method);
    switch ($method) {
      case 'COPY' :
      case 'DELETE' :
      case 'GET' :
      case 'HEAD' :
      case 'POST' :
      case 'PUT' :
        $this->_method = $method;
      break;
    }
  }

  /**
   * return current http method
   *
   * @return string
   */
  public function getMethod() {
    return $this->_method;
  }

  /**
   * set a http header, the second parameter allows to set several headers with the same name.
   *
   * @param string $name header name
   * @param string $value
   * @param boolean $allowDuplicates optional, default value FALSE
   * @access public
   * @return boolean
   */
  public function setHeader($name, $value, $allowDuplicates = FALSE) {
    return $this->getRequestHeaders()->set($name, $value, $allowDuplicates);
  }

  /**
   * get a request http header value
   *
   * @param string $name
   * @return string|array|NULL
   */
  public function getHeader($name) {
    return $this->getRequestHeaders()->get($name);
  }

  /**
   * add request data, this data will be encoded and send to the server
   *
   * @param mixed $data
   * @param mixed $value - if value is set $data should be an simple name string
   * @access public
   * @return void
   */
  public function addRequestData($data, $value = NULL) {
    if (isset($value)) {
      $data = array(
        (string)$data => $value
      );
    }
    if (is_array($data)) {
      foreach ($data as $name => $value) {
        if (is_array($value)) {
          $elements = $this->_flattenArray($name, $value);
          foreach ($elements as $flatName => $flatValue) {
            $this->_requestData[$flatName] = (string)$flatValue;
          }
        } else {
          $this->_requestData[$name] = (string)$value;
        }
      }
    }
  }

  /**
   * add files to request
   *
   * @param \Papaya\HTTP\Client\File $file
   * @return boolean
   */
  public function addRequestFile(\Papaya\HTTP\Client\File $file) {
    $this->_requestFiles[$file->getName()] = $file;
    return TRUE;
  }

  /**
   * Flatten a recursive array to a single level depth - the keys are build using []
   *
   * @param string $name
   * @param array $data
   * @return array
   */
  private function _flattenArray($name, $data) {
    $result = array();
    foreach ($data as $elementName => $value) {
      $elementPath = $name.'['.$elementName.']';
      if (is_array($value)) {
        $elements = $this->_flattenArray($elementPath, $value);
        foreach ($elements as $flatName => $flatValue) {
          $result[$flatName] = (string)$flatValue;
        }
      } else {
        $result[$elementPath] = (string)$value;
      }
    }
    return $result;
  }

  /**
   * read response headers from socket
   *
   * @return void
   */
  public function readResponseHeaders() {
    $responseHeaders = $this->getResponseHeaders(TRUE);
    if (isset($this->_socket) &&
      $this->_socket->isActive()) {
      $headerLines = array();
      $this->_socket->activateReadTimeout($this->_timeoutRead);
      while (!$this->_socket->eof()) {
        $headerLine = chop($this->_socket->readLine());
        if (empty($headerLine)) {
          break;
        } elseif (preg_match('(^HTTP/1.[01]\s+(\d+))', $headerLine, $match)) {
          $this->_responseStatus = (int)$match[1];
        } elseif (substr($headerLine, 0, 1) == "\t") {
          $headerLines[count($headerLines) - 1] .= trim($headerLine);
        } else {
          $headerLines[] = $headerLine;
        }
      }
      foreach ($headerLines as $line) {
        $pos = strpos($line, ':');
        if ($pos > 0) {
          $name = substr($line, 0, $pos);
          $value = trim(substr($line, $pos + 1));
          $responseHeaders->set($name, $value);
        }
      }
      $this->_actOnResponseHeaders($responseHeaders);
    }
  }

  /**
   * act on certain response headers
   *
   * @param \Papaya\HTTP\Headers $responseHeaders
   */
  private function _actOnResponseHeaders($responseHeaders) {
    if (isset($responseHeaders['Connection']) &&
      strtolower($responseHeaders['Connection']) === 'close') {
      $this->_socket->setKeepAlive(FALSE);
    }
    if ($this->_method === 'HEAD') {
      $this->_socket->setContentLength(0);
    } elseif (isset($responseHeaders['Location']) &&
      $this->_redirectLimit > $this->_redirects++) {
      $this->_handleRedirect($responseHeaders['Location']);
    } elseif (isset($responseHeaders['Transfer-Encoding']) &&
      strtolower($responseHeaders['Transfer-Encoding']) == 'chunked') {
      $this->_socket->setContentLength(-2);
    } elseif (isset($responseHeaders['Content-Length']) &&
      $responseHeaders['Content-Length'] > 0) {
      $this->_socket->setContentLength((int)$responseHeaders['Content-Length']);
    } else {
      $this->_socket->setContentLength(-1);
    }
  }

  /**
   * Do a redirect
   *
   * @param $targetLocation
   */
  private function _handleRedirect($targetLocation) {
    $this->_socket->close();
    $this->setUrl($targetLocation);
    $this->send();
  }

  /**
   * return response status
   *
   * @access public
   * @return integer
   */
  public function getResponseStatus() {
    return $this->_responseStatus;
  }

  /**
   * get response header value
   *
   * @param string $name
   * @access public
   * @return mixed
   */
  public function getResponseHeader($name) {
    return $this->getResponseHeaders()->get($name);
  }

  /**
   * get response data and close socket
   *
   * @access public
   * @return string
   */
  public function getResponseData() {
    $result = '';
    if (isset($this->_socket) &&
      $this->_socket->isActive()) {
      while (!$this->_socket->eof()) {
        $data = $this->_socket->read();
        $result .= $data;
      }
      $this->_socket->close();
    }
    return $result;
  }
}
