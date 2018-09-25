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
namespace Papaya\Filter\Exception\OutOfRange;

use Papaya\Filter;

/**
 * This exception is thrown if a value is to small.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class ToSmall extends Filter\Exception\OutOfRange {
  /**
   * Construct object with length informations
   *
   * @param int|float $expected
   * @param int|float $actual
   */
  public function __construct($expected, $actual) {
    parent::__construct(
      \sprintf(
        'Value is to small. Expecting a minimum of "%s", got "%s".',
        $expected,
        $actual
      ),
      $expected,
      $actual
    );
  }
}
