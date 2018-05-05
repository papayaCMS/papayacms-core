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
* This exception is thrown if a value is not considered empty.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterExceptionNotEmpty extends \PapayaFilterException {

  /**
  * Construct object with value informations
  *
  * @param mixed $actual
  */
  public function __construct($actual) {
    parent::__construct(
      sprintf(
        'Value is to not empty. Got "%s".',
        is_array($actual) ? 'array' : $actual
      )
    );
  }
}
