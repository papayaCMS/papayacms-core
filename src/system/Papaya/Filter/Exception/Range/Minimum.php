<?php
/**
* This exception is thrown if a value is to small.
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
* @version $Id: Minimum.php 35649 2011-04-07 11:40:05Z Yurtsever $
*/

/**
* This exception is thrown if a value is to small.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterExceptionRangeMinimum extends PapayaFilterExceptionRange {

  /**
  * Construct object with length informations
  *
  * @param integer|float $expected
  * @param integer|float $actual
  */
  public function __construct($expected, $actual) {
    parent::__construct(
      sprintf(
        'Value is to small. Expecting a minimum of "%s", got "%s".',
        $expected,
        $actual
      ),
      $expected,
      $actual
    );
  }
}