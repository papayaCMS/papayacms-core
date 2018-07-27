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
/**
 * Abstract filter class implementing logical links between other Filters
 *
 * You can create this class with two or more subfilters classes, these filters are linked
 * depending on the concrete implementation of the child classes.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
abstract class Logical implements \Papaya\Filter {

  /**
   * Filter list
   *
   * @var array(\Papaya\PapayaFilter)
   */
  protected $_filters = array();

  /**
   * Construct object and initialize subfilter objects
   *
   * The constructor needs at least two filters
   *
   * @internal param \Papaya\PapayaFilter $filterOne
   * @internal param \Papaya\PapayaFilter $filterTwo
   * @throws \InvalidArgumentException
   */
  public function __construct() {
    $this->_setFilters(func_get_args());
  }

  /**
   * Check subfilters and save them in a protected property
   *
   * @param array(\Papaya\PapayaFilter) $filters
   * @throws \InvalidArgumentException
   * @return void
   */
  protected function _setFilters($filters) {
    if (is_array($filters) &&
      count($filters) > 1) {
      foreach ($filters as $filter) {
        if ($filter instanceof \Papaya\Filter) {
          $this->_filters[] = $filter;
        } elseif (is_scalar($filter)) {
          $this->_filters[] = new \Papaya\Filter\Equals($filter);
        } else {
          throw new \InvalidArgumentException(
            sprintf(
              'Only %1$s classes expected: "%2$s" found.',
              \Papaya\Filter::class,
              is_object($filter) ? get_class($filter) : gettype($filter)
            )
          );
        }
      }
    } else {
      throw new \InvalidArgumentException(
        sprintf(
          '%1$s needs at least two other %2$s classes.',
          static::class,
          \Papaya\Filter::class
        )
      );
    }
  }
}
