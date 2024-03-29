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
 * Abstract class for file upload handling
 *
 * @package Papaya-Library
 * @subpackage HTTP-Client
 */
abstract class File {
  /**
   * linebreak chars
   *
   * @var string
   */
  protected $_lineBreak = "\r\n";

  /**
   * buffer size for read and send file data
   *
   * @var int
   */
  protected $_bufferSize = 10240;

  /**
   * field name
   *
   * @var string
   */
  protected $_name = '';

  /**
   * file name
   *
   * @var string
   */
  protected $_fileName = '';

  /**
   * file mime type
   *
   * @var string
   */
  protected $_mimeType = '';

  /**
   * abstract send function
   *
   * @param Socket $socket
   * @param bool $chunked optional, default value FALSE
   * @param int $bufferSize optional, default value 0
   */
  abstract public function send(Socket $socket, $chunked = FALSE, $bufferSize = 0);

  /**
   * get file size property value
   *
   * @return int
   */
  abstract public function getSize();

  /**
   * get file name property
   *
   * @throws \UnexpectedValueException
   *
   * @return string
   */
  public function getName() {
    if (empty($this->_name)) {
      throw new \UnexpectedValueException('Invalid name property', E_USER_WARNING);
    }
    return $this->_name;
  }

  public function getFileName(): string {
    return $this->_fileName;
  }

  public function getMimeType(): string {
    return $this->_mimeType;
  }

  /**
   * get http headers (for multipart formatting)
   *
   * @return string
   */
  public function getHeaders() {
    $result = \sprintf(
      'Content-Disposition: form-data; name="%s"; filename="%s"'.$this->_lineBreak,
      $this->_name,
      $this->_fileName
    );
    $result .= 'Content-Transfer-Encoding: binary'.$this->_lineBreak;
    if (empty($this->_mimeType)) {
      $result .= 'Content-Type: application/octet-stream'.$this->_lineBreak;
    } else {
      $result .= 'Content-Type: '.$this->_mimeType.$this->_lineBreak;
    }
    $result .= 'Content-Length: '.$this->getSize().$this->_lineBreak;
    return $result;
  }

  /**
   * escape a header value (like the filename)
   *
   * @param $value
   *
   * @return string
   */
  protected function _escapeHeaderValue($value) {
    return \str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
  }
}
