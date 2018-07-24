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
 * Request parameters are used to create cache condition data.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Parameters
  extends \PapayaObjectInteractive
  implements \Papaya\Cache\Identifier\Definition {

  private $_names = array();

  /**
   * Provide the request parameter names, a parameter group and a method.
   *
   * @param array|string $names
   * @param string|NULL $group
   * @param int $method
   */
  public function __construct($names, $group = NULL, $method = self::METHOD_GET) {
    \PapayaUtilConstraints::assertNotEmpty($names);
    if (is_array($names) || $names instanceof \Traversable) {
      $this->_names = $names;
    } else {
      $this->_names = array($names);
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
   * @return TRUE|array
   */
  public function getStatus() {
    $data = array();
    foreach ($this->_names as $name) {
      $name = new \PapayaRequestParametersName($name);
      if ($this->parameters()->has((string)$name)) {
        $value = $this->parameters()->get((string)$name, NULL);
        $data[(string)$name] = $value;
      }
    }
    return empty($data) ? TRUE : array(get_class($this) => $data);
  }

  /**
   * The source depends on the method. If the method is GET, only valeus from the query string
   * are used - the source is URL otherwise values from the request body are used, too.
   *
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   * @return integer
   */
  public function getSources() {
    return $this->parameterMethod() == self::METHOD_GET ? self::SOURCE_URL : self::SOURCE_REQUEST;
  }
}
