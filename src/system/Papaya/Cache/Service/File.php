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
namespace Papaya\Cache\Service;

use Papaya\Cache;
use Papaya\File\System as FileSystem;
use Papaya\Utility;

/**
 * Papaya Cache Service for file system cache
 *
 * @package Papaya-Library
 * @subpackage Cache
 */
class File extends Cache\Service {
  /**
   * cache base directory
   *
   * @var string $cacheDirectory
   */
  private $_cacheDirectory;

  /**
   * notifier script for cache update syncs
   *
   * @var string $_notifierScript
   */
  private $_notifierScript;

  /**
   * @var FileSystem\Change\Notifier
   */
  private $_notifier;

  /**
   * @var bool
   */
  private $_allowUnlink;

  /**
   * Set configuration option
   *
   * @param Cache\Configuration $configuration
   */
  public function setConfiguration(Cache\Configuration $configuration) {
    $this->_cacheDirectory = $configuration['FILESYSTEM_PATH'];
    $this->_notifierScript =
      empty($configuration['FILESYSTEM_NOTIFIER_SCRIPT'])
        ? FALSE
        : $configuration['FILESYSTEM_NOTIFIER_SCRIPT'];
    $this->_allowUnlink =
      isset($configuration['FILESYSTEM_DISABLE_CLEAR'])
        ? !$configuration['FILESYSTEM_DISABLE_CLEAR']
        : TRUE;
  }

  /**
   * Check cache is usable
   *
   * @param bool $silent
   *
   * @throws \LogicException
   *
   * @return bool
   */
  public function verify($silent = TRUE) {
    if (empty($this->_cacheDirectory)) {
      $message = 'No cache directory defined';
    } elseif (\file_exists($this->_cacheDirectory) &&
      \is_dir($this->_cacheDirectory) &&
      \is_readable($this->_cacheDirectory) &&
      \is_writable($this->_cacheDirectory)) {
      return TRUE;
    } else {
      $message = 'Cache directory does not exist or has invalid permissions';
    }
    if (!$silent) {
      throw new \LogicException($message);
    }
    return FALSE;
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
    $oldMask = NULL;
    if (
      $this->verify() &&
      ($identifiers = $this->_getCacheIdentification($group, $element, $parameters)) &&
      $this->_ensureLocalDirectory($identifiers['group'], $oldMask) &&
      $this->_ensureLocalDirectory($identifiers['element'], $oldMask)
    ) {
      if (NULL !== $oldMask) {
        \umask($oldMask);
      }
      if ($fh = \fopen($identifiers['file'], 'wb')) {
        \fwrite($fh, $data);
        \fclose($fh);
        $this->notify(FileSystem\Change\Notifier::ACTION_MODIFIED, $identifiers['file']);
        return $identifiers['identifier'];
      }
      // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
    return FALSE;
  }

  /**
   * Create local directory
   *
   * @param string $directory
   * @param int $oldMask
   *
   * @return bool
   */
  private function _ensureLocalDirectory($directory, &$oldMask) {
    if (\file_exists($directory) && \is_dir($directory)) {
      return TRUE;
    }
    if (NULL === $oldMask) {
      $oldMask = \umask(0);
    }
    /** @noinspection MkdirRaceConditionInspection */
    @\mkdir($directory, 0777);
    if (\file_exists($directory)) {
      $this->notify(FileSystem\Change\Notifier::ACTION_ADD, NULL, $directory);
      return TRUE;
      // @codeCoverageIgnoreStart
    }
    return FALSE;
    // @codeCoverageIgnoreEnd
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
    if ($this->exists($group, $element, $parameters, $expires, $ifModifiedSince)) {
      $identifiers = $this->_getCacheIdentification($group, $element, $parameters);
      return \file_get_contents($identifiers['file']);
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
    return (FALSE !== $this->created($group, $element, $parameters, $expires, $ifModifiedSince));
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
    if (
      $expires > 0 &&
      $this->verify() &&
      ($identifiers = $this->_getCacheIdentification($group, $element, $parameters)) &&
      \file_exists($identifiers['file']) &&
      \is_readable($identifiers['file'])
    ) {
      $created = \filemtime($identifiers['file']);
      if (($created + $expires) > \time()) {
        if (NULL === $ifModifiedSince || $ifModifiedSince < $created) {
          return $created;
        }
      }
    }
    return FALSE;
  }

  /**
   * Delete element(s) from cache
   *
   * @param string $group
   * @param string $element
   * @param mixed $parameters
   *
   * @return int|bool
   */
  public function delete($group = NULL, $element = NULL, $parameters = NULL) {
    if ($this->verify()) {
      $cache = Utility\File\Path::cleanup($this->_cacheDirectory);
      if (NULL !== $group) {
        $cache .= $this->_escapeIdentifierString($group).'/';
      }
      if (NULL !== $element) {
        $cache .= $this->_escapeIdentifierString($element).'/';
      }
      if (NULL !== $parameters) {
        $cache .= $this->_escapeIdentifierString(
          $this->_serializeParameters($parameters)
        );
      }
      if (\file_exists($cache) && \is_file($cache)) {
        \unlink($cache);
        $this->notify(FileSystem\Change\Notifier::ACTION_DELETED, $cache);
        return 1;
      }
      if ($this->_allowUnlink && \file_exists($cache) && \is_dir($cache)) {
        $count = Utility\File\Path::clear($cache);
        $this->notify(FileSystem\Change\Notifier::ACTION_CLEARED, NULL, $cache);
        return $count;
      }
      $this->notify(FileSystem\Change\Notifier::ACTION_INVALIDATED, NULL, $cache);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * get different cache identifier data
   *
   * @param string $group
   * @param string $element
   * @param string|array $parameters
   *
   * @throws \InvalidArgumentException
   *
   * @return array
   */
  protected function _getCacheIdentification($group, $element, $parameters) {
    $identification = parent::_getCacheIdentification($group, $element, $parameters);
    $groupDirectory = Utility\File\Path::cleanup($this->_cacheDirectory).$identification['group'];
    $cacheId =
      $identification['group'].'/'.$identification['element'].'/'.$identification['parameters'];
    if (\strlen($cacheId) > 255) {
      throw new \InvalidArgumentException('Cache id string to large');
    }
    return [
      'group' => $groupDirectory,
      'element' => $groupDirectory.'/'.$identification['element'],
      'file' => $groupDirectory.'/'.$identification['element'].'/'.$identification['parameters'],
      'identifier' => $cacheId
    ];
  }

  /**
   * Notify the sync script about the change
   *
   * @param int $action
   * @param string|null $file
   * @param string|null $path
   */
  private function notify($action, $file = NULL, $path = NULL) {
    if ($notifier = $this->notifier()) {
      $notifier->notify($action, $file, $path);
    }
  }

  /**
   * Getter/Setter for a notifer object - this will notify an external script or url
   * about the file change.
   *
   * @param FileSystem\Change\Notifier $notifier
   *
   * @return FileSystem\Change\Notifier|false
   */
  public function notifier(FileSystem\Change\Notifier $notifier = NULL) {
    if (NULL !== $notifier) {
      $this->_notifier = $notifier;
    } elseif (NULL === $this->_notifier) {
      if ($this->_notifierScript) {
        $this->_notifier = new FileSystem\Change\Notifier($this->_notifierScript);
      } else {
        $this->_notifier = FALSE;
      }
    }
    return $this->_notifier;
  }
}
