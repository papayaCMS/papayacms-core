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
namespace Papaya\Filter;

use Papaya\Filter;

/**
 * Papaya filter class that chcks if the value is an empty one
 *
 * The private typeMapping property is used to specifiy possible casts.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class NotNull implements Filter {
  /**
   * Check the value throw exception if value is not set
   *
   * @param mixed $value
   *
   * @throws Exception\IsUndefined
   *
   * @return true
   */
  public function validate($value) {
    if (NULL !== $value) {
      return TRUE;
    }
    throw new Exception\IsUndefined();
  }

  /**
   * The filter function always returns the value if it is set or NULL
   *
   * @param mixed $value
   *
   * @return mixed
   */
  public function filter($value) {
    return isset($value) ? $value : NULL;
  }
}
