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
namespace Papaya\Database\Interfaces {

  interface Connection {

    /**
     * @param \Papaya\Database\Interfaces\Statement|string $statement
     * @param int $options
     * @return mixed
     */
    public function execute($statement, $options = 0);

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
     * @return \Papaya\Database\Schema
     */
    public function schema();

    /**
     * @return \Papaya\Database\Syntax
     */
    public function syntax();

    public function isExtensionAvailable();
    public function connect();
    public function disconnect();

    public function insertRecord($table, $identifierField, $values);
    public function insertRecords($table, $values);
    public function lastInsertId($table, $idField);

    /**
     * @param string $name
     * @param callable $function
     */
    public function registerFunction($name, callable $function);
  }
}
