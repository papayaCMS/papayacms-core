<?php
/**
* Papaya filter class that chcks if the value is not NULL or Undefined
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
* @version $Id: Isset.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Papaya filter class that chcks if the value is an empty one
*
* The private typeMapping property is used to specifiy possible casts.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterIsset implements PapayaFilter {

  /**
   * Check the value throw exception if value is not set
   *
   * @param string $value
   * @throws PapayaFilterExceptionUndefined
   * @return TRUE
   */
  public function validate($value) {
    if (isset($value)) {
      return TRUE;
    }
    throw new PapayaFilterExceptionUndefined();
  }

  /**
  * The filter function always returns the value if it is set or NULL
  *
  * @param string $value
  * @return mixed
  */
  public function filter($value) {
    return isset($value) ? $value : NULL;
  }
}