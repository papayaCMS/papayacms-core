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
use Papaya\Utility;

/**
 * A boolean value or callback returing a boolean value defines if caching is allowed
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Callback
  implements Cache\Identifier\Definition {
  private $_callback;

  private $_data;

  public function __construct($callback) {
    Utility\Constraints::assertCallable($callback);
    $this->_callback = $callback;
  }

  /**
   * Return cache identification data from a callback, return FALSE if it is nor cacheable
   *
   * @see \Papaya\Cache\Identifier\Definition::getStatus()
   *
   * @return array|false
   */
  public function getStatus() {
    if (NULL === $this->_data) {
      $callback = $this->_callback;
      if (!($this->_data = $callback())) {
        $this->_data = FALSE;
      }
    }
    return $this->_data ? [\get_class($this) => $this->_data] : FALSE;
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
