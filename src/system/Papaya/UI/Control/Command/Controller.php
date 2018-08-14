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

namespace Papaya\UI\Control\Command;
/**
 * A group of commands, one of the command ist executed depending on parameter value and
 * default command.
 *
 * The controller is a command itselt, so one controller can be added to another for subcommands.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Controller
  extends \Papaya\UI\Control\Command
  implements \ArrayAccess, \Countable, \IteratorAggregate {

  /**
   * Array of command objects
   *
   * @var \Papaya\UI\Control\Command[]
   */
  protected $_commands = array();

  /**
   * Parameter name
   *
   * @var \Papaya\Request\Parameters\Name
   */
  private $_parameterName;

  /**
   * Default command identifier, if it is set it is used if the parameter value is empty
   * or unkown.
   *
   * @var string
   */
  private $_defaultCommand;

  /**
   * Initialize parameter controller, set parameter name and default command identifier
   *
   * @param array|\Papaya\Request\Parameters\Name|string $parameterName
   * @param string $defaultCommand
   */
  public function __construct($parameterName, $defaultCommand = '') {
    $this->_parameterName = new \Papaya\Request\Parameters\Name($parameterName);
    $this->_defaultCommand = \Papaya\Utility\Text\Identifier::toUnderscoreLower($defaultCommand);
  }

  /**
   * Execute command and append output after validating the user permission
   *
   * @param \Papaya\XML\Element $parent
   * @return \Papaya\XML\Element|NULL
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    if (
      $this->validateCondition() &&
      $this->validatePermission() &&
      ($command = $this->getCurrent()) &&
      $command->validateCondition() &&
      $command->validatePermission()
    ) {
      return $command->appendTo($parent);
    }
    return NULL;
  }

  /**
   * Get the current command, checking the parameter values and default command name.
   *
   * @return NULL|\Papaya\UI\Control\Command
   */
  public function getCurrent() {
    $name = \Papaya\Utility\Text\Identifier::toUnderscoreLower(
      $this->owner()->parameters()->get((string)$this->_parameterName, '')
    );
    if (isset($this->_commands[$name])) {
      return $this->_commands[$name];
    }
    if (isset($this->_commands[$this->_defaultCommand])) {
      return $this->_commands[$this->_defaultCommand];
    }
    return NULL;
  }

  /**
   * ArrayAccess interface: check if a command name exists
   *
   * @param string $name
   * @return bool
   */
  public function offsetExists($name) {
    return isset($this->_commands[\Papaya\Utility\Text\Identifier::toUnderscoreLower($name)]);
  }

  /**
   * ArrayAccess interface: get command by name
   *
   * @param string $name
   * @return \Papaya\UI\Control\Command
   */
  public function offsetGet($name) {
    return $this->_commands[\Papaya\Utility\Text\Identifier::toUnderscoreLower($name)];
  }

  /**
   * ArrayAccess interface: set command by name, overwrite existing command, set owner on
   * added command if the controller has an owner.
   *
   * @param string $name
   * @param \Papaya\UI\Control\Command $command
   */
  public function offsetSet($name, $command) {
    $name = \Papaya\Utility\Text\Identifier::toUnderscoreLower($name);
    if ($this->hasOwner()) {
      $command->owner($this->owner());
    }
    $this->_commands[$name] = $command;
  }

  /**
   * ArrayAccess interface: remove command from list
   *
   * @param string $name
   */
  public function offsetUnset($name) {
    unset($this->_commands[\Papaya\Utility\Text\Identifier::toUnderscoreLower($name)]);
  }

  /**
   * Countable interface: return command count
   *
   * @return integer
   */
  public function count() {
    return count($this->_commands);
  }

  /**
   * IteratorAggregate interface: return iterator for commands
   *
   * @return \ArrayIterator
   */
  public function getIterator() {
    return new \ArrayIterator($this->_commands);
  }

  /**
   * Overload owner method to set owner on all commands, too.
   *
   * @param \Papaya\Request\Parameters\Access $owner
   * @return \Papaya\Request\Parameters\Access
   */
  public function owner(\Papaya\Request\Parameters\Access $owner = NULL) {
    if (NULL !== $owner) {
      /** @var \Papaya\UI\Control\Command $command */
      foreach ($this->_commands as $command) {
        $command->owner($owner);
      }
    }
    return parent::owner($owner);
  }

  /**
   * Magic method, check if a command with the specified name exists.
   *
   * @param string $name
   * @return bool
   */
  public function __isset($name) {
    return $this->offsetExists($name);
  }

  /**
   * Magic method, threat the command names as properties to read them.
   *
   * @param string $name
   * @return NULL|\Papaya\UI\Control\Command
   */
  public function __get($name) {
    return $this->offsetGet($name);
  }

  /**
   * Magic method, threat the command names as properties to write them.
   *
   * @param string $name
   * @param \Papaya\UI\Control\Command $command
   */
  public function __set($name, $command) {
    $this->offsetSet($name, $command);
  }

  /**
   * Magic method, remove command with the specified name
   *
   * @param string $name
   */
  public function __unset($name) {
    $this->offsetUnset($name);
  }
}
