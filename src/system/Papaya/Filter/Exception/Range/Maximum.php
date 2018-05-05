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
* This exception is thrown if a value is to small.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterExceptionRangeMaximum extends \PapayaFilterExceptionRange {

  /**
  * Construct object with length informations
  *
  * @param integer|float $expected
  * @param integer|float $actual
  */
  public function __construct($expected, $actual) {
    parent::__construct(
      sprintf(
        'Value is to large. Expecting a maximum of "%s", got "%s".',
        $expected,
        $actual
      ),
      $expected,
      $actual
    );
  }
}
