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
namespace Papaya\Cache\Identifier\Definition;

use Papaya\Cache;

/**
 * Use the all values provided in the constructor as cache condition data
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Values
  implements Cache\Identifier\Definition {
  private $_values;

  /**
   * Just store all arguments into an private member variable
   *
   * @param array $values
   */
  public function __construct(...$values) {
    $this->_values = $values;
  }

  /**
   * If here are stored values return them in an array element, the key of this element is the
   * class name.
   *
   * If no arguments whre stored, return TRUE.
   *
   * @see \Papaya\Cache\Identifier\Definition::getStatus()
   *
   * @return true|array
   */
  public function getStatus() {
    return empty($this->_values) ? TRUE : [\get_class($this) => $this->_values];
  }

  /**
   * Values are from variables provided creating the object.
   *
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   *
   * @return int
   */
  public function getSources() {
    return self::SOURCE_VARIABLES;
  }
}
