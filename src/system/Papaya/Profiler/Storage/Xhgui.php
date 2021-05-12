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
namespace Papaya\Profiler\Storage;

use Papaya\Application;
use Papaya\Database;
use Papaya\Profiler;
use Papaya\Utility;

/**
 * Stores the Xhrof profiling data into a database table for the XHGui by Paul Rheinheimer.
 *
 * https://github.com/preinheimer/xhprof/
 *
 * @package Papaya-Library
 * @subpackage Profiler
 */
class Xhgui
  implements Application\Access, Profiler\Storage, Database\Accessible {
  use Database\Accessible\Aggregation;

  /**
   * @var string
   */
  private $_database;

  /**
   * @var string
   */
  private $_tableName;

  /**
   * @var string
   */
  private $_serverId;

  /**
   * Create storage object and store configuration options
   *
   * @param string $database
   * @param string $tableName
   * @param string $serverId
   */
  public function __construct($database, $tableName, $serverId) {
    $this->_database = (string)$database;
    $this->_tableName = (string)$tableName;
    $this->_serverId = (string)$serverId;
  }

  public function getDatabase(): string {
    return $this->_database;
  }

  public function getTableName(): string {
    return $this->_tableName;
  }

  public function getServerID(): string {
    return $this->_serverId;
  }

  /**
   * Save the profiling data of a run into the database table
   *
   * @param array $data
   * @param string $type
   * @return string
   */
  public function saveRun($data, $type) {
    $databaseAccess = $this->getDatabaseAccess();
    /** @noinspection PhpComposerExtensionStubsInspection */
    $record = [
      'id' => $this->getId(), // unique id for the run
      'url' => $url = $this->removeSid(
        isset($_SERVER['REQUEST_URI'])
          ? $_SERVER['REQUEST_URI']
          : ($_SERVER['PHP_SELF'] ?? '')
      ),
      'c_url' => $this->normalizeURL($url), // normalized url to group requests
      'server name' => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '', // server name
      'perfdata' => \gzcompress(\serialize($data), 2), // profiling data
      'type' => $type, //profiling data type (group/application)
      'cookie' => \serialize($_COOKIE),
      'get' => \serialize($_GET),
      'post' => \serialize(['Skipped' => 'Post data omitted.']),
      'pmu' => isset($data['main()']['pmu']) ? $data['main()']['pmu'] : '',
      'wt' => isset($data['main()']['wt']) ? $data['main()']['wt'] : '',
      'cpu' => isset($data['main()']['cpu']) ? $data['main()']['cpu'] : '',
      'server_id' => $this->_serverId
    ];
    $sql = 'INSERT INTO %s (`'.\implode('`, `', \array_keys($record)).'`)
            VALUES ('.\substr(\str_repeat("'%s', ", \count($record)), 0, -2).')';
    $parameters = \array_values($record);
    \array_unshift($parameters, $databaseAccess->getTableName($this->_tableName, FALSE));
    $databaseAccess->queryFmtWrite($sql, $parameters);
    return $record['id'];
  }

  /**
   * create id for profiling run
   *
   * @return string
   */
  protected function getId() {
    return Utility\Random::getId();
  }

  /**
   * Remove session id, querystring and fragment from url
   *
   * @param string $url
   *
   * @return string
   */
  private function normalizeURL($url) {
    $url = \preg_replace('([?#].*$)', '', $url);
    return $url;
  }

  private function removeSid($url) {
    return \preg_replace('(//sid[a-z]*([a-zA-Z0-9,-]{20,40}))', '//', $url);
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
      $this->_databaseAccessObject = new Database\Access($this, $this->_database);
      $this->_databaseAccessObject->papaya($this->papaya());
    }
    return $this->_databaseAccessObject;
  }
}
