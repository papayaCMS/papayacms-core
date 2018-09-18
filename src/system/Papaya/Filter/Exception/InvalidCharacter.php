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
 * This exception is thrown if an invalid character is found in the given input
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class InvalidCharacter extends \Papaya\Filter\Exception {
  /**
   * Position of invalid character
   *
   * @var int
   */
  private $_characterPosition = 0;

  /**
   * Initialize object, store character position and generate error message.
   *
   * @param string $value
   * @param int $offset
   */
  public function __construct($value, $offset) {
    $this->_characterPosition = $offset;
    if (\strlen($value) > 50) {
      if ($offset > 50) {
        $from = $offset - 50;
        $length = 50;
      } else {
        $from = 0;
        $length = $offset;
      }
      parent::__construct(
        \sprintf(
          'Invalid character at offset #%d near "%s".',
          $offset,
          \substr($value, $from, $length)
        )
      );
    } else {
      parent::__construct(
        \sprintf(
          'Invalid character in value "%s" at offset #%d.',
          $value,
          $offset
        )
      );
    }
  }

  /**
   * Return the character position
   *
   * @return int
   */
  public function getCharacterPosition() {
    return $this->_characterPosition;
  }
}
