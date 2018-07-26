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
/**
 * Papaya database query exception, thrown on sql errors
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Query extends \PapayaDatabaseException {

  /**
   * Sent sql query
   *
   * @var string
   */
  private $_sql;

  /**
   * Initialize exception and store values.
   *
   * @param string $message
   * @param integer $code
   * @param integer $severity
   * @param string $sql
   */
  public function __construct($message, $code = 0, $severity = NULL, $sql = '') {
    parent::__construct($message, $code, $severity);
    $this->_sql = $sql;
  }

  /**
   * Return sql query
   */
  public function getStatement() {
    return $this->_sql;
  }
}
