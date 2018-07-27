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

namespace Papaya\Filter\Exception\RegEx;
/**
 * This exception is thrown if a value does not match a given pcre pattern.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class NoMatch extends \PapayaFilterException {

  /**
   * Pcre pattern used for validation
   *
   * @var string
   */
  private $_pattern;

  /**
   * Construct object and set (static) message.
   *
   * @param string $pattern
   */
  public function __construct($pattern) {
    $this->_pattern = $pattern;
    parent::__construct(
      sprintf(
        'Value does not match pattern "%s"',
        $pattern
      )
    );
  }

  /**
   * Get pattern for individual error messages
   *
   * @return string
   */
  public function getPattern() {
    return $this->_pattern;
  }
}
