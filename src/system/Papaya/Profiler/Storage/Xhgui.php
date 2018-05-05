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

/**
* Stores the Xhrof profiling data into a database table for the XHGui by Paul Rheinheimer.
*
* https://github.com/preinheimer/xhprof/
*
* @package Papaya-Library
* @subpackage Profiler
*/
class PapayaProfilerStorageXhgui
  extends \PapayaObject
  implements \PapayaProfilerStorage, PapayaDatabaseInterfaceAccess {

  /**
   * @var string
   */
  private $_database = '';

  /**
   * @var string
   */
  private $_tableName = '';

  /**
   * @var string
   */
  private $_serverId = '';

  /**
   * @var PapayaDatabaseAccess
   */
  private $_databaseAccessObject = NULL;

  /**
  * Create storage object and store configuration options
  *
  * @param string $database
  * @param string $tableName
  * @param string $serverId
  */
  public function __construct($database, $tableName, $serverId) {
    $this->_database = $database;
    $this->_tableName = $tableName;
    $this->_serverId = $serverId;
  }

  /**
  * Save the profiling data of a run into the database table
  *
  * @param array $data
  * @param string $type
  */
  public function saveRun($data, $type) {
    $databaseAccess = $this->getDatabaseAccess();
    $record = array(
      'id' => $this->getId(), // unique id for the run
      'url' => $url = $this->removeSid(
        isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']
      ),
      'c_url' => $this->normalizeUrl($url), // normalized url to group requests
      'server name' => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '', // server name
      'perfdata' => gzcompress(serialize($data), 2), // profiling data
      'type' => $type, //profiling data type (group/application)
      'cookie' => serialize($_COOKIE),
      'get' => serialize($_GET),
      'post' => serialize(array("Skipped" => "Post data omitted.")),
      'pmu' => isset($data['main()']['pmu']) ? $data['main()']['pmu'] : '',
      'wt' => isset($data['main()']['wt'])  ? $data['main()']['wt']  : '',
      'cpu' => isset($data['main()']['cpu']) ? $data['main()']['cpu'] : '',
      'server_id' => $this->_serverId
    );
    $sql = "INSERT INTO %s (`".implode('`, `', array_keys($record))."`)
            VALUES (".substr(str_repeat("'%s', ", count($record)), 0, -2).")";
    $parameters = array_values($record);
    array_unshift($parameters, $databaseAccess->getTableName($this->_tableName, FALSE));
    $databaseAccess->queryFmtWrite($sql, $parameters);
    return $record['id'];
  }

  /**
  * create id for profiling run
  *
  * @return string
  */
  protected function getId() {
    return uniqid();
  }

  /**
  * Remove session id, querystring and fragment from url
  *
  * @param string $url
  * @return string
  */
  private function normalizeUrl($url) {
    $url = preg_replace('([?#].*$)', '', $url);
    return $url;
  }

  private function removeSid($url) {
    return preg_replace('(//sid[a-z]*([a-zA-Z0-9,-]{20,40}))', '//', $url);
  }

  /**
   * Set database access object
   * @param \PapayaDatabaseAccess $databaseAccessObject
   */
  public function setDatabaseAccess(\PapayaDatabaseAccess $databaseAccessObject) {
    $this->_databaseAccessObject = $databaseAccessObject;
  }

  /**
  * Get database access object
  * @return \PapayaDatabaseAccess
  */
  public function getDatabaseAccess() {
    if (!isset($this->_databaseAccessObject)) {
      $this->_databaseAccessObject = new \PapayaDatabaseAccess($this, $this->_database);
      $this->_databaseAccessObject->papaya($this->papaya());
    }
    return $this->_databaseAccessObject;
  }
}
