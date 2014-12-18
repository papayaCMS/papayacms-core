<?php
/**
* Papaya Streamwrapper for Amazon S3
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
* @subpackage Streamwrapper
* @version $Id: S3.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Papaya Streamwrapper for Amazon S3
*
* @package Papaya-Library
* @subpackage Streamwrapper
*/
class PapayaStreamwrapperS3 {

  const SECRET_PATTERN = '(^[^@\r\n]{40}$)';

  const PATH_PATTERN = '(^
    [a-zA-Z\d]+:(?://)?
    (?P<id>[A-Z\d]{20}):
    (?P<secret>([^@\r\n]{40})?)@
    (?P<bucket>[a-z\d][a-z\d.-]{2,63})/
    (?P<object>.*)
  )Dx';

  /**
  * secrets for s3 authentication
  * @var array
  */
  static private $_secrets = array();

  /**
  * Bitmask with options.
  *
  * All funktions that set this should not unset STREAM_REPORT_ERRORS
  * except when we should really stay quiet (e.g. for url_stat form
  * file_exists), because that constant will not be set by php itself.
  *
  * @var integer
  */
  private $_options = STREAM_REPORT_ERRORS;

  /**
  * resource location data
  * @var array
  */
  private $_location = array();

  /**
  * resource location data
  * @var array
  */
  private $_writeable = FALSE;

  /**
  * resource size
  * @var integer
  */
  private $_size = 0;

  /**
  * resource modification date
  * @var integer
  */
  private $_lastModified = 0;

  /**
  * internal file pointer position
  * @var integer
  */
  private $_position = 0;

  /**
  * internal direcotry position for readdir
  * which contains the last returned result
  * @var string
  */
  private $_directoryPosition = '';

  /**
  * internal directory cache for readdir
  * which contains results that were not yet returned
  * @var array
  */
  private $_directoryCache = array();

  /**
  * Amazon S3 handler object
  * @var PapayaStreamwrapperS3Handler
  */
  private $_handler = NULL;

  /**
  * buffer for reads
  * @var string
  */
  private $_buffer = "";

  /**
  * position in the file where the buffer starts
  * @var integer
  */
  private $_bufferStartPosition = 0;

  /**
  * Set the secret to use with an ID from Amazon S3
  * @param string $id
  * @param string $secret
  * @return boolean
  */
  public static function setSecret($id, $secret) {
    if (is_null($secret)) {
      unset(self::$_secrets[$id]);
      return FALSE;
    } elseif (1 === preg_match(self::SECRET_PATTERN, $secret)) {
      self::$_secrets[$id] = $secret;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Register stream wrapper if not defined
   * @param string $protocol
   * @return bool
   */
  public static function register($protocol) {
    $wrappers = stream_get_wrappers();
    foreach ($wrappers as $wrapper) {
      if ($wrapper === $protocol) {
        stream_wrapper_unregister($protocol);
      }
    }
    return stream_wrapper_register($protocol, 'PapayaStreamwrapperS3', STREAM_IS_URL);
  }

  /**
   * Set Amazon S3 handler object
   * @param PapayaStreamwrapperS3Handler $handler
   */
  public function setHandler(PapayaStreamwrapperS3Handler $handler) {
    $this->_handler = $handler;
  }

  /**
  * Get the Amazon S3 handler object
  * @return PapayaStreamwrapperS3Handler
  */
  public function getHandler() {
    if (!($this->_handler instanceof PapayaStreamwrapperS3Handler)) {
      $this->_handler = new PapayaStreamwrapperS3Handler();
    }
    return $this->_handler;
  }

  /**
  * Parse the given path into needed parts
  *
  * @param string $path
  * @param integer $options
  * @return array|boolean array with the path information or FALSE
  */
  public function parsePath($path, $options) {
    if (preg_match(self::PATH_PATTERN, $path, $matches)) {
      if (empty($matches['secret'])) {
        if (empty(self::$_secrets[$matches['id']])) {
          if ($options & STREAM_REPORT_ERRORS) {
            trigger_error(
              'No secret for Amazon S3 ID found',
              E_USER_WARNING
            );
          }
          return FALSE;
        }
        $matches['secret'] = self::$_secrets[$matches['id']];
      }
      return array(
        'bucket' => $matches['bucket'],
        'id' => $matches['id'],
        'secret' => $matches['secret'],
        'object' => $matches['object']
      );
    } elseif ($options & STREAM_REPORT_ERRORS) {
      trigger_error(
        'Invalid Amazon S3 resource string',
        E_USER_WARNING
      );
    }
    return FALSE;
  }

  /**
  * return TRUE if file pointer is at the end of the file
  *
  * @return integer
  */
  public function stream_eof() {
    return $this->_position >= $this->_size;
  }

  /**
  * Open file resource and cache informations
  *
  * @param string $path
  * @param string $mode
  * @param string $options
  * @param string $openedPath
  * @return boolean success
  */
  public function stream_open($path, $mode, $options, &$openedPath) {
    if (in_array($mode, array('r', 'w', 'rt', 'rb', 'wt', 'wb'))) {
      $this->_options |= $options;
      if ($this->_location = $this->parsePath($path, $this->_options)) {
        if (in_array($mode, array('w', 'wt', 'wb'))) {
          $this->_lastModified = strtotime(gmdate(DATE_RFC1123));
          $this->_writeable = TRUE;
          return $this->getHandler()->openWriteFile(
            $this->_location,
            $this->_options
          );
        } else {
          $resourceData = $this->fillBuffer(TRUE);
          if (is_null($resourceData)) {
            if ($this->_options & STREAM_REPORT_ERRORS) {
              trigger_error(
                'Can not find amazon resource.',
                E_USER_WARNING
              );
            }
          } else {
            $this->_size = $resourceData['size'];
            $this->_lastModified = $resourceData['modified'];
            return TRUE;
          }
        }
      }
    } elseif ($this->_options & STREAM_REPORT_ERRORS) {
      trigger_error(
        'Mode not support by stream wrapper: '.$mode,
        E_USER_WARNING
      );
    }
    return FALSE;
  }

  /**
  * Read given count of bytes and return them
  *
  * @param integer $count
  * @return string
  */
  public function stream_read($count) {
    if ($count > 0 &&
        $this->_position < $this->_size) {
      /* use a bigger buffer internally because
         php will only ever do reads of max size 8K */
      if ($this->_bufferStartPosition === $this->_position && strlen($this->_buffer) > 0) {
        $result = substr($this->_buffer, 0, $count);
        $this->_buffer = substr($this->_buffer, $count);
      } else {
        $this->fillBuffer();
        $result = substr($this->_buffer, 0, $count);
        $this->_buffer = substr($this->_buffer, $count);
      }
      if (!empty($result)) {
        $this->_position += strlen($result);
        $this->_bufferStartPosition = $this->_position;
        return $result;
      }
    }
    return '';
  }

  /**
  * Fill the read buffer and return the stat information
  *
  * @param boolean $force an request when the size is not yet known
  * @return array|NULL stat information or NULL
  */
  public function fillBuffer($force = FALSE) {
    if ($this->_position < $this->_size) {
      $bufferSize = $this->_size - $this->_position;
      if ($bufferSize > 1024 * 1024) {
        $bufferSize = 1024 * 1024;
      }
    } elseif ($force) {
      $bufferSize = 1024 * 1024;
    } else {
      return NULL;
    }
    list($this->_buffer, $stat) = $this->getHandler()->readFileContent(
      $this->_location, $this->_position, $bufferSize, $this->_options
    );
    $this->_bufferStartPosition = $this->_position;
    return $stat;
  }

  /**
  * Move internal file pointer
  *
  * @param integer $offset
  * @param integer $whence
  * @return boolean success
  */
  public function stream_seek($offset, $whence = SEEK_SET) {
    if (FALSE !== $this->_writeable) {
      if ($this->_options & STREAM_REPORT_ERRORS) {
        trigger_error(
          'Seek ist not supported for writeable streams',
          E_USER_WARNING
        );
      }
      return FALSE;
    }
    switch ($whence) {
    case SEEK_SET :
      if ($offset >= 0) {
        $this->_position = $offset;
        return TRUE;
      } else {
        return FALSE;
      }
      break;
    case SEEK_CUR :
      if ($offset >= 0) {
        $this->_position += $offset;
        return TRUE;
      } else {
        return FALSE;
      }
      break;
    case SEEK_END :
      if ($this->_size + $offset >= 0) {
        $this->_position = $this->_size + $offset;
        return TRUE;
      } else {
        return FALSE;
      }
      break;
    default:
      return FALSE;
    }
  }

  /**
  * Return current file pointer position
  *
  * @return integer
  */
  public function stream_tell() {
    return $this->_position;
  }

  /**
  * Return cached information about current resource
  *
  * @return array with stat information
  */
  public function stream_stat() {
    return array(
      'dev' => 0,
      'ino' => 0,
      'mode' => 0100006,
      'nlink' => 0,
      'uid' => 0,
      'gid' => 0,
      'rdev' => 0,
      'size' => $this->_size,
      'atime' => $this->_lastModified,
      'mtime' => $this->_lastModified,
      'ctime' => $this->_lastModified,
      'blksize' => 0,
      'blocks' => -1
    );
  }

  /**
  * Return information about specified resource
  *
  * @param string $path
  * @param integer $flags bitmask
  * @return array|NULL stat information or NULL
  */
  public function url_stat($path, $flags) {
    $resourceData = NULL;
    if ($location = $this->parsePath($path, $this->_options)) {
      if (substr($location['object'], -1) != '/') {
        $resourceData = $this->getHandler()->getFileInformations(
          $location,
          $this->_options
        );
      }
      if (is_null($resourceData)) {
        $resourceData = $this->getHandler()->getDirectoryInformations(
          $location,
          $this->_options
        );
      }
      if (!is_null($resourceData)) {
        return array(
          'dev' => 0,
          'ino' => 0,
          'mode' => $resourceData['mode'],
          'nlink' => 0,
          'uid' => 0,
          'gid' => 0,
          'rdev' => 0,
          'size' => $resourceData['size'],
          'atime' => $resourceData['modified'],
          'mtime' => $resourceData['modified'],
          'ctime' => $resourceData['modified'],
          'blksize' => 0,
          'blocks' => -1
        );
      } elseif ($this->_options & STREAM_REPORT_ERRORS &&
          !($flags & STREAM_URL_STAT_QUIET)) {
        trigger_error(
          'Can not find amazon resource.',
          E_USER_WARNING
        );
      }
    }
    return NULL;
  }

  /**
  * Open the specified directory
  *
  * @param string $path
  * @param integer $options bitmask
  * @return boolean success
  */
  public function dir_opendir($path, $options) {
    $this->_options |= $options;
    if ($location = $this->parsePath($path, $this->_options)) {
      $resourceData = $this->getHandler()->getDirectoryInformations(
        $location,
        $this->_options
      );
      if (is_null($resourceData)) {
        if ($this->_options & STREAM_REPORT_ERRORS) {
          trigger_error(
            'Can not find amazon resource.',
            E_USER_WARNING
          );
        }
      } else {
        $this->_location = $location;
        $this->_directoryCache = $resourceData;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Read the next entry from the directory
  *
  * @return string|boolean name of the entry or
  *   FALSE for the end of entries or failure
  */
  public function dir_readdir() {
    while (FALSE === $result = current($this->_directoryCache['contents'])) {
      // load more
      if ($this->_directoryCache['moreContent'] !== TRUE) {
        // no more entries in this directory
        return FALSE;
      }
      $resourceData = $this->getHandler()->getDirectoryInformations(
        $this->_location,
        $this->_options,
        4000,
        $this->_directoryPosition
      );
      if (is_null($resourceData)) {
        if ($this->_options & STREAM_REPORT_ERRORS) {
          trigger_error(
            'Can not find amazon resource.',
            E_USER_WARNING
          );
        }
        return FALSE;
      }
      $this->_directoryCache = $resourceData;
    }

    next($this->_directoryCache['contents']);
    $this->_directoryPosition = $result;
    return $result;
  }

  /**
  * Reset status like just after dir_opendir was called
  *
  * @return boolean success
  */
  public function dir_rewinddir() {
    if ($this->_directoryCache['startMarker'] !== '') {
      $this->_directoryCache['startMarker'] = '';
      $this->_directoryCache['contents'] = array();
      $this->_directoryCache['moreContent'] = TRUE;
    } else {
      reset($this->_directoryCache['contents']);
    }
    $this->_directoryPosition = '';
    return TRUE;
  }

  /**
  * Write $data to stream
  *
  * @param string $data
  * @return integer amount of bytes written
  */
  public function stream_write($data) {
    $result = $this->getHandler()->writeFileContent($this->_options, $data);
    $this->_size += $result;
    $this->_position += $result;
    return $result;
  }

  /**
  * Close the stream, necessary for writeable streams
  *
  * @return void
  */
  public function stream_close() {
    if (TRUE === $this->_writeable) {
      $this->getHandler()->closeWriteFile($this->_options);
    }
  }

  /**
  * Remove a file.
  *
  * @param string $path
  * @return boolean success
  */
  public function unlink($path) {
    if ($location = $this->parsePath($path, $this->_options)) {
      $handler = $this->getHandler();
      if (NULL === $handler->getFileInformations($location, $this->_options)) {
        if ($this->_options & STREAM_REPORT_ERRORS) {
          trigger_error(
            'Can not find amazon resource.',
            E_USER_WARNING
          );
        }
        return FALSE;
      }
      return $handler->removeFile($location, $this->_options);
    }
    return FALSE;
  }

  /**
  * Create a direktory.
  *
  * @param string $path
  * @param integer $mode permission mask
  * @param integer $options bitmask
  * @return boolean success
  */
  public function mkdir($path, $mode, $options) {
    $this->_options |= $options;
    if ($location = $this->parsePath($path, $this->_options)) {
      $handler = $this->getHandler();
      if (substr($location['object'], -1) === '/') {
        $location['object'] = substr($location['object'], 0, -1);
      }

      $status = $handler->getDirectoryInformations($location, $this->_options);
      if (NULL !== $status) {
        if ($this->_options & STREAM_REPORT_ERRORS) {
          trigger_error(
            'Directory already present.',
            E_USER_WARNING
          );
        }
        return FALSE;
      }

      $status = $handler->getFileInformations($location, $this->_options);
      if (NULL !== $status) {
        if ($this->_options & STREAM_REPORT_ERRORS) {
          trigger_error(
            'File already present.',
            E_USER_WARNING
          );
        }
        return FALSE;
      }

      // create object with the same name as the directory
      $status = $handler->openWriteFile(
        $location,
        $this->_options,
        'application/x-directory'
      );
      if (FALSE === $status) {
        return FALSE;
      }
      $handler->closeWriteFile($this->_options);

      //create a file named $ in the directory
      $location['object'] .= '/$';
      $status = $handler->openWriteFile(
        $location,
        $this->_options
      );
      if (FALSE === $status) {
        return FALSE;
      }
      $handler->closeWriteFile($this->_options);

      return TRUE;
    }
    return FALSE;
  }

  /**
  * Removes a direktory.
  *
  * @param string $path
  * @param integer $options bitmask
  * @return boolean success
  */
  public function rmdir($path, $options) {
    $this->_options |= $options;
    if ($location = $this->parsePath($path, $this->_options)) {
      $handler = $this->getHandler();
      $directoryInformation =
        $handler->getDirectoryInformations($location, $this->_options, 2);
      if (NULL === $directoryInformation) {
        if ($this->_options & STREAM_REPORT_ERRORS) {
          trigger_error(
            'Can not find amazon resource.',
            E_USER_WARNING
          );
        }
        return FALSE;
      }
      if ($directoryInformation['moreContent'] === TRUE) {
        if ($this->_options & STREAM_REPORT_ERRORS) {
          trigger_error(
            'Directory not empty.',
            E_USER_WARNING
          );
        }
        return FALSE;
      }
      $empty = TRUE;
      foreach ($directoryInformation['contents'] as $value) {
        if ($value !== '$') {
          $empty = FALSE;
          break;
        }
      }
      if ($empty === FALSE) {
        if ($this->_options & STREAM_REPORT_ERRORS) {
          trigger_error(
            'Directory not empty.',
            E_USER_WARNING
          );
        }
        return FALSE;
      }
      if (substr($location['object'], -1) === '/') {
        $location['object'] = substr($location['object'], 0, -1);
      }
      // remove file with the same name as the directory (s3fs style)
      $handler->removeFile($location, $this->_options);
      // remove s3fox style directory indicator
      $s3fox = $location;
      $s3fox['object'] .= '_$folder$';
      $handler->removeFile($s3fox, $this->_options);
      // remove our directory indicator
      $location['object'] .= '/$';
      return $handler->removeFile($location, $this->_options);
    }
    return FALSE;
  }
}

