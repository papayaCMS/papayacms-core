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
* A command condition based on a value.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiControlCommandConditionValue extends PapayaUiControlCommandCondition {

  /**
  * member variable to store the value
  *
  * @var boolean
  */
  private $_value = NULL;

  /**
   * Create object and store callback.
   *
   * @param boolean $value
   * @throws \InvalidArgumentException
   */
  public function __construct($value) {
    $this->_value = (boolean)$value;
  }

  /**
  * Execute callback and return value.
  *
  * @return boolean
  */
  public function validate() {
    return $this->_value;
  }
}
