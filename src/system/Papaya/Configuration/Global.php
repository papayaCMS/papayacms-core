<?php
/**
* The global configuraiton uses constants for fixed options. Constants are superglobal, so
* this is a global configuration.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Configuration
* @version $Id: Global.php 39404 2014-02-27 14:55:43Z weinert $
*/

/**
* The global configuraiton uses constants for fixed options. Constants are superglobal, so
* this is a global configuration.
*
* @package Papaya-Library
* @subpackage Configuration
*/
class PapayaConfigurationGlobal extends PapayaConfiguration {

  /**
   * Check if an option value exists, the name can be an existing constant or a key of the
   * $_options array.
   *
   * @param string $name
   * @return bool
   */
  public function has($name) {
    $name = PapayaUtilStringIdentifier::toUnderscoreUpper($name);
    if (defined($name)) {
      return TRUE;
    }
    return parent::has($name, $this->_options);
  }

  /**
   * Use constant if defined, stored value if not.
   *
   * @param string $name
   * @param mixed $default
   * @param PapayaFilter $filter
   * @return NULL|int|boolean|float|string
   */
  public function get($name, $default = NULL, PapayaFilter $filter = NULL) {
    $name = PapayaUtilStringIdentifier::toUnderscoreUpper($name);
    if (defined($name)) {
      return $this->filter(constant($name), $default, $filter);
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
      if (!defined($option) &&
          (is_scalar($value) || is_null($value))) {
        define($option, $value);
      }
    }
  }

}