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
namespace Papaya\Database\Condition;

class Contains extends Element {
  /**
   * Contains constructor.
   *
   * @param Group $parent
   * @param string $field
   * @param string $value
   */
  public function __construct(
    Group $parent, $field, $value
  ) {
    $value = (string)$value;
    $hasWildcards = (FALSE !== \strpos($value, '*')) || (FALSE !== \strpos($value, '?'));
    if (!$hasWildcards) {
      $value = '*'.$value.'*';
    }
    $value = \str_replace(['%', '*', '?'], ['%%', '%', '_'], $value);
    parent::__construct($parent, $field, $value, 'LIKE');
  }
}
