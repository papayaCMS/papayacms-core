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
namespace Papaya\UI\Option;

/**
 * Generic definition and handling for alignment attribute values.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Align {
  /**
   * Alignment left
   *
   * @var int
   */
  const LEFT = 0;

  /**
   * Alignment center
   *
   * @var int
   */
  const CENTER = 1;

  /**
   * Alignment right
   *
   * @var int
   */
  const RIGHT = 2;

  /**
   * string representation of the column alignment, used in the xml attributes
   *
   * @var array
   */
  private static $_alignAttributes = [
    self::LEFT => 'left',
    self::CENTER => 'center',
    self::RIGHT => 'right'
  ];

  /**
   * Get the string representation of the alignment value for a xml attribute. If the align
   * argument is invalid it will return "left".
   *
   * @param int $align
   *
   * @return string
   */
  public static function getString($align) {
    if (isset(self::$_alignAttributes[$align])) {
      return self::$_alignAttributes[$align];
    } else {
      return self::$_alignAttributes[self::LEFT];
    }
  }

  /**
   * Validate an alignment value. This will throw an exception if the argument is invalid.
   * An individual message for the exception can be provided, too.
   *
   * @throws \InvalidArgumentException
   *
   * @param int $align
   * @param string $message
   *
   * @return true
   */
  public static function validate($align, $message = NULL) {
    if (isset(self::$_alignAttributes[$align])) {
      return TRUE;
    } elseif (isset($message)) {
      throw new \InvalidArgumentException($message);
    } else {
      throw new \InvalidArgumentException(
        \sprintf('InvalidArgumentException: Invalid align value "%d".', $align)
      );
    }
  }
}
