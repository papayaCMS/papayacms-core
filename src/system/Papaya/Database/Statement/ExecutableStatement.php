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

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Exception\ConnectionFailed;
  use Papaya\Database\Result as DatabaseResult;
  use Papaya\Database\Statement as DatabaseStatement;

  abstract class ExecutableStatement implements DatabaseStatement {

    /**
     * @var DatabaseConnection
     */
    private $_databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection) {
      $this->_databaseConnection = $databaseConnection;
    }

    /**
     * @return DatabaseConnection
     */
    public function getDatabaseConnection() {
      return $this->_databaseConnection;
    }

    /**
     * @param int $options
     * @return FALSE|int|DatabaseResult
     */
    public function execute($options = DatabaseConnection::EMPTY_OPTIONS) {
      return $this->_databaseConnection->execute($this, $options);
    }

    public function __toString() {
      try {
        return $this->getSQLString(FALSE);
      } catch (ConnectionFailed $e) {
        return '';
      }
    }
  }
}
