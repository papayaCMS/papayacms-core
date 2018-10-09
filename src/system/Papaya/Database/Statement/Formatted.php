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
namespace Papaya\Database\Statement {

  use Papaya\Database;

  class Formatted
    implements Database\Interfaces\Statement {
    /**
     * @var string
     */
    private $_sql;

    /**
     * @var array
     */
    private $_parameters;

    /**
     * @var \Papaya\Database\Access
     */
    private $_databaseAccess;

    /**
     * Formatted constructor.
     *
     * @param \Papaya\Database\Access $databaseAccess
     * @param $sql
     * @param array $parameters
     */
    public function __construct(Database\Access $databaseAccess, $sql, array $parameters = []) {
      $this->_databaseAccess = $databaseAccess;
      $this->_sql = $sql;
      $this->_parameters = $parameters;
    }

    /**
     * @return string
     */
    public function __toString() {
      try {
        return $this->getSQL();
      } catch (\Exception $e) {
        return '';
      }
    }

    /**
     * @return string
     */
    public function getSQL() {
      return \vsprintf(
        $this->_sql,
        \array_map(
          function($value) {
            return $this->_databaseAccess->escapeString($value);
          },
          $this->_parameters
        )
      );
    }
  }
}
