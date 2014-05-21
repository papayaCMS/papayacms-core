<?php
/**
* This exception is thrown if the number of elements differ from the expected number.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Mismatch.php 35397 2011-02-04 15:52:49Z rekowski $
*/

/**
* This exception is thrown if the number of elements differ from the expected number.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterExceptionCountMismatch extends PapayaFilterException {

  /**
  * The constructor expects the expected element count, the actual number and the element type.
  *
  * @param integer $expected
  * @param integer $actual
  * @param string $type
  */
  public function __construct($expected, $actual, $type) {
    parent::__construct(
      sprintf(
        '%d element(s) of type "%s" expected, %d found.',
        $expected,
        $type,
        $actual
      )
    );
  }
}