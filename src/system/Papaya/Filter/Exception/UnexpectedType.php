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
 * This exception is thrown to report that a the value match not a specified type.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class UnexpectedType extends \Papaya\Filter\Exception {
  /**
   * expected type description
   *
   * @var string
   */
  private $_expectedType;

  /**
   * Construct object and set expected type.
   *
   * @param string $expectedType
   */
  public function __construct($expectedType) {
    $this->_expectedType = $expectedType;
    parent::__construct(
      \sprintf(
        'Value is not a "%s".',
        $expectedType
      )
    );
  }

  /**
   * Get expected type for individual error messages
   *
   * @return string
   */
  public function getExpectedType() {
    return $this->_expectedType;
  }
}
