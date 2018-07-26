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
*
* Administration implementation of programm id files for Papaya-Cron-Moduls
*
* @package Papaya
* @subpackage Administration
*/
class pidfile {
  /**
  * Pid path and file
  * @var string $pidfile
  */
  var $pidfile = '/tmp/actual.pid';
  /**
  * PID
  * @var integer $pid
  */
  var $pid = 0;
  /**
  * Old PID
  * @var integer $oldPid
  */
  var $oldPid = 0;


  /**
  * Copy parameter $pidfile in $this->pidfile when parameter is set
  *
  * @param string $pidfile optional, default value NULL
  * @access public
  */
  function __construct($pidfile = NULL) {
    if (isset($pidfile)) {
      $this->pidfile = $pidfile;
    }
  }

  /**
   * Inizialisation of PID.
   *
   * @access public
   * @param bool $echo
   * @return mixed TRUE if pid wasn't set and set through function
   *                    or FALSE when pid was set
   */
  function execute($echo = TRUE) {
    if (!$this->get($echo)) {
      return $this->set();
    } else {
      return FALSE;
    }
  }

  /**
   * Read PID
   *
   * @access public
   * @param bool $echo
   * @throws LogicException
   * @return boolean
   */
  function get($echo = TRUE) {
    $this->oldPid = 0;
    $fileSystem = new \PapayaFileSystemFactory();
    $directory = $fileSystem->getDirectory($directoryName = dirname($this->pidfile));
    if (!$directory->isWriteable()) {
      throw new LogicException(
        sprintf(
          'Directory %s not writeable, can not create process lock file.',
          $directoryName
        )
      );
    }
    if (file_exists($this->pidfile)) {
      if ($fh = fopen($this->pidfile, 'r')) {
        $this->oldPid = (int)chop(fgets($fh, 20));
        fclose($fh);
      }
    }
    $this->pid = posix_getpid();
    if (($this->oldPid > 0) && ($this->oldPid != $this->pid)) {
      if ($echo) {
        echo $this->oldPid, '#', $this->pid;
      }
      if (posix_kill($this->oldPid, '0')) {
        return TRUE;
      } else {
        return FALSE;
      }
    } else {
      return FALSE;
    }
  }

  /**
  * Create a new PID - file
  *
  * @access public
  * @return boolean TRUE if pid wrote and FALSE when an error appears during worte command
  */
  function set() {
    if ($fh = fopen($this->pidfile, 'w')) {
      fwrite($fh, $this->pid);
      fclose($fh);
      chmod($this->pidfile, 0766);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Delete PID-File
  *
  * @access public
  */
  function delete() {
    if (file_exists($this->pidfile)) {
      unlink($this->pidfile);
    }
  }
}
