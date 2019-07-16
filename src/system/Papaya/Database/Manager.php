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
namespace Papaya\Database;

use Papaya\Application;

/**
 * Database connector manager
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Manager implements Application\Access {
  use Application\Access\Aggregation;

  /**
   * @var \Papaya\Configuration $_configuration Configuration object
   */
  private $_configuration;

  /**
   * @var array $_connectors list of created connectors
   */
  private $_connectors = [];

  /**
   * get current configuration object, fetch from application object if needed
   *
   * @return \Papaya\Configuration
   */
  public function getConfiguration() {
    if (!isset($this->_configuration)) {
      $this->_configuration = $this->papaya()->options;
    }
    return $this->_configuration;
  }

  /**
   * Return current configuration object
   *
   * @param \Papaya\Configuration $configuration
   */
  public function setConfiguration($configuration) {
    $this->_configuration = $configuration;
  }

  /**
   * Create an database access instance and return it.
   *
   * @param string|null $readUri URI for read connection, use options if empty
   * @param string|null $writeUri URI for write connection, use $readUri if empty
   *
   * @return Access
   */
  public function createDatabaseAccess($readUri = NULL, $writeUri = NULL) {
    $result = new Access($readUri, $writeUri);
    $result->papaya($this->papaya());
    return $result;
  }

  /**
   * Get connector for given URIs, create if none exists
   *
   * @param string|null $readUri URI for read connection, use options if empty
   * @param string|null $writeUri URI for write connection, use $readUri if empty
   *
   * @return \Papaya\Database\Connector
   */
  public function getConnector($readUri = NULL, $writeUri = NULL) {
    list($readUri, $writeUri) = $this->_getConnectorUris($readUri, $writeUri);
    $identifier = $readUri."\n".$writeUri;
    if (!isset($this->_connectors[$identifier])) {
      $connector = new \Papaya\Database\Connector($readUri, $writeUri);
      $connector->papaya($this->papaya());
      $this->_connectors[$identifier] = $connector;
    }
    return $this->_connectors[$identifier];
  }

  /**
   * Get connector for given URIs, existing connector will be overwritten
   *
   * @param \Papaya\Database\Connector $connector connector object
   */
  public function setConnector($connector) {
    $readUri = $connector->getDatabaseURI();
    $writeUri = $connector->getDatabaseURI(Connector::MODE_WRITE);
    $identifier = $readUri."\n".$writeUri;
    $this->_connectors[$identifier] = $connector;
  }

  /**
   * Get connector Uris from configuration object
   *
   * @param string $readUri
   * @param string $writeUri
   *
   * @return array
   */
  protected function _getConnectorUris($readUri = NULL, $writeUri = NULL) {
    if (NULL === $readUri) {
      $configuration = $this->getConfiguration();
      $readUri = $configuration->get('PAPAYA_DB_URI');
      $writeUri = $configuration->get('PAPAYA_DB_URI_WRITE');
    }
    if (empty($writeUri)) {
      $writeUri = $readUri;
    }
    return [
      $readUri,
      $writeUri
    ];
  }

  /**
   * Close all open connections to database servers
   */
  public function close() {
    /** @var \Papaya\Database\Connector $connector */
    foreach ($this->_connectors as $connector) {
      $connector->disconnect();
    }
  }
}
