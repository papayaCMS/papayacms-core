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

use Papaya\Request;
use Papaya\Utility;

/**
 * Request parameters are used to create cache condition data.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Parameters
  implements Request\Parameters\Access, \Papaya\Cache\Identifier\Definition {
  use Request\Parameters\Access\Integration;

  private $_names = [];

  /**
   * Provide the request parameter names, a parameter group and a method.
   *
   * @param array|string $names
   * @param string|null $group
   * @param int $method
   */
  public function __construct($names, $group = NULL, $method = self::METHOD_GET) {
    Utility\Constraints::assertNotEmpty($names);
    if (\is_array($names) || $names instanceof \Traversable) {
      $this->_names = $names;
    } else {
      $this->_names = [$names];
    }
    $this->parameterGroup($group);
    $this->parameterMethod($method);
  }

  /**
   * Read request parameters into an condition data array. Prefix it with the class name and
   * return it.
   *
   * If a paramter does not exist in the request, it will not be added to the condition data,
   * if none of the specified parameters exists the result will be TRUE.
   *
   * @return true|array
   */
  public function getStatus() {
    $data = [];
    foreach ($this->_names as $name) {
      $name = new Request\Parameters\Name($name);
      if ($this->parameters()->has((string)$name)) {
        $value = $this->parameters()->get((string)$name, NULL);
        $data[(string)$name] = $value;
      }
    }
    return empty($data) ? TRUE : [\get_class($this) => $data];
  }

  /**
   * The source depends on the method. If the method is GET, only values from the query string
   * are used - the source is URL otherwise values from the request body are used, too.
   *
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   *
   * @return int
   */
  public function getSources() {
    return self::METHOD_GET === $this->parameterMethod() ? self::SOURCE_URL : self::SOURCE_REQUEST;
  }
}
