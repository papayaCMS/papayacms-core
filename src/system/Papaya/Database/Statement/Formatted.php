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

  use Papaya\Database\Connection;

  class Formatted
    extends ExecutableStatement {
    /**
     * @var string
     */
    private $_sql;

    /**
     * @var array
     */
    private $_parameters;

    /**
     * Formatted constructor.
     *
     * @param \Papaya\Database\Connection $databaseConnection
     * @param $sql
     * @param array $parameters
     */
    public function __construct(Connection $databaseConnection, $sql, array $parameters = []) {
      parent::__construct($databaseConnection);
      $this->_sql = $sql;
      $this->_parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getSQLString() {
      return \vsprintf(
        $this->_sql,
        \array_map(
          function($value) {
            return $this->getDatabaseConnection()->escapeString($value);
          },
          $this->_parameters
        )
      );
    }

    /**
     * @return array
     */
    public function getSQLParameters() {
      return [];
    }
  }
}
