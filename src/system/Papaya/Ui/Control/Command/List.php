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
* A command that executes a list of other commands. This can be used to combine separate commands
* into a single one.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiControlCommandList
  extends \PapayaUiControlCommand
  implements \ArrayAccess, \Countable, \IteratorAggregate {

  /**
  * List of commands
  *
  * @var array
  */
  private $_commands = array();

  /**
  * Create object, assign all arguments as commands to the internal list.
  */
  public function __construct() {
    foreach (func_get_args() as $command) {
      $this->offsetSet(NULL, $command);
    }
  }

  /**
  * Execute commands and append result to output xml
  *
  * @param \PapayaXmlElement
  */
  public function appendTo(\PapayaXmlElement $parent) {
    /** @var \PapayaUiControlCommand $command */
    foreach ($this->_commands as $command) {
      if ($command->validateCondition() &&
          $command->validatePermission()) {
        $command->appendTo($parent);
      }
    }
  }

  /**
   * Overload owner method to set owner on all commands, too.
   *
   * @param \Papaya\Request\Parameters\Access|\PapayaUiControlInteractive $owner
   * @return \Papaya\Request\Parameters\Access
   */
  public function owner(\Papaya\Request\Parameters\Access $owner = NULL) {
    \Papaya\Utility\Constraints::assertInstanceOf(\PapayaUiControlInteractive::class, $owner);
    if (isset($owner)) {
      /** @var \PapayaUiControlCommand $command */
      foreach ($this->_commands as $command) {
        $command->owner($owner);
      }
    }
    return parent::owner($owner);
  }

  /**
   * ArrayAccess interface: validate if command with the offset is set.
   *
   * @param integer $offset
   * @return bool
   */
  public function offsetExists($offset) {
    return isset($this->_commands[$offset]);
  }

  /**
  * ArrayAccess interface: get command at given offset.
  *
  * @param integer $offset
  * @return \PapayaUiControlCommand
  */
  public function offsetGet($offset) {
    return $this->_commands[$offset];
  }

  /**
   * ArrayAccess interface: add/replace command
   *
   * @param integer $offset
   * @param \PapayaUiControlCommand $command
   * @throws \UnexpectedValueException
   */
  public function offsetSet($offset, $command) {
    if ($command instanceof \PapayaUiControlCommand) {
      $this->_commands[$offset] = $command;
      $this->_commands = array_values($this->_commands);
    } else {
      throw new \UnexpectedValueException(
        sprintf(
          'Expected instance of "PapayaUiControlCommand" but "%s" was given.',
          is_object($command) ? get_class($command) : gettype($command)
        )
      );
    }
  }

  /**
  * ArrayAccess interface: remove command at given offset.
  *
  * @param integer $offset
  */
  public function offsetUnset($offset) {
    unset($this->_commands[$offset]);
    $this->_commands = array_values($this->_commands);
  }

  /**
  * Countable interface: get command count.
  *
  * @return integer
  */
  public function count() {
    return count($this->_commands);
  }

  /**
  * IteratorAggregate interface: create iterator for commands
  *
  * @return \ArrayIterator
  */
  public function getIterator() {
    return new \ArrayIterator($this->_commands);
  }
}
