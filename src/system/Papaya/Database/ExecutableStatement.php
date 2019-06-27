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

  use Papaya\Database\Interfaces\Statement;

  abstract class ExecutableStatement implements Statement {

    /**
     * @var \Papaya\Database\Access
     */
    protected $_databaseAccess;

    public function __construct(Access $databaseAccess) {
      $this->_databaseAccess = $databaseAccess;
    }

    /**
     * @param bool $forceWriteConnection
     * @return FALSE|\Papaya\Database\Result
     */
    public function execute($forceWriteConnection = FALSE) {
      if ($forceWriteConnection) {
        return $this->_databaseAccess->queryWrite($forceWriteConnection);
      }
      return $this->_databaseAccess->query($forceWriteConnection);
    }

    public function __toString() {
      return $this->getSQLString();
    }
  }
}
