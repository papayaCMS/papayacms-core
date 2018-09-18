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

/**
 * A boolean value or callback returning a boolean value defines if caching is allowed
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class BooleanValue
  implements \Papaya\Cache\Identifier\Definition {
  private $_callback;

  private $_cacheable;

  public function __construct($condition) {
    if (\is_bool($condition)) {
      $this->_cacheable = $condition;
    } else {
      \Papaya\Utility\Constraints::assertCallable($condition);
      $this->_callback = $condition;
    }
  }

  /**
   * Return cachable status, if a callback was provided and the cacheable status not yet calculated
   * call it.
   *
   * @see \Papaya\Cache\Identifier\Definition::getStatus()
   * @return BooleanValue
   */
  public function getStatus() {
    if (NULL === $this->_cacheable) {
      $this->_cacheable = (bool)\call_user_func($this->_callback);
    }
    return $this->_cacheable;
  }

  /**
   * Values are from variables provided creating the object.
   *
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   * @return int
   */
  public function getSources() {
    return self::SOURCE_VARIABLES;
  }
}
