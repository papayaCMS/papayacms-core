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
 * Papaya HTTP Client File String - handle file upload resource using a data string
 *
 * @package Papaya-Library
 * @subpackage HTTP-Client
 */
class Text extends HTTP\Client\File {
  /**
   * data size
   *
   * @var null|int
   */
  private $_size;

  /**
   * content
   *
   * @var string
   */
  private $_data;

  /**
   * constructor
   *
   * @param string $name
   * @param string $fileName
   * @param string $data
   * @param string $mimeType optional, default value ''
   */
  public function __construct($name, $fileName, $data, $mimeType = '') {
    Utility\Constraints::assertString($name);
    Utility\Constraints::assertString($fileName);
    Utility\Constraints::assertString($data);
    Utility\Constraints::assertNotEmpty($name);
    Utility\Constraints::assertNotEmpty($fileName);
    Utility\Constraints::assertNotEmpty($data);
    $this->_name = $name;
    $this->_fileName = $fileName;
    $this->_data = $data;
    if (!empty($mimeType)) {
      $this->_mimeType = $mimeType;
    }
  }

  /**
   * set data string size and/or return it
   *
   * @return int
   */
  public function getSize() {
    if (NULL === $this->_size) {
      $this->_size = \strlen($this->_data);
    }
    return $this->_size;
  }

  /**
   * send file data
   *
   * @param HTTP\Client\Socket $socket
   * @param bool $chunked optional, default value FALSE
   * @param int $bufferSize optional, default value 0
   */
  public function send(HTTP\Client\Socket $socket, $chunked = FALSE, $bufferSize = 0) {
    if (
      \is_string($this->_data) &&
      $this->getSize() > 0 &&
      $socket->isActive()) {
      if ($chunked) {
        $socket->writeChunk($this->_data);
        $socket->writeChunk($this->_lineBreak);
      } else {
        $socket->write($this->_data);
        $socket->write($this->_lineBreak);
      }
    }
  }
}
