<?php
/**
* Papaya Cache Service for file system cache
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Cache
* @version $Id: File.php 39418 2014-02-27 17:14:05Z weinert $
*/

/**
* Papaya Cache Service for file system cache
*
* @package Papaya-Library
* @subpackage Cache
*/
class PapayaCacheServiceFile extends PapayaCacheService {

  /**
  * cache base directory
  * @var string $cacheDirectory
  */
  private $_cacheDirectory = NULL;

  /**
  * notifier script for cache update syncs
  * @var string $_notifierScript
  */
  private $_notifierScript = NULL;

  /**
  * @var PapayaFileSystemChangeNotifier
  */
  private $_notifier = NULL;


  /**
  * @var boolean
  */
  private $_allowUnlink = NULL;

  /**
  * Set configuration option
  * @param PapayaCacheConfiguration $configuration
  * @return void
  */
  public function setConfiguration(PapayaCacheConfiguration $configuration) {
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
   * @param boolean $silent
   * @throws LogicException
   * @return boolean
   */
  public function verify($silent = TRUE) {
    if (empty($this->_cacheDirectory)) {
      $message = 'No cache directory defined';
    } elseif (file_exists($this->_cacheDirectory) &&
              is_dir($this->_cacheDirectory) &&
              is_readable($this->_cacheDirectory) &&
              is_writeable($this->_cacheDirectory)) {
      return TRUE;
    } else {
      $message = 'Cache directory does not exist or has invalid permissions';
    }
    if (!$silent) {
      throw new LogicException($message);
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
  * @param integer $expires Maximum age in seconds
  * @return boolean
  */
  public function write($group, $element, $parameters, $data, $expires = NULL) {
    $oldMask = NULL;
    if ($this->verify() &&
        ($identifiers = $this->_getCacheIdentification($group, $element, $parameters)) &&
        $this->_ensureLocalDirectory($identifiers['group'], $oldMask) &&
        $this->_ensureLocalDirectory($identifiers['element'], $oldMask)) {
      if (!is_null($oldMask)) {
        umask($oldMask);
      }
      if ($fh = fopen($identifiers['file'], 'w')) {
        fwrite($fh, $data);
        fclose($fh);
        $this->notify(PapayaFileSystemChangeNotifier::ACTION_MODIFIED, $identifiers['file']);
        return $identifiers['identifier'];
      }
      // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
    return FALSE;
  }

  /**
  * Create local directory
  * @param string $directory
  * @param integer $oldMask
  * @return boolean
  */
  private function _ensureLocalDirectory($directory, &$oldMask) {
    if (file_exists($directory) && is_dir($directory)) {
      return TRUE;
    } else {
      if (is_null($oldMask)) {
        $oldMask = umask(0);
      }
      @mkdir($directory, 0777);
      if (file_exists($directory)) {
        $this->notify(PapayaFileSystemChangeNotifier::ACTION_ADD, NULL, $directory);
        return TRUE;
        // @codeCoverageIgnoreStart
      }
      return FALSE;
      // @codeCoverageIgnoreEnd
    }
  }

  /**
  * Read element from cache
  *
  * @param string $group
  * @param string $element
  * @param string $parameters
  * @param integer $expires Maximum age in seconds
  * @param integer $ifModifiedSince first possible creation time
  * @return string|FALSE
  */
  public function read($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->exists($group, $element, $parameters, $expires, $ifModifiedSince)) {
      $identifiers = $this->_getCacheIdentification($group, $element, $parameters);
      return file_get_contents($identifiers['file']);
    }
    return FALSE;
  }

  /**
  * Check if element in cache exists and is still valid
  *
  * @param string $group
  * @param string $element
  * @param string $parameters
  * @param integer $expires Maximum age in seconds
  * @param integer $ifModifiedSince first possible creation time
  * @return boolean
  */
  public function exists($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->verify() &&
        $expires > 0 &&
       ($identifiers = $this->_getCacheIdentification($group, $element, $parameters))) {
      if (file_exists($identifiers['file']) && is_readable($identifiers['file'])) {
        $created = filemtime($identifiers['file']);
        if (($created + $expires) > time()) {
          if (is_null($ifModifiedSince) || $ifModifiedSince < $created) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Check if element in cache exists and which time is was created
  *
  * @param string $group
  * @param string $element
  * @param string $parameters
  * @param integer $expires Maximum age in seconds
  * @param integer $ifModifiedSince first possible creation time
  * @return integer|FALSE
  */
  public function created($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
    if ($this->verify() &&
        $expires > 0 &&
       ($identifiers = $this->_getCacheIdentification($group, $element, $parameters))) {
      if (file_exists($identifiers['file']) && is_readable($identifiers['file'])) {
        $created = filemtime($identifiers['file']);
        if (($created + $expires) > time()) {
          if (is_null($ifModifiedSince) || $ifModifiedSince < $created) {
            return $created;
          }
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
  * @param string $parameters
  * @return integer|boolean
  */
  public function delete($group = NULL, $element = NULL, $parameters = NULL) {
    if ($this->verify()) {
      $cache = PapayaUtilFilePath::cleanup($this->_cacheDirectory);
      if (!empty($group)) {
        $cache .= $this->_escapeIdentifierString($group).'/';
      }
      if (!empty($element)) {
        $cache .= $this->_escapeIdentifierString($element).'/';
      }
      if (!empty($parameters)) {
        $cache .= $this->_escapeIdentifierString(
          $this->_serializeParameters($parameters)
        );
      }
      if (file_exists($cache) && is_file($cache)) {
        unlink($cache);
        $this->notify(PapayaFileSystemChangeNotifier::ACTION_DELETED, $cache);
        return 1;
      } elseif (file_exists($cache) && is_dir($cache) && $this->_allowUnlink) {
        $count = PapayaUtilFilePath::clear($cache);
        $this->notify(PapayaFileSystemChangeNotifier::ACTION_CLEARED, NULL, $cache);
        return $count;
      } else {
        $this->notify(PapayaFileSystemChangeNotifier::ACTION_INVALIDATED, NULL, $cache);
      }
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
   * @throws InvalidArgumentException
   * @return array
   */
  protected function _getCacheIdentification($group, $element, $parameters) {
    $identification = parent::_getCacheIdentification($group, $element, $parameters);
    $groupDirectory = PapayaUtilFilePath::cleanup($this->_cacheDirectory).$identification['group'];
    $cacheId =
      $identification['group'].'/'.$identification['element'].'/'.$identification['parameters'];
    if (strlen($cacheId) > 255) {
      throw new InvalidArgumentException('Cache id string to large');
    }
    return array(
      'group' => $groupDirectory,
      'element' => $groupDirectory.'/'.$identification['element'],
      'file' => $groupDirectory.'/'.$identification['element'].'/'.$identification['parameters'],
      'identifier' => $cacheId
    );
  }

  /**
  * Notify the sync script about the change
  *
  * @param integer $action
  * @param string|NULL $file
  * @param string|NULL $path
  */
  private function notify($action, $file = NULL, $path = NULL) {
    if ($notifier = $this->notifier()) {
      $notifier->notify($action, $file, $path);
    };
  }

  /**
  * Getter/Setter for a notifer object - this will notify an external script or url
  * about the file change.
  *
  * @param PapayaFileSystemChangeNotifier $notifier
  * @return PapayaFileSystemChangeNotifier
  */
  public function notifier(PapayaFileSystemChangeNotifier $notifier = NULL) {
    if (isset($notifier)) {
      $this->_notifier = $notifier;
    } elseif (NULL === $this->_notifier) {
      if ($this->_notifierScript) {
        $this->_notifier = new PapayaFileSystemChangeNotifier($this->_notifierScript);
      } else {
        $this->_notifier = FALSE;
      }
    }
    return $this->_notifier;
  }
}