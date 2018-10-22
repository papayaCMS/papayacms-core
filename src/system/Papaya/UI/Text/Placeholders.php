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

use Papaya\UI;

/**
 * Class Papaya\UI\Text\Placeholders
 */
class Placeholders extends UI\Text {
  /**
   * Buffered/cached result string
   *
   * @var string|null
   */
  private $_string;

  /**
   * Allow to cast the object into a string, replacing the {key} placeholders in the string.
   *
   * return string
   */
  public function __toString() {
    if (NULL === $this->_string) {
      $this->_string = \preg_replace_callback(
        '(\\{(?P<key>[^}\r\n ]+)\\})u',
        function($match) {
          if (isset($match['key'], $this->_values[$match['key']])) {
            return $this->_values[$match['key']];
          }
          return '';
        },
        $this->_pattern
      );
    }
    return (string)$this->_string;
  }
}
