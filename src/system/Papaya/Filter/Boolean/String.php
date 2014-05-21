<?php
/**
* Papaya filter that interprets an string as boolean value, mapping several string and
* casting others.
*
* @copyright 2013 by papaya Software GmbH - All rights reserved.
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
* @version $Id: String.php 39172 2014-02-10 18:33:44Z weinert $
*/

/**
* Papaya filter that interprets an string as boolean value, mapping several string and
* casting others.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterBooleanString
  implements PapayaFilter {

  private $_mapping = array(
    '+' => TRUE,
    'y' => TRUE,
    'yes' => TRUE,
    't' => TRUE,
    'true' => TRUE,
    '1' => TRUE,
    '-' => FALSE,
    'n' => FALSE,
    'no' => FALSE,
    'f' => FALSE,
    'false' => FALSE,
    '0' => FALSE
  );

  private $_castEmptyString = FALSE;

  /**
   * Create the filter and store options. $castEmptyString defines if an empty string
   * (or one consisting only of whitespaces) will bee casted to boolean FALSE. If set FALSE the
   * empty string will be considered as empty/missing value trowing an exception in validate()
   * and return NULL in filter()
   *
   * @param bool $castEmptyString
   */
  public function __construct($castEmptyString = TRUE) {
    $this->_castEmptyString = $castEmptyString;
  }

  /**
   * Validate the given value and return TRUE, throw and exception if it is empty.
   *
   * @param mixed $value
   * @throws PapayaFilterExceptionEmpty
   * @return TRUE
   */
  public function validate($value) {
    if (NULL === $this->filter($value)) {
      throw new PapayaFilterExceptionEmpty();
    }
    return TRUE;
  }

  /**
   * Filter the given value and return it as an boolean. An empty string can be condidered as FALSE
   * or a missing value. This is depending on the $castEmptyString constructor argument.
   *
   * @param mixed $value
   * @return boolean|NULL
   */
  public function filter($value) {
    if (!isset($value)) {
      return NULL;
    } elseif (is_bool($value)) {
      return $value;
    } elseif (is_integer($value)) {
      return (boolean)$value;
    }
    $normalized = trim(strtoLower($value));
    if (!$this->_castEmptyString && $normalized === '') {
      return NULL;
    }
    if (isset($this->_mapping[$normalized])) {
      return $this->_mapping[$normalized];
    }
    return (boolean)$normalized;
  }
}