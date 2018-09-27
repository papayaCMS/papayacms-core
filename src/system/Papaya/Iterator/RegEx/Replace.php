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
namespace Papaya\Iterator\RegEx;

use Papaya\Iterator;
use Papaya\Utility;

/**
 * This iterator allows convert the values on request using a preg_replace().
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Replace extends Iterator\Callback {
  /**
   * Create object and store properties
   *
   * @param \Traversable $traversable
   * @param string $pattern
   * @param string $replacement
   * @param int $limit
   */
  public function __construct(\Traversable $traversable, $pattern, $replacement, $limit = -1) {
    Utility\Constraints::assertString($pattern);
    Utility\Constraints::assertString($replacement);
    Utility\Constraints::assertInteger($limit);
    parent::__construct(
      $traversable,
      function($current) use ($pattern, $replacement, $limit) {
        return \preg_replace($pattern, $replacement, $current, $limit);
      }
    );
  }
}
