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

namespace Papaya\Response;

/**
 * Papaya Response Helper Object
 *
 * @package Papaya-Library
 * @subpackage Response
 */
class Helper {
  protected static $headerSent = FALSE;

  /**
   * Send http header (wrapper for php function)
   *
   * @codeCoverageIgnore
   *
   * @param string $string
   * @param bool $replace
   * @param int|null $responseCode
   */
  public function header($string, $replace = TRUE, $responseCode = NULL) {
    \header($string, $replace, $responseCode);
  }

  /**
   * Check if http headers where already sent (wrapper for php function)
   *
   * @return bool
   */
  public function headersSent() {
    if (!self::$headerSent) {
      self::$headerSent = \headers_sent();
    }
    return self::$headerSent;
  }
}
