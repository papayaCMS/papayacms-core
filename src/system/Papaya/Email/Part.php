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
* An email consists of one or more parts, each part has indivdual headers an a content. The
* content can be a list of other parts.
*
* @package Papaya-Library
* @subpackage Email
*
* @property PapayaEmailHeaders $headers
* @property PapayaEmailContent $content
*/
class PapayaEmailPart {

  /**
  * Internal variable for the headers subobject
  *
  * @var PapayaEmailHeaders $headers
  */
  protected $_headers = NULL;
  /**
  * Internal variable for the content subobject
  *
  * @var PapayaEmailContent $headers
  */
  protected $_content = NULL;

  /**
  * Create object and set content subobject
  *
  * @param PapayaEmailContent $content
  */
  public function __construct(PapayaEmailContent $content) {
    $this->content($content);
  }

  /**
  * Getter/Setter vor header subobject
  *
  * @param PapayaEmailHeaders $headers
  * @return PapayaEmailHeaders
  */
  public function headers(PapayaEmailHeaders $headers = NULL) {
    if (isset($headers)) {
      $this->_headers = $headers;
    }
    if (is_null($this->_headers)) {
      $this->_headers = new \PapayaEmailHeaders();
    }
    return $this->_headers;
  }

  /**
  * Getter/Setter for content subobject
  *
  * @param PapayaEmailContent $content
  * @return PapayaEmailContent
  */
  public function content(PapayaEmailContent $content = NULL) {
    if (isset($content)) {
      $this->_content = $content;
    }
    return $this->_content;
  }

  /**
   * Allow headers() and content() to be used as properties.
   *
   * @param string $name
   * @throws LogicException
   * @return \PapayaEmailContent|\PapayaEmailHeaders
   */
  public function __get($name) {
    switch ($name) {
    case 'headers' :
      return $this->headers();
    case 'content' :
      return $this->content();
    }
    throw new \LogicException(
      sprintf(
        'LogicException: Unknown property "%s::$%s".',
        get_class($this),
        $name
      )
    );
  }

  /**
   * Allow headers() and content() to be used as properties.
   *
   * @param string $name
   * @param mixed $value
   * @throws LogicException
   */
  public function __set($name, $value) {
    switch ($name) {
    case 'headers' :
      $this->headers($value);
      return;
    case 'content' :
      $this->content($value);
      return;
    }
    throw new \LogicException(
      sprintf(
        'LogicException: Unknown property "%s::$%s".',
        get_class($this),
        $name
      )
    );
  }
}
