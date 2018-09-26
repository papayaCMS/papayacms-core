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
namespace Papaya\HTTP\Client;

/**
 * Papaya HTTP Client Socket - Handles the connection resource
 *
 * @package Papaya-Library
 * @subpackage HTTP-Client
 */
class Socket {
  /**
   * connection resource id
   *
   * @var resource
   */
  protected $_resource;

  /**
   * connection pool object
   *
   * @var Socket\Pool
   */
  private $_pool;

  /**
   * host used the connection was opened to
   *
   * @var string
   */
  private $_host;

  /**
   * port the connection was opened to
   *
   * @var int
   */
  private $_port;

  /**
   * size of the current chunk (reading only)
   *
   * @var int
   */
  private $_currentChunkSize = 0;

  /**
   * linebreak chars
   *
   * @var string
   */
  private $_lineBreak = "\r\n";

  /**
   * expected content length
   *
   * -1 no content length (read until connection closes)
   * -2 chunked encoding
   *
   * @var int
   */
  private $_contentLength = -1;

  /**
   * FALSE if the connection should not be put into the pool on close
   *
   * @var bool
   */
  private $_keepAlive = TRUE;

  /**
   * set the connection pool object
   *
   * @param Socket\Pool $pool
   */
  public function setPool(Socket\Pool $pool) {
    $this->_pool = $pool;
  }

  /**
   * return the connection pool object
   *
   * @return Socket\Pool
   */
  public function getPool() {
    if (NULL === $this->_pool) {
      $this->_pool = new Socket\Pool();
    }
    return $this->_pool;
  }

  /**
   * open the socket
   *
   * @param string $host
   * @param int $port
   * @param int $timeout optional, default value 10
   * @param string $transport optional, default value ''
   *
   * @return bool
   */
  public function open($host, $port, $timeout = 10, $transport = '') {
    $this->_contentLength = -1;
    $this->_host = $host;
    $this->_port = $port;
    $this->_keepAlive = TRUE;
    $this->_resource = $this->getPool()->getConnection($host, $port);
    $hostUri = $transport.'://'.$host;

    if (NULL === $this->_resource) {
      $errorNo = 0;
      $errorString = '';
      $this->_resource = @\fsockopen(
        $hostUri, $port, $errorNo, $errorString, $timeout
      );
    }

    if (false === $this->_resource) {
      $ip = \gethostbyname($host);
      if (!empty($transport)) {
        $ip = $transport.'://'.$ip;
      }
      $errorNo = 0;
      $errorString = '';
      $this->_resource = @\fsockopen(
        $ip, $port, $errorNo, $errorString, $timeout
      );
    }
    return \is_resource($this->_resource);
  }

  /**
   * set resource id (dependency injection for testing)
   *
   * @param $resource
   */
  public function setResource($resource) {
    $this->_resource = $resource;
  }

  /**
   * set the content length to read
   *
   * @param int $length -1 means unknown, -2 means chunked mode
   */
  public function setContentLength($length) {
    $this->_contentLength = $length;
  }

  /**
   * read response data
   *
   * @param $maxBytes
   *
   * @return string
   */
  public function read($maxBytes = 8192) {
    if ($this->_contentLength > 0) {
      $data = $this->_readBytes(
        ($maxBytes > $this->_contentLength) ? $this->_contentLength : $maxBytes
      );
      $size = \strlen($data);
      if ($this->_contentLength - $size > 0) {
        $this->_contentLength -= $size;
      } else {
        $this->_contentLength = 0;
        $this->close();
      }
      return $data;
    }
    if (-1 === $this->_contentLength) {
      return $this->_readBytes($maxBytes);
    }
    if (-2 === $this->_contentLength) {
      return $this->_readChunked($maxBytes);
    }
    return FALSE;
  }

  /**
   * read n bytes from response data
   *
   * @param int $maxBytes optional, default value 2048
   *
   * @return string
   */
  private function _readBytes($maxBytes) {
    $data = \fread($this->_resource, $maxBytes);
    $this->closeOnTimeout();
    return $data;
  }

  /**
   * read chunked response data (Transfer-Encoding: chunked)
   *
   * @param int $maxBytes optional, default value 2048
   *
   * @return string
   */
  private function _readChunked($maxBytes = 8192) {
    $result = '';
    if (0 === $this->_currentChunkSize) {
      $line = \rtrim($this->readLine());
      if ('0' === $line) {
        $this->_readBytes(2);
        $this->_contentLength = 0;
        $this->close();
        return FALSE;
      }
      if (\preg_match('(^([0-9a-f]+)(?:;.*)?$)i', $line, $match)) {
        $this->_currentChunkSize = \hexdec($match[1]);
      }
    }
    if ($this->_currentChunkSize > 0) {
      $readBytes = ($this->_currentChunkSize > $maxBytes) ? $maxBytes : $this->_currentChunkSize;
      $result = $this->_readBytes($readBytes);
      $this->_currentChunkSize -= \strlen($result);
      if ($this->_currentChunkSize <= 0) {
        $this->_readBytes(2);
      }
    }
    return $result;
  }

  /**
   * read a single line from response data
   *
   * @return string
   */
  public function readLine() {
    $data = \fgets($this->_resource);
    $this->closeOnTimeout();
    return $data;
  }

  /**
   * write line breaks
   *
   * @param $count
   */
  public function writeLineBreak($count = 1) {
    $this->write(\str_repeat($this->_lineBreak, $count));
  }

  /**
   * write request data
   *
   * @param $data
   */
  public function write($data) {
    \fwrite($this->_resource, $data);
  }

  /**
   * write request data chunked
   *
   * @param string $data optional, default value ''
   */
  public function writeChunk($data = '') {
    $this->write(\dechex(\strlen($data)).$this->_lineBreak);
    $this->write($data.$this->_lineBreak);
  }

  /**
   * write end chunk (0 byte chunk)
   */
  public function writeChunkEnd() {
    $this->write('0'.$this->_lineBreak.$this->_lineBreak);
  }

  /**
   * check for eof status
   *
   * @return bool
   */
  public function eof() {
    if (0 !== $this->_contentLength && $this->isActive()) {
      return \feof($this->_resource);
    }
    return TRUE;
  }

  /**
   * check for active connection resource
   *
   * @return bool
   */
  public function isActive() {
    return (NULL !== $this->_resource && \is_resource($this->_resource));
  }

  /**
   * close active connection resource
   *
   * @return bool
   */
  public function close() {
    if ($this->isActive()) {
      if (FALSE === $this->_keepAlive) {
        \fclose($this->_resource);
        $this->_resource = NULL;
      } elseif (0 === $this->_contentLength) {
        $this->getPool()
          ->putConnection($this->_resource, $this->_host, $this->_port);
        $this->_resource = NULL;
      } elseif ($this->_contentLength > 0 &&
                $this->_contentLength < 10 * 1024 * 1024) {
        while (!$this->eof()) {
          $this->read(2048);
        }
      } else {
        \fclose($this->_resource);
        $this->_resource = NULL;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Set if the connection is to be put back into the connection pool
   *   on close after this request.
   *
   * @param bool $keepAlive
   *
   * @return bool value that is now in effect
   */
  public function setKeepAlive($keepAlive) {
    if (\is_bool($keepAlive)) {
      $this->_keepAlive = $keepAlive;
    }
    return $this->_keepAlive;
  }

  /**
   * Activate a reading timeout for the stream. The value is given in seconds.
   *
   * @param int $time
   *
   * @return bool
   */
  public function activateReadTimeout($time) {
    if ($this->isActive()) {
      return \stream_set_timeout($this->_resource, $time);
    }
    return FALSE;
  }

  /**
   * Validate if the current connection has timed out.
   *
   * @return bool
   */
  public function hasTimedOut() {
    $meta = \stream_get_meta_data($this->_resource);
    return isset($meta['timed_out']) && $meta['timed_out'];
  }

  /**
   * Close the current connection if it has timed out
   */
  private function closeOnTimeout() {
    if ($this->hasTimedOut()) {
      $this->close();
    }
  }
}
