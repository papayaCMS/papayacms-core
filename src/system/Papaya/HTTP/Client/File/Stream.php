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
namespace Papaya\HTTP\Client\File;

use Papaya\HTTP;
use Papaya\Utility;

/**
 * Papaya HTTP Client File Resource - handle file upload resource using a resource id
 *
 * @package Papaya-Library
 * @subpackage HTTP-Client
 */
class Stream extends HTTP\Client\File {
  /**
   * @var int|null $_size
   */
  private $_size;

  /**
   * @var Stream $_resource
   */
  private $_resource;

  /**
   * @param string $name
   * @param string $fileName
   * @param resource $resource
   * @param string $mimeType optional, default value ''
   *
   * @throws \InvalidArgumentException
   */
  public function __construct($name, $fileName, $resource, $mimeType = '') {
    Utility\Constraints::assertString($name);
    Utility\Constraints::assertString($fileName);
    Utility\Constraints::assertNotEmpty($name);
    Utility\Constraints::assertNotEmpty($fileName);
    Utility\Constraints::assertResource($resource);
    $this->_name = (string)$name;
    $this->_fileName = (string)$fileName;
    $this->_resource = $resource;
    if (!empty($mimeType)) {
      $this->_mimeType = $mimeType;
    }
  }

  public function getResource() {
    return $this->_resource;
  }

  /**
   * read file resource size and/or return it
   *
   * @return int
   */
  public function getSize() {
    if (\is_resource($this->_resource) && NULL === $this->_size) {
      $this->_size = 0;
      $stat = \fstat($this->_resource);
      if (isset($stat['size'])) {
        $this->_size = (int)$stat['size'];
      }
    }
    return $this->_size;
  }

  /**
   * send file data
   *
   * @param HTTP\Client\Socket $socket
   * @param bool $chunked optional, default value FALSE
   * @param int $bufferSize optional, default value 0
   *
   * @throws \UnexpectedValueException
   */
  public function send(HTTP\Client\Socket $socket, $chunked = FALSE, $bufferSize = 0) {
    Utility\Constraints::assertResource($this->_resource);
    if (\is_resource($this->_resource) && $socket->isActive()) {
      if ($bufferSize <= 0) {
        $bufferSize = $this->_bufferSize;
      }
      if ($chunked) {
        while (!\feof($this->_resource)) {
          $data = \fread($this->_resource, $bufferSize);
          if ('' !== $data) {
            $socket->writeChunk($data);
          }
        }
        $socket->writeChunk($this->_lineBreak);
      } else {
        $size = $this->getSize();
        $sent = 0;
        while (!\feof($this->_resource) && $size >= ($sent + $bufferSize)) {
          $data = \fread($this->_resource, $bufferSize);
          if ('' !== $data) {
            $socket->write($data);
            $sent += \strlen($data);
          }
        }
        if ($size > $sent) {
          $bytesToSend = $size - $sent;
          $data = \fread($this->_resource, $bytesToSend);
          $socket->write($data);
        }
        $socket->write($this->_lineBreak);
      }
    }
  }
}
