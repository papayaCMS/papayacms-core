<?php
/**
* Execute a local script
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @subpackage FileSystem
* @version $Id: Script.php 38144 2013-02-19 16:28:58Z weinert $
*/

/**
* Execute a local script
*
* @package Papaya-Library
* @subpackage FileSystem
*/
class PapayaFileSystemActionScript implements PapayaFileSystemAction {

  private $_script;

  public function __construct($script) {
    $this->_script = $script;
  }

  /**
   * Execute a local script
   * @param array $parameters
   */
  public function execute(array $parameters = array()) {
    $arguments = array();
    foreach ($parameters as $name => $value) {
      $arguments['--'.$name] = $value;
    }
    $this->executeCommand($this->_script, $arguments);
  }


  /**
   * Execute a shell command
   * @param string $command
   * @param array $arguments
   * @codeCoverageIgnore
   */
  protected function executeCommand($command, $arguments) {
    if (is_callable('pcntl_exec')) {
      pcntl_exec($command, $arguments);
    } else {
      $command = escapeshellcmd($command);
      foreach ($arguments as $name => $value) {
        $command .= ' '.escapeshellarg($name.'='.$value);
      }
      exec($command);
    }
  }
}
