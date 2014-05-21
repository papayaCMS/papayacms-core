<?php
/**
* Papaya HTTP Client File String - handle file upload resource using a data string
*
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @subpackage HTTP-Client
*/

/**
* Papaya HTTP Client File String - handle file upload resource using a data string
*
* @package Papaya-Library
* @subpackage HTTP-Client
*/
class PapayaHttpClientFileString extends PapayaHttpClientFile {

  /**
  * data size
  * @var NULL|Integer
  */
  protected $_size = NULL;
  /**
  * content
  * @var string
  */
  private $_data = '';

  /**
  * constructor
  *
  * @param string $name
  * @param string $fileName
  * @param string $data
  * @param string $mimeType optional, default value ''
  * @access public
  */
  public function __construct($name, $fileName, $data, $mimeType = '') {
    if (!empty($name) &&
        !empty($fileName) &&
        is_string($data) &&
        !empty($data)) {
      $this->_name = $name;
      $this->_fileName = $fileName;
      $this->_data = $data;
      if (!empty($mimeType)) {
        $this->_mimeType = $mimeType;
      }
    } else {
      trigger_error('Invalid configuration for element: '.$name, E_USER_WARNING);
    }
  }

  /**
  * set data string size and/or return it
  *
  * @access public
  * @return integer
  */
  public function getSize() {
    if (!isset($this->_size)) {
      $this->_size = strlen($this->_data);
    }
    return $this->_size;
  }

  /**
  * send file data
  *
  * @param PapayaHttpClientSocket $socket
  * @param boolean $chunked optional, default value FALSE
  * @param integer $bufferSize optional, default value 0
  * @access public
  * @return void
  */
  public function send(PapayaHttpClientSocket $socket, $chunked = FALSE, $bufferSize = 0) {
    if (is_string($this->_data) && $this->getSize() > 0) {
      if ($socket->isActive()) {
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
}