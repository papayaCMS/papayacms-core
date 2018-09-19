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
 * Request parameters are used to create cache condition data.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Environment
  implements Cache\Identifier\Definition {
  private $_name;

  /**
   * Store the name of the environment variable
   *
   * @param string $name
   */
  public function __construct($name) {
    Utility\Constraints::assertString($name);
    Utility\Constraints::assertNotEmpty($name);
    $this->_name = $name;
  }

  /**
   * If the environment variable is empty, it is not relevant for the cache identifier so return
   * TRUE. In all other cases return the name of the variable and the value
   *
   * @return true|array
   */
  public function getStatus() {
    return empty($_SERVER[$this->_name])
      ? TRUE
      : [\get_class($this) => [$this->_name => $_SERVER[$this->_name]]];
  }

  /**
   * Any kind of data from the request environment
   *
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   * @return int
   */
  public function getSources() {
    return self::SOURCE_REQUEST;
  }
}
