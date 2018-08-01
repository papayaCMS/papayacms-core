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

namespace Papaya\Iterator\Tree\Groups;
/**
 * An iterator that group items using a regex match
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class RegEx extends \Papaya\Iterator\Tree\Groups {

  const GROUP_VALUES = 1;
  const GROUP_KEYS = 2;

  private $_pattern = '';
  private $_subMatch = 0;

  /**
   * @param array|\Traversable $traversable
   * @param string $pattern
   * @param int|string $subMatch
   * @param int $target
   */
  public function __construct($traversable, $pattern, $subMatch = 0, $target = self::GROUP_VALUES) {
    parent::__construct($traversable, array($this, 'callbackMatchGroup'));
    $this->_pattern = $pattern;
    $this->_subMatch = $subMatch;
    $this->_target = $target;
  }

  /**
   * Match element and return match, if nothing was matching return the element as group.
   *
   * @param mixed $element
   * @param int|boolean|float|string $index
   * @return NULL|string
   */
  public function callbackMatchGroup($element, $index) {
    if ($this->_target == self::GROUP_KEYS) {
      return $this->matchValue($index);
    } else {
      return $this->matchValue($element);
    }
  }

  /**
   * Get the group string from the value
   *
   * @param string $value
   * @return string|NULL
   */
  private function matchValue($value) {
    $matches = array();
    if (preg_match($this->_pattern, (string)$value, $matches) &&
      !empty($matches[$this->_subMatch])) {
      return $matches[$this->_subMatch];
    }
    return NULL;
  }
}
