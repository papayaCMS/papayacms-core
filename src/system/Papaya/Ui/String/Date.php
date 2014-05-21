<?php
/**
* Papaya Interface String Translated, a string object that will be translated before usage
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Ui
* @version $Id: Date.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Papaya Interface String Translated, a string object that will be translated before usage
*
* It allows to create a string object later casted to string. The basic string can
* be a pattern (using sprintf syntax).
*
* Additionally the pattern will be translated into the current user language before the values are
* inserted.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiStringDate extends PapayaUiString {

  const SHOW_DATE = 0;
  const SHOW_TIME = 1;
  const SHOW_SECONDS = 2;

  /**
  * Store timestamp
  *
  * @var integer
  */
  private $_timestamp = 0;

  private $_options = self::SHOW_TIME;

  /**
   * create object and store timestamp
   *
   * @param integer $timestamp
   * @param int $options
   */
  public function __construct($timestamp, $options = self::SHOW_TIME) {
    $this->_timestamp = (int)$timestamp;
    $this->_options = (int)$options;
  }

  /**
  * Allow to cast the object into a string, converting the timestamp into a string.
  *
  * return string
  */
  public function __toString() {
    $pattern = 'Y-m-d';
    if (PapayaUtilBitwise::inBitmask(self::SHOW_TIME, $this->_options)) {
      $pattern .= ' H:i';
      if (PapayaUtilBitwise::inBitmask(self::SHOW_SECONDS, $this->_options)) {
        $pattern .= ':s';
      }
    }
    if (is_null($this->_string)) {
      $this->_string = date($pattern, $this->_timestamp);
    }
    return $this->_string;
  }
}