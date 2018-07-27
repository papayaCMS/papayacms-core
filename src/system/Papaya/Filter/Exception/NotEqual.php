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

namespace Papaya\Filter\Exception;
/**
 * This exception is thrown if a value is not equal to a given comparsion value.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class NotEqual extends \PapayaFilterException {

  /**
   * Construct object with value informations
   *
   * @param mixed $expected
   */
  public function __construct($expected) {
    parent::__construct(
      sprintf(
        'Value does not equal comparsion value. Expected "%s".',
        $expected
      )
    );
  }
}
