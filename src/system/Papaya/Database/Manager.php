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
* Database connector manager
*
* @package Papaya-Library
* @subpackage Database
*/
class PapayaDatabaseManager extends PapayaObject {

  /**
  * @var PapayaConfiguration $_configuration Configuration object
  */
  private $_configuration = NULL;
  /**
  * @var array $_connectors list of created connectors
  */
  private $_connectors = array();

  /**
  * get current configuration object
  * @return \PapayaConfiguration
  */
  public function getConfiguration() {
    return $this->_configuration;
  }

  /**
  * Return current conifuration object
  * @param \PapayaConfiguration $configuration
  */
  public function setConfiguration($configuration) {
    $this->_configuration = $configuration;
  }

  /**
  * Create an database access instance and return it.
  *
  * @param object $owner
  * @param string|NULL $readUri URI for read connection, use options if empty
  * @param string|NULL $writeUri URI for write connection, use $readUri if empty
  * @return \PapayaDatabaseAccess
  */
  public function createDatabaseAccess($owner, $readUri = NULL, $writeUri = NULL) {
    $result = new \PapayaDatabaseAccess($owner, $readUri, $writeUri);
    $result->papaya($this->papaya());
    return $result;
  }

  /**
  * Get connector for given URIs, create if none exists
  * @param string|NULL $readUri URI for read connection, use options if empty
  * @param string|NULL $writeUri URI for write connection, use $readUri if empty
  * @return db_simple
  */
  public function getConnector($readUri = NULL, $writeUri = NULL) {
    list($readUri, $writeUri) = $this->_getConnectorUris($readUri, $writeUri);
    $identifier = $readUri."\n".$writeUri;
    if (!isset($this->_connectors[$identifier])) {
      $connector = new \db_simple();
      $connector->papaya($this->papaya());
      $connector->databaseURIs = array(
        'read' => $readUri,
        'write' => $writeUri
      );
      $this->_connectors[$identifier] = $connector;
    }
    return $this->_connectors[$identifier];
  }

  /**
  * Get connector for given URIs, existing connector will be overwritten
  * @param db_simple $connector connector object
  * @param string|NULL $readUri URI for read connection, use options if empty
  * @param string|NULL $writeUri URI for write connection, use $readUri if empty
  * @return db_simple
  */
  public function setConnector($connector, $readUri = NULL, $writeUri = NULL) {
    list($readUri, $writeUri) = $this->_getConnectorUris($readUri, $writeUri);
    $identifier = $readUri."\n".$writeUri;
    $this->_connectors[$identifier] = $connector;
  }

  /**
   * Get connector Uris from configuration object
   * @param string $readUri
   * @param string $writeUri
   * @return array
   */
  protected function _getConnectorUris($readUri = NULL, $writeUri = NULL) {
    if (empty($readUri)) {
      $configuration = $this->getConfiguration();
      $readUri = $configuration->get('PAPAYA_DB_URI');
      $writeUri = $configuration->get('PAPAYA_DB_URI_WRITE');
    }
    if (empty($writeUri)) {
      $writeUri = $readUri;
    }
    return array(
      $readUri,
      $writeUri
    );
  }

  /**
  * Close all open connections to database servers
  * @return void
  */
  public function close() {
    /** @var db_simple $connector */
    foreach ($this->_connectors as $connector) {
      $connector->close();
    }
  }
}
