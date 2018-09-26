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

use Papaya\Iterator;

/**
 * An iterator that group items using a regex match
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class RegEx extends Iterator\Tree\Groups {
  const GROUP_VALUES = 1;

  const GROUP_KEYS = 2;

  /**
   * @param array|\Traversable $traversable
   * @param string $pattern
   * @param int|string $subMatch
   * @param int $target
   */
  public function __construct($traversable, $pattern, $subMatch = 0, $target = self::GROUP_VALUES) {
    parent::__construct(
      $traversable,
      function($element, $index) use ($pattern, $subMatch, $target) {
        $value = (self::GROUP_KEYS === $target) ? $index: $element;
        $matches = [];
        if (
          \preg_match($pattern, (string)$value, $matches) &&
          isset($matches[$subMatch])
        ) {
          return $matches[$subMatch];
        }
        return NULL;
      }
    );
  }
}
