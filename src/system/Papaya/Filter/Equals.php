<?php
/**
* Papaya filter class that chcks if the value is an empty one
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
* @subpackage Filter
* @version $Id: Equals.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Papaya filter class that chcks if the value is an empty one
*
* The private typeMapping property is used to specifiy possible casts.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterEquals implements PapayaFilter {

  /**
  * The comparsion
  * @var mixed
  */
  private $_value = TRUE;

  /**
  * Construct object, check and store options
  *
  * @param mixed $value
  */
  public function __construct($value) {
    $this->_value = $value;
  }

  /**
   * Check the value throw exception if value is not empty
   *
   * @param string $value
   * @throws PapayaFilterExceptionNotEqual
   * @return TRUE
   */
  public function validate($value) {
    if ($this->_value != $value) {
      throw new PapayaFilterExceptionNotEqual($this->_value);
    }
    return TRUE;
  }

  /**
  * The filter function always returns NULL
  *
  * @param string $value
  * @return mixed|NULL
  */
  public function filter($value) {
    if ($this->_value == $value) {
      return $this->_value;
    }
    return NULL;
  }
}