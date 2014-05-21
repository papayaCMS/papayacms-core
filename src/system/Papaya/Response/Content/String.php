<?php
/**
* Simple string response content
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
* @subpackage Response
* @version $Id: String.php 38146 2013-02-20 11:29:05Z weinert $
*/

/**
* Simple string response content
*
* Additionally to length() and output(), the object supports to be casted to a string.
*
* @package Papaya-Library
* @subpackage Response
*/
class PapayaResponseContentString implements PapayaResponseContent {

  /**
  * string content buffer
  * @var string
  */
  private $_content = '';

  /**
  * Initialize object from a string
  *
  * @param string $contentString
  */
  public function __construct($contentString) {
    PapayaUtilConstraints::assertString($contentString);
    $this->_content = $contentString;
  }

  /**
  * Return content length for the http header
  *
  * @return integer
  */
  public function length() {
    return strlen($this->_content);
  }

  /**
  * Output string content to standard output
  *
  * @return string
  */
  public function output() {
    echo $this->_content;
  }

  /**
  * Cast object back into a string
  *
  * @return string
  */
  public function __toString() {
    return $this->_content;
  }
}