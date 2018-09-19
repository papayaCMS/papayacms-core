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

/** @noinspection PhpUndefinedClassInspection */
namespace Papaya\Cache\Service;

use Papaya\Cache;

/**
 * Papaya Cache Service for memcache based cache
 *
 * @package Papaya-Library
 * @subpackage Cache
 */
class Memcache extends Cache\Service {
  /**
   * cache path configuration (servers and parameters)
   *
   * @var string
   */
  private $_cachePath = '';

  /**
   * memcache connection object
   *
   * @var object
   */
  private $_memcache;

  /**
   * memcache connection status
   *
   * @var bool
   */
  private $_connected = FALSE;

  /**
   * process cache - to avoid double requests
   *
   * @var array
   */
  protected $_localCache = [];

  /**
   * process cache - cache create times
   *
   * @var array
   */
  protected $_cacheCreated = [];

  /**
   * pattern that matches a singler server uri in configuration string
   *
   * @var string
   */
  private $_cachePathPattern =
    /* @lang TEXT */
    '(
      tcp://
      (?<host>[a-z\\d_.-]+)
      (?::(?<port>\\d+))?
      (?:\\?(?<params>[^,]+))?
    )ix';

  /**
   * pattern that matches the attributes for server connection in die query string part
   *
   * @var string
   */
  private $_cacheAttrPattern =
    '((?:^|&)(?P<name>persistent|weight|timeout|retry_interval)=(?P<value>\d+))';

  /**
   * possible Memcache classes
   *
   * @var array
   */
  protected $_memcacheClasses = [
    '\\Memcached',
    '\\Memcache'
  ];

  /**
   * set the memcache object
   *
   * @param \Memcache|\Memcached $memcache
   */
  public function setMemcacheObject($memcache) {
    $this->_memcache = $memcache;
  }

  /**
   * get memcache object instance
   *
   * @return \memcache|\memcached|false
   */
  public function getMemcacheObject() {
    if (NULL === $this->_memcache) {
      $this->_memcache = $this->_createMemcacheObject();
    }
    return $this->_memcache;
  }

  /**
   * @return false|\memcache|\memcached
   */
  protected function _createMemcacheObject() {
    foreach ($this->_memcacheClasses as $class) {
      if (\class_exists($class, FALSE)) {
        return $this->_memcache = new $class();
      }
    }
    return FALSE;
  }

  /**
   * read cache path option from configuration or ini file
   *
   * @param Cache\Configuration $configuration
   *
   * @return bool
   */
  public function setConfiguration(Cache\Configuration $configuration) {
    $this->_cachePath = $configuration['MEMCACHE_SERVERS'];
    if (empty($this->_cachePath) && \is_callable('ini_get')) {
      $this->_cachePath = ('memcache' === \ini_get('session.save_handler'))
        ? \ini_get('session.save_path') : '';
    }
    return TRUE;
  }

  /**
   * Check cache is usable
   *
   * @param bool $silent
   *
   * @throws \BadMethodCallException
   *
   * @return bool
   */
  public function verify($silent = TRUE) {
    $valid = $this->setUp();
    if (!($silent || $valid)) {
      throw new \BadMethodCallException('Memcache not available or invalid server.');
    }
    return $valid;
  }

  /**
   * Initialize connections to memcache servers
   *
   * @return bool
   */
  public function setUp() {
    if (($memcache = $this->getMemcacheObject()) &&
      FALSE !== $memcache &&
      !$this->_connected) {
      $servers = $this->getServersConfiguration();
      if (\count($servers) > 0) {
        $connected = FALSE;
        foreach ($servers as $server) {
          $connectedServer = $this->_connect($memcache, $server);
          if (!$connected && $connectedServer) {
            $connected = TRUE;
          }
        }
      } else {
        $connected = $memcache->addServer('localhost', 11211);
      }
      if (!$connected) {
        $memcache = $this->_memcache = FALSE;
      }
    }
    if (NULL !== $memcache && FALSE !== $memcache) {
      return $this->_connected = TRUE;
    }
    return FALSE;
  }

  /**
   * Initialize connection to a single memcache server
   *
   * @param \Memcache|\Memcached $memcache
   * @param array $server
   *
   * @return bool
   */
  private function _connect($memcache, $server) {
    $result = FALSE;
    if ($memcache instanceof \Memcache) {
      $result = $memcache->addServer(
        $server['host'],
        $server['port'],
        $server['persistent'],
        $server['weight'],
        $server['timeout'],
        $server['retry_interval']
      );
    } elseif ($memcache instanceof \Memcached) {
      $result = $memcache->addServer(
        $server['host'],
        $server['port'],
        $server['weight']
      );
    }
    return $result;
  }

  /**
   * load servers configuration from MEMCACHE_SERVERS or from
   * php.ini option session.save_path is the session handler is memcache
   *
   * @return array Servers
   */
  public function getServersConfiguration() {
    $servers = [];
    if (\preg_match_all($this->_cachePathPattern, $this->_cachePath, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        $server = [
          'host' => $match['host'],
          'port' => isset($match['port']) ? (int)$match['port'] : NULL,
          'persistent' => NULL,
          'weight' => NULL,
          'timeout' => NULL,
          'retry_interval' => NULL
        ];
        if (!empty($match['params'])) {
          $hasParameters = \preg_match_all(
            $this->_cacheAttrPattern, $match['params'], $subMatches, PREG_SET_ORDER
          );
          if ($hasParameters) {
            foreach ($subMatches as $subMatch) {
              $server[$subMatch['name']] = (int)$subMatch['value'];
            }
          }
        }
        $servers[] = $server;
      }
    }
    return $servers;
  }

  /**
   * Write element to cache
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param string $data Element data
   * @param int $expires Maximum age in seconds
   *
   * @return bool
   */
  public function write($group, $element, $parameters, $data, $expires = NULL) {
    if ($this->setUp() &&
      ($cacheId = $this->getCacheIdentifier($group, $element, $parameters))) {
      $time = \time();
      if ($this->_memcache->replace($cacheId, $time.':'.$data, $expires)) {
        return $cacheId;
      }
      if ($this->_memcache->set($cacheId, $time.':'.$data, $expires)) {
        return $cacheId;
      }
    }
    return FALSE;
  }

  /**
   * Read element from cache
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param int $expires Maximum age in seconds
   * @param int $ifModifiedSince first possible creation time
   *
   * @return string|false
   */
  public function read($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->setUp() &&
      ($cacheId = $this->getCacheIdentifier($group, $element, $parameters))) {
      if (isset($this->_localCache[$cacheId]) ||
        $this->_read($cacheId, $expires, $ifModifiedSince)) {
        return $this->_localCache[$cacheId];
      }
    }
    return FALSE;
  }

  /**
   * Check if element in cache exists and is still valid
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param int $expires Maximum age in seconds
   * @param int $ifModifiedSince first possible creation time
   *
   * @return bool
   */
  public function exists($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->setUp() &&
      ($cacheId = $this->getCacheIdentifier($group, $element, $parameters))) {
      if (isset($this->_localCache[$cacheId])) {
        return !empty($this->_localCache[$cacheId]);
      }
      return (bool)$this->_read($cacheId, $expires, $ifModifiedSince);
    }
    return FALSE;
  }

  /**
   * Check if element in cache exists and which time is was created
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   * @param int $expires Maximum age in seconds
   * @param int $ifModifiedSince first possible creation time
   *
   * @return int|false
   */
  public function created($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->verify() &&
      ($cacheId = $this->getCacheIdentifier($group, $element, $parameters))) {
      if (isset($this->_cacheCreated[$cacheId])) {
        return $this->_cacheCreated[$cacheId];
      }
      if ($this->_read($cacheId, $expires, $ifModifiedSince)) {
        return $this->_cacheCreated[$cacheId];
      }
    }
    return FALSE;
  }

  /**
   * Delete element(s) from cache, memcache supports no groups - so delete all
   *
   * @param string $group
   * @param string $element
   * @param string $parameters
   *
   * @return int
   */
  public function delete($group = NULL, $element = NULL, $parameters = NULL) {
    $result = TRUE;
    $servers = $this->getServersConfiguration();
    if (\count($servers) > 0) {
      foreach ($servers as $server) {
        $memcache = $this->_createMemcacheObject();
        if ($this->_connect($memcache, $server)) {
          if (!$memcache->flush()) {
            $result = 0;
          }
        } else {
          $result = 0;
        }
      }
    } elseif ($this->setUp()) {
      if (!$this->_memcache->flush()) {
        $result = 0;
      }
    }
    return $result;
  }

  /**
   * internal read item from cache - the item time is put to the top so we can extract and check it
   *
   * @param string $cacheId
   * @param int $expires
   * @param int $ifModifiedSince
   *
   * @return bool
   */
  private function _read($cacheId, $expires, $ifModifiedSince) {
    $data = $this->_memcache->get($cacheId);
    $headerEnd = \strpos($data, ':');
    if ($headerEnd > 0) {
      $created = (int)\substr($data, 0, $headerEnd);
      if (($created + $expires) > \time()) {
        if (NULL === $ifModifiedSince || $ifModifiedSince < $created) {
          $this->_localCache[$cacheId] = \substr($data, $headerEnd + 1);
          $this->_cacheCreated[$cacheId] = $created;
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}
