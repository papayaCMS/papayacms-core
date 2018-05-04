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

/**
* Abstract class for file upload handling
*
* @package Papaya-Library
* @subpackage HTTP-Client
*/
abstract class PapayaHttpClientFile {

  /**
  * linebreak chars
  * @var string
  */
  protected $_lineBreak = "\r\n";

  /**
  * buffer size for read and send file data
  * @var integer
  */
  protected $_bufferSize = 10240;

  /**
  * field name
  * @var string
  */
  protected $_name = '';

  /**
  * file name
  * @var string
  */
  protected $_fileName = '';

  /**
  * file mime type
  * @var string
  */
  protected $_mimeType = '';

  /**
  * file size
  * @var integer
  */
  protected $_size = 0;

  /**
  * abstract send function
  *
  * @param \PapayaHttpClientSocket $socket
  * @param boolean $chunked optional, default value FALSE
  * @param integer $bufferSize optional, default value 0
  * @return void
  */
  abstract public function send(\PapayaHttpClientSocket $socket, $chunked = FALSE, $bufferSize = 0);

  /**
  * get file size property value
  *
  * @return integer
  */
  public function getSize() {
    return $this->_size;
  }

  /**
   * get file name property
   *
   * @throws \UnexpectedValueException
   * @return string
   */
  public function getName() {
    if (empty($this->_name)) {
      throw new \UnexpectedValueException('Invalid name property', E_USER_WARNING);
    } else {
      return $this->_name;
    }
  }

  /**
  * get http headers (for multipart formatting)
  *
  * @return string
  */
  public function getHeaders() {
    $result = sprintf(
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
  * @access public
  * @return string
  */
  protected function _escapeHeaderValue($value) {
    return str_replace(array('\\', '"'), array('\\\\', '\\"'), $value);
  }
}
