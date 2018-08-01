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
* Papaya Interface String, an object representing a text for interface usage.
*
* It allows to create a string object later casted to string. The basic string can
* be a pattern (using sprintf syntax).
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiString extends \Papaya\Application\BaseObject {

  /**
  * String pattern
  * @var string
  */
  protected $_pattern = '';

  /**
  * Pattern values
  * @var array
  */
  protected $_values = array();

  /**
  * Buffered/cached result string
  * @var string
  */
  protected $_string = NULL;

  /**
  * Create object and store arguments into variables
  *
  * @param $pattern
  * @param $values
   */
  public function __construct($pattern, array $values = array()) {
    \Papaya\Utility\Constraints::assertString($pattern);
    \Papaya\Utility\Constraints::assertNotEmpty($pattern);
    $this->_pattern = $pattern;
    $this->_values = $values;
  }

  /**
  * Allow to cast the object into a string, compiling the pattern and values into a result string.
  *
  * return string
  */
  public function __toString() {
    if (is_null($this->_string)) {
      $this->_string = $this->compile($this->_pattern, $this->_values);
    }
    return $this->_string;
  }

  /**
   * Compile pattern and values into a single string
   *
   * @param string $pattern
   * @param array $values
   * @return string
   */
  protected function compile($pattern, $values) {
    if (count($values) > 0) {
      return vsprintf($pattern, $values);
    } else {
      return $pattern;
    }
  }
}
