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
namespace Papaya\BaseObject\Options;

/**
 * A superclass for options list with a definition of possible options. The possible options are
 * defined in a array with names and list of possible values.
 *
 * If no value fo the given option is found in the options array, the first value from the
 * definition property is used.
 *
 * The values have to be scalars, complex types are not allowed.
 *
 * @package Papaya-Library
 * @subpackage Objects
 */
abstract class Defined
  extends Collection {
  /**
   * Dialog option definitions: The key is the option name, the element a list of possible values.
   *
   * @var array
   */
  protected $_definitions = [];

  /**
   * Convert options into an array with name => value pairs
   *
   * @return array
   */
  public function toArray() {
    $result = [];
    foreach (\array_keys($this->_definitions) as $name) {
      $result[$name] = $this->_read($name);
    }
    return $result;
  }

  /**
   * Each option has a default value, so this method return the count of all option definitions.
   *
   * @return int
   */
  public function count() {
    return \count($this->_definitions);
  }

  /**
   * Write an option value
   *
   * @param $name
   *
   * @throws \InvalidArgumentException
   *
   * @return mixed
   */
  protected function _read($name) {
    if (\array_key_exists($name, $this->_options)) {
      return $this->_options[$name];
    } elseif (isset($this->_definitions[$name])) {
      return $this->_definitions[$name][0];
    }
    throw new \InvalidArgumentException(
      \sprintf('Unknown option name "%s".', $name)
    );
  }

  /**
   * Read an option value
   *
   * @param $name
   * @param $value
   *
   * @throws \InvalidArgumentException
   */
  protected function _write($name, $value) {
    if (isset($this->_definitions[$name])) {
      $this->_options[$name] = $value;
      return;
    }
    throw new \InvalidArgumentException(
      \sprintf('Unknown option name "%s".', $name)
    );
  }

  /**
   * Check if an option value exists
   *
   * @param $name
   *
   * @return bool
   */
  protected function _exists($name) {
    return isset($this->_definitions[$name]);
  }
}
