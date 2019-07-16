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

namespace Papaya\Database {

  interface Connection {

    const EMPTY_OPTIONS = 0;
    const REQUIRE_ABSOLUTE_COUNT = 1;
    const DISABLE_RESULT_CLEANUP = 2;
    const USE_WRITE_CONNECTION = 4;

    /**
     * @param string $sql
     * @return \Papaya\Database\Statement\Prepared
     */
    public function prepare($sql);

    /**
     * @param \Papaya\Database\Statement|string $statement
     * @param int $options
     * @return mixed
     */
    public function execute($statement, $options = self::EMPTY_OPTIONS);

    /**
     * @param string $literal
     * @return string
     */
    public function escapeString($literal);

    /**
     * @param string $literal
     * @return string
     */
    public function quoteString($literal);

    /**
     * @param string $name
     * @return string
     */
    public function quoteIdentifier($name);

    /**
     * @param string $name
     * @param bool $usePrefix
     * @return string
     */
    public function getTableName($name, $usePrefix = FALSE);

    /**
     * @return \Papaya\Database\Schema
     */
    public function schema();

    /**
     * @return \Papaya\Database\Syntax
     */
    public function syntax();

    /**
     * @return bool
     */
    public function isExtensionAvailable();

    /**
     * @return self
     */
    public function connect();

    public function disconnect();

    public function insert($tableName, array $values);

    public function lastInsertId($tableName, $idField);

    /**
     * @param string $name
     * @param callable $function
     */
    public function registerFunction(
      $name, callable $function
    );
  }

  if (!defined('DB_FETCHMODE_DEFAULT')) {
    define('DB_FETCHMODE_DEFAULT', Result::FETCH_BOTH);
  }
  if (!defined('DB_FETCHMODE_ORDERED')) {
    define('DB_FETCHMODE_ORDERED', Result::FETCH_ORDERED);
  }
  if (!defined('DB_FETCHMODE_ASSOC')) {
    define('DB_FETCHMODE_ASSOC', Result::FETCH_ASSOC);
  }
}
