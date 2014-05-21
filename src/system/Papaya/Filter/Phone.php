<?php
/**
* Papaya filter class for phone numbers.
*
* @copyright 2011-2012 by papaya Software GmbH - All rights reserved.
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
* @subpackage Filter
* @version $Id: Phone.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* This filter class checks a phone number.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterPhone implements PapayaFilter {

  /**
  * Pattern to check for a linebreak
  * @var string
  */
  private $_patternCheck = '
    (^
      (((\+|00)\d{2}|\(0\d+\))[-\s]?)?
      (\d+[-\s]?)*
      \d+
    $)Dux';

  /**
   * Check the value if it's a valid phone number, if not throw an exception.
   *
   * @param string $value
   * @throws PapayaFilterExceptionType
   * @return TRUE
   */
  public function validate($value) {
    if (!preg_match($this->_patternCheck, $value)) {
      throw new PapayaFilterExceptionType('phone');
    }
    return TRUE;
  }

  /**
  * The filter function is used to read an input value if it is valid.
  *
  * @param string $value
  * @return string
  */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (PapayaFilterException $e) {
      return NULL;
    }
  }
}