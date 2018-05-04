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
* A length exception is thrown if a certain length is expected and another if found
*
* In other words if a value is to short or to long
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterExceptionInvalid extends PapayaFilterException {

  /**
  * The actual value
  * @var string
  */
  private $_actualValue = 0;

  /**
  * Construct object, set message and length informations
  *
  * @param integer $actual
  */
  public function __construct($actual) {
    $this->_actualValue = $actual;
    parent::__construct(sprintf('Invalid value "%s".', $actual));
  }

  /**
  * Read private actual value property
  *
  * @return integer
  */
  public function getActualValue() {
    return $this->_actualValue;
  }
}
