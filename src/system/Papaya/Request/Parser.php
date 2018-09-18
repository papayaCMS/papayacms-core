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

namespace Papaya\Request;

/**
 * Papaya Request Parser super class
 *
 * @package Papaya-Library
 * @subpackage Request
 */
abstract class Parser extends \Papaya\Application\BaseObject {
  /**
   * parse request
   *
   * @param \Papaya\URL $url
   * @return false|array
   */
  abstract public function parse($url);

  /**
   * If a parser hast the "last" property, the loop is finished if it matches.
   *
   * @return bool
   */
  public function isLast() {
    return TRUE;
  }
}
