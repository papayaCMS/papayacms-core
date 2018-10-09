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
 * Filter class making an encapsulated filter optional, allowing empty values
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Optional implements Filter {
  private $_innerFilter;

  private $_filter;

  /**
   * Store inner filter object
   *
   * @param Filter $filter
   */
  public function __construct(Filter $filter) {
    $this->_innerFilter = $filter;
  }

  /**
   * Return the inner filter, the condition if the value is not empty
   *
   * @return null|Filter
   */
  public function getInnerFilter() {
    return $this->_innerFilter;
  }

  /**
   * Return the combined filter allowing empty values
   *
   * @return Filter
   */
  public function getFilter() {
    if (NULL !== $this->_filter) {
      return $this->_filter;
    }
    return $this->_filter = new LogicalOr(
      $this->getInnerFilter(),
      new EmptyValue()
    );
  }

  /**
   * Validate the value using the combined filter
   *
   * @param mixed $value
   *
   * @return true
   * @throws Exception
   */
  public function validate($value) {
    return $this->getFilter()->validate($value);
  }

  /**
   * Filter the value using the combined filter
   *
   * @param mixed $value
   *
   * @return mixed
   */
  public function filter($value) {
    return $this->getFilter()->filter($value);
  }
}
