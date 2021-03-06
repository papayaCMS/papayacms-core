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
namespace Papaya\Filter\Factory\Profile;

use Papaya\Filter;

/**
 * Profile creating filter specified by the first element in the options array using the
 * other elements of the options array as arguments.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Generator extends Filter\Factory\Profile {
  /**
   * @see \Papaya\Filter\Factory\Profile::getFilter()
   *
   * @throws \ReflectionException
   * @throws Filter\Factory\Exception\InvalidFilter
   * @throws Filter\Factory\Exception\InvalidOptions
   */
  public function getFilter() {
    $arguments = $this->options();
    if (\is_array($arguments)) {
      $name = \array_shift($arguments);
      $filterReflection = new \ReflectionClass($name);
      if ($filterReflection->isSubclassOf(Filter::class)) {
        return \call_user_func_array(
          [$filterReflection, 'newInstance'],
          $arguments
        );
      }
      throw new Filter\Factory\Exception\InvalidFilter($name);
    }
    throw new Filter\Factory\Exception\InvalidOptions(__CLASS__);
  }
}
