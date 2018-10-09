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
namespace Papaya\Database\Interfaces\Access;

use Papaya\Application;
use Papaya\Database;

trait Aggregation {
  use Application\Access\Aggregation;

  /**
   * Database read uri
   *
   * @var string|null
   */
  private $_databaseURIs = [
    'read' => NULL,
    'write' => NULL
  ];

  /**
   * Stored database access object
   *
   * @var Database\Access
   */
  protected $_databaseAccessObject;

  /**
   * @param null|string $read
   * @param null|string $write
   */
  public function setDatabaseURIs($read, $write = NULL) {
    $this->_databaseURIs = [
      'read' => $read,
      'write' => $write
    ];
  }

  /**
   * Set database access object
   *
   * @param Database\Access $databaseAccessObject
   */
  public function setDatabaseAccess(Database\Access $databaseAccessObject) {
    $this->_databaseAccessObject = $databaseAccessObject;
  }

  /**
   * Get database access object
   *
   * @return Database\Access
   */
  public function getDatabaseAccess() {
    if (NULL === $this->_databaseAccessObject) {
      $this->_databaseAccessObject = new Database\Access(
        $this, $this->_databaseURIs['read'], $this->_databaseURIs['write']
      );
      if ($this instanceof Application\Access) {
        $this->_databaseAccessObject->papaya($this->papaya());
      }
    }
    return $this->_databaseAccessObject;
  }
}
