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
 * Papaya HTTP Client File Name - handle file upload resource using a filename
 *
 * @package Papaya-Library
 * @subpackage HTTP-Client
 */
class Name extends HTTP\Client\File {
  /**
   * initialize to an inter value on first @see getSize()
   *
   * @var int
   */
  private $_size;

  /**
   * @param string $name
   * @param string $fileName
   * @param string $mimeType optional, default value ''
   *
   * @throws \LogicException
   */
  public function __construct($name, $fileName, $mimeType = '') {
    Utility\Constraints::assertString($name);
    Utility\Constraints::assertString($fileName);
    Utility\Constraints::assertNotEmpty($name);
    Utility\Constraints::assertNotEmpty($fileName);
    if (
      \file_exists($fileName) &&
      \is_file($fileName) &&
      \is_readable($fileName)
    ) {
      $this->_name = $name;
      $this->_fileName = $fileName;
      if (!empty($mimeType)) {
        $this->_mimeType = $mimeType;
      }
    } else {
      throw new \LogicException('Invalid configuration for element: '.$name);
    }
  }

  /**
   * read filesize and/or return it
   *
   * @return int
   */
  public function getSize() {
    if (NULL === $this->_size) {
      $this->_size = \filesize($this->_fileName);
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
   * @throws \LogicException
   */
  public function send(HTTP\Client\Socket $socket, $chunked = FALSE, $bufferSize = 0) {
    if ($fh = @\fopen($this->_fileName, 'rb')) {
      if ($socket->isActive()) {
        if ($bufferSize <= 0) {
          $bufferSize = $this->_bufferSize;
        }
        if ($chunked) {
          while (!\feof($fh)) {
            $data = \fread($fh, $bufferSize);
            if ('' !== $data) {
              $socket->writeChunk($data);
            }
          }
          $socket->writeChunk($this->_lineBreak);
        } else {
          $size = $this->getSize();
          $sent = 0;
          while (!\feof($fh) && $size >= ($sent + $bufferSize)) {
            $data = \fread($fh, $bufferSize);
            if ('' !== $data) {
              $socket->write($data);
              $sent += \strlen($data);
            }
          }
          if ($size > $sent) {
            $bytesToSend = $size - $sent;
            $data = \fread($fh, $bytesToSend);
            $socket->write($data);
          }
          $socket->write($this->_lineBreak);
        }
      }
      \fclose($fh);
    } else {
      throw new \LogicException('Could not open file: '.$this->_fileName);
    }
  }
}
