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
namespace Papaya\UI\Text;

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\Utility;

/**
 * Papaya Interface String Translated, a string object that will be translated before usage
 *
 * It allows to create a string object later casted to string. The basic string can
 * be a pattern (using sprintf syntax).
 *
 * Additionally the pattern will be translated into the current user language before the values are
 * inserted.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Date implements StringCastable {
  const SHOW_DATE = 0;

  const SHOW_TIME = 1;

  const SHOW_SECONDS = 2;

  /**
   * Store timestamp
   *
   * @var int
   */
  private $_timestamp;

  /**
   * @var int
   */
  private $_options;

  /**
   * @var string|null
   */
  private $_string;

  /**
   * create object and store timestamp
   *
   * @param int $timestamp
   * @param int $options
   */
  public function __construct($timestamp, $options = self::SHOW_TIME) {
    $this->_timestamp = (int)$timestamp;
    $this->_options = (int)$options;
  }

  /**
   * Allow to cast the object into a string, converting the timestamp into a string.
   *
   * return string
   */
  public function __toString() {
    $pattern = 'Y-m-d';
    if (Utility\Bitwise::inBitmask(self::SHOW_TIME, $this->_options)) {
      $pattern .= ' H:i';
      if (Utility\Bitwise::inBitmask(self::SHOW_SECONDS, $this->_options)) {
        $pattern .= ':s';
      }
    }
    if (NULL === $this->_string) {
      $this->_string = \date($pattern, $this->_timestamp);
    }
    return (string)$this->_string;
  }
}
