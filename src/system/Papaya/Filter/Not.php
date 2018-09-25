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
 * Abstract filter class implementing logical not, wrapping another filter.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Not implements Filter {
  /**
   * Wrapped filter
   *
   * @var Filter
   */
  protected $_filter;

  /**
   * Construct object and store subfilter
   *
   * @param Filter $filter
   */
  public function __construct(Filter $filter) {
    $this->_filter = $filter;
  }

  /**
   * Validate the input value using the defined wrapped filter object. If it matches
   * throw an exception. In result the wrapped filter is used as a negative criterion.
   *
   * @throws Exception\InvalidValue
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    try {
      $this->_filter->validate($value);
    } catch (\Exception $e) {
      return TRUE;
    }
    throw new Exception\InvalidValue($value);
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param mixed $value
   *
   * @return mixed
   */
  public function filter($value) {
    return $value;
  }
}
