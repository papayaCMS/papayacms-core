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
namespace Papaya\Media\Storage;

/**
 * Abstract storage service class for Papaya Media Storage
 *
 * @package Papaya-Library
 * @subpackage Media-Storage
 */
abstract class Service extends \Papaya\Application\BaseObject {
  /**
   * Constructor - set configuration if provided
   *
   * @param \Papaya\Configuration $configuration
   */
  public function __construct($configuration = NULL) {
    if (NULL !== $configuration && \is_object($configuration)) {
      $this->setConfiguration($configuration);
    }
  }

  /**
   * set configuration data from configuration object
   *
   * @param \Papaya\Configuration $configuration
   */
  abstract public function setConfiguration(\Papaya\Configuration $configuration);

  /**
   * Get a list of storage ids in a storage group
   *
   * @param string $storageGroup
   * @param string $startsWith
   *
   * @return array
   */
  abstract public function browse($storageGroup, $startsWith = '');

  /**
   * save a resource into the storage
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string|resource $content data string or resource id
   * @param string $mimeType
   * @param bool $isPublic
   *
   * @return bool
   */
  abstract public function store(
    $storageGroup, $storageId, $content, $mimeType = 'application/octet-stream', $isPublic = FALSE
  );

  /**
   * save a file into the storage
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string $filename
   * @param string $mimeType
   * @param bool $isPublic
   *
   * @return bool
   */
  abstract public function storeLocalFile(
    $storageGroup, $storageId, $filename, $mimeType = 'application/octet-stream', $isPublic = FALSE
  );

  /**
   * remove a resource from storage
   *
   * @param string $storageGroup
   * @param string $storageId
   *
   * @return bool
   */
  abstract public function remove($storageGroup, $storageId);

  /**
   * check if resource exists in storage
   *
   * @param string $storageGroup
   * @param string $storageId
   *
   * @return bool
   */
  abstract public function exists($storageGroup, $storageId);

  /**
   * Check if the configuration allows public urls with this storage handler
   *
   * @return bool
   */
  abstract public function allowPublic();

  /**
   * check if storage id is public
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string $mimeType
   *
   * @return bool $isPublic
   */
  abstract public function isPublic($storageGroup, $storageId, $mimeType);

  /**
   * set public status for storage id
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param bool $isPublic
   * @param string $mimeType
   *
   * @return bool file is now in target status
   */
  abstract public function setPublic($storageGroup, $storageId, $isPublic, $mimeType);

  /**
   * return resource content
   *
   * @param string $storageGroup
   * @param string $storageId
   *
   * @return string|null
   */
  abstract public function get($storageGroup, $storageId);

  /**
   * get public url for a storage id if possible
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string $mimeType
   *
   * @return string|null
   */
  abstract public function getURL($storageGroup, $storageId, $mimeType);

  /**
   * get local file for storage resource and temporary status.
   *
   * @param string $storageGroup
   * @param string $storageId
   *
   * @return array array('filename' => string, 'is_temporary' => boolean)
   */
  abstract public function getLocalFile($storageGroup, $storageId);

  /**
   * output resource content
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param int $rangeFrom
   * @param int $rangeTo
   * @param int $bufferSize
   */
  abstract public function output(
    $storageGroup, $storageId, $rangeFrom = 0, $rangeTo = 0, $bufferSize = 1024
  );
}
