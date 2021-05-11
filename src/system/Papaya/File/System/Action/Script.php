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
namespace Papaya\File\System\Action;

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\File\System as FileSystem;

/**
 * Execute a local script
 *
 * @package Papaya-Library
 * @subpackage FileSystem
 */
class Script implements FileSystem\Action, StringCastable {
  private $_script;

  public function __construct($script) {
    $this->_script = $script;
  }

  public function __toString() {
    return $this->_script;
  }

  /**
   * Execute a local script
   *
   * @param array $parameters
   */
  public function execute(array $parameters = []) {
    $arguments = [];
    foreach ($parameters as $name => $value) {
      $arguments['--'.$name] = $value;
    }
    $this->executeCommand($this->_script, $arguments);
  }

  /**
   * Execute a shell command
   *
   * @param string $command
   * @param array $arguments
   * @codeCoverageIgnore
   */
  protected function executeCommand($command, $arguments) {
    if (\is_callable('pcntl_exec')) {
      /** @noinspection PhpComposerExtensionStubsInspection */
      pcntl_exec($command, $arguments);
    } else {
      $command = \escapeshellcmd($command);
      foreach ($arguments as $name => $value) {
        $command .= ' '.\escapeshellarg($name.'='.$value);
      }
      \exec($command);
    }
  }
}
