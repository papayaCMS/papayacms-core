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

use Papaya\Filter;

/**
 * This exception is thrown if an invalid part is encountered.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class InvalidPart extends Filter\Exception {
  /**
   * The constructor expects the number of the invalid part, its type and optionally a message
   * of the underlying exception.
   *
   * @param int $position
   * @param string $type
   * @param string $message
   */
  public function __construct($position, $type, $message = '') {
    parent::__construct(
      \sprintf(
        'Part number %d of type "%s" is invalid%s',
        $position,
        $type,
        ('' !== (string)$message) ? ': '.$message : '.'
      )
    );
  }
}
