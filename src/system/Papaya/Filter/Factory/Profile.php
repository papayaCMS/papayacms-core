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

namespace Papaya\Filter\Factory;

/**
 * Superclass for papaya filter factory profiles, definition how a filter is created
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
abstract class Profile {
  /**
   * @var mixed
   */
  private $_options = FALSE;

  /**
   * Create and return the filter object
   *
   * @return \Papaya\Filter
   */
  abstract public function getFilter();

  /**
   * The filter options data
   *
   * @param mixed $options
   * @return mixed|null
   */
  public function options($options = NULL) {
    if (NULL !== $options) {
      $this->_options = $options;
    }
    return $this->_options;
  }
}
