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
namespace Papaya\Database\Exception;

use Papaya\Database;

/**
 * Papaya database connection exception, thrown if an error occurs during connect
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class ConnectionFailed extends Database\Exception {
  /**
   * Create exception and store values
   *
   * @param string $message
   * @param int $code
   */
  public function __construct($message, $code = 0) {
    parent::__construct($message, $code, Database\Exception::SEVERITY_ERROR);
  }
}
