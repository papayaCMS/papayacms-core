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
 * Use the all values provided in the constructor as cache condition data
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Url
  implements \Papaya\Cache\Identifier\Definition {

  /**
   * Use the current request url as cache definition parameter
   *
   * @see \Papaya\Cache\Identifier\Definition::getStatus()
   * @return TRUE|array
   */
  public function getStatus() {
    return array(get_class($this) => \Papaya\Utility\Request\Url::get());
  }

  /**
   * Values are from variables provided creating the object.
   *
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   * @return integer
   */
  public function getSources() {
    return self::SOURCE_URL;
  }
}
