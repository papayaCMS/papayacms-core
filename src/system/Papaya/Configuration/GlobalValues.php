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

namespace Papaya\Configuration;

/**
 * The global configuration uses constants for fixed options. Constants are superglobal, so
 * this is a global configuration.
 *
 * @package Papaya-Library
 * @subpackage Configuration
 */
class GlobalValues extends \Papaya\Configuration {
  /**
   * Check if an option value exists, the name can be an existing constant or a key of the
   * $_options array.
   *
   * @param string $name
   * @return bool
   */
  public function has($name) {
    $name = \Papaya\Utility\Text\Identifier::toUnderscoreUpper($name);
    if (\defined($name)) {
      return TRUE;
    }
    return parent::has($name);
  }

  /**
   * Use constant if defined, stored value if not.
   *
   * @param string $name
   * @param mixed $default
   * @param \Papaya\Filter $filter
   * @return null|int|bool|float|string
   */
  public function get($name, $default = NULL, \Papaya\Filter $filter = NULL) {
    $name = \Papaya\Utility\Text\Identifier::toUnderscoreUpper($name);
    if (\defined($name)) {
      return $this->filter(\constant($name), $default, $filter);
    }
    return parent::get($name, $default, $filter);
  }

  /**
   * Defines all options in the internal array as global constants. This fill make all
   * option values unchangeable in the current request.
   *
   * This is called at a point in the initialization to avoid security problems by modules that
   * change an option value.
   */
  public function defineConstants() {
    foreach ($this->_options as $option => $value) {
      if (!\defined($option) &&
        (\is_scalar($value) || NULL === $value)) {
        \define($option, $value);
      }
    }
  }
}
