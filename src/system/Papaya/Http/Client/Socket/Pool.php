<?php
/**
* Papaya HTTP Client Socket Pool - Manages a pool of connections (resources)
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage HTTP-Client
* @version $Id: Pool.php 39403 2014-02-27 14:25:16Z weinert $
*/


/**
* Papaya HTTP Client Socket Pool - Manages a pool of connections (resources)
*
* @package Papaya-Library
* @subpackage HTTP-Client
*/
class PapayaHttpClientSocketPool {

  /**
  * connection pool array(host => array(port => array(resource)))
  * @var array
  */
  private static $_connectionPool = array();

  /**
   * Get a connection from pool
   *
   * @param string $host
   * @param integer $port
   * @return mixed|null
   */
  public function getConnection($host, $port) {
    if (isset(self::$_connectionPool[$host]) &&
        isset(self::$_connectionPool[$host][$port]) &&
        NULL !== ($connection = array_pop(self::$_connectionPool[$host][$port]))) {
      if (feof($connection)) {
        fclose($connection);
      } else {
        return $connection;
      }
    }
    return NULL;
  }

  /**
  * Put a connection into pool for reusage
  * @param object $connection
  * @param string $host
  * @param integer $port
  */
  public function putConnection($connection, $host, $port) {
    self::$_connectionPool[$host][$port][] = $connection;
  }
}


