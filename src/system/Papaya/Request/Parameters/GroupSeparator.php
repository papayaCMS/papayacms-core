<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Request\Parameters {

  use Papaya\BaseObject\Interfaces\StringCastable;

  class GroupSeparator implements StringCastable {

    const ARRAY_SYNTAX = '[]';
    const CHARACTERS = [',', ':', '*', '!', '/'];
    const CHOICES =  [
      self::ARRAY_SYNTAX => '[] - Array Syntax', ',' => ',', ':' => ':', '*' => '*', '!' => '!', '/' => '/'
    ];

    private $_value = self::ARRAY_SYNTAX;

    public function __construct($separator = '') {
      if (self::validate($separator)) {
        $this->_value = $separator;
      }
    }

    public function __toString() {
      return $this->_value;
    }

    public static function validate($separator, $silent = FALSE) {
      if ($separator === '' || $separator === self::ARRAY_SYNTAX) {
        return TRUE; // allow empty string / array syntax
      }
      $separators = self::CHOICES;
      if (isset($separators[$separator])) {
        return TRUE;
      }
      if ($silent) {
        return FALSE;
      }
      throw new \InvalidArgumentException(
        \sprintf(
          'Invalid parameter group separator: "%s".', $separator
        )
      );
    }
  }
}


