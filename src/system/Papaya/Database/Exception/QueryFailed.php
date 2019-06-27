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
 * Papaya database query exception, thrown on sql errors
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class QueryFailed extends Database\Exception {
  /**
   * Sent sql query
   *
   * @var string|\Papaya\Database\Interfaces\Statement
   */
  private $_statement;

  /**
   * Initialize exception and store values.
   *
   * @param string $message
   * @param int $code
   * @param int $severity
   * @param string|\Papaya\Database\Interfaces\Statement $statement
   */
  public function __construct($message, $code = 0, $severity = NULL, $statement = '') {
    parent::__construct($message, $code, $severity);
    $this->_statement = $statement;
  }

  /**
   * Return sql query
   */
  public function getStatement() {
    return (string)$this->_statement;
  }
}
