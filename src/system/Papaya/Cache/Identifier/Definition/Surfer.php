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
class Surfer
  extends \PapayaObject
  implements \Papaya\Cache\Identifier\Definition {

  /**
   * Check the surfer, return the id if it valid, TRUE otherwise
   *
   * @see \Papaya\Cache\Identifier\Definition::getStatus()
   * @return array|TRUE
   */
  public function getStatus() {
    $surfer = $this->papaya()->surfer;
    if ($surfer->isValid) {
      return array(get_class($this) => $surfer->id);
    }
    return TRUE;
  }

  /**
   * The surfer is defined by request data
   *
   * @see \Papaya\Cache\Identifier\Definition::getSources()
   * @return integer
   */
  public function getSources() {
    return self::SOURCE_REQUEST;
  }
}
