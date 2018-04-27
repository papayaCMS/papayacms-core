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
* Amazon S3 based storage service for Papaya Media Storage
*
* @package Papaya-Library
* @subpackage Media-Storage
*/
class PapayaMediaStorageServiceS3 extends PapayaMediaStorageService {

  /**
  * Amazon S3 bucket name
  * @var string
  */
  private $_storageBucket = '';

  /**
  * base storage directory - will contain subdirectories for each storage group
  * @var string $_storageDirectory
  */
  private $_storageDirectory = '';

  /**
  * subdirectory levels to avoid to many files in one directory
  * @var integer $_storageDirectoryDepth
  */
  private $_storageDirectoryDepth = 1;

  /**
  * how long is the status cache valid (in seconds)
  * @var integer $_storageCacheExpire
  */
  private $_storageCacheExpire = 86400;

  /**
  * handler object
  * @var PapayaMediaStorageServiceS3Handler
  */
  private $_handler;

  /**
   * @var PapayaCacheService cache for meta information
   */
  private $_cacheService;

  /**
   * Name for the cache group (public status information)
   *
   * @var string
   */
  private $_statusCacheName = 'mediastatus';

  /**
   * Set the storage configuration values.
   *
   * @param PapayaConfiguration $configuration
   * @return void
   */
  public function setConfiguration($configuration) {
    $this->_storageBucket = $configuration->get(
      'PAPAYA_MEDIA_STORAGE_S3_BUCKET', $this->_storageBucket
    );

    $this->_handler = new PapayaMediaStorageServiceS3Handler($configuration);

    $this->_storageDirectory = $configuration->get(
      'PAPAYA_MEDIA_STORAGE_SUBDIRECTORY', $this->_storageDirectory
    );
    if (!empty($this->_storageDirectory)) {
      $lastChar = substr($this->_storageDirectory, -1);
      if ('/' !== $lastChar) {
        $this->_storageDirectory .= '/';
      }
    }
    $this->_storageDirectoryDepth = $configuration->get(
      'PAPAYA_MEDIA_STORAGE_DIRECTORY_DEPTH', $this->_storageDirectoryDepth
    );
    $this->_storageCacheExpire = $configuration->get(
      'PAPAYA_MEDIA_STORAGE_CACHE_EXPIRE', $this->_storageCacheExpire
    );
  }

  public function cache(PapayaCacheService $service = NULL) {
    if (NULL !== $service) {
      $this->_cacheService = $service;
    } elseif (NULL === $this->_cacheService) {
      $this->_cacheService = PapayaCache::get(PapayaCache::DATA, $this->papaya()->options);
    }
    return $this->_cacheService;
  }

  /**
  * Set the used HTTP client object.
  *
  * @param object PapayaHttpClient $client
  * @return void
  */
  public function setHTTPClient($client) {
    $this->_handler->setHTTPClient($client);
  }

  /**
  * Get storage bucket url
  * @return string
  */
  private function _getBucketUrl() {
    return 'http://'.$this->_storageBucket.'.s3.amazonaws.com';
  }

  /**
  * Get Storage object path depending on given the directory depth
  * @param string $storageGroup
  * @param string $storageId
  * @return string
  */
  private function _getStorageObject($storageGroup, $storageId) {
    $result = $this->_storageDirectory.$storageGroup;
    for ($i = $this->_storageDirectoryDepth, $offset = 0; $i > 0; $i--, $offset++) {
      $result .= '/'.substr($storageId, $offset, 1);
    }
    return $result.'/'.$storageId;
  }

  /**
  * Set the used handler object.
  *
  * @param PapayaMediaStorageServiceS3Handler $handler
  * @return void
  */
  public function setHandler(PapayaMediaStorageServiceS3Handler $handler) {
    $this->_handler = $handler;
  }

  /**
  * Get response xml and create xpath object
  *
  * @param PapayaHttpClient $client
  * @return DOMXPath
  */
  private function _doXmlRequest(PapayaHttpClient $client) {
    $client->send();
    $dom = new DOMDocument('1.0', 'UTF-8');
    if (200 === $client->getResponseStatus()) {
      $xml = $client->getResponseData();
      $dom->loadXML($xml);
    }
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('aws', 'http://s3.amazonaws.com/doc/2006-03-01/');
    return $xpath;
  }

  /**
  * Get a list of resource ids in a storage group
  *
  * @param string $storageGroup
  * @param string $startsWith
  * @return array
  */
  public function browse($storageGroup, $startsWith = '') {
    $result = array();
    $client = $this->_handler->setUpRequest(
      $this->_getBucketUrl(),
      'GET',
      array(
        'prefix' => $storageGroup.'/'.$startsWith
      )
    );
    $response = $this->_doXmlRequest($client);
    $offset = strlen($storageGroup) + 1;
    /** @noinspection ForeachSourceInspection */
    foreach ($response->evaluate('//aws:Key') as $file) {
      $result[] = substr($file->nodeValue, $offset);
    }
    return $result;
  }

  /**
  * return resource content
  *
  * @param string $storageGroup
  * @param string $storageId
  * @return string | NULL
  */
  public function get($storageGroup, $storageId) {
    $client = $this->_handler->setUpRequest(
      $this->_getBucketUrl().'/'.$this->_getStorageObject($storageGroup, $storageId)
    );
    $client->send();
    if (200 === $client->getResponseStatus()) {
      return $client->getResponseData();
    }
    return NULL;
  }

  /**
  * get public url for a storage id if possible
  *
  * @param string $storageGroup
  * @param string $storageId
  * @param string $mimeType
  * @return string | NULL
  */
  public function getURL($storageGroup, $storageId, $mimeType) {
    if ($this->isPublic($storageGroup, $storageId, $mimeType)) {
      return $this->_getBucketUrl().'/'.$this->_getStorageObject($storageGroup, $storageId);
    }
    return NULL;
  }

  /**
  * get local file for storage resource and temporary status.
  *
  * @param string $storageGroup
  * @param string $storageId
  * @return array|FALSE array('filename' => string, 'is_temporary' => boolean)
  */
  public function getLocalFile($storageGroup, $storageId) {
    $tempDirectory = (0 === strpos(PHP_OS, 'WIN')) ? 'c:\tmp' :  '/tmp';
    if (function_exists('sys_get_temp_dir')) {
      $tempDirectory = sys_get_temp_dir();
    }
    $localFile = tempnam($tempDirectory, 'papayaMedia');
    if ($fh = fopen($localFile, 'wb')) {
      $client = $this->_handler->setUpRequest(
        $this->_getBucketUrl().'/'.$this->_getStorageObject($storageGroup, $storageId)
      );
      $client->send();
      if (200 === $client->getResponseStatus()) {
        $socket = $client->getSocket();
        while (!$socket->eof()) {
          fwrite($fh, $socket->read());
        }
        fclose($fh);
        return array(
          'filename' => $localFile,
          'is_temporary' => TRUE
        );
      }
    }
    return FALSE;
  }

  /**
  * output resource content
  *
  * @param string $storageGroup
  * @param string $storageId
  * @param integer $rangeFrom
  * @param integer $rangeTo
  * @param integer $bufferSize
  * @return void
  */
  public function output(
    $storageGroup, $storageId, $rangeFrom = 0, $rangeTo = 0, $bufferSize = 2048
  ) {
    if ($rangeFrom > 0 && $rangeTo > 0) {
      $headers = array(
        'Range' => 'bytes='.$rangeFrom.'-'.$rangeTo
      );
    } elseif ($rangeFrom) {
      $headers = array(
        'Range' => 'bytes='.$rangeFrom.'-'
      );
    } else {
      $headers = array();
    }
    $client = $this->_handler->setUpRequest(
      $this->_getBucketUrl().'/'.$this->_getStorageObject($storageGroup, $storageId),
      'GET',
      array(),
      $headers
    );
    $client->send();
    $code = $client->getResponseStatus();
    if (200 === $code || 206 === $code) {
      $socket = $client->getSocket();
      while (!$socket->eof()) {
        echo $socket->read($bufferSize);
      }
    }
  }

  /**
  * Save object into storage
  *
  * @access private
  * @param string $storageGroup
  * @param string $storageId
  * @param object PapayaHttpClientFile $resource
  * @param string $mimeType
  * @param boolean $isPublic
  * @return boolean
  */
  private function _storeResource(
    $storageGroup, $storageId, $resource, $mimeType, $isPublic
  ) {
    $headers = array(
      'Content-Type' => $mimeType,
      'x-amz-acl' => $isPublic ? 'public-read' : 'private'
    );
    $client = $this->_handler->setUpRequest(
      $this->_getBucketUrl().'/'.$this->_getStorageObject($storageGroup, $storageId),
      'PUT',
      array(),
      $headers
    );
    $client->addRequestFile($resource);
    $client->send();
    return (200 === $client->getResponseStatus());
  }

  /**
   * save a resource into the storage
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param mixed $content data string or resource id
   * @param string $mimeType
   * @param boolean $isPublic
   * @return boolean
   * @throws \InvalidArgumentException
   */
  public function store(
    $storageGroup,
    $storageId,
    $content,
    $mimeType = 'application/octet-stream',
    $isPublic = FALSE
  ) {
    if (is_resource($content)) {
      $resource = new PapayaHttpClientFileResource('filedata', 'file.dat', $content, $mimeType);
    } else {
      $resource = new PapayaHttpClientFileString('filedata', 'file.dat', $content, $mimeType);
    }
    return $this->_storeResource($storageGroup, $storageId, $resource, $mimeType, $isPublic);
  }

  /**
   * save a file into the storage
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string $filename
   * @param string $mimeType
   * @param boolean $isPublic
   * @return boolean
   * @throws \LogicException
   */
  public function storeLocalFile(
    $storageGroup, $storageId, $filename, $mimeType = 'application/octet-stream', $isPublic = FALSE
  ) {
    $resource = new PapayaHttpClientFileName('filedata', $filename, $mimeType);
    return $this->_storeResource($storageGroup, $storageId, $resource, $mimeType, $isPublic);
  }

  /**
  * remove a resource from storage
  *
  * @param string $storageGroup
  * @param string $storageId
  * @return boolean
  */
  public function remove($storageGroup, $storageId) {
    $client = $this->_handler->setUpRequest(
      $this->_getBucketUrl().'/'.$this->_getStorageObject($storageGroup, $storageId),
      'DELETE'
    );
    $client->send();
    return (204 === $client->getResponseStatus());
  }

  /**
  * check if resource exists in storage
  *
  * @param string $storageGroup
  * @param string $storageId
  * @return boolean
  */
  public function exists($storageGroup, $storageId) {
    $client = $this->_handler->setUpRequest(
      $this->_getBucketUrl().'/'.$this->_getStorageObject($storageGroup, $storageId),
      'HEAD'
    );
    $client->send();
    return (200 === $client->getResponseStatus());
  }

  /**
   * S3 generally supports public files
   *
   * @return bool
   */
  public function allowPublic() {
    return TRUE;
  }

  /**
  * check if storage id is public
  *
  * @param string $storageGroup
  * @param string $storageId
  * @param string $mimeType
  * @return boolean $isPublic
  */
  public function isPublic($storageGroup, $storageId, $mimeType) {
    $cacheParameters = array($storageId, $mimeType);
    if (
      ($cache = $this->cache()) &&
      (
        $status = $cache->read(
          $this->_statusCacheName, $storageGroup, $cacheParameters, $this->_storageCacheExpire
        )
      )
    ) {
      return 'public' === $status;
    }
    $client = $this->_handler->setUpRequest(
      $this->_getBucketUrl().'/'.$this->_getStorageObject($storageGroup, $storageId),
      'HEAD'
    );
    $client->send();
    if (200 !== $client->getResponseStatus()) {
      return FALSE;
    }
    if ($mimeType !== $client->getResponseHeader('Content-Type')) {
      return FALSE;
    }
    $client = $this->_handler->setUpRequest(
      $this->_getBucketUrl().'/'.$this->_getStorageObject($storageGroup, $storageId).'?acl',
      'GET'
    );
    $response = $this->_doXmlRequest($client);
    $userPattern = 'aws:Grantee/aws:URI/text() = "http://acs.amazonaws.com/groups/global/AllUsers"';
    $permissionPattern = 'string(//aws:Grant['.$userPattern.']/aws:Permission/text())';
    $permission = $response->evaluate($permissionPattern);
    $isPublic = ('READ' === $permission || 'FULL_CONTROL' === $permission);
    if ($cache) {
      $cache->write(
        'mediastatus',
        $storageGroup,
        $cacheParameters,
        $isPublic ? 'public' : 'private',
        $this->_storageCacheExpire
      );
    }
    return $isPublic;
  }

  /**
  * set public status for storage id
  *
  * @param string $storageGroup
  * @param string $storageId
  * @param boolean $isPublic
  * @param string $mimeType
  * @return boolean file is now in target status
  */
  public function setPublic($storageGroup, $storageId, $isPublic, $mimeType) {
    $client = $this->_handler->setUpRequest(
      $this->_getBucketUrl().'/'.$this->_getStorageObject($storageGroup, $storageId),
      'PUT',
      array(),
      array(
        'x-amz-acl' => $isPublic ? 'public-read' : 'private',
        'x-amz-copy-source' => '/'.$this->_storageBucket.'/'
          .$this->_getStorageObject($storageGroup, $storageId),
        'x-amz-metadata-directive' => 'REPLACE',
        'Content-Type' => $mimeType
      )
    );
    $client->send();
    if (200 === $client->getResponseStatus()) {
      $cacheParameters = array($storageId, $mimeType);
      if ($cache = $this->cache()) {
        $cache->write(
          $this->_statusCacheName,
          $storageGroup,
          $cacheParameters,
          $isPublic ? 'public' : 'private',
          $this->_storageCacheExpire
        );
      }
      return TRUE;
    }
    return FALSE;
  }
}
