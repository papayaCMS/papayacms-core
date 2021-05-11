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
namespace Papaya\HTTP {

  use Papaya\HTTP\Client\File as HTTPClientFile;
  use Papaya\HTTP\Headers as HTTPHeaders;
  use Papaya\URL;
  use Papaya\Utility\Random;

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
     * @var Client\Socket
     */
    private $_socket;

    /**
     * request method
     *
     * @var string
     */
    private $_method = 'GET';

    /**
     * remote url
     *
     * @var array
     */
    private $_url = [];

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
    private $_proxy;

    /**
     * proxy server authorization
     *
     * @var array
     */
    private $_proxyAuthorization;

    /**
     * timeout in seconds for request while connecting
     *
     * @var int
     */
    private $_timeout = 10;

    /**
     * timeout in seconds for request while reading data
     *
     * @var int
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
     * @var HTTPHeaders
     */
    private $_requestHeaders;

    /**
     * http request headers
     *
     * @var string[]
     */
    private $_defaultRequestHeaders = [
      'Accept' => '*/*',
      'Accept-Charset' => 'utf-8,*',
      'Connection' => 'keep-alive'
    ];

    /**
     * request data array
     *
     * @var array
     */
    private $_requestData = [];

    /**
     * request files array
     *
     * @var HTTPClientFile[]
     */
    private $_requestFiles = [];

    /**
     * http response headers
     *
     * @var HTTPHeaders
     */
    protected $_responseHeaders;

    /**
     * http response status code
     *
     * @var int
     */
    private $_responseStatus = 0;

    /**
     * maximum internal redirects
     *
     * @var int
     */
    private $_redirectLimit = 10;

    /**
     * internal redirect counter
     *
     * @var int
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
        $this->setURL($url);
      }
    }

    /**
     * set the url to request
     *
     * @param string $url
     *
     * @throws \InvalidArgumentException
     */
    public function setURL($url) {
      if (!empty($url)) {
        $urlObject = new URL();
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
        $transformer = new \Papaya\URL\Transformer\Absolute();
        $newURL = $transformer->transform($urlObject, $url);
        $url = $newURL;
        $this->_url = \parse_url($url);
      } else {
        throw new \InvalidArgumentException('Invalid url');
      }
    }

    public function getMethod(): string {
      return $this->_method;
    }

    public function getParsedURL(): array {
      return $this->_url;
    }

    /**
     * reset request/response data
     */
    public function reset() {
      $this->_requestHeaders = NULL;
      $this->_requestData = [];
      $this->_requestFiles = [];
      $this->_responseHeaders = NULL;
      $this->_responseStatus = 0;
      $this->_redirects = 0;
    }

    /**
     * @return Headers
     */
    public function getRequestHeaders() {
      if (NULL === $this->_requestHeaders) {
        $this->_requestHeaders = new HTTPHeaders($this->_defaultRequestHeaders);
      }
      return $this->_requestHeaders;
    }

    public function getResponseHeaders($reset = FALSE) {
      if ($reset || NULL === $this->_responseHeaders) {
        $this->_responseHeaders = new HTTPHeaders();
      }
      return $this->_responseHeaders;
    }

    /**
     * Dependency injection of a socket object
     *
     * @param $socket
     */
    public function setSocket(Client\Socket $socket) {
      $this->_socket = $socket;
    }

    /**
     * return socket object
     *
     * @return Client\Socket
     */
    public function getSocket() {
      if (NULL === $this->_socket) {
        $this->_socket = new Client\Socket();
      }
      return $this->_socket;
    }

    /**
     * Set the transport protocol
     *
     * @param string $transport
     *
     * @return bool TRUE if empty or available in stream_get_transports(), FALSE otherwise
     */
    public function setTransport($transport) {
      $result = FALSE;
      if ('' === $transport || \in_array($transport, \stream_get_transports(), TRUE)) {
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
     * @param int $port optional, default value NULL
     * @param string $user optional, default value NULL
     * @param string $password optional, default value NULL
     *
     * @throws \InvalidArgumentException
     */
    public function setProxy($server, $port = NULL, $user = NULL, $password = NULL) {
      $this->_proxy = NULL;
      $this->_proxyAuthorization = NULL;
      if (!empty($server)) {
        $this->_proxy = [
          'host' => $server
        ];
        if (isset($port) && $port > 0) {
          $this->_proxy['port'] = (int)$port;
        } else {
          $this->_proxy['port'] = 80;
        }
        if (!empty($user)) {
          $this->_proxyAuthorization = [
            'user' => $user
          ];
          if (!empty($password)) {
            $this->_proxyAuthorization['password'] = $password;
          }
        }
      } else {
        throw new \InvalidArgumentException('Invalid proxy server');
      }
    }

    public function getProxyConfiguration(): array {
      return $this->_proxy;
    }

    public function getProxyAuthorization(): array {
      return $this->_proxyAuthorization;
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
     * @return bool
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
          if (
            isset($this->_requestFiles) &&
            \is_array($this->_requestFiles) &&
            \count($this->_requestFiles) > 0
          ) {
            $this->_sendMultipartFormData(
              isset($requestHeaders['Transfer-Encoding']) &&
              'chunked' === $requestHeaders['Transfer-Encoding']
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
            $this->_sendURLEncodedFormData();
          }
          break;
        case 'PUT' :
          if (
            isset($this->_requestFiles) &&
            \is_array($this->_requestFiles) &&
            \count($this->_requestFiles) > 0
          ) {
            $file = \reset($this->_requestFiles);
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
     * @return Client\Socket
     */
    public function open() {
      $socket = $this->getSocket();
      if (isset($this->_proxy)) {
        $server = $this->_proxy['host'];
        $port = $this->_proxy['port'];
      } else {
        $server = empty($this->_url['host'])
          ? 'localhost' : $this->_url['host'];
        $defaultPort = 'https' === $this->_url['scheme'] ? 443 : 80;
        $port = empty($this->_url['port']) || $this->_url['port'] <= 0
          ? $defaultPort : (int)$this->_url['port'];
        if ('https' === $this->_url['scheme'] && empty($this->_transport)) {
          $this->setTransport('tls');
        }
      }
      $opened = $socket->open(
        $server, $port, $this->_timeout, $this->_transport
      );
      if ($opened) {
        if ('close' === \strtolower($this->getHeader('Connection'))) {
          $socket->setKeepAlive(FALSE);
        }
        $socket->write($this->getRequestHeaderString());
        return $socket;
      }
      return NULL;
    }

    /**
     * send a multipart/form-data formatted request body
     *
     * @param bool $chunked optional, default value FALSE
     */
    private function _sendMultipartFormData($chunked = FALSE) {
      $boundary = '-------------'.\md5(Random::rand());
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
      $requestBodySize = \strlen($requestBody) + \strlen($requestBodyClose);
      $requestFileHeaders = [];
      if (
        isset($this->_requestFiles) &&
        \is_array($this->_requestFiles) &&
        \count($this->_requestFiles) > 0
      ) {
        /** @var HTTPClientFile $file */
        foreach ($this->_requestFiles as $name => $file) {
          $size = $file->getSize();
          if (!empty($name) && $size > 0) {
            $requestFileHeader = '--'.$boundary.$this->_lineBreak;
            $requestFileHeader .= $file->getHeaders();
            $requestFileHeader .= $this->_lineBreak;
            $requestBodySize += $size + \strlen($requestFileHeader) + \strlen($this->_lineBreak);
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
     */
    private function _sendRawPostData() {
      $data = $this->_lineBreak;
      $data .= \implode('', $this->_requestData);
      $data .= $this->_lineBreak;
      $this->_socket->write($data);
    }

    /**
     * send urlencoded form data request body (no file uploads)
     */
    private function _sendURLEncodedFormData() {
      $data = '';
      foreach ($this->_requestData as $name => $value) {
        $data .= '&'.\rawurlencode($name).'='.\rawurlencode($value);
      }
      $data = \substr($data, 1);
      $requestHeaders = $this->getRequestHeaders();
      $additionalRequestHeaders = new HTTPHeaders();
      if (!isset($requestHeaders['Content-Type'])) {
        $additionalRequestHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
      }
      $additionalRequestHeaders['Content-Length'] = \strlen($data);
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
      if (
        \is_array($this->_requestData) &&
        \count($this->_requestData) > 0 &&
        \in_array($this->_method, ['GET', 'HEAD', 'COPY', 'DELETE'])
      ) {
        $queryString = '';
        foreach ($this->_requestData as $name => $value) {
          $queryString .= '&'.\rawurlencode($name).'='.\rawurlencode($value);
          if (\strlen($queryString) > 4048) {
            $queryString = \substr($queryString, 0, 4048);
            break;
          }
        }
        if (FALSE !== \strpos($path, '?')) {
          $path .= $queryString;
        } else {
          $path .= '?'.\substr($queryString, 1);
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
        $result .= 'Proxy-Authorization: basic '.\base64_encode($proxyAuthorization).$this->_lineBreak;
      }
      $result .= $requestHeaders;
      return $result;
    }

    /**
     * close the current socket
     *
     * @return bool
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
     */
    public function setMethod($method) {
      $method = \strtoupper($method);
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
     * set a http header, the second parameter allows to set several headers with the same name.
     *
     * @param string $name header name
     * @param string $value
     * @param bool $allowDuplicates optional, default value FALSE
     *
     * @return bool
     */
    public function setHeader($name, $value, $allowDuplicates = FALSE) {
      return $this->getRequestHeaders()->set($name, $value, $allowDuplicates);
    }

    /**
     * get a request http header value
     *
     * @param string $name
     *
     * @return string|array|null
     */
    public function getHeader($name) {
      return $this->getRequestHeaders()->get($name);
    }

    /**
     * add request data, this data will be encoded and send to the server
     *
     * @param mixed $data
     * @param mixed $value - if value is set $data should be an simple name string
     */
    public function addRequestData($data, $value = NULL) {
      if (isset($value)) {
        $data = [
          (string)$data => $value
        ];
      }
      if (\is_array($data)) {
        foreach ($data as $name => $subValue) {
          if (\is_array($subValue)) {
            $elements = $this->_flattenArray($name, $subValue);
            foreach ($elements as $flatName => $flatValue) {
              $this->_requestData[$flatName] = (string)$flatValue;
            }
          } else {
            $this->_requestData[$name] = (string)$subValue;
          }
        }
      }
    }

    public function getRequestData(): array {
      return $this->_requestData;
    }

    /**
     * add files to request
     *
     * @param HTTPClientFile $file
     *
     * @return bool
     */
    public function addRequestFile(HTTPClientFile $file) {
      $this->_requestFiles[$file->getName()] = $file;
      return TRUE;
    }

    /**
     * Flatten a recursive array to a single level depth - the keys are build using []
     *
     * @param string $name
     * @param array $data
     *
     * @return array
     */
    private function _flattenArray($name, $data) {
      $result = [];
      foreach ($data as $elementName => $value) {
        $elementPath = $name.'['.$elementName.']';
        if (\is_array($value)) {
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
     */
    public function readResponseHeaders() {
      $responseHeaders = $this->getResponseHeaders(TRUE);
      if (
        isset($this->_socket) &&
        $this->_socket->isActive()
      ) {
        $headerLines = [];
        $this->_socket->activateReadTimeout($this->_timeoutRead);
        while (!$this->_socket->eof()) {
          $headerLine = \rtrim($this->_socket->readLine());
          if (empty($headerLine)) {
            break;
          }
          if (\preg_match('(^HTTP/1.[01]\s+(\d+))', $headerLine, $match)) {
            $this->_responseStatus = (int)$match[1];
          } elseif (0 === \strpos($headerLine, "\t")) {
            $headerLines[\count($headerLines) - 1] .= \trim($headerLine);
          } else {
            $headerLines[] = $headerLine;
          }
        }
        foreach ($headerLines as $line) {
          $pos = \strpos($line, ':');
          if ($pos > 0) {
            $name = \substr($line, 0, $pos);
            $value = \trim(\substr($line, $pos + 1));
            $responseHeaders->set($name, $value);
          }
        }
        $this->_actOnResponseHeaders($responseHeaders);
      }
    }

    /**
     * act on certain response headers
     *
     * @param HTTPHeaders $responseHeaders
     */
    private function _actOnResponseHeaders($responseHeaders) {
      if (
        isset($responseHeaders['Connection']) &&
        'close' === \strtolower($responseHeaders['Connection'])
      ) {
        $this->_socket->setKeepAlive(FALSE);
      }
      if ('HEAD' === $this->_method) {
        $this->_socket->setContentLength(0);
      } elseif (
        isset($responseHeaders['Location']) &&
        $this->_redirectLimit > $this->_redirects++
      ) {
        $this->_handleRedirect($responseHeaders['Location']);
      } elseif (
        isset($responseHeaders['Transfer-Encoding']) &&
        'chunked' === \strtolower($responseHeaders['Transfer-Encoding'])
      ) {
        $this->_socket->setContentLength(-2);
      } elseif (
        isset($responseHeaders['Content-Length']) &&
        $responseHeaders['Content-Length'] > 0
      ) {
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
      $this->setURL($targetLocation);
      $this->send();
    }

    /**
     * return response status
     *
     * @return int
     */
    public function getResponseStatus() {
      return $this->_responseStatus;
    }

    /**
     * get response header value
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getResponseHeader($name) {
      return $this->getResponseHeaders()->get($name);
    }

    /**
     * get response data and close socket
     *
     * @return string
     */
    public function getResponseData() {
      $result = '';
      if (
        isset($this->_socket) &&
        $this->_socket->isActive()
      ) {
        while (!$this->_socket->eof()) {
          $data = $this->_socket->read();
          $result .= $data;
        }
        $this->_socket->close();
      }
      return $result;
    }
  }
}
