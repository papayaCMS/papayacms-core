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

namespace Papaya\Cache\Identifier\Definition\Session;

use Papaya\Application;
use Papaya\Cache;

/**
 * Request parameters are used to create cache condition data.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Parameters
  implements Application\Access, Cache\Identifier\Definition {
  use Application\Access\Aggregation;

  /**
   * @var array
   */
  private $_identifiers;

  /**
   * Provide the request parameter names, a parameter group and a method.
   *
   * @param mixed ...$identifiers multiple identifiers possible
   */
  public function __construct(...$identifiers) {
    $this->_identifiers = $identifiers;
  }

  /**
   * Read request parameters into an condition data array. Prefix it with the class name and
   * return it.
   *
   * If a parameter does not exist in the request, it will not be added to the condition data,
   * if none of the specified parameters exists the result will be TRUE.
   *
   * @return true|array
   */
  public function getStatus() {
    $data = [];
    if ($this->papaya()->session && $this->papaya()->session->isActive()) {
      /** @var \Papaya\Session\Values $values */
      $values = $this->papaya()->session->values();
      foreach ($this->_identifiers as $identifier) {
        $key = $values->getKey($identifier);
        if (NULL !== ($value = $values[$key])) {
          $data[$key] = $value;
        }
      }
    }
    return empty($data) ? TRUE : [\get_class($this) => $data];
  }

  /**
   * Any kind of data from the session
   *
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   * @return int
   */
  public function getSources() {
    return self::SOURCE_SESSION;
  }
}
